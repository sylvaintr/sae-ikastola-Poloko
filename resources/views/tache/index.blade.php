<x-app-layout>
    <div class="container py-4 demande-page">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-4 mb-3">
            <div>
                <h1 class="fw-bold display-4 mb-1" style="font-size: 2.5rem;">Orbana</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Tâches</p>
            </div>

            @can('gerer-tache')
                <div class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">
                    <div class="d-flex flex-column align-items-start">
                        <a href="{{ route('tache.create') }}" class="admin-add-button">
                            Gehitu zeregin bat
                        </a>
                        <p class="text-muted mb-0 admin-button-subtitle">Ajouter une tâche</p>
                    </div>
                </div>
            @endcan
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

        <form class="row g-3 align-items-start" method="GET" action="{{ route('tache.index') }}" id="tache-filter-form">
            {{-- RECHERCHE --}}
            <div class="col-md-4">
                <input
                    type="text"
                    id="search-global"
                    name="search"
                    class="admin-search-input"
                    placeholder="Bilatu zereginetan..."
                    value="{{ $filters['search'] ?? '' }}"
                >
                <p class="text-muted mt-0 admin-button-subtitle">
                    Rechercher dans les tâches
                </p>
            </div>
            {{-- FILTRES --}}
            <div class="col-md-8 d-flex justify-content-md-end">
                <div class="dropdown">
                    <button
                        class="demande-filter-toggle fw-semibold"
                        type="button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                    >
                        <span class="d-block">Iragazi arabera</span>
                        <small class="text-muted d-block">Filtrer par</small>
                        <i class="bi bi-chevron-down"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end demande-filter-panel p-3"
                        style="width: 340px; border-radius: 1rem;">

                        {{-- STATUT --}}
                        <div class="mb-3">
                            <label class="form-label small">
                                Egoera
                                <small class="d-block text-muted">Statut</small>
                            </label>
                            <select id="filter-etat" name="etat" class="form-select">
                                <option value="all" @selected(($filters['etat'] ?? 'all') === 'all')>Tous les statuts</option>
                                @foreach ($etats as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['etat'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- URGENCE --}}
                        <div class="mb-3">
                            <label class="form-label small">
                                Larrialdia
                                <small class="d-block text-muted">Urgence</small>
                            </label>
                            <select id="filter-urgence" name="urgence" class="form-select">
                                <option value="all" @selected(($filters['urgence'] ?? 'all') === 'all')>Toutes les urgences</option>
                                @foreach ($urgences as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['urgence'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DATES --}}
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label small">
                                    Data min
                                    <small class="d-block text-muted">Date min</small>
                                </label>
                                <input type="date" id="filter-date-min" name="date_min" class="form-control" value="{{ $filters['date_min'] ?? '' }}">
                            </div>

                            <div class="col-6">
                                <label class="form-label small">
                                    Data max
                                    <small class="d-block text-muted">Date max</small>
                                </label>
                                <input type="date" id="filter-date-max" name="date_max" class="form-control" value="{{ $filters['date_max'] ?? '' }}">
                            </div>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning w-100">
                                Filtrer
                            </button>

                            <a href="{{ route('tache.index') }}" class="btn btn-outline-warning w-100">
                                Réinitialiser
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </form>

        {{-- TABLE --}}
        <div class="table-responsive row overflow-auto" style="width: 100%; max-height: 75vh;">
            <table class="table align-middle demande-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <span class="admin-table-heading">Eskatu ID</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Request ID</p>
                        </th>
                        <th>
                            <span class="admin-table-heading">Data</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Date</p>
                        </th>
                        <th>
                            <span class="admin-table-heading">Izenburua</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Titre</p>
                        </th>
                        <th>
                            <span class="admin-table-heading">Esleipena</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Assignation</p>
                        </th>
                        <th>
                            <span class="admin-table-heading">Larrialdia</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Urgence</p>
                        </th>
                        <th>
                            <span class="admin-table-heading">Egoera</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Statut</p>
                        </th>
                        <th style="min-width: 140px;">
                            <span class="admin-table-heading">Ekintzak</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Actions</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taches as $tache)
                        <tr>
                            <td class="fw-semibold">#{{ $tache->idTache }}</td>
                            <td>{{ optional($tache->dateD)->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $tache->titre }}</td>
                            @php
                                $first = $tache->realisateurs->first();
                                $assignation = $first ? ($first->prenom . ' ' . strtoupper(substr($first->nom, 0, 1)) . '.') : '—';
                                $urgenceLabel = $urgences[$tache->type] ?? '—';
                                $etatLabel = $etats[$tache->etat] ?? '—';
                            @endphp
                            <td>{{ $assignation }}</td>
                            <td>{{ $urgenceLabel }}</td>
                            <td>{{ $etatLabel }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('tache.show', $tache) }}" title="Voir plus" class="demande-action-btn">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('gerer-tache')
                                        <a href="{{ route('tache.edit', $tache) }}" title="Modifier la tâche" class="demande-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706l-1 1a.5.5 0 0 1-.708 0L13 2.207l1-1a.5.5 0 0 1 .707 0l.795.733z" />
                                                <path d="M13.5 3.207L6 10.707V13h2.293l7.5-7.5L13.5 3.207zm-10 8.647V14h2.146l8.147-8.146-2.146-2.147L3.5 11.854z" />
                                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 1 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                                            </svg>
                                        </a>
                                        <a href="#" class="delete-tache demande-action-btn" data-url="{{ route('tache.delete', $tache) }}" title="Supprimer la tâche">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5" />
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM14.5 2h-13v1h13z" />
                                            </svg>
                                        </a>
                                        @if ($tache->etat === 'done')
                                            <i class="bi bi-check-circle-fill demande-action-btn big-icon text-success" title="Tâche terminée" style="opacity: 0.5; cursor:not-allowed;"></i>
                                        @else
                                            <a href="#" class="mark-done demande-action-btn text-success" title="Marquer comme terminée" data-url="{{ route('tache.markDone', $tache->idTache) }}">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucune tâche trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $taches->links() }}
        </div>
    </div>

    {{-- MODALE CONFIRMATION / ERREUR --}}
    <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalTitle">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="confirmModalBody">
                    Êtes-vous sûr ?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="button" class="btn demande-btn-primary" id="confirmModalAction">
                        Confirmer
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- MODALE CONFIRMATION SUPPRESSION --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la tâche</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Voulez-vous vraiment supprimer cette tâche ?
                    <br>
                    <small class="text-muted">
                        Cette action est définitive.
                    </small>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <form method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger">
                            Supprimer
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Toast
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

            function debounce(fn, delay = 300) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            const filterForm = document.getElementById('tache-filter-form');
            const searchInput = document.getElementById('search-global');

            if (searchInput && filterForm) {
                searchInput.addEventListener('input', debounce(function () {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('page');
                    const value = this.value.trim();
                    if (value) {
                        url.searchParams.set('search', value);
                    } else {
                        url.searchParams.delete('search');
                    }
                    window.location.href = url.toString();
                }, 500));
            }


            {{-- CSRF --}}
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let pendingAction = null;

            function openConfirmModal(title, body, onConfirm) {
                $('#confirmModalTitle').text(title);
                $('#confirmModalBody').text(body);
                pendingAction = onConfirm;

                new bootstrap.Modal(
                    document.getElementById('confirmActionModal')
                ).show();
            }

            $('#confirmModalAction').on('click', function () {
                if (pendingAction) pendingAction();
                pendingAction = null;

                bootstrap.Modal.getInstance(
                    document.getElementById('confirmActionModal')
                ).hide();
            });

            {{-- MARK DONE --}}
            $(document).on('click', '.mark-done', function (e) {
                e.preventDefault();

                const $btn = $(this);
                const url = $btn.data('url');

                openConfirmModal(
                    'Marquer comme terminée',
                    'Voulez-vous vraiment marquer cette tâche comme terminée ?',
                    function () {
                        $.ajax({
                            url: url,
                            type: 'PATCH',
                            success: function () {
                                window.location.reload();
                            },
                            error: function () {
                                openConfirmModal(
                                    'Erreur',
                                    'Impossible de marquer la tâche comme terminée.',
                                    function () {}
                                );
                            }
                        });
                    }
                );
            });
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.delete-tache');
                if (!btn) return;

                e.preventDefault();

                const deleteUrl = btn.dataset.url;
                const form = document.getElementById('deleteForm');

                form.action = deleteUrl;

                new bootstrap.Modal(
                    document.getElementById('deleteConfirmModal')
                ).show();
            });
        });

    </script>
    @endpush
</x-app-layout>
