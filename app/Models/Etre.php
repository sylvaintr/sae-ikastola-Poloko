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
 * @property int $idEnfant
 * @property string $activite
 * @property Carbon $dateP
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
}
