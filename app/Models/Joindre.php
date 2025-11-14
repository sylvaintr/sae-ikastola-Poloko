<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Joindre
 * 
 * @property int $idDocument Identifiant du document joint.
 * @property int $idActualite Identifiant de l'actualité à laquelle le document est attaché.
 *
 * @package App\Models
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

	public function document()
	{
		return $this->belongsTo(Document::class, 'idDocument');
	}

	public function actualite()
	{
		return $this->belongsTo(Actualite::class, 'idActualite');
	}
}
