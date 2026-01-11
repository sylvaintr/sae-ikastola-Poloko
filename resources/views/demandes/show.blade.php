<x-app-layout>
    <div class="container py-4 demande-show-page">
        <div class="mb-4">
            <a href="{{ route('demandes.index') }}" class="d-inline-flex align-items-center gap-2 fw-semibold demande-link-primary">
                <i class="bi bi-arrow-left"></i>
                <span class="d-flex flex-column lh-sm">
                    <span>{{ __('demandes.show.back.eu') }}</span>
                    <small class="text-muted">{{ __('demandes.show.back.fr') }}</small>
                </span>
            </a>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 gap-md-4 mb-4 mb-md-5">
            <div>
                <h1 class="fw-bold mb-1">{{ $demande->titre }}</h1>
                <p class="text-uppercase text-muted small mb-2">{{ $demande->type ?? __('demandes.show.type_default') }}</p>
                <p class="text-muted mb-0">
                    {{ __('demandes.show.reported_by', ['name' => $metadata['reporter'], 'date' => $metadata['report_date']]) }}
                </p>
            </div>
            <div class="d-flex flex-column align-items-md-end align-items-center">
                <div class="text-uppercase text-muted small fw-semibold text-center">
                    {{ __('demandes.status.label.eu') }}
                    <small class="text-muted d-block">{{ __('demandes.status.label.fr') }}</small>
                </div>
                <div class="demande-status-pill mt-1">{{ $demande->etat ?? 'Abian' }}</div>
            </div>
        </div>

        <section class="mb-4">
            <h5 class="fw-bold mb-3">{{ __('demandes.history.section.description.eu') }} <small class="text-muted d-block">{{ __('demandes.history.section.description.fr') }}</small></h5>
            <p class="mb-0">{{ $demande->description }}</p>
        </section>

        <section class="mb-5">
            <h5 class="fw-bold mb-3">{{ __('demandes.history.section.photo.eu') }} <small class="text-muted d-block">{{ __('demandes.history.section.photo.fr') }}</small></h5>
            @if (count($photos))
                <div class="row g-3">
                    @foreach ($photos as $photo)
                        <div class="col-12 col-md-6">
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
            <h5 class="fw-bold mb-3">{{ __('demandes.history.section.history.eu') }} <small class="text-muted d-block">{{ __('demandes.history.section.history.fr') }}</small></h5>
            @if ($historiques->isEmpty())
                <p class="text-muted">{{ __('demandes.history.empty') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle demande-history-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">{{ __('demandes.history.columns.status.eu') }}</span>
                                        <span class="fr">{{ __('demandes.history.columns.status.fr') }}</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">{{ __('demandes.history.columns.date.eu') }}</span>
                                        <span class="fr">{{ __('demandes.history.columns.date.fr') }}</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">{{ __('demandes.history.columns.title.eu') }}</span>
                                        <span class="fr">{{ __('demandes.history.columns.title.fr') }}</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">{{ __('demandes.history.columns.assignment.eu') }}</span>
                                        <span class="fr">{{ __('demandes.history.columns.assignment.fr') }}</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-cell">
                                        <div class="demande-header-label">
                                            <span class="basque">{{ __('demandes.history.columns.expense.eu') }}</span>
                                            <span class="fr">{{ __('demandes.history.columns.expense.fr') }}</span>
                                        </div>
                                        <i class="bi bi-caret-down"></i>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="demande-header-label">
                                        <span class="basque">{{ __('demandes.history.columns.actions.eu') }}</span>
                                        <span class="fr">{{ __('demandes.history.columns.actions.fr') }}</span>
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
                                        <button type="button" class="btn demande-action-btn history-view-btn"
                                            data-description="{{ $item->description ?? '—' }}"
                                            data-titre="{{ $item->titre }}"
                                            data-date="{{ optional($item->date_evenement)->format('d/m/Y') ?? '—' }}"
                                            data-depense="{{ $item->depense ? number_format($item->depense, 2, ',', ' ') . ' €' : '—' }}"
                                            title="{{ __('demandes.actions.view') }}">
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
                <div>{{ __('demandes.history.planned') }} : <span class="text-dark">{{ $demande->montantP ? number_format($demande->montantP, 0, ',', ' ') . ' €' : '—' }}</span></div>
                <div>{{ __('demandes.history.real') }} : <span class="text-dark">{{ $totalDepense ? number_format($totalDepense, 0, ',', ' ') . ' €' : '—' }}</span></div>
            </div>
        @if ($demande->etat !== 'Terminé')
            <div class="text-center">
                <a href="{{ route('demandes.historique.create', $demande) }}" class="btn demande-btn-primary px-4">
                    {{ __('demandes.history.button.eu') }}
                </a>
                <div class="text-muted small">{{ __('demandes.history.button.fr') }}</div>
            </div>
        @endif
        </div>
    </div>
</x-app-layout>

<div class="modal fade" id="viewHistoryModal" tabindex="-1" aria-labelledby="viewHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewHistoryModalLabel">{{ __('demandes.modals.history_view.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('demandes.actions.close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1 text-muted small">{{ __('demandes.modals.history_view.fields.title') }}</p>
                <p class="fw-semibold" data-history-titre>—</p>
                <p class="mb-1 text-muted small">{{ __('demandes.modals.history_view.fields.date') }}</p>
                <p data-history-date>—</p>
                <p class="mb-1 text-muted small">{{ __('demandes.modals.history_view.fields.expense') }}</p>
                <p data-history-depense>—</p>
                <p class="mb-1 text-muted small">{{ __('demandes.modals.history_view.fields.description') }}</p>
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

