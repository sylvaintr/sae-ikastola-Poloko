<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Contenir
 * 
 * @property int $idUtilisateur
 * @property int $idDocument
 *
 * @package App\Models
 */
class Contenir extends Model
{
	protected $table = 'contenir';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idUtilisateur' => 'int',
		'idDocument' => 'int'
	];
}
