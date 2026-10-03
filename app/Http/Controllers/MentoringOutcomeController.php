<?php

namespace App\Http\Controllers;

use App\Models\MentoringMatch;
use Illuminate\View\View;

class MentoringOutcomeController extends Controller
{
    public function __invoke(MentoringMatch $match): View
    {
        $user = auth()->user();
        abort_unless($match->involves($user) || $user->hasAnyRole(['admin', 'programme-lead']), 403);
        $match->load(['mentor', 'mentee', 'cluster', 'programmeCycle', 'goals.milestones.owner', 'closure']);
        abort_unless($match->status === 'closed' && $match->closure?->mentor_confirmed_at && $match->closure?->mentee_confirmed_at, 404);

        $milestones = $match->goals->flatMap->milestones;
        $summary = [
            'recordId' => 'ISC2-ZW-MENT-'.str_pad((string) $match->id, 6, '0', STR_PAD_LEFT),
            'goalCount' => $match->goals->count(),
            'completedGoalCount' => $match->goals->where('status', 'completed')->count(),
            'milestoneCount' => $milestones->count(),
            'completedMilestoneCount' => $milestones->where('status', 'completed')->count(),
            'durationDays' => $match->started_at && $match->closed_at ? (int) $match->started_at->diffInDays($match->closed_at) : null,
        ];

        return view('matches.outcome', compact('match', 'summary'));
    }
}
