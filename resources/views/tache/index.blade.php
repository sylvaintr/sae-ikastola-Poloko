<x-app-layout>
    <div class="container py-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-4 mb-5">
            <div>
                <h1 class="fw-bold display-4 mb-1" style="font-size: 2.5rem;">Orbana</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Tâches</p>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">
                <div class="d-flex flex-column align-items-start">
                    <a href="{{ route('tache.create') }}" class="admin-add-button">
                        Gehitu zeregin bat
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle">Ajouter une tâche</p>
                </div>
            </div>
        </div>

        <div class="row g-3 align-items-start">
            {{-- RECHERCHE --}}
            <div class="col-md-4">
                <input type="text" id="search-id" class="admin-search-input" placeholder="Eskaera ID baten bilaketa...">
                <p class="text-muted mt-0 admin-button-subtitle">Rechercher un Request ID</p>
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
                            <select id="filter-etat" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="todo">En attente</option>
                                <option value="doing">En cours</option>
                                <option value="done">Terminé</option>
                            </select>
                        </div>

                        {{-- URGENCE --}}
                        <div class="mb-3">
                            <label class="form-label small">
                                Larrialdia
                                <small class="d-block text-muted">Urgence</small>
                            </label>
                            <select id="filter-urgence" class="form-select">
                                <option value="">Toutes les urgences</option>
                                <option value="low">Faible</option>
                                <option value="medium">Moyenne</option>
                                <option value="high">Élevée</option>
                            </select>
                        </div>

                        {{-- DATES --}}
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label small">
                                    Data min
                                    <small class="d-block text-muted">Date min</small>
                                </label>
                                <input type="date" id="filter-date-min" class="form-control">
                            </div>

                            <div class="col-6">
                                <label class="form-label small">
                                    Data max
                                    <small class="d-block text-muted">Date max</small>
                                </label>
                                <input type="date" id="filter-date-max" class="form-control">
                            </div>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="d-flex gap-2">
                            <button id="apply-filters" class="btn btn-warning w-100">
                                Filtrer
                            </button>

                            <button id="reset-filters" class="btn btn-outline-warning w-100">
                                Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- TABLE --}}
        <div class="row overflow-auto" style="width: 100%; max-height: 75vh;">
            <table class="table align-middle admin-table datatable-taches w-100">
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
                        <th>
                            <span class="admin-table-heading">Ekintzak</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Actions</p>
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
                    <button type="button" class="btn btn-primary" id="confirmModalAction">
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
                    <h5 class="modal-title">
                        Supprimer la tâche
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-0">
                        Cette action est <strong>irréversible</strong>.<br>
                        Voulez-vous vraiment supprimer cette tâche ?
                    </p>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button class="btn btn-danger" id="confirmDeleteBtn">
                        Supprimer
                    </button>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let table = $('.datatable-taches').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                lengthChange: false,
                pageLength: 50,
                scrollX: true,
                language: {
                    url: "/datatables/i18n/fr-FR.json"
                },
                ajax: {
                    url: "{{ route('tache.get-datatable') }}",
                    data: function (d) {
                        d.request_id = $('#search-id').val();
                        d.etat       = $('#filter-etat').val();
                        d.urgence    = $('#filter-urgence').val();
                        d.date_min   = $('#filter-date-min').val();
                        d.date_max   = $('#filter-date-max').val();
                    }
                },
                columns: [
                    { data: 'idTache' },
                    { data: 'dateD' },
                    { data: 'titre' },
                    { data: 'assignation', orderable: false, searchable: false },
                    { data: 'urgence', orderable: false, searchable: false },
                    { data: 'etat', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false },
                ],
                drawCallback: function () {
                    this.api().columns.adjust();
                }
            });

            {{-- Recherche Request ID --}}
            $('#search-id').on('keyup change', function () {
                table.ajax.reload();
            });

            // Appliquer filtres
            $('#apply-filters').on('click', function () {
                table.ajax.reload();
            });

            // Réinitialiser filtres
            $('#reset-filters').on('click', function () {
                $('#filter-etat').val('');
                $('#filter-urgence').val('');
                $('#filter-date-min').val('');
                $('#filter-date-max').val('');
                table.ajax.reload();
            });


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
                                table.ajax.reload(null, false);
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

            {{-- MARK DOING --}}
            $(document).on('click', '.mark-doing', function (e) {
                e.preventDefault();

                const $btn = $(this);
                const url = $btn.data('url');

                openConfirmModal(
                    'Marquer comme en cours',
                    'Voulez-vous vraiment marquer cette tâche comme en cours ?',
                    function () {
                        $.ajax({
                            url: url,
                            type: 'PATCH',
                            success: function () {
                                table.ajax.reload(null, false);
                            },
                            error: function () {
                                openConfirmModal(
                                    'Erreur',
                                    'Impossible de marquer la tâche comme en cours.',
                                    function () {}
                                );
                            }
                        });
                    }
                );
            });

            {{-- DELETE --}}
            let deleteUrl = null;

            // clic sur icône supprimer
            $(document).on('click', '.delete-tache', function (e) {
                e.preventDefault();
                deleteUrl = $(this).data('url');

                new bootstrap.Modal(
                    document.getElementById('deleteConfirmModal')
                ).show();
            });

            // confirmation suppression
            $('#confirmDeleteBtn').on('click', function () {

                if (!deleteUrl) return;

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    success: function () {
                        $('.datatable-taches').DataTable().ajax.reload(null, false);
                    },
                    error: function () {
                        alert('Erreur lors de la suppression.');
                    }
                });

                bootstrap.Modal.getInstance(
                    document.getElementById('deleteConfirmModal')
                ).hide();

                deleteUrl = null;
            });

        });
    </script>
    @endpush
</x-app-layout>
