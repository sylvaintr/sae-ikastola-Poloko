<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CalendrierController extends Controller
{
    private const STATUS_TERMINE = 'Terminé';

    public function index()
    {
        return view('calendrier.index');
    }

    // FullCalendar appelle: /calendrier/events?start=...&end=...
    public function events(Request $request): JsonResponse
    {
        $start = $request->query('start'); // ISO
        $end   = $request->query('end');   // ISO

        // --- Événements ---
        $eventQuery = Evenement::query();

        if ($start && $end) {
            // events qui chevauchent la fenêtre
            $eventQuery->where(function ($q) use ($start, $end) {
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

        $events = $eventQuery->get()->map(function (Evenement $e) {
            $startAt = $e->start_at; // cast Carbon
            $endAt   = $e->end_at;

            return [
                'id'    => 'event-' . $e->idEvenement,
                'title' => $e->titre ?? 'Évènement',
                'start' => optional($startAt)->toISOString(),
                'end'   => optional($endAt)->toISOString(), // peut être null
                'allDay' => false,

                'extendedProps' => [
                    'type'        => 'evenement',
                    'description' => $e->description,
                    'obligatoire' => (bool) $e->obligatoire,
                    'startLabel'  => optional($startAt)->translatedFormat('l d F Y \\à H\\hi'),
                    'endLabel'    => $endAt ? $endAt->translatedFormat('l d F Y \\à H\\hi') : null,
                ],
            ];
        });

        // --- Demandes non terminées ---
        $demandeQuery = Tache::where('etat', '!=', self::STATUS_TERMINE);

        if ($start && $end) {
            $demandeQuery->where(function ($q) use ($start, $end) {
                $q->whereBetween('dateD', [$start, $end])
                    ->orWhereBetween('dateF', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('dateD', '<=', $start)
                            ->where(function ($q3) use ($end) {
                                $q3->whereNull('dateF')
                                    ->orWhere('dateF', '>=', $end);
                            });
                    })
                    // Inclure aussi les demandes sans date de fin qui commencent avant la fin de la fenêtre
                    ->orWhere(function ($q2) use ($end) {
                        $q2->whereNull('dateF')
                            ->where('dateD', '<=', $end);
                    });
            });
        }

        $demandes = $demandeQuery->get()->map(function (Tache $d) {
            $startAt = $d->dateD;
            $endAt   = $d->dateF;

            return [
                'id'    => 'demande-' . $d->idTache,
                'title' => $d->titre ?? 'Demande',
                'start' => optional($startAt)->toDateString(), // Date seulement pour allDay
                'end'   => $endAt ? $endAt->addDay()->toDateString() : null, // FullCalendar: end est exclusif
                'allDay' => true,

                'extendedProps' => [
                    'type'        => 'demande',
                    'description' => $d->description,
                    'urgence'     => $d->urgence,
                    'etat'        => $d->etat,
                    'demandeType' => $d->type,
                    'startLabel'  => optional($startAt)->translatedFormat('l d F Y'),
                    'endLabel'    => $endAt ? $endAt->translatedFormat('l d F Y') : null,
                ],
            ];
        });

        // Fusionner événements et demandes
        $allEvents = $events->merge($demandes);

        return response()->json($allEvents);
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
