<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Role;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvenementController extends Controller
{
    private const DATE_FORMAT_CSV = 'd/m/Y';

    /**
     * Afficher tous les événements
     */
    public function index(Request $request)
    {
        $query = Evenement::query();

        // Recherche par titre ou ID
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('idEvenement', $search);
            });
        }

        // Tri dynamique
        $sort = $request->input('sort', 'id_desc');
        $allowedSorts = [
            'id_desc' => ['idEvenement', 'desc'],
            'id_asc' => ['idEvenement', 'asc'],
            'date_desc' => ['start_at', 'desc'],
            'date_asc'  => ['start_at', 'asc'],
        ];

        if (! array_key_exists($sort, $allowedSorts)) {
            $sort = 'id_desc';
        }

        [$column, $direction] = $allowedSorts[$sort];

        $evenements = $query->orderBy($column, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('evenements.index', compact('evenements', 'sort'));
    }


    /**
     * Formulaire de création
     */
    public function create()
    {
        $roles = Role::query()->orderBy('name')->get();
        return view('evenements.create', compact('roles'));
    }

    /**
     * Enregistrement d'un événement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'obligatoire' => ['nullable', 'boolean'],

            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],

            'roles' => ['required', 'array', 'min:1', 'max:50'],
            'roles.*' => ['integer', 'exists:role,idRole'],
        ]);

        // Sanitization contre XSS
        $titre = strip_tags($validated['titre']);
        $description = strip_tags($validated['description']);

        $evenement = Evenement::create([
            'titre' => $titre,
            'description' => $description,
            'obligatoire' => (bool)($validated['obligatoire'] ?? false),

            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'] ?? null,
        ]);

        $evenement->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('evenements.index')
            ->with('success', 'Événement créé avec succès');
    }

    /**
     * Afficher un événement
     */
    public function show($id)
    {
        $evenement = Evenement::with(['roles', 'demandes.roles', 'demandes.historiques'])->findOrFail($id);

        // Calculer les dépenses des demandes liées
        $demandesDepenses = $evenement->demandes->sum(function ($demande) {
            return $demande->historiques->sum('depense');
        });

        return view('evenements.show', compact('evenement', 'demandesDepenses'));
    }

    /**
     * Formulaire d’édition
     */
    public function edit($id)
    {
        $evenement = Evenement::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('evenements.edit', compact('evenement', 'roles'));
    }

    /**
     * Mise à jour d'un événement
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'obligatoire' => ['nullable', 'boolean'],

            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],

            'roles' => ['required', 'array', 'min:1', 'max:50'],
            'roles.*' => ['integer', 'exists:role,idRole'],
        ]);

        $evenement = Evenement::findOrFail($id);

        // Sanitization contre XSS
        $titre = strip_tags($validated['titre']);
        $description = strip_tags($validated['description']);

        // Détecter si les dates ont changé pour synchroniser les demandes
        $datesChanged = $evenement->start_at != $validated['start_at'] ||
                        $evenement->end_at != $validated['end_at'];

        $evenement->update([
            'titre' => $titre,
            'description' => $description,
            'obligatoire' => (bool)($validated['obligatoire'] ?? false),

            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'] ?? null,
        ]);

        $evenement->roles()->sync($validated['roles'] ?? []);

        // Synchroniser les dates avec les demandes liées si les dates ont changé
        if ($datesChanged) {
            $evenement->demandes()->update([
                'dateD' => $validated['start_at'],
                'dateF' => $validated['end_at'] ?? null,
            ]);
        }

        return redirect()->route('evenements.index')
            ->with('success', 'Événement mis à jour avec succès');
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        $evenement = Evenement::findOrFail($id);

        // Détacher les demandes liées (mettre idEvenement à NULL)
        $evenement->demandes()->update(['idEvenement' => null]);

        // Détacher les rôles
        $evenement->roles()->detach();

        // Supprimer l'événement
        $evenement->delete();

        return redirect()->route('evenements.index')
            ->with('success', 'Événement supprimé avec succès');
    }

    /**
     * Exporte tous les événements filtrés en CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Evenement::with(['recettes', 'roles', 'demandes.historiques']);

        // Recherche par titre ou ID
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('idEvenement', $search);
            });
        }

        // Tri dynamique
        $sort = $request->input('sort', 'id_desc');
        $allowedSorts = [
            'id_desc' => ['idEvenement', 'desc'],
            'id_asc' => ['idEvenement', 'asc'],
            'date_desc' => ['start_at', 'desc'],
            'date_asc'  => ['start_at', 'asc'],
        ];

        if (! array_key_exists($sort, $allowedSorts)) {
            $sort = 'id_desc';
        }

        [$column, $direction] = $allowedSorts[$sort];
        $evenements = $query->orderBy($column, $direction)->get();

        $filename = 'evenements_' . date('Y-m-d') . '.csv';
        $headers = $this->buildCsvHeaders($filename);

        $callback = function () use ($evenements) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            // En-têtes du tableau principal
            fputcsv($file, [
                __('evenements.export.id'),
                __('evenements.export.titre'),
                __('evenements.export.description'),
                __('evenements.export.start_at'),
                __('evenements.export.end_at'),
                __('evenements.export.obligatoire'),
                __('evenements.export.roles'),
                __('evenements.export.total_recettes'),
                __('evenements.export.total_depenses_prev'),
                __('evenements.export.total_depenses'),
                'Dépenses demandes',
                'Total dépenses (avec demandes)',
                'Balance',
            ], ';');

            foreach ($evenements as $evenement) {
                $totalRecettes = $evenement->recettes
                    ->where('type', 'recette')
                    ->sum(fn($r) => $r->prix * $r->quantite);
                $totalDepensesPrev = $evenement->recettes
                    ->where('type', 'depense_previsionnelle')
                    ->sum(fn($r) => $r->prix * $r->quantite);
                $totalDepenses = $evenement->recettes
                    ->where('type', 'depense')
                    ->sum(fn($r) => $r->prix * $r->quantite);

                // Calculer les dépenses des demandes liées
                $demandesDepenses = $evenement->demandes->sum(function ($demande) {
                    return $demande->historiques->sum('depense');
                });

                $totalDepensesAvecDemandes = $totalDepenses + $demandesDepenses;
                $balance = $totalRecettes - $totalDepensesAvecDemandes;

                $roles = $evenement->roles->pluck('name')->join(', ');

                fputcsv($file, [
                    $evenement->idEvenement,
                    $evenement->titre,
                    $evenement->description ?? '—',
                    $this->formatDateForCsv($evenement->start_at),
                    $this->formatDateForCsv($evenement->end_at),
                    $evenement->obligatoire ? __('evenements.status_obligatoire') : __('evenements.status_optionnel'),
                    $roles ?: '—',
                    $this->formatMontantForCsv($totalRecettes),
                    $this->formatMontantForCsv($totalDepensesPrev),
                    $this->formatMontantForCsv($totalDepenses),
                    $this->formatMontantForCsv($demandesDepenses),
                    $this->formatMontantForCsv($totalDepensesAvecDemandes),
                    $this->formatMontantForCsv($balance),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporte un événement unique avec ses recettes détaillées.
     */
    public function exportCsv(Evenement $evenement): StreamedResponse
    {
        $evenement->loadMissing(['recettes', 'roles', 'demandes.historiques', 'demandes.roles']);

        $titreClean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $evenement->titre);
        $titreClean = preg_replace('/_+/', '_', $titreClean);
        $titreClean = trim($titreClean, '_');
        $filename = $titreClean . '_evenement_' . date('Y-m-d') . '.csv';

        $headers = $this->buildCsvHeaders($filename);

        $callback = function () use ($evenement) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            // Section événement
            fputcsv($file, [__('evenements.export.evenement_title')], ';');
            fputcsv($file, [], ';');
            fputcsv($file, [__('evenements.export.id'), $evenement->idEvenement], ';');
            fputcsv($file, [__('evenements.export.titre'), $evenement->titre], ';');
            fputcsv($file, [__('evenements.export.description'), $evenement->description ?? '—'], ';');
            fputcsv($file, [__('evenements.export.start_at'), $this->formatDateForCsv($evenement->start_at)], ';');
            fputcsv($file, [__('evenements.export.end_at'), $this->formatDateForCsv($evenement->end_at)], ';');
            fputcsv($file, [__('evenements.export.obligatoire'), $evenement->obligatoire ? __('evenements.status_obligatoire') : __('evenements.status_optionnel')], ';');

            $roles = $evenement->roles->pluck('name')->join(', ');
            fputcsv($file, [__('evenements.export.roles'), $roles ?: '—'], ';');
            fputcsv($file, [], ';');

            // Section comptabilité
            fputcsv($file, [__('evenements.export.comptabilite_title')], ';');
            fputcsv($file, [], ';');
            fputcsv($file, [
                __('evenements.export.type_col'),
                __('evenements.export.description_col'),
                __('evenements.export.prix'),
                __('evenements.export.quantite'),
                __('evenements.export.total'),
            ], ';');

            foreach ($evenement->recettes as $recette) {
                $typeLabel = match ($recette->type) {
                    'recette' => __('evenements.type_recette'),
                    'depense_previsionnelle' => __('evenements.type_depense_prev'),
                    'depense' => __('evenements.type_depense'),
                    default => $recette->type,
                };

                fputcsv($file, [
                    $typeLabel,
                    $recette->description ?? '—',
                    $this->formatMontantForCsv($recette->prix),
                    $recette->quantite ?? 1,
                    $this->formatMontantForCsv($recette->prix * ($recette->quantite ?? 1)),
                ], ';');
            }

            fputcsv($file, [], ';');

            // Section demandes liées
            if ($evenement->demandes->count() > 0) {
                fputcsv($file, [], ';');
                fputcsv($file, ['Demandes liées à cet événement'], ';');
                fputcsv($file, [], ';');
                fputcsv($file, [
                    'ID',
                    'Titre',
                    'Urgence',
                    'État',
                    'Dépenses réelles',
                    'Commissions',
                ], ';');

                foreach ($evenement->demandes as $demande) {
                    $totalDepenseDemande = $demande->historiques->sum('depense');
                    $commissions = $demande->roles->pluck('name')->join(', ');

                    fputcsv($file, [
                        $demande->idTache,
                        $demande->titre,
                        $demande->urgence ?? '—',
                        $demande->etat ?? '—',
                        $this->formatMontantForCsv($totalDepenseDemande),
                        $commissions ?: '—',
                    ], ';');
                }
                fputcsv($file, [], ';');
            }

            // Totaux
            $totalRecettes = $evenement->recettes
                ->where('type', 'recette')
                ->sum(fn($r) => $r->prix * $r->quantite);
            $totalDepensesPrev = $evenement->recettes
                ->where('type', 'depense_previsionnelle')
                ->sum(fn($r) => $r->prix * $r->quantite);
            $totalDepenses = $evenement->recettes
                ->where('type', 'depense')
                ->sum(fn($r) => $r->prix * $r->quantite);

            // Calculer les dépenses des demandes
            $demandesDepenses = $evenement->demandes->sum(function ($demande) {
                return $demande->historiques->sum('depense');
            });

            $totalDepensesAvecDemandes = $totalDepenses + $demandesDepenses;
            $balance = $totalRecettes - $totalDepensesAvecDemandes;

            fputcsv($file, [__('evenements.export.total_recettes'), $this->formatMontantForCsv($totalRecettes)], ';');
            fputcsv($file, [__('evenements.export.total_depenses_prev'), $this->formatMontantForCsv($totalDepensesPrev)], ';');
            fputcsv($file, [__('evenements.export.total_depenses'), $this->formatMontantForCsv($totalDepenses)], ';');

            if ($demandesDepenses > 0) {
                fputcsv($file, ['Dépenses des demandes', $this->formatMontantForCsv($demandesDepenses)], ';');
                fputcsv($file, ['Total dépenses (avec demandes)', $this->formatMontantForCsv($totalDepensesAvecDemandes)], ';');
                fputcsv($file, ['Balance', $this->formatMontantForCsv($balance)], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Construit les en-têtes HTTP pour le CSV.
     */
    private function buildCsvHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
            'Expires' => '0',
        ];
    }

    /**
     * Formate une date pour l'export CSV.
     */
    private function formatDateForCsv($date): string
    {
        return $date ? $date->format(self::DATE_FORMAT_CSV) : '—';
    }

    /**
     * Formate un montant pour l'export CSV.
     */
    private function formatMontantForCsv(?float $montant): string
    {
        if ($montant === null) {
            return '—';
        }
        return number_format($montant, 2, ',', ' ') . ' €';
    }
}
