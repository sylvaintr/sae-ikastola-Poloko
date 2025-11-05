<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Correspondre
 * 
 * @property int $idActualite Identifiant de l'actualité liée.
 * @property int $idEtiquette Identifiant de l'étiquette / catégorie associée.
 *
 * @package App\Models
 */
class Correspondre extends Model
{
	protected $table = 'correspondre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idActualite' => 'int',
		'idEtiquette' => 'int'
	];

	public function actualite()
	{
		return $this->belongsTo(Actualite::class, 'idActualite');
	}

	public function etiquette()
	{
		return $this->belongsTo(Etiquette::class, 'idEtiquette');
	}
}
