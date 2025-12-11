<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Joindre
 *
 * Pivot reliant un `Document` à une `Actualite` (documents joints aux actualités).
 *
 * @package App\Models
 *
 * @property int $idDocument Identifiant du document joint.
 * @property int $idActualite Identifiant de l'actualité associée.
 */
class Joindre extends Pivot
{
	protected $table = 'joindre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idDocument' => 'int',
		'idActualite' => 'int'
	];

	/**
	 * Relation belongsTo vers le document joint.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function document()
	{
		return $this->belongsTo(Document::class, 'idDocument');
	}

	/**
	 * Relation belongsTo vers l'actualité associée.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function actualite()
	{
		return $this->belongsTo(Actualite::class, 'idActualite');
	}
}
