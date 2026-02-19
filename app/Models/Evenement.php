<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
	protected $table = 'evenement';
	protected $primaryKey = 'idEvenement';
	public $incrementing = true;
	public $timestamps = false;

	protected $casts = [
		'idEvenement' => 'int',
		'obligatoire' => 'bool',
		'start_at' => 'datetime',
		'end_at' => 'datetime',
	];

	protected $fillable = [
		'titre',
		'description',
		'obligatoire',
		'start_at',
		'end_at',
	];

	/**
	 * Relation belongsToMany vers les rôles associés à cet événement (pivot `evenement_role`).
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function roles()
	{
		return $this->belongsToMany(Role::class, 'evenement_role', 'idEvenement', 'idRole');
	}

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
