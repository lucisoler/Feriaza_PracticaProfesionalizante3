<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Manda SMS por la API REST de Twilio (credenciales en config/services.php).
 */
class SmsService
{
    /**
     * @throws RuntimeException si Twilio no está configurado o rechaza el envío
     */
    public function enviar(string $telefono, string $mensaje): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $desde = config('services.twilio.desde');

        if (! $sid || ! $token || ! $desde) {
            throw new RuntimeException('Twilio no está configurado (TWILIO_SID, TWILIO_TOKEN, TWILIO_DESDE).');
        }

        Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $this->numeroE164($telefono),
                'From' => $desde,
                'Body' => $mensaje,
            ])
            ->throw();
    }

    /**
     * Los feriantes cargan el teléfono a mano: se lo lleva al formato internacional.
     * Sin código de país se asume Argentina, celular (+54 9).
     */
    public function numeroE164(string $telefono): string
    {
        $digitos = preg_replace('/\D/', '', $telefono) ?? '';

        if (str_starts_with($digitos, '54')) {
            return '+'.$digitos;
        }

        return '+549'.ltrim($digitos, '0');
    }
}
