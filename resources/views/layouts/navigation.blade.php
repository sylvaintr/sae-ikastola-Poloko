<nav class="navbar navbar-expand-sm navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <x-application-logo class="me-2" />
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-sm-0">
                <li class="nav-item text-capitalize ">
                    <x-nav-link href="#" class="nav-link">{{ __('nav.actualites') }}</x-nav-link>
                </li>
                @can('access-demande')
                    <li class="nav-item text-capitalize">
                        <x-nav-link href="#" class="nav-link">{{ __('nav.demande') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-tache')
                    <li class="nav-item text-capitalize">
                        <x-nav-link href="#" class="nav-link">{{ __('nav.tache') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-presence')
                    <li class="nav-item text-capitalize">
                        <x-nav-link href="#" class="nav-link">{{ __('nav.presence') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-evenement')
                    <li class="nav-item text-capitalize">
                        <x-nav-link href="#" class="nav-link">{{ __('nav.evenement') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-calendrier')
                    <li class="nav-item text-capitalize">
                        <x-nav-link href="#" class="nav-link">{{ __('calendrier') }}</x-nav-link>
                    </li>
                @endcan
                @can('access-administration')
                    <li class="nav-item text-capitalize">
                        <x-nav-link href="#" class="nav-link">{{ __('nav.administration') }}</x-nav-link>
                    </li>
                @endcan
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-sm-0 align-items-center">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-inline-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="me-2">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">{{ __('auth.deconnexion') }}</button>
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
                            <li><a class="dropdown-item d-flex align-items-center"
                                    href="#"><x-flag-basque />&nbsp;{{ __('basque') }}</a></li>
                            <li><a class="dropdown-item d-flex align-items-center"
                                    href="#"><x-flag-french />&nbsp;{{ __('francais') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('login')" class="nav-link">{{ __('connexion') }}</x-nav-link>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>