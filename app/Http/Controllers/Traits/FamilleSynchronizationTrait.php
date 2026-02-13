<?php
namespace App\Http\Controllers\Traits;

use App\Models\Enfant;
use App\Models\Famille;
use App\Models\Utilisateur;

trait FamilleSynchronizationTrait
{
    /**
     * Méthode pour synchronise les enfants d'une famille (ajout, mise à jour, suppression)
     * @param array $enfantsData Liste des données des enfants à synchroniser, chaque élément doit contenir les informations nécessaires pour identifier et mettre à jour ou créer un enfant
     * @param int $familleId ID de la famille à laquelle les enfants sont associés
     * @return void
     */
    protected function syncEnfants(array $enfantsData, int $familleId): void
    {
        $enfantsActuels = Enfant::where('idFamille', $familleId)->pluck('idEnfant')->toArray();
        $idsNouveaux    = $this->extractEnfantIds($enfantsData);

        $this->detachEnfants($enfantsActuels, $idsNouveaux);
        $this->attachOrUpdateEnfants($enfantsData, $familleId);
    }

    /**
     * Méthode pour extraire les IDs des enfants depuis les données.
     * @param array $enfantsData Liste des données des enfants
     * @return array Liste des IDs des enfants extraits des données fournies
     */
    private function extractEnfantIds(array $enfantsData): array
    {
        $ids = [];
        foreach ($enfantsData as $enfantData) {
            if (isset($enfantData['idEnfant'])) {
                $ids[] = $enfantData['idEnfant'];
            }
        }
        return $ids;
    }

    /**
     * Méthode pour détacher les enfants qui ne sont plus dans la liste.
     * @param array $enfantsActuels Liste des IDs des enfants actuellement associés à la famille
     * @param array $idsNouveaux Liste des IDs des enfants qui doivent être associés à la famille après la synchronisation
     * @return void
     */
    private function detachEnfants(array $enfantsActuels, array $idsNouveaux): void
    {
        $idsASupprimer = array_diff($enfantsActuels, $idsNouveaux);
        if (! empty($idsASupprimer)) {
            Enfant::whereIn('idEnfant', $idsASupprimer)->update(['idFamille' => null]);
        }
    }

    /**
     * Méthode pour attacher ou mettre à jour les enfants de la famille.
     * @param array $enfantsData Liste des données des enfants à attacher ou mettre à jour, chaque élément doit contenir les informations nécessaires pour identifier et mettre à jour ou créer un enfant
     * @param int $familleId ID de la famille à laquelle les enfants sont associés
     * @return void
     */
    private function attachOrUpdateEnfants(array $enfantsData, int $familleId): void
    {
        foreach ($enfantsData as $enfantData) {
            if (isset($enfantData['idEnfant'])) {
                $this->updateExistingEnfant($enfantData, $familleId);
            } else {
                $this->createNewEnfant($enfantData, $familleId);
            }
        }
    }

    /**
     * Méthode pour mettre à jour un enfant existant.
     * @param array $enfantData Données de l'enfant à mettre à jour
     * @param int $familleId ID de la famille à laquelle l'enfant est associé
     * @return void
     */
    private function updateExistingEnfant(array $enfantData, int $familleId): void
    {
        $enfant = Enfant::find($enfantData['idEnfant']);
        if (! $enfant) {
            return;
        }

        $updatableFields = ['nom', 'prenom', 'dateN', 'sexe', 'NNI', 'idClasse', 'nbFoisGarderie'];
        foreach ($updatableFields as $field) {
            if (isset($enfantData[$field])) {
                $enfant->$field = $enfantData[$field];
            }
        }
        $enfant->idFamille = $familleId;
        $enfant->save();
    }

    /**
     * Méthode pour créer un nouvel enfant.
     * @param array $enfantData Données de l'enfant à créer
     * @param int $familleId ID de la famille à laquelle l'enfant est associé
     * @return void
     */
    private function createNewEnfant(array $enfantData, int $familleId): void
    {
        Enfant::create([
            'nom'       => $enfantData['nom'],
            'prenom'    => $enfantData['prenom'],
            'dateN'     => $enfantData['dateN'],
            'sexe'      => $enfantData['sexe'],
            'NNI'       => $enfantData['NNI'],
            'idClasse'  => $enfantData['idClasse'],
            'idFamille' => $familleId,
        ]);
    }

    /**
     * Méthode pour synchroniser les utilisateurs d'une famille (ajout, mise à jour, suppression)
     * @param array $usersData Liste des données des utilisateurs à synchroniser, chaque élément doit contenir les informations nécessaires pour identifier et mettre à jour ou créer un utilisateur, ainsi que la parité dans la famille
     * @param Famille $famille Instance de la famille à laquelle les utilisateurs sont associés
     * @return void
     */
    protected function syncUtilisateurs(array $usersData, Famille $famille): void
    {
        $utilisateursActuels = $famille->utilisateurs->pluck('idUtilisateur')->toArray();
        $idsNouveaux         = $this->extractUtilisateurIds($usersData);

        $this->detachUtilisateurs($famille, $utilisateursActuels, $idsNouveaux);
        $this->attachOrUpdateUtilisateurs($usersData, $famille);
    }

