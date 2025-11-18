<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Etre;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    /**
     * Methode pour obtenir la liste des classes
     * @return \Illuminate\Http\JsonResponse la réponse JSON contenant la liste des classes
     */
    public function classes()
    {
        $classes = Classe::query()
            ->orderBy('nom')
            ->get(['idClasse', 'nom', 'niveau']);

        return response()->json($classes);
    }

    /**
     * Methode pour obtenir la liste des élèves pour une classe donnée
     * @param Request $request la requête HTTP contenant le paramètre 'classe_id'
     * @return \Illuminate\Http\JsonResponse la réponse JSON contenant la liste des élèves
     */
    public function students(Request $request)
    {
        $classId = (int) $request->query('classe_id');
        if (!$classId) {
            return response()->json([], 200);
        }

        $students = Enfant::query()
            ->where('idClasse', $classId)
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get(['idEnfant', 'prenom', 'nom']);

        return response()->json($students);
    }

    /**
     * Methode pour obtenir la liste des identifiants des élèves présents pour une date/activité et une classe données
     * @param Request $request la requête HTTP contenant les paramètres 'classe_id', 'date' et 'activite'
     * @return \Illuminate\Http\JsonResponse la réponse JSON contenant la liste des identifiants des élèves présents
     */
    public function status(Request $request)
    {
        $classId = (int) $request->query('classe_id');
        $date = $request->query('date');
        $activite = (string) $request->query('activite', 'cantine');
        if (!$classId || !$date) {
            return response()->json(['presentIds' => []]);
        }

        $presentIds = Etre::query()
            ->join('enfant', 'enfant.idEnfant', '=', 'etre.idEnfant')
            ->where('enfant.idClasse', $classId)
            ->whereDate('etre.dateP', $date)
            ->where('etre.activite', $activite)
            ->pluck('etre.idEnfant')
            ->all();

        return response()->json(['presentIds' => $presentIds]);
    }

    /**
     * Methode pour enregistrer en masse le statut de présence pour une date/activité donnée
     * @param Request $request la requête HTTP contenant les données de présence
     * @return \Illuminate\Http\JsonResponse la réponse JSON indiquant le statut de l'opération
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'activite' => ['required', 'string'],
            'items' => ['required', 'array'],
            'items.*.idEnfant' => ['required', 'integer'],
            'items.*.present' => ['required', 'boolean'],
        ]);

        $date = $data['date'];
        $activite = $data['activite'];
        $items = $data['items'];

        $enfantIds = collect($items)->pluck('idEnfant')->all();
        $presentIds = collect($items)->filter(fn($i) => $i['present'])->pluck('idEnfant')->all();

        DB::transaction(function () use ($date, $activite, $enfantIds, $presentIds) {
            // Remove existing records for these enfants on this date/activity
            Etre::query()
                ->whereIn('idEnfant', $enfantIds)
                ->whereDate('dateP', $date)
                ->where('activite', $activite)
                ->delete();

            if (!empty($presentIds)) {
                $rows = array_map(function ($id) use ($date, $activite) {
                    return [
                        'idEnfant' => $id,
                        'activite' => $activite,
                        'dateP' => $date,
                    ];
                }, $presentIds);
                // Insert presence rows
                Etre::query()->insert($rows);
            }
        });

        return response()->json(['status' => 'ok']);
    }
}
