<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Avoir
 * 
 * @property int $idUtilisateur Identifiant de l'utilisateur.
 * @property int $idRole Identifiant du rôle attribué à l'utilisateur.
 *
 * @package App\Models
 */
class Avoir extends Model
{
	protected $table = 'avoir';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idUtilisateur' => 'int',
		'idRole' => 'int'
	];

	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	public function role()
	{
		return $this->belongsTo(Role::class, 'idRole');
	}
}
