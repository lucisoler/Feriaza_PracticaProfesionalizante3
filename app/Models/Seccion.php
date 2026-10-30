<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seccion extends Model
{
    protected $table = 'secciones';

    /** Separación entre centros de mesas al distribuirlas en grilla (unidades del plano). */
    public const PASO_X = 34;
    public const PASO_Y = 26;

    protected $fillable = ['lugar_id', 'nombre', 'categoria_id', 'disponible', 'x', 'y', 'ancho', 'alto'];

    protected $casts = [
        'disponible' => 'boolean',
        'x' => 'integer',
        'y' => 'integer',
        'ancho' => 'integer',
        'alto' => 'integer',
    ];

    public function lugar(): BelongsTo
    {
        return $this->belongsTo(Lugar::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function puestos(): HasMany
    {
        return $this->hasMany(Puesto::class);
    }

    public function contiene(int $x, int $y): bool
    {
        return $x >= $this->x && $x <= $this->x + $this->ancho
            && $y >= $this->y && $y <= $this->y + $this->alto;
    }

    /**
     * Hasta $cantidad posiciones en grilla dentro de la sección que no pisan
     * las mesas que ya están. Cada posición es ['x' => int, 'y' => int].
     *
     * @param  array<int, array{x: int, y: int}>  $ocupadas
     * @return array<int, array{x: int, y: int}>
     */
    public function posicionesLibres(int $cantidad, array $ocupadas = []): array
    {
        $columnas = max(1, intdiv($this->ancho - 8, self::PASO_X));
        $filas = max(1, intdiv($this->alto - 4, self::PASO_Y));

        // La grilla queda centrada dentro del rectángulo
        $inicioX = $this->x + intdiv($this->ancho - ($columnas - 1) * self::PASO_X, 2);
        $inicioY = $this->y + intdiv($this->alto - ($filas - 1) * self::PASO_Y, 2);

        $libres = [];
        for ($f = 0; $f < $filas && count($libres) < $cantidad; $f++) {
            for ($c = 0; $c < $columnas && count($libres) < $cantidad; $c++) {
                $punto = ['x' => $inicioX + $c * self::PASO_X, 'y' => $inicioY + $f * self::PASO_Y];

                $pisa = collect($ocupadas)->contains(fn ($o) =>
                    abs($o['x'] - $punto['x']) < self::PASO_X - 6 && abs($o['y'] - $punto['y']) < self::PASO_Y - 6
                );

                if (! $pisa) {
                    $libres[] = $punto;
                }
            }
        }

        return $libres;
    }
}
