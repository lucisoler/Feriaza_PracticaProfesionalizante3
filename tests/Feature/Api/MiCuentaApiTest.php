<?php

namespace Tests\Feature\Api;

use App\Models\Feriante;
use App\Models\Puesto;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Cuenta propia del feriante por la API (RF-04).
 */
class MiCuentaApiTest extends TestCase
{
    use RefreshDatabase;

    private Feriante $feriante;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
        $this->feriante = Feriante::factory()->create();
        Sanctum::actingAs($this->feriante, ['feriante']);
    }

    public function test_ver_mi_cuenta(): void
    {
        $this->getJson('/api/v1/mi-cuenta')
            ->assertOk()
            ->assertJsonPath('data.id', $this->feriante->id)
            ->assertJsonMissingPath('data.password');
    }

    public function test_actualizar_solo_los_campos_enviados(): void
    {
        $apellido = $this->feriante->apellido;

        $this->patchJson('/api/v1/mi-cuenta', ['telefono' => '3624999999'])
            ->assertOk()
            ->assertJsonPath('data.telefono', '3624999999')
            ->assertJsonPath('data.apellido', $apellido);
    }

    public function test_no_puede_usar_el_email_de_otro_feriante(): void
    {
        $otro = Feriante::factory()->create();

        $this->patchJson('/api/v1/mi-cuenta', ['email' => $otro->email])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_la_baja_libera_la_mesa(): void
    {
        $puesto = Puesto::create(['numero' => 2, 'estado' => 'reservado', 'feriante_id' => $this->feriante->id]);

        $this->deleteJson('/api/v1/mi-cuenta')->assertOk();

        $this->assertModelMissing($this->feriante);
        $this->assertSame('libre', $puesto->fresh()->estado);
    }
}
