<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.classes.show', $classe) }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour à la classe</span>
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

                    <div class="d-flex gap-3 justify-content-end">
                        <a href="{{ route('admin.classes.show', $classe) }}" class="btn admin-cancel-btn px-4">{{ __('admin.classes_page.create.cancel') }}</a>
                        <button type="submit" class="btn fw-semibold px-4 admin-submit-btn">{{ __('admin.classes_page.create.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

