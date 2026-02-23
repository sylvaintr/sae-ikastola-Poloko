<x-app-layout>
    <div class="container py-4 demande-page">
        <a href="{{ route('evenements.index') }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('evenements.back_to_list') }}</span>
        </a>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                {{-- Titre bilingue --}}
                <h1 class="h4 fw-bold mb-1">{{ Lang::get('evenements.create_title', [], 'eus') }}</h1>
                @if (Lang::getLocale() == 'fr')
                    <p class="text-muted mb-4">{{ Lang::get('evenements.create_title') }}</p>
                @else
                    <div class="mb-4"></div>
                @endif

                <form method="POST" action="{{ route('evenements.store') }}" class="admin-form">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="titre" class="form-label fw-semibold">
                                <span class="basque">{{ Lang::get('evenements.titre', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted"> / {{ Lang::get('evenements.titre') }}</span>
                                @endif
                            </label>
                            <input id="titre" name="titre" type="text"
                                class="form-control @error('titre') is-invalid @enderror" value="{{ old('titre') }}"
                                required maxlength="255">
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="all_day" name="all_day"
                                    {{ old('all_day') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="all_day">
                                    <span class="basque">{{ Lang::get('evenements.all_day', [], 'eus') }}</span>
                                    @if (Lang::getLocale() == 'fr')
                                        <span class="fr text-muted"> / {{ Lang::get('evenements.all_day') }}</span>
                                    @endif
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label fw-semibold">
                                <span class="basque">{{ Lang::get('evenements.start_date', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted"> / {{ Lang::get('evenements.start_date') }}</span>
                                @endif
                            </label>
                            <input id="start_date" name="start_date" type="date"
                                class="form-control @error('start_date') is-invalid @enderror @error('start_at') is-invalid @enderror"
                                value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('start_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="start_time_container">
                            <label for="start_time" class="form-label fw-semibold">
                                <span class="basque">{{ Lang::get('evenements.start_time', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted"> / {{ Lang::get('evenements.start_time') }}</span>
                                @endif
                            </label>
                            <input id="start_time" name="start_time" type="time"
                                class="form-control @error('start_time') is-invalid @enderror"
                                value="{{ old('start_time', '09:00') }}">
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label fw-semibold">
                                <span class="basque">{{ Lang::get('evenements.end_date', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted"> / {{ Lang::get('evenements.end_date') }}</span>
                                @endif
                            </label>
                            <input id="end_date" name="end_date" type="date"
                                class="form-control @error('end_date') is-invalid @enderror @error('end_at') is-invalid @enderror"
                                value="{{ old('end_date') }}">
                            <small class="text-muted">{{ __('evenements.end_date_hint') }}</small>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('end_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="end_time_container">
                            <label for="end_time" class="form-label fw-semibold">
                                <span class="basque">{{ Lang::get('evenements.end_time', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted"> / {{ Lang::get('evenements.end_time') }}</span>
                                @endif
                            </label>
                            <input id="end_time" name="end_time" type="time"
                                class="form-control @error('end_time') is-invalid @enderror"
                                value="{{ old('end_time', '18:00') }}">
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Champs cachés pour soumettre start_at et end_at --}}
                        <input type="hidden" id="start_at" name="start_at" value="{{ old('start_at') }}">
                        <input type="hidden" id="end_at" name="end_at" value="{{ old('end_at') }}">

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">
                                <span class="basque">{{ Lang::get('evenements.description', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted"> / {{ Lang::get('evenements.description') }}</span>
                                @endif
                            </label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                rows="4" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Obligatoire --}}
                        <div class="col-md-6">
                            <label for="obligatoire" class="form-label fw-semibold">
                                <span class="basque">{{ Lang::get('evenements.obligatoire', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted"> / {{ Lang::get('evenements.obligatoire') }}</span>
                                @endif
                            </label>
                            <div class="form-check form-switch mt-2">
                                <input id="obligatoire" name="obligatoire" type="checkbox" class="form-check-input"
                                    value="1" {{ old('obligatoire') ? 'checked' : '' }}>
                                <label for="obligatoire" class="form-check-label">{{ __('evenements.obligatoire_label') }}</label>
                            </div>
                            @error('obligatoire')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-label fw-semibold mb-2">
                                <span class="basque">{{ Lang::get('evenements.cibles', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted"> / {{ Lang::get('evenements.cibles') }}</span>
                                @endif
                            </div>

                            <div class="role-selector-container">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="role-search" class="form-label small">{{ __('evenements.search_cible') }}</label>
                                        <input type="text" id="role-search" class="form-control"
                                            placeholder="{{ __('evenements.search_cible_placeholder') }}">
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
                                        <div id="roles-error" class="invalid-feedback d-none mt-2">{{ __('evenements.cible_error') }}</div>
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
                        <a href="{{ route('evenements.index') }}" class="btn admin-cancel-btn px-4">
                            {{ __('evenements.cancel') }}
                        </a>
                        <button type="submit" class="btn fw-semibold px-4 admin-submit-btn">
                            {{ __('evenements.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gestion des champs date/heure
                const allDayCheckbox = document.getElementById('all_day');
                const startTimeContainer = document.getElementById('start_time_container');
                const endTimeContainer = document.getElementById('end_time_container');
                const startDateInput = document.getElementById('start_date');
                const startTimeInput = document.getElementById('start_time');
                const endDateInput = document.getElementById('end_date');
                const endTimeInput = document.getElementById('end_time');
                const startAtHidden = document.getElementById('start_at');
                const endAtHidden = document.getElementById('end_at');
                const form = document.querySelector('form');

                function toggleTimeFields() {
                    if (allDayCheckbox.checked) {
                        startTimeContainer.classList.add('d-none');
                        endTimeContainer.classList.add('d-none');
                    } else {
                        startTimeContainer.classList.remove('d-none');
                        endTimeContainer.classList.remove('d-none');
                    }
                }

                function updateHiddenFields() {
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value || startDate;

                    if (allDayCheckbox.checked) {
                        startAtHidden.value = startDate ? `${startDate}T00:00` : '';
                        endAtHidden.value = endDate ? `${endDate}T23:59` : '';
                    } else {
                        const startTime = startTimeInput.value || '00:00';
                        const endTime = endTimeInput.value || '23:59';
                        startAtHidden.value = startDate ? `${startDate}T${startTime}` : '';
                        endAtHidden.value = endDate ? `${endDate}T${endTime}` : '';
                    }
                }

                allDayCheckbox.addEventListener('change', toggleTimeFields);
                startDateInput.addEventListener('change', updateHiddenFields);
                startTimeInput.addEventListener('change', updateHiddenFields);
                endDateInput.addEventListener('change', updateHiddenFields);
                endTimeInput.addEventListener('change', updateHiddenFields);

                // Initialisation
                toggleTimeFields();

                const roleSearch = document.getElementById('role-search');
                const availableRoles = document.getElementById('available-roles');
                const selectedRoles = document.getElementById('selected-roles');
                const roleInputs = document.getElementById('role-inputs');
                const rolesError = document.getElementById('roles-error');
                const selectedRoleIds = new Set();
                const allRolesData = @json(
                    $roles->map(function ($r) {
                            return ['idRole' => $r->idRole, 'name' => $r->name];
                        })->values());
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

                // Filtrer les rôles disponibles
                function filterRoles(searchTerm) {
                    const normalizedTerm = normalizeString(searchTerm.trim());
                    const hasTerm = normalizedTerm.length > 0;

                    requestAnimationFrame(() => {
                        roleElements.forEach(role => {
                            const roleId = role.dataset.roleId;
                            const isSelected = selectedRoleIds.has(roleId);

                            if (isSelected) {
                                role.style.display = 'none';
                                return;
                            }

                            if (!hasTerm) {
                                role.style.display = 'flex';
                                return;
                            }

                            const normalizedRoleName = roleNamesCache.get(role);
                            role.style.display = normalizedRoleName.includes(normalizedTerm) ? 'flex' : 'none';
                        });
                    });
                }

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
                    if (emptyEl) emptyEl.style.display = 'none';

                    selectedRoles.appendChild(createSelectedRoleElement(roleId, roleData.name));

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'roles[]';
                    input.value = roleId;
                    roleInputs.appendChild(input);

                    updateAvailableRolesDisplay();
                    rolesError?.classList.add('d-none');
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

                roleSearch.addEventListener('input', function() {
                    filterRoles(this.value);
                });

                availableRoles.addEventListener('click', function(e) {
                    const roleItem = e.target.closest('.role-item');
                    if (roleItem && !roleItem.classList.contains('selected')) {
                        addRole(roleItem.dataset.roleId);
                    }
                });

                // Validation du formulaire
                form.addEventListener('submit', function(e) {
                    updateHiddenFields();

                    if (selectedRoleIds.size === 0) {
                        e.preventDefault();
                        rolesError.classList.remove('d-none');
                        rolesError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
                });

                // Restaurer les valeurs old()
                const oldRoles = @json(old('roles', []));
                if (Array.isArray(oldRoles) && oldRoles.length > 0) {
                    oldRoles.forEach(roleId => addRole(roleId));
                }
            });
        </script>
    @endpush

</x-app-layout>
