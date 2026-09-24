<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterParticipantRequest;
use App\Models\Consent;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterParticipantRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'roles' => [$validated['participation_type']],
            ]);

            ParticipantProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $validated['date_of_birth'],
                'participation_type' => $validated['participation_type'],
            ]);

            foreach (['matching', 'privacy'] as $type) {
                Consent::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'granted' => true,
                    'policy_version' => 'pilot-1.0',
                    'recorded_at' => now(),
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('intake.edit');
    }
}
