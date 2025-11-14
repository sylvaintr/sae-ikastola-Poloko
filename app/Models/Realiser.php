<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Realiser
 * 
 * @property int $idUtilisateur Identifiant de l'utilisateur qui réalise la tâche.
 * @property int $idTache Identifiant de la tâche réalisée.
 * @property Carbon|null $dateM Date de réalisation ou modification (peut être nulle).
 * @property string|null $description Description du travail réalisé (optionnel).
 *
 * @package App\Models
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

	protected $fillable = [
		'dateM',
		'description'
	];

	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	public function tache()
	{
		return $this->belongsTo(Tache::class, 'idTache');
	}
}
