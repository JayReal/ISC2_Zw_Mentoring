<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MentoringSupportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportRequestController extends Controller
{
    public function update(Request $request, MentoringSupportRequest $supportRequest): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:in-review,resolved,closed'], 'resolution' => ['required', 'string', 'max:3000']]);
        $supportRequest->update([...$validated, 'assigned_to' => $request->user()->id, 'resolved_at' => in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null]);
        AuditLog::record($request, 'support_request.updated', $supportRequest, ['status' => $validated['status']]);

        return back()->with('status', 'Support request updated.');
    }
}
