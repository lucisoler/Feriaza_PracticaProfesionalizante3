<?php

namespace Tests\Feature;

use App\Models\Feriante;
use App\Notifications\RestablecerContrasena;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * "¿Olvidaste tu contraseña?" del feriante: enlace por correo y contraseña nueva.
 */
class RecuperarContrasenaTest extends TestCase
{
    use RefreshDatabase;

    private Feriante $feriante;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
        $this->feriante = Feriante::factory()->create(['email' => 'ana@gmail.com']);
    }

    public function test_muestra_el_formulario(): void
    {
        $this->get('/mi-cuenta/olvide-contrasena')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Feriante/OlvideContrasena'));
    }

    public function test_manda_el_enlace_por_correo(): void
    {
        Notification::fake();

        $this->post('/mi-cuenta/olvide-contrasena', ['email' => 'ana@gmail.com'])
            ->assertSessionHas('success');

        Notification::assertSentTo($this->feriante, RestablecerContrasena::class);
    }

    public function test_no_revela_si_el_email_no_tiene_cuenta(): void
    {
        Notification::fake();

        $this->post('/mi-cuenta/olvide-contrasena', ['email' => 'nadie@gmail.com'])
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_con_el_enlace_crea_una_contrasena_nueva_y_entra(): void
    {
        Notification::fake();
        $this->post('/mi-cuenta/olvide-contrasena', ['email' => 'ana@gmail.com']);

        $token = null;
        Notification::assertSentTo($this->feriante, RestablecerContrasena::class, function ($n) use (&$token) {
            $token = $n->token;

            return str_contains($n->toMail($this->feriante)->actionUrl, "/mi-cuenta/restablecer/{$n->token}");
        });

        $this->post('/mi-cuenta/restablecer', [
            'token' => $token,
            'email' => 'ana@gmail.com',
            'password' => 'nueva-clave-1',
            'password_confirmation' => 'nueva-clave-1',
        ])->assertRedirect('/mi-cuenta/login');

        $this->assertTrue(Hash::check('nueva-clave-1', $this->feriante->fresh()->password));

        $this->post('/mi-cuenta/login', ['email' => 'ana@gmail.com', 'password' => 'nueva-clave-1'])
            ->assertRedirect('/mi-cuenta');
    }

    public function test_un_token_invalido_no_cambia_la_contrasena(): void
    {
        $this->post('/mi-cuenta/restablecer', [
            'token' => 'cualquiera',
            'email' => 'ana@gmail.com',
            'password' => 'nueva-clave-1',
            'password_confirmation' => 'nueva-clave-1',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $this->feriante->fresh()->password));
    }
}
