<x-admin.shell title="Programme overview">
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach(['participants'=>'Participants','incomplete'=>'Incomplete intake','ready'=>'Ready for review','proposedMatches'=>'Match proposals','activeMatches'=>'Active matches'] as $key=>$label)
            <div class="panel p-4"><p class="text-xs font-bold text-neutral-500 uppercase">{{ $label }}</p><p class="mt-2 text-3xl font-extrabold">{{ $metrics[$key] }}</p></div>
        @endforeach
    </div>
    <div class="mt-6 grid gap-5 lg:grid-cols-[1.35fr_.65fr]">
        <section class="panel overflow-hidden"><div class="flex items-center justify-between border-b p-4 sm:p-5"><div><h2 class="font-extrabold">Participant review queue</h2><p class="text-sm text-neutral-500">Newest registrations and intake activity</p></div><a class="text-sm font-bold text-isc2-green" href="{{ route('admin.participants.index') }}">View all</a></div>
            <div class="divide-y">@forelse($recentParticipants as $profile)<a class="flex items-center justify-between gap-3 p-4 hover:bg-neutral-50" href="{{ route('admin.participants.show',$profile) }}"><div><p class="font-bold">{{ $profile->user->name }}</p><p class="text-sm text-neutral-500">{{ ucfirst($profile->participation_type) }} · {{ $profile->primaryCluster?->name ?? 'Cluster not selected' }}</p></div><span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold">{{ str($profile->intake_status)->replace('_',' ')->title() }}</span></a>@empty<p class="p-5 text-sm text-neutral-500">No participants yet.</p>@endforelse</div>
        </section>
        <aside class="space-y-5"><section class="rounded-xl bg-isc2-dark-green p-5 text-white"><p class="text-xs font-bold tracking-widest text-[#9ac23c] uppercase">Current cycle</p><h2 class="mt-2 text-xl font-extrabold">{{ $activeCycle?->name ?? 'No active cycle' }}</h2><p class="mt-2 text-sm text-neutral-200">{{ $activeCycle ? $activeCycle->starts_on->format('j M Y').' – '.$activeCycle->ends_on->format('j M Y') : 'Create or activate a programme cycle before assigning participants.' }}</p></section>
            <section class="panel p-5"><h2 class="font-extrabold">Recent decisions</h2><div class="mt-3 space-y-3">@forelse($recentActivity as $activity)<div class="border-l-2 border-isc2-green pl-3"><p class="text-sm font-bold">{{ str($activity->action)->replace('.',' ')->title() }}</p><p class="text-xs text-neutral-500">{{ $activity->actor?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}</p></div>@empty<p class="text-sm text-neutral-500">No administrative decisions recorded.</p>@endforelse</div></section></aside>
    </div>
</x-admin.shell>
