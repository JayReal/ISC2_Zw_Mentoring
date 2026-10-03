<x-layouts.app title="Reset password">
    <div class="page-shell py-12 sm:py-20"><div class="mx-auto max-w-md"><p class="section-kicker">Account access</p><h1 class="page-heading mt-1.5">Reset your password</h1><p class="mt-3 text-sm leading-6 text-neutral-600">Enter your registered email address and we will send a secure reset link.</p>
        @if(session('status'))<div class="notice-success mt-5" role="status">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('password.email') }}" class="panel mt-6 grid gap-5 p-5 sm:p-8">@csrf<div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">@error('email')<p class="error-text">{{ $message }}</p>@enderror</div><button class="button-primary w-full">Send reset link</button><a class="text-center text-sm font-bold text-isc2-green" href="{{ route('login') }}">Return to login</a></form>
    </div></div>
</x-layouts.app>
