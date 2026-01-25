<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Evenement
 *
 * @property int $idEvenement Identifiant de l'événement.
 * @property string $titre Titre de l'événement.
 * @property string $description Description détaillée.
 * @property bool $obligatoire Indique si l'événement est obligatoire.
 * @property Carbon $start_at Date et heure de début de l'événement.
 * @property Carbon|null $end_at Date et heure de fin de l'événement.e
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

	/**
	 * Attributs assignables (fillable) pour un événement.
	 *
	 * - `titre` (string) : titre de l'événement.
	 * - `description` (string) : description détaillée.
	 * - `obligatoire` (bool) : indique si l'événement est obligatoire.
	 * - `start_at` (datetime) : date et heure de début de l'événement.
	 * - `end_at` (datetime) : date et heure de fin de l'événement.
	 */
	protected $fillable = [
		'titre',
		'description',
		'obligatoire',
		'start_at',
		'end_at',
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

	/**
	 * Rôles associés à l'événement (pivot `evenement_role`).
	 */
	public function roles()
	{
		return $this->belongsToMany(Role::class, 'evenement_role', 'idEvenement', 'idRole');
	}
}
