<x-app-layout>
    <div class="container py-4 demande-page">
        @if (session('status'))
            <div id="status-alert" class="alert alert-success status-alert mb-3 d-flex align-items-center justify-content-between">
                <span>{{ session('status') }}</span>
                <button type="button" class="btn-close btn-close-sm" aria-label="Close" onclick="this.parentElement.remove()"></button>
            </div>
        @endif

        {{-- Titre / sous-titre --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
            <div>
                <h1 class="text-capitalize mb-0">
                    {{ Lang::get('evenements.title', [], 'eus') }}
                </h1>
                @if (Lang::getLocale() == 'fr')
                    <p class="text-capitalize mb-0 text-muted">
                        {{ Lang::get('evenements.title') }}
                    </p>
                @endif
            </div>
            <div class="d-flex flex-nowrap gap-3 align-items-start">
                <div class="d-flex flex-column align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="export-csv-btn" class="btn demande-btn-outline"
                            data-export-url="{{ route('evenements.export', request()->query()) }}">
                            {{ Lang::get('evenements.export_btn', [], 'eus') }}
                        </button>
                        <i class="bi bi-info-circle text-info"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="{{ __('evenements.export_btn_help') }}"
                           style="cursor: help; font-size: 1.1rem;"></i>
                    </div>
                    @if (Lang::getLocale() == 'fr')
                        <small class="text-muted mt-1">{{ Lang::get('evenements.export_btn') }}</small>
                    @endif
                </div>
                <div class="d-flex flex-column align-items-center">
                    <a href="{{ route('evenements.create') }}" class="btn demande-btn-primary text-white">
                        {{ Lang::get('evenements.add', [], 'eus') }}
                    </a>
                    @if (Lang::getLocale() == 'fr')
                        <small class="text-muted mt-1">{{ Lang::get('evenements.add') }}</small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" action="{{ route('evenements.index') }}" class="row g-3 align-items-end admin-actualites-filters mb-3">
            <div class="col-sm-4">
                <label for="search-event" class="form-label fw-semibold">
                    <span class="basque">{{ Lang::get('evenements.search', [], 'eus') }}</span>
                    @if (Lang::getLocale() == 'fr')
                        <span class="fr text-muted"> / {{ Lang::get('evenements.search') }}</span>
                    @endif
                </label>
                <input type="text" id="search-event" name="search" class="form-control"
                       value="{{ request('search') }}"
                       placeholder="{{ __('evenements.search_placeholder') }}">
            </div>
            <div class="col-sm-4">
                <label for="sort" class="form-label fw-semibold">
                    <span class="basque">{{ Lang::get('evenements.sort', [], 'eus') }}</span>
                    @if (Lang::getLocale() == 'fr')
                        <span class="fr text-muted"> / {{ Lang::get('evenements.sort') }}</span>
                    @endif
                </label>
                @php $currentSort = $sort ?? 'id_desc'; @endphp
                <select id="sort" name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="id_desc" @selected($currentSort === 'id_desc')>{{ __('evenements.sort_id_desc') }}</option>
                    <option value="id_asc" @selected($currentSort === 'id_asc')>{{ __('evenements.sort_id_asc') }}</option>
                    <option value="date_desc" @selected($currentSort === 'date_desc')>{{ __('evenements.sort_date_desc') }}</option>
                    <option value="date_asc" @selected($currentSort === 'date_asc')>{{ __('evenements.sort_date_asc') }}</option>
                </select>
            </div>
            <div class="col-sm-4 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn demande-btn-primary text-white">{{ __('evenements.search') }}</button>
                <a href="{{ route('evenements.index') }}" class="btn demande-btn-outline">{{ __('evenements.cancel') }}</a>
            </div>
        </form>

        {{-- Tableau --}}
        <div class="table-responsive">
            <table class="table align-middle demande-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">ID</span>
                            </div>
                        </th>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('evenements.titre', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('evenements.titre') }}</span>
                            </div>
                        </th>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('evenements.date', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('evenements.date') }}</span>
                            </div>
                        </th>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('evenements.description', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('evenements.description') }}</span>
                            </div>
                        </th>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('evenements.statut', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('evenements.statut') }}</span>
                            </div>
                        </th>
                        <th class="text-center">
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('evenements.actions', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('evenements.actions') }}</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($evenements as $evenement)
                        <tr>
                            <td>{{ $evenement->idEvenement }}</td>
                            <td class="fw-semibold">{{ $evenement->titre }}</td>
                            <td>{{ optional($evenement->start_at)->format('d/m/Y') }}</td>
                            <td>
                                <span title="{{ $evenement->description }}">
                                    {{ \Illuminate\Support\Str::limit($evenement->description, 50) }}
                                </span>
                            </td>
                            <td>
                                @if($evenement->obligatoire)
                                    <span class="badge bg-danger">{{ __('evenements.status_obligatoire') }}</span>
                                @else
                                    <span class="badge bg-success">{{ __('evenements.status_optionnel') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <a href="{{ route('evenements.show', $evenement->idEvenement) }}" class="admin-action-link" title="{{ __('evenements.action_view') }}">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('evenements.edit', $evenement->idEvenement) }}" class="admin-action-link" title="{{ __('evenements.action_edit') }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('evenements.destroy', $evenement->idEvenement) }}" method="POST" class="d-inline delete-event-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="admin-action-link btn btn-link p-0 m-0 delete-event-btn" data-event-title="{{ $evenement->titre }}" title="{{ __('evenements.action_delete') }}">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ __('evenements.no_events') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($evenements->hasPages())
            <div class="mt-3 admin-pagination-container">
                {{ $evenements->links() }}
            </div>
        @endif

        {{-- Modal confirmation suppression --}}
        <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteEventLabel">{{ __('evenements.delete_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{ __('evenements.delete_confirm') }} « <span data-event-title></span> » ?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cancel-delete" data-bs-dismiss="modal">{{ __('evenements.cancel') }}</button>
                        <button type="button" class="btn btn-danger confirm-delete">{{ __('evenements.delete') }}</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    });

    // Alert auto-disparition
    (function () {
        const alert = document.getElementById('status-alert');
        if (!alert) { return; }
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    })();

    // Suppression avec modal
    (function () {
        const modal = document.getElementById('deleteEventModal');
        if (!modal) { return; }

        const bootstrapModal = new bootstrap.Modal(modal);
        let currentForm = null;

        document.querySelectorAll('.delete-event-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                currentForm = this.closest('.delete-event-form');
                const eventTitle = this.getAttribute('data-event-title') || '';
                const label = modal.querySelector('[data-event-title]');
                if (label) {
                    label.textContent = eventTitle;
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

    // Export CSV
    (function () {
        const exportBtn = document.getElementById('export-csv-btn');
        if (!exportBtn) { return; }

        exportBtn.addEventListener('click', function () {
            const url = this.getAttribute('data-export-url');
            if (url) {
                window.location.href = url;
            }
        });
    })();
</script>
