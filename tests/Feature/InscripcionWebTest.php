<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feriante;
use App\Models\Puesto;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Formulario público de inscripción (RF-01, RF-02, RF-07; RN-03, RN-09).
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
            'asistencia_confirmada' => true,
        ], $cambios);
    }

    public function test_inscripcion_reserva_la_mesa_y_deja_la_sesion_iniciada(): void
    {
        $puesto = Puesto::create(['numero' => 1]);

        $this->post('/feriantes', $this->datos(['puesto_id' => $puesto->id]))
            ->assertRedirect('/gracias')
            ->assertSessionHas('whatsapp');

        $feriante = Feriante::where('email', 'ana@example.com')->firstOrFail();
        $this->assertSame('reservado', $puesto->fresh()->estado);
        $this->assertSame($feriante->id, $puesto->fresh()->feriante_id);
        $this->assertAuthenticatedAs($feriante, 'feriante');
    }

    public function test_no_se_puede_reservar_una_mesa_ya_reservada(): void
    {
        $puesto = Puesto::create(['numero' => 1, 'estado' => 'reservado']);

        $this->post('/feriantes', $this->datos(['puesto_id' => $puesto->id]))
            ->assertSessionHasErrors('puesto_id');

        // Todo o nada: el feriante no queda guardado sin mesa
        $this->assertDatabaseMissing('feriantes', ['email' => 'ana@example.com']);
    }

    public function test_las_categorias_con_mesa_tienen_que_elegir_puesto(): void
    {
        $this->post('/feriantes', $this->datos())
            ->assertSessionHasErrors('puesto_id');
    }

    public function test_el_revendedor_se_inscribe_sin_mesa_aunque_mande_un_puesto(): void
    {
        $puesto = Puesto::create(['numero' => 1]);
        $revendedores = Categoria::where('nombre', 'Revendedores')->value('id');

        $this->post('/feriantes', $this->datos(['categoria_id' => $revendedores, 'puesto_id' => $puesto->id]))
            ->assertRedirect('/gracias');

        $this->assertSame('libre', $puesto->fresh()->estado);
        $this->assertNull($puesto->fresh()->feriante_id);
    }

    public function test_la_categoria_otro_exige_describir_el_rubro(): void
    {
        $puesto = Puesto::create(['numero' => 1]);
        $otro = Categoria::idOtro();

        $this->post('/feriantes', $this->datos(['categoria_id' => $otro, 'puesto_id' => $puesto->id]))
            ->assertSessionHasErrors('rubro_otro');
    }

    public function test_no_se_repite_el_email(): void
    {
        Feriante::factory()->create(['email' => 'ana@example.com']);
        $puesto = Puesto::create(['numero' => 1]);

        $this->post('/feriantes', $this->datos(['puesto_id' => $puesto->id]))
            ->assertSessionHasErrors('email');
    }
}
