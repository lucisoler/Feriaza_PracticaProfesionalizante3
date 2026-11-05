<?php

namespace App\Notifications;

use App\Models\Feriante;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso por correo al feriante de que el coordinador anuló su cuenta (RN-18, RN-19).
 */
class CuentaAnulada extends Notification
{
    use Queueable;

    public function __construct(public Feriante $feriante) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cuenta fue anulada')
            ->greeting("Hola, {$notifiable->nombre}.")
            ->line("El coordinador anuló tu cuenta: {$this->feriante->motivo_anulacion}.")
            ->line('Se liberaron tus mesas reservadas y no vas a poder inscribirte a nuevos eventos.')
            ->salutation('Feriaza');
    }
}
