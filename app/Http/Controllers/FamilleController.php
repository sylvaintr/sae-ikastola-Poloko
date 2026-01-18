<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;
use App\Models\Role;

class FamilleController extends Controller
{
    private const FAMILLE_NOT_FOUND = 'Famille non trouvée';

    public function ajouter(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enfants' => 'array',
            'utilisateurs' => 'array',
        ]);

        $famille = Famille::create(['aineDansAutreSeaska' => false]);

        $this->createEnfants($data['enfants'] ?? [], $famille->idFamille);
        $this->createUtilisateurs($data['utilisateurs'] ?? [], $famille);

        $famille->load('enfants', 'utilisateurs');

        return response()->json([
            'message' => 'Famille construite avec succès',
            'famille' => $famille,
        ], 201);
    }

    public function show($id)
    {
        $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

        if (request()->wantsJson()) {
            return $famille
                ? response()->json($famille)
                : response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
        }

        if (!$famille) {
            return redirect()->route('admin.familles.index');
        }

        return view('admin.familles.show', compact('famille'));
    }

    public function create(): View
    {
        // Filtrer uniquement les utilisateurs ayant le rôle "parent" (ils peuvent avoir d'autres rôles aussi)
        $roleParent = Role::where('name', 'parent')->first();
        $tousUtilisateurs = Utilisateur::doesntHave('familles')
            ->whereHas('rolesCustom', function ($query) use ($roleParent) {
                if ($roleParent) {
                    $query->where('role.idRole', $roleParent->idRole);
                }
            })
            ->get();

        $tousEnfants = Enfant::where(function ($query) {
            $query->whereNull('idFamille')
                  ->orWhere('idFamille', 0);
        })->get();

        return view('admin.familles.create', compact('tousUtilisateurs', 'tousEnfants'));
    }

    public function edit($id)
    {
        $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

        if (!$famille) {
            return redirect()->route('admin.familles.index');
        }

        // Charger aussi les utilisateurs et enfants disponibles pour pouvoir en ajouter
        $idsUtilisateursFamille = $famille->utilisateurs->pluck('idUtilisateur')->toArray();
        $idsEnfantsFamille = $famille->enfants->pluck('idEnfant')->toArray();

        // Utilisateurs sans famille OU déjà dans cette famille
        $tousUtilisateurs = Utilisateur::where(function ($query) use ($idsUtilisateursFamille) {
            $query->doesntHave('familles')
                  ->orWhereIn('idUtilisateur', $idsUtilisateursFamille);
        })->get();

        // Enfants sans famille OU déjà dans cette famille
        $tousEnfants = Enfant::where(function ($query) use ($idsEnfantsFamille) {
            $query->where(function ($q) {
                $q->whereNull('idFamille')
                  ->orWhere('idFamille', 0);
            })
            ->orWhereIn('idEnfant', $idsEnfantsFamille);
        })->get();

        return view('admin.familles.create', compact('famille', 'tousUtilisateurs', 'tousEnfants'));
    }

    public function index()
    {
        $familles = Famille::with(['enfants', 'utilisateurs'])->get();

        if (request()->wantsJson()) {
            return response()->json($familles);
        }

        return view('admin.familles.index', compact('familles'));
    }

    public function delete($id): JsonResponse
    {
        $famille = Famille::find($id);

        if (!$famille) {
            return response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
        }

        // Vérifier s'il y a des factures associées
        $hasFactures = $famille->factures()->exists();
        
        if ($hasFactures) {
            return response()->json([
                'message' => 'Impossible de supprimer la famille : des factures sont associées',
                'error' => 'HAS_FACTURES'
            ], 422);
        }

        // Détacher les enfants plutôt que de les supprimer (mettre idFamille à null)
        // Cela préserve les données des enfants pour d'éventuelles réassignations
        $famille->enfants()->update(['idFamille' => null]);
        
        // Détacher les utilisateurs de la famille
        $famille->utilisateurs()->detach();
        
        // Supprimer la famille
        $famille->delete();

        return response()->json(['message' => 'Famille supprimée avec succès. Les enfants ont été détachés.']);
    }

    public function searchByParent(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string|min:2|max:50',
        ]);

        $query = $request->input('q');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $familles = Famille::with(['utilisateurs', 'enfants'])
            ->whereHas('utilisateurs', function ($q2) use ($query) {
                $q2->where('nom', 'like', "%{$query}%")
                   ->orWhere('prenom', 'like', "%{$query}%");
            })
            ->limit(50)
            ->get();

        if ($familles->isEmpty()) {
            return response()->json(['message' => 'Aucune famille trouvée']);
        }

        return response()->json($familles);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string|min:0|max:50',
        ]);

        $query = $request->input('q', '');

        // Filtrer uniquement les utilisateurs ayant le rôle "parent" (ils peuvent avoir d'autres rôles aussi)
        $roleParent = Role::where('name', 'parent')->first();
        
        $users = Utilisateur::doesntHave('familles')
            ->whereHas('rolesCustom', function ($q) use ($roleParent) {
                if ($roleParent) {
                    $q->where('role.idRole', $roleParent->idRole);
                }
            })
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('nom', 'like', "%{$query}%")
                         ->orWhere('prenom', 'like', "%{$query}%");
                });
            })
            ->limit(50)
            ->get()
            ->map(function ($user) {
                return [
                    'idUtilisateur' => $user->idUtilisateur,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                ];
            });

        return response()->json($users);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $famille = Famille::find($id);

        if ($famille === null) {
            return response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
        }

        $data = $request->validate([
            'enfants' => 'array',
            'utilisateurs' => 'array',
            'aineDansAutreSeaska' => 'boolean',
        ]);

        // Mettre à jour les attributs de la famille
        if (isset($data['aineDansAutreSeaska'])) {
            $famille->aineDansAutreSeaska = $data['aineDansAutreSeaska'];
            $famille->save();
        }

        // Gérer les enfants : mise à jour, ajout et suppression
        if (isset($data['enfants'])) {
            $this->syncEnfants($data['enfants'], $famille->idFamille);
        }

        // Gérer les utilisateurs : mise à jour, ajout et suppression
        if (isset($data['utilisateurs'])) {
            $this->syncUtilisateurs($data['utilisateurs'], $famille);
        }

        $famille->load('enfants', 'utilisateurs');

        return response()->json([
            'message' => 'Famille mise à jour avec succès',
            'famille' => $famille,
        ], 200);
    }

    private function createEnfants(array $enfantsData, int $familleId): void
    {
        foreach ($enfantsData as $enfantData) {
            if (isset($enfantData['idEnfant'])) {
                Enfant::where('idEnfant', $enfantData['idEnfant'])
                    ->update(['idFamille' => $familleId]);
            } else {
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
        }
    }

    private function createUtilisateurs(array $usersData, Famille $famille): void
    {
        foreach ($usersData as $userData) {
            if (isset($userData['idUtilisateur'])) {
                $famille->utilisateurs()->attach($userData['idUtilisateur'], [
                    'parite' => $userData['parite'] ?? null,
                ]);
            } else {
                // Générer un mot de passe aléatoire sécurisé si non fourni
                $password = $userData['mdp'] ?? null;
                if (!$password) {
                    $password = \Illuminate\Support\Str::random(12);
                }

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
    }


    /**
     * Synchronise les enfants d'une famille (ajout, mise à jour, suppression)
     */
    private function syncEnfants(array $enfantsData, int $familleId): void
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
                $this->createNewEnfant($enfantData, $familleId);
            }
        }
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
    private function syncUtilisateurs(array $usersData, Famille $famille): void
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
                $this->createNewUtilisateur($userData, $famille);
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
        if ($famille->utilisateurs()->where('idUtilisateur', $utilisateurId)->exists()) {
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

