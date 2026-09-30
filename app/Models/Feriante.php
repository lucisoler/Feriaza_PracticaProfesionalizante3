<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feriante extends Model
{
    protected $fillable = [
        'nombre','apellido','email','password','nombre_emprendimiento',
        'rubro','rubro_otro','telefono',
        'instagram','facebook','tiktok',
        'consulta','estado','asistencia_confrimada',
    ];

protected $casts = [
    'password'=>'hashed',
    'asistencia_confirmada'=>'boolean'

];

public function puesto():HasOne{
    return $this->hasOne(Puesto::class);
}
}
