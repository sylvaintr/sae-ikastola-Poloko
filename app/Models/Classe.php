<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Classe
 * 
 * @property int $idClasse Identifiant de la classe.
 * @property string $nom Nom de la classe (ex: "3A").
 * @property string $niveau Niveau ou cycle (ex: "CE2", "CM1").
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

	public function enfants()
	{
		return $this->hasMany(Enfant::class, 'idClasse', 'idClasse');
	}
}
