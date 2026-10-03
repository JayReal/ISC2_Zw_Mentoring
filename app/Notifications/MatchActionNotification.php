<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MatchActionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $matchId,
        public string $actorName,
        public string $action,
        public string $message,
        public bool $sendEmail = true,
        public string $category = 'activity',
        public ?string $anchor = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->sendEmail ? ['database', 'mail'] : ['database'];
    }

    public function viaConnections(): array
    {
        return ['database' => 'sync', 'mail' => 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->category === 'reminder' ? 'Mentoring action reminder' : 'Mentoring workspace update')
            ->line($this->actorName.' '.$this->action.'.')
            ->line($this->message)
            ->action('Open mentoring workspace', $this->url())
            ->line('Please sign in to respond or update the shared plan.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return ['match_id' => $this->matchId, 'actor_name' => $this->actorName, 'action' => $this->action, 'message' => $this->message, 'category' => $this->category, 'url' => $this->url()];
    }

    private function url(): string
    {
        return route('matches.show', $this->matchId).($this->anchor ? '#'.$this->anchor : '');
    }
}
