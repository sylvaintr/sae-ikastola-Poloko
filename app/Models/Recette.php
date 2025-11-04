<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Recette
 * 
 * @property int $idRecette
 * @property string $description
 * @property string $prix
 * @property string $quantite
 * @property int $idEvenement
 *
 * @package App\Models
 */
class Recette extends Model
{
	protected $table = 'recette';
	protected $primaryKey = 'idRecette';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idRecette' => 'int',
		'idEvenement' => 'int'
	];

	protected $fillable = [
		'description',
		'prix',
		'quantite',
		'idEvenement'
	];
}
