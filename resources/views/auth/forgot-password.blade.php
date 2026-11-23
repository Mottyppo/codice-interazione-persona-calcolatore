<x-guest-layout>
    <x-authentication-card class="landing-dot-field" data-auth-dot-background>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-back-to-home />

        <div class="mb-4 text-sm text-muted-foreground">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="\App\Support\FormValue::old('email')" required autofocus autocomplete="username" />
                <x-input-error for="email" class="mt-2" />
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                <x-secondary-button :href="route('login')">
                    Torna al login
                </x-secondary-button>
                <x-button>
                    {{ __('Email Password Reset Link') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
