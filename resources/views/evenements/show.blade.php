<x-app-layout>
    <div class="container py-4">

        {{-- Retour --}}
        <a href="{{ route('evenements.index') }}"
           class="text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
            <i class="bi bi-arrow-left"></i>
            Retour
        </a>

        {{-- EN-TÊTE EVENEMENT --}}
        <div class="mb-4">
            <h2 class="fw-bold">{{ $evenement->titre }}</h2>

            <div class="d-flex flex-wrap gap-4 text-muted small mt-2">
                <div>
                    <strong>Date :</strong>
                    {{ \Carbon\Carbon::parse($evenement->dateE)->format('d F Y') }}
                </div>
                <div>
                    <strong>Public :</strong>
                    {{ $evenement->roles->count() ? 'Restreint' : 'Tous' }}
                </div>
                <div>
                    <strong>Récurrence :</strong>
                    Annuelle
                </div>
            </div>
        </div>

        {{-- DESCRIPTION --}}
        <div class="border-top pt-3 mb-4">
            <p class="mb-1 fw-semibold">Description</p>
            <p class="text-muted">
                {{ $evenement->description ?: 'Aucune description fournie.' }}
            </p>
        </div>

        {{-- ACTIONS --}}
        <div class="d-flex gap-2 mb-4">
            <a href="{{ route('evenements.edit', $evenement) }}"
               class="btn btn-warning text-white">
                <i class="bi bi-pencil"></i> Modifier
            </a>

            <button class="btn btn-warning text-white">
                Ajouter une recette
            </button>
        </div>

        {{-- COMPTABILITÉ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <h5 class="fw-bold mb-3">Comptabilité</h5>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>

                        {{-- RECETTES --}}
                        @foreach($evenement->recettes as $recette)
                            <tr>
                                <td>Recette prévue</td>
                                <td>{{ number_format($recette->montant, 0, ',', ' ') }} €</td>
                                <td>{{ $recette->type ?? '—' }}</td>
                                <td>
                                    <span class="text-muted">En attente</span>
                                </td>
                                <td class="text-end">
                                    <i class="bi bi-pencil mx-1"></i>
                                    <i class="bi bi-x-lg mx-1 text-danger"></i>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>

                {{-- TOTAUX --}}
                <div class="d-flex justify-content-end mt-4 small fw-semibold">
                    <div>
                        Total des recettes :
                        {{ number_format($evenement->recettes->sum('montant'), 0, ',', ' ') }} €
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
