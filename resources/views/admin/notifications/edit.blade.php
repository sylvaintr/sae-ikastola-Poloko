<x-app-layout>
    <div class="container py-4 py-md-5">
        <div class="mb-5">
            <h2 class="fw-bold mb-0">
                {{ __('notifications.edit_header', [], 'eus') }}
            </h2>
            
            {{-- Sous-titre avec le nom de la règle --}}
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
            <div class="card-body p-3 p-md-4">
                
                <form method="POST" action="{{ route('admin.notifications.update', $setting->id) }}" class="admin-form" id="notification-form">
                    @csrf
                    @method('PUT')
                    
                   
                    <div class="row g-3 mb-4 align-items-start">
                        
                      
                        <div class="col-12 col-md-6">
                            <label for="title" class="form-label fw-bold">
                                {{ __('notifications.form_title', [], 'eus') }}
                                @if(app()->getLocale() == 'fr')
                                    <div class="text-muted small fw-normal" style="font-size: 0.85em;">
                                        {{ __('notifications.form_title', [], 'fr') }}
                                    </div>
                                @endif
                            </label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   value="{{ old('title', $setting->title) }}" 
                                   placeholder="{{ __('notifications.form_title_placeholder') }}" required>
                        </div>

                       
                        <div class="col-6 col-md-2">
                            <label for="recurrence" class="form-label fw-bold">
                                {{ __('notifications.form_recurrence', [], 'eus') }}
                                @if(app()->getLocale() == 'fr')
                                    <div class="text-muted small fw-normal" style="font-size: 0.8em; line-height: 1.2;">
                                        {{ __('notifications.form_recurrence', [], 'fr') }}
                                    </div>
                                @endif
                            </label>
                            <input type="number" id="recurrence" name="recurrence_days" class="form-control" 
                                   value="{{ old('recurrence_days', $setting->recurrence_days) }}" 
                                   placeholder="{{ __('notifications.form_recurrence_placeholder') }}">
                        </div>

                       
                        <div class="col-6 col-md-2">
                            <label for="reminder" class="form-label fw-bold">
                                {{ __('notifications.form_reminder', [], 'eus') }}
                                @if(app()->getLocale() == 'fr')
                                    <div class="text-muted small fw-normal" style="font-size: 0.8em; line-height: 1.2;">
                                        {{ __('notifications.form_reminder', [], 'fr') }}
                                    </div>
                                @endif
                            </label>
                            <input type="number" id="reminder" name="reminder_days" class="form-control" 
                                   value="{{ old('reminder_days', $setting->reminder_days) }}" 
                                   placeholder="{{ __('notifications.form_reminder_placeholder') }}" required>
                        </div>

                      
                        <div class="col-12 col-md-2 text-start text-md-center">
                            <label class="form-label fw-bold d-block mb-2">
                                {{ __('notifications.form_active', [], 'eus') }}
                                @if(app()->getLocale() == 'fr')
                                    <div class="text-muted small fw-normal" style="font-size: 0.85em;">
                                        {{ __('notifications.form_active', [], 'fr') }}
                                    </div>
                                @endif
                            </label>
                            <div class="form-check form-switch d-flex justify-content-start justify-content-md-center ps-0">
                                <input class="form-check-input ms-0" type="checkbox" id="isActive" name="is_active" 
                                       {{ $setting->is_active ? 'checked' : '' }} 
                                       style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                
                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">
                            {{ __('notifications.form_description', [], 'eus') }}
                            @if(app()->getLocale() == 'fr')
                                <div class="text-muted small fw-normal" style="font-size: 0.85em;">
                                    {{ __('notifications.form_description', [], 'fr') }}
                                </div>
                            @endif
                        </label>
                        <textarea id="description" name="description" class="form-control" rows="3" style="resize: none;" 
                                  placeholder="{{ __('notifications.form_description_placeholder') }}">{{ old('description', $setting->description) }}</textarea>
                    </div>

                    <hr class="text-muted my-4">
                  
                 
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

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div id="available-modules" class="border rounded p-2 bg-white" style="height: auto; min-height: 200px;">
                                
                                {{-- Document --}}
                                <div class="module-item d-flex align-items-center justify-content-between p-3 mb-3 border rounded bg-white shadow-sm" 
                                     data-id="0" data-type="Document" data-model-class="App\Models\DocumentObligatoire"
                                     data-name="{{ __('notifications.module_doc_title', [], 'eus') }}" style="cursor: pointer;">
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

                                {{-- Événement --}}
                                <div class="module-item d-flex align-items-center justify-content-between p-3 mb-2 border rounded bg-white shadow-sm" 
                                     data-id="0" data-type="Evènement" data-model-class="App\Models\Evenement"
                                     data-name="{{ __('notifications.module_event_title', [], 'eus') }}" style="cursor: pointer;">
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
                        
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <span class="text-muted small d-block">
                                    {{ __('notifications.selection_active', [], 'eus') }}
                                </span>
                                @if(app()->getLocale() == 'fr')
                                    <span class="text-muted small d-block">
                                        {{ __('notifications.selection_active', [], 'fr') }}
                                    </span>
                                @endif
                            </div>

                            <div id="selected-modules-container" class="border rounded p-3 bg-light" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                <div class="module-list-empty-message text-muted text-center">
                                    <i class="bi bi-arrow-up bi-arrow-left-md me-2 d-none d-md-inline"></i>
                                    <i class="bi bi-arrow-up d-inline d-md-none me-2"></i>
                                    {{ __('notifications.selection_empty', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="small mt-1">{{ __('notifications.selection_empty', [], 'fr') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div id="hidden-inputs-container"></div>
                        </div>
                    </div>

                    {{-- BOUTONS --}}
                    <div class="d-flex flex-column flex-md-row justify-content-end gap-3 mt-5">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary px-4 fw-bold order-2 order-md-1">
                            @if(app()->getLocale() == 'fr')
                                {{ __('notifications.btn_cancel', [], 'fr') }}
                            @else
                                {{ __('notifications.btn_cancel', [], 'eus') }}
                            @endif
                        </a>
                        <button type="submit" class="btn text-white px-4 fw-bold order-1 order-md-2" style="background-color: #F59E0B;">
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
    {{-- SCRIPT JS IDENTIQUE --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        
            const currentTargetType = "{{ str_replace('\\', '\\\\', $setting->target_type) }}";
            const currentTargetId = "{{ $setting->target_id }}";

            const style = document.createElement('style');
            style.innerHTML = `
                .form-check-input:checked { background-color: #F59E0B; border-color: #F59E0B; }
                .module-item:hover { background-color: #f8f9fa !important; transform: translateX(5px); }
                .module-selected-item { background-color: #F59E0B; color: white; width: 100%; }
            `;
            document.head.appendChild(style);

            const availableContainer = document.getElementById('available-modules');
            const selectedContainer = document.getElementById('selected-modules-container');
            const hiddenInputsContainer = document.getElementById('hidden-inputs-container');
            
            availableContainer.addEventListener('click', function(e) {
                const item = e.target.closest('.module-item');
                if (!item) return;
                addModule(item.dataset.id, item.dataset.type, item.dataset.name, item);
            });

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

                const inputId = document.createElement('input'); inputId.type = 'hidden'; inputId.name = 'module_id'; inputId.value = id;
                hiddenInputsContainer.appendChild(inputId);

                const inputType = document.createElement('input'); inputType.type = 'hidden'; inputType.name = 'module_type'; inputType.value = type;
                hiddenInputsContainer.appendChild(inputType);
            }

            function removeModule(selectedItem, originalItem) {
                selectedItem.remove();
                originalItem.style.display = null;
                originalItem.classList.add('d-flex');
                hiddenInputsContainer.innerHTML = '';
                selectedContainer.style.display = 'flex';
                selectedContainer.innerHTML = `
                    <div class="module-list-empty-message text-muted text-center">
                        <i class="bi bi-arrow-up bi-arrow-left-md me-2 d-none d-md-inline"></i> 
                        <i class="bi bi-arrow-up d-inline d-md-none me-2"></i>
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

           
            const items = document.querySelectorAll('.module-item');
            items.forEach(item => {
              
                if (item.dataset.modelClass === currentTargetType) {
                    addModule(item.dataset.id, item.dataset.type, item.dataset.name, item);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>