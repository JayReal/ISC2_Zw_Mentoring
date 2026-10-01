<?php

namespace App\Models;

use Database\Factories\MentoringGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['mentoring_match_id', 'created_by', 'last_updated_by', 'title', 'description', 'status', 'target_date', 'discussed_at', 'agreed_at', 'completed_at'])]
class MentoringGoal extends Model
{
    /** @use HasFactory<MentoringGoalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['target_date' => 'date', 'discussed_at' => 'datetime', 'agreed_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function mentoringMatch(): BelongsTo
    {
        return $this->belongsTo(MentoringMatch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(GoalMilestone::class)->orderBy('due_on');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(MatchActivity::class)->latest();
    }
}
