<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Http\Controllers\Traits\FamilleSynchronizationTrait;

class FamilleController extends Controller
{
    use FamilleSynchronizationTrait;
    private const FAMILLE_NOT_FOUND = 'Famille non trouvée';

    public function ajouter(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enfants' => 'array',
            'utilisateurs' => 'array',
            'aineDansAutreSeaska' => 'nullable|boolean',
        ]);

        $famille = Famille::create([
            'aineDansAutreSeaska' => (bool) ($data['aineDansAutreSeaska'] ?? false),
        ]);

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
        // Un parent peut appartenir à plusieurs familles => on affiche tous les parents (sans filtrer sur "sans famille")
        $tousUtilisateurs = Utilisateur::whereHas('rolesCustom', function ($query) use ($roleParent) {
            if ($roleParent) {
                $query->where('role.idRole', $roleParent->idRole);
            }
        })
            ->orderBy('nom')
            ->orderBy('prenom')
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

        // Filtrer uniquement les utilisateurs ayant le rôle "parent" (ils peuvent avoir d'autres rôles aussi)
        $roleParent = Role::where('name', 'parent')->first();

        // Un parent peut appartenir à plusieurs familles => tous les parents.
        // On garde en plus ceux déjà liés à la famille (au cas où un utilisateur lié n'aurait pas le rôle parent).
        $tousUtilisateurs = Utilisateur::where(function ($query) use ($idsUtilisateursFamille, $roleParent) {
            $query->whereHas('rolesCustom', function ($q2) use ($roleParent) {
                if ($roleParent) {
                    $q2->where('role.idRole', $roleParent->idRole);
                }
            });

            if (!empty($idsUtilisateursFamille)) {
                $query->orWhereIn('idUtilisateur', $idsUtilisateursFamille);
            }
        })
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        // Enfants sans famille OU déjà dans cette famille
        $tousEnfants = Enfant::where(function ($query) use ($idsEnfantsFamille) {
            $query->where(function ($q) {
                $q->whereNull('idFamille')
                  ->orWhere('idFamille', 0);
            })
            ->orWhereIn('idEnfant', $idsEnfantsFamille);
        })
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

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
            'famille_id' => 'nullable|integer',
        ]);

        $query = $request->input('q', '');
        $familleId = $request->input('famille_id');

        if (trim((string) $query) === '') {
            return response()->json([]);
        }

        // Filtrer uniquement les utilisateurs ayant le rôle "parent" (ils peuvent avoir d'autres rôles aussi)
        $roleParent = Role::where('name', 'parent')->first();

        $idsUtilisateursFamille = [];
        if (!empty($familleId)) {
            $famille = Famille::with('utilisateurs')->find($familleId);
            if ($famille) {
                $idsUtilisateursFamille = $famille->utilisateurs->pluck('idUtilisateur')->toArray();
            }
        }

        // Un parent peut appartenir à plusieurs familles => recherche sur tous les parents.
        // On garde en plus ceux déjà liés à la famille (au cas où un utilisateur lié n'aurait pas le rôle parent).
        $users = Utilisateur::where(function ($q) use ($roleParent, $idsUtilisateursFamille) {
            if ($roleParent) {
                $q->whereHas('rolesCustom', function ($q3) use ($roleParent) {
                    $q3->where('role.idRole', $roleParent->idRole);
                });
            }

            if (!empty($idsUtilisateursFamille)) {
                $q->orWhereIn('idUtilisateur', $idsUtilisateursFamille);
            }
        })
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('nom', 'like', "%{$query}%")
                         ->orWhere('prenom', 'like', "%{$query}%");
                });
            })
            ->orderBy('nom')
            ->orderBy('prenom')
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


}

