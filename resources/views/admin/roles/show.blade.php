<x-app-layout>
    <div class="container py-4">

        {{-- Header : titre + retour --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">
                    {{ $role->name }}
                </h1>
            </div>

            <div class="text-end">
                <a href="{{ route('admin.roles.index') }}" class="text-decoration-none fw-semibold text-warning">
                    ← {{ __('admin.back_to_roles', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block small fw-semibold text-warning">
                            {{ __('admin.back_to_roles') }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Carte principale --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <h2 class="h6 fw-semibold mb-4">
                    {{ __('admin.manage_permissions', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block fw-light text-muted">
                            {{ __('admin.manage_permissions') }}
                        </span>
                    @endif
                </h2>

                <form id="role-permissions-form" action="{{ route('admin.roles.permissions.attach', $role) }}" method="POST" class="small">
                    @csrf
                    
                    <div class="row g-4">
                        {{-- Liste des permissions disponibles (A GAUCHE) --}}
                        <div class="col-md-6">
                            <label for="perm-search" class="form-label small mb-1">
                                {{ __('admin.search_permissions', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('admin.search_permissions') }}
                                    </span>
                                @endif
                            </label>

                            <input type="text" id="perm-search" class="form-control mb-3"
                                placeholder="{{ __('admin.search_permissions_placeholder', [], 'eus') }}"
                                maxlength="100">

                            <div id="available-permissions" class="role-list" style="max-height: 400px; overflow-y: auto;">
                                @foreach ($permissions as $permission)
                                    {{-- On affiche seulement si le rôle ne l'a pas déjà --}}
                                    @php $hasPerm = $role->hasPermissionTo($permission); @endphp
                                    <div class="role-item perm-item {{ $hasPerm ? 'd-none' : '' }}" 
                                         data-perm-id="{{ $permission->id }}"
                                         data-perm-name="{{ $permission->name }}">
                                        <span>{{ $permission->name }}</span>
                                        <i class="bi bi-plus-circle fs-5 text-muted cursor-pointer"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Liste des permissions sélectionnées (A DROITE) --}}
                        <div class="col-md-6">
                            <div class="form-label small mb-1">
                                {{ __('admin.selected_permissions', [], 'eus') }} <span class="text-danger">*</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('admin.selected_permissions') }}
                                    </span>
                                @endif
                            </div>

                            <div id="selected-permissions" class="role-list" style="min-height: 200px; background-color: #f8f9fa;">
                                {{-- Message si vide --}}
                                <div class="role-list-empty-message perm-empty-message text-center py-5 {{ $role->permissions->count() > 0 ? 'd-none' : '' }}">
                                    <span class="text-muted small italic">
                                        {{ __('admin.no_permissions_selected', [], 'eus') }}
                                        @if (Lang::getLocale() == 'fr')
                                            <br>
                                            {{ __('admin.no_permissions_selected') }}
                                        @endif
                                    </span>
                                </div>

                                {{-- Permissions déjà assignées --}}
                                @foreach($role->permissions as $perm)
                                    <div class="role-item selected-item" data-perm-id="{{ $perm->id }}">
                                        <span>{{ $perm->name }}</span>
                                        <i class="bi bi-x-lg fs-6 text-muted cursor-pointer"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Inputs hidden pour l'envoi du formulaire --}}
                    <div id="permissions-inputs">
                        @foreach($role->permissions as $perm)
                            <input type="hidden" name="permissions[]" value="{{ $perm->id }}">
                        @endforeach
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="d-flex justify-content-end pt-3 mt-5 border-top">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-link text-muted me-3 text-decoration-none">
                            <span class="fw-bold border-bottom">{{ __('admin.cancel', [], 'eus') }}</span>
                            @if (Lang::getLocale() == 'fr')
                            <span class="d-block small fw-light">{{ __('admin.cancel') }}</span>
                            @endif
                        </a>

                        <button type="submit" class="btn btn-warning fw-bold px-4 py-2 shadow-sm" style="background-color: #ffc107; border:none;">
                            {{ __('admin.save', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                            <span class="d-block small fw-light">{{ __('admin.save') }}</span>
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script personnalisé pour gérer l'ajout/suppression --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const availableContainer = document.getElementById('available-permissions');
            const selectedContainer = document.getElementById('selected-permissions');
            const inputsContainer = document.getElementById('permissions-inputs');
            const emptyMessage = document.querySelector('.perm-empty-message');
            const searchInput = document.getElementById('perm-search');

            // --- RECHERCHE ---
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                document.querySelectorAll('.perm-item').forEach(item => {
                    const name = item.getAttribute('data-perm-name').toLowerCase();
                    item.style.display = name.includes(term) ? '' : 'none';
                });
            });

            // --- AJOUTER ---
            availableContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.bi-plus-circle');
                if (!btn) return;

                const item = btn.closest('.perm-item');
                const id = item.getAttribute('data-perm-id');
                const name = item.getAttribute('data-perm-name');

                // Cacher à gauche
                item.classList.add('d-none');

                // Ajouter à droite
                const newItem = document.createElement('div');
                newItem.className = 'role-item selected-item';
                newItem.setAttribute('data-perm-id', id);
                newItem.innerHTML = `<span>${name}</span><i class="bi bi-x-lg fs-6 text-muted cursor-pointer"></i>`;
                selectedContainer.appendChild(newItem);

                // Ajouter l'input hidden
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'permissions[]';
                input.value = id;
                input.id = `input-perm-${id}`;
                inputsContainer.appendChild(input);

                checkEmptyState();
            });

            // --- SUPPRIMER ---
            selectedContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.bi-x-lg');
                if (!btn) return;

                const item = btn.closest('.selected-item');
                const id = item.getAttribute('data-perm-id');

                // Retirer de la droite
                item.remove();

                // Ré-afficher à gauche
                const leftItem = availableContainer.querySelector(`[data-perm-id="${id}"]`);
                if (leftItem) leftItem.classList.remove('d-none');

                // Retirer l'input hidden (attention : gère les anciens inputs déjà présents)
                const input = inputsContainer.querySelector(`input[value="${id}"]`);
                if (input) input.remove();

                checkEmptyState();
            });

            function checkEmptyState() {
                const count = selectedContainer.querySelectorAll('.selected-item').length;
                emptyMessage.classList.toggle('d-none', count > 0);
            }
        });
    </script>
    @endpush

    <style>
        .role-list {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px;
            min-height: 250px;
        }
        .role-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 5px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .role-item:hover {
            background-color: #fdfdfd;
            border-color: #ccc;
        }
        .cursor-pointer { cursor: pointer; }
    </style>
</x-app-layout>