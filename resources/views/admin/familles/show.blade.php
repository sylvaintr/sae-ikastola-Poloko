<x-app-layout>
    <div class="container mt-5">
        {{-- Titre principal bilingue --}}
        <div class="mb-5">
            <h2 class="fw-bolder mb-0">{{ __('famille.management_title', [], 'eus') }}</h2>
            @if (Lang::getLocale() == 'fr')
                <small class="text-muted d-block">{{ __('famille.management_title') }}</small>
            @endif
        </div>

        {{-- Section Parents --}}
        <div class="mb-5">
            <div class="mb-4">
                <h4 class="fw-bolder mb-0">{{ __('famille.users_section', [], 'eus') }}</h4>
                @if (Lang::getLocale() == 'fr')
                    <small class="text-muted d-block text-capitalize">Parents</small>
                @endif
            </div>

            <table class="table table-borderless align-middle">
                <thead>
                    <tr>
                        <th>
                            {{ __('famille.nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.nom') }}</small> @endif
                        </th>
                        <th>
                            {{ __('famille.prenom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.prenom') }}</small> @endif
                        </th>
                        <th>
                            {{ __('famille.role', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.role') }}</small> @endif
                        </th>
                        <th>
                            {{ __('famille.statut', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.statut') }}</small> @endif
                        </th>
                        <th>
                            {{ __('famille.actions', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.actions') }}</small> @endif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($famille->utilisateurs as $parent)
                        <tr>
                            <td>{{ $parent->nom ?? '-' }}</td>
                            <td>{{ $parent->prenom ?? '-' }}</td>
                            <td class="text-capitalize">
                                {{ __('famille.parent_label', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr') <br><small class="text-muted">{{ __('famille.parent_label') }}</small> @endif
                            </td>
                            <td>{{ $parent->statut ?? 'Inconnu' }}</td>
                            <td>
                                <div class="d-flex gap-3 align-items-center">
                                    <a href="#" class="text-dark" title="{{ __('famille.view') }}">
                                      <i class="bi bi-eye fs-4"></i>
                                    </a>
                                    <a href="#" class="text-dark" title="{{ __('famille.edit') }}">
                                        <i class="bi bi-pencil-square fs-4"></i>
                                    </a>
                                    <button class="border-0 bg-transparent text-secondary" title="{{ __('famille.delete') }}">
                                      <i class="bi bi-x-lg fs-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center py-4">
                                {{ __('famille.no_parent_registered', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.no_parent_registered') }}</small> @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Section Enfants --}}
        <div class="mb-5">
            <div class="mb-4">
                <h4 class="fw-bolder mb-0">Ikasleak</h4> {{-- Titre fixe en basque --}}
                @if (Lang::getLocale() == 'fr')
                    <small class="text-muted d-block">Enfants</small>
                @endif
            </div>

            <table class="table table-borderless align-middle">
                <thead>
                    <tr>
                        <th>
                            {{ __('famille.nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.nom') }}</small> @endif
                        </th>
                        <th>
                            {{ __('famille.prenom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.prenom') }}</small> @endif
                        </th>
                        <th>
                            {{ __('famille.role', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.role') }}</small> @endif
                        </th>
                        <th>
                            {{ __('famille.classe', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.classe') }}</small> @endif
                        </th>
                        <th>
                            {{ __('famille.actions', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.actions') }}</small> @endif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($famille->enfants as $enfant)
                        <tr>
                            <td>{{ $enfant->nom ?? '-' }}</td>
                            <td>{{ $enfant->prenom ?? '-' }}</td>
                            <td class="text-capitalize">
                                {{ __('famille.child_label', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr') <br><small class="text-muted">{{ __('famille.child_label') }}</small> @endif
                            </td>
                            <td>
                                {{ $enfant->classe->nom ?? '-' }}
                            </td>
                            <td>
                                <div class="d-flex gap-3 align-items-center">
                                    <a href="#" class="text-dark" title="{{ __('famille.view') }}">
                                      <i class="bi bi-eye fs-4"></i>
                                    </a>
                                    <a href="#" class="text-dark" title="{{ __('famille.edit') }}">
                                        <i class="bi bi-pencil-square fs-4"></i>
                                    </a>
                                    <button class="border-0 bg-transparent text-secondary" title="{{ __('famille.delete') }}">
                                      <i class="bi bi-x-lg fs-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center py-4">
                                {{ __('famille.no_child_registered', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.no_child_registered') }}</small> @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

