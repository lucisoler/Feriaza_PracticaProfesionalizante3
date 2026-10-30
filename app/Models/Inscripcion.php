<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inscripcion extends Model
{
    // Laravel pluraliza en inglés ("inscripcions"), por eso se indica la tabla
    protected $table = 'inscripciones';

    protected $fillable = [
        'evento_id', 'feriante_id', 'puesto_id', 'fecha',
        'estado', 'confirmacion', 'asistencia',
        'presente_at', 'reglamento_aceptado_at',
    ];

    protected $casts = [
        'presente_at' => 'datetime',
        'reglamento_aceptado_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Sin día indicado (eventos de un solo día, API anterior), va al primer día del evento
        static::creating(function (Inscripcion $inscripcion) {
            if (empty($inscripcion->getAttributes()['fecha'])) {
                $inscripcion->fecha = $inscripcion->evento?->fecha_inicio;
            }
        });
    }

    /**
     * Día de la inscripción. Se guarda siempre como Y-m-d (sin hora) para que
     * el índice único evento + día + mesa compare igual en SQLite y en MySQL.
     */
    protected function fecha(): Attribute
    {
        return Attribute::make(
            get: fn (?string $valor) => $valor ? Carbon::parse($valor)->startOfDay() : null,
            set: fn ($valor) => $valor ? Carbon::parse($valor)->toDateString() : null,
        );
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function feriante(): BelongsTo
    {
        return $this->belongsTo(Feriante::class);
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    public function sancion(): HasOne
    {
        return $this->hasOne(Sancion::class);
    }
}
