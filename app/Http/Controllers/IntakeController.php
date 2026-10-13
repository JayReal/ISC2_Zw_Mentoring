<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIntakeRequest;
use App\Models\AuditLog;
use App\Models\Cluster;
use App\Models\User;
use App\Notifications\ParticipantProfileUpdatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class IntakeController extends Controller
{
    /** @var array<string, string> */
    private const PROFILE_FIELD_LABELS = [
        'pathway' => 'Pathway',
        'primary_cluster_id' => 'Primary community',
        'secondary_cluster_ids' => 'Other communities',
        'goals' => 'Goals',
        'experience_summary' => 'Experience summary',
        'preferred_language' => 'Preferred language',
        'preferred_format' => 'Meeting format',
        'availability' => 'Availability',
        'mentoring_styles' => 'Working style preferences',
        'mentoring_style' => 'Additional working-style note',
        'university_context' => 'University or college context',
        'accessibility_needs' => 'Restricted participation information',
        'conflict_declarations' => 'Restricted participation information',
    ];

    public function edit(): View
    {
        $profile = auth()->user()->participantProfile()->firstOrFail();

        return view('intake.edit', [
            'profile' => $profile,
            'clusters' => Cluster::query()->where('is_active', true)->orderBy('display_order')->get(),
        ]);
    }

    public function update(StoreIntakeRequest $request): RedirectResponse
    {
        $profile = $request->user()->participantProfile()->firstOrFail();
        $profileData = $request->safe()->only([
            'pathway',
            'primary_cluster_id',
            'secondary_cluster_ids',
            'goals',
            'experience_summary',
            'preferred_language',
            'preferred_format',
            'availability',
            'mentoring_styles',
            'mentoring_style',
            'university_context',
            'accessibility_needs',
            'conflict_declarations',
        ]);
        $profileData['secondary_cluster_ids'] = array_map('intval', $profileData['secondary_cluster_ids'] ?? []);
        $profileData['mentoring_styles'] = array_values($profileData['mentoring_styles'] ?? []);

        if ($request->validated('university_context_status') === 'not-applicable') {
            $profileData['university_context'] = null;
        }

        if ($request->validated('conflict_status') === 'none') {
            $profileData['conflict_declarations'] = null;
        }

        $changedFields = collect($profileData)->filter(function (mixed $value, string $attribute) use ($profile): bool {
            $currentValue = $profile->{$attribute};

            return is_array($value)
                ? array_values($currentValue ?? []) !== array_values($value)
                : $currentValue !== $value;
        })->keys()->map(fn (string $attribute): string => self::PROFILE_FIELD_LABELS[$attribute] ?? str($attribute)->replace('_', ' ')->title())
            ->unique()->values()->all();
        $matchingInformationChanged = $changedFields !== [];
        $wasFirstSubmission = in_array($profile->intake_status, ['not_started', 'in_progress'], true);
        $wasApproved = $profile->intake_status === 'approved';
        $reviewWasAlreadyPending = $profile->profile_review_requested_at !== null;
        $hasActiveMatch = $request->user()->mentoringAsMentor()->where('status', 'active')->exists()
            || $request->user()->mentoringAsMentee()->where('status', 'active')->exists();
        $reviewRequested = ($wasApproved || $reviewWasAlreadyPending) && $matchingInformationChanged;
        $intakeStatus = match (true) {
            $wasFirstSubmission => 'complete',
            $reviewRequested && ! $hasActiveMatch => 'under_review',
            default => $profile->intake_status,
        };

        $profile->update([
            ...$profileData,
            'intake_status' => $intakeStatus,
            'completed_at' => $profile->completed_at ?? now(),
            'profile_review_requested_at' => $reviewRequested ? now() : $profile->profile_review_requested_at,
        ]);

        if ($reviewRequested) {
            AuditLog::record($request, 'participant.profile_updated', $profile, ['fields' => $changedFields], 'Participant updated matching information after approval.');

            if ($reviewWasAlreadyPending) {
                return redirect()->route('dashboard')->with('status', 'Your updates have been saved and remain flagged for programme review.');
            }

            $staff = User::query()
                ->where(function ($query): void {
                    $query->whereJsonContains('roles', 'admin')
                        ->orWhereJsonContains('roles', 'programme-lead')
                        ->orWhereJsonContains('roles', 'matching-team')
                        ->orWhereJsonContains('roles', 'safeguarding');
                })
                ->get();

            Notification::send($staff, new ParticipantProfileUpdatedNotification($profile->id, $request->user()->name, $changedFields));
        }

        $message = match (true) {
            $reviewRequested => 'Your updates have been saved and flagged for programme review.',
            $wasFirstSubmission => 'Your profile is ready for programme review.',
            default => 'Your profile changes have been saved.',
        };

        return redirect()->route('dashboard')->with('status', $message);
    }
}
