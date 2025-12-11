<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Inclure
 *
 * Pivot indiquant quels matériels sont inclus dans un événement.
 *
 * @package App\Models
 *
 * @property int $idEvenement Identifiant de l'événement.
 * @property int $idMateriel Identifiant du matériel inclus.
 */
class Inclure extends Pivot
{
	protected $table = 'inclure';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEvenement' => 'int',
		'idMateriel' => 'int'
	];

	/**
	 * Relation belongsTo vers l'événement.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function evenement()
	{
		return $this->belongsTo(Evenement::class, 'idEvenement');
	}

	/**
	 * Relation belongsTo vers le matériel inclus.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function materiel()
	{
		return $this->belongsTo(Materiel::class, 'idMateriel');
	}
}
