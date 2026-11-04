<?php

namespace App\Notifications;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso por correo al coordinador de que un feriante se inscribió (RF-15).
 */
class NuevaInscripcion extends Notification
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
        $feriante = $inscripcion->feriante;
        $evento = $inscripcion->evento;

        return (new MailMessage)
            ->subject("Nueva inscripción: {$feriante->nombre} {$feriante->apellido}")
            ->greeting('Nueva inscripción')
            ->line("{$feriante->nombre} {$feriante->apellido} se inscribió a {$evento->nombre} ({$evento->lugar->nombre}).")
            ->line('Día: ' . $inscripcion->fecha->format('d/m/Y'))
            ->line($inscripcion->puesto ? "Mesa N° {$inscripcion->puesto->numero}" : 'Sin mesa (trae la suya)')
            ->line("Categoría: {$feriante->categoria->nombre}")
            ->line("Teléfono: {$feriante->telefono} · Email: {$feriante->email}")
            ->action('Ver el evento', url("/coordinador/eventos/{$evento->id}?dia=" . $inscripcion->fecha->toDateString()))
            ->salutation('Feriaza');
    }
}
