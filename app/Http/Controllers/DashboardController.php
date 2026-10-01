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
        $matches = MentoringMatch::with(['mentor', 'mentee'])
            ->where(fn ($query) => $query->where('mentor_id', auth()->id())->orWhere('mentee_id', auth()->id()))
            ->latest()->get();

        return view('dashboard', compact('profile', 'matches'));
    }
}
