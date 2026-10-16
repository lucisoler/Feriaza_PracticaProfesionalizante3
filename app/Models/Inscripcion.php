<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inscripcion extends Model
{
    // Laravel pluraliza en inglés ("inscripcions"), por eso se indica la tabla
    protected $table = 'inscripciones';

    protected $fillable = [
        'evento_id', 'feriante_id', 'puesto_id',
        'estado', 'confirmacion', 'asistencia',
        'presente_at', 'reglamento_aceptado_at',
    ];

    protected $casts = [
        'presente_at' => 'datetime',
        'reglamento_aceptado_at' => 'datetime',
    ];

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
