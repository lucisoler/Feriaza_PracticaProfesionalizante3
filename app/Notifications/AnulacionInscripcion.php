<?php

namespace App\Notifications;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso por correo al feriante de que se anuló su inscripción por no
 * presentarse dentro de la hora de tolerancia (RN-07, RN-08).
 */
class AnulacionInscripcion extends Notification
{
    use Queueable;

    public function __construct(public Inscripcion $inscripcion) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $inscripcion = $this->inscripcion;
        $evento = $inscripcion->evento;

        $mensaje = (new MailMessage)
            ->subject("Se anuló tu inscripción en {$evento->nombre}")
            ->greeting("Hola, {$notifiable->nombre}.")
            ->line("Tu inscripción a {$evento->nombre} ({$evento->lugar->nombre}) del día ".$inscripcion->fecha->format('d/m/Y').' fue anulada: no te presentaste dentro de la hora de tolerancia.')
            ->line('Se liberó tu mesa para que la use otro feriante.');

        if ($evento->grande) {
            $mensaje->line('Por ser un evento grande, quedás vedado para inscribirte a los próximos eventos durante un tiempo.');
        }

        return $mensaje->salutation('Feriaza');
    }
}
