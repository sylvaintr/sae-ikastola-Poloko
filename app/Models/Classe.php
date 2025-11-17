<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Classe
 *
 * Représente une classe scolaire (groupe d'enfants) dans l'application.
 *
 * @package App\Models
 *
 * @property int $idClasse Identifiant de la classe.
 * @property string $nom Nom de la classe (ex: "3A").
 * @property string $niveau Niveau ou cycle (ex: "CE2").
 */
class Classe extends Model
{
	use HasFactory;
	protected $table = 'classe';
	protected $primaryKey = 'idClasse';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idClasse' => 'int'
	];


	/**
	 * Attributs assignables (fillable) pour une classe.
	 *
	 * - `nom` (string) : nom de la classe (ex: "3A").
	 * - `niveau` (string) : niveau ou cycle (ex: "CE2").
	 */
	protected $fillable = [
		'nom',
		'niveau'
	];

	/**
	 * Relation hasMany vers les enfants appartenant à cette classe.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function enfants()
	{
		return $this->hasMany(Enfant::class, 'idClasse', 'idClasse');
	}
}
