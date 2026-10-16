<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sancion extends Model
{
    protected $table = 'sanciones';

    protected $fillable = ['inscripcion_id', 'registrada_por', 'motivo', 'observaciones'];

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    /** Coordinador que registró la sanción. */
    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }
}
