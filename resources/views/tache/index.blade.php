<x-app-layout>
    <div class="container py-4">

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
        <div class="mb-3">
            <label class="form-label fw-bold">Rechercher par Request ID :</label><br>
            <input type="text" id="search-id" class="admin-search-input" placeholder="Entrer un Request ID...">
        </div>
        <div class="row overflow-auto" style="width: 100%; max-height: 75vh;">

                <table class="table align-middle admin-table datatable-taches">
                    <colgroup>
                        <col style="width:90px">
                        <col style="width:100px">
                        <col style="width:250px">
                        <col style="width:130px">
                        <col style="width:120px">
                        <col style="width:90px">
                        <col style="width:100px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">
                                <span class="admin-table-heading">Eskatu ID</span>
                                <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Request ID</p>
                            </th>
                            <th scope="col">
                                <span class="admin-table-heading">Data</span>
                                <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Date</p>
                            </th>
                            <th scope="col">
                                <span class="admin-table-heading">Izenburua</span>
                                <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Titre</p>
                            </th>
                            <th scope="col">
                                <span class="admin-table-heading">Esleipena</span>
                                <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Assignation</p>
                            </th>
                            <th scope="col">
                                <span class="admin-table-heading">Larrialdia</span>
                                <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Urgence</p>
                            </th>
                            <th scope="col">
                                <span class="admin-table-heading">Egoera</span>
                                <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Statut</p>
                            </th>
                            <th scope="col" style="width:120px;">
                                <span class="admin-table-heading">Ekintzak</span>
                                <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: normal; margin-top: 0.25rem;">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(function() {

            var table = $('.datatable-taches').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{ route('tache.get-datatable') }}",
                    data: function (d) {
                        // envoie le Request ID en plus des paramètres DataTables
                        d.request_id = $('#search-id').val().trim();
                    }
                },
                autoWidth: false,
                deferRender: true,
                lengthChange: false,
                pageLength: 50,
                scrollX: true,
                responsive: false,
                language: {
                    url: "/datatables/i18n/fr-FR.json"
                },
                columns: [
                    { data: 'idTache', name: 'idTache', width: "90px" },
                    { data: 'dateD', name: 'dateD', width: "100px" },
                    { data: 'titre', name: 'titre', width: "250px" },
                    { data: 'assignation', name: 'assignation', width: "130px" },
                    { data: 'urgence', name: 'urgence', width: "120px" },
                    { data: 'etat', name: 'etat', width: "90px" },
                    { data: 'action', name: 'action', width: "100px", orderable: false, searchable: false },
                ],
                initComplete: function() {
                    this.api().columns.adjust();
                },
                drawCallback: function(settings) {
                    this.api().columns.adjust();
                }
            });

            // ---------- RECHERCHE : on appuie sur Entrée ou on change (debounced) ----------
            let searchTimer;
            $('#search-id').on('keyup', function (e) {
                // si Enter => reload immédiat
                if (e.key === 'Enter') {
                    table.ajax.reload(null, false);
                    return;
                }
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    table.ajax.reload(null, false);
                }, 350); // debounce pour éviter trop de requêtes
            });

            // Optionnel : bouton pour réinitialiser la recherche
            $('#search-id').on('search', function () {
                // navigateur peut émettre event "search" sur input[type=text] quand on vide
                table.ajax.reload(null, false);
            });
        });
        
        // CSRF pour jQuery AJAX (utilise la meta présente dans app.blade.php)
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // délégation : déclenché même pour éléments ajoutés dynamiquement
        $(document).on('click', '.mark-done', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            const url = $btn.data('url');
            const id  = $btn.data('id');

            if (!url) {
                console.error('No data-url on mark-done element');
                return;
            }

            // Optionnel: petite confirmation
            if (!confirm('Marquer cette tâche comme réalisée ?')) {
                return;
            }

            // Indicateur UI (griser icône le temps de la requête)
            $btn.addClass('text-muted').find('i').css('opacity', 0.4);

            $.ajax({
                url: url,
                type: 'PATCH',
                success: function(resp) {
                    // reload de la table sans reset de la page courante
                    $('.datatable-taches').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Erreur : impossible de marquer la tâche comme réalisée.');
                    $btn.removeClass('text-muted').find('i').css('opacity', 1);
                }
            });
        });
        
        // délégation pour mark-doing
        $(document).on('click', '.mark-doing', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            const url = $btn.data('url');
            const id  = $btn.data('id');

            if (!url) {
                console.error('No data-url on mark-doing element');
                return;
            }

            if (!confirm('Marquer cette tâche comme en cours ?')) {
                return;
            }

            // Indicateur UI (griser icône le temps de la requête)
            $btn.addClass('text-muted').find('i').css('opacity', 0.4);

            $.ajax({
                url: url,
                type: 'PATCH',
                success: function(resp) {
                    // reload de la table sans reset de la page courante
                    $('.datatable-taches').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Erreur : impossible de marquer la tâche comme réalisée.');
                    $btn.removeClass('text-muted').find('i').css('opacity', 1);
                }
            });
        });
    });
    </script>
    @endpush

</x-app-layout>
