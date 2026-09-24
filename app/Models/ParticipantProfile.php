<?php

namespace App\Models;

use Database\Factories\ParticipantProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'programme_cycle_id', 'primary_cluster_id', 'date_of_birth', 'participation_type', 'pathway', 'goals', 'experience_summary', 'secondary_cluster_ids', 'availability', 'preferred_language', 'preferred_format', 'mentoring_style', 'university_context', 'accessibility_needs', 'conflict_declarations', 'intake_status', 'completed_at'])]
class ParticipantProfile extends Model
{
    /** @use HasFactory<ParticipantProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'secondary_cluster_ids' => 'array',
            'availability' => 'array',
            'accessibility_needs' => 'encrypted',
            'conflict_declarations' => 'encrypted',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryCluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class, 'primary_cluster_id');
    }

    public function programmeCycle(): BelongsTo
    {
        return $this->belongsTo(ProgrammeCycle::class);
    }
}
