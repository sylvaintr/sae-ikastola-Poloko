<x-app-layout>
    <div class="container py-5 demande-history-create">
        <div class="mb-4">
            <a href="{{ route('demandes.show', $demande) }}" class="text-decoration-none text-muted small">
                {{ __('demandes.history_form.link', ['id' => '#' . $demande->idTache]) }}
            </a>
        </div>

        <div class="text-center mb-5">
            <p class="text-uppercase text-muted mb-1">{{ __('demandes.history.section.history.eu') }}</p>
            <h1 class="fw-bold mb-1">{{ __('demandes.history_form.heading.eu') }}</h1>
            <p class="text-muted mb-3">{{ __('demandes.history_form.heading.fr') }}</p>
            <p class="text-muted mb-0">{{ __('demandes.history_form.subtitle.eu') }}</p>
            <p class="text-muted small">{{ __('demandes.history_form.subtitle.fr') }}</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('demandes.historique.store', $demande) }}" class="avancement-form">
                    @csrf
                    <div class="mb-4">
                        <label for="history-title" class="form-label fw-semibold">{{ __('demandes.history_form.fields.title.eu') }} <small class="text-muted d-block">{{ __('demandes.history_form.fields.title.fr') }}</small></label>
                        <input id="history-title" type="text" name="titre" class="form-control form-control-lg" value="{{ old('titre') }}" required>
                    </div>
                    <div class="mb-4">
                        <label for="history-description" class="form-label fw-semibold">{{ __('demandes.history_form.fields.description.eu') }} <small class="text-muted d-block">{{ __('demandes.history_form.fields.description.fr') }}</small></label>
                        <textarea id="history-description" name="description" rows="5" class="form-control form-control-lg">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label for="history-expense" class="form-label fw-semibold">{{ __('demandes.history_form.fields.expense.eu') }} <small class="text-muted d-block">{{ __('demandes.history_form.fields.expense.fr') }}</small></label>
                        <input id="history-expense" type="number" step="0.01" min="0" name="depense" class="form-control depense-input" value="{{ old('depense') }}">
                    </div>
                    <div class="text-center mt-5">
                        <button type="submit" class="btn demande-btn-primary px-5">
                            {{ __('demandes.history_form.button.eu') }}
                        </button>
                        <div class="text-muted small mt-2">{{ __('demandes.history_form.button.fr') }}</div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

