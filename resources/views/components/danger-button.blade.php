<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-destructive-foreground uppercase tracking-widest hover:brightness-95 active:brightness-90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
