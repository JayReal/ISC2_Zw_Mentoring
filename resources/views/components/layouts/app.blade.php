<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ISC2 Zimbabwe Chapter Mentorship and Professional Growth Programme">
    <title>{{ $title ?? 'ISC2 Zimbabwe Mentoring' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:not-sr-only focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:shadow-lg">Skip to content</a>

    <header class="relative z-20 border-b border-neutral-200/90 bg-white">
        <div class="page-shell flex min-h-16 items-center justify-between gap-3 py-2.5 sm:min-h-[4.5rem] sm:gap-6">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="ISC2 Zimbabwe Mentoring home">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-isc2-green text-[10px] font-extrabold tracking-wide text-white sm:size-11" aria-label="Authorised logo placeholder">ISC2</span>
                <span class="hidden min-w-0 leading-tight min-[520px]:block">
                    <span class="block truncate text-sm leading-5 font-extrabold text-neutral-950">ISC2 Zimbabwe Chapter</span>
                    <span class="hidden truncate text-xs leading-4 text-neutral-500 sm:block">Mentorship and Professional Growth</span>
                </span>
            </a>

            <nav class="primary-nav flex shrink-0 items-center gap-0.5 text-xs font-bold sm:gap-1.5 sm:text-sm" aria-label="Primary navigation">
                <a class="hidden rounded-lg px-2.5 py-2 text-neutral-600 transition hover:bg-neutral-50 hover:text-isc2-green lg:inline-flex" href="https://isc2chapters.isc2.org/" rel="external">Chapter CMMS</a>
                @auth
                    @php($unreadCount = auth()->user()->unreadNotifications()->count())
                    @if(auth()->user()->isProgrammeStaff())<a class="rounded-lg px-2.5 py-2 text-neutral-700 transition hover:bg-neutral-50 hover:text-isc2-green" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.*')) aria-current="page" @endif>Admin</a>@endif
                    <a class="hidden rounded-lg px-2.5 py-2 text-neutral-700 transition hover:bg-neutral-50 hover:text-isc2-green min-[420px]:inline-flex" href="{{ route('dashboard') }}" title="See what needs your attention" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                    <a class="hidden rounded-lg px-2.5 py-2 text-neutral-700 transition hover:bg-neutral-50 hover:text-isc2-green sm:inline-flex" href="{{ route('matches.index') }}" title="Work with your mentor or mentee" @if(request()->routeIs('matches.*')) aria-current="page" @endif>Mentoring</a>
                    <a class="relative rounded-lg px-2.5 py-2 text-neutral-700 transition hover:bg-neutral-50 hover:text-isc2-green" href="{{ route('notifications.index') }}" title="See what changed recently" @if(request()->routeIs('notifications.*')) aria-current="page" @endif aria-label="Notifications{{ $unreadCount ? ', '.$unreadCount.' unread' : '' }}">Updates @if($unreadCount)<span class="ml-1 rounded-full bg-isc2-green px-1.5 py-0.5 text-[10px] text-white">{{ $unreadCount }}</span>@endif</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg bg-neutral-900 px-3 py-2.5 text-white transition hover:bg-neutral-700 sm:px-4">Log out</button></form>
                @else
                    <a class="rounded-md px-2 py-2 text-neutral-700 hover:text-isc2-green" href="{{ route('login') }}">Log In</a>
                    <a class="rounded-md bg-isc2-green px-2.5 py-2 text-white hover:bg-[#327137] sm:px-3.5" href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main" class="grow" tabindex="-1">{{ $slot }}</main>

    <footer class="border-t border-neutral-800 bg-neutral-950 text-white">
        <div class="page-shell flex flex-col gap-5 py-8 text-sm sm:flex-row sm:items-end sm:justify-between sm:py-9">
            <div class="max-w-2xl">
                <p class="font-bold">ISC2 Zimbabwe Chapter</p>
                <p class="mt-1 text-sm leading-6 text-neutral-400">A Chapter-led programme. Participation does not guarantee employment, promotion, certification or financial outcomes.</p>
            </div>
            <div class="flex shrink-0 gap-4 text-sm"><a class="underline decoration-neutral-600 underline-offset-4 hover:decoration-white" href="https://isc2chapter-zimbabwe.org/">Chapter website</a><span class="text-neutral-500">Logo pending</span></div>
        </div>
    </footer>
</body>
</html>
