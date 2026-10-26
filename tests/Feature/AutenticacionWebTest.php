<?php

namespace Tests\Feature;

use App\Models\Feriante;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Inicio y cierre de sesión en la web, con un guard por rol (RF-03).
 */
class AutenticacionWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_coordinador_inicia_sesion_con_datos_correctos(): void
    {
        $coordinador = User::factory()->create(['password' => 'Coordinador123']);

        $this->post('/login', ['email' => $coordinador->email, 'password' => 'Coordinador123'])
            ->assertRedirect('/coordinador');

        $this->assertAuthenticatedAs($coordinador);
    }

    public function test_el_coordinador_no_entra_con_contrasena_incorrecta(): void
    {
        $coordinador = User::factory()->create();

        $this->post('/login', ['email' => $coordinador->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_el_coordinador_cierra_sesion(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_el_feriante_inicia_y_cierra_sesion(): void
    {
        $this->seed(CategoriaSeeder::class);
        $feriante = Feriante::factory()->create(['password' => 'Feriante123']);

        $this->post('/mi-cuenta/login', ['email' => $feriante->email, 'password' => 'Feriante123'])
            ->assertRedirect('/mi-cuenta');
        $this->assertAuthenticatedAs($feriante, 'feriante');

        $this->post('/mi-cuenta/logout')->assertRedirect('/');
        $this->assertGuest('feriante');
    }

    public function test_cada_rol_es_redirigido_a_su_login(): void
    {
        $this->get('/coordinador')->assertRedirect('/login');
        $this->get('/mi-cuenta')->assertRedirect('/mi-cuenta/login');
    }

    public function test_un_feriante_no_entra_al_panel_del_coordinador(): void
    {
        $this->seed(CategoriaSeeder::class);
        $feriante = Feriante::factory()->create(['password' => 'Feriante123']);

        // Se entra por el login real: actingAs() cambiaría el guard por defecto y daría un falso positivo
        $this->post('/mi-cuenta/login', ['email' => $feriante->email, 'password' => 'Feriante123']);

        $this->get('/coordinador')->assertRedirect('/login');
    }
}
