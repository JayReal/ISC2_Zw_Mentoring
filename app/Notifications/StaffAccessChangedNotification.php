<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffAccessChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $actorName,
        public array $beforeRoles,
        public array $afterRoles,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        return ['database' => 'sync', 'mail' => 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your mentoring programme access changed')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->actorName.' updated your ISC2 Zimbabwe mentoring programme access.')
            ->line($this->summary());

        return $message
            ->action($this->afterRoles === [] ? 'Open participant dashboard' : 'Open programme workspace', $this->url())
            ->line('If you believe this change is incorrect, contact a programme administrator.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'actor_name' => $this->actorName,
            'action' => 'updated your programme staff access',
            'message' => $this->summary(),
            'category' => 'access',
            'url' => $this->url(),
            'link_label' => $this->afterRoles === [] ? 'Open participant dashboard' : 'Open programme workspace',
        ];
    }

    private function summary(): string
    {
        if ($this->afterRoles === []) {
            return 'Your programme staff access was removed. Any mentor or mentee participation remains unchanged.';
        }

        $labels = collect($this->afterRoles)
            ->map(fn (string $role): string => User::PROGRAMME_ROLE_OPTIONS[$role]['label'] ?? $role)
            ->implode(', ');

        return 'Your current programme roles are: '.$labels.'.';
    }

    private function url(): string
    {
        return $this->afterRoles === [] ? route('dashboard') : route('admin.dashboard');
    }
}
