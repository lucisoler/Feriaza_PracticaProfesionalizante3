<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Planilla PDF de los feriantes inscriptos en un día del evento (panel del coordinador).
 */
class PlanillaInscriptosTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinador;

    private Evento $evento;

    private string $diaUno;

    private string $diaDos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
        $this->coordinador = User::factory()->create();

        $lugar = Lugar::where('slug', 'plaza-central')->firstOrFail();
        $this->evento = Evento::create([
            'nombre' => 'Feria de prueba',
            'lugar_id' => $lugar->id,
            'fecha_inicio' => now()->addDays(5)->toDateString(),
            'fecha_fin' => now()->addDays(6)->toDateString(),
            'hora_inicio' => '17:00',
            'estado' => 'abierto',
        ]);

        $this->diaUno = $this->evento->fecha_inicio->toDateString();
        $this->diaDos = $this->evento->fecha_fin->toDateString();
    }

    public function test_descarga_un_pdf_con_los_inscriptos_del_dia(): void
    {
        $this->inscribir('Gomez', $this->diaUno, 'aprobada');
        $this->inscribir('Lopez', $this->diaDos, 'aprobada');
        $this->inscribir('Ruiz', $this->diaUno, 'cancelada');

        $respuesta = $this->actingAs($this->coordinador)
            ->get("/coordinador/eventos/{$this->evento->id}/planilla?dia={$this->diaUno}")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringContainsString('attachment', $respuesta->headers->get('Content-Disposition'));
        $pdf = $respuesta->getContent();
        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('Gomez', $pdf);
        $this->assertStringNotContainsString('Lopez', $pdf);
        $this->assertStringNotContainsString('Ruiz', $pdf);
        $this->assertStringContainsString('Total: 1 inscriptos', $pdf);
    }

    public function test_el_visitante_no_descarga_la_planilla(): void
    {
        $this->get("/coordinador/eventos/{$this->evento->id}/planilla?dia={$this->diaUno}")
            ->assertRedirect('/login');
    }

    private function inscribir(string $apellido, string $fecha, string $estado): Inscripcion
    {
        $feriante = Feriante::factory()->create([
            'apellido' => $apellido,
            'categoria_id' => Categoria::where('nombre', 'Masas')->value('id'),
        ]);

        return Inscripcion::create([
            'evento_id' => $this->evento->id,
            'feriante_id' => $feriante->id,
            'fecha' => $fecha,
            'estado' => $estado,
            'confirmacion' => 'sin_confirmar',
            'reglamento_aceptado_at' => now(),
        ]);
    }
}
