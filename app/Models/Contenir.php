<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Contenir
 *
 * Pivot représentant la relation entre un `Utilisateur` et un `Document` (documents possédés / attachés à un utilisateur).
 *
 * @package App\Models
 *
 * @property int $idUtilisateur Identifiant de l'utilisateur propriétaire / lié au document.
 * @property int $idDocument Identifiant du document.
 */
class Contenir extends Pivot
{
	protected $table = 'contenir';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idUtilisateur' => 'int',
		'idDocument' => 'int'
	];

	/**
	 * Relation belongsTo vers l'utilisateur propriétaire / lié au document.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	/**
	 * Relation belongsTo vers le document.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function document()
	{
		return $this->belongsTo(Document::class, 'idDocument');
	}
}
