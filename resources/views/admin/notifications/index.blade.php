<x-app-layout>
    <div class="container py-4 py-md-5">
        
       
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-5">
            <div>
                <h2 class="fw-bold mb-0">
                    {{ __('notifications.title', [], 'eus') }}
                </h2>
                @if(app()->getLocale() == 'fr')
                    <div class="text-muted small mt-1">
                        {{ __('notifications.title', [], 'fr') }}
                    </div>
                @endif
            </div>

            <div class="d-flex flex-column align-items-start align-items-md-end">
                <a href="{{ route('admin.notifications.create') }}" class="btn text-white px-4 fw-bold w-100 w-md-auto" style="background-color: #F59E0B;">
                    {{ __('notifications.add', [], 'eus') }}
                </a>
                @if(app()->getLocale() == 'fr')
                    <span class="text-muted small mt-1">
                        {{ __('notifications.add', [], 'fr') }}
                    </span>
                @endif
            </div>
        </div>

        
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">
                                    {{ __('notifications.table_title', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="text-muted small fw-normal mt-1">{{ __('notifications.table_title', [], 'fr') }}</div>
                                    @endif
                                </th>
                                <th>
                                    {{ __('notifications.table_module', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="text-muted small fw-normal mt-1">{{ __('notifications.table_module', [], 'fr') }}</div>
                                    @endif
                                </th>
                                <th>
                                    {{ __('notifications.table_description', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="text-muted small fw-normal mt-1">{{ __('notifications.table_description', [], 'fr') }}</div>
                                    @endif
                                </th>
                                <th class="text-center">
                                    {{ __('notifications.table_recurrence', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="text-muted small fw-normal mt-1">{{ __('notifications.table_recurrence', [], 'fr') }}</div>
                                    @endif
                                </th>
                                <th class="text-center">
                                    {{ __('notifications.table_reminder', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="text-muted small fw-normal mt-1">{{ __('notifications.table_reminder', [], 'fr') }}</div>
                                    @endif
                                </th>
                                <th class="text-center">
                                    {{ __('notifications.table_active', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="text-muted small fw-normal mt-1">{{ __('notifications.table_active', [], 'fr') }}</div>
                                    @endif
                                </th>
                                <th class="text-end pe-4">
                                    {{ __('notifications.table_actions', [], 'eus') }}
                                    @if(app()->getLocale() == 'fr')
                                        <div class="text-muted small fw-normal mt-1">{{ __('notifications.table_actions', [], 'fr') }}</div>
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $setting)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    {{ Str::limit($setting->title, 25) }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $setting->module_label }}
                                    </span>
                                </td>
                                <td class="text-muted">
                                    {{ Str::limit($setting->description, 20) }}
                                </td>
                                <td class="text-center">
                                    @if($setting->recurrence_days)
                                        {{ $setting->recurrence_days }} j
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">
                                    {{ $setting->reminder_days }} j
                                </td>
                                <td class="text-center">
                                    @if($setting->is_active)
                                        <i class="bi bi-check-lg fw-bold fs-5 text-success"></i>
                                    @else
                                        <i class="bi bi-dash-lg fs-5 text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.notifications.edit', $setting->id) }}" class="text-dark">
                                        <i class="bi bi-pencil-fill fs-6"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                                    <div class="fw-bold">{{ __('notifications.empty', [], 'eus') }}</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

