<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Correspondre
 * 
 * @property int $idActualite
 * @property int $idEtiquette
 *
 * @package App\Models
 */
class Correspondre extends Model
{
	protected $table = 'correspondre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idActualite' => 'int',
		'idEtiquette' => 'int'
	];
}
