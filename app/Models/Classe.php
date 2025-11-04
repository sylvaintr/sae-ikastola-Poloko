<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Classe
 * 
 * @property int $idClasse
 * @property string $nom
 * @property string $niveau
 *
 * @package App\Models
 */
class Classe extends Model
{
	protected $table = 'classe';
	protected $primaryKey = 'idClasse';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idClasse' => 'int'
	];

	protected $fillable = [
		'nom',
		'niveau'
	];
}
