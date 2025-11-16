<x-app-layout>
    <div class="container py-4">

      <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0 text-uppercase text-secondary" style="font-size: 1.8rem; font-weight: 600;">Évènement</h2>

    <a href="{{ route('evenements.create') }}" class="btn btn-warning-main shadow-sm">
        Ajouter un évènement
    </a>
</div>


        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">

                    <div class="d-flex align-items-center">
                        <div class="input-group" style="width: auto;">
                            <input id="search-input" type="text" class="form-control" placeholder="." aria-label="Entrez un request ID">
                            <button id="search-button" class="btn btn-primary text-white fw-semibold" type="button">Ikerketzako</button>
                        </div>
                        <p class="ms-3 mb-0 text-muted d-none d-md-block" style="font-size: 0.8rem;">Rechercher</p>
                    </div>

                    <div class="d-flex align-items-center">
                        <p class="mb-0 me-2 text-muted" style="font-size: 0.9rem;">Filtrer par</p>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownFiltre" data-bs-toggle="dropdown" aria-expanded="false">
                                Type d'événement
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFiltre">
                                <li><a class="dropdown-item filter-type" href="#" data-filter="all">Tous les types</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item filter-type" href="#" data-filter="Marché">Marché</a></li>
                                <li><a class="dropdown-item filter-type" href="#" data-filter="Scolaire">Scolaire</a></li>
                                <li><a class="dropdown-item filter-type" href="#" data-filter="Ménage">Ménage</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-secondary fw-semibold">Request ID</th>
                                <th class="text-secondary fw-semibold">Date</th>
                                <th class="text-secondary fw-semibold">Titre</th>
                                <th class="text-secondary fw-semibold">Cible</th>
                                <th class="text-secondary fw-semibold text-center">Recettes</th>
                                <th class="text-secondary fw-semibold text-center">Dépenses</th>
                                <th class="text-secondary fw-semibold text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="events-table-body">
                            @foreach($evenements as $evenement)
                                <tr>
                                    <td>{{ $evenement->request_id }}</td>
                                    <td>{{ $evenement->date }}</td>
                                    <td>{{ $evenement->titre }}</td>
                                    <td>{{ $evenement->cible }}</td>
                                    <td class="text-center">{{ $evenement->recettes }} €</td>
                                    <td class="text-center">{{ $evenement->depenses }} €</td>
                                    <td class="text-center">
                                        {{-- <a href="{{ route('evenements.show', $evenement->id) }}" class="btn btn-sm btn-primary">Voir</a> --}}
                                        {{-- <a href="{{ route('evenements.edit', $evenement->id) }}" class="btn btn-sm btn-warning">Modifier</a> --}}
                                        {{-- <form action="{{ route('evenements.destroy', $evenement->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression ?')">
                                                Supprimer
                                            </button>
                                        </form> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>

    <div id="notification-container"
         style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    </div>
</x-app-layout>
