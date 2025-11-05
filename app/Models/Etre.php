<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Etre
 * 
 * @property int $idEnfant Identifiant de l'enfant lié à cette présence/inscription.
 * @property string $activite Référence à l'activité (valeur string correspondant à `Activite.activite`).
 * @property Carbon $dateP Date de la présence/inscription liée à l'activité.
 *
 * @package App\Models
 */
class Etre extends Model
{
	protected $table = 'etre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEnfant' => 'int',
		'dateP' => 'datetime'
	];

	public function enfant()
	{
		return $this->belongsTo(Enfant::class, 'idEnfant');
	}

	public function activite()
	{
		// The "activite" column stores the activity identifier (string). We map to Activite.activite
		return $this->belongsTo(Activite::class, 'activite', 'activite');
	}
}
