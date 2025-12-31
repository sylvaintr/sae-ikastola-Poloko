<x-app-layout>
    @php
        $recettes = $evenement->recettes ?? collect();
        $totalRecettes = $recettes->where('type', 'recette')->sum('prix');
        $totalDepensesPrev = $recettes->where('type', 'depense_previsionnelle')->sum('prix');
        $totalDepenses = $recettes->where('type', 'depense')->sum('prix');

        $typeLabels = [
            'recette' => 'Recette',
            'depense_previsionnelle' => 'Dépense prévisionnelle',
            'depense' => 'Dépense',
        ];
    @endphp

    <div class="container py-4">
        {{-- Retour --}}
        <a href="{{ route('evenements.index') }}"
           class="text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
            <i class="bi bi-arrow-left"></i>
            Retour aux événements
        </a>

        {{-- En-tête --}}
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h1 class="fw-bold mb-1">{{ $evenement->titre }}</h1>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($evenement->dateE)->format('d F Y') }}</div>
            </div>
            <div class="d-flex flex-wrap gap-4 text-muted small">
                <div><strong>Cible :</strong> {{ $evenement->roles->count() ? 'Restreint' : 'Tous' }}</div>
                <div><strong>Récurrence :</strong> Annuelle</div>
            </div>
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <p class="mb-1 fw-semibold">Description</p>
            <p class="text-muted">{{ $evenement->description ?: 'Aucune description fournie.' }}</p>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('evenements.edit', $evenement) }}" class="btn btn-warning text-white">
                <i class="bi bi-pencil"></i> Modifier l'événement
            </a>
            <button class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#modalRecette">
                <i class="bi bi-plus-circle"></i> Ajouter une recette
            </button>
        </div>

        {{-- Comptabilité / Recettes --}}
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
                            @forelse($recettes as $recette)
                                <tr>
                                    <td>{{ $typeLabels[$recette->type] ?? ucfirst($recette->type ?? 'Recette') }}</td>
                                    <td>{{ number_format((float) $recette->prix, 2, ',', ' ') }} €</td>
                                    <td class="text-muted">{{ $recette->description }}</td>
                                    <td><span class="text-muted">En attente</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('recettes.edit', $recette) }}" class="text-decoration-none me-2" title="Modifier">
                                             <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('recettes.destroy', $recette) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette recette ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link p-0 m-0 text-danger" title="Supprimer">
                                               <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Aucune recette pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Totaux collés en bas --}}
                <div class="position-sticky bottom-0 bg-white border-top pt-3 mt-4">
                    <div class="d-flex flex-column flex-lg-row flex-wrap gap-3 fw-semibold small">
                        <div>Total des dépenses prévisionnelles : {{ number_format($totalDepensesPrev, 2, ',', ' ') }} €</div>
                        <div>Total des dépenses : {{ number_format($totalDepenses, 2, ',', ' ') }} €</div>
                        <div>Total des recettes : {{ number_format($totalRecettes, 2, ',', ' ') }} €</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal : Ajouter une recette --}}
    <div class="modal fade" id="modalRecette" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                <div class="modal-header border-0 px-4 pt-4">
                    <h2 class="modal-title fw-bold">Ajouter une recette</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form action="{{ route('recettes.store', $evenement) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-2">Description</label>
                            <textarea name="description" class="form-control" rows="3" style="border-radius: 8px;" required></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-2">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="recette">Recette</option>
                                    <option value="depense_previsionnelle">Dépense prévisionnelle</option>
                                    <option value="depense">Dépense</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-2">Montant (€)</label>
                                <input type="number" name="prix" step="0.01" min="0" class="form-control" style="border-radius: 8px;" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-2">Quantité</label>
                                <input type="text" name="quantite" class="form-control" style="border-radius: 8px;" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn border-0 fw-bold px-4 py-2" style="background-color: #f39c12; color: white; border-radius: 8px;">
                                Ajouter une recette
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
