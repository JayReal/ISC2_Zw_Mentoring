<?php

namespace App\Http\Controllers;

use App\Models\MentoringMatch;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MentoringCheckInController extends Controller
{
    public function store(Request $request, MentoringMatch $match): RedirectResponse
    {
        abort_unless($match->involves($request->user()) && $match->status === 'active', 403);
        $validated = $request->validate(['meeting_held' => ['required', 'boolean'], 'usefulness_score' => ['required', 'integer', 'between:1,5'], 'needs_support' => ['required', 'boolean'], 'comment' => ['nullable', 'string', 'max:2000']]);
        $period = now()->startOfMonth()->toDateString();
        $checkIn = $match->checkIns()->where('user_id', $request->user()->id)->whereDate('period_month', $period)->firstOrNew();
        $checkIn->fill([...$validated, 'user_id' => $request->user()->id, 'period_month' => $period])->save();
        if ($validated['needs_support']) {
            $match->supportRequests()->firstOrCreate(['requested_by' => $request->user()->id, 'type' => 'support', 'status' => 'open'], ['reason' => $validated['comment'] ?: 'Participant requested support through the monthly check-in.']);
        }
        $match->update(['last_activity_at' => now()]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'completed a monthly check-in', 'Open the workspace to complete your own check-in.', false, 'check-in', 'charter'));

        return back()->with('status', 'Monthly check-in saved.');
    }
}
