<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.accounts.show', $account) }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.accounts_page.back') }}</span>
        </a>

        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-4">{{ __('admin.accounts_page.edit.title') }}</h1>

                <form method="POST" action="{{ route('admin.accounts.update', $account) }}" class="admin-form">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="prenom" class="form-label fw-semibold">{{ __('admin.accounts_page.create.fields.first_name') }}</label>
                            <input id="prenom" name="prenom" type="text" class="form-control @error('prenom') is-invalid @enderror"
                                   value="{{ old('prenom', $account->prenom) }}" required maxlength="15">
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nom" class="form-label fw-semibold">{{ __('admin.accounts_page.create.fields.last_name') }}</label>
                            <input id="nom" name="nom" type="text" class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom', $account->nom) }}" required maxlength="15">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">{{ __('admin.accounts_page.create.fields.email') }}</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $account->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="languePref" class="form-label fw-semibold">{{ __('admin.accounts_page.create.fields.language') }}</label>
                            <select id="languePref" name="languePref" class="form-select @error('languePref') is-invalid @enderror" required>
                                <option value="fr" {{ old('languePref', $account->languePref) === 'fr' ? 'selected' : '' }}>Français</option>
                                <option value="eus" {{ old('languePref', $account->languePref) === 'eus' ? 'selected' : '' }}>Euskara</option>
                            </select>
                            @error('languePref')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="mdp" class="form-label fw-semibold">{{ __('admin.accounts_page.edit.fields.password') }}</label>
                            <input id="mdp" name="mdp" type="password" class="form-control @error('mdp') is-invalid @enderror"
                                   minlength="8" autocomplete="new-password">
                            <div class="password-strength-container mt-2">
                                <div class="password-strength-bar">
                                    <div id="password-strength-fill" class="password-strength-fill"></div>
                                </div>
                                <small id="password-strength-text" class="password-strength-text"></small>
                            </div>
                            <small class="text-muted d-block mt-1">{{ __('admin.accounts_page.edit.fields.password_help') }}</small>
                            @error('mdp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="mdp_confirmation" class="form-label fw-semibold">{{ __('admin.accounts_page.edit.fields.password_confirmation') }}</label>
                            <input id="mdp_confirmation" name="mdp_confirmation" type="password" class="form-control @error('mdp_confirmation') is-invalid @enderror"
                                   minlength="8" autocomplete="new-password">
                            <small id="password-match-text" class="text-muted mt-1 d-block"></small>
                            @error('mdp_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="statutValidation" class="form-label fw-semibold">{{ __('admin.accounts_page.create.fields.status') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input id="statutValidation" name="statutValidation" type="checkbox" class="form-check-input" value="1" {{ old('statutValidation', $account->statutValidation) ? 'checked' : '' }}>
                                <label for="statutValidation" class="form-check-label">Validé</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('admin.accounts_page.edit.fields.roles') }}</label>
                            
                            <div class="role-selector-container">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="role-search" class="form-label small">{{ __('admin.accounts_page.edit.fields.roles_search') }}</label>
                                        <input type="text" id="role-search" class="form-control" placeholder="{{ __('admin.accounts_page.edit.fields.roles_search_placeholder') }}">
                                        <div id="available-roles" class="role-list mt-2">
                                            @php
                                                $selectedRoleIds = old('roles', $account->rolesCustom->pluck('idRole')->toArray());
                                            @endphp
                                            @foreach($roles as $role)
                                                @if(!in_array($role->idRole, $selectedRoleIds))
                                                    <div class="role-item" data-role-id="{{ $role->idRole }}" data-role-name="{{ $role->name }}">
                                                        <span>{{ $role->name }}</span>
                                                        <i class="bi bi-plus-circle"></i>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">{{ __('admin.accounts_page.edit.fields.roles_selected') }} <span class="text-danger">*</span></label>
                                        <div id="selected-roles" class="role-list mt-2">
                                            @if(count($selectedRoleIds) === 0)
                                                <div class="role-list-empty-message">Aucun rôle n'a été sélectionné</div>
                                            @else
                                                @foreach($roles as $role)
                                                    @if(in_array($role->idRole, $selectedRoleIds))
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
                                    @foreach($selectedRoleIds as $roleId)
                                        <input type="hidden" name="roles[]" value="{{ $roleId }}">
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
                        <a href="{{ route('admin.accounts.show', $account) }}" class="btn admin-cancel-btn px-4">
                            {{ __('admin.accounts_page.edit.cancel') }}
                        </a>
                        <button type="submit" class="btn fw-semibold px-4 admin-submit-btn">
                            {{ __('admin.accounts_page.edit.submit') }}
                        </button>
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
            const selectedRoleIds = new Set();
            const allRolesData = @json($roles->map(function($r) { return ['idRole' => $r->idRole, 'name' => $r->name]; })->values());
            const form = document.querySelector('form');
            
            // Initialiser les rôles déjà sélectionnés
            document.querySelectorAll('#selected-roles .role-item').forEach(item => {
                const roleId = item.dataset.roleId;
                selectedRoleIds.add(roleId);
            });
            
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
            
            // Filtrer les rôles disponibles (optimisé avec batch DOM updates)
            function filterRoles(searchTerm) {
                const normalizedTerm = normalizeString(searchTerm.trim());
                const hasTerm = normalizedTerm.length > 0;
                
                // Utiliser requestAnimationFrame pour de meilleures performances
                requestAnimationFrame(() => {
                    // Batch les modifications DOM
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
                    
                    // Force un reflow seulement si nécessaire
                    if (hasChanges && availableRoles.offsetHeight) {
                        availableRoles.offsetHeight;
                    }
                });
            }
            
            // Mettre à jour le message vide
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
            
            // Valider les rôles
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
            
            // Créer un élément de rôle disponible (optimisé)
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
                
                // Ajouter au cache
                roleNamesCache.set(roleItem, normalizeString(role.name));
                roleElements.push(roleItem);
                
                return roleItem;
            }
            
            // Debounce pour la recherche de rôles
            let searchTimeout;
            roleSearch.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterRoles(e.target.value);
                }, 150);
            });
            
            // Ajouter un rôle (optimisé avec DocumentFragment)
            function addRole(roleItem) {
                const roleId = roleItem.dataset.roleId;
                const roleName = roleItem.dataset.roleName;
                
                if (selectedRoleIds.has(roleId)) {
                    return;
                }
                
                selectedRoleIds.add(roleId);
                
                // Utiliser requestAnimationFrame pour les mises à jour DOM
                requestAnimationFrame(() => {
                    // Créer l'élément dans la liste sélectionnée
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
                    
                    // Ajouter l'événement pour retirer
                    selectedItem.addEventListener('click', function() {
                        removeRole(roleId);
                    });
                    
                    selectedRoles.appendChild(selectedItem);
                    
                    // Créer l'input hidden
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'roles[]';
                    input.value = roleId;
                    roleInputs.appendChild(input);
                    
                    // Masquer le rôle de la liste disponible
                    roleItem.style.display = 'none';
                    
                    // Réinitialiser la recherche et réafficher tous les rôles
                    roleSearch.value = '';
                    
                    // Réafficher tous les rôles non sélectionnés
                    roleElements.forEach(el => {
                        if (!selectedRoleIds.has(el.dataset.roleId)) {
                            el.style.display = 'flex';
                        }
                    });
                    
                    // Mettre à jour le message vide
                    updateEmptyMessage();
                    
                    // Valider les rôles
                    validateRoles();
                });
            }
            
            // Retirer un rôle (optimisé)
            function removeRole(roleId) {
                selectedRoleIds.delete(roleId);
                
                requestAnimationFrame(() => {
                    // Retirer de la liste sélectionnée
                    const selectedItem = selectedRoles.querySelector(`[data-role-id="${roleId}"]`);
                    if (selectedItem) {
                        selectedItem.remove();
                    }
                    
                    // Retirer l'input hidden
                    const input = roleInputs.querySelector(`input[value="${roleId}"]`);
                    if (input) {
                        input.remove();
                    }
                    
                    // Réafficher dans la liste disponible
                    let availableItem = roleElements.find(el => el.dataset.roleId == roleId);
                    if (!availableItem) {
                        // Si le rôle n'existe pas dans la liste disponible, le créer
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
                    
                    // Réappliquer le filtre de recherche seulement si nécessaire
                    if (availableItem && roleSearch.value.trim().length > 0) {
                        filterRoles(roleSearch.value);
                    } else if (availableItem) {
                        availableItem.style.display = 'flex';
                    }
                    
                    // Mettre à jour le message vide
                    updateEmptyMessage();
                    
                    // Valider les rôles
                    validateRoles();
                });
            }
            
            // Ajouter les événements aux rôles disponibles
            availableRoles.addEventListener('click', function(e) {
                const roleItem = e.target.closest('.role-item');
                if (roleItem && !selectedRoleIds.has(roleItem.dataset.roleId)) {
                    addRole(roleItem);
                }
            });
            
            // Ajouter les événements aux rôles sélectionnés
            selectedRoles.addEventListener('click', function(e) {
                const roleItem = e.target.closest('.role-item');
                if (roleItem) {
                    removeRole(roleItem.dataset.roleId);
                }
            });
            
            // Valider avant la soumission du formulaire
            form.addEventListener('submit', function(e) {
                if (!validateRoles()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
            
            // Initialiser l'affichage
            filterRoles('');
            updateEmptyMessage();
            
            // Gestion de la force du mot de passe
            const passwordInput = document.getElementById('mdp');
            const passwordConfirmationInput = document.getElementById('mdp_confirmation');
            const passwordStrengthFill = document.getElementById('password-strength-fill');
            const passwordStrengthText = document.getElementById('password-strength-text');
            const passwordMatchText = document.getElementById('password-match-text');
            
            // Cache pour éviter de recalculer la force
            let lastPassword = '';
            let lastStrengthResult = null;
            
            function calculatePasswordStrength(password) {
                // Utiliser le cache si le mot de passe n'a pas changé
                if (password === lastPassword && lastStrengthResult) {
                    return lastStrengthResult;
                }
                
                let strength = 0;
                
                if (password.length === 0) {
                    lastPassword = '';
                    lastStrengthResult = { strength: 0, label: '', class: '' };
                    return lastStrengthResult;
                }
                
                const hasLower = /[a-z]/.test(password);
                const hasUpper = /[A-Z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecial = /[^a-zA-Z0-9]/.test(password);
                
                // Longueur
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                
                // Types de caractères
                if (hasLower) strength++;
                if (hasUpper) strength++;
                if (hasNumber) strength++;
                if (hasSpecial) strength++;
                
                // Déterminer le niveau de force
                let strengthLabel = '';
                let strengthClass = '';
                
                if (strength <= 2) {
                    strengthLabel = '{{ __('admin.accounts_page.password_strength.weak') }}';
                    strengthClass = 'weak';
                } else if (strength <= 3) {
                    strengthLabel = '{{ __('admin.accounts_page.password_strength.medium') }}';
                    strengthClass = 'medium';
                } else if (strength <= 4) {
                    strengthLabel = '{{ __('admin.accounts_page.password_strength.strong') }}';
                    strengthClass = 'strong';
                } else {
                    strengthLabel = '{{ __('admin.accounts_page.password_strength.very_strong') }}';
                    strengthClass = 'very-strong';
                }
                
                lastPassword = password;
                lastStrengthResult = { strength, label: strengthLabel, class: strengthClass };
                return lastStrengthResult;
            }
            
            // Debounce pour la mise à jour de la force du mot de passe
            let passwordStrengthTimeout;
            function updatePasswordStrength() {
                clearTimeout(passwordStrengthTimeout);
                passwordStrengthTimeout = setTimeout(() => {
                    const password = passwordInput.value;
                    const strength = calculatePasswordStrength(password);
                    
                    requestAnimationFrame(() => {
                        if (password.length === 0) {
                            passwordStrengthFill.style.width = '0%';
                            passwordStrengthFill.className = 'password-strength-fill';
                            passwordStrengthText.textContent = '';
                            passwordStrengthText.className = 'password-strength-text';
                        } else {
                            passwordStrengthFill.className = 'password-strength-fill ' + strength.class;
                            passwordStrengthText.textContent = strength.label;
                            passwordStrengthText.className = 'password-strength-text ' + strength.class;
                        }
                    });
                }, 100);
            }
            
            // Debounce pour la vérification de correspondance
            let passwordMatchTimeout;
            function checkPasswordMatch() {
                clearTimeout(passwordMatchTimeout);
                passwordMatchTimeout = setTimeout(() => {
                    const password = passwordInput.value;
                    const confirmation = passwordConfirmationInput.value;
                    
                    // Si les deux champs sont vides, ne rien afficher (pour l'édition)
                    if (password.length === 0 && confirmation.length === 0) {
                        requestAnimationFrame(() => {
                            passwordMatchText.textContent = '';
                            passwordMatchText.className = '';
                        });
                        return;
                    }
                    
                    // Si un seul des deux champs est rempli, ne rien afficher
                    if (password.length === 0 || confirmation.length === 0) {
                        requestAnimationFrame(() => {
                            passwordMatchText.textContent = '';
                            passwordMatchText.className = '';
                        });
                        return;
                    }
                    
                    requestAnimationFrame(() => {
                        if (password === confirmation) {
                            passwordMatchText.textContent = '{{ __('admin.accounts_page.password_strength.match') }}';
                            passwordMatchText.className = 'match';
                        } else {
                            passwordMatchText.textContent = '{{ __('admin.accounts_page.password_strength.no_match') }}';
                            passwordMatchText.className = 'no-match';
                        }
                    });
                }, 150);
            }
            
            passwordInput.addEventListener('input', function() {
                updatePasswordStrength();
                checkPasswordMatch();
            });
            
            passwordConfirmationInput.addEventListener('input', function() {
                checkPasswordMatch();
            });
            
            // Validation avant soumission
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmation = passwordConfirmationInput.value;
                
                // Si un mot de passe est saisi, la confirmation est requise
                if (password.length > 0 && password !== confirmation) {
                    e.preventDefault();
                    e.stopPropagation();
                    passwordConfirmationInput.classList.add('is-invalid');
                    passwordMatchText.textContent = '{{ __('admin.accounts_page.password_strength.no_match') }}';
                    passwordMatchText.className = 'no-match';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

