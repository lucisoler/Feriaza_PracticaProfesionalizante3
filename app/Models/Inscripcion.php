<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Inscripcion extends Model
{
    // Laravel pluraliza en inglés ("inscripcions"), por eso se indica la tabla
    protected $table = 'inscripciones';

    protected $fillable = [
        'evento_id', 'feriante_id', 'puesto_id', 'fecha',
        'estado', 'confirmacion', 'reconfirmada_at', 'medio_pago', 'pagado_at', 'asistencia',
        'presente_at', 'reglamento_aceptado_at', 'recordatorio_reconfirmacion_enviado_at',
    ];

    protected $casts = [
        'reconfirmada_at' => 'datetime',
        'pagado_at' => 'datetime',
        'presente_at' => 'datetime',
        'reglamento_aceptado_at' => 'datetime',
        'recordatorio_reconfirmacion_enviado_at' => 'datetime',
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

    /** Fecha y hora en que empieza la feria el día de esta inscripción. */
    public function inicio(): Carbon
    {
        return $this->fecha->copy()->setTimeFromTimeString($this->evento->hora_inicio);
    }

    /** Desde cuándo puede reconfirmar el puesto: 72 horas antes del inicio de ese día (RN-16). */
    public function aperturaReconfirmacion(): Carbon
    {
        return $this->inicio()->subHours(config('feria.horas_reconfirmacion'));
    }

    public function puedeReconfirmar(): bool
    {
        return $this->confirmacion === 'sin_confirmar'
            && now()->between($this->aperturaReconfirmacion(), $this->inicio());
    }

    /** Ya se abrió la reconfirmación y todavía no se le avisó al feriante por correo. */
    public function debeAvisarseReconfirmacion(): bool
    {
        return $this->recordatorio_reconfirmacion_enviado_at === null && $this->puedeReconfirmar();
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
