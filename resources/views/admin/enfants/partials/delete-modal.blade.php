<div class="modal fade" id="deleteEnfantModal" tabindex="-1" aria-labelledby="deleteEnfantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4">
                <h2 class="h5 fw-bold mb-3">{{ __('admin.enfants_page.actions.delete') }}</h2>
                <p class="mb-0 text-muted">
                    {!! __('admin.enfants_page.delete_confirmation', ['name' => '<span class="fw-semibold text-dark" data-enfant-name></span>']) !!}
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary admin-cancel-btn cancel-delete px-4">
                    {{ __('admin.enfants_page.create.cancel') }}
                </button>
                <button type="button" class="btn btn-danger confirm-delete px-4 fw-semibold">
                    {{ __('admin.enfants_page.actions.confirm_delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

