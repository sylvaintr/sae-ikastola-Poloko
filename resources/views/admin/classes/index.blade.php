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
                <h1 class="fw-bold display-4 mb-1" style="font-size: 2.5rem;">{{ __('admin.classes_page.title') }}</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ __('admin.classes_page.title_subtitle') }}</p>
            </div>

            <div class="d-flex flex-column align-items-start">
                <a href="{{ route('admin.classes.create') }}" class="btn admin-add-button">
                    {{ __('admin.classes_page.add_button') }}
                </a>
                <p class="text-muted mb-0 admin-button-subtitle">{{ __('admin.classes_page.add_button_subtitle') }}</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        @foreach (__('admin.classes_page.columns') as $column)
                            <th scope="col">
                                <span class="admin-table-heading">{{ $column['title'] }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $classe)
                        <tr>
                            <td>{{ $classe->idClasse }}</td>
                            <td>{{ $classe->nom }}</td>
                            <td>{{ $classe->niveau }}</td>
                            <td>{{ $classe->enfants_count }}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <a href="{{ route('admin.classes.show', $classe) }}" class="admin-action-link" title="{{ __('admin.classes_page.actions.view') }}">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('admin.classes.edit', $classe) }}" class="admin-action-link" title="{{ __('admin.classes_page.actions.edit') }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.classes.destroy', $classe) }}" method="POST" class="d-inline delete-classe-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="admin-action-link btn btn-link p-0 m-0 delete-classe-btn" data-classe-name="{{ $classe->nom }}" title="{{ __('admin.classes_page.actions.delete') }}">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                Aucune classe disponible pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

@include('admin.classes.partials.delete-modal')

<script>
    (function () {
        const alert = document.getElementById('status-alert');
        if (!alert) { return; }
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    })();

    (function () {
        const modal = document.getElementById('deleteClasseModal');
        if (!modal) { return; }

        const bootstrapModal = new bootstrap.Modal(modal);
        let currentForm = null;

        document.querySelectorAll('.delete-classe-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                currentForm = this.closest('.delete-classe-form');
                const classeName = this.getAttribute('data-classe-name') || '';
                const label = modal.querySelector('[data-classe-name]');
                if (label) {
                    label.textContent = classeName;
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

