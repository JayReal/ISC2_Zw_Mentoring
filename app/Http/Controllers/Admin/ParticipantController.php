<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Cluster;
use App\Models\ParticipantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $profiles = ParticipantProfile::query()->with(['user', 'primaryCluster', 'programmeCycle'])
            ->when($request->filled('status'), fn ($query) => $query->where('intake_status', $request->string('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('participation_type', $request->string('type')))
            ->when($request->filled('search'), fn ($query) => $query->whereHas('user', fn ($users) => $users->where('name', 'like', '%'.$request->string('search').'%')->orWhere('email', 'like', '%'.$request->string('search').'%')))
            ->when($request->boolean('review_required'), fn ($query) => $query->whereNotNull('profile_review_requested_at'))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.participants.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ParticipantProfile $participant): View
    {
        $participant->load(['user.consents', 'primaryCluster', 'programmeCycle']);
        $secondaryClusters = Cluster::query()
            ->whereKey($participant->secondary_cluster_ids ?? [])
            ->orderBy('display_order')
            ->get();
        $reviewChangedFields = [];
        $reviewAuditId = null;

        if ($participant->profile_review_requested_at) {
            $auditQuery = AuditLog::query()
                ->where('subject_type', $participant->getMorphClass())
                ->where('subject_id', $participant->getKey());
            $lastReviewAuditId = (clone $auditQuery)->where('action', 'participant.reviewed')->max('id') ?? 0;
            $pendingProfileUpdates = (clone $auditQuery)
                ->where('action', 'participant.profile_updated')
                ->where('id', '>', $lastReviewAuditId)
                ->orderBy('id')
                ->get();
            $reviewChangedFields = $pendingProfileUpdates
                ->flatMap(fn (AuditLog $audit): array => data_get($audit->changes, 'fields', []))
                ->unique()
                ->values()
                ->all();
            $reviewAuditId = $pendingProfileUpdates->last()?->id;
        }

        return view('admin.participants.show', compact('participant', 'secondaryClusters', 'reviewChangedFields', 'reviewAuditId'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ParticipantProfile $participantProfile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParticipantProfile $participant): RedirectResponse
    {
        $validated = $request->validate([
            'intake_status' => ['required', 'in:not_started,in_progress,complete,under_review,approved,on_hold,closed'],
            'programme_cycle_id' => ['nullable', 'exists:programme_cycles,id'],
            'reason' => ['required', 'string', 'max:500'],
            'orientation_completed' => ['nullable', 'boolean'],
            'review_requested_at' => ['nullable', 'date'],
            'review_audit_id' => ['nullable', 'integer'],
        ]);

        $currentReviewToken = $participant->profile_review_requested_at?->format('Y-m-d H:i:s');
        $currentReviewAuditId = $currentReviewToken === null ? null : AuditLog::query()
            ->where('subject_type', $participant->getMorphClass())
            ->where('subject_id', $participant->getKey())
            ->where('action', 'participant.profile_updated')
            ->latest('id')
            ->value('id');
        $reviewChangedSincePageLoad = $currentReviewAuditId
            ? (int) ($validated['review_audit_id'] ?? 0) !== (int) $currentReviewAuditId
            : $currentReviewToken !== null && ($validated['review_requested_at'] ?? null) !== $currentReviewToken;

        if ($reviewChangedSincePageLoad) {
            throw ValidationException::withMessages([
                'review_requested_at' => 'This profile changed after you opened it. Refresh the page and review the newest information before saving.',
            ]);
        }
        $before = $participant->only(['intake_status', 'programme_cycle_id', 'profile_review_requested_at']);
        $participant->update([
            'intake_status' => $validated['intake_status'],
            'programme_cycle_id' => $validated['programme_cycle_id'],
            'profile_review_requested_at' => null,
        ]);
        if (in_array($participant->participation_type, ['mentor', 'both'], true) && $request->has('orientation_completed')) {
            $participant->update(['mentor_orientation_completed_at' => $request->boolean('orientation_completed') ? ($participant->mentor_orientation_completed_at ?? now()) : null]);
        }
        AuditLog::record($request, 'participant.reviewed', $participant, ['before' => $before, 'after' => $participant->only(array_keys($before))], $validated['reason']);

        return back()->with('status', 'Participant review saved.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParticipantProfile $participantProfile)
    {
        //
    }
}
