<x-app-layout>
    <div class="container py-5">
        <div class="mb-5">
            <h1 class="fw-bold display-4 mb-0" style="font-size: 2.5rem;">{{ Lang::get('admin.title', [], 'eus') }}</h1>
            @if (Lang::getLocale() == 'fr')
                <p class="fw-light text-muted mb-0" style="font-size: 1.25rem;">{{ __('admin.title') }}</p>
            @endif
        </div>

        <div class="row g-4">
            @php
                $sections = [
                    'add_message' => [
                        'route' => 'admin.actualites.index',
                        'icon' => 'bi-newspaper',
                        'color' => 'primary',
                        'permission' => 'gerer-actualites',
                    ],
                    'accounts' => [
                        'route' => 'admin.accounts.index',
                        'icon' => 'bi-people',
                        'color' => 'info',
                        'permission' => 'gerer-utilisateurs',
                    ],
                    'families' => [
                        'route' => 'admin.familles.index',
                        'icon' => 'bi-house-heart',
                        'color' => 'success',
                        'permission' => 'gerer-familles',
                    ],
                    'classes' => [
                        'route' => 'admin.classes.index',
                        'icon' => 'bi-book',
                        'color' => 'warning',
                        'permission' => 'gerer-classes',
                    ],
                    'enfants' => [
                        'route' => 'admin.enfants.index',
                        'icon' => 'bi-person-badge',
                        'color' => 'info',
                        'permission' => 'gerer-enfants',
                    ],
                    'obligatory_documents' => [
                        'route' => 'admin.obligatory_documents.index',
                        'icon' => 'bi-file-earmark-text',
                        'color' => 'danger',
                        'permission' => 'gerer-document-obligatoire',
                    ],
                    'invoices' => [
                        'route' => 'admin.facture.index',
                        'icon' => 'bi-receipt',
                        'color' => 'secondary',
                        'permission' => 'gerer-factures',
                    ],
                    'role_permissions' => [
                        'route' => 'admin.roles.index',
                        'icon' => 'bi-shield-lock',
                        'color' => 'dark',
                        'permission' => 'gerer-roles',
                    ],
                    'notifications' => [
                        'route' => 'admin.notifications.index',
                        'icon' => 'bi-bell',
                        'color' => 'primary',
                        'permission' => 'gerer-notifications',
                    ],
                ];
            @endphp

            @foreach ($sections as $key => $section)
                @can($section['permission'])
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route($section['route']) }}" class="text-decoration-none admin-card-link">
                            <div class="card admin-dashboard-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="admin-card-icon admin-card-icon-{{ $section['color'] }}">
                                            <i class="bi {{ $section['icon'] }}"></i>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <h3 class="card-title mb-0 fw-bold">{{ Lang::get('admin.sections.' . $key, [], 'eus') }}</h3>
                                            @if (Lang::getLocale() == 'fr')
                                                <p class="mb-0 fw-light text-muted" style="font-size: 0.85rem;">{{ __('admin.sections.' . $key) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-text text-muted mb-0">
                                        <span>{{ Lang::get('admin.sections.descriptions.' . $key, [], 'eus') }}</span>
                                        @if (Lang::getLocale() == 'fr')
                                            <br><span class="fw-light" style="font-size: 0.85rem;">{{ __('admin.sections.descriptions.' . $key) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endcan
            @endforeach
        </div>
    </div>
</x-app-layout>
