<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Lier
 * 
 * @property int $idUtilisateur
 * @property int $idFamille
 * @property string|null $parite
 *
 * @package App\Models
 */
class Lier extends Model
{
	protected $table = 'lier';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idUtilisateur' => 'int',
		'idFamille' => 'int'
	];

	protected $fillable = [
		'parite'
	];
}
