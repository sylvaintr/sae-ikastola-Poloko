<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Evenement
 * 
 * @property int $idEvenement
 * @property string $titre
 * @property string $description
 * @property bool $obligatoire
 * @property Carbon $dateE
 *
 * @package App\Models
 */
class Evenement extends Model
{
	protected $table = 'evenement';
	protected $primaryKey = 'idEvenement';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEvenement' => 'int',
		'obligatoire' => 'bool',
		'dateE' => 'datetime'
	];

	protected $fillable = [
		'titre',
		'description',
		'obligatoire',
		'dateE'
	];
}
