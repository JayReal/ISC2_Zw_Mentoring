@php
    $openActions = $match->goals
        ->flatMap->milestones
        ->where('status', '!=', 'completed')
        ->sortBy(fn ($milestone) => $milestone->due_on?->timestamp ?? PHP_INT_MAX)
        ->take(4);
@endphp

<section class="panel mt-7 overflow-hidden" aria-labelledby="progress-heading">
    <div class="panel-header">
        <div>
            <p class="section-kicker">Progress and accountability</p>
            <h2 id="progress-heading" class="mt-1 text-xl font-extrabold">Shared progress at a glance</h2>
            <p class="mt-1 text-sm leading-6 text-neutral-600">Completion is based on the milestones you have recorded together.</p>
        </div>
        <span class="status-badge">{{ $workspace['progressPercentage'] }}% complete</span>
    </div>

    <div class="p-5 sm:p-6">
        <div class="h-2.5 overflow-hidden rounded-full bg-neutral-200" role="progressbar" aria-label="Milestone completion" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $workspace['progressPercentage'] }}">
            <div class="h-full rounded-full bg-isc2-green transition-all" style="width: {{ $workspace['progressPercentage'] }}%"></div>
        </div>

        <dl class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-neutral-50 p-4"><dt class="text-xs font-bold text-neutral-500 uppercase">Open actions</dt><dd class="mt-1 text-2xl font-extrabold">{{ $workspace['openMilestoneCount'] }}</dd></div>
            <div class="rounded-xl bg-neutral-50 p-4"><dt class="text-xs font-bold text-neutral-500 uppercase">Assigned to you</dt><dd class="mt-1 text-2xl font-extrabold">{{ $workspace['ownedByMeMilestoneCount'] }}</dd></div>
            <div class="rounded-xl bg-neutral-50 p-4"><dt class="text-xs font-bold text-neutral-500 uppercase">Due within 7 days</dt><dd class="mt-1 text-2xl font-extrabold">{{ $workspace['dueSoonMilestoneCount'] }}</dd></div>
            <div class="rounded-xl {{ $workspace['blockedMilestoneCount'] ? 'bg-red-50 text-red-900' : 'bg-neutral-50' }} p-4"><dt class="text-xs font-bold uppercase {{ $workspace['blockedMilestoneCount'] ? 'text-red-700' : 'text-neutral-500' }}">Blocked</dt><dd class="mt-1 text-2xl font-extrabold">{{ $workspace['blockedMilestoneCount'] }}</dd></div>
        </dl>

        <div class="mt-6 border-t border-neutral-200 pt-5">
            <div class="flex flex-wrap items-end justify-between gap-2"><div><h3 class="font-extrabold">Next actions</h3><p class="mt-1 text-sm text-neutral-600">The nearest open commitments across your shared goals.</p></div><a href="#notes" class="text-sm font-bold text-isc2-green hover:underline">Open full plan &darr;</a></div>
            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                @forelse($openActions as $milestone)
                    <div class="rounded-xl border {{ $milestone->status === 'blocked' || ($milestone->due_on?->isPast()) ? 'border-red-200 bg-red-50/60' : 'border-neutral-200 bg-white' }} p-4">
                        <div class="flex items-start justify-between gap-4"><div>
                            <p class="text-sm font-extrabold">{{ $milestone->title }}</p>
                            <p class="mt-1 text-xs text-neutral-600">{{ $milestone->goal->title }}</p>
                            <p class="mt-2 text-xs font-bold {{ $milestone->due_on?->isPast() ? 'text-red-700' : 'text-neutral-500' }}">{{ $milestone->owner?->name ?? 'Shared action' }} &middot; {{ $milestone->due_on ? ($milestone->due_on->isPast() ? 'Overdue '.$milestone->due_on->diffForHumans() : 'Due '.$milestone->due_on->format('j M Y')) : 'No due date' }}</p>
                        </div><span class="status-badge">{{ str($milestone->status)->replace('-', ' ')->title() }}</span></div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-neutral-300 p-5 text-sm text-neutral-600 lg:col-span-2">Add milestones beneath a shared goal to turn the mentoring plan into clear, owned actions.</div>
                @endforelse
            </div>
        </div>

    </div>
</section>
