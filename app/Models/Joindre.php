<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Joindre
 * 
 * @property int $idDocument
 * @property int $idActualite
 *
 * @package App\Models
 */
class Joindre extends Model
{
	protected $table = 'joindre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idDocument' => 'int',
		'idActualite' => 'int'
	];
}
