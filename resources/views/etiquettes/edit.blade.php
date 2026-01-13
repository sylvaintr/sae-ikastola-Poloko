<x-app-layout>

    <div class="container py-4">
        <a href="{{ route('admin.etiquettes.index') }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('etiquette.retour') }}</span>
        </a>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-4">{{ Lang::get('etiquette.nouvelle', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <p class="fw-light mb-0">{{ Lang::get('etiquette.nouvelle') }}</p>
                    @endif
                </h1>

                <form action="{{ route('admin.etiquettes.update', $etiquette->idEtiquette) }}" method="POST" enctype="multipart/form-data" class="admin-form">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-12">
                            <label for="nom" class="form-label fw-semibold">Nom de l'étiquette</label>
                            <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $etiquette->nom) }}" required maxlength="50">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" {{ old('is_public', $etiquette->is_public) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_public">{{ __('etiquette.is_public') }}</label>
                                <div class="form-text">{{ __('etiquette.is_public_help') }}</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-label fw-semibold mb-2">{{ __('admin.accounts_page.create.fields.roles') }}</div>
                            <div class="role-selector-container">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="role-search" class="form-label small">{{ __('admin.accounts_page.create.fields.roles_search') }}</label>
                                        <input type="text" id="role-search" class="form-control" placeholder="{{ __('admin.accounts_page.create.fields.roles_search_placeholder') }}">
                                        <div id="available-roles" class="role-list mt-2">
                                            @php
                                                $selectedRoleIds = old('roles', $etiquette->roles->pluck('idRole')->toArray());
                                            @endphp
                                            @foreach ($roles as $role)
                                                @if (!in_array($role->idRole, $selectedRoleIds))
                                                    <div class="role-item" data-role-id="{{ $role->idRole }}" data-role-name="{{ $role->name }}">
                                                        <span>{{ $role->name }}</span>
                                                        <i class="bi bi-plus-circle"></i>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-label small mb-2">{{ __('admin.accounts_page.create.fields.roles_selected') }} <span class="text-danger">*</span></div>
                                        <div id="selected-roles" class="role-list mt-2">
                                            @if (count($selectedRoleIds) === 0)
                                                <div class="role-list-empty-message">Aucun rôle n'a été sélectionné</div>
                                            @else
                                                @foreach ($roles as $role)
                                                    @if (in_array($role->idRole, $selectedRoleIds))
                                                        <div class="role-item selected" data-role-id="{{ $role->idRole }}" data-role-name="{{ $role->name }}">
                                                            <span>{{ $role->name }}</span>
                                                            <i class="bi bi-x-circle"></i>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                        <div id="roles-error" class="invalid-feedback d-none mt-2">Au moins un rôle doit être sélectionné.</div>
                                    </div>
                                </div>

                                <div id="role-inputs">
                                    {{-- Préremplir les inputs pour les rôles déjà sélectionnés --}}
                                    @foreach ($selectedRoleIds as $rid)
                                        <input type="hidden" name="roles[]" value="{{ $rid }}">
                                    @endforeach
                                </div>
                            </div>
                            @error('roles')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                            @error('roles.*')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4 justify-content-end">
                        <a href="{{ route('admin.etiquettes.index') }}" class="btn admin-cancel-btn px-4">{{ __('etiquette.annuler') }}</a>
                        <button type="submit" class="btn fw-semibold px-4 admin-submit-btn">{{ __('etiquette.enregistrer') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const roleSearch = document.getElementById('role-search');
                const availableRoles = document.getElementById('available-roles');
                const selectedRoles = document.getElementById('selected-roles');
                const roleInputs = document.getElementById('role-inputs');
                const rolesError = document.getElementById('roles-error');
                const selectedRoleIds = new Set(Array.from(roleInputs.querySelectorAll('input[name="roles[]"]')).map(i => i.value));
                const allRolesData = @json($roles->map(function ($r) { return ['idRole' => $r->idRole, 'name' => $r->name]; })->values());

                function normalizeString(str) {
                    return str.normalize('NFD').replace(/[\u0000-\\u036f]/g, '').toLowerCase();
                }

                const roleNamesCache = new Map();
                const roleElements = Array.from(availableRoles.querySelectorAll('.role-item'));
                roleElements.forEach(role => {
                    roleNamesCache.set(role, normalizeString(role.dataset.roleName));
                });

                function filterRoles(searchTerm) {
                    const normalizedTerm = normalizeString(searchTerm.trim());
                    const hasTerm = normalizedTerm.length > 0;

                    requestAnimationFrame(() => {
                        let hasChanges = false;

                        roleElements.forEach(role => {
                            const roleId = role.dataset.roleId;
                            const isSelected = selectedRoleIds.has(roleId);

                            if (isSelected) {
                                if (role.style.display !== 'none') {
                                    role.style.display = 'none';
                                    hasChanges = true;
                                }
                                return;
                            }

                            if (!hasTerm) {
                                if (role.style.display !== 'flex') {
                                    role.style.display = 'flex';
                                    hasChanges = true;
                                }
                                return;
                            }

                            const normalizedRoleName = roleNamesCache.get(role);
                            const shouldShow = normalizedRoleName.includes(normalizedTerm);
                            const currentDisplay = role.style.display;

                            if (shouldShow && currentDisplay !== 'flex') {
                                role.style.display = 'flex';
                                hasChanges = true;
                            } else if (!shouldShow && currentDisplay !== 'none') {
                                role.style.display = 'none';
                                hasChanges = true;
                            }
                        });

                        if (hasChanges && availableRoles.offsetHeight) {
                            availableRoles.offsetHeight;
                        }
                    });
                }

                function updateEmptyMessage() {
                    const emptyMessage = selectedRoles.querySelector('.role-list-empty-message');
                    if (selectedRoleIds.size === 0) {
                        if (!emptyMessage) {
                            const message = document.createElement('div');
                            message.className = 'role-list-empty-message';
                            message.textContent = 'Aucun rôle n\'a été sélectionné';
                            selectedRoles.appendChild(message);
                        }
                    } else {
                        if (emptyMessage) {
                            emptyMessage.remove();
                        }
                    }
                }

                function validateRoles() {
                    if (selectedRoleIds.size === 0) {
                        rolesError.classList.remove('d-none');
                        rolesError.classList.add('d-block');
                        return false;
                    } else {
                        rolesError.classList.remove('d-block');
                        rolesError.classList.add('d-none');
                        return true;
                    }
                }

                function createRoleItem(role) {
                    const roleItem = document.createElement('div');
                    roleItem.className = 'role-item';
                    roleItem.dataset.roleId = role.idRole;
                    roleItem.dataset.roleName = role.name;

                    const span = document.createElement('span');
                    span.textContent = role.name;
                    const icon = document.createElement('i');
                    icon.className = 'bi bi-plus-circle';

                    roleItem.appendChild(span);
                    roleItem.appendChild(icon);

                    roleItem.addEventListener('click', function() {
                        addRole(roleItem);
                    });

                    roleNamesCache.set(roleItem, normalizeString(role.name));
                    roleElements.push(roleItem);

                    return roleItem;
                }

                let searchTimeout;
                roleSearch.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        filterRoles(e.target.value);
                    }, 150);
                });

                function addRole(roleItem) {
                    const roleId = roleItem.dataset.roleId;
                    const roleName = roleItem.dataset.roleName;

                    if (selectedRoleIds.has(roleId)) {
                        return;
                    }

                    selectedRoleIds.add(roleId);

                    requestAnimationFrame(() => {
                        const selectedItem = document.createElement('div');
                        selectedItem.className = 'role-item selected';
                        selectedItem.dataset.roleId = roleId;
                        selectedItem.dataset.roleName = roleName;

                        const span = document.createElement('span');
                        span.textContent = roleName;
                        const icon = document.createElement('i');
                        icon.className = 'bi bi-x-circle';

                        selectedItem.appendChild(span);
                        selectedItem.appendChild(icon);

                        selectedItem.addEventListener('click', function() {
                            removeRole(roleId);
                        });

                        selectedRoles.appendChild(selectedItem);

                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'roles[]';
                        input.value = roleId;
                        roleInputs.appendChild(input);

                        roleItem.style.display = 'none';
                        roleSearch.value = '';

                        roleElements.forEach(el => {
                            if (!selectedRoleIds.has(el.dataset.roleId)) {
                                el.style.display = 'flex';
                            }
                        });

                        updateEmptyMessage();
                        validateRoles();
                    });
                }

                function removeRole(roleId) {
                    selectedRoleIds.delete(roleId);

                    requestAnimationFrame(() => {
                        const selectedItem = selectedRoles.querySelector(`[data-role-id="${roleId}"]`);
                        if (selectedItem) {
                            selectedItem.remove();
                        }

                        const input = roleInputs.querySelector(`input[value="${roleId}"]`);
                        if (input) {
                            input.remove();
                        }

                        let availableItem = roleElements.find(el => el.dataset.roleId == roleId);
                        if (!availableItem) {
                            const role = allRolesData.find(r => r.idRole == roleId);
                            if (role) {
                                availableItem = createRoleItem(role);
                                roleNamesCache.set(availableItem, normalizeString(role.name));
                                roleElements.push(availableItem);
                                availableRoles.appendChild(availableItem);
                            }
                        } else {
                            availableItem.style.display = 'flex';
                        }

                        if (availableItem && roleSearch.value.trim().length > 0) {
                            filterRoles(roleSearch.value);
                        } else if (availableItem) {
                            availableItem.style.display = 'flex';
                        }

                        updateEmptyMessage();
                        validateRoles();
                    });
                }

                availableRoles.addEventListener('click', function(e) {
                    const roleItem = e.target.closest('.role-item');
                    if (roleItem && !selectedRoleIds.has(roleItem.dataset.roleId)) {
                        addRole(roleItem);
                    }
                });

                selectedRoles.addEventListener('click', function(e) {
                    const roleItem = e.target.closest('.role-item');
                    if (roleItem) {
                        removeRole(roleItem.dataset.roleId);
                    }
                });

                const form = document.querySelector('form');
                form.addEventListener('submit', function(e) {
                    if (!validateRoles()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });

                updateEmptyMessage();
            });
        </script>
    @endpush
</x-app-layout>

