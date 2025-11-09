<x-app-layout>
    <x-slot name="header">
        <h2>Créer une famille</h2>
    </x-slot>

    <div class="container mt-4">
        <form action="{{ route('familles.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">ID Famille :</label>
                <input type="text" name="idFamille" class="form-control" required>
            </div>

            <hr>
            <h4>Enfants</h4>
            <div id="enfants-wrapper">
                <div class="enfant mb-3 border p-3 rounded">
                    <input type="text" name="enfants[0][prenom]" placeholder="Prénom" class="form-control mb-2">
                    <input type="text" name="enfants[0][nom]" placeholder="Nom" class="form-control mb-2">
                    <input type="date" name="enfants[0][dateN]" class="form-control mb-2">
                    <input type="text" name="enfants[0][sexe]" placeholder="Sexe (M/F)" class="form-control mb-2">
                    <input type="number" name="enfants[0][NNI]" placeholder="NNI" class="form-control mb-2">
                    <input type="number" name="enfants[0][idClasse]" placeholder="ID Classe" class="form-control mb-2">
                </div>
            </div>
            <button type="button" id="add-enfant" class="btn btn-secondary">+ Ajouter un enfant</button>

            <hr>
            <h4>Utilisateurs (Parents)</h4>
            <div id="utilisateurs-wrapper">
                <div class="utilisateur mb-3 border p-3 rounded">
                    <input type="number" name="utilisateurs[0][idUtilisateur]" placeholder="ID Utilisateur" class="form-control mb-2">
                    <input type="text" name="utilisateurs[0][parite]" placeholder="Parité (Père / Mère)" class="form-control mb-2">
                </div>
            </div>
            <button type="button" id="add-user" class="btn btn-secondary">+ Ajouter un utilisateur</button>

            <hr>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>

    <script>
        let enfantIndex = 1;
        document.getElementById('add-enfant').addEventListener('click', function() {
            const wrapper = document.getElementById('enfants-wrapper');
            const div = document.createElement('div');
            div.classList.add('enfant', 'mb-3', 'border', 'p-3', 'rounded');
            div.innerHTML = `
                <input type="text" name="enfants[${enfantIndex}][prenom]" placeholder="Prénom" class="form-control mb-2">
                <input type="text" name="enfants[${enfantIndex}][nom]" placeholder="Nom" class="form-control mb-2">
                <input type="date" name="enfants[${enfantIndex}][dateN]" class="form-control mb-2">
                <input type="text" name="enfants[${enfantIndex}][sexe]" placeholder="Sexe" class="form-control mb-2">
                <input type="number" name="enfants[${enfantIndex}][NNI]" placeholder="NNI" class="form-control mb-2">
                <input type="number" name="enfants[${enfantIndex}][idClasse]" placeholder="ID Classe" class="form-control mb-2">
            `;
            wrapper.appendChild(div);
            enfantIndex++;
        });

        let userIndex = 1;
        document.getElementById('add-user').addEventListener('click', function() {
            const wrapper = document.getElementById('utilisateurs-wrapper');
            const div = document.createElement('div');
            div.classList.add('utilisateur', 'mb-3', 'border', 'p-3', 'rounded');
            div.innerHTML = `
                <input type="number" name="utilisateurs[${userIndex}][idUtilisateur]" placeholder="ID Utilisateur" class="form-control mb-2">
                <input type="text" name="utilisateurs[${userIndex}][parite]" placeholder="Parité" class="form-control mb-2">
            `;
            wrapper.appendChild(div);
            userIndex++;
        });
    </script>
</x-app-layout>
