<x-guest-layout>
    <x-authentication-card class="landing-dot-field" data-auth-dot-background>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <a href="{{ url('/') }}" class="mb-5 inline-flex items-center gap-2 rounded-md text-sm text-muted-foreground hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
            <x-ui.icon name="arrow-right" class="rotate-180" />
            Torna alla home
        </a>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="\App\Support\FormValue::old('email')" required autofocus autocomplete="username" />
                <x-input-error for="email" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <div class="relative mt-1" data-validation-group x-data="{ showPassword: false }">
                    <x-input id="password" class="block w-full pr-12" type="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" />
                    <button type="button" x-cloak
                        class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center rounded-md text-muted-foreground hover:text-accent-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        aria-label="Mostra password" aria-controls="password" aria-pressed="false"
                        x-bind:aria-label="showPassword ? 'Nascondi password' : 'Mostra password'"
                        x-bind:aria-pressed="showPassword"
                        x-on:click="showPassword = !showPassword">
                        <x-ui.icon name="eye" class="!size-5" x-show="!showPassword" />
                        <x-ui.icon name="eye-off" class="!size-5" x-show="showPassword" />
                    </button>
                </div>
                <x-input-error for="password" class="mt-2" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-muted-foreground">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-muted-foreground hover:text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ms-4">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
