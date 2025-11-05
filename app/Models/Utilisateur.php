<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

/**
 * Class Utilisateur
 * 
 * @property int $idUtilisateur Identifiant de l'utilisateur.
 * @property string $prenom Prénom de l'utilisateur.
 * @property string $nom Nom de l'utilisateur.
 * @property string $mdp Mot de passe (hashé) — ne pas stocker ni exposer en clair.
 * @property string|null $email Adresse e‑mail (peut être nulle).
 * @property string $languePref Langue préférée (ex: "fr", "en").
 * @property bool|null $statutValidation Indique si le compte est validé / activé.
 * @property string|null $remember_token Jeton de "remember me" (peut être nul).
 * @property Carbon|null $created_at Date de création du compte.
 * @property Carbon|null $updated_at Date de dernière mise à jour du compte.
 *
 * @package App\Models
 */
class Utilisateur extends Authenticatable implements CanResetPasswordContract
{
	use HasFactory, Notifiable, HasRoles, CanResetPassword;
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
	 * Return the e-mail address where password reset links are sent.
	 */
	public function getEmailForPasswordReset()
	{
		return $this->email;
	}

	/**
	 * Send the password reset notification.
	 * Explicitly define to ensure the default ResetPassword notification is used.
	 */
	public function sendPasswordResetNotification($token)
	{
		$this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
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
		$info = password_get_info($value);

		// Only hash if not already hashed
		if ($info['algo'] === null || $info['algo'] === 0) {
			return password_hash($value, PASSWORD_DEFAULT);
		}

		return $value;
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

	/* Relations */

	public function actualites()
	{
		return $this->hasMany(Actualite::class, 'idUtilisateur');
	}

	public function documents()
	{
		return $this->belongsToMany(Document::class, 'contenir', 'idUtilisateur', 'idDocument');
	}

	public function factures()
	{
		return $this->hasMany(Facture::class, 'idUtilisateur');
	}

	public function avoirs()
	{
		return $this->hasMany(Avoir::class, 'idUtilisateur');
	}

	public function rolesCustom()
	{
		// Pivot table `avoir` links utilisateurs and roles in this project
		return $this->belongsToMany(Role::class, 'avoir', 'idUtilisateur', 'idRole');
	}

	public function familles()
	{
		return $this->belongsToMany(Famille::class, 'lier', 'idUtilisateur', 'idFamille')->withPivot('parite');
	}

	public function tachesRealisees()
	{
		return $this->belongsToMany(Tache::class, 'realiser', 'idUtilisateur', 'idTache')->withPivot('dateM', 'description');
	}
}
