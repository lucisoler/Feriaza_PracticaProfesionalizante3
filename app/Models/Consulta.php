<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consulta extends Model
{
    protected $table = 'consultas';

    protected $fillable = [
        'feriante_id', 'asunto', 'mensaje',
        'respuesta', 'estado', 'respondida_por', 'respondida_at',
    ];

    protected $casts = [
        'respondida_at' => 'datetime',
    ];

    public function feriante(): BelongsTo
    {
        return $this->belongsTo(Feriante::class);
    }

    /** Coordinador que respondió la consulta. */
    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondida_por');
    }
}
