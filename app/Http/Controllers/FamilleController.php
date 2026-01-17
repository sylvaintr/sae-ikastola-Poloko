<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;

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
        $tousUtilisateurs = Utilisateur::doesntHave('familles')->get();

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
            'q' => 'nullable|string|min:2|max:50',
        ]);

        $query = $request->input('q');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $users = Utilisateur::doesntHave('familles')
            ->where(function ($q) use ($query) {
                $q->where('nom', 'like', "%{$query}%")
                  ->orWhere('prenom', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

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

    private function updateEnfants(array $enfantsData): void
    {
        foreach ($enfantsData as $childData) {
            if (!isset($childData['idEnfant'])) {
                continue;
            }

            $enfant = Enfant::find($childData['idEnfant']);

            if ($enfant) {
                // Mise à jour de tous les champs modifiables
                $updatableFields = ['nom', 'prenom', 'dateN', 'sexe', 'NNI', 'idClasse', 'nbFoisGarderie'];
                
                foreach ($updatableFields as $field) {
                    if (isset($childData[$field])) {
                        $enfant->$field = $childData[$field];
                    }
                }
                
                $enfant->save();
            }
        }
    }

    private function updateUtilisateurs(array $usersData): void
    {
        foreach ($usersData as $userData) {
            if (!isset($userData['idUtilisateur'])) {
                continue;
            }

            $utilisateur = Utilisateur::find($userData['idUtilisateur']);

            if ($utilisateur) {
                // Mise à jour de tous les champs modifiables
                $updatableFields = ['nom', 'prenom', 'email', 'languePref'];
                
                foreach ($updatableFields as $field) {
                    if (isset($userData[$field])) {
                        $utilisateur->$field = $userData[$field];
                    }
                }
                
                // Mise à jour du mot de passe si fourni
                if (isset($userData['mdp']) && !empty($userData['mdp'])) {
                    $utilisateur->mdp = bcrypt($userData['mdp']);
                }
                
                $utilisateur->save();
            }
        }
    }

    /**
     * Synchronise les enfants d'une famille (ajout, mise à jour, suppression)
     */
    private function syncEnfants(array $enfantsData, int $familleId): void
    {
        // Récupérer les IDs des enfants actuels de la famille
        $enfantsActuels = Enfant::where('idFamille', $familleId)->pluck('idEnfant')->toArray();
        
        // IDs des enfants dans la nouvelle liste
        $idsNouveaux = [];
        foreach ($enfantsData as $enfantData) {
            if (isset($enfantData['idEnfant'])) {
                $idsNouveaux[] = $enfantData['idEnfant'];
            }
        }

        // Enfants à supprimer (détacher)
        $idsASupprimer = array_diff($enfantsActuels, $idsNouveaux);
        if (!empty($idsASupprimer)) {
            Enfant::whereIn('idEnfant', $idsASupprimer)->update(['idFamille' => null]);
        }

        // Mettre à jour ou ajouter les enfants
        foreach ($enfantsData as $enfantData) {
            if (isset($enfantData['idEnfant'])) {
                // Mise à jour d'un enfant existant
                $enfant = Enfant::find($enfantData['idEnfant']);
                if ($enfant) {
                    $updatableFields = ['nom', 'prenom', 'dateN', 'sexe', 'NNI', 'idClasse', 'nbFoisGarderie'];
                    foreach ($updatableFields as $field) {
                        if (isset($enfantData[$field])) {
                            $enfant->$field = $enfantData[$field];
                        }
                    }
                    $enfant->idFamille = $familleId;
                    $enfant->save();
                }
            } else {
                // Création d'un nouvel enfant
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

    /**
     * Synchronise les utilisateurs d'une famille (ajout, mise à jour, suppression)
     */
    private function syncUtilisateurs(array $usersData, Famille $famille): void
    {
        // Récupérer les IDs des utilisateurs actuels de la famille
        $utilisateursActuels = $famille->utilisateurs->pluck('idUtilisateur')->toArray();
        
        // IDs des utilisateurs dans la nouvelle liste
        $idsNouveaux = [];
        foreach ($usersData as $userData) {
            if (isset($userData['idUtilisateur'])) {
                $idsNouveaux[] = $userData['idUtilisateur'];
            }
        }

        // Utilisateurs à détacher
        $idsADetacher = array_diff($utilisateursActuels, $idsNouveaux);
        if (!empty($idsADetacher)) {
            $famille->utilisateurs()->detach($idsADetacher);
        }

        // Mettre à jour ou ajouter les utilisateurs
        foreach ($usersData as $userData) {
            if (isset($userData['idUtilisateur'])) {
                // Mise à jour d'un utilisateur existant
                $utilisateur = Utilisateur::find($userData['idUtilisateur']);
                if ($utilisateur) {
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

                // Mettre à jour ou créer le lien avec la parité
                if ($famille->utilisateurs()->where('idUtilisateur', $userData['idUtilisateur'])->exists()) {
                    // Mettre à jour la parité
                    $famille->utilisateurs()->updateExistingPivot($userData['idUtilisateur'], [
                        'parite' => $userData['parite'] ?? null,
                    ]);
                } else {
                    // Créer le lien
                    $famille->utilisateurs()->attach($userData['idUtilisateur'], [
                        'parite' => $userData['parite'] ?? null,
                    ]);
                }
            } else {
                // Création d'un nouvel utilisateur
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
}

