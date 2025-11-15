<x-app-layout>
    <div class="container py-4 demande-create-page">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">Sortu txartel eskaera</h1>
                <p class="text-muted mb-0">Créez une nouvelle demande en complétant les champs ci-dessous.</p>
            </div>
            <a href="{{ route('demandes.index') }}" class="btn demande-btn-outline px-4 fw-semibold">Itzuli</a>
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

        <form method="POST" action="{{ route('demandes.store') }}" enctype="multipart/form-data"
            class="demande-create-form">
            @csrf
            <div class="demande-field-row">
                <div class="demande-field-col flex-grow-1">
                    <label class="form-label">Titre</label>
                    <input type="text" name="titre" class="form-control" value="{{ old('titre') }}" required>
                </div>
                <div class="demande-field-col demande-field-sm">
                    <label class="form-label">Urgence</label>
                    <select name="urgence" class="form-select" required>
                        @foreach ($urgences as $urgence)
                            <option value="{{ $urgence }}" @selected(old('urgence') === $urgence)>{{ $urgence }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col w-100">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control" required>{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col flex-grow-1">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" class="form-control" list="types-list" value="{{ old('type') }}" required>
                    <datalist id="types-list">
                        @foreach ($types as $type)
                            <option value="{{ $type }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="demande-field-col demande-field-sm">
                    <label class="form-label">Dépense prévisionnelle (€)</label>
                    <input type="number" step="0.01" min="0" name="montantP" class="form-control"
                        value="{{ old('montantP') }}">
                </div>
            </div>

            <div class="demande-field-row">
                <div class="demande-field-col flex-grow-1">
                    <label class="form-label">Date de début</label>
                    <input type="date" name="dateD" class="form-control" value="{{ old('dateD', now()->toDateString()) }}"
                        required>
                </div>
                <div class="demande-field-col flex-grow-1">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="dateF" class="form-control" value="{{ old('dateF') }}">
                </div>
            </div>

            <div class="demande-field-row align-items-center">
                <div class="demande-field-col flex-grow-1">
                    <label class="form-label d-block">Photo</label>
                    <label class="demande-upload-btn">
                        <input id="photos-input" type="file" name="photos[]" class="d-none" accept=".jpg,.jpeg,.png" multiple>
                        Sélectionner un fichier
                    </label>
                    <small class="text-muted d-block mt-1">Formats : JPG ou PNG, 4 Mo max, jusqu'à 4 photos.</small>
                    <div id="photos-preview" class="row g-2 mt-2"></div>
                </div>
            </div>

            <div class="demande-form-actions d-flex justify-content-end gap-4 mt-4">
                <div class="text-center">
                    <a href="{{ route('demandes.index') }}" class="btn demande-btn-outline px-5">Utzi</a>
                    <small class="text-muted d-block mt-1">Annuler</small>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn demande-btn-primary px-5">Gorde</button>
                    <small class="text-muted d-block mt-1">Enregistrer</small>
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

        input.addEventListener('change', function () {
            preview.innerHTML = '';
            const files = Array.from(this.files || []);

            if (!files.length) {
                preview.innerHTML = '<div class="text-muted small">Aucun fichier sélectionné.</div>';
                return;
            }

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const col = document.createElement('div');
                    col.className = 'col-md-3';
                    col.innerHTML = `
                        <div class="demande-photo-thumb">
                            <img src="${event.target.result}" alt="${file.name}">
                        </div>
                        <div class="small text-muted text-truncate">${file.name}</div>
                    `;
                    preview.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>

