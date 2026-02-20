<?php

namespace App\Services;

use App\Models\Actualite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Yajra\DataTables\Facades\DataTables;

class ActualiteDataTableService
{
    private ActualiteFilterService $filterService;

    public function __construct(ActualiteFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Construit les données pour DataTables.
     *
     * @param Request|null $request
     * @param callable $filterTitre
     * @param callable $filterEtiquettes
     * @return mixed
     */
    public function buildDataTable(?Request $request, callable $filterTitre, callable $filterEtiquettes)
    {
        $request = $request ?? request();
        $query = Actualite::query()->with('etiquettes');
        $filters = $this->filterService->extractFilters($request);
        $this->filterService->applyFilters($query, $filters);

        return DataTables::of($query)
            ->addColumn('titre', fn($actu) => $actu->titrefr ?? 'Sans titre')
            ->addColumn('etiquettes', fn($actu) => $actu->etiquettes->pluck('nom')->join(', '))
            ->addColumn('etat', fn($actu) => $actu->archive ? Lang::get('actualite.archived') : Lang::get('actualite.active'))
            ->addColumn('actions', fn($actu) => view('actualites.template.colonne-action', ['actualite' => $actu]))
            ->filterColumn('titre', $filterTitre)
            ->filterColumn('etiquettes', $filterEtiquettes)
            ->filterColumn('titre', [$this, 'filterColumnTitreCallback'])
            ->filterColumn('etiquettes', [$this, 'filterColumnEtiquettesCallback'])
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Filtre par titre (pour DataTables).
     *
     * @param Builder $q
     * @param string $keyword
     * @return void
     */
    public function filterColumnTitreInline($q, $keyword): void
    {
        $q->where(fn($sq) => $sq->where('titrefr', 'like', "%{$keyword}%")->orWhere('titreeus', 'like', "%{$keyword}%"));
    }

    /**
     * Filtre par étiquettes (pour DataTables).
     *
     * @param Builder $q
     * @param string $keyword
     * @return void
     */
    public function filterColumnEtiquettesInline($q, $keyword): void
    {
        $q->whereHas('etiquettes', fn($sq) => $sq->where('nom', 'like', "%{$keyword}%"));
    }

    /**
     * Callback wrapper pour filtres DataTables (compatibilité tests).
     */
    public function filterColumnTitreCallback($q, $keyword)
    {
        $this->filterColumnTitreInline($q, $keyword);
    }

    /**
     * Callback wrapper pour filtres DataTables (compatibilité tests).
     */
    public function filterColumnEtiquettesCallback($q, $keyword)
    {
        $this->filterColumnEtiquettesInline($q, $keyword);
    }
}
