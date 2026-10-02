<?php

namespace App\Http\Controllers;

use App\Models\MatchActivity;
use App\Models\MentoringMatch;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MatchConfirmationController extends Controller
{
    public function update(Request $request, MentoringMatch $match): RedirectResponse
    {
        abort_unless($match->involves($request->user()), 403);
        abort_unless(in_array($match->status, ['proposed', 'pending-confirmation'], true), 409);
        if ($match->expires_at?->isPast()) {
            $match->update(['status' => 'expired']);
            abort(409, 'This match proposal has expired and requires programme review.');
        }
        $validated = $request->validate(['decision' => ['required', 'in:accept,decline'], 'reason' => ['nullable', 'required_if:decision,decline', 'string', 'max:1000']]);
        $isMentor = (int) $request->user()->getKey() === (int) $match->mentor_id;

        if ($validated['decision'] === 'decline') {
            $match->update(['status' => 'declined', 'declined_by' => $request->user()->id, 'decline_reason' => $validated['reason'], 'last_activity_at' => now()]);
            $message = 'declined the proposed match. Programme staff will review the next step.';
        } else {
            $match->update([$isMentor ? 'mentor_confirmed_at' : 'mentee_confirmed_at' => now(), 'status' => 'pending-confirmation', 'last_activity_at' => now()]);
            $match->refresh();
            if ($match->mentor_confirmed_at && $match->mentee_confirmed_at) {
                $match->update(['status' => 'active', 'started_at' => now()]);
            }
            $message = $match->status === 'active' ? 'accepted the match. Your mentoring workspace is now active.' : 'accepted the match. Your confirmation is still required.';
        }

        MatchActivity::create(['mentoring_match_id' => $match->id, 'user_id' => $request->user()->id, 'type' => 'confirmation', 'body' => $message]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, $validated['decision'] === 'accept' ? 'accepted the proposed match' : 'declined the proposed match', $message));

        return back()->with('status', 'Your decision has been recorded.');
    }
}
