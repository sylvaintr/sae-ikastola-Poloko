<form
    action="{{ isset($actualite) ? route('admin.actualites.update', $actualite->idActualite) : route('admin.actualites.store') }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf
    @if(isset($actualite))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="titre" class="form-label">Titre</label>
        <input
            id="titre"
            name="titre"
            type="text"
            class="form-control"
            placeholder="Ajouter un titre..."
            value="{{ old('titre', $actualite->titre ?? '') }}"
        />
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea
            id="description"
            name="description"
            rows="3"
            cols="50"
            class="form-control"
            placeholder="Ajouter une description..."
        >{{ old('description', $actualite->description ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label for="contenu" class="form-label">Contenu</label>
        <textarea
            id="contenu"
            name="contenu"
            rows="15"
            cols="50"
            class="form-control"
            placeholder="Contenu de l'article..."
        >{{ old('contenu', $actualite->contenu ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label for="type" class="form-label">Type</label>
        <select name="type" id="type" class="form-select">
            <option value="Privée" {{ old('type', $actualite->type ?? '') == 'Privée' ? 'selected' : '' }}>Privée</option>
            <option value="Publique" {{ old('type', $actualite->type ?? '') == 'Publique' ? 'selected' : '' }}>Publique</option>
        </select>
    </div>

    <input type="hidden" name="archive" value="0">
    <div class="mb-3 form-check">
        <input
            type="checkbox"
            id="archive"
            name="archive"
            value="1"
            class="form-check-input"
            {{ old('archive', $actualite->archive ?? false) ? 'checked' : '' }}
        >
        <label for="archive" class="form-check-label">Archiver</label>
    </div>

    @if(isset($actualite) && $actualite->documents->count() > 0)
    <div class="mb-3">
        <p>Images liées :</p>
        <div class="d-flex gap-3" style="flex-wrap: wrap;">
            @foreach($actualite->documents as $document)
                <div class="card d-flex text-center">
                    <div class="card-header">
                        <img
                            src="{{ asset('storage/'.$document->chemin) }}"
                            alt={{ $document->nom }}
                            style="height:150px; width:auto;"
                        >
                    </div>
                    <div class="card-body">
                        {{ $document->nom }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="mb-3">
        <label for="documents" class="form-label">Images (optionnel)</label>
        <input
            type="file"
            name="documents[]"
            id="documents"
            class="form-control"
            multiple
            accept="image/*"
        >
    </div>

    <div class="mb-3">
        <label for="lien" class="form-label">Lien (optionnel)</label>
        <textarea
            id="lien"
            name="lien"
            rows="2"
            cols="50"
            class="form-control"
            placeholder="Lien"
        >{{ old('lien', $actualite->lien ?? '') }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary fw-bold text-center">
        {{ isset($actualite) ? 'Modifier' : 'Ajouter' }}
    </button>
</form>
<span>
    <button class="btn btn-secondary mt-1" onclick="history.back();">Retour</button>
</span>

