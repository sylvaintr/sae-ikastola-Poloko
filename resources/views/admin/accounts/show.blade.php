<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.accounts.index') }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.accounts_page.back') }}</span>
        </a>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                        <h1 class="h4 fw-bold mb-0">{{ $account->prenom }} {{ $account->nom }}</h1>
                        <div class="d-flex align-items-center gap-2">
                            @if ($account->statutValidation)
                                <span class="badge bg-success">Validé</span>
                            @else
                                <span class="badge bg-secondary">Non validé</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.accounts.edit', $account) }}" class="btn btn-sm fw-semibold d-inline-flex align-items-center gap-2 admin-submit-btn">
                        <i class="bi bi-pencil-square"></i>
                        <span>{{ __('admin.accounts_page.actions.edit') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-4">{{ __('admin.accounts_page.show.roles_label') }}</h2>
                @if ($account->rolesCustom->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($account->rolesCustom as $role)
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('admin.accounts_page.show.no_roles') }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

