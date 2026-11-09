{{-- Barra di navigazione con menu condizionato dal ruolo (Spatie @role / @can) --}}
<nav class="ui-navbar">
    <div class="app-container">
        <a class="ui-brand" href="{{ route('dashboard') }}">
            <x-ui.icon name="calendar" class="mr-1 text-primary" /> Coordinamento Esami
        </a>

        <button class="ui-nav-toggle" type="button" data-menu-toggle
            aria-controls="navbarMain" aria-expanded="false" aria-label="Apri il menu">
            <x-ui.icon name="menu" />
        </button>

        <div class="ui-navbar-menu" id="navbarMain">
            <ul class="ui-navbar-links ui-navbar-links-main mr-auto mb-2">
                <li >
                    <a class="ui-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                        href="{{ route('dashboard') }}">Dashboard</a>
                </li>

                {{-- Voci riservate all'amministratore --}}
                @role('amministratore')
                    @if (Route::has('struttura.index'))
                        <li >
                            <a class="ui-nav-link {{ request()->routeIs('struttura.*') ? 'is-active' : '' }}"
                                href="{{ route('struttura.index') }}">Struttura didattica</a>
                        </li>
                    @endif
                    @if (Route::has('import.index'))
                        <li >
                            <a class="ui-nav-link {{ request()->routeIs('import.*') ? 'is-active' : '' }}"
                                href="{{ route('import.index') }}">Importa CSV</a>
                        </li>
                    @endif
                    @if (Route::has('configurazione.edit'))
                        <li >
                            <a class="ui-nav-link {{ request()->routeIs('configurazione.*') ? 'is-active' : '' }}"
                                href="{{ route('configurazione.edit') }}">Configurazione</a>
                        </li>
                    @endif
                @endrole

                {{-- Appelli: i docenti gestiscono i propri, l'admin li vede tutti --}}
                @if (Route::has('appelli.index'))
                    <li >
                        <a class="ui-nav-link {{ request()->routeIs('appelli.*') ? 'is-active' : '' }}"
                            href="{{ route('appelli.index') }}">@role('docente') I miei appelli @else Appelli @endrole</a>
                    </li>
                @endif

                {{-- Calendario: visibile a tutti gli utenti autenticati --}}
                @if (Route::has('calendario.index'))
                    <li >
                        <a class="ui-nav-link {{ request()->routeIs('calendario.*') ? 'is-active' : '' }}"
                            href="{{ route('calendario.index') }}">Calendario</a>
                    </li>
                @endif
            </ul>

            {{-- Menu utente --}}
            <ul class="ui-navbar-links">
                <li class="ui-dropdown" data-dropdown>
                    <a class="ui-nav-link ui-dropdown-trigger" href="#" id="userMenu" role="button"
                        data-dropdown-toggle aria-controls="userDropdown" aria-expanded="false">
                        <x-ui.icon name="user-circle" class="mr-1" />{{ Auth::user()->name }}
                    </a>
                    <ul class="ui-dropdown-menu" id="userDropdown" aria-labelledby="userMenu" hidden>
                        <li>
                            <span class="ui-dropdown-label">
                                {{ Auth::user()->getRoleNames()->map(fn ($r) => ucfirst($r))->implode(', ') }}
                            </span>
                        </li>
                        <li><hr class="ui-dropdown-divider"></li>
                        <li><a class="ui-dropdown-item" href="{{ route('profile.show') }}">Profilo</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="ui-dropdown-item">Esci</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
