<x-layouts.app title="Log In">
    <div class="page-shell py-12 sm:py-16">
        <div class="mx-auto max-w-md">
            <div class="mb-6"><p class="section-kicker">Participant workspace</p><h1 class="mt-1 text-3xl font-extrabold tracking-tight">Welcome back</h1><p class="mt-2 text-sm text-neutral-600">Log in to review your next programme step.</p></div>
            <form method="POST" action="{{ route('login') }}" class="panel grid gap-5 p-5 sm:p-7">@csrf
                <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">@error('email')<p class="error-text">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
                <label class="flex items-center gap-2 font-normal"><input type="checkbox" name="remember" value="1"><span class="text-sm">Remember me</span></label>
                <button class="button-primary w-full">Log In</button>
                <p class="text-center text-sm text-neutral-500">New to the programme? <a class="font-bold text-isc2-green underline underline-offset-2" href="{{ route('register') }}">Create an account</a></p>
            </form>
        </div>
    </div>
</x-layouts.app>
