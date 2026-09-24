<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $profile = auth()->user()->participantProfile()->with('primaryCluster')->firstOrFail();

        return view('dashboard', compact('profile'));
    }
}
