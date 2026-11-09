<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-primary-foreground uppercase tracking-widest hover:bg-accent hover:text-accent-foreground focus:bg-primary active:bg-primary focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
