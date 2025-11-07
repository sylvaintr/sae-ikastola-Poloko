<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Famille
 * @property int $idFamille Identifiant de la famille / tuteur (regroupe des enfants et contacts).
 * @package App\Models
 */
class Famille extends Model
{
	use HasFactory;
	protected $table = 'famille';
	protected $primaryKey = 'idFamille';
	public $incrementing = true;
	public $timestamps = false;
    protected $fillable = [
        'idFamille'
    ];
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

	/**
	 * Compatibility accessor: provide $famille->id mapping to the model primary key.
	 */
	public function getIdAttribute()
	{
		return $this->getKey();
	}
}
