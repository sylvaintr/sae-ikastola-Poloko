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
     * Return all classes for the selector.
     */
    public function classes()
    {
        $classes = Classe::query()
            ->orderBy('nom')
            ->get(['idClasse', 'nom', 'niveau']);

        return response()->json($classes);
    }

    /**
     * Return students for a given class id.
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
     * Return present student ids for a given date/activity and class.
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
     * Save presence status in bulk for a given date/activity.
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


