<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CalendrierController extends Controller
{
    public function index()
    {
        return view('calendrier.index');
    }

    public function events(Request $request): JsonResponse
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $query = Evenement::query();

        if ($start && $end) {
            $query->whereBetween('dateE', [$start, $end]);
        }

        $events = $query->get()->map(function (Evenement $e) {
            return [
                'id'    => $e->idEvenement,
                'title' => $e->titre ?? 'Évènement',
                'start' => optional($e->dateE)->toISOString(),
                'allDay' => true,

                // Données supplémentaires pour le modal
                'extendedProps' => [
                    'description' => $e->description,
                    'obligatoire' => $e->obligatoire,
                    'date'        => optional($e->dateE)->translatedFormat('l d F Y'),
                ],
            ];
        });


        return response()->json($events);
    }

    public function update(Request $request, Evenement $evenement): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['nullable', 'date'],
        ]);

        $evenement->date_debut = $validated['start'];
        $evenement->date_fin   = $validated['end'] ?? null;
        $evenement->save();

        return response()->json(['ok' => true]);
    }
}
