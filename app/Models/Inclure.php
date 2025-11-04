<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Inclure
 * 
 * @property int $idEvenement
 * @property int $idMateriel
 *
 * @package App\Models
 */
class Inclure extends Model
{
	protected $table = 'inclure';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEvenement' => 'int',
		'idMateriel' => 'int'
	];
}
