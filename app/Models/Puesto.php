<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Puesto extends Model
{
    protected $fillable = ['lugar_id', 'seccion_id', 'numero', 'estado', 'feriante_id', 'habilitado', 'x', 'y'];

    protected $casts = [
        'habilitado' => 'boolean',
        'x' => 'integer',
        'y' => 'integer',
    ];

    public function feriante(): BelongsTo
    {
        return $this->belongsTo(Feriante::class);
    }

    public function lugar(): BelongsTo
    {
        return $this->belongsTo(Lugar::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    /** Tiene inscripciones en eventos que todavía no terminaron: no se puede borrar. */
    public function tieneInscripcionesVigentes(): bool
    {
        return $this->inscripciones()
            ->whereHas('evento', fn ($q) => $q->whereIn('estado', ['borrador', 'abierto']))
            ->exists();
    }
}
