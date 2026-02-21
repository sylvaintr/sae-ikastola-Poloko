<x-app-layout>
    <div class="container py-5 demande-history-create">
        <div class="mb-4">
            <a href="{{ route('tache.show', $tache) }}" class="text-decoration-none text-muted small">
                {{ __('taches.history_form.link', ['id' => '#' . $tache->idTache]) }}
            </a>
        </div>

        <div class="text-center mb-5">
            <p class="text-uppercase text-muted mb-1">{{ __('taches.history.section.history.eu') }}</p>
            <h1 class="fw-bold mb-1">{{ __('taches.history_form.heading.eu') }}</h1>
            <p class="text-muted mb-3">{{ __('taches.history_form.heading.fr') }}</p>
            <p class="text-muted mb-0">{{ __('taches.history_form.subtitle.eu') }}</p>
            <p class="text-muted small">{{ __('taches.history_form.subtitle.fr') }}</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tache.historique.store', $tache) }}" class="avancement-form">
                    @csrf
                    <div class="mb-4">
                        <label for="history-title" class="form-label fw-semibold">{{ __('taches.history_form.fields.title.eu') }} <small class="text-muted d-block">{{ __('taches.history_form.fields.title.fr') }}</small></label>
                        <input id="history-title" type="text" name="titre" class="form-control form-control-lg" value="{{ old('titre') }}" required>
                    </div>
                    <div class="mb-4">
                        <label for="history-description" class="form-label fw-semibold">{{ __('taches.history_form.fields.description.eu') }} <small class="text-muted d-block">{{ __('taches.history_form.fields.description.fr') }}</small></label>
                        <textarea id="history-description" name="description" rows="5" class="form-control form-control-lg">{{ old('description') }}</textarea>
                    </div>
                    <div class="text-center mt-5">
                        <button type="submit" class="btn demande-btn-primary px-5">
                            {{ __('taches.history_form.button.eu') }}
                        </button>
                        <div class="text-muted small mt-2">{{ __('taches.history_form.button.fr') }}</div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

