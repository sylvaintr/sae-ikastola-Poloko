<x-app-layout>
    <div class="container py-4">
		<h2 class="fw-bold fs-3 text-dark mb-4">Publications</h2>
        <div class="d-flex justify-content-end mb-3">
        <a class="btn-ikastola" href="{{ route('admin.actualites.create') }}">Ajouter une publication</a>
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
                            <th style="width:80px;">Actions</th>
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
