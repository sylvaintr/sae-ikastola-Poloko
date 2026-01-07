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
                    {{ __('demandes.status.label.eu') }} :
                    <small class="text-muted d-block">{{ __('demandes.status.label.fr') }} :</small>
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
    </div>

</x-app-layout>
