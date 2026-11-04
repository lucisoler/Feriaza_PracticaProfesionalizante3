<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Enlace por correo para que el feriante cree una contraseña nueva.
 */
class RestablecerContrasena extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/mi-cuenta/restablecer/' . $this->token . '?email=' . urlencode($notifiable->getEmailForPasswordReset()));
        $minutos = config('auth.passwords.feriantes.expire');

        return (new MailMessage)
            ->subject('Recuperá tu cuenta de Feriaza')
            ->greeting("Hola, {$notifiable->nombre}")
            ->line('Recibimos un pedido para cambiar la contraseña de tu cuenta.')
            ->action('Crear una contraseña nueva', $url)
            ->line("El enlace vence en {$minutos} minutos.")
            ->line('Si no lo pediste, ignorá este correo: tu contraseña no cambia.')
            ->salutation('Feriaza');
    }
}
