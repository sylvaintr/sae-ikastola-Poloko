<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * Class Role
 * 
 * @property int $idRole Identifiant du rôle.
 * @property string $name Nom interne du rôle (ex: "admin", "enseignant").
 * @property string $guard_name Nom du guard/auth driver (si utilisé).
 * @property Carbon|null $created_at Date de création du rôle.
 * @property Carbon|null $updated_at Date de dernière mise à jour du rôle.
 *
 * @package App\Models
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

	protected $fillable = [
		'name',
		'guard_name'
	];

	/**
	 * Documents obligatoires attributés à ce rôle (pivot `attribuer`).
	 */
	public function documentObligatoires()
	{
		return $this->belongsToMany(DocumentObligatoire::class, 'attribuer', 'idRole', 'idDocumentObligatoire');
	}
}
