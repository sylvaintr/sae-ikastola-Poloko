
<x-app-layout>
    <div class="container mt-4">
        {{-- Titre principal --}}
        <h2 class="mb-4">Familles</h2>

        {{-- Barre d’actions alignée à droite --}}
        <div class="d-flex justify-content-end align-items-center mb-4">
            {{-- Champ de recherche utilisateur (plus petit) --}}
            <input type="text" class="form-control" style="width: 250px;" placeholder="Rechercher un utilisateur">

            {{-- Bouton ajouter une famille avec marge à gauche --}}
            <a href="#" class="btn text-white ms-3" style="background-color:#ffa94d; border-color:#f4a261;">
                Ajouter une famille
            </a>
        </div>

        {{-- Tableau des familles avec marge supplémentaire en haut --}}
        <div class="mt-5">
            <table class="table table-borderless">
                <thead class="table-light">
                    <tr>
                        <th class="text-center align-middle">ID Famille</th>
                        <th class="text-center align-middle">Nom parent 1</th>
                        <th class="text-center align-middle">Prénom parent 1</th>
                        <th class="text-center align-middle">Nom parent 2</th>
                        <th class="text-center align-middle">Prénom parent 2</th>
                        <th class="text-center align-middle">Nombre d'enfants</th>
                        <th class="text-center align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($familles as $famille)
                        @php
                            $parents = $famille->utilisateurs;
                            $enfants = $famille->enfants;
                            $parent1 = $parents->get(0);
                            $parent2 = $parents->get(1);
                        @endphp
                        <tr id="famille-row-{{ $famille->idFamille }}">
                            <td class="text-center align-middle">{{ $famille->idFamille }}</td>
                            <td class="text-center align-middle">{{ $parent1->nom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $parent1->prenom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $parent2->nom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $parent2->prenom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $enfants->count() }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-3">
                                    {{-- Voir --}}
                                    <a href="{{ route('familles.show', $famille->idFamille) }}" title="Voir" class="text-dark">
                                        <i class="bi bi-eye fs-5"></i>
                                    </a>

                                    {{-- Modifier --}}
                                    <a href="#" title="Modifier" class="text-dark">
                                        <i class="bi bi-pencil fs-5"></i>
                                    </a>

                                    {{-- Supprimer : juste un X noir --}}
                                    <button type="button" title="Supprimer" class="border-0 bg-transparent text-dark"
                                            onclick="deleteFamille({{ $famille->idFamille }})">
                                        <span class="fs-5">X</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center align-middle text-muted">Aucune famille enregistrée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Script AJAX pour suppression --}}
    <script>
        function deleteFamille(id) {
            if (!confirm("Supprimer cette famille ?")) return;

            fetch(`/familles/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error("Erreur serveur");
                return response.json();
            })
            .then(data => {
                alert(data.message);
                document.getElementById(`famille-row-${id}`).remove();
            })
            .catch(error => {
                console.error(error);
                alert("Impossible de supprimer la famille");
            });
        }
    </script>
</x-app-layout>





