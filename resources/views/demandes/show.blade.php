<x-app-layout>
    <div class="container py-4 demande-show-page">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4 mb-5">
            <div>
                <p class="text-uppercase text-muted small mb-1">{{ $demande->type ?? 'Gertakaria' }}</p>
                <h1 class="fw-bold mb-2">{{ $demande->titre }}</h1>
                <p class="text-muted mb-0">
                    {{ $metadata['reporter'] }} jakinarazia • {{ $metadata['report_date'] }}
                </p>
            </div>
            <div class="text-md-end">
                <div class="text-uppercase text-muted small">Egoera</div>
                <div class="demande-status-pill">{{ $demande->etat ?? 'Abian' }}</div>
            </div>
        </div>

        <section class="mb-4">
            <h5 class="fw-bold mb-3">Izenburua <small class="text-muted d-block">Titre</small></h5>
            <p class="mb-0">{{ $demande->description }}</p>
        </section>

        <section class="mb-5">
            <h5 class="fw-bold mb-3">Photo</h5>
            @if (count($photos))
                <div class="row g-3">
                    @foreach ($photos as $photo)
                        <div class="col-md-6">
                            <div class="demande-photo-card">
                                <img src="{{ $photo['url'] }}" alt="{{ $photo['nom'] }}" class="img-fluid w-100 rounded-3">
                                <div class="small text-muted mt-2">{{ $photo['nom'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">Aucune photo n’a été fournie pour cette demande.</p>
            @endif
        </section>

        <section class="mb-5">
            <h5 class="fw-bold mb-3">Historikoa <small class="text-muted d-block">Historique</small></h5>
            <p class="text-muted">La chronologie des actions apparaîtra ici.</p>
        </section>
    </div>
</x-app-layout>

