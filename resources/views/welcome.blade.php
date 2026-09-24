<x-layouts.app title="Mentorship and Professional Growth">
    <section class="relative overflow-hidden bg-isc2-dark-green text-white">
        <div class="absolute inset-x-0 bottom-0 h-px bg-white/15"></div>
        <div class="page-shell grid gap-8 py-12 md:grid-cols-[1.15fr_.85fr] md:items-center md:py-16 lg:gap-14">
            <div>
                <p class="text-xs font-bold tracking-[0.15em] text-[#9ac23c] uppercase">One community. Many pathways.</p>
                <h1 class="mt-3 max-w-3xl text-4xl leading-[1.08] font-extrabold tracking-tight sm:text-5xl">Practical guidance for your next professional step.</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-200 sm:text-lg">Structured mentoring for Zimbabwean students, career changers, leaders, cybersecurity practitioners and community contributors aged 18 and over.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="button-primary">Register as a mentor or mentee</a>
                    <a href="#programme" class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/35 px-5 py-2.5 text-sm font-bold text-white hover:bg-white/10">Explore the programme</a>
                </div>
                <p class="mt-4 text-sm leading-6 text-neutral-400">Independent sign-in · Human-reviewed matching · Confidential rematching</p>
            </div>

            <div class="rounded-xl border border-white/15 bg-white p-5 text-neutral-950 shadow-2xl shadow-black/20 sm:p-6">
                <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3">
                    <div><p class="section-kicker">Participant journey</p><h2 class="mt-1 text-lg font-extrabold">From profile to progress</h2></div>
                    <span class="rounded-full bg-isc2-gray px-2.5 py-1 text-xs font-bold">18+</span>
                </div>
                <ol class="mt-4 grid gap-1">
                    @foreach([
                        ['Create your profile', 'Share your goals, interests and availability.'],
                        ['Review a suggestion', 'Programme staff check fit, conflicts and capacity.'],
                        ['Confirm the match', 'Both people agree before mentoring begins.'],
                        ['Set goals and meet', 'Use a charter, check-ins and a clear closure point.'],
                    ] as [$title, $description])
                        <li class="grid grid-cols-[2rem_1fr] gap-3 rounded-lg p-2.5">
                            <span class="grid size-8 place-items-center rounded-full bg-isc2-green text-sm font-extrabold text-white">{{ $loop->iteration }}</span>
                            <div><p class="text-sm font-bold leading-5">{{ $title }}</p><p class="text-sm leading-5 text-neutral-500">{{ $description }}</p></div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    <section id="programme" class="page-shell py-12 sm:py-14">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="section-kicker">Three levels of support</p><h2 class="mt-1 text-3xl font-extrabold tracking-tight">A useful route at every stage</h2></div>
            <p class="max-w-md text-sm leading-6 text-neutral-600">When a suitable individual match is unavailable, community sessions and office hours keep participants moving forward.</p>
        </div>

        <div class="mt-7 grid gap-4 md:grid-cols-3">
            @foreach([
                ['01', 'Community mentoring', 'Webinars, peer circles and office hours for accessible, practical support.'],
                ['02', 'Matched mentoring', 'One-to-one or small-cohort relationships for a defined three-to-six-month cycle.'],
                ['03', 'Specialist mentoring', 'Advanced technical or leadership engagements with clear scope and prerequisites.'],
            ] as [$number, $name, $description])
                <article class="panel p-5">
                    <div class="flex items-center gap-3"><span class="text-xs font-extrabold text-isc2-green">{{ $number }}</span><span class="h-px grow bg-neutral-200"></span></div>
                    <h3 class="mt-4 text-lg font-extrabold">{{ $name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $description }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="border-y border-neutral-200 bg-isc2-gray/60">
        <div class="page-shell py-11 sm:py-12">
            <div class="max-w-2xl"><p class="section-kicker">Programme pathways</p><h2 class="mt-1 text-2xl font-extrabold">Choose the outcome that matters now</h2></div>
            <div class="mt-6 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-5">
                @foreach(['Explore','Student and University','Career Transition','Employment and Employability','Professional Growth','Advanced Technical','Leadership and Management','Entrepreneurship','Life and Professional Success','Community and Cyber Safety'] as $pathway)
                    <div class="flex min-h-14 items-center rounded-lg border border-neutral-200 bg-white px-4 py-3 text-sm font-bold shadow-xs">{{ $pathway }}</div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-shell py-11 text-center sm:py-12">
        <h2 class="text-2xl font-extrabold">Ready to take part?</h2>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-neutral-600">Register as a mentor, mentee or both. Employment is one possible outcome, never a guarantee.</p>
        <a href="{{ route('register') }}" class="button-primary mt-5">Start your profile</a>
    </section>
</x-layouts.app>
