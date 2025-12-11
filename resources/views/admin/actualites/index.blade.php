<x-app-layout>
    <div class="container py-4">

        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-4 mb-5">
            <div>
                <h1 class="fw-bold display-4 mb-1" style="font-size: 2.5rem;">Argitalpenak</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Publications</p>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">
                <div class="d-flex flex-column align-items-start">
                    <a href="{{ route('admin.actualites.create') }}" class="admin-add-button">
                        Gehitu mesu bat
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle">Ajouter une publication</p>
                </div>
            </div>
        </div>
        <div class="row overflow-auto" style="width: 100%; max-height: 75vh;">
                <table class="table align-middle admin-table datatable-publications">
                    <thead>
                        <tr>
                            <th scope="col"><span class="admin-table-heading">Titre</span></th>
                            <th scope="col"><span class="admin-table-heading">Type</span></th>
                            <th scope="col"><span class="admin-table-heading">Date publication</span></th>
                            <th scope="col"><span class="admin-table-heading">Etat</span></th>
                            <th scope="col" style="width:100px;"><span class="admin-table-heading">Actions</span></th>
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

        var table = $('.datatable-publications').DataTable({
            pagination: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.actualites.get-datatable') }}",

            lengthChange: false,
            pageLength: 50,
            autoWidth: false,

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
    @endpush

</x-app-layout>
