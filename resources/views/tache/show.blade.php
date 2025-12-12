<x-app-layout>

<div class="container py-4">

    <a href="{{ route('tache.index') }}" class="btn btn-secondary mb-3">← Retour</a>

    <h2 class="fw-bold mb-4">Détails de la tâche</h2>

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

            <p><strong>Description :</strong></p>
            <p>{{ $tache->description }}</p>

            <hr>

            <p><strong>Utilisateurs assignés :</strong></p>

            @if($tache->realisateurs->count() > 0)

                <ul class="list-group">
                    @foreach ($tache->realisateurs as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $user->prenom }} {{ $user->nom }}</strong><br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>

                            <div class="text-end">
                                <span class="badge bg-primary">
                                    {{ $user->pivot->dateM ? $user->pivot->dateM->format('d/m/Y H:i') : 'Non renseigné' }}
                                </span>

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

</div>

</x-app-layout>
