<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.obligatory_documents.index') }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.obligatory_documents.back') }}</span>
        </a>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-4">{{ __('admin.obligatory_documents.create.title') }}</h1>

                <form method="POST" action="{{ route('admin.obligatory_documents.store') }}" class="admin-form" id="document-form">
                    @csrf

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="nom" class="form-label fw-semibold">{{ __('admin.obligatory_documents.fields.name') }} <span class="text-danger">*</span></label>
                            <input id="nom" name="nom" type="text" class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom') }}" required maxlength="100">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="expirationType" class="form-label fw-semibold">{{ __('admin.obligatory_documents.fields.expiration_type') }}</label>
                            <select id="expirationType" name="expirationType" class="form-select @error('expirationType') is-invalid @enderror" required>
                                <option value="none" {{ old('expirationType', 'none') === 'none' ? 'selected' : '' }}>{{ __('admin.obligatory_documents.fields.expiration_none') }}</option>
                                <option value="delai" {{ old('expirationType') === 'delai' ? 'selected' : '' }}>{{ __('admin.obligatory_documents.fields.expiration_delai') }}</option>
                                <option value="date" {{ old('expirationType') === 'date' ? 'selected' : '' }}>{{ __('admin.obligatory_documents.fields.expiration_date') }}</option>
                            </select>
                            @error('expirationType')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 expiration-delai-field" style="display: {{ old('expirationType') === 'delai' ? 'block' : 'none' }};">
                            <label for="delai" class="form-label fw-semibold">{{ __('admin.obligatory_documents.fields.delai') }} <span class="text-danger">*</span></label>
                            <input id="delai" name="delai" type="number" class="form-control @error('delai') is-invalid @enderror"
                                   value="{{ old('delai') }}" min="0" placeholder="{{ __('admin.obligatory_documents.fields.delai_placeholder') }}">
                            <small class="text-muted d-block mt-1">{{ __('admin.obligatory_documents.fields.delai_help') }}</small>
                            @error('delai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 expiration-date-field" style="display: {{ old('expirationType') === 'date' ? 'block' : 'none' }};">
                            <label for="dateExpiration" class="form-label fw-semibold">{{ __('admin.obligatory_documents.fields.date_expiration') }} <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center gap-2 position-relative">
                                <div id="display-expiration-date" class="fw-semibold me-1 presence-date-text"></div>
                                <button id="open-expiration-date" type="button" class="btn btn-link p-0 presence-date-btn" aria-label="Choisir la date" style="color: #e48a1f;">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <input id="dateExpiration" name="dateExpiration" type="date" value="{{ old('dateExpiration') }}" class="presence-date-input-hidden @error('dateExpiration') is-invalid @enderror">
                                <div id="custom-expiration-calendar" class="presence-calendar-dropdown"></div>
                            </div>
                            @error('dateExpiration')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-label fw-semibold mb-2">{{ __('admin.obligatory_documents.fields.roles') }} <span class="text-danger">*</span></div>
                            
                            <div class="role-selector-container">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="role-search" class="form-label small">{{ __('admin.obligatory_documents.fields.roles_search') }}</label>
                                        <input type="text" id="role-search" class="form-control" placeholder="{{ __('admin.obligatory_documents.fields.roles_search_placeholder') }}">
                                        <div id="available-roles" class="role-list mt-2">
                                            <div class="role-item role-item-all" data-role-all="true" style="background-color: #f0f0f0; font-weight: bold;">
                                                <span>{{ __('admin.obligatory_documents.all_roles_option') }}</span>
                                                <i class="bi bi-plus-circle"></i>
                                            </div>
                                            @foreach($roles as $role)
                                                <div class="role-item" data-role-id="{{ $role->idRole }}" data-role-name="{{ $role->name }}">
                                                    <span>{{ $role->name }}</span>
                                                    <i class="bi bi-plus-circle"></i>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-label small mb-2">{{ __('admin.obligatory_documents.fields.roles_selected') }} <span class="text-danger">*</span></div>
                                        <div id="selected-roles" class="role-list mt-2">
                                            <div class="role-list-empty-message">{{ __('admin.obligatory_documents.fields.no_roles_selected') }}</div>
                                        </div>
                                        <div id="roles-error" class="invalid-feedback d-none mt-2">{{ __('admin.obligatory_documents.fields.roles_required') }}</div>
                                    </div>
                                </div>
                                
                                <div id="role-inputs">
                                    {{-- Les inputs seront ajoutés dynamiquement par JavaScript --}}
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
                        <a href="{{ route('admin.obligatory_documents.index') }}" class="btn admin-cancel-btn px-4">
                            {{ __('admin.obligatory_documents.create.cancel') }}
                        </a>
                        <button type="submit" class="btn fw-semibold px-4 admin-submit-btn">
                            {{ __('admin.obligatory_documents.create.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const expirationTypeSelect = document.getElementById('expirationType');
            const delaiField = document.querySelector('.expiration-delai-field');
            const dateField = document.querySelector('.expiration-date-field');
            const delaiInput = document.getElementById('delai');
            const dateExpirationInput = document.getElementById('dateExpiration');
            const form = document.getElementById('document-form');

            // Afficher/masquer les champs d'expiration selon le type sélectionné
            function toggleExpirationFields() {
                const selectedType = expirationTypeSelect.value;
                
                if (selectedType === 'delai') {
                    delaiField.style.display = 'block';
                    dateField.style.display = 'none';
                    delaiInput.setAttribute('required', 'required');
                    dateExpirationInput.removeAttribute('required');
                    dateExpirationInput.value = '';
                } else if (selectedType === 'date') {
                    delaiField.style.display = 'none';
                    dateField.style.display = 'block';
                    delaiInput.removeAttribute('required');
                    dateExpirationInput.setAttribute('required', 'required');
                    delaiInput.value = '';
                } else {
                    delaiField.style.display = 'none';
                    dateField.style.display = 'none';
                    delaiInput.removeAttribute('required');
                    dateExpirationInput.removeAttribute('required');
                    delaiInput.value = '';
                    dateExpirationInput.value = '';
                }
            }

            expirationTypeSelect.addEventListener('change', toggleExpirationFields);
            toggleExpirationFields(); // Initialiser l'état

            // Calendrier personnalisé pour la date d'expiration
            const expirationDateInput = document.getElementById('dateExpiration');
            const expirationDateDisplay = document.getElementById('display-expiration-date');
            const expirationDateBtn = document.getElementById('open-expiration-date');
            const expirationCalendarDropdown = document.getElementById('custom-expiration-calendar');
            let expirationCalendarVisible = false;
            let expirationCurrentDate = expirationDateInput.value ? new Date(expirationDateInput.value) : new Date();

            function formatDateToYYYYMMDD(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function formatFr(dateStr) {
                if (!dateStr) return '';
                try {
                    const d = new Date(dateStr + 'T00:00:00');
                    const txt = d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                    return txt.charAt(0).toUpperCase() + txt.slice(1);
                } catch (_) { return ''; }
            }

            function renderExpirationDate() {
                expirationDateDisplay.textContent = formatFr(expirationDateInput.value) || 'Sélectionner une date';
            }

            function renderExpirationCalendar() {
                const year = expirationCurrentDate.getFullYear();
                const month = expirationCurrentDate.getMonth();
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const startDate = new Date(firstDay);
                startDate.setDate(startDate.getDate() - startDate.getDay());
                
                const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                
                const currentYear = today.getFullYear();
                const years = [];
                // Permettre les années futures (10 ans dans le futur)
                for (let y = currentYear - 10; y <= currentYear + 10; y++) {
                    years.push(y);
                }
                
                let html = `<div class="presence-calendar">
                    <div class="presence-calendar-header">
                        <button type="button" class="presence-calendar-nav" id="prev-expiration-month"><i class="bi bi-chevron-left"></i></button>
                        <div class="presence-calendar-title-group">
                            <select id="expiration-calendar-month" class="presence-calendar-select">${monthNames.map((m, i) => `<option value="${i}" ${i === month ? 'selected' : ''}>${m}</option>`).join('')}</select>
                            <select id="expiration-calendar-year" class="presence-calendar-select">${years.map(y => `<option value="${y}" ${y === year ? 'selected' : ''}>${y}</option>`).join('')}</select>
                        </div>
                        <button type="button" class="presence-calendar-nav" id="next-expiration-month"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="presence-calendar-weekdays">
                        ${dayNames.map(d => `<div class="presence-calendar-weekday">${d}</div>`).join('')}
                    </div>
                    <div class="presence-calendar-days">`;
                
                const current = new Date(startDate);
                const todayStr = formatDateToYYYYMMDD(today);
                for (let i = 0; i < 42; i++) {
                    const dateStr = formatDateToYYYYMMDD(current);
                    const isCurrentMonth = current.getMonth() === month;
                    const isToday = dateStr === todayStr;
                    const isSelected = dateStr === expirationDateInput.value;
                    
                    let classes = 'presence-calendar-day';
                    if (!isCurrentMonth) classes += ' presence-calendar-day-other';
                    if (isToday) classes += ' presence-calendar-day-today';
                    if (isSelected) classes += ' presence-calendar-day-selected';
                    
                    html += `<div class="${classes}" data-date="${dateStr}">${current.getDate()}</div>`;
                    current.setDate(current.getDate() + 1);
                }
                
                html += `</div></div>`;
                expirationCalendarDropdown.innerHTML = html;
                
                document.getElementById('prev-expiration-month').addEventListener('click', (e) => {
                    e.stopPropagation();
                    expirationCurrentDate.setMonth(expirationCurrentDate.getMonth() - 1);
                    renderExpirationCalendar();
                });
                
                document.getElementById('next-expiration-month').addEventListener('click', (e) => {
                    e.stopPropagation();
                    expirationCurrentDate.setMonth(expirationCurrentDate.getMonth() + 1);
                    renderExpirationCalendar();
                });
                
                document.getElementById('expiration-calendar-month').addEventListener('change', (e) => {
                    e.stopPropagation();
                    expirationCurrentDate.setMonth(parseInt(e.target.value));
                    renderExpirationCalendar();
                });
                
                document.getElementById('expiration-calendar-year').addEventListener('change', (e) => {
                    e.stopPropagation();
                    expirationCurrentDate.setFullYear(parseInt(e.target.value));
                    renderExpirationCalendar();
                });
                
                document.getElementById('expiration-calendar-month').addEventListener('click', (e) => e.stopPropagation());
                document.getElementById('expiration-calendar-year').addEventListener('click', (e) => e.stopPropagation());
                
                document.querySelectorAll('#custom-expiration-calendar .presence-calendar-day[data-date]').forEach(day => {
                    day.addEventListener('click', (e) => {
                        e.stopPropagation();
                        expirationDateInput.value = day.getAttribute('data-date');
                        renderExpirationDate();
                        expirationCalendarDropdown.classList.remove('show');
                        expirationCalendarVisible = false;
                    });
                });
            }

            expirationDateBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                expirationCalendarVisible = !expirationCalendarVisible;
                if (expirationCalendarVisible) {
                    expirationCalendarDropdown.classList.add('show');
                    renderExpirationCalendar();
                } else {
                    expirationCalendarDropdown.classList.remove('show');
                }
            });

            document.addEventListener('click', function(e) {
                if (!expirationDateBtn.contains(e.target) && !expirationCalendarDropdown.contains(e.target)) {
                    expirationCalendarDropdown.classList.remove('show');
                    expirationCalendarVisible = false;
                }
            });

            expirationDateInput.addEventListener('change', renderExpirationDate);
            renderExpirationDate();

            // Gestion des rôles (code similaire à accounts/create.blade.php)
            const roleSearch = document.getElementById('role-search');
            const availableRoles = document.getElementById('available-roles');
            const selectedRoles = document.getElementById('selected-roles');
            const roleInputs = document.getElementById('role-inputs');
            const rolesError = document.getElementById('roles-error');
            const selectedRoleIds = new Set();
            const allRolesData = @json($roles->map(function($r) { return ['idRole' => $r->idRole, 'name' => $r->name]; })->values());
            
            function normalizeString(str) {
                return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            }
            
            const roleNamesCache = new Map();
            const roleElements = Array.from(availableRoles.querySelectorAll('.role-item:not(.role-item-all)'));
            const allRolesOption = availableRoles.querySelector('.role-item-all');
            roleElements.forEach(role => {
                if (role.dataset.roleName) {
                    roleNamesCache.set(role, normalizeString(role.dataset.roleName));
                }
            });
            
            function filterRoles(searchTerm) {
                const normalizedTerm = normalizeString(searchTerm.trim());
                const hasTerm = normalizedTerm.length > 0;
                
                requestAnimationFrame(() => {
                    // Gérer l'option "Tous"
                    if (allRolesOption) {
                        const allSelected = allRolesData.length > 0 && allRolesData.every(r => selectedRoleIds.has(r.idRole));
                        if (allSelected) {
                            allRolesOption.style.display = 'none';
                        } else {
                            allRolesOption.style.display = hasTerm ? 'none' : 'flex';
                        }
                    }
                    
                    roleElements.forEach(role => {
                        const roleId = role.dataset.roleId;
                        if (!roleId) return;
                        
                        const isSelected = selectedRoleIds.has(parseInt(roleId));
                        
                        if (isSelected) {
                            role.style.display = 'none';
                            return;
                        }
                        
                        if (!hasTerm) {
                            role.style.display = 'flex';
                            return;
                        }
                        
                        const normalizedRoleName = roleNamesCache.get(role);
                        if (normalizedRoleName) {
                            const shouldShow = normalizedRoleName.includes(normalizedTerm);
                            role.style.display = shouldShow ? 'flex' : 'none';
                        }
                    });
                });
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
            
            let searchTimeout;
            roleSearch.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterRoles(e.target.value);
                }, 150);
            });
            
            function updateEmptyMessage() {
                const emptyMessage = selectedRoles.querySelector('.role-list-empty-message');
                if (selectedRoleIds.size === 0) {
                    if (!emptyMessage) {
                        const message = document.createElement('div');
                        message.className = 'role-list-empty-message';
                        message.textContent = '{{ __('admin.obligatory_documents.fields.no_roles_selected') }}';
                        selectedRoles.appendChild(message);
                    }
                } else {
                    if (emptyMessage) {
                        emptyMessage.remove();
                    }
                }
            }
            
            function addAllRoles() {
                // Ajouter tous les rôles
                allRolesData.forEach(role => {
                    if (!selectedRoleIds.has(role.idRole)) {
                        addRoleById(role.idRole, role.name);
                    }
                });
                
                // Masquer l'option "Tous"
                if (allRolesOption) {
                    allRolesOption.style.display = 'none';
                }
                
                roleSearch.value = '';
                filterRoles('');
                updateEmptyMessage();
                validateRoles();
            }
            
            function addRoleById(roleId, roleName) {
                if (selectedRoleIds.has(roleId)) {
                    return;
                }
                
                selectedRoleIds.add(roleId);
                
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
                
                // Masquer le rôle de la liste disponible
                const availableItem = roleElements.find(el => parseInt(el.dataset.roleId) === roleId);
                if (availableItem) {
                    availableItem.style.display = 'none';
                }
            }
            
            function addRole(roleItem) {
                // Vérifier si c'est l'option "Tous"
                if (roleItem.dataset.roleAll === 'true') {
                    addAllRoles();
                    return;
                }
                
                const roleId = parseInt(roleItem.dataset.roleId);
                const roleName = roleItem.dataset.roleName;
                
                if (!roleId || selectedRoleIds.has(roleId)) {
                    return;
                }
                
                addRoleById(roleId, roleName);
                
                roleSearch.value = '';
                filterRoles('');
                updateEmptyMessage();
                validateRoles();
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
                    
                    let availableItem = roleElements.find(el => parseInt(el.dataset.roleId) === roleId);
                    if (!availableItem) {
                        const role = allRolesData.find(r => r.idRole === roleId);
                        if (role) {
                            availableItem = document.createElement('div');
                            availableItem.className = 'role-item';
                            availableItem.dataset.roleId = role.idRole;
                            availableItem.dataset.roleName = role.name;
                            
                            const span = document.createElement('span');
                            span.textContent = role.name;
                            const icon = document.createElement('i');
                            icon.className = 'bi bi-plus-circle';
                            
                            availableItem.appendChild(span);
                            availableItem.appendChild(icon);
                            
                            availableItem.addEventListener('click', function() {
                                addRole(availableItem);
                            });
                            
                            roleNamesCache.set(availableItem, normalizeString(role.name));
                            roleElements.push(availableItem);
                            availableRoles.appendChild(availableItem);
                        }
                    } else {
                        availableItem.style.display = 'flex';
                    }
                    
                    // Réafficher l'option "Tous" si tous les rôles ne sont plus sélectionnés
                    if (allRolesOption) {
                        const allSelected = allRolesData.length > 0 && allRolesData.every(r => selectedRoleIds.has(r.idRole));
                        if (!allSelected) {
                            allRolesOption.style.display = roleSearch.value.trim().length === 0 ? 'flex' : 'none';
                        }
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
                if (roleItem) {
                    if (roleItem.dataset.roleAll === 'true') {
                        addRole(roleItem);
                    } else if (roleItem.dataset.roleId && !selectedRoleIds.has(parseInt(roleItem.dataset.roleId))) {
                        addRole(roleItem);
                    }
                }
            });
            
            selectedRoles.addEventListener('click', function(e) {
                const roleItem = e.target.closest('.role-item');
                if (roleItem) {
                    removeRole(roleItem.dataset.roleId);
                }
            });
            
            form.addEventListener('submit', function(e) {
                if (!validateRoles()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
            
            filterRoles('');
            updateEmptyMessage();
        });
    </script>
    @endpush
</x-app-layout>

