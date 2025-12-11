<x-app-layout>
<div class="container py-4">
    <div class="card p-4">
        <h2 class="fw-bold mb-4">{{ isset($tache) ? 'Modifier la tâche' : 'Ajouter une tâche' }}</h2>

        <form action="{{ isset($tache) ? route('tache.update', $tache->idTache) : route('tache.store') }}"
              method="POST" id="tache-form">
            @csrf
            @if(isset($tache)) @method('PUT') @endif

            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Titre</label>
                    <input type="text" name="titre" class="form-control"
                           value="{{ old('titre', $tache->titre ?? '') }}" maxlength="255" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Urgence</label>
                    <select name="type" class="form-select">
                        <option value="low" {{ old('type', $tache->type ?? '') == 'low' ? 'selected' : '' }}>Faible</option>
                        <option value="medium" {{ old('type', $tache->type ?? '') == 'medium' ? 'selected' : '' }}>Moyenne</option>
                        <option value="high" {{ old('type', $tache->type ?? '') == 'high' ? 'selected' : '' }}>Élevée</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" class="form-control" rows="6" required>{{ old('description', $tache->description ?? '') }}</textarea>
            </div>

            <hr>

            <h5 class="fw-bold mb-3">Assignation</h5>

            <div class="row">
                <!-- Colonne gauche : recherche utilisateurs -->
                <div class="col-md-6">
                    <div class="admin-search-user-container position-relative">
                        <label class="admin-table-heading mb-2">Rechercher un utilisateur</label>

                        <input type="text"
                            id="user-search"
                            class="admin-search-input"
                            placeholder="Nom ou email…">

                        <div id="user-search-results" class="admin-search-dropdown"></div>
                    </div>
                </div>

                <!-- Colonne droite : utilisateurs sélectionnés et roles -->
                <div class="col-md-6">
                    <label class="form-label">Utilisateurs sélectionnés</label>
                    <div id="assigned-users" class="mb-3" style="min-height:60px">
                        <!-- chips ajoutés via JS -->
                        @if(isset($tache))
                            @foreach($tache->realisateurs as $r)
                                <div class="badge bg-secondary me-2 mb-2 assigned-user" data-id="{{ $r->idUtilisateur }}">
                                    {{ $r->prenom }} {{ $r->nom }}
                                    <button type="button" class="btn-close btn-close-white ms-2 remove-assigned" aria-label="Remove"></button>
                                    <input type="hidden" name="realisateurs[]" value="{{ $r->idUtilisateur }}">
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <label class="form-label">Rôles (optionnel)</label>
                    <select name="roles[]" class="form-select" multiple>
                        @foreach($roles ?? [] as $role)
                            <option value="{{ $role->idRole }}"
                                @if(isset($selectedRoles) && in_array($role->idRole, $selectedRoles)) selected @endif>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Choisissez un ou plusieurs rôles (facultatif).</div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="{{ route('tache.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">{{ isset($tache) ? 'Enregistrer' : 'Ajouter' }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    const search = document.getElementById('user-search');

    function addAssignedUser(id, nom, prenom) {

        // Vérifie si déjà présent
        if (document.querySelector('#assigned-users .assigned-user[data-id="'+id+'"]')) {
            return;
        }

        const div = document.createElement('div');
        div.className = 'badge bg-secondary me-2 mb-2 assigned-user d-inline-flex align-items-center';
        div.setAttribute('data-id', id);

        // Contenu visuel
        div.innerHTML = `
            ${nom} ${prenom}
            <button type="button" class="btn-close btn-close-white ms-2 remove-assigned" aria-label="Remove"></button>
        `;

        // Input hidden pour envoyer au backend
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'realisateurs[]';
        input.value = id;

        div.appendChild(input);

        document.getElementById('assigned-users').appendChild(div);
    }

    function removeAssignedUser(userId) {

        // remove chip
        const chip = document.querySelector(`#assigned-users .assigned-user[data-id="${userId}"]`);
        if (chip) chip.remove();

        // remove hidden input
        const input = document.querySelector(`#assigned-users input[name="realisateurs[]"][value="${userId}"]`);
        if (input) input.remove();
    }

    $(document).ready(function () {

    let timer = null;

    $('#user-search').on('input', function () {
        const query = $(this).val().trim();

        clearTimeout(timer);

        if (query.length < 2) {
            $('#user-search-results').hide().empty();
            return;
        }

        timer = setTimeout(() => {
            $.ajax({
                url: "{{ route('users.search') }}",
                method: "GET",
                data: { q: query },
                success: function (data) {

                    let html = '';

                    if (data.length === 0) {
                        html = `<div class="admin-search-item disabled">Aucun utilisateur trouvé</div>`;
                    } else {
                        data.forEach(user => {
                            html += `
                                <div class="admin-search-item"
                                    data-id-utilisateur="${user.idUtilisateur}"
                                    data-nom="${user.nom}"
                                    data-prenom="${user.prenom}">
                                    <div>
                                        <strong>${user.nom} ${user.prenom}</strong><br>
                                        <small class="text-muted">${user.email}</small>
                                    </div>
                                </div>`;
                        });
                    }

                    $('#user-search-results').html(html).show();
                }
            });
        }, 250);
    });

    // Choisir un utilisateur
    $(document).on('click', '.admin-search-item', function () {
        if ($(this).hasClass('disabled')) return;

        const idUtilisateur = $(this).data('id-utilisateur');
        const nom = $(this).data('nom');
        const prenom = $(this).data('prenom');

        addAssignedUser(idUtilisateur, nom, prenom);

        $('#user-search').val('');
        $('#user-search-results').hide().empty();
    });

    // Retirer un utilisateur assigné
    $(document).on('click', '.remove-assigned', function () {
        const userId = $(this).closest('.assigned-user').data('id');
        removeAssignedUser(userId);
    });

    // Clic hors du dropdown -> fermer
    $(document).click(function (e) {
        if (!$(e.target).closest('.admin-search-user-container').length) {
            $('#user-search-results').hide();
        }
    });

});

});
</script>
@endpush

</x-app-layout>
