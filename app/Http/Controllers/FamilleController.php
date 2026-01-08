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
    private const DEFAULT_PASSWORD = 'defaultpassword';

    // Ajout d'une famille
    public function ajouter(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enfants' => 'array',
            'utilisateurs' => 'array',
        ]);

        // Création de la famille
        $famille = Famille::create(['aineDansAutreSeaska' => false]);
        $this->createEnfants($data['enfants'] ?? [], $famille->idFamille);
        $this->createUtilisateurs($data['utilisateurs'] ?? [], $famille);

        $famille->load('enfants', 'utilisateurs');

        return response()->json([
            'message' => 'Famille construite avec succès',
            'famille' => $famille,
        ], 201);
    }

    // Affichage d'une famille
    public function show($id)
    {
        $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

        if (!$famille) {
            if (request()->wantsJson()) {
                return response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
            }
            return redirect()->route('admin.familles.index');
        }

        if (request()->wantsJson()) {
            return response()->json($famille);
        }

        return view('admin.familles.show', compact('famille'));
    }

    // Formulaire de création
    public function create(): View
    {
        $tousUtilisateurs = Utilisateur::doesntHave('familles')->get();

        $tousEnfants = Enfant::where(function ($query) {
            $query->whereNull('idFamille')
                  ->orWhere('idFamille', 0);
        })->get();

        return view('admin.familles.create', compact('tousUtilisateurs', 'tousEnfants'));
    }

    // Formulaire d'édition
    public function edit($id)
    {
        $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

        if (!$famille) {
            return redirect()->route('admin.familles.index');
        }

        return view('admin.familles.create', compact('famille'));
    }

    // Liste des familles
    public function index()
    {
        $familles = Famille::with(['enfants', 'utilisateurs'])->get();

        if (request()->wantsJson()) {
            return response()->json($familles);
        }

        return view('admin.familles.index', compact('familles'));
    }

    // Suppression d'une famille
    public function delete($id): JsonResponse
    {
        $famille = Famille::find($id);

        if (!$famille) {
            return response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
        }

        $famille->enfants()->delete();
        $famille->utilisateurs()->detach();
        $famille->delete();

        return response()->json(['message' => 'Famille et enfants supprimés avec succès']);
    }

    // Recherche de familles par parent
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

    // Recherche d'utilisateurs
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

    // Mise à jour d'une famille
    public function update(Request $request, int $id): JsonResponse
    {
        $famille = Famille::find($id);

        if ($famille === null) {
            return response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
        }

        $data = $request->validate([
            'enfants' => 'array',
            'utilisateurs' => 'array',
        ]);

        $this->updateEnfants($data['enfants'] ?? []);
        $this->updateUtilisateurs($data['utilisateurs'] ?? []);

        return response()->json([
            'message' => 'Famille mise à jour (enfants + utilisateurs)',
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
                $newUser = Utilisateur::create([
                    'nom' => $userData['nom'],
                    'prenom' => $userData['prenom'],
                    'mdp' => $userData['mdp'] ?? bcrypt(self::DEFAULT_PASSWORD),
                    'languePref' => $userData['languePref'] ?? 'fr',
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
                if (isset($childData['nom'])) {
                    $enfant->nom = $childData['nom'];
                }
                if (isset($childData['prenom'])) {
                    $enfant->prenom = $childData['prenom'];
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
                if (isset($userData['languePref'])) {
                    $utilisateur->languePref = $userData['languePref'];
                }
                $utilisateur->save();
            }
        }
    }
}

