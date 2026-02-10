<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.index') }}" class="text-decoration-none d-inline-flex align-items-center gap-2 mb-4" style="color: #f39c12;">
            <i class="bi bi-arrow-left"></i>
            <span class="fw-bold">{{ __('Retour') }}</span>
        </a>

        <h1 class="display-6 fw-bold mb-4" style="color: #333;">{{ __('Rôles') }}</h1>

        <div class="card border-0 shadow-sm" style="border-radius: 10px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th class="ps-4 py-3 text-dark fw-bold" style="border-bottom: 1px solid #eee;">
                                    {{ __('Nom') }}
                                    <div class="text-muted small fw-normal">{{ __('Rôle Nom') }}</div>
                                </th>
                                <th class="pe-4 py-3 text-end text-dark fw-bold" style="border-bottom: 1px solid #eee;">
                                    {{ __('Ekintzak') }}
                                    <div class="text-muted small fw-normal text-end">{{ __('Actions') }}</div>
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
            </div>
        </div>
    </div>
</x-app-layout>
