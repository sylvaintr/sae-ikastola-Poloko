<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Avoir
 * 
 * @property int $idUtilisateur Identifiant de l'utilisateur.
 * @property int $idRole Identifiant du rôle attribué à l'utilisateur.
 * @property string $model_type Type du modèle (pour relation polymorphique).
 *
 * @package App\Models
 */
class Avoir extends Pivot
{
	protected $table = 'avoir';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idUtilisateur' => 'int',
		'idRole' => 'int'
	];

	protected $fillable = [
		'idUtilisateur',
		'idRole',
		'model_type'
	];

	/**
	 * Automatically set model_type when creating a new pivot record
	 */
	public static function boot()
	{
		parent::boot();

		static::creating(function ($avoir) {
			if (empty($avoir->model_type)) {
				$avoir->model_type = Utilisateur::class;
			}
		});
	}

	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	public function role()
	{
		return $this->belongsTo(Role::class, 'idRole');
	}
}
