<x-app-layout>
    <div class="container mt-4">
        {{-- Titre : Familiak (Basque) + Familles (Français si locale est fr) --}}
        <div class="mb-4">
            <h2 class="fw-bolder mb-0">{{ __('famille.title', [], 'eus') }}</h2>
            @if (Lang::getLocale() == 'fr')
                <small class="text-muted d-block">{{ __('famille.title') }}</small>
            @endif
        </div>

        <div class="d-flex justify-content-end align-items-center mb-4">
            {{-- Recherche --}}
            <div class="text-end">
                <label for="searchUser" class="visually-hidden">{{ __('famille.search_user_placeholder') }}</label>
                <input type="text" id="searchUser" class="form-control w-auto" 
                       placeholder="{{ __('famille.search_ajax_placeholder', [], 'eus') }}" 
                       style="min-width: 250px;">
                @if (Lang::getLocale() == 'fr')
                    <small class="text-muted d-block mt-1">{{ __('famille.search_user_placeholder') }}</small>
                @endif
            </div>

            {{-- Bouton Ajouter --}}
            <div class="text-center ms-3">
                <a href="{{ route('admin.familles.create')}}" class="btn text-white fw-bold" 
                   style="background: orange; border: 1px solid orange; border-radius: 6px;">
                    {{ __('famille.add', [], 'eus') }}
                </a>
                @if (Lang::getLocale() == 'fr')
                    <small class="text-muted d-block mt-1">{{ __('famille.add') }}</small>
                @endif
            </div>
        </div>

        <div class="mt-5">
            <table class="table table-borderless">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center align-middle">
                            {{ __('famille.id', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.id') }}</small> @endif
                        </th>
                        <th scope="col" class="text-center align-middle">
                            {{ __('famille.parent1_nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.parent1_nom') }}</small> @endif
                        </th>
                        <th scope="col" class="text-center align-middle">
                            {{ __('famille.parent1_prenom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.parent1_prenom') }}</small> @endif
                        </th>
                        <th scope="col" class="text-center align-middle">
                            {{ __('famille.parent2_nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.parent2_nom') }}</small> @endif
                        </th>
                        <th scope="col" class="text-center align-middle">
                            {{ __('famille.parent2_prenom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.parent2_prenom') }}</small> @endif
                        </th>
                        <th scope="col" class="text-center align-middle">
                            {{ __('famille.children_count', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.children_count') }}</small> @endif
                        </th>
                        <th scope="col" class="text-center align-middle">
                            {{ __('famille.actions', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small class="fw-light text-muted">{{ __('famille.actions') }}</small> @endif
                        </th>
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
                                <span class="d-inline-block border-bottom border-dark pb-1">
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
                                    <a href="{{ route('admin.familles.show', $famille->idFamille) }}" title="{{ __('famille.view', [], 'eus') }}" class="text-dark">
                                        <i class="bi bi-eye fs-4"></i>
                                    </a>
                                    <a href="{{ route('admin.familles.edit', $famille->idFamille) }}" title="{{ __('famille.edit', [], 'eus') }}" class="text-dark">
                                        <i class="bi bi-pencil-square fs-4"></i>
                                    </a>
                                    <button type="button" title="{{ __('famille.delete', [], 'eus') }}" class="border-0 bg-transparent text-dark"
                                            onclick="prepareDelete({{ $famille->idFamille }})">
                                        <i class="bi bi-x-lg fs-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center align-middle text-muted">
                                {{ __('famille.none_registered', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.none_registered') }}</small> @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL SUPPRESSION BILINGUE --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-0 pb-0 ps-4 pt-4">
                    <h5 class="modal-title fw-bold fs-4 text-dark">
                        {{ __('famille.confirm_delete_title', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr') <span class="d-block small text-muted fw-normal">{{ __('famille.confirm_delete_title') }}</span> @endif
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

    <script>
        const jsTrans = {
            noResults: "{{ __('famille.no_results', [], 'eus') }} @if(Lang::getLocale() == 'fr') - {{ __('famille.no_results') }} @endif"
        };

        let idToDelete = null;
        let deleteModalInstance = null;

        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        document.addEventListener('DOMContentLoaded', function() {
            deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteModal'));
        });

        function prepareDelete(id) {
            idToDelete = id;
            document.getElementById('deleteErrorMsg').classList.add('d-none');
            deleteModalInstance.show();
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', function() {
            if (!idToDelete) return;
            const btn = this;
            const originalText = btn.innerText;
            const errorDiv = document.getElementById('deleteErrorMsg');
            btn.innerText = "...";
            btn.disabled = true;

            fetch(`/admin/familles/${idToDelete}`, {
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
                const row = document.getElementById(`famille-row-${idToDelete}`);
                if (row) row.remove();
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

        document.getElementById('searchUser').addEventListener('keyup', function() {
            const input = this;
            const tbody = document.getElementById('famillesTableBody');
            const q = input.value.trim();

            if (q.length === 0) { location.reload(); return; }
            if (q.length < 2) return;

            fetch(`/api/search?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center align-middle text-muted">${jsTrans.noResults}</td></tr>`;
                    return;
                }
                data.forEach(famille => {
                    const p1 = famille.utilisateurs[0] || {};
                    const p2 = famille.utilisateurs[1] || {};
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr id="famille-row-${famille.idFamille}">
                            <td class="text-center align-middle">#${famille.idFamille}</td>
                            <td class="text-center align-middle">${escapeHtml(p1.nom ?? '-')}</td>
                            <td class="text-center align-middle">${escapeHtml(p1.prenom ?? '-')}</td>
                            <td class="text-center align-middle">${escapeHtml(p2.nom ?? '-')}</td>
                            <td class="text-center align-middle">${escapeHtml(p2.prenom ?? '-')}</td>
                            <td class="text-center align-middle">${famille.enfants ? famille.enfants.length : 0}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-3 align-items-center">
                                    <a href="/admin/familles/${famille.idFamille}" class="text-dark"><i class="bi bi-eye fs-4"></i></a>
                                    <a href="/admin/familles/${famille.idFamille}/edit" class="text-dark"><i class="bi bi-pencil-square fs-4"></i></a>
                                    <button type="button" class="border-0 bg-transparent text-dark p-0" onclick="prepareDelete(${famille.idFamille})">
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

