<x-app-layout>
    <script>
        const currentLang = "{{ app()->getLocale() }}";
    </script>

    @vite(['resources/js/actualite.js'])

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Gestion des Actualités</h2>
            <a href="{{ route('admin.actualites.create') }}" class="btn btn-orange">
                <i class="bi bi-plus-circle"></i> Nouvelle Actualité
            </a>
        </div>


        <table class="table table-hover align-middle mb-0" id="TableActualites" style="width:100%">
            <thead class="bg-light">
                <tr>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Date Pub.</th>
                    <th>État</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
        </table>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                afficherDataTable('TableActualites');
            });
        </script>
    </div>
</x-app-layout>
