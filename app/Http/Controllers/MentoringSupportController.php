<?php

namespace App\Http\Controllers;

use App\Models\MentoringMatch;
use App\Models\User;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class MentoringSupportController extends Controller
{
    public function store(Request $request, MentoringMatch $match): RedirectResponse
    {
        abort_unless($match->involves($request->user()) && in_array($match->status, ['active', 'pending-confirmation'], true), 403);
        $validated = $request->validate(['type' => ['required', 'in:support,rematch,safeguarding'], 'reason' => ['required', 'string', 'min:20', 'max:3000']]);
        $supportRequest = $match->supportRequests()->create([...$validated, 'requested_by' => $request->user()->id]);
        if ($validated['type'] === 'rematch') {
            $match->update(['status' => 'rematch-requested', 'rematch_requested_by' => $request->user()->id, 'rematch_reason' => $validated['reason']]);
        }
        $staff = User::where(fn ($query) => $query->whereJsonContains('roles', 'admin')->orWhereJsonContains('roles', 'programme-lead')->when($validated['type'] === 'safeguarding', fn ($roles) => $roles->orWhereJsonContains('roles', 'safeguarding')))->get();
        Notification::send($staff, new MatchActionNotification($match->id, $request->user()->name, 'submitted a confidential '.$validated['type'].' request', 'A new request requires authorised staff review.'));

        return back()->with('status', 'Your confidential request has been sent to authorised programme staff.');
    }
}
