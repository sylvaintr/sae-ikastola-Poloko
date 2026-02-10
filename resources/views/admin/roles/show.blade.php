<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.roles.index') }}" class="text-decoration-none d-inline-flex align-items-center gap-2 mb-4" style="color: #f39c12;">
            <i class="bi bi-arrow-left"></i>
            <span class="fw-bold">{{ __('Retour') }}</span>
        </a>

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-0 text-dark">{{ $role->name }}</h1>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-4 text-dark">{{ __('Permissions assignées') }}</h2>

                        @if($role->permissions->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($role->permissions as $perm)
                                    <div class="d-flex align-items-center bg-primary text-white px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.9rem; background-color: #0d6efd !important;">
                                        <span class="me-2">{{ $perm->name }}</span>
                                        <form action="{{ route('admin.roles.permissions.detach', [$role, $perm->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn p-0 leading-none d-flex align-items-center"
                                                    onclick="return confirm('{{ __('Supprimer la permission ?') }}');"
                                                    style="background: white; border-radius: 50%; width: 20px; height: 20px; justify-content: center;">
                                                <i class="bi bi-x text-danger fw-bold" style="font-size: 1.1rem;"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-shield-exclamation text-muted fs-1"></i>
                                <p class="text-muted mt-2">Aucune permission assignée.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-4 text-dark">{{ __('Ajouter une permission') }}</h2>

                        <form action="{{ route('admin.roles.permissions.attach', $role) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">{{ __('PERMISSIONS DISPONIBLES') }}</label>
                                
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary px-3"
                                            onclick="document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true)">
                                        {{ __('Tout sélectionner') }}
                                    </button>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary px-3"
                                            onclick="document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false)">
                                        {{ __('Tout désélectionner') }}
                                    </button>
                                </div>

                                <div class="permission-list border rounded p-3 bg-light-subtle shadow-inner" style="max-height: 280px; overflow-y: auto; border-color: #eee !important;">
                                    @foreach($permissions as $permission)
                                        @if(!$role->hasPermissionTo($permission))
                                            <div class="form-check mb-2">
                                                <input class="form-check-input permission-checkbox" type="checkbox" value="{{ $permission->id }}" id="perm-{{ $permission->id }}" name="permissions[]">
                                                <label class="form-check-label text-dark ps-2" for="perm-{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                @error('permissions')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.roles.index') }}" class="btn px-4 py-2 fw-bold" style="color: #ffa500; border: 1px solid #ffa500; border-radius: 8px;">
                                    {{ __('Annuler') }}
                                </a>
                                <button type="submit" class="btn px-4 py-2 fw-bold text-white shadow-sm" style="background-color: #ffa500; border: none; border-radius: 8px;">
                                    {{ __('Ajouter') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script>
        function initPermissionToggles() {
            try {
                const selectAll = document.getElementById('select-all-perms');
                const clearAll = document.getElementById('clear-all-perms');
                const container = document.querySelector('.permission-list');

                if (!selectAll || !clearAll || !container) return;

                selectAll.addEventListener('click', function() {
                    const checkboxes = container.querySelectorAll('.permission-checkbox');
                    checkboxes.forEach(cb => cb.checked = true);
                });

                clearAll.addEventListener('click', function() {
                    const checkboxes = container.querySelectorAll('.permission-checkbox');
                    checkboxes.forEach(cb => cb.checked = false);
                });
            } catch (err) {
                console.error('initPermissionToggles error', err);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPermissionToggles);
        } else {
            initPermissionToggles();
        }

        const btnSelectAll = document.getElementById('select-all-perms');
        const btnClearAll = document.getElementById('clear-all-perms');
        const checkboxes = document.querySelectorAll('.permission-checkbox');

        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function () {
                checkboxes.forEach(cb => {
                    cb.checked = true;
                });
            });
        }

        if (btnClearAll) {
            btnClearAll.addEventListener('click', function () {
                checkboxes.forEach(cb => {
                    cb.checked = false;
                });
            });
        }
    
    </script>
@endpush
