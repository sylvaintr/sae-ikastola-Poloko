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
                <h1 class="fw-bold display-4 mb-1" style="font-size: 2.5rem;">{{ __('admin.obligatory_documents.title') }}</h1>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ __('admin.obligatory_documents.subtitle') }}</p>
            </div>

            <div class="d-flex flex-column align-items-start">
                <a href="{{ route('admin.obligatory_documents.create') }}" class="btn admin-add-button">
                    {{ __('admin.obligatory_documents.add_button') }}
                </a>
                <p class="text-muted mb-0 admin-button-subtitle">{{ __('admin.obligatory_documents.add_button_subtitle') }}</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        <th scope="col">
                            <span class="admin-table-heading">{{ __('admin.obligatory_documents.columns.title') }}</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ __('admin.obligatory_documents.columns.title_subtitle') }}</p>
                        </th>
                        <th scope="col">
                            <span class="admin-table-heading">{{ __('admin.obligatory_documents.columns.role') }}</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ __('admin.obligatory_documents.columns.role_subtitle') }}</p>
                        </th>
                        <th scope="col">
                            <span class="admin-table-heading">{{ __('admin.obligatory_documents.columns.expiration_date') }}</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ __('admin.obligatory_documents.columns.expiration_date_subtitle') }}</p>
                        </th>
                        <th scope="col">
                            <span class="admin-table-heading">{{ __('admin.obligatory_documents.columns.actions') }}</span>
                            <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">{{ __('admin.obligatory_documents.columns.actions_subtitle') }}</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>{{ $document->nom }}</td>
                            <td>
                                @if($document->roles->count() > 0)
                                    @if($document->hasAllRoles ?? false)
                                        {{ __('admin.obligatory_documents.all_roles') }}
                                    @else
                                        {{ $document->roles->pluck('name')->join(', ') }}
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($document->calculatedExpirationDate)
                                    @php
                                        $expirationDate = \Carbon\Carbon::parse($document->calculatedExpirationDate);
                                        $formattedDate = $expirationDate->format('d/m/Y');
                                    @endphp
                                    <span>{{ $formattedDate }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <a href="{{ route('admin.obligatory_documents.edit', $document) }}" class="admin-action-link" title="{{ __('admin.obligatory_documents.actions.edit') }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.obligatory_documents.destroy', $document) }}" method="POST" class="d-inline delete-document-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="admin-action-link btn btn-link p-0 m-0 delete-document-btn" data-document-name="{{ $document->nom }}" title="{{ __('admin.obligatory_documents.actions.delete') }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                {{ __('admin.obligatory_documents.no_documents') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

@include('admin.obligatory-documents.partials.delete-modal')

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
        const modal = document.getElementById('deleteDocumentModal');
        if (!modal) { return; }

        const bootstrapModal = new bootstrap.Modal(modal);
        let currentForm = null;

        document.querySelectorAll('.delete-document-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                currentForm = this.closest('.delete-document-form');
                const documentName = this.getAttribute('data-document-name') || '';
                const label = modal.querySelector('[data-document-name]');
                if (label) {
                    label.textContent = documentName;
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

