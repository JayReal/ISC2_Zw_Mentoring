<?php

namespace App\Http\Controllers;

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

        return view('dashboard', compact('profile'));
    }
}
