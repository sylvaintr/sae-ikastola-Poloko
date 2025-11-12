<form 
    action="{{ isset($actualite) ? route('actualites.update', $actualite->idActualite) : route('actualites.store') }}" 
    method="POST"
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
            placeholder="Titre"
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
            placeholder="Description"
        >{{ old('description', $actualite->description ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label for="type" class="form-label">Type</label>
        <select name="type" id="type" class="form-select">
            <option value="Privée" {{ old('type', $actualite->type ?? '') == 'Privée' ? 'selected' : '' }}>Privée</option>
            <option value="Publique" {{ old('type', $actualite->type ?? '') == 'Publique' ? 'selected' : '' }}>Publique</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="idUtilisateur" class="form-label">Utilisateur</label>
        <select name="idUtilisateur" id="idUtilisateur" class="form-select">
            <option value="">-- Sélectionner un utilisateur --</option>
            @foreach ($utilisateurs as $utilisateur)
                <option value="{{ $utilisateur->idUtilisateur }}" 
                    {{ old('idUtilisateur', $actualite->idUtilisateur ?? '') == $utilisateur->idUtilisateur ? 'selected' : '' }}>
                    {{ $utilisateur->nom }} {{ $utilisateur->prenom }}
                </option>
            @endforeach
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
