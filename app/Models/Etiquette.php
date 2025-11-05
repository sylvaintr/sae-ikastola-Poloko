<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Etiquette
 * 
 * @property int $idEtiquette Identifiant de l'étiquette.
 * @property string $nom Nom / libellé de l'étiquette.
 *
 * @package App\Models
 */
class Etiquette extends Model
{
	protected $table = 'etiquette';
	protected $primaryKey = 'idEtiquette';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEtiquette' => 'int'
	];

	protected $fillable = [
		'nom'
	];

	/**
	 * Actualités associées via la table pivot `correspondre`.
	 */
	public function actualites()
	{
		return $this->belongsToMany(Actualite::class, 'correspondre', 'idEtiquette', 'idActualite');
	}
}
