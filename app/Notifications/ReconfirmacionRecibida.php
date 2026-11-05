<?php

namespace App\Notifications;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso por correo al feriante de que su reconfirmación quedó registrada (RN-16).
 */
class ReconfirmacionRecibida extends Notification
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

        return (new MailMessage)
            ->subject("Reconfirmaste tu puesto en {$evento->nombre}")
            ->greeting("¡Hola, {$notifiable->nombre}!")
            ->line("Confirmamos tu reconfirmación del puesto en {$evento->nombre} ({$evento->lugar->nombre}) del día ".$inscripcion->fecha->format('d/m/Y').'.')
            ->line($inscripcion->puesto ? "Mesa N° {$inscripcion->puesto->numero}." : 'Traés tu propia mesa.')
            ->line('Recordá presentarte dentro de la hora de tolerancia desde que empieza la feria: si no llegás a tiempo, se anula tu lugar.')
            ->salutation('Feriaza');
    }
}
