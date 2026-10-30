<?php

namespace App\Models;

use Database\Factories\FerianteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class Feriante extends Authenticatable
{
    /** @use HasFactory<FerianteFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombre', 'apellido', 'email', 'password', 'proveedor', 'proveedor_id',
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

    // La categoría se carga siempre junto con el feriante
    protected $with = ['categoria'];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // Puesto del flujo actual (se reemplaza por las inscripciones más adelante)
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