<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Etiquette
 *
 * Représente une étiquette / catégorie utilisée pour taguer des actualités.
 *
 * @package App\Models
 *
 * @property int $idEtiquette Identifiant de l'étiquette.
 * @property string $nom Nom / libellé de l'étiquette.
 */
class Etiquette extends Model
{
	use HasFactory;
	protected $table = 'etiquette';
	protected $primaryKey = 'idEtiquette';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEtiquette' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour une étiquette.
	 *
	 * - `nom` (string) : nom / libellé de l'étiquette.
	 */
	protected $fillable = [
		'nom'
	];

	/**
	 * Relation belongsToMany vers les actualités associées à cette étiquette via la table pivot `correspondre`.
	 * 
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function actualites()
	{
		return $this->belongsToMany(Actualite::class, 'correspondre', 'idEtiquette', 'idActualite');
	}
}
