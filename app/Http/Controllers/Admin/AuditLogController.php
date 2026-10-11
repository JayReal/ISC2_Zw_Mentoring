<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = AuditLog::query()
            ->with('actor:id,name,email')
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('actor', fn (Builder $actors): Builder => $actors
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%'));
            })
            ->when($validated['action'] ?? null, fn (Builder $query, string $action): Builder => $query->where('action', $action))
            ->when($validated['from'] ?? null, fn (Builder $query, string $from): Builder => $query->whereDate('created_at', '>=', $from))
            ->when($validated['to'] ?? null, fn (Builder $query, string $to): Builder => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit.index', [
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'filters' => $validated,
            'logs' => $logs,
        ]);
    }
}
