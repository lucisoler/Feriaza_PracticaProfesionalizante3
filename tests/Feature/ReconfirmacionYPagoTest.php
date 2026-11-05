<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\User;
use App\Notifications\CuentaAnulada;
use App\Notifications\ReconfirmacionRecibida;
use App\Notifications\RecordatorioReconfirmacion;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\LugarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Plazos de inscripción y reconfirmación, pago de la mesa y anulación de la
 * cuenta (RN-15 a RN-19). Se usa un revendedor para no depender del plano.
 */
class ReconfirmacionYPagoTest extends TestCase
{
    use RefreshDatabase;

    private Evento $evento;

    private Feriante $feriante;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategoriaSeeder::class, LugarSeeder::class]);

        $this->travelTo(Carbon::parse('2026-11-01 10:00'));

        // Primer día: 20/11 a las 17:00
        $this->evento = Evento::create([
            'nombre' => 'Feria de noviembre',
            'lugar_id' => Lugar::where('slug', 'plaza-central')->value('id'),
            'fecha_inicio' => '2026-11-20',
            'fecha_fin' => '2026-11-20',
            'hora_inicio' => '17:00',
            'estado' => 'abierto',
        ]);

        $this->feriante = Feriante::factory()->create([
            'categoria_id' => Categoria::where('nombre', 'Revendedores')->value('id'),
        ]);
    }

    private function inscribir()
    {
        return $this->actingAs($this->feriante, 'feriante')->post('/mi-cuenta/inscripciones', [
            'evento_id' => $this->evento->id,
            'fecha' => '2026-11-20',
            'acepta_reglamento' => true,
        ]);
    }

    private function inscripcion(): Inscripcion
    {
        return Inscripcion::create([
            'evento_id' => $this->evento->id,
            'feriante_id' => $this->feriante->id,
            'fecha' => '2026-11-20',
            'reglamento_aceptado_at' => now(),
        ]);
    }

    public function test_la_inscripcion_no_se_abre_hasta_dos_semanas_antes(): void
    {
        $this->actingAs($this->feriante, 'feriante')
            ->get('/mi-cuenta/inscripciones/nueva')
            ->assertInertia(fn ($page) => $page->has('eventos', 0));

        $this->inscribir()->assertSessionHasErrors('evento_id');
        $this->assertDatabaseCount('inscripciones', 0);
    }

    public function test_desde_dos_semanas_antes_se_inscribe_sin_reconfirmar(): void
    {
        $this->travelTo(Carbon::parse('2026-11-06 08:00'));

        $this->inscribir()->assertRedirect('/gracias');

        $this->assertDatabaseHas('inscripciones', [
            'feriante_id' => $this->feriante->id,
            'confirmacion' => 'sin_confirmar',
        ]);
    }

    public function test_no_puede_reconfirmar_antes_de_las_72_horas(): void
    {
        $inscripcion = $this->inscripcion();
        $this->travelTo(Carbon::parse('2026-11-17 16:59'));

        $this->actingAs($this->feriante, 'feriante')
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/reconfirmar")
            ->assertSessionHasErrors('inscripcion');

        $this->assertSame('sin_confirmar', $inscripcion->fresh()->confirmacion);
    }

    public function test_reconfirma_el_puesto_y_avisa_por_correo(): void
    {
        Notification::fake();
        $inscripcion = $this->inscripcion();
        $this->travelTo(Carbon::parse('2026-11-17 17:00'));

        $this->actingAs($this->feriante, 'feriante')
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/reconfirmar")
            ->assertSessionHas('success');

        $inscripcion->refresh();
        $this->assertSame('confirmada', $inscripcion->confirmacion);
        $this->assertNotNull($inscripcion->reconfirmada_at);
        Notification::assertSentTo($this->feriante, ReconfirmacionRecibida::class);
    }

    public function test_avisa_por_correo_cuando_se_abre_la_reconfirmacion(): void
    {
        Notification::fake();
        $inscripcion = $this->inscripcion();

        // Todavía no se abrió: no se manda nada
        $this->travelTo(Carbon::parse('2026-11-17 16:59'));
        $this->artisan('feria:recordar-reconfirmacion');
        Notification::assertNothingSent();

        // Se abre a las 72 horas: se manda una vez
        $this->travelTo(Carbon::parse('2026-11-17 17:00'));
        $this->artisan('feria:recordar-reconfirmacion');
        Notification::assertSentTo($this->feriante, RecordatorioReconfirmacion::class);
        $this->assertNotNull($inscripcion->fresh()->recordatorio_reconfirmacion_enviado_at);

        // No se vuelve a mandar en una corrida posterior
        Notification::fake();
        $this->artisan('feria:recordar-reconfirmacion');
        Notification::assertNothingSent();
    }

    public function test_no_avisa_por_correo_si_ya_se_reconfirmo(): void
    {
        Notification::fake();
        $inscripcion = $this->inscripcion();
        $inscripcion->update(['confirmacion' => 'confirmada', 'reconfirmada_at' => now()]);

        $this->travelTo(Carbon::parse('2026-11-17 17:00'));
        $this->artisan('feria:recordar-reconfirmacion');

        Notification::assertNothingSent();
    }

    public function test_no_puede_reconfirmar_cuando_la_feria_ya_empezo(): void
    {
        $inscripcion = $this->inscripcion();
        $this->travelTo(Carbon::parse('2026-11-20 17:01'));

        $this->actingAs($this->feriante, 'feriante')
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/reconfirmar")
            ->assertSessionHasErrors('inscripcion');
    }

    public function test_el_coordinador_marca_evento_grande_al_crear_o_editar(): void
    {
        $coordinador = User::factory()->create();

        $this->actingAs($coordinador)->patch("/coordinador/eventos/{$this->evento->id}", [
            'nombre' => $this->evento->nombre,
            'lugar_id' => $this->evento->lugar_id,
            'fecha_inicio' => '2026-11-20',
            'fecha_fin' => '2026-11-20',
            'hora_inicio' => '17:00',
            'estado' => 'abierto',
            'grande' => true,
        ])->assertSessionHasNoErrors();

        $this->assertTrue($this->evento->fresh()->grande);
    }

    public function test_el_coordinador_anula_la_cuenta_con_un_motivo_y_avisa_por_correo(): void
    {
        Notification::fake();
        $coordinador = User::factory()->create();
        $this->inscripcion();

        $this->actingAs($coordinador)
            ->patch("/coordinador/feriantes/{$this->feriante->id}/estado", ['estado' => 'anulado'])
            ->assertSessionHasErrors('motivo_anulacion');

        $this->actingAs($coordinador)
            ->patch("/coordinador/feriantes/{$this->feriante->id}/estado", [
                'estado' => 'anulado',
                'motivo_anulacion' => 'Inasistencia el 20/11',
            ])
            ->assertSessionHasNoErrors();

        $this->feriante->refresh();
        $this->assertTrue($this->feriante->estaAnulado());
        $this->assertSame('Inasistencia el 20/11', $this->feriante->motivo_anulacion);
        $this->assertDatabaseCount('inscripciones', 0);
        Notification::assertSentTo($this->feriante, CuentaAnulada::class);
    }

    public function test_el_feriante_cede_su_lugar_el_mismo_dia_y_avisa_al_coordinador(): void
    {
        $inscripcion = $this->inscripcion();

        $this->travelTo(Carbon::parse('2026-11-19 10:00'));
        $this->actingAs($this->feriante, 'feriante')
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/ceder-lugar")
            ->assertSessionHasErrors('inscripcion');
        $this->assertModelExists($inscripcion);

        $this->travelTo(Carbon::parse('2026-11-20 18:00'));
        $this->actingAs($this->feriante, 'feriante')
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/ceder-lugar")
            ->assertSessionHas('success');

        $this->assertModelMissing($inscripcion);
        $this->assertDatabaseHas('avisos', [
            'mensaje' => "{$this->feriante->nombre} {$this->feriante->apellido} cedió su lugar en {$this->evento->nombre}.",
        ]);
    }

    public function test_una_cuenta_anulada_no_puede_inscribirse(): void
    {
        $this->travelTo(Carbon::parse('2026-11-06 08:00'));
        $this->feriante->update(['estado' => 'anulado']);

        $this->actingAs($this->feriante, 'feriante')
            ->get('/mi-cuenta/inscripciones/nueva')
            ->assertRedirect('/mi-cuenta');

        $this->inscribir()->assertSessionHasErrors('evento_id');
        $this->assertDatabaseCount('inscripciones', 0);
    }
}
