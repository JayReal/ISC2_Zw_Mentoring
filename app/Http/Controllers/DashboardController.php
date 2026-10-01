<?php

namespace App\Http\Controllers;

use App\Models\MentoringMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View|RedirectResponse
    {
        if (auth()->user()->isProgrammeStaff() && ! auth()->user()->participantProfile()->exists()) {
            return redirect()->route('admin.dashboard');
        }

        $profile = auth()->user()->participantProfile()->with('primaryCluster')->firstOrFail();
        $matches = MentoringMatch::with(['mentor', 'mentee', 'goals.milestones'])
            ->where(fn ($query) => $query->where('mentor_id', auth()->id())->orWhere('mentee_id', auth()->id()))
            ->latest()->get();
        $actionMatch = $matches->first(fn (MentoringMatch $match) => in_array($match->status, ['proposed', 'pending-confirmation'], true) && ! ((int) $match->mentor_id === (int) auth()->id() ? $match->mentor_confirmed_at : $match->mentee_confirmed_at))
            ?? $matches->firstWhere('status', 'active');
        $intakeComplete = in_array($profile->intake_status, ['complete', 'under_review', 'approved'], true);

        return view('dashboard', compact('profile', 'matches', 'actionMatch', 'intakeComplete'));
    }
}
