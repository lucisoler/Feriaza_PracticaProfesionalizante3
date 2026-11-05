<?php

namespace App\Models;

use App\Notifications\RestablecerContrasena;
use Database\Factories\FerianteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Feriante extends Authenticatable
{
    /** @use HasFactory<FerianteFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombre', 'apellido', 'email', 'password', 'proveedor', 'proveedor_id',
        'nombre_emprendimiento',
        'categoria_id', 'rubro_otro', 'telefono',
        'instagram', 'facebook', 'tiktok',
        'consulta', 'estado', 'motivo_anulacion',
        'vetado_desde_evento_id', 'vetado_motivo', 'vetado_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'vetado_at' => 'datetime',
        'password' => 'hashed', // Laravel la hashea sola al guardar
    ];

    // La categoría se carga siempre junto con el feriante
    protected $with = ['categoria'];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** El enlace del correo lleva a la pantalla del feriante, no a la de Laravel por defecto. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new RestablecerContrasena($token));
    }

    /** Cuenta anulada por el coordinador: inasistencia, falta de pago u otra falta (RN-18, RN-19). */
    public function estaAnulado(): bool
    {
        return $this->estado === 'anulado';
    }

    public function vetadoDesdeEvento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'vetado_desde_evento_id');
    }

    /** Vetado mientras no pasaron N eventos reales desde el que lo vetó (sin contarlo), RN-07/RN-08. */
    public function estaVetado(): bool
    {
        if (! $this->vetado_desde_evento_id) {
            return false;
        }

        $eventoVeto = $this->vetadoDesdeEvento;
        $eventosPosteriores = Evento::where('id', '!=', $eventoVeto->id)
            ->where('estado', '!=', 'borrador')
            ->where('fecha_inicio', '>', $eventoVeto->fecha_inicio)
            ->count();

        return $eventosPosteriores < config('feria.eventos_veto');
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
