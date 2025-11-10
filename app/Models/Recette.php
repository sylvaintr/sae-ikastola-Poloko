<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Recette
 * 
 * @property int $idRecette Identifiant de la recette.
 * @property string $description Description ou nom de la recette.
 * @property string $prix Prix (champ string selon le schéma actuel).
 * @property string $quantite Quantité (stockée comme string dans le modèle actuel).
 * @property int $idEvenement Identifiant de l'événement lié (ex: vente, cantine).
 *
 * @package App\Models
 */
class Recette extends Model
{
	protected $table = 'recette';
	protected $primaryKey = 'idRecette';
	public $incrementing = true;
	protected $keyType = 'int';
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

	public function evenement()
	{
		return $this->belongsTo(Evenement::class, 'idEvenement');
	}
}
