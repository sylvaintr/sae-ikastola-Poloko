<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Attribuer
 * 
 * @property int $idRole
 * @property int $idDocumentObligatoire
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
}
