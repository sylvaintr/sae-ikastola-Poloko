<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tache
 * 
 * @property int $idTache
 * @property string $titre
 * @property string $description
 * @property string $type
 * @property string $etat
 * @property Carbon|null $dateD
 * @property Carbon|null $dateF
 * @property float|null $montantP
 * @property float|null $montantR
 * @property int|null $idEvenement
 *
 * @package App\Models
 */
class Tache extends Model
{
	protected $table = 'tache';
	protected $primaryKey = 'idTache';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idTache' => 'int',
		'dateD' => 'datetime',
		'dateF' => 'datetime',
		'montantP' => 'float',
		'montantR' => 'float',
		'idEvenement' => 'int'
	];

	protected $fillable = [
		'titre',
		'description',
		'type',
		'etat',
		'dateD',
		'dateF',
		'montantP',
		'montantR',
		'idEvenement'
	];
}
