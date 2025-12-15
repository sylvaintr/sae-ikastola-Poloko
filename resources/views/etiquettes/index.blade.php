<x-app-layout>
    <script>
        const currentLang = "{{ app()->getLocale() }}";
    </script>

    @vite(['resources/js/etiquette.js'])

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">{{ Lang::get('etiquette.gestion', [], 'eus') }}
                @if (Lang::getLocale() == 'fr')
                    <p class="fw-light mb-0">{{ Lang::get('etiquette.gestion') }}</p>
                @endif
            </h2>
            <a href="{{ route('admin.etiquettes.create') }}" class="btn btn-orange">
                <i class="bi bi-plus-circle"></i> {{ Lang::get('etiquette.nouvelle', [], 'eus') }}
                @if (Lang::getLocale() == 'fr')
                    <span class="fw-light ms-2">{{ Lang::get('etiquette.nouvelle') }}</span>
                @endif
            </a>
        </div>

        {{-- Filters for etiquettes table --}}
        @php $roles = \App\Models\Role::all(); @endphp
        <div class="d-flex flex-row-reverse row mb-3 g-2">
            <div class="col-sm-2 d-flex">
                <button id="reset-etiquette-filters" class="btn btn-outline-secondary ms-auto">{{ __('actualite.reset') ?? 'Réinitialiser' }}</button>
            </div>
            
            <div class="col-sm-4">
                <select id="filter-role" class="form-select">
                    <option value="">{{ __('etiquette.all_roles')  }}</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->idRole }}">{{ $r->name ?? $r->display_name ?? $r->idRole }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <table class="table table-hover align-middle mb-0" id="TableEtiquettes" style="width:100%">
            <thead class="bg-light">
                <tr>
                    <th>{{ Lang::get('etiquette.id', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('etiquette.id') }}</p>
                        @endif
                    </th>
                    <th>{{ Lang::get('etiquette.nom', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('etiquette.nom') }}</p>
                        @endif
                    </th>
                    <th>{{ Lang::get('etiquette.roles', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('etiquette.roles') }}</p>
                        @endif
                    </th>
                    <th class="text-end pe-4">{{ Lang::get('etiquette.actions', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('etiquette.actions') }}</p>
                        @endif
                    </th>
                </tr>
            </thead>
        </table>
        
        {{-- Delete confirmation modal (reused for all etiquette rows) --}}
        <div class="modal fade" id="deleteConfirmModalEtiquette" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">{{ __('common.confirm_delete') ?? 'Confirmer la suppression' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('etiquette.confirm_delete_message') ?? 'Voulez-vous vraiment supprimer cette étiquette ?' }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') ?? 'Annuler' }}</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteEtiquetteBtn">{{ __('common.delete') ?? 'Supprimer' }}</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                afficherDataTableEtiquettes('TableEtiquettes');
            });
        </script>
        <script>
            // Modal deletion handling for etiquettes table
            document.addEventListener('DOMContentLoaded', function () {
                const deleteModalEl = document.getElementById('deleteConfirmModalEtiquette');
                if (!deleteModalEl) return;
                const deleteModal = new bootstrap.Modal(deleteModalEl);
                let formToSubmit = null;

                // Open modal when any delete button is clicked
                document.body.addEventListener('click', function (e) {
                    const btn = e.target.closest('.btn-open-delete-modal');
                    if (!btn) return;
                    e.preventDefault();
                    const formId = btn.getAttribute('data-form-id');
                    formToSubmit = document.getElementById(formId);
                    deleteModal.show();
                });

                // Confirm deletion
                document.getElementById('confirmDeleteEtiquetteBtn').addEventListener('click', function () {
                    if (formToSubmit) {
                        formToSubmit.submit();
                    }
                });
            });
        </script>
    </div>
</x-app-layout>
