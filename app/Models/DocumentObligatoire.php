<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DocumentObligatoire
 * 
 * @property int $idDocumentObligatoire
 * @property string|null $nom
 * @property bool|null $dateE
 *
 * @package App\Models
 */
class DocumentObligatoire extends Model
{
	protected $table = 'document_obligatoire';
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
}
