<?php

namespace App\Http\Controllers\Traits;

use App\Models\Enfant;
use App\Models\Utilisateur;
use App\Models\Famille;

trait FamilleSynchronizationTrait
{
    /**
     * Synchronise les enfants d'une famille (ajout, mise à jour, suppression)
     */
    protected function syncEnfants(array $enfantsData, int $familleId): void
    {
        $enfantsActuels = Enfant::where('idFamille', $familleId)->pluck('idEnfant')->toArray();
        $idsNouveaux = $this->extractEnfantIds($enfantsData);
        
        $this->detachEnfants($enfantsActuels, $idsNouveaux);
        $this->attachOrUpdateEnfants($enfantsData, $familleId);
    }

    /**
     * Extrait les IDs des enfants depuis les données.
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
     * Détache les enfants qui ne sont plus dans la liste.
     */
    private function detachEnfants(array $enfantsActuels, array $idsNouveaux): void
    {
        $idsASupprimer = array_diff($enfantsActuels, $idsNouveaux);
        if (!empty($idsASupprimer)) {
            Enfant::whereIn('idEnfant', $idsASupprimer)->update(['idFamille' => null]);
        }
    }

    /**
     * Attache ou met à jour les enfants de la famille.
     */
    private function attachOrUpdateEnfants(array $enfantsData, int $familleId): void
    {
        foreach ($enfantsData as $enfantData) {
            if (isset($enfantData['idEnfant'])) {
                $this->updateExistingEnfant($enfantData, $familleId);
            } else {
                if (! $this->hasRequiredEnfantFields($enfantData)) {
                    continue;
                }
                $this->createNewEnfant($enfantData, $familleId);
            }
        }
    }

    /**
     * Vérifie la présence des champs requis pour créer un nouvel enfant.
     */
    private function hasRequiredEnfantFields(array $enfantData): bool
    {
        $required = ['nom', 'prenom', 'dateN', 'sexe', 'NNI', 'idClasse'];
        foreach ($required as $field) {
            if (! isset($enfantData[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Met à jour un enfant existant.
     */
    private function updateExistingEnfant(array $enfantData, int $familleId): void
    {
        $enfant = Enfant::find($enfantData['idEnfant']);
        if (!$enfant) {
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
     * Crée un nouvel enfant.
     */
    private function createNewEnfant(array $enfantData, int $familleId): void
    {
        Enfant::create([
            'nom' => $enfantData['nom'],
            'prenom' => $enfantData['prenom'],
            'dateN' => $enfantData['dateN'],
            'sexe' => $enfantData['sexe'],
            'NNI' => $enfantData['NNI'],
            'idClasse' => $enfantData['idClasse'],
            'idFamille' => $familleId,
        ]);
    }

    /**
     * Synchronise les utilisateurs d'une famille (ajout, mise à jour, suppression)
     */
    protected function syncUtilisateurs(array $usersData, Famille $famille): void
    {
        $utilisateursActuels = $famille->utilisateurs->pluck('idUtilisateur')->toArray();
        $idsNouveaux = $this->extractUtilisateurIds($usersData);
        
        $this->detachUtilisateurs($famille, $utilisateursActuels, $idsNouveaux);
        $this->attachOrUpdateUtilisateurs($usersData, $famille);
    }

    /**
     * Extrait les IDs des utilisateurs depuis les données.
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
     * Détache les utilisateurs qui ne sont plus dans la liste.
     */
    private function detachUtilisateurs(Famille $famille, array $utilisateursActuels, array $idsNouveaux): void
    {
        $idsADetacher = array_diff($utilisateursActuels, $idsNouveaux);
        if (!empty($idsADetacher)) {
            $famille->utilisateurs()->detach($idsADetacher);
        }
    }

    /**
     * Attache ou met à jour les utilisateurs de la famille.
     */
    private function attachOrUpdateUtilisateurs(array $usersData, Famille $famille): void
    {
        foreach ($usersData as $userData) {
            if (isset($userData['idUtilisateur'])) {
                $this->updateExistingUtilisateur($userData, $famille);
            } else {
                // En update, ignorer les entrées sans idUtilisateur
                continue;
            }
        }
    }

    /**
     * Met à jour un utilisateur existant et sa relation avec la famille.
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
     * Met à jour les champs d'un utilisateur.
     */
    private function updateUtilisateurFields(Utilisateur $utilisateur, array $userData): void
    {
        $updatableFields = ['nom', 'prenom', 'email', 'languePref'];
        foreach ($updatableFields as $field) {
            if (isset($userData[$field])) {
                $utilisateur->$field = $userData[$field];
            }
        }
        if (isset($userData['mdp']) && !empty($userData['mdp'])) {
            $utilisateur->mdp = bcrypt($userData['mdp']);
        }
        $utilisateur->save();
    }

    /**
     * Synchronise la relation pivot (parité) entre famille et utilisateur.
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
     * Crée un nouvel utilisateur et l'attache à la famille.
     */
    private function createNewUtilisateur(array $userData, Famille $famille): void
    {
        $password = $userData['mdp'] ?? \Illuminate\Support\Str::random(12);

        $newUser = Utilisateur::create([
            'nom' => $userData['nom'],
            'prenom' => $userData['prenom'],
            'mdp' => bcrypt($password),
            'languePref' => $userData['languePref'] ?? 'fr',
            'email' => $userData['email'] ?? null,
        ]);

        $famille->utilisateurs()->attach($newUser->idUtilisateur, [
            'parite' => $userData['parite'] ?? null,
        ]);
    }
}

