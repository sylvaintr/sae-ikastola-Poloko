<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Enfant
 * 
 * @property int $idEnfant
 * @property string $nom
 * @property string $prenom
 * @property Carbon $dateN
 * @property string $sexe
 * @property int $NNI
 * @property int $idClasse
 * @property int $idFamille
 *
 * @package App\Models
 */
class Enfant extends Model
{
	protected $table = 'enfant';
	protected $primaryKey = 'idEnfant';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEnfant' => 'int',
		'dateN' => 'datetime',
		'NNI' => 'int',
		'idClasse' => 'int',
		'idFamille' => 'int'
	];

	protected $fillable = [
		'nom',
		'prenom',
		'dateN',
		'sexe',
		'NNI',
		'idClasse',
		'idFamille'
	];
}
