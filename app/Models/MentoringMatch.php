<?php

namespace App\Models;

use Database\Factories\MentoringMatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['programme_cycle_id', 'mentor_id', 'mentee_id', 'cluster_id', 'proposed_by', 'tier', 'status', 'compatibility_score', 'rationale', 'override_reason', 'mentor_confirmed_at', 'mentee_confirmed_at', 'declined_by', 'decline_reason', 'rematch_requested_by', 'rematch_reason', 'expires_at', 'confirmation_reminded_at', 'started_at', 'last_activity_at', 'closed_at'])]
class MentoringMatch extends Model
{
    /** @use HasFactory<MentoringMatchFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['mentor_confirmed_at' => 'datetime', 'mentee_confirmed_at' => 'datetime', 'expires_at' => 'datetime', 'confirmation_reminded_at' => 'datetime', 'started_at' => 'datetime', 'last_activity_at' => 'datetime', 'closed_at' => 'datetime', 'decline_reason' => 'encrypted', 'rematch_reason' => 'encrypted'];
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

    public function goals(): HasMany
    {
        return $this->hasMany(MentoringGoal::class)->orderBy('target_date');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(MatchActivity::class)->latest();
    }

    public function charter(): HasOne
    {
        return $this->hasOne(MentoringCharter::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(MentoringCheckIn::class);
    }

    public function supportRequests(): HasMany
    {
        return $this->hasMany(MentoringSupportRequest::class);
    }

    public function involves(User $user): bool
    {
        return in_array((int) $user->getKey(), [(int) $this->mentor_id, (int) $this->mentee_id], true);
    }

    public function counterpartFor(User $user): User
    {
        return (int) $user->getKey() === (int) $this->mentor_id ? $this->mentee : $this->mentor;
    }
}
