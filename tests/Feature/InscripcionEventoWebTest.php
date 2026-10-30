<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\Puesto;
use App\Models\Seccion;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\LugarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Inscripción a un evento por día, con mesa de la sección de su categoría
 * en el plano del lugar del evento (RF-07; RN-03, RN-09).
 */
class InscripcionEventoWebTest extends TestCase
{
    use RefreshDatabase;

    private Lugar $plaza;
    private Evento $evento;
    private Feriante $artesano;
    private Puesto $mesaArtesanos;
    private Puesto $mesaMasas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategoriaSeeder::class, LugarSeeder::class]);

        $this->plaza = Lugar::where('slug', 'plaza-central')->firstOrFail();
        $this->evento = Evento::create([
            'nombre' => 'Feria de prueba',
            'lugar_id' => $this->plaza->id,
            'fecha_inicio' => now()->addDays(5)->toDateString(),
            'fecha_fin' => now()->addDays(6)->toDateString(),
            'hora_inicio' => '17:00',
            'estado' => 'abierto',
        ]);

        $this->artesano = Feriante::factory()->create([
            'categoria_id' => Categoria::where('nombre', 'Artesanos')->value('id'),
        ]);

        $this->mesaArtesanos = $this->mesaEn('Artesanos', 1);
        $this->mesaMasas = $this->mesaEn('Masas', 2);
    }

    private function mesaEn(string $categoria, int $numero): Puesto
    {
        $seccion = Seccion::where('lugar_id', $this->plaza->id)
            ->where('categoria_id', Categoria::where('nombre', $categoria)->value('id'))
            ->firstOrFail();

        return Puesto::create([
            'lugar_id' => $this->plaza->id,
            'seccion_id' => $seccion->id,
            'numero' => $numero,
            'x' => $seccion->x + 20,
            'y' => $seccion->y + 20,
        ]);
    }

    private function inscribir(Feriante $feriante, array $cambios = [])
    {
        return $this->actingAs($feriante, 'feriante')->post('/mi-cuenta/inscripciones', array_merge([
            'evento_id' => $this->evento->id,
            'fecha' => $this->evento->fecha_inicio->toDateString(),
            'puesto_id' => $this->mesaArtesanos->id,
            'acepta_reglamento' => true,
        ], $cambios));
    }

    public function test_la_pagina_muestra_los_eventos_abiertos_con_su_plano(): void
    {
        $this->actingAs($this->artesano, 'feriante')
            ->get('/mi-cuenta/inscripciones/nueva')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Feriante/Inscribirse')
                ->has('eventos', 1)
                ->where('eventos.0.dias', $this->evento->dias())
                ->has("lugares.{$this->plaza->id}.secciones")
                ->has("lugares.{$this->plaza->id}.puestos", 2));
    }

    public function test_el_feriante_reserva_una_mesa_de_su_seccion_para_un_dia(): void
    {
        $this->inscribir($this->artesano)
            ->assertRedirect('/gracias')
            ->assertSessionHas('whatsapp');

        $this->assertDatabaseHas('inscripciones', [
            'evento_id' => $this->evento->id,
            'feriante_id' => $this->artesano->id,
            'puesto_id' => $this->mesaArtesanos->id,
            'fecha' => $this->evento->fecha_inicio->toDateString(),
        ]);
    }

    public function test_no_puede_elegir_una_mesa_de_otra_categoria(): void
    {
        $this->inscribir($this->artesano, ['puesto_id' => $this->mesaMasas->id])
            ->assertSessionHasErrors('puesto_id');
    }

    public function test_no_puede_elegir_una_mesa_de_una_zona_no_disponible(): void
    {
        $this->mesaArtesanos->seccion->update(['disponible' => false]);

        $this->inscribir($this->artesano)->assertSessionHasErrors('puesto_id');
    }

    public function test_no_puede_elegir_una_mesa_deshabilitada(): void
    {
        $this->mesaArtesanos->update(['habilitado' => false]);

        $this->inscribir($this->artesano)->assertSessionHasErrors('puesto_id');
    }

    public function test_la_misma_mesa_se_reserva_una_vez_por_dia_pero_queda_libre_otro_dia(): void
    {
        $otro = Feriante::factory()->create(['categoria_id' => $this->artesano->categoria_id]);
        $this->inscribir($this->artesano)->assertRedirect('/gracias');

        $this->inscribir($otro)->assertSessionHasErrors('puesto_id');

        $this->inscribir($otro, ['fecha' => $this->evento->fecha_fin->toDateString()])
            ->assertRedirect('/gracias');
    }

    public function test_no_se_inscribe_dos_veces_el_mismo_dia(): void
    {
        $this->inscribir($this->artesano)->assertRedirect('/gracias');

        $otraMesa = $this->mesaEn('Artesanos', 3);
        $this->inscribir($this->artesano, ['puesto_id' => $otraMesa->id])
            ->assertSessionHasErrors('fecha');
    }

    public function test_el_dia_tiene_que_ser_del_evento(): void
    {
        $this->inscribir($this->artesano, ['fecha' => now()->addDays(30)->toDateString()])
            ->assertSessionHasErrors('fecha');
    }

    public function test_solo_se_inscribe_a_eventos_abiertos(): void
    {
        $this->evento->update(['estado' => 'borrador']);

        $this->inscribir($this->artesano)->assertSessionHasErrors('evento_id');
    }

    public function test_hay_que_aceptar_el_reglamento(): void
    {
        $this->inscribir($this->artesano, ['acepta_reglamento' => false])
            ->assertSessionHasErrors('acepta_reglamento');
    }

    public function test_el_revendedor_se_inscribe_sin_mesa(): void
    {
        $revendedor = Feriante::factory()->create([
            'categoria_id' => Categoria::where('nombre', 'Revendedores')->value('id'),
        ]);

        $this->inscribir($revendedor)->assertRedirect('/gracias');

        $this->assertNull(Inscripcion::where('feriante_id', $revendedor->id)->value('puesto_id'));
    }

    public function test_al_cancelar_se_libera_la_mesa(): void
    {
        $this->inscribir($this->artesano);
        $inscripcion = Inscripcion::where('feriante_id', $this->artesano->id)->firstOrFail();

        $this->actingAs($this->artesano, 'feriante')
            ->from('/mi-cuenta')
            ->delete("/mi-cuenta/inscripciones/{$inscripcion->id}")
            ->assertRedirect('/mi-cuenta');

        $this->assertModelMissing($inscripcion);
    }

    public function test_no_cancela_inscripciones_de_otro_feriante(): void
    {
        $this->inscribir($this->artesano);
        $inscripcion = Inscripcion::where('feriante_id', $this->artesano->id)->firstOrFail();
        $otro = Feriante::factory()->create();

        $this->actingAs($otro, 'feriante')
            ->delete("/mi-cuenta/inscripciones/{$inscripcion->id}")
            ->assertNotFound();
    }
}