    /**
     * Extrait les IDs des utilisateurs depuis les données.
     * @param array $usersData Liste des données des utilisateurs
     * @return array Liste des IDs des utilisateurs extraits des données fournies
     */
    private function extractUtilisateurIds(array $usersData): array
    {
        $ids = [];
        foreach ($usersData as $userData) {
            if (isset($userData['idUtilisateur'])) {
                $ids[] = $userData['idUtilisateur'];
            }
        }
        return $ids;
    }

    /**
     * Méthode pour détacher les utilisateurs qui ne sont plus dans la liste.
     * @param Famille $famille Instance de la famille dont les utilisateurs doivent être détachés
     * @param array $utilisateursActuels Liste des IDs des utilisateurs actuellement associés à la famille
     * @param array $idsNouveaux Liste des IDs des utilisateurs qui doivent être associés à la famille après la synchronisation
     * @return void
     */
    private function detachUtilisateurs(Famille $famille, array $utilisateursActuels, array $idsNouveaux): void
    {
        $idsADetacher = array_diff($utilisateursActuels, $idsNouveaux);
        if (! empty($idsADetacher)) {
            $famille->utilisateurs()->detach($idsADetacher);
        }
    }

    /**
     * Méthode pour attacher ou mettre à jour les utilisateurs de la famille.
     * @param array $usersData Liste des données des utilisateurs à attacher ou mettre à jour, chaque élément doit contenir les informations nécessaires pour identifier et mettre à jour ou créer un utilisateur, ainsi que la parité dans la famille
     * @param Famille $famille Instance de la famille à laquelle les utilisateurs sont associés
     * @return void
     * Note importante : la relation belongsToMany entre Famille et Utilisateur utilise une table pivot `lier` qui contient `idUtilisateur` et `idFamille`. Lors de la synchronisation, il est crucial d'utiliser wherePivot pour éviter les ambiguïtés liées à la colonne `idUtilisateur` qui existe à la fois dans la table `utilisateur` et dans la table pivot `lier`.
     */
    private function attachOrUpdateUtilisateurs(array $usersData, Famille $famille): void
    {
        foreach ($usersData as $userData) {
            if (isset($userData['idUtilisateur'])) {
                $this->updateExistingUtilisateur($userData, $famille);
            } else {
                $this->createNewUtilisateur($userData, $famille);
            }
        }
    }

    /**
     * Méthode pour mettre à jour un utilisateur existant et sa relation avec la famille.
     * @param array $userData Données de l'utilisateur à mettre à jour
     * @param Famille $famille Instance de la famille à laquelle l'utilisateur est associé
     * @return void
     */
    private function updateExistingUtilisateur(array $userData, Famille $famille): void
    {
        $utilisateur = Utilisateur::find($userData['idUtilisateur']);
        if ($utilisateur) {
            $this->updateUtilisateurFields($utilisateur, $userData);
        }
        $this->syncUtilisateurPivot($famille, $userData['idUtilisateur'], $userData['parite'] ?? null);
    }

    /**
     * Méthode pour mettre à jour les champs d'un utilisateur.
     * @param Utilisateur $utilisateur Instance de l'utilisateur à mettre à jour
     * @param array $userData Données de l'utilisateur à utiliser pour la mise à jour
     * @return void
     */
    private function updateUtilisateurFields(Utilisateur $utilisateur, array $userData): void
    {
        $updatableFields = ['nom', 'prenom', 'email', 'languePref'];
        foreach ($updatableFields as $field) {
            if (isset($userData[$field])) {
                $utilisateur->$field = $userData[$field];
            }
        }
        if (isset($userData['mdp']) && ! empty($userData['mdp'])) {
            $utilisateur->mdp = bcrypt($userData['mdp']);
        }
        $utilisateur->save();
    }

    /**
     * Méthode pour synchroniser la relation pivot (parité) entre famille et utilisateur.
     * @param Famille $famille Instance de la famille
     * @param int $utilisateurId ID de l'utilisateur
     * @param int|null $parite Parité à synchroniser
     * @return void
     */
    private function syncUtilisateurPivot(Famille $famille, int $utilisateurId, ?int $parite): void
    {
        // IMPORTANT: la relation belongsToMany joint `utilisateur` et `lier`.
        // `idUtilisateur` existe dans les 2 tables => utiliser wherePivot pour éviter la colonne ambiguë.
        if ($famille->utilisateurs()->wherePivot('idUtilisateur', $utilisateurId)->exists()) {
            $famille->utilisateurs()->updateExistingPivot($utilisateurId, ['parite' => $parite]);
        } else {
            $famille->utilisateurs()->attach($utilisateurId, ['parite' => $parite]);
        }
    }

    /**
     * Méthode pour créer un nouvel utilisateur et l'attacher à la famille.
     * @param array $userData Données de l'utilisateur à créer
     * @param Famille $famille Instance de la famille à laquelle l'utilisateur sera associé
     * @return void
     */
    private function createNewUtilisateur(array $userData, Famille $famille): void
    {
        $password = $userData['mdp'] ?? \Illuminate\Support\Str::random(12);

        $newUser = Utilisateur::create([
            'nom'        => $userData['nom'],
            'prenom'     => $userData['prenom'],
            'mdp'        => bcrypt($password),
            'languePref' => $userData['languePref'] ?? 'fr',
            'email'      => $userData['email'] ?? null,
        ]);

        $famille->utilisateurs()->attach($newUser->idUtilisateur, [
            'parite' => $userData['parite'] ?? null,
        ]);
    }
}
