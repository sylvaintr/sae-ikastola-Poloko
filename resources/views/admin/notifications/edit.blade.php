<x-app-layout>
    <div class="container py-5">
        <div class="mb-5">
            {{-- HEADER BILINGUE --}}
            <h2 class="fw-bold mb-0">
                {{ __('notifications.edit_header', [], 'eus') }}
            </h2>
            
            {{-- Sous-titre Français en dessous --}}
            @if(app()->getLocale() == 'fr')
                <div class="text-muted small mt-1">
                    {{ __('notifications.edit_subtitle', [], 'fr') }} : {{ $setting->title }}
                </div>
            @else
                <div class="text-muted small mt-1">
                    {{ __('notifications.edit_subtitle', [], 'eus') }} : {{ $setting->title }}
                </div>
            @endif
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                
                {{-- FORMULAIRE D'ÉDITION --}}
                <form method="POST" action="{{ route('admin.notifications.update', $setting->id) }}" class="admin-form" id="notification-form">
                    @csrf
                    @method('PUT')

                    {{-- Section 1 : Paramètres Généraux --}}
                    <div class="row g-4 mb-4 align-items-start">
                        
                        {{-- TITRE --}}
                        <div class="col-md-6">
                            <label for="title" class="form-label fw-bold">
                                {{ __('notifications.form_title', [], 'eus') }}
                                @if(app()->getLocale() == 'fr')
                                    <div class="text-muted small fw-normal" style="font-size: 0.85em;">
                                        {{ __('notifications.form_title', [], 'fr') }}
                                    </div>
                                @endif
                            </label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   value="{{ old('title', $setting->title) }}" required>
                        </div>

                        {{-- RÉCURRENCE --}}
                        <div class="col-md-2">
                            <label for="recurrence" class="form-label fw-bold">
                                {{ __('notifications.form_recurrence', [], 'eus') }}
                                @if(app()->getLocale() == 'fr')
                                    <div class="text-muted small fw-normal" style="font-size: 0.8em; line-height: 1.2;">
                                        {{ __('notifications.form_recurrence', [], 'fr') }}
                                    </div>
                                @endif
                            </label>
                            <input type="number" id="recurrence" name="recurrence_days" class="form-control" 
                                   value="{{ old('recurrence_days', $setting->recurrence_days) }}">
                        </div>

                        {{-- RAPPEL --}}
                        <div class="col-md-2">
                            <label for="reminder" class="form-label fw-bold">
                                {{ __('notifications.form_reminder', [], 'eus') }}
                                @if(app()->getLocale() == 'fr')
                                    <div class="text-muted small fw-normal" style="font-size: 0.8em; line-height: 1.2;">
                                        {{ __('notifications.form_reminder', [], 'fr') }}
                                    </div>
                                @endif
                            </label>
                            <input type="number" id="reminder" name="reminder_days" class="form-control" 
                                   value="{{ old('reminder_days', $setting->reminder_days) }}" required>
                        </div>

                        {{-- ACTIVÉ --}}
                        <div class="col-md-2 text-center">
                            <label class="form-label fw-bold d-block mb-2">
                                {{ __('notifications.form_active', [], 'eus') }}
                                @if(app()->getLocale() == 'fr')
                                    <div class="text-muted small fw-normal" style="font-size: 0.85em;">
                                        {{ __('notifications.form_active', [], 'fr') }}
                                    </div>
                                @endif
                            </label>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" id="isActive" name="is_active" 
                                       {{ $setting->is_active ? 'checked' : '' }}
                                       style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="mb-5">
                        <label for="description" class="form-label fw-bold">
                            {{ __('notifications.form_description', [], 'eus') }}
                            @if(app()->getLocale() == 'fr')
                                <div class="text-muted small fw-normal" style="font-size: 0.85em;">
                                    {{ __('notifications.form_description', [], 'fr') }}
                                </div>
                            @endif
                        </label>
                        <textarea id="description" name="description" class="form-control" rows="3" style="resize: none;">{{ old('description', $setting->description) }}</textarea>
                    </div>

                    <hr class="text-muted my-4">

                    {{-- Section 2 : Sélection du Module --}}
                    <div class="mb-3">
                        <h4 class="fw-bold mb-0">
                            {{ __('notifications.target_title', [], 'eus') }}
                        </h4>
                        @if(app()->getLocale() == 'fr')
                            <div class="text-muted small mt-1">{{ __('notifications.target_subtitle', [], 'fr') }}</div>
                        @else
                            <div class="text-muted small mt-1">{{ __('notifications.target_subtitle', [], 'eus') }}</div>
                        @endif
                    </div>

                    <div class="row g-4">
                        {{-- Colonne Gauche : Les Boutons --}}
                        <div class="col-md-6">
                            <div id="available-modules" class="border rounded p-2 bg-white" style="height: auto; min-height: 200px;">
                                
                                {{-- BOUTON 1 : DOCUMENTS --}}
                                <div class="module-item d-flex align-items-center justify-content-between p-3 mb-3 border rounded bg-white shadow-sm" 
                                     data-id="0" 
                                     data-type="Document" 
                                     data-model-class="App\Models\DocumentObligatoire" {{-- IMPORTANT POUR LA PRÉ-SÉLECTION --}}
                                     data-name="{{ __('notifications.module_doc_title', [], 'eus') }}" 
                                     style="cursor: pointer; transition: 0.2s;">
                                    <div>
                                        <span class="fw-bold d-block text-primary">
                                            {{ __('notifications.module_doc_title', [], 'eus') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ __('notifications.module_doc_desc', [], 'eus') }}
                                            @if(app()->getLocale() == 'fr')
                                                <div style="font-size: 0.85em; opacity: 0.8; margin-top: 2px;">
                                                    {{ __('notifications.module_doc_desc', [], 'fr') }}
                                                </div>
                                            @endif
                                        </small>
                                    </div>
                                    <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
                                </div>

                                {{-- BOUTON 2 : ÉVÉNEMENTS --}}
                                <div class="module-item d-flex align-items-center justify-content-between p-3 mb-2 border rounded bg-white shadow-sm" 
                                     data-id="0" 
                                     data-type="Evènement" 
                                     data-model-class="App\Models\Evenement" {{-- IMPORTANT POUR LA PRÉ-SÉLECTION --}}
                                     data-name="{{ __('notifications.module_event_title', [], 'eus') }}" 
                                     style="cursor: pointer; transition: 0.2s;">
                                    <div>
                                        <span class="fw-bold d-block text-success">
                                            {{ __('notifications.module_event_title', [], 'eus') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ __('notifications.module_event_desc', [], 'eus') }}
                                            @if(app()->getLocale() == 'fr')
                                                <div style="font-size: 0.85em; opacity: 0.8; margin-top: 2px;">
                                                    {{ __('notifications.module_event_desc', [], 'fr') }}
                                                </div>
                                            @endif
                                        </small>
                                    </div>
                                    <i class="bi bi-calendar-event fs-3 text-success"></i>
                                </div>

                            </div>
                        </div>

                        {{-- Colonne Droite : Sélection --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="text-muted small d-block">
                                    {{ __('notifications.selection_active', [], 'eus') }}
                                </span>
                                @if(app()->getLocale() == 'fr')
                                    <span class="text-muted small d-block">
                                        {{ __('notifications.selection_active', [], 'fr') }}
                                    </span>
                                @endif
                            </div>

                            <div id="selected-modules-container" class="border rounded p-3 bg-light" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                <div class="module-list-empty-message text-muted text-center">
                                    <i class="bi bi-arrow-left me-2"></i> 
                                    {{ __('notifications.selection_empty', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="small mt-1">{{ __('notifications.selection_empty', [], 'fr') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div id="hidden-inputs-container"></div>
                        </div>
                    </div>

                    {{-- BOUTONS D'ACTION --}}
                    <div class="d-flex justify-content-end gap-3 mt-5">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary px-4 fw-bold">
                            @if(app()->getLocale() == 'fr')
                                {{ __('notifications.btn_cancel', [], 'fr') }}
                            @else
                                {{ __('notifications.btn_cancel', [], 'eus') }}
                            @endif
                        </a>
                        <button type="submit" class="btn text-white px-4 fw-bold" style="background-color: #F59E0B;">
                            @if(app()->getLocale() == 'fr')
                                {{ __('notifications.btn_update', [], 'fr') }}
                            @else
                                {{ __('notifications.btn_update', [], 'eus') }}
                            @endif
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // -- DONNÉES ACTUELLES (PHP -> JS) --
            // On échappe les backslashes pour que App\Models\Toto ne casse pas le JS
            const currentTargetType = "{{ str_replace('\\', '\\\\', $setting->target_type) }}";
            const currentTargetId = "{{ $setting->target_id }}";

            // -- STYLE CSS --
            const style = document.createElement('style');
            style.innerHTML = `
                .form-check-input:checked { background-color: #F59E0B; border-color: #F59E0B; }
                .module-item:hover { background-color: #f8f9fa !important; transform: translateX(5px); }
                .module-selected-item { background-color: #F59E0B; color: white; width: 100%; }
            `;
            document.head.appendChild(style);

            // -- LOGIQUE JS --
            const availableContainer = document.getElementById('available-modules');
            const selectedContainer = document.getElementById('selected-modules-container');
            const hiddenInputsContainer = document.getElementById('hidden-inputs-container');
            
            // 1. GESTION DU CLIC
            availableContainer.addEventListener('click', function(e) {
                const item = e.target.closest('.module-item');
                if (!item) return;
                addModule(item.dataset.id, item.dataset.type, item.dataset.name, item);
            });

            // 2. AJOUT MODULE
            function addModule(id, type, name, originalItem) {
                clearSelection();
                originalItem.style.display = 'none';
                originalItem.classList.remove('d-flex');
                selectedContainer.innerHTML = '';
                selectedContainer.style.display = 'block';

                const selectedItem = document.createElement('div');
                selectedItem.className = 'module-selected-item d-flex align-items-center justify-content-between p-3 mb-2 border rounded shadow-sm';
                selectedItem.innerHTML = `
                    <div><span class="fw-bold d-block">${name}</span><small style="opacity: 0.8;">Type: ${type}</small></div>
                    <i class="bi bi-x-circle-fill fs-4 text-white" style="cursor: pointer;"></i>
                `;

                selectedItem.querySelector('.bi-x-circle-fill').addEventListener('click', function() {
                    removeModule(selectedItem, originalItem);
                });

                selectedContainer.appendChild(selectedItem);

                // Inputs cachés pour le formulaire
                const inputId = document.createElement('input');
                inputId.type = 'hidden'; inputId.name = 'module_id'; inputId.value = id;
                hiddenInputsContainer.appendChild(inputId);

                const inputType = document.createElement('input');
                inputType.type = 'hidden'; inputType.name = 'module_type'; inputType.value = type;
                hiddenInputsContainer.appendChild(inputType);
            }

            // 3. RETIRER MODULE
            function removeModule(selectedItem, originalItem) {
                selectedItem.remove();
                originalItem.style.display = null;
                originalItem.classList.add('d-flex');
                hiddenInputsContainer.innerHTML = '';
                selectedContainer.style.display = 'flex';
                selectedContainer.innerHTML = `
                    <div class="module-list-empty-message text-muted text-center">
                        <i class="bi bi-arrow-left me-2"></i> 
                        {{ __('notifications.selection_empty', [], 'eus') }}
                        @if(app()->getLocale() == 'fr')
                            <div class="small mt-1">{{ __('notifications.selection_empty', [], 'fr') }}</div>
                        @endif
                    </div>`;
            }

            function clearSelection() {
                const existingItems = selectedContainer.querySelectorAll('.module-selected-item');
                existingItems.forEach(item => item.querySelector('.bi-x-circle-fill').click());
            }

            // --- AUTO-INITIALISATION (Correction de la sélection par défaut) ---
            const items = document.querySelectorAll('.module-item');
            items.forEach(item => {
                // On vérifie si l'attribut data-model-class correspond à ce qui est en base de données
                // ex: App\Models\DocumentObligatoire == App\Models\DocumentObligatoire
                if (item.dataset.modelClass === currentTargetType) {
                    addModule(item.dataset.id, item.dataset.type, item.dataset.name, item);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>