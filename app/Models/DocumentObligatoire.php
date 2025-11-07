<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DocumentObligatoire
 * 
 * @property int $idDocumentObligatoire Identifiant du document obligatoire.
 * @property string|null $nom Nom du document requis (peut être nul).
 * @property bool|null $dateE Indicateur lié à la date d'exigence — vérifier le type en base (peut être bool ou date selon le schéma).
 *
 * @package App\Models
 */
class DocumentObligatoire extends Model
{
	protected $table = 'documentObligatoire';
	protected $primaryKey = 'idDocumentObligatoire';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idDocumentObligatoire' => 'int',
		'dateE' => 'bool'
	];

	protected $fillable = [
		'nom',
		'dateE'
	];

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'attribuer', 'idDocumentObligatoire', 'idRole');
	}
}
