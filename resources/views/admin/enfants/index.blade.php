<x-app-layout>
    <div class="container py-4">
        @if (session('success'))
            <div id="status-alert"
                class="alert alert-success status-alert mb-3 d-flex align-items-center justify-content-between">
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close btn-close-sm" aria-label="Close"
                    onclick="this.parentElement.remove()"></button>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-3 gap-md-4 mb-4 mb-md-5">
            <div>
                <h1 class="fw-bold mb-1" style="font-size: clamp(1.75rem, 4vw, 2.5rem);">{{ __('admin.enfants_page.title', [], 'eus') }}</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ __('admin.enfants_page.title_subtitle') }}</p>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-sm-end gap-2 gap-sm-3 w-100 w-md-auto">
                <div class="admin-search-container flex-grow-1 flex-sm-grow-0">
                    <input type="text" id="search-enfant" name="search" class="form-control admin-search-input"
                        placeholder="{{ __('admin.enfants_page.search_placeholder') }}"
                        value="{{ request('search') }}">
                    <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">
                        {{ __('admin.enfants_page.search_label') }}</p>
                </div>

                <div class="d-flex flex-column align-items-end">
                    <a href="{{ route('admin.enfants.create') }}" class="btn admin-add-button w-100 w-sm-auto">
                        {{ __('admin.enfants_page.add_button', [], 'eus') }}
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle text-end">{{ __('admin.enfants_page.add_button_subtitle') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        @php
                            $allColumns = __('admin.enfants_page.columns');
                            if (!is_array($allColumns)) {
                                $allColumns = [];
                            }
                            $columnOrder = ['last_name', 'first_name', 'birth_date', 'sexe', 'classe', 'famille', 'actions'];
                            $sortableColumns = ['last_name' => 'nom', 'first_name' => 'prenom', 'birth_date' => 'dateN', 'sexe' => 'sexe', 'classe' => 'classe', 'famille' => 'famille'];
                        @endphp
                        @foreach ($columnOrder as $key)
                            @php
                                $column = $allColumns[$key] ?? [
                                    'title' => ucfirst(str_replace('_', ' ', $key)),
                                    'subtitle' => '',
                                ];
                            @endphp
                            <th scope="col">
                                @if (isset($sortableColumns[$key]))
                                    @php
                                        $currentSort = $sortColumn ?? 'nom';
                                        $currentDirection = $sortDirection ?? 'asc';
                                        $isCurrentColumn = ($sortableColumns[$key] === $currentSort);
                                        $nextDirection = $isCurrentColumn && $currentDirection === 'asc' ? 'desc' : 'asc';
                                        $sortUrl = request()->fullUrlWithQuery(['sort' => $sortableColumns[$key], 'direction' => $nextDirection, 'page' => 1]);
                                    @endphp
                                    <a href="{{ $sortUrl }}" class="text-decoration-none text-dark d-inline-block" style="width: 100%;">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="admin-table-heading">{{ $column['title'] }}</span>
                                            <i class="bi bi-chevron-down ms-1" style="font-size: 0.9rem;"></i>
                                        </div>
                                        <p class="text-muted mb-0 text-center" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ $column['subtitle'] }}</p>
                                    </a>
                                @else
                                    <div class="text-center">
                                        <span class="admin-table-heading">{{ $column['title'] }}</span>
                                        <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ $column['subtitle'] }}</p>
                                    </div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enfants as $enfant)
                        <tr>
                            <td>{{ $enfant->nom }}</td>
                            <td>{{ $enfant->prenom }}</td>
                            <td>{{ $enfant->dateN ? $enfant->dateN->format('d/m/Y') : '—' }}</td>
                            <td>
                                @if($enfant->sexe === 'M')
                                    {{ __('enfants.garcon') }}
                                @elseif($enfant->sexe === 'F')
                                    {{ __('enfants.fille') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($enfant->classe)
                                    {{ $enfant->classe->nom }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($enfant->famille)
                                    #{{ $enfant->famille->idFamille }}
                                    @if($enfant->famille->utilisateurs->count() > 0)
                                        - {{ $enfant->famille->utilisateurs->first()->nom }} {{ $enfant->famille->utilisateurs->first()->prenom }}
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <a href="{{ route('admin.enfants.show', $enfant->idEnfant) }}" class="admin-action-link"
                                        title="{{ __('admin.enfants_page.actions.view') }}">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('admin.enfants.edit', $enfant->idEnfant) }}" class="admin-action-link"
                                        title="{{ __('admin.enfants_page.actions.edit') }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.enfants.destroy', $enfant->idEnfant) }}" method="POST"
                                        class="d-inline delete-enfant-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="admin-action-link btn btn-link p-0 m-0 delete-enfant-btn"
                                            data-enfant-name="{{ $enfant->prenom }} {{ $enfant->nom }}"
                                            title="{{ __('admin.enfants_page.actions.delete') }}">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                {{ __('admin.enfants_page.no_children') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enfants->hasPages())
            <div class="admin-pagination-container">
                {{ $enfants->links() }}
            </div>
        @endif

    </div>
</x-app-layout>

@include('admin.enfants.partials.delete-modal')

<script>
    (function() {
        const alert = document.getElementById('status-alert');
        if (!alert) {
            return;
        }
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    })();

    (function() {
        const searchInput = document.getElementById('search-enfant');
        if (!searchInput) {
            return;
        }

        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchValue = this.value.trim();
                const url = new URL(window.location.href);

                // Réinitialiser à la page 1 lors de la recherche
                url.searchParams.delete('page');

                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }

                window.location.href = url.toString();
            }, 500);
        });
    })();

    (function() {
        const modal = document.getElementById('deleteEnfantModal');
        if (!modal) {
            return;
        }

        const bootstrapModal = new bootstrap.Modal(modal);
        let currentForm = null;

        document.querySelectorAll('.delete-enfant-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                currentForm = this.closest('.delete-enfant-form');
                const enfantName = this.getAttribute('data-enfant-name') || '';
                const label = modal.querySelector('[data-enfant-name]');
                if (label) {
                    label.textContent = enfantName;
                }
                bootstrapModal.show();
            });
        });

        const cancelBtn = modal.querySelector('.cancel-delete');
        const confirmBtn = modal.querySelector('.confirm-delete');

        cancelBtn?.addEventListener('click', () => {
            bootstrapModal.hide();
            currentForm = null;
        });

        confirmBtn?.addEventListener('click', () => {
            if (currentForm) {
                currentForm.submit();
                bootstrapModal.hide();
                currentForm = null;
            }
        });
    })();
</script>
