<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Actualite
 *
 * Modèle représentant une actualité / annonce dans l'application.
 *
 * @package App\Models
 *
 * @property int $idActualite Identifiant unique de l'actualité.
 * @property string|null $titre Titre (peut être nul).
 * @property string $description Contenu de l'actualité.
 * @property string $type Catégorie ou type (ex: annonce).
 * @property Carbon $dateP Date de publication ou date associée.
 * @property bool $archive Indique si l'actualité est archivée.
 * @property string|null $lien Lien externe associé (peut être nul).
 * @property int $idUtilisateur Identifiant de l'utilisateur auteur.
 */
class Actualite extends Model
{
	use HasFactory;
	protected $table = 'actualite';
	protected $primaryKey = 'idActualite';
	public $incrementing = true;
	protected $keyType = 'int';
	public $timestamps = false;

	protected $casts = [
		'idActualite' => 'int',
		'dateP' => 'datetime',
		'archive' => 'bool',
		'idUtilisateur' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour une actualité.
	 *
	 * - `titre` (string|null) : titre de l'actualité.
	 * - `description` (string) : contenu / texte de l'actualité.
	 * - `type` (string) : catégorie ou type (ex: annonce).
	 * - `dateP` (datetime) : date de publication ou date associée.
	 * - `archive` (bool) : indique si l'actualité est archivée.
	 * - `lien` (string|null) : lien externe associé.
	 * - `idUtilisateur` (int) : identifiant de l'auteur / créateur.
	 */
	protected $fillable = [
		'titre',
		'description',
		'contenu',
		'type',
		'dateP',
		'archive',
		'lien',
		'idUtilisateur',
		'idDocument',
		'idEtiquette',
	];

	/**
	 * Relation belongsTo vers l'utilisateur auteur de l'actualité.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	/**
	 * Relation belongsToMany vers les étiquettes via la pivot `correspondre`.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function etiquettes()
	{
		return $this->belongsToMany(Etiquette::class, 'correspondre', 'idActualite', 'idEtiquette');
	}

	/**
	 * Relation belongsToMany vers les documents joints à l'actualité (pivot `joindre`).
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function documents()
	{
		return $this->belongsToMany(Document::class, 'joindre', 'idActualite', 'idDocument');
	}
}
