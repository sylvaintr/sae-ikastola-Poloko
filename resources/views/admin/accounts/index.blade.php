<x-app-layout>
    <div class="container py-4">
        @if (session('status'))
            <div id="status-alert" class="alert alert-success status-alert mb-3 d-flex align-items-center justify-content-between">
                <span>{{ session('status') }}</span>
                <button type="button" class="btn-close btn-close-sm" aria-label="Close" onclick="this.parentElement.remove()"></button>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-4 mb-5">
            <div>
                <h1 class="fw-bold display-4 mb-1" style="font-size: 2.5rem;">{{ __('admin.accounts_page.title') }}</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ __('admin.accounts_page.title_subtitle') }}</p>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">
                <div class="admin-search-container">
                    <input type="text" id="search-account" name="search"
                           class="form-control admin-search-input"
                           placeholder="{{ __('admin.accounts_page.search_placeholder') }}"
                           value="{{ request('search') }}">
                    <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ __('admin.accounts_page.search_label') }}</p>
                </div>

                <div class="d-flex flex-column align-items-start">
                    <a href="{{ route('admin.accounts.create') }}" class="btn admin-add-button">
                        {{ __('admin.accounts_page.add_button') }}
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle">{{ __('admin.accounts_page.add_button_subtitle') }}</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <div class="admin-filter-container">
                <select id="filter-role" name="role" class="form-select admin-filter-select">
                    <option value="">{{ __('admin.accounts_page.filter.all_roles') }}</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->idRole }}" {{ request('role') == $role->idRole ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-muted mb-0 mt-2 text-center" style="font-size: 0.75rem;">{{ __('admin.accounts_page.filter.role_label') }}</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        @php
                            $allColumns = __('admin.accounts_page.columns');
                            $columnOrder = ['first_name', 'last_name', 'email', 'famille', 'roles', 'status', 'actions'];
                            $sortableColumns = ['first_name' => 'prenom', 'last_name' => 'nom', 'email' => 'email', 'famille' => 'famille', 'status' => 'statutValidation'];
                        @endphp
                        @foreach ($columnOrder as $key)
                            @php $column = $allColumns[$key]; @endphp
                            <th scope="col">
                                @if (isset($sortableColumns[$key]))
                                    @php
                                        $currentSort = $sortColumn ?? 'nom';
                                        $currentDirection = $sortDirection ?? 'asc';
                                        $isCurrentColumn = ($sortableColumns[$key] === $currentSort);
                                        $nextDirection = $isCurrentColumn && $currentDirection === 'asc' ? 'desc' : 'asc';
                                        $sortUrl = request()->fullUrlWithQuery(['sort' => $sortableColumns[$key], 'direction' => $nextDirection, 'page' => 1]);
                                    @endphp
                                    <a href="{{ $sortUrl }}" class="text-decoration-none text-dark d-inline-block" style="width: 100%;">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="admin-table-heading">{{ $column['title'] }}</span>
                                            <i class="bi bi-chevron-down ms-1" style="font-size: 0.9rem;"></i>
                                        </div>
                                        <p class="text-muted mb-0 text-center" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ $column['subtitle'] }}</p>
                                    </a>
                                @else
                                    <div class="text-center">
                                        <span class="admin-table-heading">{{ $column['title'] }}</span>
                                        <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ $column['subtitle'] }}</p>
                                    </div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td>{{ $account->prenom }}</td>
                            <td>{{ $account->nom }}</td>
                            <td>{{ $account->email ?? '—' }}</td>
                            <td>
                                @if ($account->familles->isNotEmpty())
                                    {{ $account->familles->pluck('idFamille')->join(', ') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($account->rolesCustom->isNotEmpty())
                                    {{ $account->rolesCustom->pluck('name')->join(', ') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($account->statutValidation)
                                    <span class="badge bg-success">Validé</span>
                                @else
                                    <span class="badge bg-secondary">Non validé</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <a href="{{ route('admin.accounts.show', $account) }}" class="admin-action-link" title="{{ __('admin.accounts_page.actions.view') }}">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('admin.accounts.edit', $account) }}" class="admin-action-link" title="{{ __('admin.accounts_page.actions.edit') }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @if (!$account->statutValidation)
                                        <form action="{{ route('admin.accounts.validate', $account) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="admin-action-link admin-validate-link btn btn-link p-0 m-0" title="{{ __('admin.accounts_page.actions.validate') }}">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.accounts.destroy', $account) }}" method="POST" class="d-inline delete-account-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="admin-action-link btn btn-link p-0 m-0 delete-account-btn" data-account-name="{{ $account->prenom }} {{ $account->nom }}" title="{{ __('admin.accounts_page.actions.delete') }}">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                Aucun compte disponible pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($accounts->hasPages())
            <div class="admin-pagination-container">
                {{ $accounts->links() }}
            </div>
        @endif

    </div>
</x-app-layout>

@include('admin.accounts.partials.delete-modal')

<script>
    (function () {
        const alert = document.getElementById('status-alert');
        if (!alert) { return; }
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    })();

    (function () {
        const searchInput = document.getElementById('search-account');
        if (!searchInput) { return; }

        let searchTimeout;
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '{{ route('admin.accounts.index') }}';

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchValue = this.value.trim();
                const url = new URL(window.location.href);
                
                // Réinitialiser à la page 1 lors de la recherche
                url.searchParams.delete('page');
                
                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }
                
                window.location.href = url.toString();
            }, 500);
        });
    })();

    (function () {
        const roleFilter = document.getElementById('filter-role');
        if (!roleFilter) { return; }

        roleFilter.addEventListener('change', function () {
            const url = new URL(window.location.href);
            
            // Réinitialiser à la page 1 lors du changement de filtre
            url.searchParams.delete('page');
            
            if (this.value) {
                url.searchParams.set('role', this.value);
            } else {
                url.searchParams.delete('role');
            }
            
            window.location.href = url.toString();
        });
    })();

    (function () {
        const modal = document.getElementById('deleteAccountModal');
        if (!modal) { return; }

        const bootstrapModal = new bootstrap.Modal(modal);
        let currentForm = null;

        document.querySelectorAll('.delete-account-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                currentForm = this.closest('.delete-account-form');
                const accountName = this.getAttribute('data-account-name') || '';
                const label = modal.querySelector('[data-account-name]');
                if (label) {
                    label.textContent = accountName;
                }
                bootstrapModal.show();
            });
        });

        const cancelBtn = modal.querySelector('.cancel-delete');
        const confirmBtn = modal.querySelector('.confirm-delete');

        cancelBtn?.addEventListener('click', () => {
            bootstrapModal.hide();
            currentForm = null;
        });

        confirmBtn?.addEventListener('click', () => {
            if (currentForm) {
                currentForm.submit();
                bootstrapModal.hide();
                currentForm = null;
            }
        });
    })();
</script>

