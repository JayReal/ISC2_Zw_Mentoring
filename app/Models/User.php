<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'roles'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PROGRAMME_ROLE_OPTIONS = [
        'admin' => ['label' => 'Administrator', 'description' => 'Full programme access, including staff access management.'],
        'programme-lead' => ['label' => 'Programme lead', 'description' => 'Oversees participants, matching, cycles and programme delivery.'],
        'matching-team' => ['label' => 'Matching team', 'description' => 'Reviews participants and manages mentoring matches.'],
        'cluster-lead' => ['label' => 'Cluster lead', 'description' => 'Maintains mentoring cluster information and views aggregate programme progress.'],
        'university-lead' => ['label' => 'University lead', 'description' => 'Views aggregate progress for university mentoring support.'],
        'technical-guild-lead' => ['label' => 'Technical guild lead', 'description' => 'Maintains specialist cluster information and views aggregate progress.'],
        'safeguarding' => ['label' => 'Safeguarding', 'description' => 'Reviews participant records and handles restricted support matters.'],
        'reporting-lead' => ['label' => 'Reporting lead', 'description' => 'Views aggregate programme measures without participant details.'],
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
        ];
    }

    public function participantProfile(): HasOne
    {
        return $this->hasOne(ParticipantProfile::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    public function participantRequests(): HasMany
    {
        return $this->hasMany(ParticipantRequest::class);
    }

    public function hasAnyRole(array $roles): bool
    {
        return count(array_intersect($this->roles ?? [], $roles)) > 0;
    }

    public function isProgrammeStaff(): bool
    {
        return $this->hasAnyRole(self::programmeRoles());
    }

    /**
     * @return array<int, string>
     */
    public static function programmeRoles(): array
    {
        return array_keys(self::PROGRAMME_ROLE_OPTIONS);
    }

    public function canReviewParticipants(): bool
    {
        return $this->hasAnyRole(['admin', 'programme-lead', 'matching-team', 'safeguarding']);
    }

    public function canUseAttentionQueue(): bool
    {
        return $this->hasAnyRole(['admin', 'programme-lead', 'matching-team', 'safeguarding']);
    }

    public function canViewProgrammeOperations(): bool
    {
        return $this->hasAnyRole(['admin', 'programme-lead']);
    }

    public function mentoringAsMentor(): HasMany
    {
        return $this->hasMany(MentoringMatch::class, 'mentor_id');
    }

    public function mentoringAsMentee(): HasMany
    {
        return $this->hasMany(MentoringMatch::class, 'mentee_id');
    }
}
