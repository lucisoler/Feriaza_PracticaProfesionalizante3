<?php

namespace Tests\Feature;

use App\Models\Feriante;
use App\Models\Puesto;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cuenta propia del feriante desde la web (RF-04).
 */
class MiCuentaWebTest extends TestCase
{
    use RefreshDatabase;

    private Feriante $feriante;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
        $this->feriante = Feriante::factory()->create();
    }

    public function test_el_feriante_ve_su_cuenta(): void
    {
        $this->actingAs($this->feriante, 'feriante')
            ->get('/mi-cuenta')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Feriante/MiCuenta')
                ->where('feriante.id', $this->feriante->id));
    }

    public function test_el_feriante_actualiza_sus_datos(): void
    {
        $this->actingAs($this->feriante, 'feriante')
            ->from('/mi-cuenta')
            ->patch('/mi-cuenta', [
                'nombre' => 'Nuevo',
                'apellido' => $this->feriante->apellido,
                'categoria_id' => $this->feriante->categoria_id,
                'telefono' => '3624111111',
            ])
            ->assertRedirect('/mi-cuenta')
            ->assertSessionHas('success');

        $this->assertSame('Nuevo', $this->feriante->fresh()->nombre);
    }

    public function test_la_baja_libera_la_mesa_y_cierra_la_sesion(): void
    {
        $puesto = Puesto::create(['numero' => 1, 'estado' => 'reservado', 'feriante_id' => $this->feriante->id]);

        $this->actingAs($this->feriante, 'feriante')
            ->delete('/mi-cuenta')
            ->assertRedirect('/');

        $this->assertModelMissing($this->feriante);
        $this->assertSame('libre', $puesto->fresh()->estado);
        $this->assertGuest('feriante');
    }
}
