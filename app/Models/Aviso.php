<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Aviso para el panel del coordinador (ej.: un feriante cedió su lugar).
 */
class Aviso extends Model
{
    protected $fillable = ['mensaje', 'leido_at'];

    protected $casts = [
        'leido_at' => 'datetime',
    ];
}
