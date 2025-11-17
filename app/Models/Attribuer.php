<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Attribuer
 *
 * Pivot représentant l'attribution d'un document obligatoire à un rôle.
 *
 * @package App\Models
 *
 * @property int $idRole Identifiant du rôle attribué.
 * @property int $idDocumentObligatoire Identifiant du document obligatoire.
 */
class Attribuer extends Pivot
{
	protected $table = 'attribuer';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idRole' => 'int',
		'idDocumentObligatoire' => 'int'
	];

	/**
	 * Relation belongsTo vers le rôle attribué.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function role()
	{
		return $this->belongsTo(Role::class, 'idRole');
	}

	/**
	 * Relation belongsTo vers le document obligatoire.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function documentObligatoire()
	{
		return $this->belongsTo(DocumentObligatoire::class, 'idDocumentObligatoire');
	}
}
