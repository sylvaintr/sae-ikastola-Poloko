<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Famille
 * 
 * @property int $idFamille
 *
 * @package App\Models
 */
class Famille extends Model
{
	protected $table = 'famille';
	protected $primaryKey = 'idFamille';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idFamille' => 'int'
	];
}
