<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Realiser
 *
 * Pivot indiquant qu'un `Utilisateur` a réalisé / participé à une `Tache`.
 *
 * @package App\Models
 *
 * @property int $idUtilisateur Identifiant de l'utilisateur.
 * @property int $idTache Identifiant de la tâche réalisée.
 * @property Carbon $dateM Date de réalisation.
 * @property string|null $description Description ou note associée.
 */
class Realiser extends Pivot
{
	protected $table = 'realiser';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idUtilisateur' => 'int',
		'idTache' => 'int',
		'dateM' => 'datetime'
	];

	/**
	 * Attributs assignables (fillable) pour le pivot realiser.
	 *
	 * - `dateM` (datetime) : date/heure de réalisation.
	 * - `description` (string|null) : commentaire ou description de la réalisation.
	 */
	protected $fillable = [
		'dateM',
		'description'
	];

	/**
	 * Relation belongsTo vers l'utilisateur qui a réalisé la tâche.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	/**
	 * Relation belongsTo vers la tâche réalisée.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function tache()
	{
		return $this->belongsTo(Tache::class, 'idTache');
	}
}
