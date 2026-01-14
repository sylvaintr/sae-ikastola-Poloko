<x-app-layout>
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-4 mb-5">
        <div>
            <h2 class="fw-bold display-4 mb-1" style="font-size: 2rem;">{{ isset($tache) ? 'Editatu zeregina' : 'Gehitu zeregin bat' }}</h2>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ isset($tache) ? 'Modifier la tâche' : 'Ajouter une tâche' }}</p>
        </div>
    </div>

    <div>

        <form action="{{ isset($tache) ? route('tache.update', $tache->idTache) : route('tache.store') }}"
              method="POST" id="tache-form">
            @csrf
            @if(isset($tache)) @method('PUT') @endif

            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold mb-0">Izenburua</label>
                    <p class="text-muted mt-0 admin-button-subtitle">Titre</p>
                    <input type="text" name="titre" class="form-control"
                           value="{{ old('titre', $tache->titre ?? '') }}" maxlength="255" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-0">Larrialdia</label>
                    <p class="text-muted mt-0 admin-button-subtitle">Urgence</p>
                    <select name="type" class="form-select">
                        <option value="low" {{ old('type', $tache->type ?? '') == 'low' ? 'selected' : '' }}>Faible</option>
                        <option value="medium" {{ old('type', $tache->type ?? '') == 'medium' ? 'selected' : '' }}>Moyenne</option>
                        <option value="high" {{ old('type', $tache->type ?? '') == 'high' ? 'selected' : '' }}>Élevée</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold mb-0">Deskribapena</label>
                <p class="text-muted mt-0 admin-button-subtitle">Description</p>
                <textarea name="description" class="form-control" rows="6" required>{{ old('description', $tache->description ?? '') }}</textarea>
            </div>

            <hr>

                <h4 class="fw-bold mb-0">Esleipena</h4>
                <p class="text-muted mt-0 admin-button-subtitle">Assignation</p>

            <div class="row">
                <!-- Colonne gauche -->
                <div class="col-md-6">
                    <label class="admin-table-heading mb-0">Bilatu erabiltzaile bat</label>
                    <p class="text-muted mt-0 admin-button-subtitle">Rechercher un utilisateur</p>

                    <input type="text"
                        id="user-search"
                        class="admin-search-input"
                        placeholder="Izena edo posta elektronikoa…">

                    <p class="text-muted mt-0 admin-button-subtitle">Nom ou email…</p>

                    <div id="user-search-results" class="user-list"></div>
                </div>

                <!-- Colonne droite -->
                <div class="col-md-6">
                    <label class="form-label mb-0">Hautatutako erabiltzaileak :</label>
                    <p class="text-muted mt-0 admin-button-subtitle">Utilisateurs sélectionnés</p>

                    <div id="assigned-users" class="user-list user-list--assigned">
                        @if(isset($tache))
                            @foreach($tache->realisateurs as $r)
                                <div class="user-item assigned-user" data-id="{{ $r->idUtilisateur }}">
                                    <div>
                                        <strong>{{ $r->nom }} {{ $r->prenom }}</strong><br>
                                        <small>{{ $r->email }}</small>
                                    </div>
                                    <span class="user-action remove-assigned">✕</span>

                                    <input type="hidden" name="realisateurs[]" value="{{ $r->idUtilisateur }}">
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4">
                <div>
                    <a href="{{ route('tache.index') }}" class="admin-secondary-button" style="padding-block: 8px;">Utzi</a>
                    <p class="text-muted mt-0 admin-button-subtitle">Annuler</p>
                </div>
                <div>
                    <button type="submit" class="admin-add-button" style="padding-block: 8px;">{{ isset($tache) ? 'Gorde' : 'Gehitu' }}</button>
                    <p class="text-muted mt-0 admin-button-subtitle">{{ isset($tache) ? 'Enregistrer' : 'Ajouter' }}</p>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =======================
       ASSIGNATION
    ======================= */

    function addAssignedUser(id, nom, prenom, email) {
        if (document.querySelector('#assigned-users .assigned-user[data-id="'+id+'"]')) {
            return;
        }

        const div = document.createElement('div');
        div.className = 'user-item assigned-user';
        div.setAttribute('data-id', id);

        div.innerHTML = `
            <div>
                <strong>${nom} ${prenom}</strong><br>
                <small>${email}</small>
            </div>
            <span class="user-action remove-assigned">✕</span>
        `;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'realisateurs[]';
        input.value = id;

        div.appendChild(input);
        document.getElementById('assigned-users').appendChild(div);
    }

    function removeAssignedUser(userId) {
        const chip = document.querySelector(`#assigned-users .assigned-user[data-id="${userId}"]`);
        if (chip) chip.remove();
    }

    /* =======================
       LISTE UTILISATEURS
    ======================= */

    function renderUserList(users) {
        let html = '';

        const assignedIds = Array.from(
            document.querySelectorAll('#assigned-users .assigned-user')
        ).map(el => el.dataset.id);

        if (!users.length) {
            html = `<div class="user-item text-muted">Aucun utilisateur</div>`;
        } else {
            users.forEach(user => {
                const isAssigned = assignedIds.includes(String(user.idUtilisateur));

                html += `
                    <div class="user-item ${isAssigned ? 'disabled' : ''}"
                        data-id="${user.idUtilisateur}"
                        data-nom="${user.nom}"
                        data-prenom="${user.prenom}"
                        data-email="${user.email}">
                        <div>
                            <strong>${user.nom} ${user.prenom}</strong><br>
                            <small>${user.email}</small>
                        </div>
                        <span class="user-action add-user">+</span>
                    </div>
                `;
            });
        }

        document.getElementById('user-search-results').innerHTML = html;
    }

    function loadUsers(query = '') {
        $.get("{{ route('users.search') }}", { q: query })
            .done(renderUserList);
    }

    loadUsers(); // chargement initial (4 users)

    /* =======================
       RECHERCHE (DEBOUNCE)
    ======================= */

    let searchTimer = null;

    document.getElementById('user-search').addEventListener('input', function () {
        const q = this.value.trim();

        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {
            loadUsers(q);
        }, 300);
    });

    /* =======================
       EVENT DELEGATION
    ======================= */

    // Ajouter utilisateur
    document.addEventListener('click', function (e) {
        if (!e.target.classList.contains('add-user')) return;

        const item = e.target.closest('.user-item');

        const id = item.dataset.id;
        const nom = item.dataset.nom;
        const prenom = item.dataset.prenom;
        const email = item.dataset.email;

        addAssignedUser(id, nom, prenom, email);

        // rafraîchir la liste gauche (désactiver le +)
        const currentQuery = document.getElementById('user-search').value.trim();
        loadUsers(currentQuery);
    });

    // Supprimer utilisateur
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-assigned');
        if (!btn) return;

        const userId = btn.closest('.assigned-user').dataset.id;
        removeAssignedUser(userId);

        // rafraîchir la liste de gauche
        const currentQuery = document.getElementById('user-search').value.trim();
        loadUsers(currentQuery);
    });


});
</script>
@endpush

</x-app-layout>
