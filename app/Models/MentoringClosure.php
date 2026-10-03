<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mentoring_match_id', 'initiated_by', 'last_updated_by', 'reason', 'summary', 'next_steps', 'mentor_confirmed_at', 'mentee_confirmed_at'])]
class MentoringClosure extends Model
{
    protected function casts(): array
    {
        return ['mentor_confirmed_at' => 'datetime', 'mentee_confirmed_at' => 'datetime'];
    }

    public function mentoringMatch(): BelongsTo
    {
        return $this->belongsTo(MentoringMatch::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
