<?php
namespace App\Http\Controllers;

use App\Models\Actualite;

/**
 * Classe d'aide pour les opérations liées aux actualités, notamment pour les filtres et les colonnes personnalisées dans les vues. Elle fournit des méthodes pour appliquer des filtres sur les titres et les étiquettes, ainsi que pour générer le contenu des colonnes dans les tableaux d'affichage des actualités.
 */
class ActualiteHelpers
{
    /**
     *  Méthode pour appliquer un filtre "whereIn" sur une colonne d'étiquette dans une requête Eloquent.
     * @param \Illuminate\Database\Eloquent\Builder $q Requête Eloquent à modifier
     * @param array $ids Liste des IDs d'étiquettes à inclure dans le filtre
     * @param string $column Nom de la colonne d'étiquette à filtrer (par défaut '.idEtiquette')
     * @return void
     */
    public function applyEtiquetteWhereIn($q, array $ids, string $column = '.idEtiquette'): void
    {
        $table = $q->getModel()->getTable();
        $q->whereIn($table . $column, $ids);
    }
    /**
     * Méthode pour appliquer un filtre "where" sur une colonne d'étiquette dans une requête Eloquent.
     * @param \Illuminate\Database\Eloquent\Builder $q Requête Eloquent à modifier
     * @param int $id ID de l'étiquette à inclure dans le filtre
     * @param string $column Nom de la colonne d'étiquette à filtrer (par défaut '.idEtiquette')
     * @return void
     */
    public function applyEtiquetteWhere($q, $id, string $column = '.idEtiquette'): void
    {
        $table = $q->getModel()->getTable();
        $q->where($table . $column, $id);
    }

    /**
     * Méthode pour appliquer un filtre "where" sur une colonne de titre dans une requête Eloquent, en utilisant une correspondance partielle (LIKE).
     * @param $query Requête Eloquent à modifier
     * @param string $keyword Mot-clé à rechercher dans la colonne de titre
     * @return void
     */
    public function filterTitreColumn($query, string $keyword): void
    {
        $like = "%{$keyword}%";
        $query->where(fn($sq) => $sq->where('titrefr', 'like', $like)->orWhere('titreeus', 'like', $like));
    }

    /**
     * Méthode pour appliquer un filtre "whereHas" sur une relation d'étiquettes dans une requête Eloquent, en recherchant les actualités qui ont des étiquettes correspondant à un mot-clé.
     * @param $query Requête Eloquent à modifier
     * @param string $keyword Mot-clé à rechercher dans les étiquettes associées
     * @return void
     */
    public function filterEtiquettesColumn($query, string $keyword): void
    {
        $query->whereHas('etiquettes', fn($sq) => $sq->where('nom', 'like', "%{$keyword}%"));
    }

    /**
     * Méthode pour générer le contenu de la colonne "Titre" dans les tableaux d'affichage des actualités, en affichant le titre en français ou un message par défaut si le titre est absent.
     * @param Actualite $actu Actualité pour laquelle générer le contenu de la colonne
     * @return string Contenu de la colonne "Titre" à afficher
     */
    public function columnTitre($actu): string
    {
        return $actu->titrefr ?? 'Sans titre';
    }

    /**
     * Méthode pour générer le contenu de la colonne "État" dans les tableaux d'affichage des actualités, en affichant "Archivée" ou "Active" en fonction de l'état d'archivage de l'actualité.
     * @param Actualite $actu Actualité pour laquelle générer le contenu de la colonne
     * @return string Contenu de la colonne "État" à afficher
     */
    public function columnEtat($actu): string
    {
        return $actu->archive ? 'Archivée' : 'Active';
    }

    /**
     * Méthode pour générer le contenu de la colonne "Étiquettes" dans les tableaux d'affichage des actualités, en affichant la liste des noms d'étiquettes associées à l'actualité, séparés par des virgules.
     * @param Actualite $actu Actualité pour laquelle générer le contenu de la colonne
     * @return string Contenu de la colonne "Étiquettes" à afficher
     */
    public function columnEtiquettesText($actu): string
    {
        return $actu->etiquettes->pluck('nom')->join(', ');
    }

    /**
     * Méthode pour générer le contenu de la colonne "Actions" dans les tableaux d'affichage des actualités, en affichant les boutons d'action correspondants à l'actualité.
     * @param Actualite $actu Actualité pour laquelle générer le contenu de la colonne
     * @return string Contenu de la colonne "Actions" à afficher (HTML des boutons d'action)
     */
    public function columnActionsHtml($actu): string
    {
        return view('actualites.template.colonne-action', ['actualite' => $actu]);
    }

    /**
     * Méthode de rappel pour appliquer le filtre sur la colonne "Titre" dans les tableaux d'affichage des actualités, en utilisant la méthode filterTitreColumn.
     * @param $query Requête Eloquent à modifier
     * @param string $keyword Mot-clé à rechercher dans la colonne de titre
     * @return void
     */
    public function filterColumnTitreCallback($query, string $keyword): void
    {
        $this->filterTitreColumn($query, $keyword);
    }

    /**
     * Méthode de rappel pour appliquer le filtre sur la colonne "Étiquettes" dans les tableaux d'affichage des actualités, en utilisant la méthode filterEtiquettesColumn.
     * @param $query Requête Eloquent à modifier
     * @param string $keyword Mot-clé à rechercher dans les étiquettes associées
     * @return void
     */
    public function filterColumnEtiquettesCallback($query, string $keyword): void
    {
        $this->filterEtiquettesColumn($query, $keyword);
    }
}
