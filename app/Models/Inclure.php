<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Inclure
 * 
 * @property int $idEvenement Identifiant de l'événement.
 * @property int $idMateriel Identifiant du matériel inclus pour l'événement.
 *
 * @package App\Models
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

	public function evenement()
	{
		return $this->belongsTo(Evenement::class, 'idEvenement');
	}

	public function materiel()
	{
		return $this->belongsTo(Materiel::class, 'idMateriel');
	}
}
