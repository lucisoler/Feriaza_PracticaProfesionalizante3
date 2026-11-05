<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\User;
use App\Notifications\AnulacionInscripcion;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\LugarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tolerancia de 1 hora para presentarse y veto por inasistencia en un
 * evento grande (RN-07, RN-08).
 */
class ToleranciaYVetoTest extends TestCase
{
    use RefreshDatabase;

    private Evento $evento;

    private Feriante $feriante;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategoriaSeeder::class, LugarSeeder::class]);

        $this->evento = Evento::create([
            'nombre' => 'Feria de noviembre',
            'lugar_id' => Lugar::where('slug', 'plaza-central')->value('id'),
            'fecha_inicio' => '2026-11-20',
            'fecha_fin' => '2026-11-20',
            'hora_inicio' => '17:00',
            'estado' => 'abierto',
            'grande' => true,
        ]);

        $this->feriante = Feriante::factory()->create([
            'categoria_id' => Categoria::where('nombre', 'Revendedores')->value('id'),
        ]);
    }

    private function inscripcion(): Inscripcion
    {
        return Inscripcion::create([
            'evento_id' => $this->evento->id,
            'feriante_id' => $this->feriante->id,
            'fecha' => '2026-11-20',
            'confirmacion' => 'confirmada',
            'reconfirmada_at' => now(),
            'reglamento_aceptado_at' => now(),
        ]);
    }

    public function test_el_coordinador_marca_presente_dentro_de_la_tolerancia(): void
    {
        $coordinador = User::factory()->create();
        $inscripcion = $this->inscripcion();

        $this->travelTo(Carbon::parse('2026-11-20 17:40'));
        $this->actingAs($coordinador)
            ->patch("/coordinador/inscripciones/{$inscripcion->id}/presente")
            ->assertSessionHasNoErrors();

        $inscripcion->refresh();
        $this->assertSame('presente', $inscripcion->asistencia);
        $this->assertNotNull($inscripcion->presente_at);

        $this->travelTo(Carbon::parse('2026-11-20 18:30'));
        $this->artisan('feria:aplicar-tolerancia');

        $this->assertSame('presente', $inscripcion->fresh()->asistencia);
        $this->assertFalse($this->feriante->fresh()->estaVetado());
    }

    public function test_no_se_puede_marcar_presente_fuera_de_la_tolerancia(): void
    {
        $coordinador = User::factory()->create();
        $inscripcion = $this->inscripcion();

        $this->travelTo(Carbon::parse('2026-11-20 18:05'));
        $this->actingAs($coordinador)
            ->patch("/coordinador/inscripciones/{$inscripcion->id}/presente")
            ->assertSessionHasErrors('inscripcion');

        $this->assertSame('pendiente', $inscripcion->fresh()->asistencia);
    }

    public function test_vencida_la_tolerancia_sin_marcar_se_anula_y_se_veta_en_evento_grande(): void
    {
        Notification::fake();
        $inscripcion = $this->inscripcion();

        $this->travelTo(Carbon::parse('2026-11-20 17:59'));
        $this->artisan('feria:aplicar-tolerancia');
        $this->assertSame('pendiente', $inscripcion->fresh()->asistencia);

        $this->travelTo(Carbon::parse('2026-11-20 18:01'));
        $this->artisan('feria:aplicar-tolerancia');

        $inscripcion->refresh();
        $this->assertSame('ausente', $inscripcion->asistencia);
        $this->assertSame('cancelada', $inscripcion->estado);
        $this->assertNull($inscripcion->puesto_id);
        $this->assertDatabaseHas('sanciones', [
            'inscripcion_id' => $inscripcion->id,
            'registrada_por' => null,
        ]);
        $this->assertDatabaseHas('avisos', [
            'mensaje' => "{$this->feriante->nombre} {$this->feriante->apellido} no se presentó dentro de la tolerancia en {$this->evento->nombre}: se anuló su inscripción y queda vedado por 3 eventos.",
        ]);
        Notification::assertSentTo($this->feriante, AnulacionInscripcion::class);

        $this->feriante->refresh();
        $this->assertTrue($this->feriante->estaVetado());
    }

    public function test_no_se_veta_si_el_evento_no_es_grande(): void
    {
        $this->evento->update(['grande' => false]);
        $inscripcion = $this->inscripcion();

        $this->travelTo(Carbon::parse('2026-11-20 18:01'));
        $this->artisan('feria:aplicar-tolerancia');

        $this->assertSame('ausente', $inscripcion->fresh()->asistencia);
        $this->assertFalse($this->feriante->fresh()->estaVetado());
    }

    public function test_un_feriante_vetado_no_puede_inscribirse_a_un_evento_nuevo(): void
    {
        $this->inscripcion();
        $this->travelTo(Carbon::parse('2026-11-20 18:01'));
        $this->artisan('feria:aplicar-tolerancia');
        $this->assertTrue($this->feriante->fresh()->estaVetado());

        $otroEvento = Evento::create([
            'nombre' => 'Feria de diciembre',
            'lugar_id' => $this->evento->lugar_id,
            'fecha_inicio' => '2026-12-04',
            'fecha_fin' => '2026-12-04',
            'hora_inicio' => '17:00',
            'estado' => 'abierto',
        ]);

        $this->actingAs($this->feriante->fresh(), 'feriante')->post('/mi-cuenta/inscripciones', [
            'evento_id' => $otroEvento->id,
            'fecha' => '2026-12-04',
            'acepta_reglamento' => true,
        ])->assertSessionHasErrors('evento_id');

        $this->assertDatabaseCount('inscripciones', 1);
    }

    public function test_el_veto_se_levanta_solo_despues_de_3_eventos_reales(): void
    {
        $this->inscripcion();
        $this->travelTo(Carbon::parse('2026-11-20 18:01'));
        $this->artisan('feria:aplicar-tolerancia');
        $feriante = $this->feriante->fresh();
        $this->assertTrue($feriante->estaVetado());

        foreach (['2026-12-01', '2026-12-08', '2026-12-15'] as $i => $fecha) {
            Evento::create([
                'nombre' => "Feria extra {$i}",
                'lugar_id' => $this->evento->lugar_id,
                'fecha_inicio' => $fecha,
                'fecha_fin' => $fecha,
                'hora_inicio' => '17:00',
                'estado' => 'abierto',
            ]);
        }

        $this->assertFalse($feriante->fresh()->estaVetado());
    }
}
