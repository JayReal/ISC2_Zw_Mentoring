<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipantRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $participantName, public string $requestType) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function viaConnections(): array
    {
        return ['database' => 'sync', 'mail' => 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Participant account request requires review')
            ->line($this->participantName.' submitted a '.$this->label().' request.')
            ->action('Review attention queue', route('admin.attention'))
            ->line('Please review the request before changing or deleting any programme records.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'actor_name' => $this->participantName,
            'action' => 'submitted a '.$this->label().' request',
            'message' => 'Authorised programme staff need to review this account and privacy request.',
            'category' => 'account',
            'url' => route('admin.attention'),
            'link_label' => 'Review attention queue',
        ];
    }

    private function label(): string
    {
        return match ($this->requestType) {
            'data-copy' => 'personal data copy',
            'deletion' => 'data deletion',
            default => 'programme withdrawal',
        };
    }
}
