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
            @endif
        </section>

        <section>
            <h5 class="fw-bold mb-3">Historikoa <small class="text-muted d-block">Historique</small></h5>
            @if ($historiques->isEmpty())
                <p class="text-muted">La chronologie des actions apparaîtra ici.</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle demande-history-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">Egoera</span>
                                        <span class="fr">Statut</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">Data</span>
                                        <span class="fr">Date</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">Izenburua</span>
                                        <span class="fr">Titre</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">Esleipena</span>
                                        <span class="fr">Assignation</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-cell">
                                        <div class="demande-header-label">
                                            <span class="basque">Gastuak</span>
                                            <span class="fr">Dépenses</span>
                                        </div>
                                        <i class="bi bi-caret-down"></i>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="demande-header-label">
                                        <span class="basque">Ekintzak</span>
                                        <span class="fr">Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historiques as $item)
                                <tr>
                                    <td>{{ $item->statut }}</td>
                                    <td>{{ optional($item->date_evenement)->format('d-m-Y') ?? '—' }}</td>
                                    <td>{{ $item->titre }}</td>
                                    <td>{{ $item->responsable ?? '—' }}</td>
                                    <td>{{ $item->depense ? number_format($item->depense, 2, ',', ' ') . ' €' : '—' }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn demande-action-btn history-view-btn" data-description="{{ $item->description ?? '—' }}" data-titre="{{ $item->titre }}" data-date="{{ optional($item->date_evenement)->format('d/m/Y') ?? '—' }}" data-depense="{{ $item->depense ? number_format($item->depense, 2, ',', ' ') . ' €' : '—' }}" title="Voir la description">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
            <div class="d-flex flex-column flex-md-row gap-4 text-muted fw-semibold">
                <div>Dépense prévisionnelle : <span class="text-dark">{{ $demande->montantP ? number_format($demande->montantP, 0, ',', ' ') . ' €' : '—' }}</span></div>
                <div>Dépense réelle : <span class="text-dark">{{ $totalDepense ? number_format($totalDepense, 0, ',', ' ') . ' €' : '—' }}</span></div>
            </div>
            <div class="text-center">
                <a href="{{ route('demandes.historique.create', $demande) }}" class="btn demande-btn-primary px-4">
                    Gehitu aurrerapena
                </a>
                <div class="text-muted small">Ajouter un avancement</div>
            </div>
        </div>
    </div>
</x-app-layout>

<div class="modal fade" id="viewHistoryModal" tabindex="-1" aria-labelledby="viewHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewHistoryModalLabel">Détail de l'avancement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1 text-muted small">Titre</p>
                <p class="fw-semibold" data-history-titre>—</p>
                <p class="mb-1 text-muted small">Date</p>
                <p data-history-date>—</p>
                <p class="mb-1 text-muted small">Dépense</p>
                <p data-history-depense>—</p>
                <p class="mb-1 text-muted small">Description</p>
                <p data-history-description>—</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('viewHistoryModal');
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        const titreEl = modalEl.querySelector('[data-history-titre]');
        const dateEl = modalEl.querySelector('[data-history-date]');
        const depenseEl = modalEl.querySelector('[data-history-depense]');
        const descEl = modalEl.querySelector('[data-history-description]');

        document.querySelectorAll('.history-view-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                titreEl.textContent = this.getAttribute('data-titre') || '—';
                dateEl.textContent = this.getAttribute('data-date') || '—';
                depenseEl.textContent = this.getAttribute('data-depense') || '—';
                descEl.textContent = this.getAttribute('data-description') || '—';
                modal.show();
            });
        });
    });
</script>

