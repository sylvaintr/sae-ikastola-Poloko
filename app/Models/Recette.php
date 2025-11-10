<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Recette
 *
 * Représente une recette / recette de vente ou élément de recette lié à un événement.
 *
 * @package App\Models
 *
 * @property int $idRecette Identifiant de la recette.
 * @property string $description Description ou nom.
 * @property string $prix Prix (format string selon le schéma actuel).
 * @property string $quantite Quantité (stockée en string dans le modèle actuel).
 * @property int $idEvenement Identifiant de l'événement lié.
 */
class Recette extends Model
{
	use HasFactory;
	protected $table = 'recette';
	protected $primaryKey = 'idRecette';
	public $incrementing = true;
	protected $keyType = 'int';
	public $timestamps = false;

	protected $casts = [
		'idRecette' => 'int',
		'idEvenement' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour une recette.
	 *
	 * - `description` (string) : description ou nom de l'élément de recette.
	 * - `prix` (string) : prix (format string selon schéma actuel).
	 * - `quantite` (string) : quantité (format string selon schéma actuel).
	 * - `idEvenement` (int) : référence vers l'événement associé.
	 */
	protected $fillable = [
		'description',
		'prix',
		'quantite',
		'idEvenement'
	];

	/**
	 * Relation belongsTo vers l'événement associé à la recette.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function evenement()
	{
		return $this->belongsTo(Evenement::class, 'idEvenement');
	}
}
