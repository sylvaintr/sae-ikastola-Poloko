<x-app-layout>
    <div class="container mt-5">
       <h2 class="mb-5 fw-bolder">Gestion de la famille</h2>

        
        <div class="mb-5">
            <h4 class="mb-4 fw-bolder">Parents</h4>
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
                                <div class="d-flex gap-3 align-items-center">
                                    <a href="#" class="text-dark" title="Voir">
                                      <i class="bi bi-eye fs-4"></i>
                                    </a>
                                    <a href="#" class="text-dark" title="Modifier">
                                        <i class="bi bi-pencil-square fs-4"></i>
                                    </a>
                                    <button class="border-0 bg-transparent text-secondary" title="Supprimer">
                                      <i class="bi bi-x-lg fs-4"></i>
                                    </button>
                                </div>
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
        <div class="mb-5">
            <h4 class="mb-4 fw-bolder">Enfants</h4>
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
                            <td>
                                {{-- Affichage de la classe liée --}}
                                {{ $enfant->classe->nom ?? '-' }}
                                
                            </td>
                            <td>
                                <div  class="d-flex gap-3 align-items-center">
                                    <a href="#" class="text-dark" title="Voir">
                                      <i class="bi bi-eye fs-4"></i>
                                    </a>
                                    <a href="#" class="text-dark" title="Modifier">
                                        <i class="bi bi-pencil-square fs-4"></i>
                                    </a>
                                    <button class="border-0 bg-transparent text-secondary" title="Supprimer">
                                        <i class="bi bi-x-lg fs-4"></i>
                                    </button>
                                </div>
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
