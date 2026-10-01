<?php

namespace App\Models;

use Database\Factories\MatchActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mentoring_match_id', 'mentoring_goal_id', 'goal_milestone_id', 'user_id', 'type', 'body'])]
class MatchActivity extends Model
{
    /** @use HasFactory<MatchActivityFactory> */
    use HasFactory;

    public function mentoringMatch(): BelongsTo
    {
        return $this->belongsTo(MentoringMatch::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(MentoringGoal::class, 'mentoring_goal_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(GoalMilestone::class, 'goal_milestone_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
