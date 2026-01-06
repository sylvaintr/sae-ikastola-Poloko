<nav class="navbar navbar-expand-sm navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center me-4" href="{{ route('home') }}" style="flex-shrink: 0;">
            <x-application-logo />
            <span class="ms-2 fw-bold text-dark navbar-brand-text">Ikastola</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-sm-0">
                <li class="nav-item">
                    <x-nav-link href="{{ route('home') }}" :active="request()->routeIs('home') || request()->is('/')" class="nav-link">{{ __('nav.actualites') }}</x-nav-link>
                </li>
                @can('access-demande')
                    <li class="nav-item">
                        <x-nav-link href="{{ route('demandes.index') }}" :active="request()->routeIs('demandes.index') || request()->is('demandes*')" class="nav-link">{{ __('nav.demandes') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-tache')
                    <li class="nav-item">
                        <x-nav-link href="/tache" :active="request()->is('tache*')" class="nav-link">{{ __('nav.tache') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-presence')
                    <li class="nav-item">
                        <x-nav-link href="{{ route('presence.index') }}" :active="request()->routeIs('presence.index') || request()->is('presence*')" class="nav-link">{{ __('nav.presence') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-evenement')
                    <li class="nav-item">
                        <x-nav-link href="/evenements" :active="request()->is('evenement*')" class="nav-link">{{ __('nav.evenement') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-calendrier')
                    <li class="nav-item">
                        <x-nav-link href="/calendrier" :active="request()->is('calendrier*')" class="nav-link">{{ __('nav.calendrier') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-administration')
                    <li class="nav-item">
                        <x-nav-link href="{{ route('admin.index') }}" :active="request()->routeIs('admin.*')" class="nav-link">{{ __('nav.administration') }}</x-nav-link>
                    </li>
                @endcan
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-sm-0 align-items-center">
                @auth
                    <li class="nav-item dropdown me-3">
                        <button class="nav-link d-flex align-items-center btn btn-link p-0" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 0.5rem;" onkeydown="if(event.key==='Enter' || event.key===' ') { this.click(); }">
                            <i class="bi bi-bell bell-icon"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="min-width: 300px;">
                            <li class="dropdown-item text-muted">{{ __('No new notifications') }}</li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-inline-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center">
                                @if(Auth::user()->name)
                                    <span class="text-dark" style="font-size: 1rem;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                @else
                                    <span class="text-dark" style="font-size: 1rem;">U</span>
                                @endif
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('auth.consulter_profil') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <li><a class="dropdown-item" href="{{ route('lang.switch', ['locale' => app()->getLocale() == 'fr' ? 'eus' : 'fr']) }}">{{ __('auth.passer_eus_fr') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-100 text-start border-0 bg-transparent">{{ __('auth.deconnexion') }}</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle d-inline-flex align-items-center" href="#" id="langDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(app()->getLocale() == 'fr')
                                <x-flag-french />
                            @else
                                <x-flag-basque />
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
                            <li><a  class="dropdown-item d-flex align-items-center"
                                    href="{{ route('lang.switch', ['locale' => 'eus']) }}"><x-flag-basque />&nbsp;{{ __('basque') }}</a></li>
                            <li><a class="dropdown-item d-flex align-items-center"
                                    href="{{ route('lang.switch', ['locale' => 'fr']) }}"><x-flag-french />&nbsp;{{ __('francais') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary fw-bold" href="{{ route('login') }}">{{ __('nav.connexion') }}</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
