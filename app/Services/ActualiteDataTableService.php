<?php

namespace App\Services;

use App\Models\Actualite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * Service for handling Actualite DataTable operations.
 */
class ActualiteDataTableService
{
    /**
     * Apply simple filters to the query
     */
    public function applySimpleFilters(Request $request, $query): void
    {
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('etat')) {
            $archive = $request->input('etat') !== 'active';
            $query->where('archive', $archive);
        }
        if ($request->filled('etiquette')) {
            $ids = array_map('intval', (array) $request->input('etiquette'));
            $query->whereHas('etiquettes', function ($q) use ($ids) {
                $q->whereIn('etiquette.idEtiquette', $ids);
            });
        }
    }

    /**
     * Apply column filters when called directly with a Request
     */
    public function applyColumnFilters(Request $request, $query): void
    {
        $columns = (array) $request->input('columns', []);
        foreach ($columns as $column) {
            $columnName = $column['name'] ?? $column['data'] ?? null;
            $keyword = $column['search']['value'] ?? '';

            if ($keyword === '') {
                continue;
            }

            if ($columnName === 'titre') {
                $this->filterColumnTitre($query, $keyword);
            } elseif ($columnName === 'etiquettes') {
                $this->filterColumnEtiquettes($query, $keyword);
            }
        }
    }

    /**
     * Check if should return simple JSON response
     */
    public function shouldReturnSimpleJson(Request $request): bool
    {
        return ! $request->has('start') && ! $request->has('length');
    }

    /**
     * Build simple JSON response for tests
     */
    public function buildSimpleJsonResponse(Request $request, $query)
    {
        $rows = $query->get()->map(function ($actu) {
            return [
                'titre' => $actu->titrefr ?? 'Sans titre',
                'type' => $actu->type,
                'etiquettes' => $actu->etiquettes->pluck('nom')->join(', '),
            ];
        })->values();

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data' => $rows,
        ]);
    }

    /**
     * Filter titre column
     */
    public function filterColumnTitre($q, $keyword): void
    {
        $q->where(fn($sq) => $sq->where('titrefr', 'like', "%{$keyword}%")->orWhere('titreeus', 'like', "%{$keyword}%"));
    }

    /**
     * Filter etiquettes column
     */
    public function filterColumnEtiquettes($q, $keyword): void
    {
        $q->whereHas('etiquettes', fn($sq) => $sq->where('nom', 'like', "%{$keyword}%"));
    }

    /**
     * Build DataTables response
     */
    public function buildDataTablesResponse($query)
    {
        return DataTables::of($query)
            ->addColumn('titre', fn($actu) => $actu->titrefr ?? 'Sans titre')
            ->addColumn('etiquettes', fn($actu) => $actu->etiquettes->pluck('nom')->join(', '))
            ->addColumn('etat', fn($actu) => $actu->archive ? Lang::get('actualite.archived') : Lang::get('actualite.active'))
            ->addColumn('actions', fn($actu) => view('actualites.template.colonne-action', ['actualite' => $actu]))
            ->filterColumn('titre', [$this, 'filterColumnTitre'])
            ->filterColumn('etiquettes', [$this, 'filterColumnEtiquettes'])
            ->rawColumns(['actions'])
            ->make(true);
    }
}
