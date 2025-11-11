<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.classes.index') }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux classes</span>
        </a>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-4">{{ __('admin.classes_page.create.title') }}</h1>

                <form method="POST" action="{{ route('admin.classes.store') }}" class="admin-form">
                    @csrf

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="nom" class="form-label fw-semibold">{{ __('admin.classes_page.create.fields.name') }}</label>
                            <input id="nom" name="nom" type="text" maxlength="20"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom') }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="niveau" class="form-label fw-semibold">{{ __('admin.classes_page.create.fields.level') }}</label>
                            <input id="niveau" name="niveau" type="text" maxlength="3"
                                   class="form-control @error('niveau') is-invalid @enderror"
                                   value="{{ old('niveau') }}" required>
                            @error('niveau')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4 justify-content-end">
                        <a href="{{ route('admin.classes.index') }}" class="btn admin-cancel-btn px-4">
                            {{ __('admin.classes_page.create.cancel') }}
                        </a>
                        <button type="submit" class="btn fw-semibold px-4 admin-submit-btn">
                            {{ __('admin.classes_page.create.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

