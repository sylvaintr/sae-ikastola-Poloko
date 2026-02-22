<x-app-layout>
    @php
        $recettes = $evenement->recettes ?? collect();
        $totalRecettes = $recettes->where('type', 'recette')->sum(fn($r) => $r->prix * ($r->quantite ?? 1));
        $totalDepensesPrev = $recettes->where('type', 'depense_previsionnelle')->sum(fn($r) => $r->prix * ($r->quantite ?? 1));
        $totalDepenses = $recettes->where('type', 'depense')->sum(fn($r) => $r->prix * ($r->quantite ?? 1));

        // Dépenses prévisionnelles des demandes liées
        $demandesDepensesPrev = $evenement->demandes->sum('montantP');
        $totalDepensesPrevAvecDemandes = $totalDepensesPrev + $demandesDepensesPrev;

        // Intégrer les dépenses réelles des demandes
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
                                @php
                                    $typeBadgeClass = match($recette->type) {
                                        'recette' => 'bg-success-subtle text-success',
                                        'depense_previsionnelle' => 'bg-warning-subtle text-warning',
                                        'depense' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                    $typeIcon = match($recette->type) {
                                        'recette' => 'bi-arrow-down-circle',
                                        'depense_previsionnelle' => 'bi-clock-history',
                                        'depense' => 'bi-arrow-up-circle',
                                        default => 'bi-question-circle'
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge {{ $typeBadgeClass }} d-inline-flex align-items-center gap-1">
                                            <i class="bi {{ $typeIcon }}"></i>
                                            {{ $typeLabels[$recette->type] ?? ucfirst($recette->type ?? __('evenements.type_recette')) }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ number_format((float) $recette->prix, 2, ',', ' ') }} &euro;</td>
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

                {{-- Résumé comptable avec cartes --}}
                <div class="row g-3 mt-4">
                    {{-- Recettes --}}
                    <div class="col-6 col-lg-3">
                        <div class="card border-0 bg-success-subtle h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-arrow-down-circle fs-4 text-success"></i>
                                <div class="text-muted small mt-1">
                                    <span class="basque">Sarrerak</span>
                                    <span class="fr d-block">Recettes</span>
                                </div>
                                <div class="fs-5 fw-bold text-success">{{ number_format($totalRecettes, 2, ',', ' ') }} &euro;</div>
                            </div>
                        </div>
                    </div>

                    {{-- Dépenses prévues --}}
                    <div class="col-6 col-lg-3">
                        <div class="card border-0 bg-warning-subtle h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-clock-history fs-4 text-warning"></i>
                                <div class="text-muted small mt-1">
                                    <span class="basque">Aurreikusitako gastuak</span>
                                    <span class="fr d-block">Dépenses prévues</span>
                                </div>
                                <div class="fs-5 fw-bold text-warning">{{ number_format($totalDepensesPrevAvecDemandes, 2, ',', ' ') }} &euro;</div>
                                @if ($demandesDepensesPrev > 0)
                                    <div class="small text-muted">({{ number_format($totalDepensesPrev, 2, ',', ' ') }} + {{ number_format($demandesDepensesPrev, 2, ',', ' ') }} demandes)</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Dépenses réelles --}}
                    <div class="col-6 col-lg-3">
                        <div class="card border-0 bg-danger-subtle h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-arrow-up-circle fs-4 text-danger"></i>
                                <div class="text-muted small mt-1">
                                    <span class="basque">Benetako gastuak</span>
                                    <span class="fr d-block">Dépenses réelles</span>
                                </div>
                                <div class="fs-5 fw-bold text-danger">{{ number_format($totalDepensesAvecDemandes, 2, ',', ' ') }} &euro;</div>
                                @if ($demandesDepenses > 0)
                                    <div class="small text-muted">({{ number_format($totalDepenses, 2, ',', ' ') }} + {{ number_format($demandesDepenses, 2, ',', ' ') }} demandes)</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Balance --}}
                    <div class="col-6 col-lg-3">
                        <div class="card border-0 {{ $balance >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle' }} h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-wallet2 fs-4 {{ $balance >= 0 ? 'text-success' : 'text-danger' }}"></i>
                                <div class="text-muted small mt-1">
                                    <span class="basque">Balantzea</span>
                                    <span class="fr d-block">Solde</span>
                                </div>
                                <div class="fs-5 fw-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $balance >= 0 ? '+' : '' }}{{ number_format($balance, 2, ',', ' ') }} &euro;
                                </div>
                            </div>
                        </div>
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
                                            <span class="basque">Aurreikusitako gastua</span>
                                            <span class="fr">Dépense prévue</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="demande-header-label">
                                            <span class="basque">Benetako gastua</span>
                                            <span class="fr">Dépense réelle</span>
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
                                            @if($demande->montantP > 0)
                                                <span class="fw-semibold text-warning">{{ number_format($demande->montantP, 2, ',', ' ') }} &euro;</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
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
