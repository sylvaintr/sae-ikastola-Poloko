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
 * @property int $idActualite
 * @property string|null $titre
 * @property string $description
 * @property string $type
 * @property Carbon $dateP
 * @property bool $archive
 * @property string|null $lien
 * @property int $idUtilisateur
 *
 * @package App\Models
 */
class Actualite extends Model
{
	protected $table = 'actualite';
	protected $primaryKey = 'idActualite';
	public $incrementing = false;
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
}
