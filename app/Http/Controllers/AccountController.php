<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.edit', [
            'accountRequests' => $request->user()->participantRequests()->latest()->limit(5)->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'current_password' => [Rule::requiredIf(fn (): bool => $request->string('email')->toString() !== $user->email), 'nullable', 'current_password'],
        ]);
        $emailChanged = $validated['email'] !== $user->email;
        $before = $user->only(['name', 'email']);

        $user->fill(['name' => $validated['name'], 'email' => $validated['email']]);
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();

        AuditLog::record($request, 'account.details_updated', $user, [
            'before' => $before,
            'after' => $user->only(['name', 'email']),
        ]);

        if ($emailChanged) {
            event(new Registered($user));

            return redirect()->route('verification.notice')->with('status', 'Account details saved. Verify your new email address to continue.');
        }

        return back()->with('status', 'Account details saved.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);
        AuditLog::record($request, 'account.password_updated', $request->user());

        return back()->with('status', 'Password updated.');
    }
}
