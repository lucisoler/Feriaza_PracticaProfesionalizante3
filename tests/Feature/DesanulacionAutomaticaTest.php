<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Lugar;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\LugarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La cuenta anulada vuelve sola tras 2 eventos reales (3 si la falta fue en un evento grande).
 */
class DesanulacionAutomaticaTest extends TestCase
{
    use RefreshDatabase;

    private int $lugarId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategoriaSeeder::class, LugarSeeder::class]);
        $this->lugarId = Lugar::where('slug', 'plaza-central')->value('id');
    }

    private function feriante(bool $vetadoEnEventoGrande = false): Feriante
    {
        return Feriante::factory()->create([
            'categoria_id' => Categoria::where('nombre', 'Revendedores')->value('id'),
            'estado' => 'anulado',
            'motivo_anulacion' => 'Falta de pago',
            'anulado_at' => '2026-11-01 10:00:00',
            'vetado_desde_evento_id' => $vetadoEnEventoGrande ? $this->evento('2026-10-25', true)->id : null,
        ]);
    }

    private function evento(string $fecha, bool $grande = false, string $estado = 'cerrado'): Evento
    {
        return Evento::create([
            'nombre' => "Feria {$fecha}",
            'lugar_id' => $this->lugarId,
            'fecha_inicio' => $fecha,
            'fecha_fin' => $fecha,
            'hora_inicio' => '17:00',
            'estado' => $estado,
            'grande' => $grande,
        ]);
    }

    public function test_una_cuenta_anulada_vuelve_sola_tras_2_eventos_regulares(): void
    {
        $feriante = $this->feriante();
        $this->evento('2026-11-08');

        $this->artisan('feria:desanular-cuentas');
        $this->assertSame('anulado', $feriante->fresh()->estado);

        $this->evento('2026-11-15');
        $this->artisan('feria:desanular-cuentas');

        $feriante->refresh();
        $this->assertSame('aprobado', $feriante->estado);
        $this->assertNull($feriante->motivo_anulacion);
        $this->assertNull($feriante->anulado_at);
    }

    public function test_los_eventos_sin_realizar_no_cuentan_para_desanular(): void
    {
        $feriante = $this->feriante();
        $this->evento('2026-11-08');
        $this->evento('2026-11-15', false, 'borrador');

        $this->artisan('feria:desanular-cuentas');

        $this->assertSame('anulado', $feriante->fresh()->estado);
    }

    public function test_si_la_falta_fue_en_evento_grande_hacen_falta_3_eventos(): void
    {
        $feriante = $this->feriante(vetadoEnEventoGrande: true);
        $this->evento('2026-11-08');
        $this->evento('2026-11-15');

        $this->artisan('feria:desanular-cuentas');
        $this->assertSame('anulado', $feriante->fresh()->estado);

        $this->evento('2026-11-22');
        $this->artisan('feria:desanular-cuentas');

        $this->assertSame('aprobado', $feriante->fresh()->estado);
    }
}
