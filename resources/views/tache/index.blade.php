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
        <div class="row overflow-auto" style="width: 100%; max-height: 75vh;">
                <table class="table align-middle admin-table datatable-taches">
                    <colgroup>
                        <col style="width:90px">
                        <col style="width:90px">
                        <col style="width:240px">
                        <col style="width:140px">
                        <col style="width:120px">
                        <col style="width:120px">
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
                            <th scope="col" style="width:100px;">
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

        // attendre que les polices soient prêtes (évite les shifts dus au font swap)
        (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(function() {

            var table = $('.datatable-taches').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tache.get-datatable') }}",

                autoWidth: false,
                deferRender: true,
                lengthChange: false,
                pageLength: 50,
                scrollX: true,         // utile pour garder largeur fixe et éviter recalcs
                responsive: false,     // désactiver responsive pour ne pas recalculer
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/French.json"
                },

                columns: [
                    { data: 'idTache', name: 'idTache', width: "90px" },
                    { data: 'dateD', name: 'dateD', width: "90px" },
                    { data: 'titre', name: 'titre', width: "240px" },
                    { data: 'assignation', name: 'assignation', width: "140px" },
                    { data: 'urgence', name: 'urgence', width: "120px" },
                    { data: 'etat', name: 'etat', width: "120px" },
                    { data: 'action', name: 'action', width: "100px", orderable: false, searchable: false },
                ],

                initComplete: function() {
                    // ajustement final quand tout est prêt
                    this.api().columns.adjust();
                },

                drawCallback: function(settings) {
                    // garantit stabilité après chaque draw
                    this.api().columns.adjust();
                }
            });

            // sécurité : un dernier ajustement court après rendu
            setTimeout(function() {
                table.columns.adjust();
            }, 150);
        });

    });
    </script>
    @endpush

</x-app-layout>
