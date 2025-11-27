<x-app-layout>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Gestion des Actualités</h2>
            <a href="{{ route('actualites.create') }}" class="btn btn-orange">
                <i class="bi bi-plus-circle"></i> Nouvelle Actualité
            </a>
        </div>

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Date Pub.</th>
                                <th>État</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($actualites as $actu)
                                <tr>
                                    <td class="fw-bold text-dark">
                                        @if (Lang::getLocale() == 'fr')
                                            {{ $actu->titrefr }}
                                        @else
                                            {{ $actu->titreeus }}
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info text-dark">{{ $actu->type }}</span></td>
                                    <td>{{ $actu->dateP->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($actu->archive)
                                            <span class="badge bg-secondary">Archivé</span>
                                        @else
                                            <span class="badge bg-success">En ligne</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('actualites.show', $actu->idActualite) }}"
                                            class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                            👁️
                                        </a>
                                        <a href="{{ route('actualites.edit', $actu->idActualite) }}"
                                            class="btn btn-sm btn-outline-warning me-1" title="Éditer">
                                            ✏️
                                        </a>
                                        <form action="{{ route('actualites.destroy', $actu->idActualite) }}"
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

        <div class="mt-4 d-flex justify-content-center">
            {{ $actualites->links() }}
        </div>
    </div>
</x-app-layout>
