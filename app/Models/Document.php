<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Document
 * 
 * @property int $idDocument
 * @property string $nom
 * @property string $chemin
 * @property string $type
 * @property string $etat
 *
 * @package App\Models
 */
class Document extends Model
{
	protected $table = 'document';
	protected $primaryKey = 'idDocument';
	public $incrementing = false;
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
}
