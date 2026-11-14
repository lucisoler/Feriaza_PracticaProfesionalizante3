<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\Puesto;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\LugarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El coordinador acepta o rechaza la inscripción de un feriante a un evento (RF-10).
 */
class AceptarInscripcionEventoTest extends TestCase
{
    use RefreshDatabase;

    private Inscripcion $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategoriaSeeder::class, LugarSeeder::class]);

        $evento = Evento::create([
            'nombre' => 'Feria de noviembre',
            'lugar_id' => Lugar::where('slug', 'plaza-central')->value('id'),
            'fecha_inicio' => '2026-11-20',
            'fecha_fin' => '2026-11-20',
            'hora_inicio' => '17:00',
            'estado' => 'abierto',
        ]);

        $feriante = Feriante::factory()->create([
            'categoria_id' => Categoria::where('nombre', 'Revendedores')->value('id'),
        ]);

        $puesto = Puesto::create([
            'lugar_id' => $evento->lugar_id,
            'numero' => 1,
            'habilitado' => true,
        ]);

        $this->inscripcion = Inscripcion::create([
            'evento_id' => $evento->id,
            'feriante_id' => $feriante->id,
            'puesto_id' => $puesto->id,
            'fecha' => '2026-11-20',
            'reglamento_aceptado_at' => now(),
        ]);
    }

    public function test_el_coordinador_acepta_la_inscripcion_de_un_feriante(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch("/coordinador/inscripciones/{$this->inscripcion->id}/estado", ['estado' => 'aprobada'])
            ->assertSessionHas('success');

        $this->assertSame('aprobada', $this->inscripcion->fresh()->estado);
        $this->assertNotNull($this->inscripcion->fresh()->puesto_id);
    }

    public function test_al_rechazar_la_inscripcion_se_libera_la_mesa(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch("/coordinador/inscripciones/{$this->inscripcion->id}/estado", ['estado' => 'rechazada']);

        $this->assertSame('rechazada', $this->inscripcion->fresh()->estado);
        $this->assertNull($this->inscripcion->fresh()->puesto_id);
    }

    public function test_no_acepta_un_estado_que_no_corresponde(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch("/coordinador/inscripciones/{$this->inscripcion->id}/estado", ['estado' => 'cancelada'])
            ->assertSessionHasErrors('estado');

        $this->assertSame('pendiente', $this->inscripcion->fresh()->estado);
    }
}
