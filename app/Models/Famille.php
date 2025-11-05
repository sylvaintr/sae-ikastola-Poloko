<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Famille
 * 
 * @property int $idFamille Identifiant de la famille / tuteur (regroupe des enfants et contacts).
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

	public function enfants()
	{
		return $this->hasMany(Enfant::class, 'idFamille', 'idFamille');
	}

	public function factures()
	{
		return $this->hasMany(Facture::class, 'idFamille');
	}

	public function utilisateurs()
	{
		return $this->belongsToMany(Utilisateur::class, 'lier', 'idFamille', 'idUtilisateur')->withPivot('parite');
	}
}
