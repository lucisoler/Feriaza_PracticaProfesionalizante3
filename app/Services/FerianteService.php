<?php

namespace App\Services;

use App\Exceptions\FerianteConEventosAbiertosException;
use App\Models\Feriante;
use App\Notifications\CuentaAnulada;
use Throwable;

/**
 * Logica de feriantes compartida entre los controladores web (Inertia) y los
 * de la API, para no duplicarla entre ambos (D-02, issue #31).
 */
class FerianteService
{
    public function registrar(array $data): Feriante
    {
        return Feriante::create($data);
    }

    /** Aplica la anulacion (RN-18, RN-19) si el estado pasa a "anulado". */
    public function actualizar(Feriante $feriante, array $data): Feriante
    {
        if (array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        }

        $pasaAAnulado = ($data['estado'] ?? null) === 'anulado' && ! $feriante->estaAnulado();

        if (array_key_exists('estado', $data) && $data['estado'] !== 'anulado') {
            $data['motivo_anulacion'] = null;
        }

        $feriante->update($data);

        if ($pasaAAnulado) {
            $this->anular($feriante);
        }

        return $feriante;
    }

    /** @throws FerianteConEventosAbiertosException */
    public function eliminar(Feriante $feriante): void
    {
        if ($this->tieneEventosAbiertos($feriante)) {
            throw new FerianteConEventosAbiertosException;
        }

        $feriante->tokens()->delete();
        $feriante->delete();
    }

    /** Baja que el feriante pide sobre su propia cuenta: no exige estar libre de eventos abiertos. */
    public function eliminarCuentaPropia(Feriante $feriante): void
    {
        $feriante->tokens()->delete();
        $feriante->delete();
    }

    private function tieneEventosAbiertos(Feriante $feriante): bool
    {
        return $feriante->inscripciones()
            ->whereHas('evento', fn ($evento) => $evento->where('estado', 'abierto'))
            ->exists();
    }

    /** Una cuenta anulada no conserva mesas: se liberan sus reservas de hoy en adelante. */
    private function anular(Feriante $feriante): void
    {
        $feriante->inscripciones()->whereDate('fecha', '>=', today())->delete();

        try {
            $feriante->notify(new CuentaAnulada($feriante));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
