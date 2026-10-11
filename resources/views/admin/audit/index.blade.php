<x-admin.shell title="Audit history">
    <p class="lede">A read-only record of sensitive programme decisions and access changes.</p>
    <form method="GET" action="{{ route('admin.audit.index') }}" class="filter-bar mt-5">
        <div class="field"><label for="search">Staff member</label><input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email"></div>
        <div class="field"><label for="action">Action</label><select id="action" name="action"><option value="">All actions</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ str($action)->replace(['.', '_'], ' ')->title() }}</option>@endforeach</select></div>
        <div class="field"><label for="from">From</label><input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="field"><label for="to">To</label><input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="flex gap-2 lg:col-span-4"><button class="button-primary">Filter history</button><a class="button-secondary" href="{{ route('admin.audit.index') }}">Clear</a></div>
    </form>
    <div class="panel mt-5 overflow-x-auto"><table class="admin-table"><thead><tr><th>When</th><th>Staff member</th><th>Action</th><th>Record</th><th>Reason</th></tr></thead><tbody>@forelse($logs as $log)<tr><td class="whitespace-nowrap"><time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('j M Y, H:i') }}</time></td><td>{{ $log->actor?->name ?? 'System' }}<p class="text-xs text-neutral-500">{{ $log->actor?->email }}</p></td><td>{{ str($log->action)->replace(['.', '_'], ' ')->title() }}</td><td>{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td><td class="max-w-md text-neutral-600">{{ $log->reason ?: '—' }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-neutral-500">No audit entries match these filters.</td></tr>@endforelse</tbody></table></div>
    <div class="mt-6">{{ $logs->links() }}</div>
</x-admin.shell>
