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
        $profile = auth()->user()->participantProfile()->firstOrFail();

        return view('intake.edit', [
            'profile' => $profile,
            'clusters' => Cluster::query()->where('is_active', true)->orderBy('display_order')->get(),
        ]);
    }

    public function update(StoreIntakeRequest $request): RedirectResponse
    {
        $profile = $request->user()->participantProfile()->firstOrFail();

        $profile->update([
            ...$request->validated(),
            'intake_status' => 'complete',
            'completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Your profile is ready for programme review.');
    }
}
