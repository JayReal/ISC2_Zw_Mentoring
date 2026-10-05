<?php

namespace App\Http\Controllers;

use App\Models\MatchActivity;
use App\Models\MentoringMatch;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MatchActivityController extends Controller
{
    public function store(Request $request, MentoringMatch $match): RedirectResponse
    {
        abort_unless($match->involves($request->user()) && $match->status === 'active', 403);
        $validated = $request->validate(['body' => ['required', 'string', 'max:3000'], 'mentoring_goal_id' => ['nullable', 'exists:mentoring_goals,id']]);
        if (! empty($validated['mentoring_goal_id'])) {
            abort_unless($match->goals()->whereKey($validated['mentoring_goal_id'])->exists(), 422);
        }
        MatchActivity::create([...$validated, 'mentoring_match_id' => $match->id, 'user_id' => $request->user()->id, 'type' => 'comment']);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'posted a shared update', $validated['body'], false, 'note', 'notes'));
        $match->update(['last_activity_at' => now()]);

        return back()->with('status', 'Your update was shared and the other participant was notified.');
    }
}
