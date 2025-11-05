<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Attribuer
 * 
 * @property int $idRole Identifiant du rôle attribué.
 * @property int $idDocumentObligatoire Identifiant du document obligatoire associé au rôle.
 *
 * @package App\Models
 */
class Attribuer extends Model
{
	protected $table = 'attribuer';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idRole' => 'int',
		'idDocumentObligatoire' => 'int'
	];

	public function role()
	{
		return $this->belongsTo(Role::class, 'idRole');
	}

	public function documentObligatoire()
	{
		return $this->belongsTo(DocumentObligatoire::class, 'idDocumentObligatoire');
	}
}
