<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Document
 *
 * Modèle représentant un document stocké (fichier, ressource) et ses métadonnées.
 *
 * @package App\Models
 *
 * @property int $idDocument Identifiant du document.
 * @property string $nom Nom / libellé du document.
 * @property string $chemin Chemin de stockage ou URL du fichier.
 * @property string $type Type de document (ex: pdf, image).
 * @property string $etat État ou statut du document.
 */
class Document extends Model
{
	use HasFactory;
	protected $table = 'document';
	protected $primaryKey = 'idDocument';
	public $incrementing = true;
	public $timestamps = false;

	protected $casts = [
		'idDocument' => 'int',
		'idTache' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour un document.
	 *
	 * - `nom` (string) : nom / libellé du document.
	 * - `chemin` (string) : chemin de stockage ou URL du fichier.
	 * - `type` (string) : type de document (ex: pdf, image).
	 * - `etat` (string) : état ou statut du document.
	 */
	protected $fillable = [
		'idDocument',
		'idTache',
		'nom',
		'chemin',
		'type',
		'etat'
	];

	/**
	 * Relation belongsToMany vers les utilisateurs qui possèdent / sont liés à ce document (pivot `contenir`).
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function utilisateurs()
	{
		return $this->belongsToMany(Utilisateur::class, 'contenir', 'idDocument', 'idUtilisateur');
	}

	public function tache()
	{
		return $this->belongsTo(Tache::class, 'idTache', 'idTache');
	}

	/**
	 * Relation belongsToMany vers les actualités auxquelles ce document est joint (pivot `joindre`).
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function actualites()
	{
		return $this->belongsToMany(Actualite::class, 'joindre', 'idDocument', 'idActualite');
	}
}
