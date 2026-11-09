<div {{ $attributes->merge(['class' => 'min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-muted']) }}>
    <div>
        {{ $logo }}
    </div>

    <div data-auth-card class="w-full sm:max-w-md mt-6 px-6 py-4 bg-card shadow-md ring-1 ring-border overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>
