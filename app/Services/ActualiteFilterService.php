<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActualiteFilterService
{
    /**
     * Extrait les filtres de la requête.
     *
     * @param Request $request
     * @return array
     */
    public function extractFilters(Request $request): array
    {
        return [
            'type'      => $request->get('type', ''),
            'etat'      => $request->get('etat', ''),
            'etiquette' => $request->get('etiquette', ''),
            'search'    => $request->get('search', ''),
        ];
    }

    /**
     * Applique les filtres à la requête.
     *
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    public function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['etat'])) {
            $query->where('archive', $filters['etat'] !== 'active');
        }

        if (! empty($filters['etiquette'])) {
            $ids = array_map('intval', (array) $filters['etiquette']);
            $query->whereHas('etiquettes', fn($q) => $q->whereIn('etiquette.idEtiquette', $ids));
        }

        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('titrefr', 'like', $term)
                    ->orWhere('titreeus', 'like', $term);
            });
        }
    }
}
