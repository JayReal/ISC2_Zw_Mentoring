<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Cluster;
use App\Models\MentoringMatch;
use App\Models\ProgrammeCycle;
use App\Models\User;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $matches = MentoringMatch::with(['mentor', 'mentee', 'cluster', 'programmeCycle'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.matches.index', compact('matches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.matches.create', [
            'mentors' => User::where(fn ($query) => $query->whereJsonContains('roles', 'mentor')->orWhereJsonContains('roles', 'both'))
                ->whereHas('participantProfile', fn ($query) => $query->whereNotNull('mentor_orientation_completed_at')->where('mentor_availability_status', 'available'))
                ->orderBy('name')->get(),
            'mentees' => User::where(fn ($query) => $query->whereJsonContains('roles', 'mentee')->orWhereJsonContains('roles', 'both'))->orderBy('name')->get(),
            'clusters' => Cluster::where('is_active', true)->orderBy('display_order')->get(),
            'cycles' => ProgrammeCycle::orderByDesc('starts_on')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'programme_cycle_id' => ['nullable', 'exists:programme_cycles,id'], 'mentor_id' => ['required', 'different:mentee_id', 'exists:users,id'],
            'mentee_id' => ['required', 'exists:users,id'], 'cluster_id' => ['nullable', 'exists:clusters,id'], 'tier' => ['required', 'in:community,matched,specialist'],
            'compatibility_score' => ['nullable', 'integer', 'between:0,100'], 'rationale' => ['required', 'string', 'min:20', 'max:3000'],
        ]);
        $mentor = User::with('participantProfile')->findOrFail($validated['mentor_id']);
        $profile = $mentor->participantProfile;
        $reservedMatches = $mentor->mentoringAsMentor()->whereIn('status', ['proposed', 'pending-confirmation', 'active'])->count();
        if (! $profile || ! in_array($profile->participation_type, ['mentor', 'both'], true) || ! $profile->mentor_orientation_completed_at || $profile->mentor_availability_status !== 'available' || $reservedMatches >= $profile->mentor_capacity) {
            throw ValidationException::withMessages(['mentor_id' => 'This mentor is not currently ready and within capacity for a new proposal.']);
        }
        $match = MentoringMatch::create([...$validated, 'proposed_by' => $request->user()->id, 'status' => 'proposed']);
        AuditLog::record($request, 'match.proposed', $match, $validated);
        Notification::send([$match->mentor, $match->mentee], new MatchActionNotification($match->id, $request->user()->name, 'created a mentoring match proposal', 'Review the proposed match and record your decision.'));

        return redirect()->route('admin.matches.show', $match)->with('status', 'Match proposal created for human review.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MentoringMatch $match): View
    {
        $relations = ['mentor.participantProfile', 'mentee.participantProfile', 'cluster', 'programmeCycle', 'proposer', 'goals.milestones'];
        if (auth()->user()->hasAnyRole(['admin', 'programme-lead'])) {
            $relations[] = 'activities.user';
        }
        $match->load($relations);

        return view('admin.matches.show', compact('match'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MentoringMatch $mentoringMatch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MentoringMatch $match): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:proposed,pending-confirmation,active,declined,rematch-requested,closed'], 'reason' => ['required', 'string', 'max:1000']]);
        $before = $match->status;
        $updates = ['status' => $validated['status']];
        if ($validated['status'] === 'active') {
            $updates['started_at'] = now();
        }
        if ($validated['status'] === 'closed') {
            $updates['closed_at'] = now();
        }
        $match->update($updates);
        AuditLog::record($request, 'match.status_changed', $match, ['before' => $before, 'after' => $match->status], $validated['reason']);
        Notification::send([$match->mentor, $match->mentee], new MatchActionNotification($match->id, $request->user()->name, 'updated your mentoring match', 'Status: '.str($match->status)->replace('-', ' ')->title().'. '.$validated['reason']));

        return back()->with('status', 'Match status updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MentoringMatch $match): RedirectResponse
    {
        abort(405);
    }
}
