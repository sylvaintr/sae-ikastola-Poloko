<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Famille
 *
 * Représente une famille / tuteur regroupant des enfants et des contacts.
 *
 * @package App\Models
 *
 * @property int $idFamille Identifiant de la famille / tuteur.
 */
class Famille extends Model
{
	use HasFactory;
	protected $table = 'famille';
	protected $primaryKey = 'idFamille';
	public $incrementing = true;
	public $timestamps = false;

	protected $casts = [
		'idFamille' => 'int',
		'aineDansAutreSeaska' => 'bool'
	];

	/**
	 * Attributs assignables (fillable) pour la famille.
	 *
	 * - `aineDansAutreSeaska` (bool) : indique si l'aîné de la famille est dans une autre structure Seaska.
	 */
	protected $fillable = [
		'aineDansAutreSeaska'
	];

	/**
	 * Relation hasMany vers les enfants associés à cette famille.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function enfants()
	{
		return $this->hasMany(Enfant::class, 'idFamille', 'idFamille');
	}

	/**
	 * Relation hasMany vers les factures associées à cette famille.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function factures()
	{
		return $this->hasMany(Facture::class, 'idFamille');
	}

	/**
	 * Relation belongsToMany vers les utilisateurs (contacts) liés à cette famille via la table pivot `lier`.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function utilisateurs()
	{
		return $this->belongsToMany(Utilisateur::class, 'lier', 'idFamille', 'idUtilisateur')->withPivot('parite');
	}

	/**
	 * Accesseur pour obtenir l'identifiant de la famille.
	 * 
	 * @return int
	 */
	public function getIdAttribute()
	{
		return $this->getKey();
	}
}
