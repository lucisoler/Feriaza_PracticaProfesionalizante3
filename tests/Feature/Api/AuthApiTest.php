<?php

namespace Tests\Feature\Api;

use App\Models\Categoria;
use App\Models\Feriante;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Autenticación de la API con Sanctum y habilidades por rol (RF-01 a RF-04).
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
    }

    public function test_las_categorias_son_publicas(): void
    {
        $this->getJson('/api/v1/categorias')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.nombre', 'Artesanos');
    }

    public function test_registro_de_feriante_devuelve_un_token(): void
    {
        $this->postJson('/api/v1/feriantes/registro', [
            'nombre' => 'Ana',
            'apellido' => 'Gómez',
            'email' => 'ana@example.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'telefono' => '3624000000',
            'categoria_id' => Categoria::where('nombre', 'Masas')->value('id'),
        ])
            ->assertCreated()
            ->assertJsonStructure(['feriante' => ['id', 'email'], 'token'])
            ->assertJsonMissingPath('feriante.password');
    }

    public function test_registro_invalido_responde_422_en_json(): void
    {
        $this->postJson('/api/v1/feriantes/registro', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'email', 'password', 'categoria_id']);
    }

    public function test_login_del_feriante(): void
    {
        $feriante = Feriante::factory()->create(['password' => 'Feriante123']);

        $this->postJson('/api/v1/feriantes/login', ['email' => $feriante->email, 'password' => 'Feriante123'])
            ->assertOk()
            ->assertJsonStructure(['token']);

        $this->postJson('/api/v1/feriantes/login', ['email' => $feriante->email, 'password' => 'otra'])
            ->assertUnprocessable();
    }

    public function test_login_del_coordinador(): void
    {
        $coordinador = User::factory()->create(['password' => 'Coordinador123']);

        $this->postJson('/api/v1/coordinador/login', ['email' => $coordinador->email, 'password' => 'Coordinador123'])
            ->assertOk()
            ->assertJsonPath('coordinador.email', $coordinador->email)
            ->assertJsonMissingPath('coordinador.password');
    }

    public function test_sin_token_responde_401(): void
    {
        $this->getJson('/api/v1/mi-cuenta')->assertUnauthorized();
        $this->getJson('/api/v1/coordinador/feriantes')->assertUnauthorized();
    }

    public function test_un_token_de_feriante_no_accede_a_rutas_del_coordinador(): void
    {
        $token = Feriante::factory()->create()->createToken('feriante', ['feriante'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/coordinador/feriantes')->assertForbidden();
    }

    public function test_un_token_de_coordinador_no_accede_a_mi_cuenta(): void
    {
        $token = User::factory()->create()->createToken('coordinador', ['coordinador'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/mi-cuenta')->assertForbidden();
    }

    public function test_logout_revoca_el_token(): void
    {
        $feriante = Feriante::factory()->create();
        $token = $feriante->createToken('feriante', ['feriante'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();

        $this->assertSame(0, $feriante->tokens()->count());
    }

    public function test_el_login_limita_los_intentos(): void
    {
        foreach (range(1, 6) as $intento) {
            $this->postJson('/api/v1/feriantes/login', ['email' => 'x@example.com', 'password' => 'mal']);
        }

        $this->postJson('/api/v1/feriantes/login', ['email' => 'x@example.com', 'password' => 'mal'])
            ->assertTooManyRequests();
    }
}
