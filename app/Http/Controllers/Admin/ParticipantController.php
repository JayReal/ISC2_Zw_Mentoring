<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ParticipantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('admin.participants.show', compact('participant'));
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
        ]);
        $before = $participant->only(['intake_status', 'programme_cycle_id']);
        $participant->update(['intake_status' => $validated['intake_status'], 'programme_cycle_id' => $validated['programme_cycle_id']]);
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
