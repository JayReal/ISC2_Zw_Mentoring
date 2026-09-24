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

    <header class="border-b border-neutral-200 bg-white">
        <div class="page-shell flex min-h-14 items-center justify-between gap-2 py-2 sm:min-h-16 sm:gap-4 sm:py-2.5">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="ISC2 Zimbabwe Mentoring home">
                <span class="grid size-9 shrink-0 place-items-center rounded-md bg-isc2-green text-[9px] font-extrabold tracking-wide text-white sm:size-10 sm:text-[10px]" aria-label="Authorised logo placeholder">ISC2</span>
                <span class="hidden min-w-0 leading-tight min-[360px]:block">
                    <span class="block truncate text-sm font-extrabold text-neutral-950">ISC2 Zimbabwe Chapter</span>
                    <span class="hidden truncate text-xs text-neutral-500 sm:block">Mentorship and Professional Growth</span>
                </span>
            </a>

            <nav class="flex shrink-0 items-center gap-1 text-xs font-bold sm:gap-2 sm:text-sm" aria-label="Primary navigation">
                <a class="hidden rounded-md px-2 py-2 text-neutral-600 hover:text-isc2-green md:inline-flex" href="https://isc2chapters.isc2.org/" rel="external">Chapter CMMS</a>
                @auth
                    <a class="rounded-md px-2 py-2 text-neutral-700 hover:text-isc2-green" href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-md bg-neutral-900 px-2.5 py-2 text-white hover:bg-neutral-700 sm:px-3.5">Log Out</button></form>
                @else
                    <a class="rounded-md px-2 py-2 text-neutral-700 hover:text-isc2-green" href="{{ route('login') }}">Log In</a>
                    <a class="rounded-md bg-isc2-green px-2.5 py-2 text-white hover:bg-[#327137] sm:px-3.5" href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main" class="grow">{{ $slot }}</main>

    <footer class="border-t border-neutral-800 bg-neutral-950 text-white">
        <div class="page-shell flex flex-col gap-3 py-6 text-sm sm:flex-row sm:items-end sm:justify-between sm:py-7">
            <div class="max-w-2xl">
                <p class="font-bold">ISC2 Zimbabwe Chapter</p>
                <p class="mt-1 text-sm leading-6 text-neutral-400">A Chapter-led programme. Participation does not guarantee employment, promotion, certification or financial outcomes.</p>
            </div>
            <div class="flex shrink-0 gap-4 text-sm"><a class="underline decoration-neutral-600 underline-offset-4 hover:decoration-white" href="https://isc2chapter-zimbabwe.org/">Chapter website</a><span class="text-neutral-500">Logo pending</span></div>
        </div>
    </footer>
</body>
</html>
