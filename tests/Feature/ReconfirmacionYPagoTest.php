<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\LugarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/reconfirmar", ['medio_pago' => 'efectivo'])
            ->assertSessionHasErrors('inscripcion');

        $this->assertSame('sin_confirmar', $inscripcion->fresh()->confirmacion);
    }

    public function test_reconfirma_el_puesto_eligiendo_el_medio_de_pago(): void
    {
        $inscripcion = $this->inscripcion();
        $this->travelTo(Carbon::parse('2026-11-17 17:00'));

        $this->actingAs($this->feriante, 'feriante')
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/reconfirmar", [])
            ->assertSessionHasErrors('medio_pago');

        $this->actingAs($this->feriante, 'feriante')
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/reconfirmar", ['medio_pago' => 'debito'])
            ->assertSessionHas('success');

        $inscripcion->refresh();
        $this->assertSame('confirmada', $inscripcion->confirmacion);
        $this->assertSame('debito', $inscripcion->medio_pago);
        $this->assertNotNull($inscripcion->reconfirmada_at);
    }

    public function test_no_puede_reconfirmar_cuando_la_feria_ya_empezo(): void
    {
        $inscripcion = $this->inscripcion();
        $this->travelTo(Carbon::parse('2026-11-20 17:01'));

        $this->actingAs($this->feriante, 'feriante')
            ->patch("/mi-cuenta/inscripciones/{$inscripcion->id}/reconfirmar", ['medio_pago' => 'efectivo'])
            ->assertSessionHasErrors('inscripcion');
    }

    public function test_el_coordinador_fija_el_dia_de_pago_y_registra_el_pago(): void
    {
        $coordinador = User::factory()->create();
        $inscripcion = $this->inscripcion();

        $this->actingAs($coordinador)->patch("/coordinador/eventos/{$this->evento->id}", [
            'nombre' => $this->evento->nombre,
            'lugar_id' => $this->evento->lugar_id,
            'fecha_inicio' => '2026-11-20',
            'fecha_fin' => '2026-11-20',
            'hora_inicio' => '17:00',
            'pago_fecha' => '2026-11-20',
            'pago_hora' => '15:30',
            'estado' => 'abierto',
        ])->assertSessionHasNoErrors();

        $this->assertSame('2026-11-20', $this->evento->fresh()->pago_fecha->toDateString());

        $this->actingAs($coordinador)
            ->patch("/coordinador/inscripciones/{$inscripcion->id}/pago", ['pagado' => true])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($inscripcion->fresh()->pagado_at);
    }

    public function test_el_coordinador_anula_la_cuenta_con_un_motivo_y_se_liberan_sus_reservas(): void
    {
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
