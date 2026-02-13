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
	public $timestamps = false;

	protected $casts = [
		'idFacture' => 'int',
		'etat' => 'string',
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

	/**
	 * Mutator to accept boolean or string values for `etat`.
	 * - boolean true => 'verifier'
	 * - boolean false => 'brouillon'
	 */
	public function setEtatAttribute($value)
	{
		if (is_bool($value)) {
			$this->attributes['etat'] = $value ? 'verifier' : 'brouillon';
			return;
		}

		if (is_string($value) && in_array($value, ['manuel', 'brouillon', 'verifier', 'manuel verifier'], true)) {
			$this->attributes['etat'] = $value;
			return;
		}

		$this->attributes['etat'] = 'brouillon';
	}

}
