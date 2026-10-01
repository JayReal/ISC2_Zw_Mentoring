<?php

namespace App\Models;

use Database\Factories\MentoringSupportRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mentoring_match_id', 'requested_by', 'type', 'reason', 'status', 'assigned_to', 'resolution', 'resolved_at'])]
class MentoringSupportRequest extends Model
{
    /** @use HasFactory<MentoringSupportRequestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['reason' => 'encrypted', 'resolution' => 'encrypted', 'resolved_at' => 'datetime'];
    }

    public function mentoringMatch(): BelongsTo
    {
        return $this->belongsTo(MentoringMatch::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
