<x-app-layout>
    <div class="container py-4 demande-page">

        @php
            $applyLabel = \Illuminate\Support\Facades\Lang::has('actualite.apply', app()->getLocale())
                ? __('actualite.apply')
                : __('demandes.filters.submit');
            $resetLabel = \Illuminate\Support\Facades\Lang::has('actualite.reset', app()->getLocale())
                ? __('actualite.reset')
                : __('demandes.filters.reset');
            $allRolesLabel = \Illuminate\Support\Facades\Lang::has('etiquette.all_roles', app()->getLocale())
                ? __('etiquette.all_roles')
                : 'Tous les rôles';
            $searchPlaceholder = \Illuminate\Support\Facades\Lang::has('etiquette.search_placeholder', app()->getLocale())
                ? __('etiquette.search_placeholder')
                : 'Rechercher une étiquette';
        @endphp

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
            <div>
                <h2 class="fw-bold mb-1">{{ Lang::get('etiquette.gestion', [], 'eus') }}</h2>
                @if (Lang::getLocale() == 'fr')
                    <p class="fw-light mb-0 text-muted">{{ Lang::get('etiquette.gestion') }}</p>
                @endif
            </div>
            <div class="d-flex flex-nowrap gap-3 align-items-start">
                <div class="d-flex flex-column align-items-center">
                    <a href="{{ route('admin.etiquettes.create') }}" class="btn demande-btn-primary text-white">
                        <i class="bi bi-plus-circle me-2"></i> {{ Lang::get('etiquette.nouvelle', [], 'eus') }}
                    </a>
                    <small class="text-muted mt-1">{{ Lang::get('etiquette.nouvelle') }}</small>
                </div>
            </div>
        </div>

        @php
            $filtersTitleEus = \Illuminate\Support\Facades\Lang::has('etiquette.filtres', 'eus')
                ? \Illuminate\Support\Facades\Lang::get('etiquette.filtres', [], 'eus')
                : 'Filtroak';
            $filtersTitleFr = \Illuminate\Support\Facades\Lang::has('etiquette.filtres', 'fr')
                ? \Illuminate\Support\Facades\Lang::get('etiquette.filtres', [], 'fr')
                : 'Filtres';
        @endphp
        <div class="mb-2">
            <h5 class="fw-semibold mb-0">{{ $filtersTitleEus }}</h5>
            @if (Lang::getLocale() == 'fr')
                <p class="text-muted mb-1">{{ $filtersTitleFr }}</p>
            @endif
        </div>

        {{-- Filtres serveur --}}
        <form id="etiquettes-filters" method="GET" action="{{ route('admin.etiquettes.index') }}" class="row g-3 align-items-end admin-actualites-filters mb-3">
            <div class="col-sm-4">
                <label for="filter-etiquette-search" class="form-label fw-semibold">{{ __('etiquette.nom') }}</label>
                <input type="text" id="filter-etiquette-search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ $searchPlaceholder }}">
            </div>
            <div class="col-sm-4">
                <label for="filter-etiquette-role" class="form-label fw-semibold">{{ __('etiquette.roles') }}</label>
                <select name="role" id="filter-etiquette-role" class="form-select">
                    <option value="">{{ $allRolesLabel }}</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->idRole }}" @selected(($filters['role'] ?? '') == $r->idRole)>{{ $r->name ?? $r->display_name ?? $r->idRole }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn demande-btn-primary text-white">{{ $applyLabel }}</button>
                <a href="{{ route('admin.etiquettes.index') }}" class="btn demande-btn-outline">{{ $resetLabel }}</a>
            </div>
        </form>

        {{-- Tableau --}}
        <div class="table-responsive">
            <table class="table align-middle demande-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('etiquette.id', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('etiquette.id') }}</span>
                            </div>
                        </th>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('etiquette.nom', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('etiquette.nom') }}</span>
                            </div>
                        </th>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('etiquette.roles', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('etiquette.roles') }}</span>
                            </div>
                        </th>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('etiquette.type', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('etiquette.type') }}</span>
                            </div>
                        </th>
                        <th class="text-center">
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('etiquette.actions', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('etiquette.actions') }}</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($etiquettes as $etiquette)
                        <tr>
                            <td class="fw-semibold">{{ $etiquette->idEtiquette }}</td>
                            <td>{{ $etiquette->nom }}</td>
                            <td>{{ $etiquette->roles->pluck('name')->join(', ') ?: '—' }}</td>
                            <td>
                                @if($etiquette->public)
                                    <span class="badge bg-success-subtle text-success">{{ __('etiquette.public') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('etiquette.private') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @include('etiquettes.template.colonne-action', ['etiquette' => $etiquette])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">{{ __('Aucune étiquette') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($etiquettes instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 admin-pagination-container">
                {{ $etiquettes->links() }}
            </div>
        @endif
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('etiquettes-filters');
            const searchInput = document.getElementById('filter-etiquette-search');
            const roleSelect = document.getElementById('filter-etiquette-role');
            let debounce;

            const submitWithResetPage = () => {
                const pageInput = form.querySelector('[name="page"]');
                if (pageInput) pageInput.remove();
                form.requestSubmit();
            };

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounce);
                    debounce = setTimeout(submitWithResetPage, 400);
                });
            }

            if (roleSelect) {
                roleSelect.addEventListener('change', submitWithResetPage);
            }
        });
    </script>
</x-app-layout>
