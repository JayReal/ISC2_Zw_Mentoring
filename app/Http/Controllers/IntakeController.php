<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIntakeRequest;
use App\Models\Cluster;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IntakeController extends Controller
{
    public function edit(): View
    {
        return view('intake.edit', [
            'profile' => auth()->user()->participantProfile,
            'clusters' => Cluster::query()->where('is_active', true)->orderBy('display_order')->get(),
        ]);
    }

    public function update(StoreIntakeRequest $request): RedirectResponse
    {
        $request->user()->participantProfile->update([
            ...$request->validated(),
            'intake_status' => 'complete',
            'completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Your profile is ready for programme review.');
    }
}
