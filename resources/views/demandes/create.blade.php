<x-app-layout>
    <div class="container py-4 demande-create-page">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                @php $isEdit = isset($demande); @endphp
                <h1 class="fw-bold mb-1">{{ $isEdit ? __('demandes.form.edit_title') : __('demandes.form.create_title') }}</h1>
                <p class="text-muted mb-0">
                    {{ $isEdit ? __('demandes.form.edit_subtitle') : __('demandes.form.create_subtitle') }}
                </p>
            </div>
            <div class="text-center text-md-end">
                <a href="{{ route('demandes.index') }}" class="btn demande-btn-outline px-4 fw-semibold">{{ __('demandes.form.buttons.back.eu') }}</a>
                <small class="text-muted d-block mt-1">{{ __('demandes.form.buttons.back.fr') }}</small>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <p class="fw-semibold mb-2">Merci de corriger les champs suivants :</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('demandes.update', $demande) : route('demandes.store') }}" enctype="multipart/form-data"
            class="demande-create-form">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <div class="demande-field-row">
                <div class="demande-field-col flex-grow-1">
                    <label for="demande-title" class="form-label">{{ __('demandes.form.labels.title.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.title.fr') }}</small></label>
                    <input id="demande-title" type="text" name="titre" class="form-control" value="{{ old('titre', $demande->titre ?? '') }}" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : 'required' }}>
                </div>
                <div class="demande-field-col demande-field-sm">
                    <label for="demande-urgency" class="form-label">{{ __('demandes.form.labels.urgency.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.urgency.fr') }}</small></label>
                    <select id="demande-urgency" name="urgence" class="form-select" required>
                        @foreach ($urgences as $urgence)
                            <option value="{{ $urgence }}" @selected(old('urgence', $demande->urgence ?? '') === $urgence)>{{ $urgence }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col w-100">
                    <label for="demande-description" class="form-label">{{ __('demandes.form.labels.description.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.description.fr') }}</small></label>
                    <textarea id="demande-description" name="description" rows="4" class="form-control" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : 'required' }}>{{ old('description', $demande->description ?? '') }}</textarea>
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col flex-grow-1">
                    <label for="demande-type" class="form-label">{{ __('demandes.form.labels.type.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.type.fr') }}</small></label>
                    <input id="demande-type" type="text" name="type" class="form-control" list="types-list" value="{{ old('type', $demande->type ?? '') }}" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : 'required' }}>
                    <datalist id="types-list">
                        @foreach ($types as $type)
                            <option value="{{ $type }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="demande-field-col demande-field-sm">
                    <label for="demande-planned-expense" class="form-label">{{ __('demandes.form.labels.planned_expense.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.planned_expense.fr') }}</small></label>
                    <input id="demande-planned-expense" type="number" step="0.01" min="0" name="montantP" class="form-control"
                        value="{{ old('montantP', $demande->montantP ?? '') }}" {{ $isEdit && ($demande->etat === 'Terminé') ? 'disabled' : '' }}>
                </div>
            </div>

            @if(!$isEdit)
                <div class="demande-field-row photo-row align-items-center">
                    <div class="demande-field-col flex-grow-1 d-flex align-items-center gap-4">
                    <label for="photos-input" class="form-label mb-0">{{ __('demandes.form.labels.photo.eu') }} <small class="text-muted d-block">{{ __('demandes.form.labels.photo.fr') }}</small></label>
                        <div class="d-flex flex-column">
                            <label for="photos-input" class="demande-upload-btn mb-0">
                            <input id="photos-input" type="file" name="photos[]" class="d-none" accept=".jpg,.jpeg,.png" multiple>
                            {{ __('demandes.form.buttons.upload.eu') }}
                            </label>
                        <small class="text-muted d-block mt-1 text-center">{{ __('demandes.form.buttons.upload.fr') }}</small>
                        </div>
                    </div>
                </div>
                <div id="photos-preview" class="row g-2 mt-2"></div>
            @endif

            <div class="demande-form-actions d-flex justify-content-end gap-4 mt-4">
                <div class="text-center">
                    <a href="{{ route('demandes.index') }}" class="btn demande-btn-outline px-5">{{ __('demandes.form.buttons.back.eu') }}</a>
                    <small class="text-muted d-block mt-1">{{ __('demandes.form.buttons.back.fr') }}</small>
                </div>
                <div class="text-center">
                    @if($isEdit && $demande->etat === 'Terminé')
                        <button type="button" class="btn demande-btn-primary px-5" disabled>{{ __('demandes.form.buttons.disabled') }}</button>
                        <small class="text-muted d-block mt-1">{{ __('demandes.form.buttons.disabled_sub') }}</small>
                    @else
                        <button type="submit" class="btn demande-btn-primary px-5">{{ __('demandes.form.buttons.save.eu') }}</button>
                        <small class="text-muted d-block mt-1">{{ __('demandes.form.buttons.save.fr') }}</small>
                    @endif
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('photos-input');
        const preview = document.getElementById('photos-preview');

        if (!input || !preview) return;
        const noFileText = '{{ __('demandes.form.no_file') }}';

        input.addEventListener('change', function () {
            preview.innerHTML = '';
            const files = Array.from(this.files || []);

            if (!files.length) {
                preview.innerHTML = `<div class="text-muted small">${noFileText}</div>`;
                return;
            }

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const col = document.createElement('div');
                    col.className = 'col-md-3';
                    // Create the thumb container
                    const thumbDiv = document.createElement('div');
                    thumbDiv.className = 'demande-photo-thumb';

                    // Create the image element
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.setAttribute('alt', file.name);
                    thumbDiv.appendChild(img);

                    // Create the file name display
                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'small text-muted text-truncate';
                    nameDiv.textContent = file.name;

                    // Append to col
                    col.appendChild(thumbDiv);
                    col.appendChild(nameDiv);
                    preview.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>

