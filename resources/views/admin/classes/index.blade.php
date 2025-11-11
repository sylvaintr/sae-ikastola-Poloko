<x-app-layout>
    <div class="container py-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3 mb-4">
            <h1 class="fw-bold display-6 mb-0">{{ __('admin.classes_page.title') }}</h1>

            @if (session('status'))
                <div id="status-alert" class="alert alert-success status-alert mb-0 px-3 py-2">
                    {{ session('status') }}
                </div>
            @endif
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

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary admin-add-button d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i>
                <span>Ajouter une classe</span>
            </a>
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

