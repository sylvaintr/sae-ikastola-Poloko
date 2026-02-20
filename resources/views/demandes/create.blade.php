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
                <div class="demande-field-col demande-field-sm">
                    <label for="demande-planned-expense" class="form-label">{{ __('demandes.form.labels.planned_expense.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.planned_expense.fr') }}</small></label>
                    <input id="demande-planned-expense" type="number" step="0.01" min="0" name="montantP" class="form-control"
                        value="{{ old('montantP', $demande->montantP ?? '') }}" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : '' }}>
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col w-100">
                    <label for="demande-evenement" class="form-label">{{ __('demandes.form.labels.evenement.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.evenement.fr') }}</small></label>
                    <select id="demande-evenement" name="idEvenement" class="form-select" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : '' }}>
                        <option value="">{{ __('demandes.form.labels.evenement.none') }}</option>
                        @foreach ($evenements ?? [] as $evenement)
                            <option value="{{ $evenement->idEvenement }}" @selected(old('idEvenement', $demande->idEvenement ?? '') == $evenement->idEvenement)>
                                {{ $evenement->titre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col w-100">
                    <label class="form-label">{{ __('demandes.form.labels.assigne.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.assigne.fr') }}</small></label>
                    @php
                        $selectedRoleIds = collect(old('roles', isset($demande) ? $demande->roles->pluck('idRole')->toArray() : []));
                        $isDisabled = $isEdit && ($demande->etat ?? '') === 'Terminé';
                    @endphp
                    <div class="role-selector-container {{ $isDisabled ? 'opacity-50 pe-none' : '' }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="commission-search" class="form-label small fw-semibold">{{ __('demandes.form.labels.commission_search') }}</label>
                                <input type="text" id="commission-search" class="form-control" placeholder="{{ __('demandes.form.labels.commission_placeholder') }}">
                                <div id="available-commissions" class="role-list mt-2">
                                    @foreach ($roles ?? [] as $role)
                                        @if (!$selectedRoleIds->contains($role->idRole))
                                            <div class="role-item" data-role-id="{{ $role->idRole }}" data-role-name="{{ $role->name }}">
                                                <span>{{ $role->name }}</span>
                                                <i class="bi bi-plus-circle"></i>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">{{ __('demandes.form.labels.commission_selected') }}</label>
                                <div id="selected-commissions" class="role-list mt-2">
                                    @forelse ($roles->whereIn('idRole', $selectedRoleIds) ?? [] as $role)
                                        <div class="role-item selected" data-role-id="{{ $role->idRole }}" data-role-name="{{ $role->name }}">
                                            <span>{{ $role->name }}</span>
                                            <i class="bi bi-x-circle"></i>
                                        </div>
                                    @empty
                                        <div class="role-list-empty-message">{{ __('demandes.form.labels.commission_empty') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div id="commissions-error" class="text-danger small mt-2" style="display:none;">
                            {{ __('demandes.form.labels.commission_required') }}
                        </div>
                        <div id="commission-inputs">
                            @foreach ($selectedRoleIds as $roleId)
                                <input type="hidden" name="roles[]" value="{{ $roleId }}">
                            @endforeach
                        </div>
                    </div>
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
        // ─── Sélecteur de commissions ───
        const commissionSearch = document.getElementById('commission-search');
        const availableCommissions = document.getElementById('available-commissions');
        const selectedCommissions = document.getElementById('selected-commissions');
        const commissionInputs = document.getElementById('commission-inputs');

        if (commissionSearch && availableCommissions) {
            function normalizeStr(str) {
                return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            }

            function updateEmptyMessage() {
                const empty = selectedCommissions.querySelector('.role-list-empty-message');
                const hasItems = selectedCommissions.querySelectorAll('.role-item').length > 0;
                if (hasItems && empty) empty.remove();
                if (!hasItems && !empty) {
                    const msg = document.createElement('div');
                    msg.className = 'role-list-empty-message';
                    msg.textContent = '{{ __('demandes.form.labels.commission_empty') }}';
                    selectedCommissions.appendChild(msg);
                }
            }

            function addCommission(item) {
                const roleId = item.dataset.roleId;
                const roleName = item.dataset.roleName;

                item.style.display = 'none';

                const selected = document.createElement('div');
                selected.className = 'role-item selected';
                selected.dataset.roleId = roleId;
                selected.dataset.roleName = roleName;
                selected.innerHTML = `<span>${roleName}</span><i class="bi bi-x-circle"></i>`;
                selected.addEventListener('click', () => removeCommission(roleId));
                selectedCommissions.appendChild(selected);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'roles[]';
                input.value = roleId;
                commissionInputs.appendChild(input);

                commissionSearch.value = '';
                filterCommissions('');
                updateEmptyMessage();
            }

            function removeCommission(roleId) {
                const selectedItem = selectedCommissions.querySelector(`[data-role-id="${roleId}"]`);
                if (selectedItem) selectedItem.remove();

                const hiddenInput = commissionInputs.querySelector(`input[value="${roleId}"]`);
                if (hiddenInput) hiddenInput.remove();

                const availItem = availableCommissions.querySelector(`[data-role-id="${roleId}"]`);
                if (availItem) availItem.style.display = 'flex';

                updateEmptyMessage();
            }

            function filterCommissions(term) {
                const normalized = normalizeStr(term.trim());
                availableCommissions.querySelectorAll('.role-item').forEach(item => {
                    const alreadySelected = selectedCommissions.querySelector(`[data-role-id="${item.dataset.roleId}"]`);
                    if (alreadySelected) { item.style.display = 'none'; return; }
                    item.style.display = (!normalized || normalizeStr(item.dataset.roleName).includes(normalized)) ? 'flex' : 'none';
                });
            }

            availableCommissions.querySelectorAll('.role-item').forEach(item => {
                item.addEventListener('click', () => addCommission(item));
            });

            selectedCommissions.querySelectorAll('.role-item').forEach(item => {
                item.addEventListener('click', () => removeCommission(item.dataset.roleId));
            });

            let searchTimeout;
            commissionSearch.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => filterCommissions(this.value), 150);
            });

            updateEmptyMessage();

            // Validation à la soumission
            const form = document.querySelector('form.demande-create-form');
            const commissionsError = document.getElementById('commissions-error');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const hasSelected = commissionInputs.querySelectorAll('input[name="roles[]"]').length > 0;
                    if (!hasSelected) {
                        e.preventDefault();
                        e.stopPropagation();
                        commissionsError.style.display = 'block';
                        selectedCommissions.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else {
                        commissionsError.style.display = 'none';
                    }
                });
            }
        }

        // ─── Prévisualisation photos ───
        const input = document.getElementById('photos-input');
        const preview = document.getElementById('photos-preview');

        if (!input || !preview) return;
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
                    // Create the thumb container
                    const thumbDiv = document.createElement('div');
                    thumbDiv.className = 'demande-photo-thumb';

                    // Create the image element
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.setAttribute('alt', file.name);
                    thumbDiv.appendChild(img);

                    // Create the file name display
                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'small text-muted text-truncate';
                    nameDiv.textContent = file.name;

                    // Append to col
                    col.appendChild(thumbDiv);
                    col.appendChild(nameDiv);
                    preview.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>

