<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentoringMatch;
use App\Models\MentoringSupportRequest;
use App\Models\ParticipantProfile;
use Illuminate\View\View;

class AttentionQueueController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $canSeeConfidentialRequests = auth()->user()->hasAnyRole(['admin', 'programme-lead', 'safeguarding']);

        return view('admin.attention.index', [
            'incompleteProfiles' => ParticipantProfile::with('user')->whereNotIn('intake_status', ['complete', 'under_review', 'approved'])->latest()->limit(10)->get(),
            'mentorOrientation' => ParticipantProfile::with('user')->whereIn('participation_type', ['mentor', 'both'])->whereNull('mentor_orientation_completed_at')->latest()->limit(10)->get(),
            'pendingMatches' => MentoringMatch::with(['mentor', 'mentee'])->whereIn('status', ['proposed', 'pending-confirmation'])->latest()->limit(10)->get(),
            'inactiveMatches' => MentoringMatch::with(['mentor', 'mentee'])->where('status', 'active')->where(function ($query) {
                $query->where('last_activity_at', '<', now()->subDays(30))
                    ->orWhere(fn ($inactive) => $inactive->whereNull('last_activity_at')->where('started_at', '<', now()->subDays(30)));
            })->limit(10)->get(),
            'supportRequests' => $canSeeConfidentialRequests
                ? MentoringSupportRequest::with(['requester', 'mentoringMatch.mentor', 'mentoringMatch.mentee'])->whereIn('status', ['open', 'in-review'])->latest()->limit(10)->get()
                : collect(),
        ]);
    }
}
