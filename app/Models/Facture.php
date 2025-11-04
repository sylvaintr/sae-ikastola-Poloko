<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Facture
 * 
 * @property int $idFacture
 * @property bool $etat
 * @property Carbon $dateC
 * @property int $idUtilisateur
 * @property int $idFamille
 *
 * @package App\Models
 */
class Facture extends Model
{
	protected $table = 'facture';
	protected $primaryKey = 'idFacture';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idFacture' => 'int',
		'etat' => 'bool',
		'dateC' => 'datetime',
		'idUtilisateur' => 'int',
		'idFamille' => 'int'
	];

	protected $fillable = [
		'etat',
		'dateC',
		'idUtilisateur',
		'idFamille'
	];
}
