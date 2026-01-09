// Centralized validation for facture modals
(function(){
    const attachValidatorsToModal = (modal) => {
        try {
            if (!modal) return;
            let form = modal.querySelector('form[id^="form-modifier-facture-"]');
            let input = modal.querySelector('input[type="file"][name="facture"]');
            let errorBox = modal.querySelector('[id^="error-facture-"]');
            if (!form || !input || !errorBox) return;

            const showError = (msg) => {
                input.classList.add('is-invalid');
                errorBox.textContent = msg;
                errorBox.classList.remove('d-none');
            };
            const clearError = () => {
                input.classList.remove('is-invalid');
                errorBox.classList.add('d-none');
                errorBox.textContent = '';
            };

            const validateFile = (file) => {
                if (!file) return modal.querySelector('label[for]') ? modal.querySelector('label[for]').textContent.trim() : 'Fichier requis';

                const allowedExtensions = /(\.doc|\.docx|\.odt)$/i;
                if (!allowedExtensions.test(file.name)) return 'Type de fichier non autorisé (doc, docx, odt)';

                const maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) return 'Taille maximale 2MB';

                return null;
            };

            // Prevent duplicate handlers by removing existing and re-binding
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);
            // re-select elements from the DOM (the cloned ones)
            form = newForm;
            input = modal.querySelector('input[type="file"][name="facture"]');
            errorBox = modal.querySelector('[id^="error-facture-"]');

            input.addEventListener('change', function(){
                clearError();
                const f = this.files?.[0] ?? null;
                const err = validateFile(f);
                if (err) showError(err);
            });

            form.addEventListener('submit', function(e){
                e.preventDefault();
                e.stopPropagation();
                clearError();
                const f = input.files?.[0] ?? null;
                const err = validateFile(f);
                if (err) { showError(err); return false; }
                // submit using native submit to avoid re-triggering the handler
                form.submit();
            });

            input.addEventListener('invalid', function(ev){
                ev.preventDefault();
                const f = input.files?.[0] ?? null;
                const err = validateFile(f) || 'Fichier invalide';
                showError(err);
            });

        } catch (ex) {
            console.error('[facture-modal] attachValidators error', ex);
        }
    };

    // Listen for any modal shown event and attach if it matches pattern
    document.addEventListener('shown.bs.modal', function(e){
        const modal = e.target;
        if (!modal) return;
        if (modal?.id.startsWith('modal-modifier-facture-')) {
            attachValidatorsToModal(modal);
        }
    });

    // Also attach to already present modals on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('[id^="modal-modifier-facture-"]').forEach(modal => {
            // only attach if modal is currently shown or will be shown later
            attachValidatorsToModal(modal);
        });
    });
})();
