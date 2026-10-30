<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feriante;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as UsuarioSocial;
use Tests\TestCase;

/**
 * Crear cuenta o entrar con Google / Facebook (RF-01, RF-03).
 */
class CuentaSocialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
    }

    private function simularProveedor(string $id, ?string $email, string $nombre = 'Ana Gómez'): void
    {
        $usuario = (new UsuarioSocial())->map(['id' => $id, 'email' => $email, 'name' => $nombre]);
        $usuario->setRaw([]);

        Socialite::shouldReceive('driver->user')->andReturn($usuario);
    }

    public function test_sin_credenciales_configuradas_vuelve_al_login_con_un_aviso(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google/redirect')
            ->assertRedirect('/')
            ->assertSessionHasErrors('social');
    }

    public function test_solo_acepta_google_y_facebook(): void
    {
        $this->get('/auth/twitter/redirect')->assertNotFound();
    }

    public function test_un_usuario_nuevo_completa_sus_datos_antes_de_crear_la_cuenta(): void
    {
        $this->simularProveedor('g-123', 'ana@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect('/registro/social');

        $this->get('/registro/social')
            ->assertInertia(fn ($page) => $page
                ->component('Feriante/Registro')
                ->where('social.email', 'ana@gmail.com')
                ->where('social.nombre', 'Ana')
                ->where('social.apellido', 'Gómez'));

        $this->post('/registro/social', [
            'nombre' => 'Ana',
            'apellido' => 'Gómez',
            'telefono' => '3644000000',
            'categoria_id' => Categoria::where('nombre', 'Masas')->value('id'),
        ])->assertRedirect('/mi-cuenta/inscripciones/nueva');

        $feriante = Feriante::where('email', 'ana@gmail.com')->firstOrFail();
        $this->assertSame('google', $feriante->proveedor);
        $this->assertSame('g-123', $feriante->proveedor_id);
        $this->assertNull($feriante->password);
        $this->assertAuthenticatedAs($feriante, 'feriante');
    }

    public function test_si_ya_tiene_cuenta_con_ese_email_entra_y_queda_vinculada(): void
    {
        $feriante = Feriante::factory()->create(['email' => 'ana@gmail.com']);
        $this->simularProveedor('fb-9', 'ana@gmail.com');

        $this->get('/auth/facebook/callback')->assertRedirect('/mi-cuenta');

        $this->assertAuthenticatedAs($feriante, 'feriante');
        $this->assertSame('facebook', $feriante->fresh()->proveedor);
    }

    public function test_si_el_proveedor_no_da_el_email_lo_pide(): void
    {
        $this->simularProveedor('fb-10', null);
        $this->get('/auth/facebook/callback');

        $this->post('/registro/social', [
            'nombre' => 'Ana',
            'apellido' => 'Gómez',
            'telefono' => '3644000000',
            'categoria_id' => Categoria::where('nombre', 'Masas')->value('id'),
        ])->assertSessionHasErrors('email');
    }

    public function test_sin_datos_del_proveedor_no_se_puede_completar_el_registro_social(): void
    {
        $this->get('/registro/social')->assertRedirect('/');
        $this->post('/registro/social', [])->assertRedirect('/');
    }
}
