<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Etre
 *
 * Pivot représentant la présence / inscription d'un `Enfant` à une `Activite`.
 *
 * @package App\Models
 *
 * @property int $idEnfant Identifiant de l'enfant lié à l'inscription.
 * @property string $activite Référence à l'activité (clé `Activite.activite`).
 * @property Carbon $dateP Date de la présence / inscription.
 */
class Etre extends Pivot
{
	protected $table = 'etre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEnfant' => 'int',
		'dateP' => 'datetime'
	];

	/**
	 * Relation belongsTo vers l'enfant associé à cette inscription.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function enfant()
	{
		return $this->belongsTo(Enfant::class, 'idEnfant');
	}

	/**
	 * Relation belongsTo vers l'activité correspondante.
	 * La colonne locale `activite` référence `Activite.activite`.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function activite()
	{
		return $this->belongsTo(Activite::class, 'activite', 'activite');
	}
}
