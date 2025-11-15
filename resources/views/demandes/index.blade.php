<x-app-layout>
    <div class="container py-4 demande-page">
        <div class="d-flex flex-column align-items-end text-end demande-toolbar">
            <div class="d-flex flex-wrap gap-3 justify-content-end">
                {{-- Export en attente de développement --}}
                <button type="button" class="btn demande-btn-outline fw-semibold px-4">
                    Esportatu (CSV)
                </button>
                <a href="{{ route('demandes.create') }}" class="btn demande-btn-primary fw-semibold px-4">
                    Sortu txartel eskaera
                </a>
            </div>
            <p class="text-muted small mt-2 mb-0">Convertisseur CSV vers Excel si on n’arrive pas à exporter en
                Excel</p>
        </div>

        @if (session('status'))
            <div id="demande-toast" class="demande-toast shadow-sm">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-sm" aria-label="Fermer"></button>
                </div>
            </div>
        @endif

        <form class="demande-filter-form mt-4" method="GET" action="{{ route('demandes.index') }}">
            <div class="row g-3 align-items-start">
                <div class="col-md-6">
                    <label for="search" class="form-label fw-semibold text-muted small mb-1">Sartu eskaeraren ID bat</label>
                    <input type="text" id="search" name="search" class="form-control demande-search-input"
                        placeholder="Entrez un request ID" value="{{ $filters['search'] }}">
                    <small class="text-muted">Entrez un request ID</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="dropdown d-inline-block">
                        <button class="btn demande-filter-toggle fw-semibold" type="button" id="filterDropdown"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            Iragazi arabera
                            <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end demande-filter-panel p-3"
                            aria-labelledby="filterDropdown">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Egoera</label>
                                <select class="form-select" name="etat">
                                    <option value="all" @selected($filters['etat'] === 'all')>Tous les statuts</option>
                                    @foreach ($etats as $etat)
                                        <option value="{{ $etat }}" @selected($filters['etat'] === $etat)>{{ $etat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Jatorra</label>
                                <select class="form-select" name="type">
                                    <option value="all" @selected($filters['type'] === 'all')>Tous les types</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Larrialdia</label>
                                <select class="form-select" name="urgence">
                                    <option value="all" @selected($filters['urgence'] === 'all')>Toutes les urgences</option>
                                    @foreach ($urgences as $urgence)
                                        <option value="{{ $urgence }}" @selected($filters['urgence'] === $urgence)>{{ $urgence }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small text-muted">Date min</label>
                                    <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Date max</label>
                                    <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] }}">
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn demande-btn-primary flex-grow-1">Filtrer</button>
                                <a href="{{ route('demandes.index') }}" class="btn demande-btn-outline flex-grow-1">Réinitialiser</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive mt-4">
            <table class="table align-middle demande-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">
                            <span class="d-block">Eskatu ID</span>
                            <small class="text-muted">Request ID</small>
                        </th>
                        <th scope="col">
                            <span class="d-inline-flex align-items-center gap-2">Data <i class="bi bi-caret-down-fill fs-6"></i></span>
                            <small class="d-block text-muted">Date</small>
                        </th>
                        <th scope="col">
                            <span class="d-block">Izenburua</span>
                            <small class="text-muted">Titre</small>
                        </th>
                        <th scope="col">
                            <span class="d-block">Jatorra</span>
                            <small class="text-muted">Type</small>
                        </th>
                        <th scope="col">
                            <span class="d-inline-flex align-items-center gap-2">Larrialdia <i class="bi bi-caret-down-fill fs-6"></i></span>
                            <small class="text-muted">Urgence</small>
                        </th>
                        <th scope="col">
                            <span class="d-inline-flex align-items-center gap-2">Egoera <i class="bi bi-caret-down-fill fs-6"></i></span>
                            <small class="text-muted">Status</small>
                        </th>
                        <th scope="col" class="text-center">
                            <span class="d-block">Ekintzak</span>
                            <small class="text-muted">Actions</small>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($demandes as $demande)
                        <tr>
                            <td class="fw-semibold">#{{ $demande->idTache }}</td>
                            <td>{{ optional($demande->dateD)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $demande->titre }}</td>
                            <td>{{ $demande->type }}</td>
                            <td>{{ $demande->urgence ?? '—' }}</td>
                            <td>{{ $demande->etat }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('demandes.show', $demande) }}" class="btn demande-action-btn" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn demande-action-btn" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('demandes.destroy', $demande) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn demande-action-btn text-danger" title="Supprimer"
                                            onclick="return confirm('Supprimer cette demande ?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn demande-action-btn" title="Valider">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucune demande disponible.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $demandes->links() }}
        </div>
    </div>
</x-app-layout>

@if (session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toast = document.getElementById('demande-toast');
            if (!toast) return;

            const closeBtn = toast.querySelector('.btn-close');
            const hideToast = () => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 250);
            };

            closeBtn?.addEventListener('click', hideToast);
            setTimeout(hideToast, 3200);
        });
    </script>
@endif
