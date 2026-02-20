<x-app-layout>
    @php
        $recettes = $evenement->recettes ?? collect();
        $totalRecettes = $recettes->where('type', 'recette')->sum(fn($r) => $r->prix * ($r->quantite ?? 1));
        $totalDepensesPrev = $recettes->where('type', 'depense_previsionnelle')->sum(fn($r) => $r->prix * ($r->quantite ?? 1));
        $totalDepenses = $recettes->where('type', 'depense')->sum(fn($r) => $r->prix * ($r->quantite ?? 1));

        // Intégrer les dépenses des demandes
        $totalDepensesAvecDemandes = $totalDepenses + ($demandesDepenses ?? 0);
        $balance = $totalRecettes - $totalDepensesAvecDemandes;

        $typeLabels = [
            'recette' => __('evenements.type_recette'),
            'depense_previsionnelle' => __('evenements.type_depense_prev'),
            'depense' => __('evenements.type_depense'),
        ];
    @endphp

    <div class="container py-4 demande-page">
        {{-- Retour --}}
        <a href="{{ route('evenements.index') }}"
           class="text-decoration-none d-inline-flex align-items-center gap-2 mb-3 demande-link-primary">
            <i class="bi bi-arrow-left"></i>
            <span class="basque">{{ Lang::get('evenements.back_to_list', [], 'eus') }}</span>
            @if (Lang::getLocale() == 'fr')
                <span class="fr text-muted">/ {{ Lang::get('evenements.back_to_list') }}</span>
            @endif
        </a>

        {{-- En-tête --}}
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h1 class="fw-bold mb-1">{{ $evenement->titre }}</h1>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($evenement->start_at)->format('d F Y') }}</div>
            </div>
            <div class="d-flex flex-wrap gap-4 text-muted small align-items-center">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <strong>
                        <span class="basque">{{ Lang::get('evenements.roles', [], 'eus') }}</span>
                        @if (Lang::getLocale() == 'fr')
                            <span class="fr">/ {{ Lang::get('evenements.roles') }}</span>
                        @endif
                        :
                    </strong>
                    @if ($evenement->roles->count())
                        @foreach ($evenement->roles as $role)
                            <span class="badge bg-warning-subtle text-warning">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    @else
                        <span class="badge bg-success-subtle text-success">
                            <span class="basque">{{ Lang::get('evenements.cible_all', [], 'eus') }}</span>
                            @if (Lang::getLocale() == 'fr')
                                <span class="fr">/ {{ Lang::get('evenements.cible_all') }}</span>
                            @endif
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <p class="mb-1 fw-semibold">
                <span class="basque">{{ Lang::get('evenements.description', [], 'eus') }}</span>
                @if (Lang::getLocale() == 'fr')
                    <span class="fr text-muted">/ {{ Lang::get('evenements.description') }}</span>
                @endif
            </p>
            <p class="text-muted">{{ $evenement->description ?: __('evenements.no_description_provided') }}</p>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <button class="btn demande-btn-primary text-white" data-bs-toggle="modal" data-bs-target="#modalRecette">
                <i class="bi bi-plus-circle"></i>
                <span class="basque">{{ Lang::get('evenements.add_recette', [], 'eus') }}</span>
                @if (Lang::getLocale() == 'fr')
                    <span class="fr">/ {{ Lang::get('evenements.add_recette') }}</span>
                @endif
            </button>
            <a href="{{ route('evenements.export.csv', $evenement) }}" class="btn demande-btn-outline">
                <i class="bi bi-download"></i>
                <span class="basque">{{ Lang::get('evenements.export_btn', [], 'eus') }}</span>
                @if (Lang::getLocale() == 'fr')
                    <span class="fr">/ {{ Lang::get('evenements.export_btn') }}</span>
                @endif
            </a>
            <a href="{{ route('demandes.index', ['evenement' => $evenement->idEvenement]) }}" class="btn demande-btn-outline">
                <i class="bi bi-list-task"></i>
                <span class="basque">{{ Lang::get('evenements.view_demandes', [], 'eus') }}</span>
                @if (Lang::getLocale() == 'fr')
                    <span class="fr">/ {{ Lang::get('evenements.view_demandes') }}</span>
                @endif
            </a>
        </div>

        {{-- Comptabilité / Recettes --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">
                    <span class="basque">{{ Lang::get('evenements.accounting', [], 'eus') }}</span>
                    @if (Lang::getLocale() == 'fr')
                        <span class="fr text-muted">/ {{ Lang::get('evenements.accounting') }}</span>
                    @endif
                </h5>

                <div class="table-responsive">
                    <table class="table align-middle demande-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">{{ Lang::get('evenements.type', [], 'eus') }}</span>
                                        <span class="fr">{{ Lang::get('evenements.type') }}</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">{{ Lang::get('evenements.amount', [], 'eus') }}</span>
                                        <span class="fr">{{ Lang::get('evenements.amount') }}</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">{{ Lang::get('evenements.quantity', [], 'eus') }}</span>
                                        <span class="fr">{{ Lang::get('evenements.quantity') }}</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="demande-header-label">
                                        <span class="basque">{{ Lang::get('evenements.description', [], 'eus') }}</span>
                                        <span class="fr">{{ Lang::get('evenements.description') }}</span>
                                    </div>
                                </th>
                                <th class="text-end">
                                    <div class="demande-header-label">
                                        <span class="basque">{{ Lang::get('evenements.actions', [], 'eus') }}</span>
                                        <span class="fr">{{ Lang::get('evenements.actions') }}</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recettes as $recette)
                                <tr>
                                    <td>{{ $typeLabels[$recette->type] ?? ucfirst($recette->type ?? __('evenements.type_recette')) }}</td>
                                    <td>{{ number_format((float) $recette->prix, 2, ',', ' ') }} &euro;</td>
                                    <td>{{ $recette->quantite }}</td>
                                    <td class="text-muted">{{ $recette->description }}</td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-3">
                                            <a href="{{ route('recettes.edit', $recette) }}" class="admin-action-link" title="{{ __('evenements.action_edit') }}">
                                                 <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('recettes.destroy', $recette) }}" method="POST" class="d-inline delete-recette-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="admin-action-link btn btn-link p-0 m-0 delete-recette-btn" title="{{ __('evenements.action_delete') }}">
                                                   <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('evenements.no_recettes') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Totaux collés en bas --}}
                <div class="position-sticky bottom-0 bg-white border-top pt-3 mt-4">
                    <div class="d-flex flex-column gap-2 fw-semibold small">
                        <div class="d-flex flex-column flex-lg-row flex-wrap gap-3">
                            <div>
                                <span class="basque">{{ Lang::get('evenements.total_depenses_prev', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.total_depenses_prev') }}</span>
                                @endif
                                : {{ number_format($totalDepensesPrev, 2, ',', ' ') }} &euro;
                            </div>
                            <div>
                                <span class="basque">{{ Lang::get('evenements.total_depenses', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.total_depenses') }}</span>
                                @endif
                                : {{ number_format($totalDepenses, 2, ',', ' ') }} &euro;
                            </div>
                            @if ($demandesDepenses > 0)
                                <div class="text-primary">
                                    <span class="basque">+ Demandes</span>
                                    @if (Lang::getLocale() == 'fr')
                                        <span class="fr text-muted">/ Demandes</span>
                                    @endif
                                    : {{ number_format($demandesDepenses, 2, ',', ' ') }} &euro;
                                </div>
                            @endif
                            <div>
                                <span class="basque">{{ Lang::get('evenements.total_recettes', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.total_recettes') }}</span>
                                @endif
                                : {{ number_format($totalRecettes, 2, ',', ' ') }} &euro;
                            </div>
                        </div>
                        @if ($demandesDepenses > 0)
                            <div class="border-top pt-2 mt-2">
                                <span class="basque">Total dépenses (avec demandes)</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ Total dépenses (avec demandes)</span>
                                @endif
                                : <strong>{{ number_format($totalDepensesAvecDemandes, 2, ',', ' ') }} &euro;</strong>
                            </div>
                            <div class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                <span class="basque">Balance</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ Solde</span>
                                @endif
                                : <strong>{{ number_format($balance, 2, ',', ' ') }} &euro;</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Section : Demandes liées --}}
        @if($evenement->demandes->count() > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <span class="basque">Demandes liées</span>
                        @if (Lang::getLocale() == 'fr')
                            <span class="fr text-muted">/ Demandes liées</span>
                        @endif
                        <span class="badge bg-primary-subtle text-primary ms-2">{{ $evenement->demandes->count() }}</span>
                    </h5>

                    <div class="table-responsive">
                        <table class="table align-middle demande-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>
                                        <div class="demande-header-label">
                                            <span class="basque">Izenburua</span>
                                            <span class="fr">Titre</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="demande-header-label">
                                            <span class="basque">Larrialdia</span>
                                            <span class="fr">Urgence</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="demande-header-label">
                                            <span class="basque">Egoera</span>
                                            <span class="fr">État</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="demande-header-label">
                                            <span class="basque">Gastuak</span>
                                            <span class="fr">Dépenses</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="demande-header-label">
                                            <span class="basque">Batzordeak</span>
                                            <span class="fr">Commissions</span>
                                        </div>
                                    </th>
                                    <th class="text-end">
                                        <div class="demande-header-label">
                                            <span class="basque">Ekintzak</span>
                                            <span class="fr">Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($evenement->demandes as $demande)
                                    @php
                                        $totalDepenseDemande = $demande->historiques->sum('depense');
                                    @endphp
                                    <tr>
                                        <td><span class="badge bg-secondary-subtle text-secondary">#{{ $demande->idTache }}</span></td>
                                        <td>{{ $demande->titre }}</td>
                                        <td>
                                            <span class="badge
                                                @if($demande->urgence === 'Élevée') bg-danger-subtle text-danger
                                                @elseif($demande->urgence === 'Moyenne') bg-warning-subtle text-warning
                                                @else bg-success-subtle text-success
                                                @endif">
                                                {{ $demande->urgence }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge
                                                @if($demande->etat === 'Terminé') bg-success-subtle text-success
                                                @elseif($demande->etat === 'En cours') bg-primary-subtle text-primary
                                                @else bg-secondary-subtle text-secondary
                                                @endif">
                                                {{ $demande->etat }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($totalDepenseDemande > 0)
                                                <span class="fw-semibold">{{ number_format($totalDepenseDemande, 2, ',', ' ') }} &euro;</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($demande->roles->count() > 0)
                                                @foreach($demande->roles as $role)
                                                    <span class="badge bg-orange-soft text-dark">{{ $role->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('demandes.show', $demande) }}" class="admin-action-link" title="Voir">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal : Ajouter une opération comptable --}}
    <div class="modal fade" id="modalRecette" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4">
                    <h2 class="modal-title fw-bold">
                        <span class="basque">{{ Lang::get('evenements.add_recette', [], 'eus') }}</span>
                        @if (Lang::getLocale() == 'fr')
                            <span class="fr text-muted">/ {{ Lang::get('evenements.add_recette') }}</span>
                        @endif
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('evenements.calendar_close') }}"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form action="{{ route('recettes.store', $evenement) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="recette-description" class="form-label fw-semibold mb-2">
                                <span class="basque">{{ Lang::get('evenements.description', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.description') }}</span>
                                @endif
                            </label>
                            <textarea id="recette-description" name="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="recette-type" class="form-label fw-semibold mb-2">
                                    <span class="basque">{{ Lang::get('evenements.type', [], 'eus') }}</span>
                                    @if (Lang::getLocale() == 'fr')
                                        <span class="fr text-muted">/ {{ Lang::get('evenements.type') }}</span>
                                    @endif
                                </label>
                                <select id="recette-type" name="type" class="form-select" required>
                                    <option value="recette">{{ __('evenements.type_recette') }}</option>
                                    <option value="depense_previsionnelle">{{ __('evenements.type_depense_prev') }}</option>
                                    <option value="depense">{{ __('evenements.type_depense') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="recette-prix" class="form-label fw-semibold mb-2">
                                    <span class="basque">{{ Lang::get('evenements.amount', [], 'eus') }}</span>
                                    @if (Lang::getLocale() == 'fr')
                                        <span class="fr text-muted">/ {{ Lang::get('evenements.amount') }}</span>
                                    @endif
                                    (&euro;)
                                </label>
                                <input id="recette-prix" type="number" name="prix" step="0.01" min="0" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="recette-quantite" class="form-label fw-semibold mb-2">
                                    <span class="basque">{{ Lang::get('evenements.quantity', [], 'eus') }}</span>
                                    @if (Lang::getLocale() == 'fr')
                                        <span class="fr text-muted">/ {{ Lang::get('evenements.quantity') }}</span>
                                    @endif
                                </label>
                                <input id="recette-quantite" type="text" name="quantite" class="form-control" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn demande-btn-outline" data-bs-dismiss="modal">
                                {{ __('evenements.cancel') }}
                            </button>
                            <button type="submit" class="btn demande-btn-primary text-white">
                                {{ __('evenements.add_recette') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal confirmation suppression recette --}}
    <div class="modal fade" id="deleteRecetteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('evenements.delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('evenements.calendar_close') }}"></button>
                </div>
                <div class="modal-body">
                    {{ __('evenements.confirm_delete_recette') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn demande-btn-outline cancel-delete" data-bs-dismiss="modal">{{ __('evenements.cancel') }}</button>
                    <button type="button" class="btn btn-danger confirm-delete">{{ __('evenements.delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Suppression recette avec modal
            const modal = document.getElementById('deleteRecetteModal');
            if (!modal) { return; }

            const bootstrapModal = new bootstrap.Modal(modal);
            let currentForm = null;

            document.querySelectorAll('.delete-recette-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    currentForm = this.closest('.delete-recette-form');
                    bootstrapModal.show();
                });
            });

            const cancelBtn = modal.querySelector('.cancel-delete');
            const confirmBtn = modal.querySelector('.confirm-delete');

            cancelBtn?.addEventListener('click', () => {
                bootstrapModal.hide();
                currentForm = null;
            });

            confirmBtn?.addEventListener('click', () => {
                if (currentForm) {
                    currentForm.submit();
                    bootstrapModal.hide();
                    currentForm = null;
                }
            });
        });
    </script>
</x-app-layout>
