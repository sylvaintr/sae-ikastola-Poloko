<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Facture
 *
 * Représente une facture émise pour une famille ou un utilisateur.
 *
 * @package App\Models
 *
 * @property int $idFacture Identifiant de la facture.
 * @property bool $etat État de la facture (ex: payée / non payée).
 * @property Carbon $dateC Date de création / émission.
 * @property int $idUtilisateur Identifiant de l'utilisateur ayant émis la facture.
 * @property int $idFamille Identifiant de la famille destinataire.
 */
class Facture extends Model
{
	use HasFactory;
	protected $table = 'facture';
	protected $primaryKey = 'idFacture';
	public $incrementing = true;
	protected $keyType = 'int';
	public $timestamps = false;

	protected $casts = [
		'idFacture' => 'int',
		'etat' => 'bool',
		'dateC' => 'datetime',
		'previsionnel' => 'bool',
		'idUtilisateur' => 'int',
		'idFamille' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour une facture.
	 *
	 * - `etat` (bool) : état de la facture (payée / non payée).
	 * - `dateC` (datetime) : date de création / émission.
	 * - `previsionnel` (bool) : indique si la facture est prévisionnelle.
	 * - `idUtilisateur` (int) : identifiant de l'utilisateur ayant émis la facture.
	 * - `idFamille` (int) : identifiant de la famille destinataire.
	 */
	protected $fillable = [
		'etat',
		'dateC',
		'previsionnel',
		'idUtilisateur',
		'idFamille'
	];

	/**
	 * Relation belongsTo vers l'utilisateur ayant émis la facture.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}


	/**
	 * Relation belongsTo vers la famille destinataire de la facture.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function famille()
	{
		return $this->belongsTo(Famille::class, 'idFamille');
	}



	/**
	 * Accesseur compatibilité : fournit un alias `id` vers la clé primaire.
	 *
	 * @return mixed
	 */
	public function getIdAttribute()
	{
		return $this->getKey();
	}
}
