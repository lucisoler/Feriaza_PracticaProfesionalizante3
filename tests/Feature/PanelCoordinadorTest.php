<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feriante;
use App\Models\Puesto;
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

    public function test_el_coordinador_crea_un_feriante_con_mesa(): void
    {
        $puesto = Puesto::create(['numero' => 5]);

        $this->actingAs($this->coordinador)
            ->post('/coordinador/feriantes', [
                'nombre' => 'Luis',
                'apellido' => 'Pérez',
                'email' => 'luis@example.com',
                'password' => 'secreto123',
                'categoria_id' => Categoria::where('nombre', 'Masas')->value('id'),
                'telefono' => '3624222222',
                'puesto_id' => $puesto->id,
            ])
            ->assertRedirect('/coordinador');

        $feriante = Feriante::where('email', 'luis@example.com')->firstOrFail();
        $this->assertSame($feriante->id, $puesto->fresh()->feriante_id);
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

    public function test_eliminar_un_feriante_libera_su_mesa(): void
    {
        $feriante = Feriante::factory()->create();
        $puesto = Puesto::create(['numero' => 3, 'estado' => 'reservado', 'feriante_id' => $feriante->id]);

        $this->actingAs($this->coordinador)
            ->from('/coordinador')
            ->delete("/coordinador/feriantes/{$feriante->id}")
            ->assertRedirect('/coordinador');

        $this->assertModelMissing($feriante);
        $this->assertSame('libre', $puesto->fresh()->estado);
    }

    public function test_el_coordinador_libera_una_mesa(): void
    {
        $feriante = Feriante::factory()->create();
        $puesto = Puesto::create(['numero' => 7, 'estado' => 'reservado', 'feriante_id' => $feriante->id]);

        $this->actingAs($this->coordinador)
            ->from('/coordinador')
            ->patch("/coordinador/puestos/{$puesto->id}/liberar")
            ->assertRedirect('/coordinador');

        $this->assertSame('libre', $puesto->fresh()->estado);
        $this->assertNull($puesto->fresh()->feriante_id);
    }
}
