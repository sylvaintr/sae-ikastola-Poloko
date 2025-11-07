<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Facture
 * 
 * @property int $idFacture Identifiant de la facture.
 * @property bool $etat État de la facture (ex: payée / non payée) — flag.
 * @property Carbon $dateC Date de création / émission de la facture.
 * @property int $idUtilisateur Identifiant de l'utilisateur ayant émis la facture.
 * @property int $idFamille Identifiant de la famille destinataire de la facture.
 *
 * @package App\Models
 */
class Facture extends Model
{
	use HasFactory;
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

	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	public function famille()
	{
		return $this->belongsTo(Famille::class, 'idFamille');
	}

	/**
	 * Compatibility accessor: provide $facture->id mapping to the model primary key.
	 */
	public function getIdAttribute()
	{
		return $this->getKey();
	}
}
