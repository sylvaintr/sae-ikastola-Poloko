<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Evenement
 *
 * Représente un événement (assemblée, sortie, vente, etc.) lié à l'application.
 *
 * @package App\Models
 *
 * @property int $idEvenement Identifiant de l'événement.
 * @property string $titre Titre de l'événement.
 * @property string $description Description détaillée.
 * @property bool $obligatoire Indique si l'événement est obligatoire.
 * @property Carbon $dateE Date de l'événement.
 */
class Evenement extends Model
{
    use HasFactory;
    protected $table      = 'evenement';
    protected $primaryKey = 'idEvenement';
    public $incrementing  = true;
    public $timestamps    = false;

    protected $casts = [
        'idEvenement' => 'int',
        'obligatoire' => 'bool',
        'dateE'       => 'datetime',
    ];

    /**
     * Attributs assignables (fillable) pour un événement.
     *
     * - `titre` (string) : titre de l'événement.
     * - `description` (string) : description détaillée.
     * - `obligatoire` (bool) : indique si l'événement est obligatoire.
     * - `dateE` (datetime) : date de l'événement.
     */
    protected $fillable = [
        'titre',
        'description',
        'obligatoire',
        'dateE',
    ];

    /**
     * Relation hasMany vers les recettes associées à cet événement.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recettes()
    {
        return $this->hasMany(Recette::class, 'idEvenement');
    }

    /**
     * Relation hasMany vers les tâches associées à cet événement.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taches()
    {
        return $this->hasMany(Tache::class, 'idEvenement');
    }

    /**
     * Relation belongsToMany vers les matériels associés à cet événement via la table pivot `inclure`.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function materiels()
    {
        return $this->belongsToMany(Materiel::class, 'inclure', 'idEvenement', 'idMateriel');
    }
}
