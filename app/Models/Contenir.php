<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Contenir
 * 
 * @property int $idUtilisateur Identifiant de l'utilisateur propriétaire / lié au document.
 * @property int $idDocument Identifiant du document contenu.
 *
 * @package App\Models
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

	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	public function document()
	{
		return $this->belongsTo(Document::class, 'idDocument');
	}
}
