<?php

namespace App\Http\Controllers\Traits;

use App\Models\Tache;
use App\Models\DemandeHistorique;
use Illuminate\Support\Facades\Auth;

trait HandlesDemandeHistory
{
    /**
     * Charge une valeur depuis la base de données ou retourne une valeur par défaut.
     */
    protected function loadOrDefault(string $column, \Illuminate\Support\Collection $fallback): \Illuminate\Support\Collection
    {
        $values = Tache::select($column)
            ->where('type', 'demande')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter();

        return $values->isEmpty() ? $fallback : $values;
    }

    /**
     * Crée l'entrée d'historique initiale lors de la création d'une demande.
     */
    protected function storeInitialHistory(Tache $demande): void
    {
        $this->addHistoryEntry(
            $demande,
            __('demandes.history_statuses.created'),
            $demande->description
        );
    }

    /**
     * Ajoute une entrée dans l'historique de la demande.
     */
    protected function addHistoryEntry(Tache $demande, string $statut, ?string $description = null, ?float $depense = null): void
    {
        $user = Auth::user();

        DemandeHistorique::create([
            'idDemande' => $demande->idTache,
            'statut' => $statut,
            'titre' => $demande->titre,
            'responsable' => $user?->name ?? '',
            'depense' => $depense,
            'dateE' => now(),
            'description' => $description,
        ]);
    }
}
