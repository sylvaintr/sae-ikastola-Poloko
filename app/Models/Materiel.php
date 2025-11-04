<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Materiel
 * 
 * @property int $idMateriel
 * @property string $provenance
 * @property string $description
 *
 * @package App\Models
 */
class Materiel extends Model
{
	protected $table = 'materiel';
	protected $primaryKey = 'idMateriel';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idMateriel' => 'int'
	];

	protected $fillable = [
		'provenance',
		'description'
	];
}
