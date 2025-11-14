<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Evenement
 * 
 * @property int $idEvenement Identifiant de l'événement.
 * @property string $titre Titre de l'événement.
 * @property string $description Description détaillée de l'événement.
 * @property bool $obligatoire Indique si l'événement est obligatoire.
 * @property Carbon $dateE Date à laquelle l'événement a lieu.
 *
 * @package App\Models
 */
class Evenement extends Model
{
	use HasFactory;
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

	public function recettes()
	{
		return $this->hasMany(Recette::class, 'idEvenement');
	}

	public function taches()
	{
		return $this->hasMany(Tache::class, 'idEvenement');
	}

	public function materiels()
	{
		return $this->belongsToMany(Materiel::class, 'inclure', 'idEvenement', 'idMateriel');
	}
}
