<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ParticipantProfileUpdatedNotification extends Notification
{
    public function __construct(
        public int $participantProfileId,
        public string $participantName,
        public array $changedFields = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'actor_name' => $this->participantName,
            'action' => 'updated their participant profile after approval',
            'message' => 'Review the updated matching or participation information.'.($this->changedFields === [] ? '' : ' Updated areas: '.implode(', ', $this->changedFields).'.'),
            'category' => 'participant',
            'url' => route('admin.participants.show', $this->participantProfileId),
            'link_label' => 'Review participant',
        ];
    }
}
