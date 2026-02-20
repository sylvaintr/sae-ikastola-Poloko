<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CalendrierController extends Controller
{
    private const STATUS_TERMINE = 'Terminé';
    private const ADMIN_PERMISSION = 'gerer-evenement';
    // format utilisé pour les labels de date sans heure
    private const DATE_LABEL_FORMAT = 'l d F Y';

    public function index()
    {
        return view('calendrier.index');
    }

    // FullCalendar appelle: /calendrier/events?start=...&end=...
    public function events(Request $request): JsonResponse
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $user = Auth::user();
        $userRoleIds = $user ? $user->rolesCustom()->pluck('role.idRole')->toArray() : [];
        $isAdmin = $user && $user->can(self::ADMIN_PERMISSION);

        $events = $this->getFilteredEvenements($isAdmin, $userRoleIds, $start, $end);
        $demandes = $this->getFilteredDemandes($isAdmin, $userRoleIds, $start, $end);
        $taches = $this->getFilteredTaches($isAdmin, $userRoleIds, $start, $end);

        return response()->json($events->concat($demandes)->concat($taches));
    }

    /**
     * Récupère les événements filtrés par rôles et dates.
     */
    private function getFilteredEvenements(bool $isAdmin, array $userRoleIds, ?string $start, ?string $end): Collection
    {
        $query = Evenement::query();

        if (!$isAdmin) {
            $this->applyRoleFilterToEvenements($query, $userRoleIds);
        }

        if ($start && $end) {
            $this->applyDateFilterToEvenements($query, $start, $end);
        }

        return $query->get()->map(fn(Evenement $e) => $this->mapEvenementToCalendar($e));
    }

    /**
     * Applique le filtre par rôles aux événements.
     */
    private function applyRoleFilterToEvenements(Builder $query, array $userRoleIds): void
    {
        if (!empty($userRoleIds)) {
            $query->where(function ($q) use ($userRoleIds) {
                $q->whereHas('roles', fn($roleQuery) => $roleQuery->whereIn('role.idRole', $userRoleIds))
                    ->orWhereDoesntHave('roles');
            });
        } else {
            $query->whereDoesntHave('roles');
        }
    }

    /**
     * Applique le filtre par dates aux événements.
     */
    private function applyDateFilterToEvenements(Builder $query, string $start, string $end): void
    {
        $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_at', [$start, $end])
                ->orWhereBetween('end_at', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_at', '<=', $start)
                        ->where(fn($q3) => $q3->whereNull('end_at')->orWhere('end_at', '>=', $end));
                });
        });
    }

    /**
     * Mappe un événement au format FullCalendar.
     */
    private function mapEvenementToCalendar(Evenement $e): array
    {
        // Détecter si c'est un événement de journée entière (00:00 → 23:59)
        $isAllDay = $e->start_at && $e->end_at &&
            $e->start_at->format('H:i') === '00:00' &&
            $e->end_at->format('H:i') === '23:59';

        // Pour les événements all-day, FullCalendar utilise des dates exclusives
        // Donc on ajoute 1 jour à la date de fin
        $endDate = $e->end_at;
        if ($isAllDay && $endDate) {
            $endDate = $endDate->copy()->addDay()->startOfDay();
        }

        // Calculer la valeur 'end' pour éviter l'opération ternaire imbriquée
        $endValue = null;
        if ($isAllDay) {
            $endValue = $endDate ? $endDate->toDateString() : null;
        } else {
            $endValue = optional($e->end_at)->toISOString();
        }

        return [
            'id' => 'event-' . $e->idEvenement,
            'title' => $e->titre ?? 'Évènement',
            'start' => $isAllDay ? $e->start_at->toDateString() : optional($e->start_at)->toISOString(),
            'end' => $endValue,
            'allDay' => $isAllDay,
            'extendedProps' => [
                'type' => 'evenement',
                'description' => $e->description,
                'obligatoire' => (bool) $e->obligatoire,
                'startLabel' => $isAllDay
                    ? optional($e->start_at)->translatedFormat(self::DATE_LABEL_FORMAT)
                    : optional($e->start_at)->translatedFormat(self::DATE_LABEL_FORMAT . ' \\à H\\hi'),
                'endLabel' => $isAllDay && $e->end_at
                    ? $e->end_at->translatedFormat(self::DATE_LABEL_FORMAT)
                    : $e->end_at?->translatedFormat(self::DATE_LABEL_FORMAT . ' \\à H\\hi'),
            ],
        ];
    }

    /**
     * Récupère les demandes filtrées par rôles et dates.
     */
    private function getFilteredDemandes(bool $isAdmin, array $userRoleIds, ?string $start, ?string $end): Collection
    {
        $query = Tache::where('type', 'demande')->where('etat', '!=', self::STATUS_TERMINE);

        if (!$isAdmin) {
            $this->applyRoleFilterToDemandes($query, $userRoleIds);
        }

        if ($start && $end) {
            $this->applyDateFilterToDemandes($query, $start, $end);
        }

        return $query->get()->map(fn(Tache $d) => $this->mapDemandeToCalendar($d));
    }

    /**
     * Récupère les tâches filtrées par rôles et dates.
     */
    private function getFilteredTaches(bool $isAdmin, ?string $start, ?string $end): Collection
    {
        $query = Tache::where('type', 'tache')->where('etat', '!=', self::STATUS_TERMINE);

        if (!$isAdmin) {
            $this->applyRoleFilterToTaches($query);
        }

        if ($start && $end) {
            $this->applyDateFilterToDemandes($query, $start, $end);
        }

        return $query->get()->map(fn(Tache $t) => $this->mapTacheToCalendar($t));
    }

    /**
     * Applique le filtre par rôles aux demandes.
     */
    private function applyRoleFilterToDemandes(Builder $query, array $userRoleIds): void
    {
        if (!empty($userRoleIds)) {
            $query->where(function ($q) use ($userRoleIds) {
                $q->whereHas('roles', fn($rq) => $rq->whereIn('role.idRole', $userRoleIds))
                    ->orWhere(fn($q2) => $q2->whereDoesntHave('roles')
                        ->whereHas('evenement.roles', fn($rq) => $rq->whereIn('role.idRole', $userRoleIds)))
                    ->orWhere(fn($q2) => $q2->whereDoesntHave('roles')->whereNull('idEvenement'))
                    ->orWhere(fn($q2) => $q2->whereDoesntHave('roles')
                        ->whereHas('evenement', fn($eq) => $eq->whereDoesntHave('roles')));
            });
        } else {
            $query->where(function ($q) {
                $q->where(fn($q2) => $q2->whereDoesntHave('roles')->whereNull('idEvenement'))
                    ->orWhere(fn($q2) => $q2->whereDoesntHave('roles')
                        ->whereHas('evenement', fn($eq) => $eq->whereDoesntHave('roles')));
            });
        }
    }

    /**
     * Applique le filtre par dates aux demandes.
     */
    private function applyDateFilterToDemandes(Builder $query, string $start, string $end): void
    {
        $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('dateD', [$start, $end])
                ->orWhereBetween('dateF', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('dateD', '<=', $start)
                        ->where(fn($q3) => $q3->whereNull('dateF')->orWhere('dateF', '>=', $end));
                })
                ->orWhere(fn($q2) => $q2->whereNull('dateF')->where('dateD', '<=', $end));
        });
    }

    /**
     * Applique le filtre par rôles aux tâches (basé sur les réalisateurs).
     */
    private function applyRoleFilterToTaches(Builder $query): void
    {
        // Pour les tâches, on filtre par les réalisateurs assignés
        // Un utilisateur voit les tâches qui lui sont assignées
        $user = Auth::user();
        if ($user) {
            $query->whereHas('realisateurs', function ($q) use ($user) {
                $q->where('utilisateur.idUtilisateur', $user->idUtilisateur);
            });
        } else {
            // Si pas connecté, ne rien montrer
            $query->whereRaw('1 = 0');
        }
    }

    /**
     * Mappe une demande au format FullCalendar.
     */
    private function mapDemandeToCalendar(Tache $d): array
    {
        return [
            'id' => 'demande-' . $d->idTache,
            'title' => $d->titre ?? 'Demande',
            'start' => optional($d->dateD)->toDateString(),
            'end' => $d->dateF?->copy()->addDay()->toDateString(),
            'allDay' => true,
            'extendedProps' => [
                'type' => 'demande',
                'description' => $d->description,
                'urgence' => $d->urgence,
                'etat' => $d->etat,
                'startLabel' => optional($d->dateD)->translatedFormat(self::DATE_LABEL_FORMAT),
                'endLabel' => $d->dateF?->translatedFormat(self::DATE_LABEL_FORMAT),
            ],
        ];
    }

    /**
     * Mappe une tâche au format FullCalendar.
     */
    private function mapTacheToCalendar(Tache $t): array
    {
        return [
            'id' => 'tache-' . $t->idTache,
            'title' => $t->titre ?? 'Tâche',
            'start' => optional($t->dateD)->toDateString(),
            'end' => $t->dateF?->copy()->addDay()->toDateString(),
            'allDay' => true,
            'extendedProps' => [
                'type' => 'tache',
                'description' => $t->description,
                'urgence' => $t->urgence,
                'etat' => $t->etat,
                'startLabel' => optional($t->dateD)->translatedFormat(self::DATE_LABEL_FORMAT),
                'endLabel' => $t->dateF?->translatedFormat(self::DATE_LABEL_FORMAT),
            ],
        ];
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
