<?php

namespace App\Http\Controllers;

use App\Models\MatchActivity;
use App\Models\MentoringMatch;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MentoringClosureController extends Controller
{
    public function update(Request $request, MentoringMatch $match): RedirectResponse
    {
        $this->authorise($request, $match);
        $validated = $request->validate([
            'reason' => ['required', 'in:planned,goal-achieved,changed-circumstances,other'],
            'summary' => ['required', 'string', 'min:20', 'max:2000'],
            'next_steps' => ['nullable', 'string', 'max:1500'],
        ]);
        $confirmationColumn = (int) $request->user()->id === (int) $match->mentor_id ? 'mentor_confirmed_at' : 'mentee_confirmed_at';
        $closure = $match->closure()->firstOrNew();
        $closure->fill([
            ...$validated,
            'initiated_by' => $closure->initiated_by ?: $request->user()->id,
            'last_updated_by' => $request->user()->id,
            'mentor_confirmed_at' => null,
            'mentee_confirmed_at' => null,
            $confirmationColumn => now(),
        ])->save();

        $message = 'Prepared a closure summary for review. The relationship remains active until both participants confirm it.';
        MatchActivity::create(['mentoring_match_id' => $match->id, 'user_id' => $request->user()->id, 'type' => 'closure_prepared', 'body' => $message]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'prepared a mentoring closure summary', 'Review the shared summary and confirm it if it reflects your final discussion.'));
        $match->update(['last_activity_at' => now()]);

        return back()->with('status', 'Closure summary saved and shared for confirmation.');
    }

    public function confirm(Request $request, MentoringMatch $match): RedirectResponse
    {
        $this->authorise($request, $match);
        $closure = $match->closure()->firstOrFail();
        $confirmationColumn = (int) $request->user()->id === (int) $match->mentor_id ? 'mentor_confirmed_at' : 'mentee_confirmed_at';
        $closure->update([$confirmationColumn => now()]);
        $closure->refresh();
        $complete = $closure->mentor_confirmed_at && $closure->mentee_confirmed_at;

        if ($complete) {
            $match->update(['status' => 'closed', 'closed_at' => now(), 'last_activity_at' => now()]);
        }

        $message = $complete ? 'Confirmed the closure summary. This mentoring relationship is now closed.' : 'Confirmed the closure summary. The other participant still needs to confirm it.';
        MatchActivity::create(['mentoring_match_id' => $match->id, 'user_id' => $request->user()->id, 'type' => 'closure_confirmed', 'body' => $message]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'confirmed the mentoring closure summary', $message));

        return back()->with('status', $complete ? 'Mentoring relationship closed.' : 'Your closure confirmation has been recorded.');
    }

    private function authorise(Request $request, MentoringMatch $match): void
    {
        abort_unless($match->involves($request->user()) && $match->status === 'active', 403);
    }
}
