<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Actualite
 * 
 * @property int $idActualite Identifiant unique de l'actualité.
 * @property string|null $titre Titre de l'actualité (peut être nul).
 * @property string $description Contenu / texte de l'actualité.
 * @property string $type Catégorie ou type de l'actualité (ex: annonce, info).
 * @property Carbon $dateP Date de publication ou date liée à l'actualité.
 * @property bool $archive Indique si l'actualité est archivée.
 * @property string|null $lien Lien externe associé (peut être nul).
 * @property int $idUtilisateur Identifiant de l'utilisateur auteur / créateur.
 *
 * @package App\Models
 */
class Actualite extends Model
{
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

	protected $fillable = [
		'titre',
		'description',
		'type',
		'dateP',
		'archive',
		'lien',
		'idUtilisateur'
	];

	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	public function etiquettes()
	{
		return $this->belongsToMany(Etiquette::class, 'correspondre', 'idActualite', 'idEtiquette');
	}

	public function documents()
	{
		return $this->belongsToMany(Document::class, 'joindre', 'idActualite', 'idDocument');
	}
}
