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
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if ($account->statutValidation)
                                <span class="badge bg-success">{{ __('admin.accounts_page.status.validated') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('admin.accounts_page.status.not_validated') }}</span>
                            @endif
                            @if ($account->isArchived())
                                <span class="badge bg-dark">{{ __('admin.accounts_page.status.archived') }}</span>
                            @endif
                        </div>
                    </div>
                    @unless ($account->isArchived())
                        <a href="{{ route('admin.accounts.edit', $account) }}" class="btn btn-sm fw-semibold d-inline-flex align-items-center gap-2 admin-submit-btn">
                            <i class="bi bi-pencil-square"></i>
                            <span>{{ __('admin.accounts_page.actions.edit') }}</span>
                        </a>
                    @endunless
                </div>
            </div>
        </div>

        @if ($account->isArchived())
            <div class="alert alert-warning border-0 shadow-sm mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>{{ __('admin.accounts_page.show.archived_notice') }}</span>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-4">{{ __('admin.accounts_page.show.details_title') }}</h2>
                @php
                    $languageLabels = [
                        'fr' => 'Français',
                        'eus' => 'Euskara',
                    ];
                @endphp
                <div class="row gy-3">
                    <div class="col-md-4">
                        <div class="text-muted small">{{ __('admin.accounts_page.show.email_label') }}</div>
                        <div class="fw-semibold">{{ $account->email ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ __('admin.accounts_page.show.language_label') }}</div>
                        <div class="fw-semibold">{{ $languageLabels[$account->languePref] ?? strtoupper($account->languePref) }}</div>
                    </div>
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


