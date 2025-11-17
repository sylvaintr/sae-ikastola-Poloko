<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Correspondre
 *
 * Pivot liant une `Actualite` à une `Etiquette` (catégorie / tag).
 *
 * @package App\Models
 *
 * @property int $idActualite Identifiant de l'actualité liée.
 * @property int $idEtiquette Identifiant de l'étiquette associée.
 */
class Correspondre extends Pivot
{
	protected $table = 'correspondre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idActualite' => 'int',
		'idEtiquette' => 'int'
	];

	/**
	 * Relation belongsTo vers l'actualité liée.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function actualite()
	{
		return $this->belongsTo(Actualite::class, 'idActualite');
	}

	/**	
	 * Relation belongsTo vers l'étiquette associée.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function etiquette()
	{
		return $this->belongsTo(Etiquette::class, 'idEtiquette');
	}
}
