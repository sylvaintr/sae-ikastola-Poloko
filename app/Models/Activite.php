<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Activite
 * 
 * @property string $activite
 * @property Carbon $dateP
 *
 * @package App\Models
 */
class Activite extends Model
{
	protected $table = 'activite';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'dateP' => 'datetime'
	];
}
