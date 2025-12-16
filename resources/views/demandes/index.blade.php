@php
    use Illuminate\Support\Str;
@endphp

<x-app-layout>
    <div class="container py-4 demande-page">
        <div class="demande-toolbar text-end">
            <div class="d-flex flex-wrap gap-4 justify-content-end">
                <div class="demande-toolbar-item">
                    <button type="button" class="btn demande-btn-outline fw-semibold px-4 py-2">
                        {{ __('demandes.toolbar.export.eu') }}
                    </button>
                    <small class="text-muted">{{ __('demandes.toolbar.export.fr') }}</small>
                </div>
                <div class="demande-toolbar-item">
                    <a href="{{ route('demandes.create') }}" class="btn demande-btn-primary fw-semibold text-white px-4 py-2">
                        {{ __('demandes.toolbar.create.eu') }}
                    </a>
                    <small class="text-muted">{{ __('demandes.toolbar.create.fr') }}</small>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div id="demande-toast" class="demande-toast shadow-sm">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-sm" aria-label="{{ __('demandes.actions.close') }}"></button>
                </div>
            </div>
        @endif

<form class="demande-filter-form mt-4" method="GET" action="{{ route('demandes.index') }}" id="demande-filter-form">
            <div class="row g-3 align-items-start">
                <div class="col-md-4">
                    <label for="search" class="form-label fw-semibold text-muted small mb-1">{{ __('demandes.search.label.eu') }} <small class="text-muted d-block">{{ __('demandes.search.label.fr') }}</small></label>
                    <input type="text" id="search" name="search" class="form-control demande-search-input"
                        placeholder="{{ __('demandes.search.placeholder') }}" value="{{ $filters['search'] }}">
                </div>
                <div class="col-md-8 text-md-end">
                    <div class="dropdown d-inline-block">
                        <button class="demande-filter-toggle fw-semibold" type="button" id="filterDropdown"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <span class="d-block">{{ __('demandes.filters.toggle.eu') }}</span>
                            <small class="text-muted d-block">{{ __('demandes.filters.toggle.fr') }}</small>
                            <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end demande-filter-panel p-3"
                            aria-labelledby="filterDropdown">
                            <div class="mb-3">
                                <label for="filter-etat" class="form-label small text-muted">{{ __('demandes.filters.status.eu') }} <small class="d-block text-muted">{{ __('demandes.filters.status.fr') }}</small></label>
                                <select id="filter-etat" class="form-select" name="etat">
                                    <option value="all" @selected($filters['etat'] === 'all')>{{ __('demandes.filters.options.all_status') }}</option>
                                    @foreach ($etats as $etat)
                                        <option value="{{ $etat }}" @selected($filters['etat'] === $etat)>{{ $etat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="filter-type" class="form-label small text-muted">{{ __('demandes.filters.type.eu') }} <small class="d-block text-muted">{{ __('demandes.filters.type.fr') }}</small></label>
                                <select id="filter-type" class="form-select" name="type">
                                    <option value="all" @selected($filters['type'] === 'all')>{{ __('demandes.filters.options.all_types') }}</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="filter-urgence" class="form-label small text-muted">{{ __('demandes.filters.urgency.eu') }} <small class="d-block text-muted">{{ __('demandes.filters.urgency.fr') }}</small></label>
                                <select id="filter-urgence" class="form-select" name="urgence">
                                    <option value="all" @selected($filters['urgence'] === 'all')>{{ __('demandes.filters.options.all_urgencies') }}</option>
                                    @foreach ($urgences as $urgence)
                                        <option value="{{ $urgence }}" @selected($filters['urgence'] === $urgence)>{{ $urgence }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="filter-date-from" class="form-label small text-muted">{{ __('demandes.filters.date_min.eu') }} <small class="d-block text-muted">{{ __('demandes.filters.date_min.fr') }}</small></label>
                                    <input id="filter-date-from" type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] }}">
                                </div>
                                <div class="col-6">
                                    <label for="filter-date-to" class="form-label small text-muted">{{ __('demandes.filters.date_max.eu') }} <small class="d-block text-muted">{{ __('demandes.filters.date_max.fr') }}</small></label>
                                    <input id="filter-date-to" type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] }}">
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn demande-btn-primary flex-grow-1">{{ __('demandes.filters.submit') }}</button>
                                <a href="{{ route('demandes.index') }}" class="btn demande-btn-outline flex-grow-1">{{ __('demandes.filters.reset') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive mt-4">
            <table class="table align-middle demande-table mb-0">
                <thead>
                    <tr>
                        @php
                            $queryBase = request()->except('page');
                            $sortState = $filters['sort'] ?? 'date';
                            $directionState = $filters['direction'] ?? 'desc';
                            $sortHelper = function (string $key) use ($sortState, $directionState, $queryBase) {
                                $isCurrent = $sortState === $key;
                                $nextDir = $isCurrent && $directionState === 'asc' ? 'desc' : 'asc';
                                $icon = $isCurrent
                                    ? ($directionState === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill')
                                    : 'bi-caret-down';
                                $url = request()->fullUrlWithQuery(array_merge($queryBase, ['sort' => $key, 'direction' => $nextDir]));
                                return [$url, $icon, $isCurrent];
                            };
                        @endphp
                        @php [$urlId, $iconId] = $sortHelper('id'); @endphp
                        <th scope="col">
                            <div class="demande-header-cell">
                                <div class="demande-header-label">
                                    <span class="basque">{{ __('demandes.table.columns.id.eu') }}</span>
                                    <span class="fr">{{ __('demandes.table.columns.id.fr') }}</span>
                                </div>
                                <a href="{{ $urlId }}" class="demande-sort-link" aria-label="{{ __('demandes.table.sort.id') }}">
                                    <i class="bi {{ $iconId }}"></i>
                                </a>
                            </div>
                        </th>
                        @php [$urlDate, $iconDate] = $sortHelper('date'); @endphp
                        <th scope="col">
                            <div class="demande-header-cell">
                                <div class="demande-header-label">
                                    <span class="basque">{{ __('demandes.table.columns.date.eu') }}</span>
                                    <span class="fr">{{ __('demandes.table.columns.date.fr') }}</span>
                                </div>
                                <a href="{{ $urlDate }}" class="demande-sort-link" aria-label="{{ __('demandes.table.sort.date') }}">
                                    <i class="bi {{ $iconDate }}"></i>
                                </a>
                            </div>
                        </th>
                        <th scope="col">
                            <div class="demande-header-label">
                                <span class="basque">{{ __('demandes.table.columns.title.eu') }}</span>
                                <span class="fr">{{ __('demandes.table.columns.title.fr') }}</span>
                            </div>
                        </th>
                        @php [$urlType, $iconType] = $sortHelper('type'); @endphp
                        <th scope="col">
                            <div class="demande-header-cell">
                                <div class="demande-header-label">
                                    <span class="basque">{{ __('demandes.table.columns.type.eu') }}</span>
                                    <span class="fr">{{ __('demandes.table.columns.type.fr') }}</span>
                                </div>
                                <a href="{{ $urlType }}" class="demande-sort-link" aria-label="{{ __('demandes.table.sort.type') }}">
                                    <i class="bi {{ $iconType }}"></i>
                                </a>
                            </div>
                        </th>
                        @php [$urlUrg, $iconUrg] = $sortHelper('urgence'); @endphp
                        <th scope="col">
                            <div class="demande-header-cell">
                                <div class="demande-header-label">
                                    <span class="basque">{{ __('demandes.table.columns.urgency.eu') }}</span>
                                    <span class="fr">{{ __('demandes.table.columns.urgency.fr') }}</span>
                                </div>
                                <a href="{{ $urlUrg }}" class="demande-sort-link" aria-label="{{ __('demandes.table.sort.urgency') }}">
                                    <i class="bi {{ $iconUrg }}"></i>
                                </a>
                            </div>
                        </th>
                        @php [$urlEtat, $iconEtat] = $sortHelper('etat'); @endphp
                        <th scope="col">
                            <div class="demande-header-cell">
                                <div class="demande-header-label">
                                    <span class="basque">{{ __('demandes.table.columns.status.eu') }}</span>
                                    <span class="fr">{{ __('demandes.table.columns.status.fr') }}</span>
                                </div>
                                <a href="{{ $urlEtat }}" class="demande-sort-link" aria-label="{{ __('demandes.table.sort.status') }}">
                                    <i class="bi {{ $iconEtat }}"></i>
                                </a>
                            </div>
                        </th>
                        <th scope="col" class="text-center">
                            <span class="d-block">{{ __('demandes.table.columns.actions.eu') }}</span>
                            <small class="text-muted">{{ __('demandes.table.columns.actions.fr') }}</small>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($demandes as $demande)
                        <tr>
                            <td class="fw-semibold">#{{ $demande->idTache }}</td>
                            <td>{{ optional($demande->dateD)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $demande->titre }}</td>
                            <td>{{ $demande->type }}</td>
                            @php
                                $urgNormalized = Str::of($demande->urgence ?? '')
                                    ->ascii()
                                    ->lower()
                                    ->value();
                                $isHighUrgency = in_array($urgNormalized, ['elevee', 'élevée', 'high', 'haute', 'eleve']);
                            @endphp
                            <td class="align-middle">
                                @if ($isHighUrgency)
                                    <span class="text-warning me-1" title="{{ __('demandes.table.urgency_high_hint') }}" aria-label="{{ __('demandes.table.urgency_high_hint') }}">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </span>
                                @endif
                                {{ $demande->urgence ?? '—' }}
                            </td>
                            <td>{{ $demande->etat }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('demandes.show', $demande) }}" class="btn demande-action-btn" title="{{ __('demandes.actions.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if ($demande->etat !== 'Terminé')
                                        <a href="{{ route('demandes.edit', $demande) }}" class="btn demande-action-btn" title="{{ __('demandes.actions.edit') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                <path
                                                    d="M15.502 1.94a.5.5 0 0 1 0 .706l-1 1a.5.5 0 0 1-.708 0L13 2.207l1-1a.5.5 0 0 1 .707 0l.795.733z" />
                                                <path
                                                    d="M13.5 3.207L6 10.707V13h2.293l7.5-7.5L13.5 3.207zm-10 8.647V14h2.146l8.147-8.146-2.146-2.147L3.5 11.854z" />
                                                <path fill-rule="evenodd"
                                                    d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 1,00000 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                                            </svg>
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('demandes.destroy', $demande) }}" class="d-inline demande-delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn demande-action-btn text-muted demande-delete-btn"
                                            data-demande-title="{{ $demande->titre }}" title="{{ __('demandes.actions.delete') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM14.5 2h-13v1h13z"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @if ($demande->etat !== 'Terminé')
                                        <form method="POST" action="{{ route('demandes.validate', $demande) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn demande-action-btn text-success" title="{{ __('demandes.actions.validate') }}">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">{{ __('demandes.table.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $demandes->links() }}
        </div>
    </div>
</x-app-layout>

@include('demandes.partials.delete-modal')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('demande-toast');
        if (toast) {
            const closeBtn = toast.querySelector('.btn-close');
            const hideToast = () => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 250);
            };
            closeBtn?.addEventListener('click', hideToast);
            setTimeout(hideToast, 3200);
        }

        const deleteModalEl = document.getElementById('deleteDemandeModal');
        const searchInput = document.getElementById('search');
        let currentForm = null;

        if (deleteModalEl) {
            const deleteModal = new bootstrap.Modal(deleteModalEl);
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
            document.querySelectorAll('.demande-delete-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    currentForm = this.closest('.demande-delete-form');
                    const title = this.getAttribute('data-demande-title') || '';
                    const label = deleteModalEl.querySelector('[data-demande-title]');
                    if (label) {
                        label.textContent = title;
                    }
                    deleteModal.show();
                });
            });
        }

        if (searchInput) {
            let debounce;
            searchInput.addEventListener('input', function () {
                clearTimeout(debounce);
                debounce = setTimeout(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('page');
                    const value = this.value.trim();
                    if (value) {
                        url.searchParams.set('search', value);
                    } else {
                        url.searchParams.delete('search');
                    }
                    window.location.href = url.toString();
                }, 500);
            });
        }
    });
</script>
