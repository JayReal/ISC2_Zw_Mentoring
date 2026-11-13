<?php

namespace App\Http\Controllers;

use App\Rules\LinkedInProfileUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MentorReadinessController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->participantProfile()->firstOrFail();
        abort_unless(in_array($profile->participation_type, ['mentor', 'both'], true), 403);

        return view('mentor-readiness.edit', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $request->user()->participantProfile()->firstOrFail();
        abort_unless(in_array($profile->participation_type, ['mentor', 'both'], true), 403);
        $request->merge(['linkedin_profile_url' => LinkedInProfileUrl::prepare($request->input('linkedin_profile_url'))]);
        $validated = $request->validate(['mentor_expertise' => ['required', 'string', 'min:30', 'max:3000'], 'mentor_prerequisites' => ['nullable', 'string', 'max:2000'], 'linkedin_profile_url' => ['nullable', 'url:http,https', 'max:500', new LinkedInProfileUrl], 'mentor_capacity' => ['required', 'integer', 'between:1,10'], 'mentor_availability_status' => ['required', 'in:available,paused,at-capacity']]);
        if (filled($validated['linkedin_profile_url'] ?? null)) {
            $validated['linkedin_profile_url'] = LinkedInProfileUrl::canonicalize($validated['linkedin_profile_url']);
        }
        $profile->update($validated);

        return redirect()->route('dashboard')->with('status', 'Mentor readiness and capacity updated.');
    }
}
