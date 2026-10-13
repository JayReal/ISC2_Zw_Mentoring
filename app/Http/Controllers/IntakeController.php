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
        $profileData = $request->safe()->only([
            'pathway',
            'primary_cluster_id',
            'secondary_cluster_ids',
            'goals',
            'experience_summary',
            'preferred_language',
            'preferred_format',
            'availability',
            'mentoring_styles',
            'mentoring_style',
            'university_context',
            'accessibility_needs',
            'conflict_declarations',
        ]);
        $profileData['secondary_cluster_ids'] = array_map('intval', $profileData['secondary_cluster_ids'] ?? []);
        $profileData['mentoring_styles'] = array_values($profileData['mentoring_styles'] ?? []);

        if ($request->validated('university_context_status') === 'not-applicable') {
            $profileData['university_context'] = null;
        }

        if ($request->validated('conflict_status') === 'none') {
            $profileData['conflict_declarations'] = null;
        }

        $profile->update([
            ...$profileData,
            'intake_status' => 'complete',
            'completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Your profile is ready for programme review.');
    }
}
