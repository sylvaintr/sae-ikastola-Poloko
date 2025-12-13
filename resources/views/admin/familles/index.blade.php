<x-app-layout>
    <div class="container mt-4">
        <h2 class="mb-4 fw-bolder">Familles</h2>

        <div class="d-flex justify-content-end align-items-center mb-4">
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
                                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M28 14C28 14 22.75 4.375 14 4.375C5.25 4.375 0 14 0 14C0 14 5.25 23.625 14 23.625C22.75 23.625 28 14 28 14ZM2.05275 14C2.89762 12.713 3.87094 11.5151 4.95775 10.4247C7.21 8.169 10.29 6.125 14 6.125C17.71 6.125 20.7882 8.169 23.044 10.4247C24.1308 11.5151 25.1041 12.713 25.949 14C25.8487 14.1517 25.7349 14.3197 25.6077 14.504C25.0215 15.344 24.1553 16.464 23.044 17.5753C20.7882 19.831 17.7083 21.875 14 21.875C10.2917 21.875 7.21175 19.831 4.956 17.5753C3.86919 16.4849 2.89762 15.287 2.05275 14Z" fill="black"/>
                                            <path d="M14 9.625C12.8397 9.625 11.7269 10.0859 10.9064 10.9064C10.0859 11.7269 9.625 12.8397 9.625 14C9.625 15.1603 10.0859 16.2731 10.9064 17.0936C11.7269 17.9141 12.8397 18.375 14 18.375C15.1603 18.375 16.2731 17.9141 17.0936 17.0936C17.9141 16.2731 18.375 15.1603 18.375 14C18.375 12.8397 17.9141 11.7269 17.0936 10.9064C16.2731 10.0859 15.1603 9.625 14 9.625ZM7.875 14C7.875 12.3755 8.52031 10.8176 9.66897 9.66897C10.8176 8.52031 12.3755 7.875 14 7.875C15.6245 7.875 17.1824 8.52031 18.331 9.66897C19.4797 10.8176 20.125 12.3755 20.125 14C20.125 15.6245 19.4797 17.1824 18.331 18.331C17.1824 19.4797 15.6245 20.125 14 20.125C12.3755 20.125 10.8176 19.4797 9.66897 18.331C8.52031 17.1824 7.875 15.6245 7.875 14Z" fill="black"/>
                                        </svg>
                                    </a>

                                    {{-- Modifier --}}
                                    <a href="{{ route('admin.familles.edit', $famille->idFamille) }}" title="Modifier" class="text-dark" aria-label="Modifier la famille {{ $famille->idFamille }}">
                                        <svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M12.8251 20.4313L21.1455 12.1109C20.0131 11.6397 18.6718 10.8655 17.4034 9.597C16.1347 8.32834 15.3604 6.98689 14.8891 5.85437L6.56867 14.1749C5.9194 14.8242 5.5947 15.1489 5.31551 15.5068C4.98614 15.9291 4.70378 16.3859 4.47338 16.8694C4.27807 17.2792 4.13289 17.7148 3.8425 18.5859L2.31126 23.1797C2.16836 23.6083 2.27992 24.0809 2.59946 24.4006C2.91899 24.7201 3.39163 24.8317 3.82032 24.6888L8.41408 23.1574C9.2852 22.8671 9.72079 22.7219 10.1306 22.5266C10.614 22.2962 11.0709 22.0139 11.4932 21.6845C11.8511 21.4052 12.1759 21.0806 12.8251 20.4313Z" fill="black"/>
                                            <path d="M23.4536 9.8022C25.1812 8.07452 25.1812 5.27342 23.4536 3.54576C21.7259 1.81808 18.9248 1.81808 17.1971 3.54576L16.1992 4.54368C16.2129 4.58494 16.227 4.62677 16.2417 4.66915C16.6075 5.72344 17.2977 7.10551 18.5959 8.40378C19.8942 9.70204 21.2762 10.3922 22.3306 10.758C22.3728 10.7726 22.4144 10.7867 22.4555 10.8003L23.4536 9.8022Z" fill="black"/>
                                        </svg>
                                    </a>

                                    {{-- Supprimer --}}
                                    <button type="button" title="Supprimer" class="border-0 bg-transparent text-dark"
                                            onclick="prepareDelete({{ $famille->idFamille }})"
                                            aria-label="Supprimer la famille {{ $famille->idFamille }}">
                                        <svg width="41" height="41" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M11.9496 11.9692C11.2824 12.6364 11.2824 13.718 11.9496 14.3852L18.0742 20.5098L11.9496 26.6345C11.2824 27.3016 11.2824 28.3833 11.9496 29.0504C12.6167 29.7175 13.6984 29.7175 14.3655 29.0504L20.4901 22.9257L26.6148 29.0504C27.2819 29.7175 28.3637 29.7175 29.0308 29.0504C29.6979 28.3833 29.6979 27.3016 29.0308 26.6345L22.906 20.5098L29.0308 14.3852C29.6979 13.7181 29.6979 12.6364 29.0308 11.9693C28.3635 11.3021 27.2819 11.3021 26.6148 11.9693L20.4901 18.0938L14.3655 11.9692C13.6984 11.3021 12.6167 11.3021 11.9496 11.9692Z" fill="#0F0F0F"/>
                                        </svg>
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
                                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M28 14C28 14 22.75 4.375 14 4.375C5.25 4.375 0 14 0 14C0 14 5.25 23.625 14 23.625C22.75 23.625 28 14 28 14ZM2.05275 14C2.89762 12.713 3.87094 11.5151 4.95775 10.4247C7.21 8.169 10.29 6.125 14 6.125C17.71 6.125 20.7882 8.169 23.044 10.4247C24.1308 11.5151 25.1041 12.713 25.949 14C25.8487 14.1517 25.7349 14.3197 25.6077 14.504C25.0215 15.344 24.1553 16.464 23.044 17.5753C20.7882 19.831 17.7083 21.875 14 21.875C10.2917 21.875 7.21175 19.831 4.956 17.5753C3.86919 16.4849 2.89762 15.287 2.05275 14Z" fill="black"/><path d="M14 9.625C12.8397 9.625 11.7269 10.0859 10.9064 10.9064C10.0859 11.7269 9.625 12.8397 9.625 14C9.625 15.1603 10.0859 16.2731 10.9064 17.0936C11.7269 17.9141 12.8397 18.375 14 18.375C15.1603 18.375 16.2731 17.9141 17.0936 17.0936C17.9141 16.2731 18.375 15.1603 18.375 14C18.375 12.8397 17.9141 11.7269 17.0936 10.9064C16.2731 10.0859 15.1603 9.625 14 9.625ZM7.875 14C7.875 12.3755 8.52031 10.8176 9.66897 9.66897C10.8176 8.52031 12.3755 7.875 14 7.875C15.6245 7.875 17.1824 8.52031 18.331 9.66897C19.4797 10.8176 20.125 12.3755 20.125 14C20.125 15.6245 19.4797 17.1824 18.331 18.331C17.1824 19.4797 15.6245 20.125 14 20.125C12.3755 20.125 10.8176 19.4797 9.66897 18.331C8.52031 17.1824 7.875 15.6245 7.875 14Z" fill="black"/></svg>
                                        </a>

                                        {{-- Modifier --}}
                                        <a href="/admin/familles/${famille.idFamille}/edit" class="text-dark" aria-label="Modifier la famille ${famille.idFamille}">
                                            <svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12.8251 20.4313L21.1455 12.1109C20.0131 11.6397 18.6718 10.8655 17.4034 9.597C16.1347 8.32834 15.3604 6.98689 14.8891 5.85437L6.56867 14.1749C5.9194 14.8242 5.5947 15.1489 5.31551 15.5068C4.98614 15.9291 4.70378 16.3859 4.47338 16.8694C4.27807 17.2792 4.13289 17.7148 3.8425 18.5859L2.31126 23.1797C2.16836 23.6083 2.27992 24.0809 2.59946 24.4006C2.91899 24.7201 3.39163 24.8317 3.82032 24.6888L8.41408 23.1574C9.2852 22.8671 9.72079 22.7219 10.1306 22.5266C10.614 22.2962 11.0709 22.0139 11.4932 21.6845C11.8511 21.4052 12.1759 21.0806 12.8251 20.4313Z" fill="black"/><path d="M23.4536 9.8022C25.1812 8.07452 25.1812 5.27342 23.4536 3.54576C21.7259 1.81808 18.9248 1.81808 17.1971 3.54576L16.1992 4.54368C16.2129 4.58494 16.227 4.62677 16.2417 4.66915C16.6075 5.72344 17.2977 7.10551 18.5959 8.40378C19.8942 9.70204 21.2762 10.3922 22.3306 10.758C22.3728 10.7726 22.4144 10.7867 22.4555 10.8003L23.4536 9.8022Z" fill="black"/></svg>
                                        </a>

                                        {{-- Supprimer (Avec prepareDelete) --}}
                                        <button type="button" class="border-0 bg-transparent text-dark p-0"
                                                onclick="prepareDelete(${famille.idFamille})"
                                                aria-label="Supprimer la famille ${famille.idFamille}">
                                            <svg width="41" height="41" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M11.9496 11.9692C11.2824 12.6364 11.2824 13.718 11.9496 14.3852L18.0742 20.5098L11.9496 26.6345C11.2824 27.3016 11.2824 28.3833 11.9496 29.0504C12.6167 29.7175 13.6984 29.7175 14.3655 29.0504L20.4901 22.9257L26.6148 29.0504C27.2819 29.7175 28.3637 29.7175 29.0308 29.0504C29.6979 28.3833 29.6979 27.3016 29.0308 26.6345L22.906 20.5098L29.0308 14.3852C29.6979 13.7181 29.6979 12.6364 29.0308 11.9693C28.3635 11.3021 27.2819 11.3021 26.6148 11.9693L20.4901 18.0938L14.3655 11.9692C13.6984 11.3021 12.6167 11.3021 11.9496 11.9692Z" fill="#0F0F0F"/></svg>
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