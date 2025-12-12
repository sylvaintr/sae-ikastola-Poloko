<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;

class FamilleController extends Controller
{
    private const FAMILLE_NOT_FOUND = 'Famille non trouvée';

    // -------------------- Ajout d'une famille --------------------
    public function ajouter(Request $request)
    {
        $data = $request->validate([
            'enfants' => 'array',
            'utilisateurs' => 'array',
        ]);

        $famille = Famille::create();

        // Gestion des enfants
        foreach ($data['enfants'] ?? [] as $enfantData) {
            if (isset($enfantData['idEnfant'])) {
                Enfant::where('idEnfant', $enfantData['idEnfant'])
                      ->update(['idFamille' => $famille->idFamille]);
            } else {
                Enfant::create([
                    'nom' => $enfantData['nom'],
                    'prenom' => $enfantData['prenom'],
                    'dateN' => $enfantData['dateN'],
                    'sexe' => $enfantData['sexe'],
                    'NNI' => $enfantData['NNI'],
                    'idClasse' => $enfantData['idClasse'],
                    'idFamille' => $famille->idFamille,
                ]);
            }
        }

        // Gestion des utilisateurs
        foreach ($data['utilisateurs'] ?? [] as $userData) {
            if (isset($userData['idUtilisateur'])) {
                $famille->utilisateurs()->attach($userData['idUtilisateur'], [
                    'parite' => $userData['parite'] ?? null,
                ]);
            } else {
                $newUser = Utilisateur::create([
                    'nom' => $userData['nom'],
                    'prenom' => $userData['prenom'],
                    'mdp' => $userData['mdp'] ?? bcrypt('defaultpassword'),
                    'languePref' => $userData['languePref'] ?? 'fr',
                ]);
                $famille->utilisateurs()->attach($newUser->idUtilisateur, [
                    'parite' => $userData['parite'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Famille construite avec succès',
            'famille' => $famille,
        ], 201);
    }

    // -------------------- Afficher une famille spécifique --------------------
    public function show($id)
    {
        $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

        if (!$famille) {
            return response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
        }
        return view('admin.familles.show', compact('famille'));
    }

    // -------------------- Page de création --------------------
    public function create()
    {
        $tousUtilisateurs = Utilisateur::doesntHave('familles')->get();

        $tousEnfants = Enfant::whereNull('idFamille')
                             ->orWhere('idFamille', 0)
                             ->get();

        return view('admin.familles.create', compact('tousUtilisateurs', 'tousEnfants'));
    }

    // -------------------- Page de modification --------------------
    public function edit($id)
    {
        $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

        if (!$famille) {
            return redirect()->route('admin.familles.index');
        }

        return view('admin.familles.create', compact('famille'));
    }

    // -------------------- Afficher la liste des familles --------------------
    public function index()
    {
        $familles = Famille::with(['enfants', 'utilisateurs'])->get();
        return view('admin.familles.index', compact('familles'));
    }

    // -------------------- Supprimer une famille --------------------
    public function delete($id)
    {
        $famille = Famille::find($id);

        if (!$famille) {
            return response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
        }

        // Supprimer les enfants liés à la famille
        $famille->enfants()->delete();

        // Supprimer les liaisons avec les utilisateurs
        $famille->utilisateurs()->detach();

        // Supprimer la famille
        $famille->delete();

        return response()->json(['message' => 'Famille et enfants supprimés avec succès']);
    }

    // -------------------- Recherche par parent (SQL) --------------------
    public function searchByParent(Request $request)
    {
        $query = $request->input('q');

        $familles = Famille::with(['utilisateurs', 'enfants'])
            ->whereHas('utilisateurs', function ($q2) use ($query) {
                $q2->where('nom', 'like', "%{$query}%")
                   ->orWhere('prenom', 'like', "%{$query}%");
            })
            ->get();

        if ($familles->isEmpty()) {
            return response()->json(['message' => 'Aucune famille trouvée']);
        }

        return response()->json($familles);
    }

    // -------------------- Recherche AJAX Utilisateurs (Create) --------------------
    public function searchUsers(Request $request)
    {
        $query = $request->input('q');

        $users = Utilisateur::doesntHave('familles') // Toujours filtrer les non-liés
            ->where(function ($q) use ($query) {
                if ($query) {
                    $q->where('nom', 'like', "%{$query}%")
                      ->orWhere('prenom', 'like', "%{$query}%");
                }
            })
            ->limit(20)
            ->get();

        return response()->json($users);
    }
}