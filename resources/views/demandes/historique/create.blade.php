<x-app-layout>
    <div class="container py-5 demande-history-create">
        <div class="mb-4">
            <a href="{{ route('demandes.show', $demande) }}" class="text-decoration-none text-muted small">
                ← Retour à la demande #{{ $demande->idTache }}
            </a>
        </div>

        <div class="text-center mb-5">
            <p class="text-uppercase text-muted mb-1">Historikoa</p>
            <h1 class="fw-bold">Ajouter un avancement</h1>
            <p class="text-muted">Complétez ce formulaire pour enregistrer un nouvel avancement dans l'historique.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('demandes.historique.store', $demande) }}" class="avancement-form">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Titre</label>
                        <input type="text" name="titre" class="form-control form-control-lg" value="{{ old('titre') }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="5" class="form-control form-control-lg">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Dépenses</label>
                        <input type="number" step="0.01" min="0" name="depense" class="form-control depense-input" value="{{ old('depense') }}">
                    </div>
                    <div class="text-center mt-5">
                        <button type="submit" class="btn demande-btn-primary px-5">
                            Sortu txartel eskaera
                        </button>
                        <div class="text-muted small mt-2">Créer un avancement</div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

