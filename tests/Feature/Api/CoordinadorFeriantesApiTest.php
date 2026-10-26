<?php

namespace Tests\Feature\Api;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Sancion;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Gestión de feriantes por el coordinador desde la API (RF-13, RF-14).
 */
class CoordinadorFeriantesApiTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
        $this->coordinador = User::factory()->create();
        Sanctum::actingAs($this->coordinador, ['coordinador']);
    }

    private function evento(string $estado): Evento
    {
        return Evento::create([
            'nombre' => 'Feria de prueba',
            'fecha_inicio' => '2026-11-07',
            'fecha_fin' => '2026-11-08',
            'hora_inicio' => '17:00',
            'estado' => $estado,
        ]);
    }

    public function test_buscar_feriantes_por_texto(): void
    {
        Feriante::factory()->create(['nombre' => 'Rosa', 'nombre_emprendimiento' => 'Tejidos Rosa']);
        Feriante::factory()->create(['nombre' => 'Mario', 'nombre_emprendimiento' => 'Panadería']);

        $this->getJson('/api/v1/coordinador/feriantes?q=tejidos')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Rosa');
    }

    public function test_crear_un_feriante(): void
    {
        $this->postJson('/api/v1/coordinador/feriantes', [
            'nombre' => 'Luis',
            'apellido' => 'Pérez',
            'email' => 'luis@example.com',
            'password' => 'secreto123',
            'telefono' => '3624222222',
            'categoria_id' => Categoria::where('nombre', 'Masas')->value('id'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'luis@example.com');
    }

    public function test_actualizar_el_estado(): void
    {
        $feriante = Feriante::factory()->create();

        $this->patchJson("/api/v1/coordinador/feriantes/{$feriante->id}", ['estado' => 'aprobado'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'aprobado');
    }

    public function test_feriante_inexistente_responde_404(): void
    {
        $this->getJson('/api/v1/coordinador/feriantes/9999')->assertNotFound();
    }

    public function test_no_se_elimina_un_feriante_con_eventos_abiertos(): void
    {
        $feriante = Feriante::factory()->create();
        Inscripcion::create([
            'evento_id' => $this->evento('abierto')->id,
            'feriante_id' => $feriante->id,
            'reglamento_aceptado_at' => now(),
        ]);

        $this->deleteJson("/api/v1/coordinador/feriantes/{$feriante->id}")->assertStatus(409);

        $this->assertModelExists($feriante);
    }

    public function test_eliminar_un_feriante_sin_eventos_abiertos(): void
    {
        $feriante = Feriante::factory()->create();

        $this->deleteJson("/api/v1/coordinador/feriantes/{$feriante->id}")->assertOk();

        $this->assertModelMissing($feriante);
    }

    public function test_historial_resume_asistencia_y_sanciones(): void
    {
        $feriante = Feriante::factory()->create();
        Inscripcion::create([
            'evento_id' => $this->evento('cerrado')->id,
            'feriante_id' => $feriante->id,
            'asistencia' => 'presente',
            'reglamento_aceptado_at' => now(),
        ]);
        $ausente = Inscripcion::create([
            'evento_id' => $this->evento('cerrado')->id,
            'feriante_id' => $feriante->id,
            'asistencia' => 'ausente',
            'reglamento_aceptado_at' => now(),
        ]);
        Sancion::create([
            'inscripcion_id' => $ausente->id,
            'registrada_por' => $this->coordinador->id,
            'motivo' => 'No se presentó',
        ]);

        $this->getJson("/api/v1/coordinador/feriantes/{$feriante->id}/historial")
            ->assertOk()
            ->assertJsonPath('resumen.participaciones', 2)
            ->assertJsonPath('resumen.presentes', 1)
            ->assertJsonPath('resumen.ausentes', 1)
            ->assertJsonPath('resumen.sanciones', 1);
    }
}
