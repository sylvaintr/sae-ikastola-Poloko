<div class="modal fade" id="deleteActualiteModal" tabindex="-1" aria-labelledby="deleteActualiteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteActualiteModalLabel">{{ __('actualite.modals.delete.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('demandes.actions.close') }}"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('actualite.modals.delete.message') }}</p>
                <p class="fw-bold mb-0" data-actualite-title=""></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary cancel-delete" data-bs-dismiss="modal">{{ __('actualite.modals.delete.cancel') }}</button>
                <button type="button" class="btn btn-danger confirm-delete">{{ __('actualite.modals.delete.confirm') }}</button>
            </div>
        </div>
    </div>
</div>

