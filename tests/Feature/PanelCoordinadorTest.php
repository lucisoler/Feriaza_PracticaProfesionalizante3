<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Panel web del coordinador: gestión de feriantes y mesas (RF-13).
 */
class PanelCoordinadorTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
        $this->coordinador = User::factory()->create();
    }

    public function test_el_panel_lista_los_feriantes(): void
    {
        Feriante::factory()->count(2)->create();

        $this->actingAs($this->coordinador)
            ->get('/coordinador')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Coordinador')->has('feriantes', 2));
    }

    public function test_el_coordinador_crea_un_feriante(): void
    {
        $this->actingAs($this->coordinador)
            ->post('/coordinador/feriantes', [
                'nombre' => 'Luis',
                'apellido' => 'Pérez',
                'email' => 'luis@example.com',
                'password' => 'secreto123',
                'categoria_id' => Categoria::where('nombre', 'Masas')->value('id'),
                'telefono' => '3624222222',
            ])
            ->assertRedirect('/coordinador');

        $this->assertNotNull(Feriante::where('email', 'luis@example.com')->first());
    }

    public function test_el_coordinador_cambia_el_estado_de_un_feriante(): void
    {
        $feriante = Feriante::factory()->create();

        $this->actingAs($this->coordinador)
            ->from('/coordinador')
            ->patch("/coordinador/feriantes/{$feriante->id}/estado", ['estado' => 'aprobado'])
            ->assertRedirect('/coordinador');

        $this->assertSame('aprobado', $feriante->fresh()->estado);
    }

    public function test_el_estado_solo_acepta_valores_validos(): void
    {
        $feriante = Feriante::factory()->create();

        $this->actingAs($this->coordinador)
            ->patch("/coordinador/feriantes/{$feriante->id}/estado", ['estado' => 'cualquiera'])
            ->assertSessionHasErrors('estado');
    }

    public function test_eliminar_un_feriante(): void
    {
        $feriante = Feriante::factory()->create();

        $this->actingAs($this->coordinador)
            ->from('/coordinador')
            ->delete("/coordinador/feriantes/{$feriante->id}")
            ->assertRedirect('/coordinador');

        $this->assertModelMissing($feriante);
    }

    public function test_no_se_elimina_un_feriante_con_eventos_abiertos(): void
    {
        $feriante = Feriante::factory()->create();
        $evento = Evento::create([
            'nombre' => 'Feria de prueba',
            'fecha_inicio' => '2026-11-07',
            'fecha_fin' => '2026-11-08',
            'hora_inicio' => '17:00',
            'estado' => 'abierto',
        ]);
        Inscripcion::create([
            'evento_id' => $evento->id,
            'feriante_id' => $feriante->id,
            'reglamento_aceptado_at' => now(),
        ]);

        $this->actingAs($this->coordinador)
            ->from('/coordinador')
            ->delete("/coordinador/feriantes/{$feriante->id}")
            ->assertRedirect('/coordinador')
            ->assertSessionHasErrors('feriante');

        $this->assertModelExists($feriante);
    }
}
