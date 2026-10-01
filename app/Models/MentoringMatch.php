<?php

namespace App\Models;

use Database\Factories\MentoringMatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['programme_cycle_id', 'mentor_id', 'mentee_id', 'cluster_id', 'proposed_by', 'tier', 'status', 'compatibility_score', 'rationale', 'override_reason', 'mentor_confirmed_at', 'mentee_confirmed_at', 'started_at', 'closed_at'])]
class MentoringMatch extends Model
{
    /** @use HasFactory<MentoringMatchFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['mentor_confirmed_at' => 'datetime', 'mentee_confirmed_at' => 'datetime', 'started_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function programmeCycle(): BelongsTo
    {
        return $this->belongsTo(ProgrammeCycle::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }
}
