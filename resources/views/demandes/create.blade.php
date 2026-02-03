<x-app-layout>
    <div class="container py-4 demande-create-page">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                @php $isEdit = isset($demande); @endphp
                <h1 class="fw-bold mb-1">{{ $isEdit ? __('demandes.form.edit_title') : __('demandes.form.create_title') }}</h1>
                <p class="text-muted mb-0">
                    {{ $isEdit ? __('demandes.form.edit_subtitle') : __('demandes.form.create_subtitle') }}
                </p>
            </div>
            <div class="text-center text-md-end">
                <a href="{{ route('demandes.index') }}" class="btn demande-btn-outline px-4 fw-semibold">{{ __('demandes.form.buttons.back.eu') }}</a>
                <small class="text-muted d-block mt-1">{{ __('demandes.form.buttons.back.fr') }}</small>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <p class="fw-semibold mb-2">Merci de corriger les champs suivants :</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('demandes.update', $demande) : route('demandes.store') }}" enctype="multipart/form-data"
            class="demande-create-form">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <div class="demande-field-row">
                <div class="demande-field-col flex-grow-1">
                    <label for="demande-title" class="form-label">{{ __('demandes.form.labels.title.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.title.fr') }}</small></label>
                    <input id="demande-title" type="text" name="titre" class="form-control" value="{{ old('titre', $demande->titre ?? '') }}" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : 'required' }}>
                </div>
                <div class="demande-field-col demande-field-sm">
                    <label for="demande-urgency" class="form-label">{{ __('demandes.form.labels.urgency.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.urgency.fr') }}</small></label>
                    <select id="demande-urgency" name="urgence" class="form-select" required>
                        @foreach ($urgences as $urgence)
                            <option value="{{ $urgence }}" @selected(old('urgence', $demande->urgence ?? '') === $urgence)>{{ $urgence }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col w-100">
                    <label for="demande-description" class="form-label">{{ __('demandes.form.labels.description.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.description.fr') }}</small></label>
                    <textarea id="demande-description" name="description" rows="4" class="form-control" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : 'required' }}>{{ old('description', $demande->description ?? '') }}</textarea>
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col flex-grow-1">
                    <label for="demande-planned-expense" class="form-label">{{ __('demandes.form.labels.planned_expense.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.planned_expense.fr') }}</small></label>
                    <input id="demande-planned-expense" type="number" step="0.01" min="0" name="montantP" class="form-control"
                        value="{{ old('montantP', $demande->montantP ?? '') }}" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : '' }}>
                </div>
            </div>

            {{-- Sélecteur d'événement associé (optionnel) --}}
            <div class="demande-field-row">
                <div class="demande-field-col w-100">
                    <label for="demande-evenement" class="form-label">{{ __('demandes.form.labels.evenement.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.evenement.fr') }}</small></label>
                    <select id="demande-evenement" name="idEvenement" class="form-select" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : '' }}>
                        <option value="">{{ __('demandes.form.evenement_none') }}</option>
                        @foreach ($evenements as $evenement)
                            <option value="{{ $evenement->idEvenement }}"
                                data-roles="{{ $evenement->roles->pluck('idRole')->toJson() }}"
                                @selected(old('idEvenement', $demande->idEvenement ?? '') == $evenement->idEvenement)>
                                {{ $evenement->titre }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('demandes.form.evenement_hint') }}</small>
                </div>
            </div>

            {{-- Sélecteur de rôles cibles --}}
            <div class="demande-field-row">
                <div class="demande-field-col w-100">
                    <div class="form-label fw-semibold mb-2">
                        {{ __('demandes.form.labels.cibles.eu') }}
                        <small class="text-muted d-block">{{ __('demandes.form.labels.cibles.fr') }}</small>
                    </div>

                    <div class="role-selector-container">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="role-search" class="form-label small">{{ __('evenements.search_cible') }}</label>
                                <input type="text" id="role-search" class="form-control"
                                    placeholder="{{ __('evenements.search_cible_placeholder') }}"
                                    {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : '' }}>
                                <div id="available-roles" class="role-list mt-2">
                                    @foreach ($roles as $role)
                                        <div class="role-item" data-role-id="{{ $role->idRole }}"
                                            data-role-name="{{ $role->name }}">
                                            <span>{{ $role->name }}</span>
                                            <i class="bi bi-plus-circle"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label small mb-2">{{ __('evenements.cibles_selected') }}</div>
                                <div id="selected-roles" class="role-list mt-2">
                                    <div class="role-list-empty-message">{{ __('evenements.no_cible_selected') }}</div>
                                </div>
                            </div>
                        </div>

                        <div id="role-inputs">
                            {{-- Les inputs seront ajoutés dynamiquement par JavaScript --}}
                        </div>
                    </div>
                    <small class="text-muted">{{ __('demandes.form.cibles_hint') }}</small>
                </div>
            </div>

            @if(!$isEdit)
                <div class="demande-field-row photo-row align-items-center">
                    <div class="demande-field-col flex-grow-1 d-flex align-items-center gap-4">
                    <label for="photos-input" class="form-label mb-0">{{ __('demandes.form.labels.photo.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.photo.fr') }}</small></label>
                        <div class="d-flex flex-column">
                            <label for="photos-input" class="demande-upload-btn mb-0">
                            <input id="photos-input" type="file" name="photos[]" class="d-none" accept=".jpg,.jpeg,.png" multiple>
                            {{ __('demandes.form.buttons.upload.eu') }}
                            </label>
                        <small class="text-muted d-block mt-1 text-center">{{ __('demandes.form.buttons.upload.fr') }}</small>
                        </div>
                    </div>
                </div>
                <div id="photos-preview" class="row g-2 mt-2"></div>
            @endif

            <div class="demande-form-actions d-flex justify-content-end gap-4 mt-4">
                <div class="text-center">
                    <a href="{{ route('demandes.index') }}" class="btn demande-btn-outline px-5">{{ __('demandes.form.buttons.back.eu') }}</a>
                    <small class="text-muted d-block mt-1">{{ __('demandes.form.buttons.back.fr') }}</small>
                </div>
                <div class="text-center">
                    @if($isEdit && $demande->etat === 'Terminé')
                        <button type="button" class="btn demande-btn-primary px-5" disabled>{{ __('demandes.form.buttons.disabled') }}</button>
                        <small class="text-muted d-block mt-1">{{ __('demandes.form.buttons.disabled_sub') }}</small>
                    @else
                        <button type="submit" class="btn demande-btn-primary px-5">{{ __('demandes.form.buttons.save.eu') }}</button>
                        <small class="text-muted d-block mt-1">{{ __('demandes.form.buttons.save.fr') }}</small>
                    @endif
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // === Gestion de l'aperçu des photos ===
        const input = document.getElementById('photos-input');
        const preview = document.getElementById('photos-preview');

        if (input && preview) {
            const noFileText = '{{ __('demandes.form.no_file') }}';

            input.addEventListener('change', function () {
                preview.innerHTML = '';
                const files = Array.from(this.files || []);

                if (!files.length) {
                    preview.innerHTML = `<div class="text-muted small">${noFileText}</div>`;
                    return;
                }

                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const col = document.createElement('div');
                        col.className = 'col-md-3';
                        const thumbDiv = document.createElement('div');
                        thumbDiv.className = 'demande-photo-thumb';
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.setAttribute('alt', file.name);
                        thumbDiv.appendChild(img);
                        const nameDiv = document.createElement('div');
                        nameDiv.className = 'small text-muted text-truncate';
                        nameDiv.textContent = file.name;
                        col.appendChild(thumbDiv);
                        col.appendChild(nameDiv);
                        preview.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        // === Gestion du sélecteur de rôles ===
        const roleSearch = document.getElementById('role-search');
        const availableRoles = document.getElementById('available-roles');
        const selectedRoles = document.getElementById('selected-roles');
        const roleInputs = document.getElementById('role-inputs');
        const evenementSelect = document.getElementById('demande-evenement');

        if (!roleSearch || !availableRoles || !selectedRoles || !roleInputs) return;

        const selectedRoleIds = new Set();
        const allRolesData = @json($roles->map(fn($r) => ['idRole' => $r->idRole, 'name' => $r->name])->values());
        const emptyMessage = @json(__('evenements.no_cible_selected'));

        // Normaliser une chaîne en supprimant les accents
        function normalizeString(str) {
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }

        // Cache pour les noms de rôles normalisés
        const roleNamesCache = new Map();
        const roleElements = Array.from(availableRoles.querySelectorAll('.role-item'));
        roleElements.forEach(role => {
            roleNamesCache.set(role, normalizeString(role.dataset.roleName));
        });

        // Créer un élément de rôle sélectionné
        function createSelectedRoleElement(roleId, roleName) {
            const roleElement = document.createElement('div');
            roleElement.className = 'role-item selected';
            roleElement.dataset.roleId = roleId;
            roleElement.dataset.roleName = roleName;
            roleElement.innerHTML = `<span>${roleName}</span><i class="bi bi-dash-circle"></i>`;
            roleElement.addEventListener('click', function() {
                removeRole(roleId);
            });
            return roleElement;
        }

        // Ajouter un rôle à la sélection
        function addRole(roleId) {
            if (selectedRoleIds.has(roleId)) return;

            selectedRoleIds.add(roleId);
            const roleData = allRolesData.find(r => r.idRole == roleId);
            if (!roleData) return;

            const emptyEl = selectedRoles.querySelector('.role-list-empty-message');
            if (emptyEl) emptyEl.remove();

            selectedRoles.appendChild(createSelectedRoleElement(roleId, roleData.name));

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'roles[]';
            hiddenInput.value = roleId;
            roleInputs.appendChild(hiddenInput);

            updateAvailableRolesDisplay();
        }

        // Retirer un rôle de la sélection
        function removeRole(roleId) {
            if (!selectedRoleIds.has(roleId)) return;

            selectedRoleIds.delete(roleId);
            selectedRoles.querySelector(`[data-role-id="${roleId}"]`)?.remove();
            roleInputs.querySelector(`input[value="${roleId}"]`)?.remove();

            if (selectedRoleIds.size === 0) {
                const emptyEl = document.createElement('div');
                emptyEl.className = 'role-list-empty-message';
                emptyEl.textContent = emptyMessage;
                selectedRoles.appendChild(emptyEl);
            }

            updateAvailableRolesDisplay();
        }

        // Vider tous les rôles sélectionnés
        function clearAllRoles() {
            selectedRoleIds.clear();
            selectedRoles.innerHTML = '';
            roleInputs.innerHTML = '';
            const emptyEl = document.createElement('div');
            emptyEl.className = 'role-list-empty-message';
            emptyEl.textContent = emptyMessage;
            selectedRoles.appendChild(emptyEl);
            updateAvailableRolesDisplay();
        }

        // Mettre à jour l'affichage des rôles disponibles
        function updateAvailableRolesDisplay() {
            const searchTerm = roleSearch.value.trim();
            const normalizedTerm = searchTerm ? normalizeString(searchTerm) : '';

            roleElements.forEach(role => {
                const roleId = role.dataset.roleId;
                if (selectedRoleIds.has(roleId)) {
                    role.style.display = 'none';
                } else if (normalizedTerm) {
                    const normalizedRoleName = roleNamesCache.get(role);
                    role.style.display = normalizedRoleName.includes(normalizedTerm) ? 'flex' : 'none';
                } else {
                    role.style.display = 'flex';
                }
            });
        }

        // Recherche de rôles
        roleSearch.addEventListener('input', function() {
            updateAvailableRolesDisplay();
        });

        // Click sur un rôle disponible
        availableRoles.addEventListener('click', function(e) {
            const roleItem = e.target.closest('.role-item');
            if (roleItem && !roleItem.classList.contains('selected')) {
                addRole(roleItem.dataset.roleId);
            }
        });

        // === Pré-remplissage des rôles depuis l'événement ===
        if (evenementSelect) {
            evenementSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const rolesData = selectedOption.dataset.roles;

                if (rolesData) {
                    try {
                        const eventRoles = JSON.parse(rolesData);
                        if (Array.isArray(eventRoles) && eventRoles.length > 0) {
                            clearAllRoles();
                            eventRoles.forEach(roleId => addRole(String(roleId)));
                        }
                    } catch (e) {
                        console.error('Erreur parsing roles:', e);
                    }
                }
            });
        }

        // === Restauration des rôles existants ===
        @if($isEdit && isset($demande) && $demande->roles)
            const existingRoles = @json($demande->roles->pluck('idRole'));
            existingRoles.forEach(roleId => addRole(String(roleId)));
        @endif

        // Restaurer les valeurs old() si présentes
        const oldRoles = @json(old('roles', []));
        if (Array.isArray(oldRoles) && oldRoles.length > 0) {
            clearAllRoles();
            oldRoles.forEach(roleId => addRole(String(roleId)));
        }
    });
</script>

