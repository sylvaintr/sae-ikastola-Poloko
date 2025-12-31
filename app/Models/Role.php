<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * Class Role
 *
 * Représente un rôle utilisateur (intégré avec Spatie Permission dans ce projet).
 *
 * @package App\Models
 *
 * @property int $idRole Identifiant du rôle.
 * @property string $name Nom interne du rôle (ex: "admin").
 * @property string $guard_name Nom du guard/auth driver (si utilisé).
 * @property Carbon|null $created_at Date de création.
 * @property Carbon|null $updated_at Date de mise à jour.
 */
class Role extends \Spatie\Permission\Models\Role
{
	use HasFactory;
	protected $table = 'role';
	protected $primaryKey = 'idRole';
	public $incrementing = true;
	protected $keyType = 'int';

	protected $casts = [
		'idRole' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour un rôle.
	 *
	 * - `name` (string) : nom interne du rôle (ex: "admin").
	 * - `guard_name` (string) : nom du guard/auth driver (si utilisé).
	 */
	protected $fillable = [
		'name',
		'guard_name'
	];

	/**
	 * Relation many-to-many vers les documents obligatoires associés à ce rôle.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function documentObligatoires()
	{
		return $this->belongsToMany(DocumentObligatoire::class, 'attribuer', 'idRole', 'idDocumentObligatoire');
	}

	/**
	 * Relation belongsToMany vers les étiquettes associées à ce rôle via la table pivot `posseder`.
	 */
	public function etiquettes()
	{
		return $this->belongsToMany(Etiquette::class, 'posseder', 'idRole', 'idEtiquette')->using(Posseder::class);
	}
}
