<?php

namespace App\Models;

use Database\Factories\MentoringCheckInFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mentoring_match_id', 'user_id', 'meeting_held', 'usefulness_score', 'needs_support', 'comment', 'period_month'])]
class MentoringCheckIn extends Model
{
    /** @use HasFactory<MentoringCheckInFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['meeting_held' => 'boolean', 'needs_support' => 'boolean', 'comment' => 'encrypted', 'period_month' => 'date'];
    }

    public function mentoringMatch(): BelongsTo
    {
        return $this->belongsTo(MentoringMatch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
