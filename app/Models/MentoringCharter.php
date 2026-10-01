<?php

namespace App\Models;

use Database\Factories\MentoringCharterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mentoring_match_id', 'meeting_cadence', 'communication_method', 'response_expectations', 'cancellation_expectations', 'confidentiality_boundaries', 'escalation_route', 'review_on', 'closure_on', 'last_updated_by', 'mentor_confirmed_at', 'mentee_confirmed_at'])]
class MentoringCharter extends Model
{
    /** @use HasFactory<MentoringCharterFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['review_on' => 'date', 'closure_on' => 'date', 'mentor_confirmed_at' => 'datetime', 'mentee_confirmed_at' => 'datetime'];
    }

    public function mentoringMatch(): BelongsTo
    {
        return $this->belongsTo(MentoringMatch::class);
    }
}
