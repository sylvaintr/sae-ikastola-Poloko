<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Activite
 *
 * Modèle Eloquent représentant une activité planifiée ou proposée dans l'application.
 *
 * @package App\Models
 *
 * @property string $activite Identifiant / nom unique de l'activité (clé logique).
 * @property Carbon $dateP Date prévue / programmée de l'activité.
 */
class Activite extends Model
{
    use HasFactory;
    protected $table     = 'activite';
    public $incrementing = false;
    public $timestamps   = false;

    protected $casts = [
        'dateP' => 'datetime',
    ];
    /**
     * Indique les attributs qui sont assignables en masse. Dans ce cas, les champs "activite" et "dateP" peuvent être remplis en utilisant des méthodes d'assignation de masse telles que create() ou fill(). Cela permet de protéger contre les assignations de masse non intentionnelles en limitant les champs qui peuvent être remplis de cette manière.
     * -activite : Il s'agit d'un champ de type string qui représente le nom ou l'identifiant de l'activité. C'est une partie de la clé primaire du modèle, ce qui signifie qu'il doit être unique pour chaque enregistrement d'activité.
     * -dateP : Il s'agit d'un champ de type date qui représente la date
     */
    protected $fillable   = ['activite', 'dateP'];
    protected $primaryKey = ['activite', 'dateP'];

    /**
     * Relation hasMany vers les enregistrements `Pratiquer` (présences/inscriptions).
     * La colonne locale `activite` (string) correspond à `Pratiquer.activite`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pratiquers()
    {
        return $this->hasMany(Pratiquer::class, 'activite', 'activite');
    }
}
