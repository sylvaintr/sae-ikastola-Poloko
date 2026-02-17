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
        
        @if (session('status'))
            @php
                $statusKey = session('status');
                $messageEu = __($statusKey . '.eu');
                $messageFr = __($statusKey . '.fr');
            @endphp
            <div id="demande-toast" class="demande-toast shadow-sm">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <div>
                            <span class="fw-semibold">{{ $messageEu }}</span>
                            <br>
                            <small class="text-muted">{{ $messageFr }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-sm" aria-label="{{ __('demandes.actions.close') }}"></button>
                </div>
            </div>
        @endif

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
                <div class="mb-2">
                    <p class="text-uppercase mb-1 fs-5 fw-bold">Lehentasuna : {{ $typeBadgeEU }}</p>
                    <p class="text-muted fs-6">Priorité : {{ $typeBadgeFR }}</p>
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
                            <li class="list-group-item">
                                <div>
                                    <strong>{{ $user->prenom }} {{ $user->nom }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
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

                                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>

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
                                            data-date="{{ $item->created_at?->format('d/m/Y H:i') }}"
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
            
            @php
                $roles = auth()->user()->getRoleNames();
                $isOnlyParent = $roles->count() === 1 && $roles->contains('parent');
            @endphp

            <div class="d-flex flex-row align-items-end justify-content-end gap-3">
                @if(!$isOnlyParent || auth()->user()->can('gerer-tache'))
                <div class="d-flex flex-column align-items-start">
                    <button
                        type="button"
                        class="btn demande-btn-outline px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteConfirmModal"
                    >
                        Ezabatu
                    </button>
                    <p class="text-muted mb-0 admin-button-subtitle">{{ __('taches.actions.delete') }}</p>
                </div>
                <div class="d-flex flex-column align-items-start">
                    <a
                        @if ($tache->etat !== 'done')
                        href="{{ route('tache.edit', $tache) }}"
                        @else
                        title="Tâche déjà terminée" style="opacity: 0.5; cursor:not-allowed;"
                        @endif
                        class="btn demande-btn-primary px-4"
                    >
                        Editatu
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle">{{ __('taches.actions.edit') }}</p>
                </div>
                @endif

                <div class="d-flex flex-column align-items-start">
                    <a
                        @if ($tache->etat !== 'done')
                        href="{{ route('tache.historique.create', $tache) }}"
                        @else
                        title="Tâche déjà terminée" style="opacity: 0.5; cursor:not-allowed;"
                        @endif
                        class="btn demande-btn-primary px-4"
                    >
                        {{ __('taches.history.button.eu') }}
                    </a>
                    <p class="text-muted mb-0 admin-button-subtitle">{{ __('taches.history.button.fr') }}</p>
                </div>
            </div>
    </div>

    <div class="modal fade" id="historiqueModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Détail de l’avancement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <h5 class="fw-bold mb-3" id="modal-titre">Titre de l'avancement</h5>
                    <div id="modal-description"></div>
                </div>

                <div class="modal-footer">
                    <dl class="row mb-3 w-100">
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

    {{-- MODALE CONFIRMATION / ERREUR --}}
    <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalTitle">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="confirmModalBody">
                    Êtes-vous sûr ?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="button" class="btn demande-btn-primary" id="confirmModalAction">
                        Confirmer
                    </button>
                </div>

            </div>
        </div>
    </div>


    {{-- MODALE CONFIRMATION SUPPRESSION --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la tâche</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Voulez-vous vraiment supprimer cette tâche ?
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <form method="POST" action="{{ route('tache.delete', $tache) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger">
                            Supprimer
                        </button>
                    </form>
                </div>

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
            const toast = document.getElementById('demande-toast');
            if (toast) {
                const closeBtn = toast.querySelector('.btn-close');
                const hideToast = () => {
                    toast.classList.add('hide');
                    setTimeout(() => toast.remove(), 250);
                };
                closeBtn?.addEventListener('click', hideToast);
                setTimeout(hideToast, 3200);
            }

            $('.datatable-historique').DataTable({
                paging: false,
                lengthChange: false,
                searching: false,
                info: false,
                ordering: true,
                order: [[1, 'asc']], // date
                columns: [
                    { data: 'statut' },
                    { data: 'date_evenement' },
                    { data: 'titre' },
                    { data: 'responsable' },
                    { data: 'action', orderable: false },
                ],
            });

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

            {{-- CSRF --}}
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let pendingAction = null;

            function openConfirmModal(title, body, onConfirm) {
                $('#confirmModalTitle').text(title);
                $('#confirmModalBody').text(body);
                pendingAction = onConfirm;

                new bootstrap.Modal(
                    document.getElementById('confirmActionModal')
                ).show();
            }

            $('#confirmModalAction').on('click', function () {
                if (pendingAction) pendingAction();
                pendingAction = null;

                bootstrap.Modal.getInstance(
                    document.getElementById('confirmActionModal')
                ).hide();
            });

            {{-- DELETE --}}
            let deleteUrl = null;

            // clic sur icône supprimer
            $(document).on('click', '.delete-tache', function (e) {
                e.preventDefault();
                deleteUrl = $(this).data('url');

                new bootstrap.Modal(
                    document.getElementById('deleteConfirmModal')
                ).show();
            });

            // confirmation suppression
            $('#confirmDeleteBtn').on('click', function () {

                if (!deleteUrl) return;

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    success: function () {
                        window.location.href = "{{ route('tache.index') }}";
                    },
                    error: function () {
                        alert('Erreur lors de la suppression.');
                    }
                });
            });
        });
    </script>

    @endpush

</x-app-layout>
