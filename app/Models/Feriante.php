<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Feriante extends Authenticatable
{
    use HasApiTokens,Notifiable;

    protected $fillable = [
        'nombre', 'apellido', 'email', 'password',
        'nombre_emprendimiento',
        'categoria_id', 'rubro_otro', 'telefono',
        'instagram', 'facebook', 'tiktok',
        'consulta', 'asistencia_confirmada', 'estado',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'asistencia_confirmada' => 'boolean',
        'password' => 'hashed', // Laravel la hashea sola al guardar
    ];

    // La categoría se carga siempre junto con el feriante,
    // así las vistas pueden mostrar su nombre sin consultas extra.
    protected $with = ['categoria'];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function puesto(): HasOne
    {
        return $this->hasOne(Puesto::class);
    }

    // Participaciones en eventos (D-07)
    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    // Consultas enviadas al coordinador (RF-17)
    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }







}
