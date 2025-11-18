<x-app-layout>
    <div class="container py-4">

        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-4 mb-5">
            <div>
                <h1 class="fw-bold display-4 mb-1" style="font-size: 2.5rem;">Argitalpenak</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Publications</p>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">
                <div class="d-flex flex-column align-items-start">
                    <a href="{{ route('admin.actualites.create') }}" class="btn admin-add-button">
                        Gehitu mesu bat
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle">Ajouter une publication</p>
                </div>
            </div>
        </div>
        <div class="card row overflow-auto" style="width: 100%; max-height: 75vh;">
            <div class="card-body">
                <table class="table table-bordered datatable-publications">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Date publication</th>
                            <th>Etat</th>
                            <th style="width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
                
            var table = $('.datatable-publications').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.actualites.get-datatable') }}",

                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/French.json"
                },
                
                columns: [
                    {data: 'titre', name: 'titre'},
                    {data: 'type', name: 'type'},
                    {data: 'dateP', name: 'dateP'},
                    {data: 'archive', name: 'archive'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

        });

    </script>
</x-app-layout>
