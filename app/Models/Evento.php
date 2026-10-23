<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = ['nombre', 'fecha_inicio', 'fecha_fin', 'hora_inicio', 'estado'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    /** Solo los eventos abiertos admiten inscripciones (RF-05). */
    public function estaAbierto(): bool
    {
        return $this->estado === 'abierto';
    }
}
