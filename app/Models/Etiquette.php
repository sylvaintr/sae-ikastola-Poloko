<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Etiquette
 * 
 * @property int $idEtiquette
 * @property string $nom
 *
 * @package App\Models
 */
class Etiquette extends Model
{
	protected $table = 'etiquette';
	protected $primaryKey = 'idEtiquette';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idEtiquette' => 'int'
	];

	protected $fillable = [
		'nom'
	];
}
