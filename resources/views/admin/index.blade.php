<x-app-layout>
    @php($sections = [
        'add_message' => 'admin.messages',
        'accounts' => 'admin.accounts',
        'families' => 'admin.families',
        'classes' => 'admin.classes.index',
        'invoices' => 'admin.invoices',
        'notifications' => 'admin.notifications',
    ])

    <div class="container py-4">
        @foreach ($sections as $key => $route)
            <a href="{{ route($route) }}" class="fw-bold fs-3 text-dark mb-4 d-block admin-section-link">
                {{ __('admin.sections.' . $key) }}
            </a>
        @endforeach
    </div>
</x-app-layout>

