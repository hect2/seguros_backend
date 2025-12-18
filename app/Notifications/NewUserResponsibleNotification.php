<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserResponsibleNotification extends Notification
{
    use Queueable;

    private $employee;

    /**
     * Create a new notification instance.
     */
    public function __construct($employee)
    {
        $this->employee = $employee;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'employee_id' => $this->employee->id,
            'title' => 'Asignación de Usuario',
            'message' => "Se te ha asignado un usuario: "  . $this->employee->full_name,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'employee_id' => $this->employee->id,
            'title' => $this->employee->title,
            'message' => "Se te ha asignado un usuario.",
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
