<?php

namespace App\Models;

use Database\Factories\GoalMilestoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mentoring_goal_id', 'created_by', 'owner_id', 'completed_by', 'title', 'description', 'due_on', 'reminder_sent_at', 'status', 'completed_at'])]
class GoalMilestone extends Model
{
    /** @use HasFactory<GoalMilestoneFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['due_on' => 'date', 'reminder_sent_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(MentoringGoal::class, 'mentoring_goal_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
