<x-layouts.app title="Mentorship and Professional Growth">
    <section class="bg-isc2-dark-green text-white">
        <div class="page-shell grid gap-8 py-12 md:grid-cols-[1.12fr_.88fr] md:items-center md:py-16 lg:gap-16 lg:py-18">
            <div>
                <p class="text-xs font-semibold tracking-[0.15em] text-[#9ac23c] uppercase">One community. Many pathways.</p>
                <h1 class="mt-4 max-w-3xl text-4xl leading-[1.08] font-semibold tracking-[-0.03em] sm:text-5xl">Practical guidance for your next professional step.</h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-neutral-200 sm:text-lg">Structured mentoring for Zimbabwean students, career changers, leaders, cybersecurity practitioners and community contributors aged 18 and over.</p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">@auth<a href="{{ route('dashboard') }}" class="button-primary">Go to your dashboard</a>@else<a href="{{ route('register') }}" class="button-primary">Register as a mentor or mentee</a>@endauth<a href="{{ route('programme') }}" class="button-secondary border-white/35 bg-transparent text-white hover:bg-white/10">Explore the programme</a></div>
                <p class="mt-5 text-sm leading-6 text-neutral-400">Independent sign-in · Human-reviewed matching · Confidential rematching</p>
            </div>
            <div class="rounded-2xl border border-white/15 bg-white p-5 text-neutral-950 shadow-xl shadow-black/15 sm:p-7">
                <p class="section-kicker">A clear, human process</p><h2 class="mt-1 text-xl font-semibold">From profile to progress</h2>
                <ol class="mt-4 grid gap-1">@foreach([['Create your profile', 'Share goals, interests and availability.'], ['Review a suggestion', 'Staff check fit, conflicts and capacity.'], ['Confirm the match', 'Both people agree before mentoring begins.'], ['Work on shared goals', 'Record milestones, notes and next actions.']] as [$title, $description])<li class="grid grid-cols-[2.25rem_1fr] gap-3 rounded-lg py-2.5"><span class="grid size-9 place-items-center rounded-full bg-isc2-green text-sm font-semibold text-white">{{ $loop->iteration }}</span><div><p class="text-sm leading-5 font-semibold">{{ $title }}</p><p class="mt-0.5 text-sm leading-5 text-neutral-500">{{ $description }}</p></div></li>@endforeach</ol>
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="page-shell py-12 sm:py-14">
            <div class="grid gap-5 lg:grid-cols-[.72fr_1.28fr] lg:gap-14"><div><p class="section-kicker">How support is structured</p><h2 class="section-heading mt-2">Start with the level of help that fits</h2></div><p class="text-base leading-7 text-neutral-600">This application manages mentoring profiles, reviewed matches and shared mentoring workspaces. Broader Chapter learning and community activities remain on the Chapter platform. An individual match is never automatic: programme staff review it and both participants confirm it.</p></div>
            <div class="mt-8 grid gap-4 lg:grid-cols-3">@foreach(config('mentoring.support_levels') as $level)<article class="panel p-5"><p class="text-xs font-medium text-isc2-green">0{{ $loop->iteration }}</p><h3 class="mt-3 text-lg font-semibold">{{ $level['name'] }}</h3><p class="mt-2 text-sm leading-6 text-neutral-600">{{ $level['summary'] }}</p></article>@endforeach</div>
        </div>
    </section>

    <section class="border-y border-neutral-200 bg-isc2-gray/55">
        <div class="page-shell grid gap-8 py-12 lg:grid-cols-[1fr_.8fr] lg:items-center sm:py-14">
            <div><p class="section-kicker">What happens next</p><h2 class="section-heading mt-2">A focused journey, without unnecessary complexity</h2><p class="mt-3 max-w-2xl text-base leading-7 text-neutral-600">Choose one main pathway for the current cycle, describe the outcome that matters, and let staff consider fit, capacity and possible conflicts. Once both people confirm, the shared workspace keeps goals and progress in one place.</p><a class="mt-5 inline-flex text-sm font-semibold text-isc2-green hover:underline" href="{{ route('programme') }}">See pathways, safeguards and full programme details →</a></div>
            <aside class="rounded-2xl bg-isc2-dark-green p-6 text-white"><p class="text-xs font-semibold tracking-[0.14em] text-[#9ac23c] uppercase">Programme safeguard</p><p class="mt-3 text-sm leading-6 text-neutral-200">Both people confirm before a match begins. Rematching can be requested confidentially. Employment, certification, promotion and financial outcomes are not guaranteed.</p></aside>
        </div>
    </section>

    <section class="bg-[#f8f8f6]"><div class="page-shell py-12 text-center sm:py-14"><p class="section-kicker">Take the next step</p><h2 class="section-heading mt-2">Ready to take part?</h2><p class="mx-auto mt-3 max-w-2xl text-base leading-7 text-neutral-600">Register as a mentor, mentee or both. Your information supports human review—it does not automatically create a match.</p><div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">@auth<a href="{{ route('dashboard') }}" class="button-primary">Go to your dashboard</a>@else<a href="{{ route('register') }}" class="button-primary">Start your profile</a><a href="{{ route('login') }}" class="button-secondary">Log in</a>@endauth</div></div></section>
</x-layouts.app>
