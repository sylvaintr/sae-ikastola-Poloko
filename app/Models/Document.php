<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Document
 * 
 * @property int $idDocument Identifiant du document.
 * @property string $nom Nom / libellé du document.
 * @property string $chemin Chemin de stockage (ou URL) du fichier.
 * @property string $type Type de document (ex: pdf, image).
 * @property string $etat État ou statut du document (ex: actif, obligatoire).
 *
 * @package App\Models
 */
class Document extends Model
{
	protected $table = 'document';
	protected $primaryKey = 'idDocument';
	public $incrementing = true;
	protected $keyType = 'int';
	public $timestamps = false;

	protected $casts = [
		'idDocument' => 'int'
	];

	protected $fillable = [
		'nom',
		'chemin',
		'type',
		'etat'
	];

	public function utilisateurs()
	{
		return $this->belongsToMany(Utilisateur::class, 'contenir', 'idDocument', 'idUtilisateur');
	}

	public function actualites()
	{
		return $this->belongsToMany(Actualite::class, 'joindre', 'idDocument', 'idActualite');

	}
}
