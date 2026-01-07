<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Enfant
 *
 * Représente un enfant enregistré dans l'application (élève / participant).
 *
 * @package App\Models
 *
 * @property int $idEnfant Identifiant de l'enfant.
 * @property string $nom Nom de famille.
 * @property string $prenom Prénom.
 * @property Carbon $dateN Date de naissance.
 * @property string $sexe Sexe (ex: "M" / "F").
 * @property int $NNI Numéro national ou interne d'identification.
 * @property int $idClasse Identifiant de la classe.
 * @property int $idFamille Identifiant de la famille / tuteur associé.
 */
class Enfant extends Model
{
	use HasFactory;
	protected $table = 'enfant';
	protected $primaryKey = 'idEnfant';
	public $incrementing = true;
	protected $keyType = 'int';
	public $timestamps = false;

	protected $casts = [
		'idEnfant' => 'int',
		'dateN' => 'datetime',
		'NNI' => 'int',
		'nbFoisGarderie' => 'int',
		'idClasse' => 'int',
		'idFamille' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour un enfant.
	 *
	 * - `idEnfant` (int) : identifiant de l'enfant (si utilisé en assignation).
	 * - `nom` (string) : nom de famille.
	 * - `prenom` (string) : prénom.
	 * - `dateN` (datetime) : date de naissance.
	 * - `sexe` (string) : sexe (ex: "M" / "F").
	 * - `NNI` (int) : numéro national / interne d'identification.
	 * - `nbFoisGarderie` (int) : nombre de passages en garderie.
	 * - `idClasse` (int) : référence vers `Classe`.
	 * - `idFamille` (int) : référence vers `Famille`.
	 */
	protected $fillable = [
		'idEnfant',
		'nom',
		'prenom',
		'dateN',
		'sexe',
		'NNI',
		'nbFoisGarderie',
		'idClasse',
		'idFamille'
	];

	/**
	 * Relation belongsTo vers la classe de l'enfant.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function classe()
	{
		return $this->belongsTo(Classe::class, 'idClasse');
	}

	/**
	 * Relation belongsTo vers la famille / tuteur de l'enfant.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function famille()
	{
		return $this->belongsTo(Famille::class, 'idFamille');
	}
}
