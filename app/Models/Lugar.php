<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lugar extends Model
{
    // Laravel pluraliza en inglés ("lugars"), por eso se indica la tabla
    protected $table = 'lugares';

    protected $fillable = ['nombre', 'slug', 'ancho', 'alto'];

    protected $casts = [
        'ancho' => 'integer',
        'alto' => 'integer',
    ];

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class);
    }

    public function puestos(): HasMany
    {
        return $this->hasMany(Puesto::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    /** Lugar con todo lo que necesita el componente del plano. */
    public function conPlano(): static
    {
        return $this->load([
            'secciones' => fn ($q) => $q->orderBy('id'),
            'puestos' => fn ($q) => $q->orderBy('numero')
                ->select(['id', 'lugar_id', 'seccion_id', 'numero', 'habilitado', 'x', 'y']),
        ]);
    }

    /** Sección que contiene el punto (x, y), si hay alguna. */
    public function seccionEn(int $x, int $y): ?Seccion
    {
        return $this->secciones()->get()->first(fn (Seccion $s) => $s->contiene($x, $y));
    }

    /** Número para una mesa nueva: el siguiente al más alto del lugar. */
    public function siguienteNumero(): int
    {
        return (int) $this->puestos()->max('numero') + 1;
    }
}
