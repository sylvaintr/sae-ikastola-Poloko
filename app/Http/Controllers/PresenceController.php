<?php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Pratiquer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{
    /**
     * Methode pour obtenir la liste des classes
     * @return JsonResponse la réponse JSON contenant la liste des classes
     */
    public function classes(): JsonResponse
    {
        $classes = Classe::query()
            ->orderBy('nom')
            ->get(['idClasse', 'nom', 'niveau']);

        return response()->json($classes);
    }

    /**
     * Methode pour obtenir la liste des élèves pour une classe donnée
     * @param Request $request la requête HTTP contenant le paramètre 'classe_id'
     * @return JsonResponse la réponse JSON contenant la liste des élèves
     */
    public function students(Request $request): JsonResponse
    {
        $classIds = $this->extractClassIds($request);
        if (empty($classIds)) {
            return response()->json([], 200);
        }

        $students = Enfant::query()
            ->join('classe', 'classe.idClasse', '=', 'enfant.idClasse')
            ->whereIn('enfant.idClasse', $classIds)
            ->orderBy('classe.nom')
            ->orderBy('enfant.prenom')
            ->orderBy('enfant.nom')
            ->get([
                'enfant.idEnfant',
                'enfant.prenom',
                'enfant.nom',
                'classe.idClasse as classe_id',
                'classe.nom as classe_nom',
            ]);

        return response()->json($students);
    }

    /**
     * Methode pour obtenir la liste des identifiants des élèves présents pour une date/activité et une classe données
     * @param Request $request la requête HTTP contenant les paramètres 'classe_id', 'date' et 'activite'
     * @return JsonResponse la réponse JSON contenant la liste des identifiants des élèves présents
     */
    public function status(Request $request): JsonResponse
    {
        $classIds = $this->extractClassIds($request);
        $date     = $request->query('date');
        $activite = (string) $request->query('activite', 'cantine');
        if (empty($classIds) || ! $date) {
            return response()->json(['presentIds' => []]);
        }

        $presentIds = Pratiquer::query()
            ->join('enfant', 'enfant.idEnfant', '=', 'pratiquer.idEnfant')
            ->whereIn('enfant.idClasse', $classIds)
            ->whereDate('pratiquer.dateP', $date)
            ->where('pratiquer.activite', $activite)
            ->pluck('pratiquer.idEnfant')
            ->all();

        return response()->json(['presentIds' => $presentIds]);
    }

    /**
     * Methode pour enregistrer en masse le statut de présence pour une date/activité donnée
     * @param Request $request la requête HTTP contenant les données de présence
     * @return JsonResponse la réponse JSON indiquant le statut de l'opération
     */
    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date'             => ['required', 'date'],
            'activite'         => ['required', 'string'],
            'items'            => ['required', 'array'],
            'items.*.idEnfant' => ['required', 'integer'],
            'items.*.present'  => ['required', 'boolean'],
        ]);

        $date     = $data['date'];
        $activite = $data['activite'];
        $items    = $data['items'];

        $enfantIds  = collect($items)->pluck('idEnfant')->all();
        $presentIds = collect($items)->filter(fn($i) => $i['present'])->pluck('idEnfant')->all();

        DB::transaction(function () use ($date, $activite, $enfantIds, $presentIds) {
            // Remove existing records for these enfants on this date/activity
            Pratiquer::query()
                ->whereIn('idEnfant', $enfantIds)
                ->whereDate('dateP', $date)
                ->where('activite', $activite)
                ->delete();

            if (! empty($presentIds)) {
                $rows = array_map(function ($id) use ($date, $activite) {
                    return [
                        'idEnfant' => $id,
                        'activite' => $activite,
                        'dateP'    => $date,
                    ];
                }, $presentIds);
                // Insert presence rows
                Pratiquer::query()->insert($rows);
            }
        });

        return response()->json(['status' => 'ok']);
    }
    /**
     * Extrait la liste des identifiants de classes depuis la requête en
     * acceptant à la fois `classe_id` (legacy) et `classe_ids[]`.
     *
     * @param Request $request
     * @return array<int>
     */
    private function extractClassIds(Request $request): array
    {
        $raw = $request->query('classe_ids', []);
        if (! is_array($raw)) {
            $raw = [$raw];
        }
        if ($request->query->has('classe_id')) {
            $raw[] = $request->query('classe_id');
        }

        return collect($raw)
            ->flatMap(function ($value) {
                if (is_array($value)) {
                    return $value;
                }
                return explode(',', (string) $value);
            })
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
