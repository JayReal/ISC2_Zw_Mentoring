<?php

namespace App\Http\Controllers;

use App\Models\MentoringMatch;
use Illuminate\View\View;

class MatchWorkspaceController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $matches = MentoringMatch::with(['mentor', 'mentee', 'cluster'])
            ->where(fn ($query) => $query->where('mentor_id', $user->id)->orWhere('mentee_id', $user->id))
            ->latest()->get();

        return view('matches.index', compact('matches'));
    }

    public function show(MentoringMatch $match): View
    {
        abort_unless($match->involves(auth()->user()) || auth()->user()->hasAnyRole(['admin', 'programme-lead']), 403);
        $match->load(['mentor', 'mentee', 'cluster', 'goals.milestones.owner', 'goals.creator', 'meetings.recorder', 'activities.user', 'charter', 'checkIns' => fn ($query) => $query->where('user_id', auth()->id())->latest()]);

        return view('matches.show', compact('match'));
    }
}
