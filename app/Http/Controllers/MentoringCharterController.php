<?php

namespace App\Http\Controllers;

use App\Models\MentoringMatch;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MentoringCharterController extends Controller
{
    public function update(Request $request, MentoringMatch $match): RedirectResponse
    {
        $this->authorise($request, $match);
        $validated = $request->validate(['meeting_cadence' => ['required', 'string', 'max:120'], 'communication_method' => ['required', 'string', 'max:120'], 'response_expectations' => ['required', 'string', 'max:1000'], 'cancellation_expectations' => ['required', 'string', 'max:1000'], 'confidentiality_boundaries' => ['required', 'string', 'max:1500'], 'escalation_route' => ['nullable', 'string', 'max:1000'], 'review_on' => ['required', 'date'], 'closure_on' => ['required', 'date', 'after:review_on']]);
        $isMentor = (int) $request->user()->id === (int) $match->mentor_id;
        $charter = $match->charter()->updateOrCreate([], [...$validated, 'last_updated_by' => $request->user()->id, 'mentor_confirmed_at' => $isMentor ? now() : null, 'mentee_confirmed_at' => $isMentor ? null : now()]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'updated the mentoring charter', 'Review the shared expectations and confirm if you agree.'));

        return back()->with('status', 'Charter saved. The other participant has been asked to confirm it.');
    }

    public function confirm(Request $request, MentoringMatch $match): RedirectResponse
    {
        $this->authorise($request, $match);
        $charter = $match->charter()->firstOrFail();
        $charter->update([(int) $request->user()->id === (int) $match->mentor_id ? 'mentor_confirmed_at' : 'mentee_confirmed_at' => now()]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'confirmed the mentoring charter', 'The shared expectations have been confirmed.'));

        return back()->with('status', 'Charter confirmation recorded.');
    }

    private function authorise(Request $request, MentoringMatch $match): void
    {
        abort_unless($match->involves($request->user()) && $match->status === 'active', 403);
    }
}
