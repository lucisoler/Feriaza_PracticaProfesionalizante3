<?php

namespace App\Models;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = ['nombre', 'lugar_id', 'fecha_inicio', 'fecha_fin', 'hora_inicio', 'estado'];

    protected $casts = [
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
    ];

    public function lugar(): BelongsTo
    {
        return $this->belongsTo(Lugar::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    /** Solo los eventos abiertos admiten inscripciones (RF-05). */
    public function estaAbierto(): bool
    {
        return $this->estado === 'abierto';
    }

    /**
     * Días del evento en formato Y-m-d, del primero al último.
     *
     * @return array<int, string>
     */
    public function dias(): array
    {
        return collect(CarbonPeriod::create($this->fecha_inicio, $this->fecha_fin))
            ->map(fn ($dia) => $dia->toDateString())
            ->all();
    }

    /**
     * Mesas ocupadas por día: ['2026-11-09' => [puesto_id, ...], ...].
     * Cualquier inscripción ocupa su mesa, igual que el índice único de la base;
     * al cancelar, la inscripción se borra y la mesa se libera.
     *
     * @return array<string, array<int, int>>
     */
    public function ocupacion(): array
    {
        $ocupacion = array_fill_keys($this->dias(), []);

        $this->inscripciones()
            ->whereNotNull('puesto_id')
            ->get(['fecha', 'puesto_id'])
            ->each(function (Inscripcion $i) use (&$ocupacion) {
                $ocupacion[$i->fecha->toDateString()][] = $i->puesto_id;
            });

        return $ocupacion;
    }
}
