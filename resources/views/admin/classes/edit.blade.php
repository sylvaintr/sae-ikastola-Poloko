<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.classes.index') }}" class="text-decoration-none mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour à la liste</span>
        </a>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-4">Modifier la classe</h1>

                <form method="POST" action="{{ route('admin.classes.update', $classe) }}" class="admin-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="nom" class="form-label fw-semibold">Nom de la classe</label>
                        <input id="nom" name="nom" type="text" class="form-control @error('nom') is-invalid @enderror"
                               value="{{ old('nom', $classe->nom) }}" required maxlength="255">
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-3">
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary px-4">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

