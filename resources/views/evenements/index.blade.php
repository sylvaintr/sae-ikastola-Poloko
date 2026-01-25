<x-app-layout>
    <div class="container py-4">
        @if (session('status'))
            <div id="status-alert" class="alert alert-success status-alert mb-3 d-flex align-items-center justify-content-between">
                <span>{{ session('status') }}</span>
                <button type="button" class="btn-close btn-close-sm" aria-label="Close" onclick="this.parentElement.remove()"></button>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-4 mb-5">
            <div>
                <h1 class="fw-bold display-4 mb-1" style="font-size: 2.5rem;">Évènements</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Liste des évènements enregistrés</p>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">
                <div class="admin-search-container">
                    <input type="text" id="search-event" name="search"
                           class="form-control admin-search-input"
                           placeholder="Rechercher un évènement..."
                           value="{{ request('search') }}">
                    <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">Recherche par titre ou ID</p>
                </div>

                <form method="GET" action="{{ route('evenements.index') }}" class="d-flex flex-column align-items-start" id="sort-form">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    <label for="sort" class="form-label fw-semibold mb-1">Trier</label>
                    <select id="sort" name="sort" class="form-select" style="min-width: 220px;" onchange="this.form.submit()">
                        @php $currentSort = $sort ?? 'id_desc'; @endphp
                        <option value="id_desc" @selected($currentSort === 'id_desc')>ID - plus récents en premier</option>
                        <option value="id_asc" @selected($currentSort === 'id_asc')>ID - plus anciens en premier</option>
                        <option value="date_desc" @selected($currentSort === 'date_desc')>Date - plus récentes en premier</option>
                        <option value="date_asc" @selected($currentSort === 'date_asc')>Date - plus anciennes en premier</option>
                    </select>
                </form>

                <div class="d-flex flex-column align-items-start">
                    <a href="{{ route('evenements.create') }}" class="btn admin-add-button">
                        Ajouter un évènement
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle">Créer un nouvel évènement</p>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        <th><span class="admin-table-heading">ID</span></th>
                        <th><span class="admin-table-heading">Titre</span></th>
                        <th><span class="admin-table-heading">Date</span></th>
                        <th><span class="admin-table-heading">Description</span></th>
                        <th><span class="admin-table-heading">Statut</span></th>
                        <th><span class="admin-table-heading">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($evenements as $evenement)
                        <tr>
                            <td>{{ $evenement->idEvenement }}</td>
                            <td><strong>{{ $evenement->titre }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($evenement->dateE)->format('d/m/Y') }}</td>
                            <td>
                                <span title="{{ $evenement->description }}">
                                    {{ \Illuminate\Support\Str::limit($evenement->description, 50) }}
                                </span>
                            </td>
                            <td>
                                @if($evenement->obligatoire)
                                    <span class="badge bg-danger">Obligatoire</span>
                                @else
                                    <span class="badge bg-success">Optionnel</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <a href="{{ route('evenements.show', $evenement->idEvenement) }}" class="admin-action-link" title="Voir les détails">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('evenements.edit', $evenement->idEvenement) }}" class="admin-action-link" title="Modifier">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('evenements.destroy', $evenement->idEvenement) }}" method="POST" class="d-inline delete-event-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="admin-action-link btn btn-link p-0 m-0 delete-event-btn" data-event-title="{{ $evenement->titre }}" title="Supprimer">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                Aucun événement disponible pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($evenements->hasPages())
            <div class="admin-pagination-container">
                {{ $evenements->links() }}
            </div>
        @endif

        {{-- Modal confirmation suppression --}}
        <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteEventLabel">Supprimer l'événement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        Voulez-vous vraiment supprimer l'événement « <span data-event-title></span> » ?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cancel-delete" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger confirm-delete">Supprimer</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

<script>
    // Alert auto-disparition
    (function () {
        const alert = document.getElementById('status-alert');
        if (!alert) { return; }
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    })();

    // Recherche auto
    (function () {
        const searchInput = document.getElementById('search-event');
        if (!searchInput) { return; }

        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchValue = this.value.trim();
                const url = new URL(window.location.href);
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
</script>
