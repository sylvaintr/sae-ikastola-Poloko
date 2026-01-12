<x-app-layout>
    <div class="container py-4 demande-show-page">
        <div class="mb-4">
            <a href="{{ route('tache.index') }}" class="d-inline-flex align-items-center gap-2 fw-semibold demande-link-primary">
                <i class="bi bi-arrow-left"></i>
                <span class="d-flex flex-column lh-sm">
                    <span>Itzuli zereginetara</span>
                    <small class="text-muted">Retour aux tâches</small>
                </span>
            </a>
        </div>

        @php
            switch ($tache->etat) {
                case 'done':
                    $etatBadgeFR = 'Terminé';
                    $etatBadgeEU = 'Amiatu';
                    break;
                case 'doing':
                    $etatBadgeFR = 'En cours';
                    $etatBadgeEU = 'Abian';
                    break;
                case 'todo':
                    $etatBadgeFR = 'En attente';
                    $etatBadgeEU = 'Zain';
                    break;
                default:
                    $etatBadgeFR = e($tache->etat);
                    $etatBadgeEU = e($tache->etat);
            }

            switch ($tache->type) {
                case 'low':
                    $typeBadgeFR = 'Faible';
                    $typeBadgeEU = 'Baxua';
                    break;
                case 'medium':
                    $typeBadgeFR = 'Moyenne';
                    $typeBadgeEU = 'Ertaina';
                    break;
                case 'high':
                    $typeBadgeFR = 'Élevée';
                    $typeBadgeEU = 'Altua';
                    break;
                default:
                    $typeBadgeFR = e($tache->type);
                    $typeBadgeEU = e($tache->type);
            }
        @endphp

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4 mb-5">
            <div>
                <h1 class="fw-bold mb-1">{{ $tache->titre }}</h1>
                <div class="text-muted small mb-2">
                    <p class="text-uppercase mb-0">Lehentasuna : {{ $typeBadgeEU }}</p>
                    <small class="text-muted d-block">Priorité : {{ $typeBadgeFR }}</small>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="text-uppercase text-muted small fw-semibold text-center me-3">
                    {{ __('taches.status.label.eu') }} :
                    <small class="text-muted d-block">{{ __('taches.status.label.fr') }} :</small>
                </div>
                <div>
                    <div class="demande-status-pill mt-1">
                        {{ $etatBadgeEU }}
                    </div>
                    <p class="text-muted small d-block text-center">{{ $etatBadgeFR }}</p>
                </div>
            </div>
        </div>

            <section class="mb-4">
                <h5 class="fw-bold mb-0">Deskribapena</h5>
                <p class="text-muted small mt-0">Description</p>
                <p>{{ $tache->description }}</p>
            </section>

            <section class="mb-5">
                <h5 class="fw-bold mb-0">Esleitutako erabiltzaileak</h5>
                <p class="text-muted small mt-0">Utilisateurs assignés</p>
            
                @if($tache->realisateurs->count() > 0)

                    <ul class="list-group">
                        @foreach ($tache->realisateurs as $user)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $user->prenom }} {{ $user->nom }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>

                                <div class="text-end">
                                    {{ $user->pivot->dateM ? $user->pivot->dateM->format('d/m/Y') : 'Non renseigné' }}

                                    @if($user->pivot->description)
                                        <div class="mt-1 text-muted small">
                                            "{{ $user->pivot->description }}"
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                @else
                    <p class="text-muted">Aucun utilisateur assigné.</p>
                @endif
            </section>

            <section class="mb-4">
                <h5 class="fw-bold mb-0">Historikoa</h5>
                <p class="text-muted small mt-0">Historique</p>

                <div class="table-responsive">
                    <table class="table table-borderless datatable-historique w-100">
                        <thead>
                            <tr>
                                <th>Égoera<br><small class="text-muted">Statut</small></th>
                                <th>Data<br><small class="text-muted">Date</small></th>
                                <th>Izenburua<br><small class="text-muted">Titre</small></th>
                                <th>Esleipena<br><small class="text-muted">Assignation</small></th>
                                <th>Ekintzak<br><small class="text-muted">Actions</small></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($historique as $item)
                                <tr>
                                    <td class="fw-semibold">
                                        @switch($item->statut)
                                            @case('created') Tâche créée @break
                                            @case('doing') Avancement @break
                                            @case('done') Effectué @break
                                            @default {{ $item->statut }}
                                        @endswitch
                                    </td>

                                    <td>{{ $item->date_evenement?->format('d-m-Y') }}</td>

                                    <td class="text-muted">
                                        {{ $item->titre ?? '—' }}
                                    </td>

                                    <td class="text-muted">
                                        {{ $item->responsable ?? '—' }}
                                    </td>

                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="demande-action-btn btn-show-historique"
                                            title="Voir"
                                            data-bs-toggle="modal"
                                            data-bs-target="#historiqueModal"
                                            data-statut="{{ $item->statut }}"
                                            data-date="{{ $item->date_evenement?->format('d/m/Y') }}"
                                            data-titre="{{ $item->titre }}"
                                            data-responsable="{{ $item->responsable }}"
                                            data-description="{{ $item->description }}"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>


            <div class="d-flex flex-column flex-row align-items-end gap-3">
                <div class="d-flex flex-column align-items-start">
                    <a href="{{ route('tache.historique.create', $tache) }}" class="admin-add-button">
                        Gehitu aurrerapena
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle">Ajouter un avancement</p>
                </div>
            </div>
    </div>

    @push('scripts')
    <script>
    function formatStatut(statut) {
        switch (statut) {
            case 'created': return 'Tâche créée';
            case 'doing': return 'Avancement';
            case 'done': return 'Effectué';
            default: return statut;
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        $('.datatable-historique').DataTable({
            paging: true,
            pageLength: 5,
            lengthChange: false,
            searching: false,
            info: true,
            ordering: true,
            order: [[1, 'asc']], // date
            language: {
                info: 'Affichage de _START_ à _END_ sur _TOTAL_ résultat(s)',
                infoEmpty: 'La chronologie des actions apparaîtra ici.',
                paginate: {
                    first: '‹‹',
                    previous: '‹',
                    next: '›',
                    last: '››'
                }
            },
            columns: [
                { data: 'statut' },
                { data: 'date_evenement' },
                { data: 'titre' },
                { data: 'responsable' },
                { data: 'action', orderable: false },
            ],
            dom: '<"d-flex justify-content-end mb-2"p>rt<"d-flex justify-content-end mt-2"i>',
        });
    });
    document.addEventListener('DOMContentLoaded', function () {

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-show-historique');
            if (!btn) return;

            const statut = btn.dataset.statut;
            const date = btn.dataset.date;
            const titre = btn.dataset.titre || '—';
            const responsable = btn.dataset.responsable || '—';
            const description = btn.dataset.description || '—';

            document.getElementById('modal-statut').textContent = formatStatut(statut);
            document.getElementById('modal-date').textContent = date;
            document.getElementById('modal-titre').textContent = titre;
            document.getElementById('modal-responsable').textContent = responsable;
            document.getElementById('modal-description').textContent = description;
        });

    });
</script>

    @endpush

<div class="modal fade" id="historiqueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Détail de l’avancement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <h5 class="fw-bold mb-3" id="modal-titre"></h5>
                <div id="modal-description"></div>
            </div>

            <div class="modal-footer">
                <dl class="row mb-3">
                    <dt class="col-sm-4">Statut :</dt>
                    <dd class="col-sm-8" id="modal-statut"></dd>

                    <dt class="col-sm-4">Date :</dt>
                    <dd class="col-sm-8" id="modal-date"></dd>

                    <dt class="col-sm-4">Responsable :</dt>
                    <dd class="col-sm-8" id="modal-responsable"></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

</x-app-layout>
