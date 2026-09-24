<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ISC2 Zimbabwe Chapter Mentorship and Professional Growth Programme">
    <title>{{ $title ?? 'ISC2 Zimbabwe Mentoring' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:p-3">Skip to content</a>
    <header class="border-b border-neutral-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 font-bold" aria-label="ISC2 Zimbabwe Mentoring home">
                <span class="grid size-11 place-items-center rounded-lg border-2 border-dashed border-isc2-green text-xs text-isc2-green" aria-hidden="true">LOGO</span>
                <span><span class="block text-sm">ISC2 Zimbabwe Chapter</span><span class="block text-xs font-normal text-neutral-600">Mentorship and Professional Growth</span></span>
            </a>
            <nav class="flex items-center gap-3 text-sm font-bold" aria-label="Primary navigation">
                <a class="hidden text-neutral-700 hover:text-isc2-green sm:inline" href="https://isc2chapters.isc2.org/" rel="external">Chapter CMMS</a>
                @auth
                    <a class="text-neutral-700 hover:text-isc2-green" href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg bg-neutral-900 px-4 py-2 text-white">Log Out</button></form>
                @else
                    <a class="text-neutral-700 hover:text-isc2-green" href="{{ route('login') }}">Log In</a>
                    <a class="rounded-lg bg-isc2-green px-4 py-2 text-white hover:bg-isc2-dark-green" href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </div>
    </header>
    <main id="main">{{ $slot }}</main>
    <footer class="border-t border-neutral-200 bg-neutral-950 text-white">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 text-sm sm:px-6 md:grid-cols-2 lg:px-8">
            <div><p class="font-bold">ISC2 Zimbabwe Chapter</p><p class="mt-2 max-w-xl text-neutral-300">A Chapter-led pilot. Participation does not guarantee employment, promotion, certification or financial outcomes.</p></div>
            <div class="md:text-right"><a class="underline" href="https://isc2chapter-zimbabwe.org/">Chapter website</a><p class="mt-2 text-neutral-400">Authorised logo asset pending replacement.</p></div>
        </div>
    </footer>
</body>
</html>
