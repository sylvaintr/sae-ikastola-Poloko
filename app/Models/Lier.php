<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Lier
 * 
 * @property int $idUtilisateur Identifiant de l'utilisateur lié.
 * @property int $idFamille Identifiant de la famille liée.
 * @property string|null $parite Rôle / parité dans la famille (ex: parent, tuteur) — peut être nul.
 *
 * @package App\Models
 */
class Lier extends Model
{
	protected $table = 'lier';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idUtilisateur' => 'int',
		'idFamille' => 'int'
	];

	protected $fillable = [
		'parite'
	];

	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	public function famille()
	{
		return $this->belongsTo(Famille::class, 'idFamille');
	}
}
