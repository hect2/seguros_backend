<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewIncidentNotification extends Notification
{
    use Queueable;

    private $incident;

    /**
     * Create a new notification instance.
     */
    public function __construct($incident)
    {
        $this->incident = $incident;
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
        $incident = $this->incident;
        return [
            'incident_id' => $this->incident->id,
            'title' => $this->incident->title,
            'district_id' => $this->incident->district_id,
            'district_code' => optional($this->incident->district)->code,
            'user_reported' => optional($this->incident->userReported)->name,
            'criticity_slug' => optional($this->incident->criticidad)->slug,
            'criticity_name' => optional($this->incident->criticidad)->name,
            'message' => "Se ha creado un nuevo incidente en tu distrito.",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'incident_id' => $this->incident->id,
            'title' => $this->incident->title,
            'district_id' => $this->incident->district_id,
            'message' => "Se ha creado un nuevo incidente en tu distrito.",
        ]);
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
