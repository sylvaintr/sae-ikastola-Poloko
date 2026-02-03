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
                    <label class="form-label fw-bold mb-0">Izenburua <span class="text-danger">*</span></label>
                    <p class="text-muted mt-0 admin-button-subtitle">Titre</p>
                    <input type="text" name="titre" class="form-control"
                           value="{{ old('titre', $tache->titre ?? '') }}" maxlength="255" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-0">Larrialdia <span class="text-danger">*</span></label>
                    <p class="text-muted mt-0 admin-button-subtitle">Urgence</p>
                    <select name="type" class="form-select">
                        <option value="low" {{ old('type', $tache->type ?? '') == 'low' ? 'selected' : '' }}>Faible</option>
                        <option value="medium" {{ old('type', $tache->type ?? '') == 'medium' ? 'selected' : '' }}>Moyenne</option>
                        <option value="high" {{ old('type', $tache->type ?? '') == 'high' ? 'selected' : '' }}>Élevée</option>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-0">Hasiera data <span class="text-danger">*</span></label>
                    <p class="text-muted mt-0 admin-button-subtitle">Date de début de la tâche</p>
                    <input type="date" name="dateD" class="form-control"
                           placeholder="Date de début de la tâche"
                           value="{{ old('dateD', isset($tache) && $tache->dateD ? $tache->dateD->format('Y-m-d') : '') }}" required>
                   
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold mb-0">Deskribapena <span class="text-danger">*</span></label>
                <p class="text-muted mt-0 admin-button-subtitle">Description</p>
                <textarea name="description" class="form-control" rows="6" required>{{ old('description', $tache->description ?? '') }}</textarea>
            </div>

            <hr>

            <h4 class="fw-bold mb-0">Esleipena</h4>
            <p class="text-muted mt-0 admin-button-subtitle">Assignation</p>

            <div class="role-selector-container">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="user-search" class="form-label small">Bilatu erabiltzaile bat</label>
                        <p class="text-muted mt-0 admin-button-subtitle small">Rechercher un utilisateur</p>
                        <input type="text" id="user-search" class="form-control" placeholder="Tapez pour rechercher...">
                        <div id="available-users" class="role-list mt-2">
                            <!-- Les utilisateurs seront chargés ici dynamiquement -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-label small mb-2">Hautatutako erabiltzaileak <span class="text-danger">*</span></div>
                        <p class="text-muted mt-0 admin-button-subtitle small">Utilisateurs sélectionnés</p>
                        <div id="selected-users" class="role-list mt-2">
                            <div class="role-list-empty-message">Aucun utilisateur n'a été sélectionné</div>
                        </div>
                        <div id="users-error" class="invalid-feedback d-none mt-2">Au moins un utilisateur doit être sélectionné.</div>
                    </div>
                </div>
                
                <div id="user-inputs">
                    @if(isset($tache))
                        @foreach($tache->realisateurs as $r)
                            <input type="hidden" name="realisateurs[]" value="{{ $r->idUtilisateur }}">
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4">
                <div>
                    <a href="{{ route('tache.index') }}" class="btn admin-cancel-btn px-4">Utzi</a>
                    <p class="text-muted mt-1 mb-0 small text-center">Annuler</p>
                </div>
                <div>
                    <button type="submit" class="btn fw-semibold px-4 admin-submit-btn">{{ isset($tache) ? 'Gorde' : 'Gehitu' }}</button>
                    <p class="text-muted mt-1 mb-0 small text-center">{{ isset($tache) ? 'Enregistrer' : 'Ajouter' }}</p>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const userSearch = document.getElementById('user-search');
    const availableUsers = document.getElementById('available-users');
    const selectedUsers = document.getElementById('selected-users');
    const userInputs = document.getElementById('user-inputs');
    const usersError = document.getElementById('users-error');
    const selectedUserIds = new Set();
    let allUsersData = [];
    
    // Normaliser une chaîne en supprimant les accents
    function normalizeString(str) {
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }
    
    // Cache pour les noms d'utilisateurs normalisés
    const userNamesCache = new Map();
    
    // Filtrer les utilisateurs disponibles
    function filterUsers(searchTerm) {
        const normalizedTerm = normalizeString(searchTerm.trim());
        const hasTerm = normalizedTerm.length > 0;
        
        requestAnimationFrame(() => {
            const userElements = Array.from(availableUsers.querySelectorAll('.role-item'));
            
            userElements.forEach(user => {
                const userId = user.dataset.userId;
                const isSelected = selectedUserIds.has(userId);
                
                if (isSelected) {
                    user.style.display = 'none';
                    return;
                }
                
                if (!hasTerm) {
                    user.style.display = 'flex';
                    return;
                }
                
                const normalizedUserName = userNamesCache.get(user);
                const shouldShow = normalizedUserName.includes(normalizedTerm);
                
                user.style.display = shouldShow ? 'flex' : 'none';
            });
        });
    }
    
    // Mettre à jour le message vide
    function updateEmptyMessage() {
        const emptyMessage = selectedUsers.querySelector('.role-list-empty-message');
        if (selectedUserIds.size === 0) {
            if (!emptyMessage) {
                const message = document.createElement('div');
                message.className = 'role-list-empty-message';
                message.textContent = 'Aucun utilisateur n\'a été sélectionné';
                selectedUsers.appendChild(message);
            }
        } else {
            if (emptyMessage) {
                emptyMessage.remove();
            }
        }
    }
    
    // Créer un élément utilisateur disponible
    function createUserItem(user) {
        const userItem = document.createElement('div');
        userItem.className = 'role-item';
        userItem.dataset.userId = user.idUtilisateur;
        userItem.dataset.userName = `${user.nom} ${user.prenom}`;
        
        const span = document.createElement('span');
        span.textContent = `${user.nom} ${user.prenom}`;
        const icon = document.createElement('i');
        icon.className = 'bi bi-plus-circle';
        
        userItem.appendChild(span);
        userItem.appendChild(icon);
        
        userItem.addEventListener('click', function() {
            addUser(userItem);
        });
        
        // Ajouter au cache
        userNamesCache.set(userItem, normalizeString(`${user.nom} ${user.prenom}`));
        
        return userItem;
    }
    
    // Ajouter un utilisateur
    function addUser(userItem) {
        const userId = userItem.dataset.userId;
        const userName = userItem.dataset.userName;
        
        if (selectedUserIds.has(userId)) {
            return;
        }
        
        selectedUserIds.add(userId);
        
        requestAnimationFrame(() => {
            // Créer l'élément dans la liste sélectionnée
            const selectedItem = document.createElement('div');
            selectedItem.className = 'role-item selected';
            selectedItem.dataset.userId = userId;
            selectedItem.dataset.userName = userName;
            
            const span = document.createElement('span');
            span.textContent = userName;
            const icon = document.createElement('i');
            icon.className = 'bi bi-x-circle';
            
            selectedItem.appendChild(span);
            selectedItem.appendChild(icon);
            
            // Ajouter l'événement pour retirer
            selectedItem.addEventListener('click', function() {
                removeUser(userId);
            });
            
            selectedUsers.appendChild(selectedItem);
            
            // Créer l'input hidden
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'realisateurs[]';
            input.value = userId;
            userInputs.appendChild(input);
            
            // Masquer l'utilisateur de la liste disponible
            userItem.style.display = 'none';
            
            // Réinitialiser la recherche
            userSearch.value = '';
            
            // Réafficher tous les utilisateurs non sélectionnés
            const userElements = Array.from(availableUsers.querySelectorAll('.role-item'));
            userElements.forEach(el => {
                if (!selectedUserIds.has(el.dataset.userId)) {
                    el.style.display = 'flex';
                }
            });
            
            // Mettre à jour le message vide
            updateEmptyMessage();
        });
    }
    
    // Retirer un utilisateur
    function removeUser(userId) {
        selectedUserIds.delete(userId);
        
        requestAnimationFrame(() => {
            // Retirer de la liste sélectionnée
            const selectedItem = selectedUsers.querySelector(`[data-user-id="${userId}"]`);
            if (selectedItem) {
                selectedItem.remove();
            }
            
            // Retirer l'input hidden
            const input = userInputs.querySelector(`input[value="${userId}"]`);
            if (input) {
                input.remove();
            }
            
            // Réafficher dans la liste disponible
            const userElements = Array.from(availableUsers.querySelectorAll('.role-item'));
            const availableItem = userElements.find(el => el.dataset.userId == userId);
            
            if (availableItem) {
                availableItem.style.display = 'flex';
            }
            
            // Réappliquer le filtre de recherche
            if (userSearch.value.trim().length > 0) {
                filterUsers(userSearch.value);
            }
            
            // Mettre à jour le message vide
            updateEmptyMessage();
        });
    }
    
    // Charger les utilisateurs depuis l'API
    function loadUsers(query = '') {
        $.get("{{ route('users.search') }}", { q: query })
            .done(function(users) {
                allUsersData = users;
                renderUserList(users);
            })
            .fail(function() {
                availableUsers.innerHTML = '<div class="role-list-empty-message">Erreur lors du chargement des utilisateurs</div>';
            });
    }
    
    // Afficher la liste des utilisateurs
    function renderUserList(users) {
        availableUsers.innerHTML = '';
        
        users.forEach(user => {
            const userItem = createUserItem(user);
            availableUsers.appendChild(userItem);
        });
        
        // Filtrer les utilisateurs déjà sélectionnés
        filterUsers('');
        updateEmptyMessage();
    }
    
    // Charger les utilisateurs déjà assignés (mode édition)
    function loadAssignedUsers() {
        @if(isset($tache))
            @foreach($tache->realisateurs as $r)
                selectedUserIds.add('{{ $r->idUtilisateur }}');
                
                const selectedItem = document.createElement('div');
                selectedItem.className = 'role-item selected';
                selectedItem.dataset.userId = '{{ $r->idUtilisateur }}';
                selectedItem.dataset.userName = '{{ $r->nom }} {{ $r->prenom }}';
                
                const span = document.createElement('span');
                span.textContent = '{{ $r->nom }} {{ $r->prenom }}';
                const icon = document.createElement('i');
                icon.className = 'bi bi-x-circle';
                
                selectedItem.appendChild(span);
                selectedItem.appendChild(icon);
                
                selectedItem.addEventListener('click', function() {
                    removeUser('{{ $r->idUtilisateur }}');
                });
                
                selectedUsers.appendChild(selectedItem);
            @endforeach
        @endif
        
        updateEmptyMessage();
    }
    
    // Debounce pour la recherche
    let searchTimeout;
    userSearch.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterUsers(e.target.value);
        }, 150);
    });
    
    // Charger les utilisateurs déjà assignés d'abord
    loadAssignedUsers();
    
    // Puis charger la liste des utilisateurs disponibles
    loadUsers();
});
</script>
@endpush

</x-app-layout>
