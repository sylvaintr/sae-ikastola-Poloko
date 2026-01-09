<x-app-layout>
    <div class="container mt-4">

        {{-- En-tête --}}
        <div class="mb-4">
            <h2 class="fw-bolder mb-0">{{ __('famille.title', [], 'eus') }}</h2>
            @if (Lang::getLocale() == 'fr')
                <small class="text-muted d-block">{{ __('famille.title') }}</small>
            @endif
        </div>

        {{-- Barre d'outils (Recherche + Bouton Ajouter) --}}
        <div class="d-flex justify-content-end align-items-center mb-4 flex-wrap gap-3">
            {{-- Recherche AJAX --}}
            <div class="text-end">
                <label for="searchUser" class="visually-hidden">{{ __('famille.search_user_placeholder') }}</label>
                <input type="text"
                       id="searchUser"
                       class="form-control w-auto"
                       placeholder="{{ __('famille.search_ajax_placeholder', [], 'eus') }}"
                       style="min-width: 250px;">
                @if (Lang::getLocale() == 'fr')
                    <small class="text-muted d-block mt-1">{{ __('famille.search_user_placeholder') }}</small>
                @endif
            </div>

            {{-- Bouton Ajouter --}}
            <div class="text-center">
                <a href="{{ route('admin.familles.create')}}"
                   class="btn text-white fw-bold"
                   style="background: orange; border: 1px solid orange; border-radius: 6px;">
                    {{ __('famille.add', [], 'eus') }}
                </a>
                @if (Lang::getLocale() == 'fr')
                    <small class="text-muted d-block mt-1">{{ __('famille.add') }}</small>
                @endif
            </div>
        </div>

        {{-- Vue Ordinateur (Tableau) --}}
        <div class="d-none d-md-block mt-4">
            <table class="table table-borderless">
                <thead class="table-light">
                    <tr>
                        <th class="text-center align-middle">
                            {{ __('famille.id', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.id') }}</small> @endif
                        </th>
                        <th class="text-center align-middle">
                            {{ __('famille.parent1_nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.parent1_nom') }}</small> @endif
                        </th>
                        <th class="text-center align-middle">
                            {{ __('famille.parent1_prenom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.parent1_prenom') }}</small> @endif
                        </th>
                        <th class="text-center align-middle">
                            {{ __('famille.parent2_nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.parent2_nom') }}</small> @endif
                        </th>
                        <th class="text-center align-middle">
                            {{ __('famille.parent2_prenom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.parent2_prenom') }}</small> @endif
                        </th>
                        <th class="text-center align-middle">
                            {{ __('famille.children_count', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.children_count') }}</small> @endif
                        </th>
                        <th class="text-center align-middle">
                            {{ __('famille.actions', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.actions') }}</small> @endif
                        </th>
                    </tr>
                </thead>
                <tbody id="famillesTableBody">
                    @forelse($familles as $famille)
                        @php
                            $parents = $famille->utilisateurs;
                            $p1 = $parents->get(0);
                            $p2 = $parents->get(1);
                        @endphp
                        <tr id="famille-row-{{ $famille->idFamille }}">
                            <td class="text-center align-middle">#{{ $famille->idFamille }}</td>
                            <td class="text-center align-middle">{{ $p1->nom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $p1->prenom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $p2->nom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $p2->prenom ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $famille->enfants->count() }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="{{ route('admin.familles.show', $famille->idFamille) }}" class="text-dark">
                                        <i class="bi bi-eye fs-4"></i>
                                    </a>
                                    <a href="{{ route('admin.familles.edit', $famille->idFamille) }}" class="text-dark">
                                        <i class="bi bi-pencil-square fs-4"></i>
                                    </a>
                                    <button type="button" class="border-0 bg-transparent text-dark" onclick="prepareDelete({{ $famille->idFamille }})">
                                        <i class="bi bi-x-lg fs-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('famille.none_registered', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.none_registered') }}</small> @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vue Mobile (Cartes) --}}
        <div class="d-md-none mt-4">
            @forelse($familles as $famille)
                @php
                    $parents = $famille->utilisateurs;
                    $p1 = $parents->get(0);
                    $p2 = $parents->get(1);
                @endphp
                <div class="border rounded p-3 mb-3 shadow-sm" id="famille-card-{{ $famille->idFamille }}">
                    <div><strong>{{ __('famille.id', [], 'eus') }}:</strong> #{{ $famille->idFamille }}</div>
                    <div><strong>{{ __('famille.parent1_nom', [], 'eus') }}:</strong> {{ $p1->nom ?? '-' }}</div>
                    <div><strong>{{ __('famille.parent1_prenom', [], 'eus') }}:</strong> {{ $p1->prenom ?? '-' }}</div>
                    <div><strong>{{ __('famille.parent2_nom', [], 'eus') }}:</strong> {{ $p2->nom ?? '-' }}</div>
                    <div><strong>{{ __('famille.parent2_prenom', [], 'eus') }}:</strong> {{ $p2->prenom ?? '-' }}</div>
                    <div><strong>{{ __('famille.children_count', [], 'eus') }}:</strong> {{ $famille->enfants->count() }}</div>
                    <div class="d-flex gap-3 mt-2">
                        <a href="{{ route('admin.familles.show', $famille->idFamille) }}" class="text-dark">
                            <i class="bi bi-eye fs-4"></i>
                        </a>
                        <a href="{{ route('admin.familles.edit', $famille->idFamille) }}" class="text-dark">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </a>
                        <button type="button" class="border-0 bg-transparent text-dark" onclick="prepareDelete({{ $famille->idFamille }})">
                            <i class="bi bi-x-lg fs-4"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    {{ __('famille.none_registered', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.none_registered') }}</small> @endif
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal de confirmation de suppression --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-0 pb-0 ps-4 pt-4">
                    <h5 class="modal-title fw-bold fs-4 text-dark">
                        {{ __('famille.confirm_delete_title', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <span class="d-block small text-muted fw-normal">{{ __('famille.confirm_delete_title') }}</span>
                        @endif
                    </h5>
                </div>
                <div class="modal-body ps-4 pe-4 pt-2 text-secondary">
                    {{ __('famille.confirm_delete_msg', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.confirm_delete_msg') }}</small> @endif
                    <br>
                    <span class="text-danger small fw-bold">
                        {{ __('famille.delete_warning', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr') <br>{{ __('famille.delete_warning') }} @endif
                    </span>
                    <div id="deleteErrorMsg" class="text-danger mt-2 d-none"></div>
                </div>
                <div class="modal-footer border-0 pe-4 pb-4">
                    <button type="button" class="btn btn-light border fw-bold px-4" data-bs-dismiss="modal">
                        {{ __('famille.cancel', [], 'eus') }}
                    </button>
                    <button type="button" id="btnConfirmDelete" class="btn btn-danger fw-bold px-4">
                        {{ __('famille.delete', [], 'eus') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts JavaScript Corrigés --}}
    <script>
        const jsTrans = {
            noResults: "{{ __('famille.no_results', [], 'eus') }} @if(Lang::getLocale() == 'fr') - {{ __('famille.no_results') }} @endif"
        };

        let idToDelete = null;
        let deleteModalInstance = null;

        /**
         * Fonction pour détecter automatiquement le dossier racine du site.
         * Indispensable pour que ça marche sur Wamp / sous-dossiers.
         */
        function getRootUrl() {
            let path = window.location.pathname;
            // On cherche où commence "/admin/familles" dans l'URL
            let index = path.indexOf('/admin/familles');
            
            if (index > -1) {
                // On garde tout ce qu'il y a avant (ex: "/sae-ikastola-Poloko/public")
                return path.substring(0, index);
            }
            // Fallback (si jamais l'URL change)
            return '';
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        document.addEventListener('DOMContentLoaded', () => {
            deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteModal'));
        });

        function prepareDelete(id) {
            idToDelete = id;
            document.getElementById('deleteErrorMsg').classList.add('d-none');
            deleteModalInstance.show();
        }

        // --- Logique de Suppression ---
        document.getElementById('btnConfirmDelete').addEventListener('click', function() {
            if (!idToDelete) return;

            const btn = this;
            const originalText = btn.innerText;
            const errorDiv = document.getElementById('deleteErrorMsg');

            btn.innerText = "...";
            btn.disabled = true;

            // Construction de l'URL dynamique
            const rootUrl = getRootUrl();
            const deleteUrl = `${rootUrl}/admin/familles/${idToDelete}`;

            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (!response.ok) throw new Error("Erreur");
                return response.json();
            })
            .then(() => {
                deleteModalInstance.hide();
                
                // Suppression de la ligne du tableau
                const row = document.getElementById(`famille-row-${idToDelete}`);
                if (row) row.remove();

                // Suppression de la carte mobile
                const card = document.getElementById(`famille-card-${idToDelete}`);
                if (card) card.remove();

                // Si ni l'un ni l'autre n'est trouvé (cas rare), on recharge
                if (!row && !card) window.location.reload();
            })
            .catch(error => {
                errorDiv.innerText = "Error: " + error.message;
                errorDiv.classList.remove('d-none');
            })
            .finally(() => {
                btn.innerText = originalText;
                btn.disabled = false;
                idToDelete = null;
            });
        });

        // --- Logique de Recherche ---
        document.getElementById('searchUser').addEventListener('keyup', function() {
            const q = this.value.trim();
            const tbody = document.getElementById('famillesTableBody');

            if (q.length === 0) {
                location.reload(); // On recharge pour récupérer la liste complète propre
                return;
            }
            if (q.length < 2) return;

            // Construction de l'URL dynamique pour la recherche
            const rootUrl = getRootUrl();
            const searchUrl = `${rootUrl}/api/search?q=${encodeURIComponent(q)}`;

            fetch(searchUrl, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">${jsTrans.noResults}</td></tr>`;
                    return;
                }
                
                data.forEach(famille => {
                    const p1 = famille.utilisateurs[0] || {};
                    const p2 = famille.utilisateurs[1] || {};
                    const enfantCount = famille.enfants ? famille.enfants.length : 0;

                    // Reconstruction des liens corrects
                    const showLink = `${rootUrl}/admin/familles/${famille.idFamille}`;
                    const editLink = `${rootUrl}/admin/familles/${famille.idFamille}/edit`;

                    tbody.insertAdjacentHTML('beforeend', `
                        <tr id="famille-row-${famille.idFamille}">
                            <td class="text-center align-middle">#${famille.idFamille}</td>
                            <td class="text-center align-middle">${escapeHtml(p1.nom ?? '-')}</td>
                            <td class="text-center align-middle">${escapeHtml(p1.prenom ?? '-')}</td>
                            <td class="text-center align-middle">${escapeHtml(p2.nom ?? '-')}</td>
                            <td class="text-center align-middle">${escapeHtml(p2.prenom ?? '-')}</td>
                            <td class="text-center align-middle">${enfantCount}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="${showLink}" class="text-dark">
                                        <i class="bi bi-eye fs-4"></i>
                                    </a>
                                    <a href="${editLink}" class="text-dark">
                                        <i class="bi bi-pencil-square fs-4"></i>
                                    </a>
                                    <button type="button" class="border-0 bg-transparent text-dark" onclick="prepareDelete(${famille.idFamille})">
                                        <i class="bi bi-x-lg fs-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });
            });
        });
    </script>
</x-app-layout>

