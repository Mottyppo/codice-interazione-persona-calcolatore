{{-- Messaggi flash e errori di validazione --}}
@if (session('success'))
    <x-ui.alert class="ui-alert-success ui-alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="ui-close" data-dismiss-alert aria-label="Chiudi"><x-ui.icon name="x" /></button>
    </x-ui.alert>
@endif

@if (session('error'))
    <x-ui.alert class="ui-alert-danger ui-alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="ui-close" data-dismiss-alert aria-label="Chiudi"><x-ui.icon name="x" /></button>
    </x-ui.alert>
@endif

@if (session('warning'))
    <x-ui.alert class="ui-alert-warning ui-alert-dismissible" role="alert">
        {{ session('warning') }}
        <button type="button" class="ui-close" data-dismiss-alert aria-label="Chiudi"><x-ui.icon name="x" /></button>
    </x-ui.alert>
@endif

@if ($errors->any())
    <x-ui.alert class="ui-alert-danger ui-alert-dismissible" role="alert">
        <strong>Si sono verificati alcuni errori:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="ui-close" data-dismiss-alert aria-label="Chiudi"><x-ui.icon name="x" /></button>
    </x-ui.alert>
@endif
