<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class Utilisateur
 *
 * Modèle utilisateur principal (authentification, profils, rôles).
 *
 * @package App\Models
 *
 * @property int $idUtilisateur Identifiant de l'utilisateur.
 * @property string $prenom Prénom de l'utilisateur.
 * @property string $nom Nom de l'utilisateur.
 * @property string $mdp Mot de passe (hashé) — ne pas exposer en clair.
 * @property string|null $email Adresse e‑mail (peut être nulle).
 * @property string $languePref Langue préférée (ex: "fr").
 * @property bool|null $statutValidation Indique si le compte est activé.
 * @property string|null $remember_token Jeton "remember me" (optionnel).
 * @property Carbon|null $created_at Date de création.
 * @property Carbon|null $updated_at Date de mise à jour.
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
	/**
	 * Attributs à cacher lors de la sérialisation (ex: JSON).
	 * mdp : Mot de passe (hashé) — ne pas exposer en clair.
	 * remember_token : Jeton "remember me".
	 */
	protected $hidden = [
		'mdp',
		'remember_token'
	];

	/**
	 * Les attributs assignables de l'utilisateur.
	 *
	 * Clés et explications :
	 * - prenom (string) : Prénom de l'utilisateur.
	 * - nom (string) : Nom de famille de l'utilisateur.
	 * - email (string) : Adresse e‑mail unique utilisée pour l'authentification et les notifications.
	 * - mdp (string) : Mot de passe haché (ne jamais stocker ni transmettre en clair).
	 * - languePref (string) : Langue préférée de l'utilisateur (ex: "fr", "eus").
	 * - statutValidation (bool|null) : Indique si le compte est activé (true) ou non (false).
	 * - remember_token (string|null) : Jeton optionnel pour la fonctionnalité "se souvenir de moi".
	 * - created_at (string|DateTime|null) : Date et heure de création de l'enregistrement.
	 * - updated_at (string|DateTime|null) : Date et heure de la dernière modification de l'enregistrement.
	 *
	 * Remarques :
	 * - Les champs sensibles (mot_de_passe, tokens) doivent être protégés et exclus des réponses publiques.
	 * - Ce commentaire documente la structure attendue du tableau retourné/attendu par le modèle Utilisateur.
	 */
	protected $fillable = [
		'prenom',
		'nom',
		'mdp',
		'email',
		'languePref',
		'statutValidation',
		'remember_token'
	];

	/**
	 * Retourne le mot de passe utilisé pour l'authentification.
	 * @return string Le mot de passe (hashé) stocké dans l'attribut `mdp`.
	 */
	public function getAuthPassword()
	{
		return $this->mdp;
	}



	/**
	 * Retourne l'adresse e-mail utilisée pour l'envoi des liens de réinitialisation.
	 * @return string|null Adresse e-mail ou null si absente.
	 */
	public function getEmailForPasswordReset()
	{
		return $this->email;
	}



	/**
	 * Envoie la notification de réinitialisation de mot de passe.
	 * @param string $token Jeton de réinitialisation fourni par le système.
	 * @return void
	 */
	public function sendPasswordResetNotification($token)
	{
		$this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
	}



	/**
	 * Accesseur de compatibilité pour obtenir le mot de passe via `$user->password`.
	 * @return string Le mot de passe hashé (attribut `mdp`).
	 */
	public function getPasswordAttribute()
	{
		return $this->mdp;
	}



	/**
	 * Mutateur de compatibilité qui permet d'assigner `$user->password = '...'`.
	 * Si la valeur fournie n'est pas déjà hachée, elle sera hachée avant stockage.
	 * @param string $value Mot de passe en clair ou déjà hashé.
	 * @return string Valeur (hashée) stockée.
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
	 * Fournit un attribut virtuel `name` combinant `prenom` et `nom`.
	 * @return string Nom complet formaté.
	 */
	public function getNameAttribute()
	{
		$prenom = $this->attributes['prenom'] ?? '';
		$nom = $this->attributes['nom'] ?? '';
		return trim($prenom . ' ' . $nom);
	}



	/**
	 * Fournit un alias `id` pointant vers la clé primaire `idUtilisateur`.
	 * @return int Identifiant primaire du modèle.
	 */
	public function getIdAttribute()
	{
		return $this->getKey();
	}


	/* Relations */

	/**
	 * Relation hasMany vers les actualités créées par l'utilisateur.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function actualites()
	{
		return $this->hasMany(Actualite::class, 'idUtilisateur');
	}


	/**
	 * Relation belongsToMany vers les documents liés à l'utilisateur via la pivot `contenir`.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function documents()
	{
		return $this->belongsToMany(Document::class, 'contenir', 'idUtilisateur', 'idDocument');
	}


	/**
	 * Relation hasMany vers les factures émises par l'utilisateur.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function factures()
	{
		return $this->hasMany(Facture::class, 'idUtilisateur');
	}


	/**
	 * Relation hasMany vers les enregistrements de pivot `avoir` (rôles attribués).
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function avoirs()
	{
		return $this->hasMany(Avoir::class, 'idUtilisateur');
	}


	/**
	 * Relation belongsToMany vers les rôles de l'utilisateur (via la table `avoir`).
	 * Retourne les rôles associés et inclut la colonne pivot `model_type`.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function rolesCustom()
	{
		// Pivot table `avoir` links utilisateurs and roles in this project
		// model_type is required for the polymorphic relationship structure
		return $this->belongsToMany(Role::class, 'avoir', 'idUtilisateur', 'idRole')
			->withPivot('model_type')
			->using(Avoir::class);
	}


	/**
	 * Relation belongsToMany vers les familles associées à l'utilisateur (pivot `lier`).
	 * La colonne pivot `parite` contient le rôle dans la famille.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function familles()
	{
		return $this->belongsToMany(Famille::class, 'lier', 'idUtilisateur', 'idFamille')->withPivot('parite');
	}


	/**
	 * Relation belongsToMany vers les tâches réalisées par l'utilisateur (pivot `realiser`).
	 * Inclut les colonnes pivot `dateM` et `description`.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function tachesRealisees()
	{
		return $this->belongsToMany(Tache::class, 'realiser', 'idUtilisateur', 'idTache')
		->using(\App\Models\Realiser::class)
		->withPivot('dateM', 'description');
	}
}
