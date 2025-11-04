<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Realiser
 * 
 * @property int $idUtilisateur
 * @property int $idTache
 * @property Carbon|null $dateM
 * @property string|null $description
 *
 * @package App\Models
 */
class Realiser extends Model
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
}
