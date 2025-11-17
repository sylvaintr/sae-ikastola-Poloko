<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;

class FamilleController extends Controller
{
    private const FAMILLE_NOT_FOUND = 'Famille non trouvée';

    // -------------------- Ajout d'une famille avec ses parents et enfants --------------------
    public function ajouter(Request $request)
    {
        $data = $request->validate([
            'enfants' => 'array',
            'utilisateurs' => 'array',
        ]);

        $famille = Famille::create();

        // Création des enfants
        foreach ($data['enfants'] ?? [] as $enfant) {
            Enfant::create([
                'nom' => $enfant['nom'],
                'prenom' => $enfant['prenom'],
                'dateN' => $enfant['dateN'],
                'sexe' => $enfant['sexe'],
                'NNI' => $enfant['NNI'],
                'idClasse' => $enfant['idClasse'],
                'idFamille' => $famille->idFamille,
            ]);
        }

        // Lier les utilisateurs avec la famille
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

        $famille->load(['enfants', 'utilisateurs']);

        return response()->json([
            'message' => 'Famille complète créée avec succès',
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

        return response()->json($famille);
    }

    // -------------------- Afficher la liste des familles --------------------
    public function index()
    {
        $familles = Famille::with(['enfants', 'utilisateurs'])->get();
        return response()->json($familles);
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

    // -------------------- Modification d'une famille --------------------
    public function update(Request $request, $id)
    {
        $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

        if (!$famille) {
            return response()->json(['message' => self::FAMILLE_NOT_FOUND], 404);
        }

        // Mise à jour des enfants
        if ($request->has('enfants')) {
            foreach ($request->enfants as $enfantData) {
                $enfant = $famille->enfants()->find($enfantData['idEnfant'] ?? null);
                if ($enfant) {
                    $enfant->update([
                        'nom' => $enfantData['nom'] ?? $enfant->nom,
                        'prenom' => $enfantData['prenom'] ?? $enfant->prenom,
                        'dateN' => $enfantData['dateN'] ?? $enfant->dateN,
                        'sexe' => $enfantData['sexe'] ?? $enfant->sexe,
                        'idClasse' => $enfantData['idClasse'] ?? $enfant->idClasse,
                    ]);
                }
            }
        }

        // Mise à jour des utilisateurs (hors pivot)
        if ($request->has('utilisateurs')) {
            foreach ($request->utilisateurs as $userData) {
                $user = Utilisateur::find($userData['idUtilisateur'] ?? null);
                if ($user) {
                    $user->update([
                        'nom' => $userData['nom'] ?? $user->nom,
                        'prenom' => $userData['prenom'] ?? $user->prenom,
                        'languePref' => $userData['languePref'] ?? $user->languePref,
                    ]);
                }
            }
        }

        $famille->load(['enfants', 'utilisateurs']);

        return response()->json([
            'message' => 'Famille mise à jour (enfants + utilisateurs)',
            'famille' => $famille,
        ]);
    }
}
