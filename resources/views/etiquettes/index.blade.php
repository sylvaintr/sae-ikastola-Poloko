<x-app-layout>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Gestion des Étiquettes</h2>
            <a href="{{ route('admin.etiquettes.create') }}" class="btn btn-orange">
                <i class="bi bi-plus-circle"></i> Nouvelle Étiquette
            </a>
        </div>

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>

                                <th>ID</th>
                                <th>NOM</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($etiquettes as $etiquette)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $etiquette->idEtiquette }}</td>
                                    <td class="fw-bold text-dark">{{ Str::limit($etiquette->nom, 40) }}</td>

                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.etiquettes.edit', $etiquette->idEtiquette) }}"
                                            class="btn btn-sm btn-outline-warning me-1" title="Éditer">
                                            ✏️
                                        </a>
                                        <form action="{{ route('admin.etiquettes.destroy', $etiquette->idEtiquette) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Voulez-vous vraiment supprimer cet article ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                title="Supprimer">
                                                🗑️
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
</x-app-layout>
