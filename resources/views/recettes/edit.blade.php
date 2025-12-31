<x-app-layout>
    <div class="container py-4" style="color: #333; font-family: sans-serif;">

        {{-- Retour --}}
        <a href="{{ route('evenements.show', $recette->evenement) }}"
           class="text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
            <i class="bi bi-arrow-left"></i>
            Retour à l'événement
        </a>

        {{-- TITRE --}}
        <h1 class="fw-bold mb-4">Modifier la recette</h1>

        {{-- FORMULAIRE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('recettes.update', $recette) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4"
                                  style="border: 1px solid #ced4da; border-radius: 8px;" required>{{ old('description', $recette->description) }}</textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="type" class="form-label fw-bold">Type</label>
                            <select name="type" id="type" class="form-select" style="border-radius: 8px; border: 1px solid #ced4da;" required>
                                <option value="recette" @selected(old('type', $recette->type) === 'recette')>Recette</option>
                                <option value="depense_previsionnelle" @selected(old('type', $recette->type) === 'depense_previsionnelle')>Dépense prévisionnelle</option>
                                <option value="depense" @selected(old('type', $recette->type) === 'depense')>Dépense</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="prix" class="form-label fw-bold">Montant (€)</label>
                            <input type="number" name="prix" id="prix" step="0.01" min="0"
                                   class="form-control" style="border-radius: 8px; border: 1px solid #ced4da;"
                                   value="{{ old('prix', $recette->prix) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="quantite" class="form-label fw-bold">Quantité</label>
                            <input type="text" name="quantite" id="quantite"
                                   class="form-control" style="border-radius: 8px; border: 1px solid #ced4da;"
                                   value="{{ old('quantite', $recette->quantite) }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('evenements.show', $recette->evenement) }}"
                           class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn border-0 fw-bold px-4 py-2"
                                style="background-color: #f39c12; color: white; border-radius: 8px;">
                            Mettre à jour la recette
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
