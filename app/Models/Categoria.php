<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['nombre', 'prioridad', 'recibe_mesa', 'color'];

    protected $casts = [
        'prioridad'   => 'integer',
        'recibe_mesa' => 'boolean',
    ];

    public function feriantes(): HasMany
    {
        return $this->hasMany(Feriante::class);
    }

    /** Id de la categoría "Otro", que exige describir el rubro. */
    public static function idOtro(): ?int
    {
        return static::where('nombre', 'Otro')->value('id');
    }
}
