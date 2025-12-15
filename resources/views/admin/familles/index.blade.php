<x-app-layout>
    <div class="container mt-4">
        <h2 class="mb-4 fw-bolder">Familles</h2>

        <div class="d-flex justify-content-end align-items-center mb-4">
            {{-- Label invisible pour accessibilité (SonarQube) --}}
            <label for="searchUser" class="visually-hidden">Rechercher un utilisateur</label>
            <input type="text" id="searchUser" class="form-control" style="width: 250px;" placeholder="Rechercher un utilisateur">

            <a href="{{ route('admin.familles.create')}}" class="btn text-white ms-3" style="background-color:#F29201; border-color:#f4a261;">
                Ajouter une famille
            </a>
        </div>

        <div class="mt-5">
            <table class="table table-borderless">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center align-middle">ID Famille</th>
                        <th scope="col" class="text-center align-middle">Nom parent 1</th>
                        <th scope="col" class="text-center align-middle">Prénom parent 1</th>
                        <th scope="col" class="text-center align-middle">Nom parent 2</th>
                        <th scope="col" class="text-center align-middle">Prénom parent 2</th>
                        <th scope="col" class="text-center align-middle">Nombre d'enfants</th>
                        <th scope="col" class="text-center align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody id="famillesTableBody">
                    @forelse($familles as $famille)
                        @php
                            $parents = $famille->utilisateurs;
                            $enfants = $famille->enfants;
                            $parent1 = $parents->get(0);
                            $parent2 = $parents->get(1);
                        @endphp
                        <tr id="famille-row-{{ $famille->idFamille }}">
                            <td class="text-center align-middle">
                                <span style="display:inline-block; border-bottom:2px solid #000; padding-bottom:2px;">
                                    #{{ $famille->idFamille }}
                                </span>
                            </td>
                            <td class="text-center align-middle">{{ $parent1->nom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $parent1->prenom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $parent2->nom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $parent2->prenom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $enfants->count() }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-3 align-items-center">

                                    {{-- Voir --}}
                                    <a href="{{ route('admin.familles.show', $famille->idFamille) }}" title="Voir" class="text-dark" aria-label="Voir la famille {{ $famille->idFamille }}">
                                       <i class="bi bi-eye fs-4"></i>
                                    </a>

                                    {{-- Modifier --}}
                                    <a href="{{ route('admin.familles.edit', $famille->idFamille) }}" title="Modifier" class="text-dark" aria-label="Modifier la famille {{ $famille->idFamille }}">
                                       <i class="bi bi-pencil-square fs-4"></i>
                                    </a>

                                    {{-- Supprimer --}}
                                    <button type="button" title="Supprimer" class="border-0 bg-transparent text-dark"
                                            onclick="prepareDelete({{ $famille->idFamille }})"
                                            aria-label="Supprimer la famille {{ $famille->idFamille }}">
                                        <i class="bi bi-x-lg fs-4"></i>
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

    {{-- MODAL SUPPRESSION --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">

                <div class="modal-header border-0 pb-0 ps-4 pt-4">
                    <h5 class="modal-title fw-bold fs-4 text-dark">Supprimer la famille</h5>
                </div>

                <div class="modal-body ps-4 pe-4 pt-2 text-secondary">
                    Êtes-vous sûr de vouloir supprimer cette famille ? <br>
                    <span class="text-danger small fw-bold">Cette action supprimera aussi tous les enfants associés.</span>
                </div>

                <div class="modal-footer border-0 pe-4 pb-4">
                    <button type="button" class="btn px-4 py-2 fw-bold" data-bs-dismiss="modal"
                            style="background: white; border: 1px solid #ced4da; color: #6c757d; border-radius: 6px;">
                        Annuler
                    </button>
                    <button type="button" id="btnConfirmDelete" class="btn px-4 py-2 fw-bold text-white"
                            style="background: #dc3545; border: 1px solid #dc3545; border-radius: 6px;">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS JS --}}
    <script>
        let idToDelete = null;
        let deleteModalInstance = null;

        document.addEventListener('DOMContentLoaded', function() {
            deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteModal'));
        });

        function prepareDelete(id) {
            idToDelete = id;
            deleteModalInstance.show();
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', function() {
            if (!idToDelete) return;

            const btn = this;
            const originalText = btn.innerText;
            btn.innerText = "...";
            btn.disabled = true;

            fetch(`/familles/${idToDelete}`, {
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
                deleteModalInstance.hide();
                const row = document.getElementById(`famille-row-${idToDelete}`);
                if (row) row.remove();

                btn.innerText = originalText;
                btn.disabled = false;
                idToDelete = null;
            })
            .catch(error => {
                console.error(error);
                alert("Impossible de supprimer la famille");
                btn.innerText = originalText;
                btn.disabled = false;
                deleteModalInstance.hide();
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('searchUser');
            const tbody = document.getElementById('famillesTableBody');
            const originalHTML = tbody.innerHTML;

            input.addEventListener('keyup', () => {
                const q = input.value.trim();

                if (q.length === 0) {
                    tbody.innerHTML = originalHTML;
                    return;
                }

                if (q.length < 2) return;

                fetch(`/api/search?q=${encodeURIComponent(q)}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';

                    if (data.message || data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center align-middle text-muted">Aucun résultat</td></tr>`;
                        return;
                    }

                    data.forEach(famille => {
                        const parent1 = famille.utilisateurs[0] || {};
                        const parent2 = famille.utilisateurs[1] || {};
                        const enfantsCount = famille.enfants ? famille.enfants.length : 0;

                        tbody.insertAdjacentHTML('beforeend', `
                            <tr id="famille-row-${famille.idFamille}">
                                <td class="text-center align-middle">
                                    <span style="display:inline-block; border-bottom:2px solid #000; padding-bottom:2px;">#${famille.idFamille}</span>
                                </td>
                                <td class="text-center align-middle">${parent1.nom ?? '-'}</td>
                                <td class="text-center align-middle">${parent1.prenom ?? '-'}</td>
                                <td class="text-center align-middle">${parent2.nom ?? '-'}</td>
                                <td class="text-center align-middle">${parent2.prenom ?? '-'}</td>
                                <td class="text-center align-middle">${enfantsCount}</td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-3 align-items-center">

                                        {{-- Voir --}}
                                        <a href="/admin/familles/${famille.idFamille}" class="text-dark" aria-label="Voir la famille ${famille.idFamille}">
                                            <i class="bi bi-eye fs-4"></i>
                                        </a>

                                        {{-- Modifier --}}
                                        <a href="/admin/familles/${famille.idFamille}/edit" class="text-dark" aria-label="Modifier la famille ${famille.idFamille}">
                                           <i class="bi bi-pencil-square fs-4"></i>
                                        </a>

                                        {{-- Supprimer (Avec prepareDelete) --}}
                                        <button type="button" class="border-0 bg-transparent text-dark p-0"
                                                onclick="prepareDelete(${famille.idFamille})"
                                                aria-label="Supprimer la famille ${famille.idFamille}">
                                           <i class="bi bi-x-lg fs-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                })
                .catch(err => {
                    console.error(err);
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">Erreur de recherche</td></tr>`;
                });
            });
        });
    </script>
</x-app-layout>

