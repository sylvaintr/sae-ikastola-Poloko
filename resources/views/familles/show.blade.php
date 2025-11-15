<x-app-layout>
    <div class="container mt-4">
        <h2 class="mb-4">Gestion de la famille #{{ $famille->idFamille }}</h2>

        {{-- Section Parents --}}
        <div class="mb-5">
            <h4 class="mb-3">Parents</h4>
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($famille->utilisateurs as $parent)
                        <tr>
                            <td>{{ $parent->nom ?? '-' }}</td>
                            <td>{{ $parent->prenom ?? '-' }}</td>
                            <td>Parent</td>
                            <td>{{ $parent->statut ?? 'Inconnu' }}</td>
                            <td>
                                <a href="#" class="text-dark me-2" title="Voir">
                                    <i class="bi bi-eye fs-5"></i>
                                </a>
                                <a href="#" class="text-dark me-2" title="Modifier">
                                    <i class="bi bi-pencil fs-5"></i>
                                </a>
                                <button class="border-0 bg-transparent text-dark" title="Supprimer">
                                    <i class="bi bi-x-circle fs-5"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center">Aucun parent enregistré</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Section Enfants --}}
        <div class="mb-4">
            <h4 class="mb-3">Enfants</h4>
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Rôle</th>
                        <th>Classe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($famille->enfants as $enfant)
                        <tr>
                            <td>{{ $enfant->nom ?? '-' }}</td>
                            <td>{{ $enfant->prenom ?? '-' }}</td>
                            <td>Enfant</td>
                            <td>{{ $enfant->classe ?? '-' }}</td>
                            <td>
                                <a href="#" class="text-dark me-2" title="Voir">
                                    <i class="bi bi-eye fs-5"></i>
                                </a>
                                <a href="#" class="text-dark me-2" title="Modifier">
                                    <i class="bi bi-pencil fs-5"></i>
                                </a>
                                <button class="border-0 bg-transparent text-dark" title="Supprimer">
                                    <i class="bi bi-x-circle fs-5"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center">Aucun enfant enregistré</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

      
    </div>
</x-app-layout>

