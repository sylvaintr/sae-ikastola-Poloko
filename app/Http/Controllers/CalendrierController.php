<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CalendrierController extends Controller
{
    private const STATUS_TERMINE = 'Terminé';
    private const ADMIN_ROLE = 'CA';

    public function index()
    {
        return view('calendrier.index');
    }

    // FullCalendar appelle: /calendrier/events?start=...&end=...
    public function events(Request $request): JsonResponse
    {
        $start = $request->query('start'); // ISO
        $end   = $request->query('end');   // ISO

        // Récupérer les rôles de l'utilisateur connecté
        $user = Auth::user();
        $userRoleIds = $user ? $user->rolesCustom()->pluck('role.idRole')->toArray() : [];

        // Vérifier si l'utilisateur est administrateur (CA)
        $isAdmin = $user && $user->hasRole(self::ADMIN_ROLE);

        // --- Événements ---
        $eventQuery = Evenement::query();

        // Si l'utilisateur est admin (CA), il voit tous les événements
        // Sinon, filtrer par rôles : afficher uniquement les événements
        // dont les rôles correspondent à ceux de l'utilisateur,
        // OU les événements sans rôles assignés (visibles par tous)
        if (!$isAdmin) {
            if (!empty($userRoleIds)) {
                $eventQuery->where(function ($q) use ($userRoleIds) {
                    $q->whereHas('roles', function ($roleQuery) use ($userRoleIds) {
                        $roleQuery->whereIn('role.idRole', $userRoleIds);
                    })->orWhereDoesntHave('roles');
                });
            } else {
                // Utilisateur sans rôles : afficher uniquement les événements sans rôles
                $eventQuery->whereDoesntHave('roles');
            }
        }

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

        // Si l'utilisateur est admin (CA), il voit toutes les demandes
        // Sinon, filtrer par rôles avec priorité :
        // 1. Si la demande a ses propres rôles → filtrer par ces rôles
        // 2. Sinon, fallback sur les rôles de l'événement associé
        // 3. Si ni la demande ni l'événement n'ont de rôles → visible par tous
        if (!$isAdmin) {
            if (!empty($userRoleIds)) {
                $demandeQuery->where(function ($q) use ($userRoleIds) {
                    // Demandes ayant leurs propres rôles correspondant à l'utilisateur
                    $q->whereHas('roles', function ($roleQuery) use ($userRoleIds) {
                        $roleQuery->whereIn('role.idRole', $userRoleIds);
                    })
                    // OU demandes sans rôles propres mais avec événement ayant des rôles correspondants
                    ->orWhere(function ($q2) use ($userRoleIds) {
                        $q2->whereDoesntHave('roles')
                            ->whereHas('evenement.roles', function ($roleQuery) use ($userRoleIds) {
                                $roleQuery->whereIn('role.idRole', $userRoleIds);
                            });
                    })
                    // OU demandes sans rôles propres et sans événement lié (orphelines)
                    ->orWhere(function ($q2) {
                        $q2->whereDoesntHave('roles')
                            ->whereNull('idEvenement');
                    })
                    // OU demandes sans rôles propres avec événement sans rôles (visible par tous)
                    ->orWhere(function ($q2) {
                        $q2->whereDoesntHave('roles')
                            ->whereHas('evenement', function ($eventQuery) {
                                $eventQuery->whereDoesntHave('roles');
                            });
                    });
                });
            } else {
                // Utilisateur sans rôles : afficher uniquement les demandes
                // sans rôles propres et (orphelines ou dont l'événement n'a pas de rôles)
                $demandeQuery->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereDoesntHave('roles')
                            ->whereNull('idEvenement');
                    })
                    ->orWhere(function ($q2) {
                        $q2->whereDoesntHave('roles')
                            ->whereHas('evenement', function ($eventQuery) {
                                $eventQuery->whereDoesntHave('roles');
                            });
                    });
                });
            }
        }

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
