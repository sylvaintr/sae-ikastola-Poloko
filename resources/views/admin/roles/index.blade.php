<x-app-layout>
    <div class="container py-4">
        
        <div class="mb-4">
        <h1 class="display-6 fw-bold mb-0" style="color: #333;">{{ Lang::get('admin.gestion_roles', [], 'eus') }}</h1>

        @if(Lang::getLocale() == 'fr')
            <p class="text-muted mb-0">{{ __('admin.gestion_roles') }}</p>
        @endif
        </div>


        <div class="card border-0 shadow-sm" style="border-radius: 10px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th class="ps-4 py-3 text-dark fw-bold" style="border-bottom: 1px solid #eee;">
                                    {{ Lang::get('admin.role_name', [], 'eus') }}
                                    @if (Lang::getLocale() == 'fr')
                                        <div class="text-muted small fw-normal">{{ __('admin.role_name') }}</div>
                                    @endif
                                </th>
                                <th class="pe-4 py-3 text-end text-dark fw-bold" style="border-bottom: 1px solid #eee;">
                                    {{ Lang::get('admin.actions',  [], 'eus') }}
                                    @if (Lang::getLocale() == 'fr')
                                        <div class="text-muted small fw-normal text-end">{{ __('admin.actions') }}</div>
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr class="border-bottom">
                                    <td class="ps-4 py-3 fw-semibold text-dark">
                                        {{ $role->name }}
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <div class="d-flex justify-content-end align-items-center gap-3">
                                            <a href="{{ route('admin.roles.show', $role) }}" class="text-dark" title="Modifier">
                                                <i class="bi bi-pencil-square fs-5"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $roles->links('pagination::bootstrap-5') }}
                    </div>
            </div>
        </div>
    </div>
</x-app-layout>
