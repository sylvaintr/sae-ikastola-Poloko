<div class="modal fade" id="deleteDocumentModal" tabindex="-1" aria-labelledby="deleteDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteDocumentModalLabel">{{ __('admin.obligatory_documents.delete_modal.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('admin.obligatory_documents.delete_modal.message') }}</p>
                <p class="fw-bold mb-0" data-document-name=""></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary cancel-delete" data-bs-dismiss="modal">{{ __('admin.obligatory_documents.delete_modal.cancel') }}</button>
                <button type="button" class="btn btn-danger confirm-delete">{{ __('admin.obligatory_documents.delete_modal.confirm') }}</button>
            </div>
        </div>
    </div>
</div>

