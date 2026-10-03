<?php

namespace App\Models;

use Database\Factories\MentoringMeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mentoring_match_id', 'recorded_by', 'last_updated_by', 'meeting_on', 'duration_minutes', 'topics_discussed', 'decisions', 'next_actions', 'next_meeting_on'])]
class MentoringMeeting extends Model
{
    /** @use HasFactory<MentoringMeetingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['meeting_on' => 'date', 'next_meeting_on' => 'date'];
    }

    public function mentoringMatch(): BelongsTo
    {
        return $this->belongsTo(MentoringMatch::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
