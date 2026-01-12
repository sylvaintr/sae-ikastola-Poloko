<x-app-layout>
    <div class="container py-4 demande-page">
        <div class="card admin-actualites-card shadow-sm border-0">
            <div class="card-body pb-2">
                @php
                    $applyLabel = \Illuminate\Support\Facades\Lang::has('actualite.apply', app()->getLocale())
                        ? __('actualite.apply')
                        : __('demandes.filters.submit');
                    $resetLabel = \Illuminate\Support\Facades\Lang::has('actualite.reset', app()->getLocale())
                        ? __('actualite.reset')
                        : __('demandes.filters.reset');
                    $searchPlaceholder = \Illuminate\Support\Facades\Lang::has('actualite.search_placeholder', app()->getLocale())
                        ? __('actualite.search_placeholder')
                        : 'Rechercher une actualité';
                    $etatBasque = \Illuminate\Support\Facades\Lang::has('actualite.etat', 'eus')
                        ? \Illuminate\Support\Facades\Lang::get('actualite.etat', [], 'eus')
                        : 'Egoera';
                    $etatFr = \Illuminate\Support\Facades\Lang::has('actualite.etat', 'fr')
                        ? \Illuminate\Support\Facades\Lang::get('actualite.etat', [], 'fr')
                        : 'État';
                    $actionsBasque = \Illuminate\Support\Facades\Lang::has('actualite.actions', 'eus')
                        ? \Illuminate\Support\Facades\Lang::get('actualite.actions', [], 'eus')
                        : 'Ekintzak';
                    $actionsFr = \Illuminate\Support\Facades\Lang::has('actualite.actions', 'fr')
                        ? \Illuminate\Support\Facades\Lang::get('actualite.actions', [], 'fr')
                        : 'Actions';
                @endphp
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                    <div>
                        <h2 class="fw-bold mb-1">{{ Lang::get('actualite.nouvelle_actualite', [], 'eus') }}</h2>
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0 text-muted">{{ Lang::get('actualite.nouvelle_actualite') }}</p>
                        @endif
                    </div>
                    <div class="d-flex flex-nowrap justify-content-end align-items-start gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <a href="{{ route('admin.actualites.create') }}" class="btn demande-btn-outline">
                                {{ Lang::get('actualite.ajouter_une_actualite', [], 'eus') }}
                            </a>
                            <small class="text-muted mt-1">{{ Lang::get('actualite.ajouter_une_actualite') }}</small>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <a href="{{ route('admin.etiquettes.index') }}" class="btn demande-btn-primary text-white">
                               {{ Lang::get('etiquette.gerer_les_etiquettes', [], 'eus') }}
                            </a>
                            <small class="text-muted mt-1">{{ Lang::get('etiquette.gerer_les_etiquettes') }}</small>
                        </div>
                    </div>
                </div>

                @php
                    $filtersTitleEus = \Illuminate\Support\Facades\Lang::has('actualite.filtres', 'eus')
                        ? \Illuminate\Support\Facades\Lang::get('actualite.filtres', [], 'eus')
                        : 'Iragazkiak';
                    $filtersTitleFr = \Illuminate\Support\Facades\Lang::has('actualite.filtres', 'fr')
                        ? \Illuminate\Support\Facades\Lang::get('actualite.filtres', [], 'fr')
                        : 'Filtres';
                @endphp
                <div class="mb-2">
                    <h5 class="fw-semibold mb-0">{{ $filtersTitleEus }}</h5>
                    @if (Lang::getLocale() == 'fr')
                        <p class="text-muted mb-1">{{ $filtersTitleFr }}</p>
                    @endif
                </div>

                {{-- Filtres serveur (sans DataTables) --}}
                <form id="actualites-filters" method="GET" action="{{ route('admin.actualites.index') }}" class="row g-3 align-items-end admin-actualites-filters mb-3">
                    <div class="col-sm-3">
                        <label for="filter-type" class="form-label fw-semibold">{{ __('actualite.type') }}</label>
                        <select id="filter-type" name="type" class="form-select">
                            <option value="">{{ __('actualite.all_types') ?? 'Tous les types' }}</option>
                            <option value="public" @selected(($filters['type'] ?? '') === 'public')>{{ __('actualite.public') }}</option>
                            <option value="private" @selected(($filters['type'] ?? '') === 'private')>{{ __('actualite.prive') }}</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label for="filter-etat" class="form-label fw-semibold">{{ __('actualite.visibilite') }}</label>
                        <select id="filter-etat" name="etat" class="form-select">
                            <option value="">{{ __('actualite.visibilite') }}</option>
                            <option value="active" @selected(($filters['etat'] ?? '') === 'active')>{{ __('actualite.active')  }}</option>
                            <option value="archived" @selected(($filters['etat'] ?? '') === 'archived')>{{ __('actualite.archived') }}</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label for="filter-etiquette" class="form-label fw-semibold">{{ __('etiquette.all') }}</label>
                        <select id="filter-etiquette" name="etiquette" class="form-select">
                            <option value="">{{ __('etiquette.all')}}</option>
                            @foreach($etiquettes as $et)
                                <option value="{{ $et->idEtiquette }}" @selected(($filters['etiquette'] ?? '') == $et->idEtiquette)>{{ $et->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label for="filter-search" class="form-label fw-semibold">{{ __('actualite.titre') }}</label>
                        <input type="text" id="filter-search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ $searchPlaceholder }}">
                    </div>
                    <div class="col-sm-12 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn demande-btn-primary text-white">{{ $applyLabel }}</button>
                        <a href="{{ route('admin.actualites.index') }}" class="btn demande-btn-outline">{{ $resetLabel }}</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive px-3 pb-3">
                <table class="table align-middle demande-table mb-0">
                    <thead>
                        <tr>
                            <th>
                                <div class="demande-header-label">
                                    <span class="basque">{{ Lang::get('actualite.titre', [], 'eus') }}</span>
                                    <span class="fr">{{ Lang::get('actualite.titre') }}</span>
                                </div>
                            </th>
                            <th>
                                <div class="demande-header-label">
                                    <span class="basque">{{ Lang::get('actualite.type', [], 'eus') }}</span>
                                    <span class="fr">{{ Lang::get('actualite.type') }}</span>
                                </div>
                            </th>
                            <th>
                                <div class="demande-header-label">
                                    <span class="basque">{{ Lang::get('actualite.date_publication', [], 'eus') }}</span>
                                    <span class="fr">{{ Lang::get('actualite.date_publication') }}</span>
                                </div>
                            </th>
                            <th>
                                <div class="demande-header-label">
                                    <span class="basque">{{ $etatBasque }}</span>
                                    <span class="fr">{{ $etatFr }}</span>
                                </div>
                            </th>
                            <th class="text-center">
                                <div class="demande-header-label">
                                    <span class="basque">{{ $actionsBasque }}</span>
                                    <span class="fr">{{ $actionsFr }}</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actualites as $actualite)
                            @php
                                $dateP = $actualite->dateP ? \Illuminate\Support\Carbon::parse($actualite->dateP) : null;
                                $title = $actualite->titrefr ?? $actualite->titreeus ?? $actualite->titre ?? '—';
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $title }}</td>
                                <td>{{ $actualite->type ?? '—' }}</td>
                                <td>{{ $dateP ? $dateP->format('d/m/Y') : '—' }}</td>
                                <td>{{ $actualite->archive ? Lang::get('actualite.archived') : Lang::get('actualite.active') }}</td>
                                <td class="text-center">
                                    @include('actualites.template.colonne-action', ['actualite' => $actualite])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('Aucune actualité') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($actualites instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-3 admin-pagination-container">
                        {{ $actualites->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>

    @include('actualites.partials.delete-modal')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('actualites-filters');
            const searchInput = document.getElementById('filter-search');
            let debounce;

            const submitWithResetPage = () => {
                // supprime un éventuel champ page pour revenir à la page 1
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

            const deleteModalEl = document.getElementById('deleteActualiteModal');
            if (deleteModalEl) {
                const deleteModal = new bootstrap.Modal(deleteModalEl);
                let currentForm = null;

                deleteModalEl.querySelector('.cancel-delete')?.addEventListener('click', () => {
                    deleteModal.hide();
                    currentForm = null;
                });

                deleteModalEl.querySelector('.confirm-delete')?.addEventListener('click', () => {
                    if (currentForm) {
                        currentForm.submit();
                        deleteModal.hide();
                        currentForm = null;
                    }
                });

                document.querySelectorAll('.actualite-delete-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        currentForm = this.closest('.actualite-delete-form');
                        const title = this.getAttribute('data-actualite-title') || '';
                        const label = deleteModalEl.querySelector('[data-actualite-title]');
                        if (label) {
                            label.textContent = title;
                        }
                        deleteModal.show();
                    });
                });
            }
        });
    </script>
</x-app-layout>
