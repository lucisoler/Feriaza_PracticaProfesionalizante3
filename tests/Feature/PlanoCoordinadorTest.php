<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\Puesto;
use App\Models\Seccion;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\LugarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Diseño del plano y gestión de eventos por el coordinador (RF-05, RF-19).
 */
class PlanoCoordinadorTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinador;
    private Lugar $plaza;
    private Seccion $artesanos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategoriaSeeder::class, LugarSeeder::class]);
        $this->coordinador = User::factory()->create();
        $this->plaza = Lugar::where('slug', 'plaza-central')->firstOrFail();
        $this->artesanos = Seccion::where('nombre', 'Vereda norte')->firstOrFail();
    }

    public function test_los_dos_lugares_tienen_su_plano(): void
    {
        $this->assertSame(['plaza-central', 'pulmon-verde'], Lugar::orderBy('id')->pluck('slug')->all());

        $this->actingAs($this->coordinador)
            ->get('/coordinador/plano?lugar=pulmon-verde')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Coordinador/Plano')
                ->where('lugar.slug', 'pulmon-verde')
                ->has('lugar.secciones')
                ->has('lugares', 2));
    }

    public function test_las_categorias_tienen_color_distintivo(): void
    {
        $colores = Categoria::pluck('color');

        $this->assertCount($colores->count(), $colores->unique());
        $this->assertTrue($colores->every(fn ($c) => preg_match('/^#[0-9a-f]{6}$/i', $c) === 1));
    }

    public function test_agrega_una_mesa_dentro_de_una_seccion(): void
    {
        $this->actingAs($this->coordinador)
            ->post('/coordinador/puestos', [
                'lugar_id' => $this->plaza->id,
                'x' => $this->artesanos->x + 30,
                'y' => $this->artesanos->y + 20,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('puestos', [
            'lugar_id' => $this->plaza->id,
            'seccion_id' => $this->artesanos->id,
            'numero' => 1,
        ]);
    }

    public function test_no_agrega_mesas_fuera_de_las_secciones_ni_en_zonas_no_disponibles(): void
    {
        $monumento = Seccion::where('nombre', 'Monumento central')->firstOrFail();

        $this->actingAs($this->coordinador)
            ->post('/coordinador/puestos', ['lugar_id' => $this->plaza->id, 'x' => 5, 'y' => 5])
            ->assertSessionHasErrors('puesto');

        $this->actingAs($this->coordinador)
            ->post('/coordinador/puestos', ['lugar_id' => $this->plaza->id, 'x' => $monumento->x + 10, 'y' => $monumento->y + 10])
            ->assertSessionHasErrors('puesto');

        $this->assertSame(0, Puesto::count());
    }

    public function test_distribuye_mesas_en_grilla_sin_superponerlas(): void
    {
        $this->actingAs($this->coordinador)
            ->post("/coordinador/secciones/{$this->artesanos->id}/completar", ['cantidad' => 8]);
        $this->actingAs($this->coordinador)
            ->post("/coordinador/secciones/{$this->artesanos->id}/completar", ['cantidad' => 4]);

        $mesas = Puesto::where('seccion_id', $this->artesanos->id)->get();
        $this->assertCount(12, $mesas);
        $this->assertCount(12, $mesas->map(fn ($p) => "{$p->x},{$p->y}")->unique());
        $this->assertEquals(range(1, 12), $mesas->pluck('numero')->sort()->values()->all());
        $this->assertTrue($mesas->every(fn ($p) => $this->artesanos->contiene($p->x, $p->y)));
    }

    public function test_al_mover_una_mesa_cambia_de_seccion(): void
    {
        $masas = Seccion::where('nombre', 'Costado oeste')->firstOrFail();
        $mesa = Puesto::create(['lugar_id' => $this->plaza->id, 'seccion_id' => $this->artesanos->id, 'numero' => 1, 'x' => 200, 'y' => 100]);

        $this->actingAs($this->coordinador)
            ->patch("/coordinador/puestos/{$mesa->id}", ['x' => $masas->x + 30, 'y' => $masas->y + 30])
            ->assertSessionHasNoErrors();

        $this->assertSame($masas->id, $mesa->fresh()->seccion_id);
    }

    public function test_crea_una_seccion_y_la_marca_no_disponible(): void
    {
        $this->actingAs($this->coordinador)
            ->post('/coordinador/secciones', [
                'lugar_id' => $this->plaza->id,
                'nombre' => 'Obra en construcción',
                'categoria_id' => null,
                'disponible' => false,
                'x' => 600, 'y' => 600, 'ancho' => 100, 'alto' => 40,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('secciones', ['nombre' => 'Obra en construcción', 'disponible' => false]);
    }

    public function test_una_seccion_no_puede_salirse_del_plano(): void
    {
        $this->actingAs($this->coordinador)
            ->post('/coordinador/secciones', [
                'lugar_id' => $this->plaza->id,
                'nombre' => 'Afuera',
                'disponible' => true,
                'x' => 950, 'y' => 650, 'ancho' => 200, 'alto' => 200,
            ])
            ->assertSessionHasErrors('seccion');
    }

    public function test_no_borra_mesas_con_inscripciones_en_eventos_abiertos(): void
    {
        $mesa = Puesto::create(['lugar_id' => $this->plaza->id, 'seccion_id' => $this->artesanos->id, 'numero' => 1, 'x' => 200, 'y' => 100]);
        $evento = Evento::create([
            'nombre' => 'Abierto', 'lugar_id' => $this->plaza->id, 'estado' => 'abierto',
            'fecha_inicio' => now()->addDay()->toDateString(), 'fecha_fin' => now()->addDay()->toDateString(), 'hora_inicio' => '17:00',
        ]);
        Inscripcion::create([
            'evento_id' => $evento->id, 'feriante_id' => Feriante::factory()->create()->id,
            'puesto_id' => $mesa->id, 'reglamento_aceptado_at' => now(),
        ]);

        $this->actingAs($this->coordinador)
            ->delete("/coordinador/puestos/{$mesa->id}")
            ->assertSessionHasErrors('puesto');

        $this->assertModelExists($mesa);
    }

    public function test_cambia_el_color_de_una_categoria(): void
    {
        $categoria = Categoria::firstOrFail();

        $this->actingAs($this->coordinador)
            ->patch("/coordinador/categorias/{$categoria->id}", ['color' => '#123abc'])
            ->assertSessionHasNoErrors();

        $this->assertSame('#123abc', $categoria->fresh()->color);
    }

    public function test_crea_un_evento_eligiendo_el_lugar(): void
    {
        $verde = Lugar::where('slug', 'pulmon-verde')->firstOrFail();

        $this->actingAs($this->coordinador)
            ->post('/coordinador/eventos', [
                'nombre' => 'Feria de verano',
                'lugar_id' => $verde->id,
                'fecha_inicio' => '2026-12-05',
                'fecha_fin' => '2026-12-06',
                'hora_inicio' => '18:00',
                'estado' => 'abierto',
            ])
            ->assertSessionHasNoErrors();

        $evento = Evento::where('nombre', 'Feria de verano')->firstOrFail();
        $this->assertSame($verde->id, $evento->lugar_id);
        $this->assertSame(['2026-12-05', '2026-12-06'], $evento->dias());
    }

    public function test_el_evento_exige_lugar_y_fechas_coherentes(): void
    {
        $this->actingAs($this->coordinador)
            ->post('/coordinador/eventos', [
                'nombre' => 'Mal cargado',
                'fecha_inicio' => '2026-12-06',
                'fecha_fin' => '2026-12-05',
                'hora_inicio' => '18:00',
                'estado' => 'borrador',
            ])
            ->assertSessionHasErrors(['lugar_id', 'fecha_fin']);
    }

    public function test_un_evento_con_inscriptos_no_se_elimina(): void
    {
        $evento = Evento::create([
            'nombre' => 'Con gente', 'lugar_id' => $this->plaza->id, 'estado' => 'abierto',
            'fecha_inicio' => '2026-12-05', 'fecha_fin' => '2026-12-05', 'hora_inicio' => '17:00',
        ]);
        Inscripcion::create([
            'evento_id' => $evento->id, 'feriante_id' => Feriante::factory()->create()->id,
            'reglamento_aceptado_at' => now(),
        ]);

        $this->actingAs($this->coordinador)
            ->delete("/coordinador/eventos/{$evento->id}")
            ->assertSessionHasErrors('evento');

        $this->assertModelExists($evento);
    }

    public function test_el_coordinador_ve_la_ocupacion_del_plano_por_dia(): void
    {
        $evento = Evento::create([
            'nombre' => 'Dos días', 'lugar_id' => $this->plaza->id, 'estado' => 'abierto',
            'fecha_inicio' => '2026-12-05', 'fecha_fin' => '2026-12-06', 'hora_inicio' => '17:00',
        ]);
        $mesa = Puesto::create(['lugar_id' => $this->plaza->id, 'seccion_id' => $this->artesanos->id, 'numero' => 1, 'x' => 200, 'y' => 100]);
        Inscripcion::create([
            'evento_id' => $evento->id, 'feriante_id' => Feriante::factory()->create()->id,
            'puesto_id' => $mesa->id, 'fecha' => '2026-12-06', 'reglamento_aceptado_at' => now(),
        ]);

        $this->actingAs($this->coordinador)
            ->get("/coordinador/eventos/{$evento->id}?dia=2026-12-06")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Coordinador/Evento')
                ->where('dia', '2026-12-06')
                ->has('inscripciones', 1)
                ->where('inscripciones.0.mesa', 1));

        $this->actingAs($this->coordinador)
            ->get("/coordinador/eventos/{$evento->id}?dia=2026-12-05")
            ->assertInertia(fn ($page) => $page->has('inscripciones', 0));
    }
}
