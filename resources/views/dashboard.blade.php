<x-layouts.app title="Dashboard">
    <div class="page-shell py-9 sm:py-14">
        @if(session('status'))<div class="notice-success mb-6">{{ session('status') }}</div>@endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="section-kicker">Participant workspace</p><h1 class="page-heading mt-1.5">Welcome, {{ auth()->user()->name }}</h1></div>
            <span class="status-badge">Intake: {{ str($profile->intake_status)->replace('_',' ')->title() }}</span>
        </div>

        <div class="mt-7 grid gap-5 lg:grid-cols-[1.35fr_.65fr]">
            <section class="panel p-5 sm:p-7">
                <div class="flex items-center gap-3 border-b border-neutral-200 pb-4"><span class="grid size-9 place-items-center rounded-full bg-isc2-green text-sm font-extrabold text-white">1</span><div><p class="text-xs font-bold text-neutral-500 uppercase">Current stage</p><h2 class="text-lg font-extrabold">{{ $profile->intake_status !== 'complete' ? 'Complete your participant profile' : 'Programme review' }}</h2></div></div>
                @if($profile->intake_status !== 'complete')
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-neutral-600">Share your goals, interests and availability so the programme team can identify a suitable pathway and cluster.</p>
                    <a class="button-primary mt-5" href="{{ route('intake.edit') }}">Complete profile</a>
                @else
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-neutral-600">Your profile is ready for human review. A mentoring proposal will appear here before it is shared with a potential mentor.</p>
                    <dl class="mt-5 grid gap-3 border-t border-neutral-100 pt-4 sm:grid-cols-2"><div><dt class="text-xs font-bold text-neutral-500 uppercase">Pathway</dt><dd class="mt-1 text-sm font-semibold">{{ str($profile->pathway)->replace('-',' ')->title() }}</dd></div><div><dt class="text-xs font-bold text-neutral-500 uppercase">Primary cluster</dt><dd class="mt-1 text-sm font-semibold">{{ $profile->primaryCluster?->name }}</dd></div></dl>
                @endif
            </section>

            <aside class="rounded-2xl bg-isc2-dark-green p-5 text-white shadow-sm sm:p-7">
                <p class="text-xs font-bold tracking-widest text-[#9ac23c] uppercase">Programme safeguards</p>
                <ul class="mt-4 grid gap-3 text-sm leading-6 text-neutral-200"><li class="border-b border-white/10 pb-3">Both people confirm before a match begins.</li><li class="border-b border-white/10 pb-3">Rematching can be requested confidentially.</li><li>Employment and other outcomes are not guaranteed.</li></ul>
            </aside>
        </div>

        @if($matches->isNotEmpty())<section class="mt-6"><div class="flex items-end justify-between gap-4"><div><p class="section-kicker">Mentoring relationships</p><h2 class="mt-1 text-2xl font-extrabold">Your shared workspaces</h2></div><a class="text-sm font-bold text-isc2-green hover:underline" href="{{ route('matches.index') }}">View all</a></div><div class="mt-4 grid gap-4 md:grid-cols-2">@foreach($matches->take(4) as $match)@php($counterpart=$match->counterpartFor(auth()->user()))<a class="panel p-5 transition hover:border-isc2-green/40 hover:shadow-md" href="{{ route('matches.show',$match) }}"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold text-neutral-500 uppercase">{{ (int) auth()->id()===(int) $match->mentor_id ? 'Mentee' : 'Mentor' }}</p><h3 class="mt-1 text-lg font-extrabold">{{ $counterpart->name }}</h3></div><span class="status-badge">{{ str($match->status)->replace('-',' ')->title() }}</span></div></a>@endforeach</div></section>@endif
    </div>
</x-layouts.app>
