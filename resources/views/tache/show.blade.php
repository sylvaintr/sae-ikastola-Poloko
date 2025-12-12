<x-app-layout>

<div class="container py-4">

    <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-4 mb-5">
        <div>
            <h2 class="fw-bold display-4 mb-1" style="font-size: 2rem;">Zereginaren xehetasunak</h2>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Détails de la tâche</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <h3 class="fw-bold">{{ $tache->titre }}</h3>

            @php
                switch ($tache->etat) {
                    case 'done':
                        $etatBadge = '<span class="badge bg-success px-2 py-1">Terminé</span>';
                        break;

                    case 'doing':
                        $etatBadge = '<span class="badge bg-warning text-dark px-2 py-1">En cours</span>';
                        break;

                    case 'todo':
                    default:
                        $etatBadge = '<span class="badge px-2 py-1" style="background-color:#fd7e14;">En attente</span>';
                        break;
                }

                switch ($tache->type) {
                    case 'low':
                        $typeBadge = '<span>Faible</span>';
                        break;
                    case 'medium':
                        $typeBadge = '<span>Moyenne</span>';
                        break;
                    case 'high':
                        $typeBadge = '<span>Élevée</span>';
                        break;
                    default:
                        $typeBadge = '<span>'.e($tache->type).'</span>';
                }
            @endphp

            <p>
                Priorité : {!! $typeBadge !!} — État : {!! $etatBadge !!}
            </p>

            <hr>

            <strong>Deskribapena :</strong>
            <p class="text-muted mt-0 admin-button-subtitle">Description</p>
            <p>{{ $tache->description }}</p>

            <hr>

            <strong>Esleitutako erabiltzaileak :</strong>
            <p class="text-muted mt-0 admin-button-subtitle">Utilisateurs assignés</p>

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

        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('tache.index') }}" class="admin-secondary-button" style="padding-block: 8px;">Itzuli</a>
        <p class="text-muted mt-0 admin-button-subtitle">Retour</p>
    </div>

</div>

</x-app-layout>
