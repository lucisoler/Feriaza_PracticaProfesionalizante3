<?php

namespace App\Exceptions;

use RuntimeException;

/** El feriante tiene inscripciones en eventos abiertos: no se puede eliminar (RF-13). */
class FerianteConEventosAbiertosException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No se puede eliminar: el feriante tiene inscripciones en eventos abiertos.');
    }
}
