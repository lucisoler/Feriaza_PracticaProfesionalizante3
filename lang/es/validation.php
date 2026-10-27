<?php

// Mensajes de validación en español para Feriaza.
// Incluye solo las reglas que usa el proyecto; si se agrega una regla nueva,
// sumar su mensaje acá.

return [

    'accepted'  => 'Tenés que aceptar :attribute.',
    'boolean'   => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'email'     => 'El :attribute debe ser una dirección de correo válida.',
    'exists'    => 'El valor elegido en :attribute no es válido.',
    'in'        => 'El valor elegido en :attribute no es válido.',
    'integer'   => 'El campo :attribute debe ser un número entero.',
    'numeric'   => 'El campo :attribute debe ser un número.',
    'required'  => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio.',
    'string'    => 'El campo :attribute debe ser un texto.',
    'unique'    => 'Ese :attribute ya está registrado.',

    'max' => [
        'string'  => 'El campo :attribute no puede tener más de :max caracteres.',
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
    ],

    'min' => [
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
    ],

    // Mensajes a medida para casos puntuales
    'custom' => [
        'rubro_otro' => [
            'required_if' => 'Contanos qué vendés.',
        ],
        'puesto_id' => [
            'required' => 'Elegí una mesa libre en el mapa.',
        ],
    ],

    // Nombres de los campos tal como los lee el usuario
    'attributes' => [
        'nombre'                => 'nombre',
        'apellido'              => 'apellido',
        'email'                 => 'email',
        'password'              => 'contraseña',
        'telefono'              => 'teléfono',
        'nombre_emprendimiento' => 'nombre del emprendimiento',
        'categoria_id'          => 'categoría',
        'rubro_otro'            => 'qué vende',
        'puesto_id'             => 'mesa',
        'instagram'             => 'Instagram',
        'facebook'              => 'Facebook',
        'tiktok'                => 'TikTok',
        'consulta'              => 'consulta',
        'estado'                => 'estado',
        'asistencia_confirmada' => 'la confirmación de asistencia',
    ],

];
