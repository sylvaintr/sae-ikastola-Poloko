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

    // FullCalendar appelle: /calendrier/events?start=...&end=...
    public function events(Request $request): JsonResponse
    {
        $start = $request->query('start'); // ISO
        $end   = $request->query('end');   // ISO

        $query = Evenement::query();

        if ($start && $end) {
            // events qui chevauchent la fenêtre
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_at', '<=', $start)
                            ->where(function ($q3) use ($end) {
                                $q3->whereNull('end_at')
                                    ->orWhere('end_at', '>=', $end);
                            });
                    });
            });
        }

        $events = $query->get()->map(function (Evenement $e) {
            $startAt = $e->start_at; // cast Carbon
            $endAt   = $e->end_at;

            return [
                'id'    => $e->idEvenement,
                'title' => $e->titre ?? 'Évènement',
                'start' => optional($startAt)->toISOString(),
                'end'   => optional($endAt)->toISOString(), // peut être null
                'allDay' => false,

                'extendedProps' => [
                    'description' => $e->description,
                    'obligatoire' => (bool) $e->obligatoire,
                    'startLabel'  => optional($startAt)->translatedFormat('l d F Y \\à H\\hi'),
                    'endLabel'    => $endAt ? $endAt->translatedFormat('l d F Y \\à H\\hi') : null,
                ],
            ];
        });

        return response()->json($events);
    }

    // Drag/drop & resize (si tu actives editable dans FullCalendar)
    public function update(Request $request, Evenement $evenement): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['nullable', 'date', 'after_or_equal:start'],
        ]);

        $evenement->start_at = $validated['start'];
        $evenement->end_at   = $validated['end'] ?? null;
        $evenement->save();

        return response()->json(['ok' => true]);
    }
}
