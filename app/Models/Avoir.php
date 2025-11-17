<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Avoir
 *
 * Pivot représentant l'association entre un utilisateur et un rôle (utilisé pour la gestion des rôles).
 *
 * @package App\Models
 *
 * @property int $idUtilisateur Identifiant de l'utilisateur.
 * @property int $idRole Identifiant du rôle.
 * @property string $model_type Type du modèle (pour relations polymorphiques si utilisé).
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

	/**
	 * Attributs assignables (fillable) pour le pivot avoir.
	 *
	 * - `idUtilisateur` (int) : identifiant de l'utilisateur.
	 * - `idRole` (int) : identifiant du rôle attribué.
	 * - `model_type` (string) : type du modèle pour relations polymorphiques.
	 */
	protected $fillable = [
		'idUtilisateur',
		'idRole',
		'model_type'
	];

	/**
	 * Boot method pour définir des valeurs par défaut lors de la création d'un pivot avoir.
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

	/**
	 * Relation belongsTo vers l'utilisateur associé.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	/**
	 * Relation belongsTo vers le rôle associé.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function role()
	{
		return $this->belongsTo(Role::class, 'idRole');
	}
}
