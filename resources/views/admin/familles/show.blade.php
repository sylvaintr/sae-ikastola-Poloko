<x-app-layout>
    <div class="container mt-5">

        <div class="mb-5">
            <h2 class="fw-bolder mb-0">{{ __('famille.management_title', [], 'eus') }}</h2>
            @if (Lang::getLocale() == 'fr')
                <small class="text-muted d-block">{{ __('famille.management_title') }}</small>
            @endif
        </div>


        <div class="mb-5">
            <h4 class="fw-bolder mb-3">{{ __('famille.users_section', [], 'eus') }}</h4>
            @if (Lang::getLocale() == 'fr')
                <small class="text-muted d-block">Parents</small>
            @endif

            {{-- TABLEAU PC --}}
            <div class="d-none d-md-block">
                <table class="table table-borderless align-middle">
                    <thead>
                        <tr>
                            <th>
                                {{ __('famille.nom', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.nom') }}</small>
                                @endif
                            </th>
                            <th>
                                {{ __('famille.prenom', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.prenom') }}</small>
                                @endif
                            </th>
                            <th>
                                {{ __('famille.role', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.role') }}</small>
                                @endif
                            </th>
                            <th>
                                {{ __('famille.statut', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.statut') }}</small>
                                @endif
                            </th>
                            <th>
                                {{ __('famille.actions', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.actions') }}</small>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($famille->utilisateurs as $parent)
                            <tr>
                                <td>{{ $parent->nom ?? '-' }}</td>
                                <td>{{ $parent->prenom ?? '-' }}</td>
                                <td>{{ __('famille.parent_label', [], 'eus') }}</td>
                                <td>{{ $parent->statut ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-3">
                                        <a href="#" class="text-dark"><i class="bi bi-eye fs-4"></i></a>
                                        <a href="#" class="text-dark"><i class="bi bi-pencil-square fs-4"></i></a>
                                        <button type="button" class="border-0 bg-transparent text-secondary">
                                            <i class="bi bi-x-lg fs-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    {{ __('famille.no_parent_registered', [], 'eus') }}
                                    @if (Lang::getLocale() == 'fr')
                                        <br><small>{{ __('famille.no_parent_registered') }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-md-none">
                @forelse($famille->utilisateurs as $parent)
                    <div class="border rounded p-3 mb-3 shadow-sm">
                        <div><strong>{{ __('famille.nom', [], 'eus') }} :</strong> {{ $parent->nom ?? '-' }}</div>
                        <div><strong>{{ __('famille.prenom', [], 'eus') }} :</strong> {{ $parent->prenom ?? '-' }}</div>
                        <div><strong>{{ __('famille.role', [], 'eus') }} :</strong> {{ __('famille.parent_label', [], 'eus') }}</div>
                        <div class="mb-3"><strong>{{ __('famille.statut', [], 'eus') }} :</strong> {{ $parent->statut ?? '-' }}</div>

                        <div class="d-flex gap-4">
                            <a href="#" class="text-dark"><i class="bi bi-eye fs-4"></i></a>
                            <a href="#" class="text-dark"><i class="bi bi-pencil-square fs-4"></i></a>
                            <button type="button" class="border-0 bg-transparent text-secondary">
                                <i class="bi bi-x-lg fs-4"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        {{ __('famille.no_parent_registered', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <br><small>{{ __('famille.no_parent_registered') }}</small>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===================== ENFANTS ============================ --}}
        <div class="mb-5">
            <h4 class="fw-bolder mb-3">Ikasleak</h4>
            @if (Lang::getLocale() == 'fr')
                <small class="text-muted d-block">Enfants</small>
            @endif

            {{-- TABLEAU PC --}}
            <div class="d-none d-md-block">
                <table class="table table-borderless align-middle">
                    <thead>
                        <tr>
                            <th>
                                {{ __('famille.nom', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.nom') }}</small>
                                @endif
                            </th>
                            <th>
                                {{ __('famille.prenom', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.prenom') }}</small>
                                @endif
                            </th>
                            <th>
                                {{ __('famille.role', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.role') }}</small>
                                @endif
                            </th>
                            <th>
                                {{ __('famille.classe', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.classe') }}</small>
                                @endif
                            </th>
                            <th>
                                {{ __('famille.actions', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="fw-light text-muted">{{ __('famille.actions') }}</small>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($famille->enfants as $enfant)
                            <tr>
                                <td>{{ $enfant->nom ?? '-' }}</td>
                                <td>{{ $enfant->prenom ?? '-' }}</td>
                                <td>{{ __('famille.child_label', [], 'eus') }}</td>
                                <td>{{ $enfant->classe->nom ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-3">
                                        <a href="#" class="text-dark"><i class="bi bi-eye fs-4"></i></a>
                                        <a href="#" class="text-dark"><i class="bi bi-pencil-square fs-4"></i></a>
                                        <button type="button" class="border-0 bg-transparent text-secondary">
                                            <i class="bi bi-x-lg fs-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    {{ __('famille.no_child_registered', [], 'eus') }}
                                    @if (Lang::getLocale() == 'fr')
                                        <br><small>{{ __('famille.no_child_registered') }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- CARTES MOBILE --}}
            <div class="d-md-none">
                @forelse($famille->enfants as $enfant)
                    <div class="border rounded p-3 mb-3 shadow-sm">
                        <div><strong>{{ __('famille.nom', [], 'eus') }} :</strong> {{ $enfant->nom ?? '-' }}</div>
                        <div><strong>{{ __('famille.prenom', [], 'eus') }} :</strong> {{ $enfant->prenom ?? '-' }}</div>
                        <div><strong>{{ __('famille.role', [], 'eus') }} :</strong> {{ __('famille.child_label', [], 'eus') }}</div>
                        <div class="mb-3"><strong>{{ __('famille.classe', [], 'eus') }} :</strong> {{ $enfant->classe->nom ?? '-' }}</div>

                        <div class="d-flex gap-4">
                            <a href="#" class="text-dark"><i class="bi bi-eye fs-4"></i></a>
                            <a href="#" class="text-dark"><i class="bi bi-pencil-square fs-4"></i></a>
                            <button type="button" class="border-0 bg-transparent text-secondary">
                                <i class="bi bi-x-lg fs-4"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        {{ __('famille.no_child_registered', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <br><small>{{ __('famille.no_child_registered') }}</small>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>

