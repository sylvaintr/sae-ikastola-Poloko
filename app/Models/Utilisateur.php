<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

/**
 * Class Utilisateur
 * 
 * @property int $idUtilisateur
 * @property string $prenom
 * @property string $nom
 * @property string $mdp
 * @property string|null $email
 * @property string $languePref
 * @property bool $statutValidation
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Utilisateur extends Authenticatable
{

	use HasFactory, Notifiable, HasRoles;

	protected $table = 'utilisateur';
	protected $primaryKey = 'idUtilisateur';
	public $incrementing = true;
	protected $keyType = 'int';

	protected $casts = [
		'idUtilisateur' => 'int',
		'statutValidation' => 'bool'
	];

	protected $hidden = [
		'mdp',
		'remember_token'
	];

	protected $fillable = [
		'prenom',
		'nom',
		'mdp',
		'email',
		'languePref',
		'statutValidation',
		'remember_token'
	];

	public function getAuthPassword()
	{
		return $this->mdp;
	}

	/**
	 * Compatibility accessor: provide $user->password (maps to mdp)
	 */
	public function getPasswordAttribute()
	{
		return $this->mdp;
	}

	/**
	 * Compatibility mutator: allow setting 'password' on the model and store it in mdp.
	 */
	public function setPasswordAttribute($value)
	{
		// If value already hashed, keep it, otherwise hash it.
		$this->attributes['mdp'] = password_get_info($value)['algo'] ? $value : bcrypt($value);
	}

	/**
	 * Provide a 'name' attribute combining prenom and nom for compatibility.
	 */
	public function getNameAttribute()
	{
		$prenom = $this->attributes['prenom'] ?? '';
		$nom = $this->attributes['nom'] ?? '';
		return trim($prenom . ' ' . $nom);
	}

	/**
	 * Provide dynamic "id" property used in tests/URL generation (maps to idUtilisateur).
	 */
	public function getIdAttribute()
	{
		return $this->getKey();
	}
}
