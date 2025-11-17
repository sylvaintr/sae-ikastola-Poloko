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
 * @property int $idEnfant Identifiant de l'enfant.
 * @property string $nom Nom de famille de l'enfant.
 * @property string $prenom Prénom de l'enfant.
 * @property Carbon $dateN Date de naissance.
 * @property string $sexe Sexe de l'enfant (ex: "M" / "F").
 * @property int $NNI Numéro national d'identification (ou numéro interne selon le projet).
 * @property int $idClasse Identifiant de la classe de l'enfant.
 * @property int $idFamille Identifiant de la famille / tuteur associé.
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
		'idEnfant',
		'nom',
		'prenom',
		'dateN',
		'sexe',
		'NNI',
		'idClasse',
		'idFamille'
	];

	public function classe()
	{
		return $this->belongsTo(Classe::class, 'idClasse');
	}

	public function famille()
	{
		return $this->belongsTo(Famille::class, 'idFamille');
	}
}
