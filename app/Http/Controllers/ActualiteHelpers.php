<?php

namespace App\Http\Controllers;

use App\Models\Actualite;

class ActualiteHelpers
{
    public function applyEtiquetteWhereIn($q, array $ids, string $column = '.idEtiquette')
    {
        $table = $q->getModel()->getTable();
        $q->whereIn($table . $column, $ids);
    }

    public function applyEtiquetteWhere($q, $id, string $column = '.idEtiquette')
    {
        $table = $q->getModel()->getTable();
        $q->where($table . $column, $id);
    }

    public function filterTitreColumn($query, string $keyword)
    {
        $like = "%{$keyword}%";
        $query->where(fn($sq) => $sq->where('titrefr', 'like', $like)->orWhere('titreeus', 'like', $like));
    }

    public function filterEtiquettesColumn($query, string $keyword)
    {
        $query->whereHas('etiquettes', fn($sq) => $sq->where('nom', 'like', "%{$keyword}%"));
    }

    public function columnTitre($actu)
    {
        return $actu->titrefr ?? 'Sans titre';
    }

    public function columnEtat($actu)
    {
        return $actu->archive ? 'Archivée' : 'Active';
    }

    public function columnEtiquettesText($actu)
    {
        return $actu->etiquettes->pluck('nom')->join(', ');
    }

    public function columnActionsHtml($actu)
    {
        return view('actualites.template.colonne-action', ['actualite' => $actu]);
    }

    public function filterColumnTitreCallback($query, string $keyword)
    {
        $this->filterTitreColumn($query, $keyword);
    }

    public function filterColumnEtiquettesCallback($query, string $keyword)
    {
        $this->filterEtiquettesColumn($query, $keyword);
    }
}
