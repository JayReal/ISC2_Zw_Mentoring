<x-layouts.app title="Dashboard">
    <div class="page-shell py-7 sm:py-12">
        @if(session('status'))<div class="mb-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-bold text-green-900">{{ session('status') }}</div>@endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="section-kicker">Participant workspace</p><h1 class="mt-1 text-3xl font-extrabold tracking-tight">Welcome, {{ auth()->user()->name }}</h1></div>
            <span class="w-fit rounded-full bg-isc2-gray px-3 py-1.5 text-xs font-bold text-neutral-700">Intake: {{ str($profile->intake_status)->replace('_',' ')->title() }}</span>
        </div>

        <div class="mt-5 grid gap-4 sm:mt-6 sm:gap-5 lg:grid-cols-[1.35fr_.65fr]">
            <section class="panel p-4 sm:p-6">
                <div class="flex items-center gap-3 border-b border-neutral-200 pb-4"><span class="grid size-9 place-items-center rounded-full bg-isc2-green text-sm font-extrabold text-white">1</span><div><p class="text-xs font-bold text-neutral-500 uppercase">Current stage</p><h2 class="text-lg font-extrabold">{{ $profile->intake_status !== 'complete' ? 'Complete your participant profile' : 'Programme review' }}</h2></div></div>
                @if($profile->intake_status !== 'complete')
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-neutral-600">Share your goals, interests and availability so the programme team can identify a suitable pathway and cluster.</p>
                    <a class="button-primary mt-5" href="{{ route('intake.edit') }}">Complete profile</a>
                @else
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-neutral-600">Your profile is ready for human review. A mentoring proposal will appear here before it is shared with a potential mentor.</p>
                    <dl class="mt-5 grid gap-3 border-t border-neutral-100 pt-4 sm:grid-cols-2"><div><dt class="text-xs font-bold text-neutral-500 uppercase">Pathway</dt><dd class="mt-1 text-sm font-semibold">{{ str($profile->pathway)->replace('-',' ')->title() }}</dd></div><div><dt class="text-xs font-bold text-neutral-500 uppercase">Primary cluster</dt><dd class="mt-1 text-sm font-semibold">{{ $profile->primaryCluster?->name }}</dd></div></dl>
                @endif
            </section>

            <aside class="rounded-xl bg-isc2-dark-green p-4 text-white sm:p-6">
                <p class="text-xs font-bold tracking-widest text-[#9ac23c] uppercase">Programme safeguards</p>
                <ul class="mt-4 grid gap-3 text-sm leading-6 text-neutral-200"><li class="border-b border-white/10 pb-3">Both people confirm before a match begins.</li><li class="border-b border-white/10 pb-3">Rematching can be requested confidentially.</li><li>Employment and other outcomes are not guaranteed.</li></ul>
            </aside>
        </div>
    </div>
</x-layouts.app>
