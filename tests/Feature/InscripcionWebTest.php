<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feriante;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registro público del feriante con email y contraseña (RF-01, RF-02).
 * La mesa ya no se elige acá sino al inscribirse a un evento (InscripcionEventoWebTest).
 */
class InscripcionWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
    }

    private function datos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Ana',
            'apellido' => 'Gómez',
            'email' => 'ana@example.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'categoria_id' => Categoria::where('nombre', 'Artesanos')->value('id'),
            'telefono' => '3624000000',
        ], $cambios);
    }

    public function test_la_vista_del_feriante_empieza_por_el_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Feriante/Login'));
    }

    public function test_con_sesion_iniciada_el_inicio_lleva_a_mi_cuenta(): void
    {
        $feriante = Feriante::factory()->create();

        $this->actingAs($feriante, 'feriante')->get('/')->assertRedirect('/mi-cuenta');
    }

    public function test_el_formulario_de_registro_esta_en_su_propia_pagina(): void
    {
        $this->get('/registro')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Feriante/Registro')->where('social', null));
    }

    public function test_el_registro_crea_la_cuenta_y_lleva_a_elegir_evento(): void
    {
        $this->post('/feriantes', $this->datos())
            ->assertRedirect('/mi-cuenta/inscripciones/nueva');

        $feriante = Feriante::where('email', 'ana@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($feriante, 'feriante');
    }

    public function test_la_categoria_otro_exige_describir_el_rubro(): void
    {
        $this->post('/feriantes', $this->datos(['categoria_id' => Categoria::idOtro()]))
            ->assertSessionHasErrors('rubro_otro');
    }

    public function test_no_se_repite_el_email(): void
    {
        Feriante::factory()->create(['email' => 'ana@example.com']);

        $this->post('/feriantes', $this->datos())
            ->assertSessionHasErrors('email');
    }
}
