<?php

namespace App\Notifications;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso por correo al feriante de que ya puede reconfirmar su puesto y elegir
 * cómo va a pagar la mesa, 72 horas antes de la feria (RN-16).
 */
class RecordatorioReconfirmacion extends Notification
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
            ->subject("Reconfirmá tu puesto en {$evento->nombre}")
            ->greeting("¡Hola, {$notifiable->nombre}!")
            ->line("Ya podés reconfirmar tu puesto en {$evento->nombre} ({$evento->lugar->nombre}) del día ".$inscripcion->fecha->format('d/m/Y').'.')
            ->line($inscripcion->puesto ? "Mesa N° {$inscripcion->puesto->numero}." : 'Traés tu propia mesa.')
            ->line('Entrá a Mi cuenta y elegí cómo vas a pagarla: en efectivo o con tarjeta de débito.')
            ->action('Reconfirmar mi puesto', url('/mi-cuenta'))
            ->line('Si no reconfirmás antes de que empiece la feria, se libera tu lugar.')
            ->salutation('Feriaza');
    }
}
