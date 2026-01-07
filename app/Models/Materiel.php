<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Materiel
 *
 * Modèle représentant du matériel/ressource pouvant être utilisé pour des événements.
 *
 * @package App\Models
 *
 * @property int $idMateriel Identifiant du matériel.
 * @property string $provenance Provenance (achat, don, prêt, ...).
 * @property string $description Description détaillée.
 */
class Materiel extends Model
{
	use HasFactory;
	protected $table = 'materiel';
	protected $primaryKey = 'idMateriel';
	public $incrementing = true;
	protected $keyType = 'int';
	public $timestamps = false;

	protected $casts = [
		'idMateriel' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour le matériel.
	 *
	 * - `provenance` (string) : provenance du matériel (achat, don, prêt, ...).
	 * - `description` (string) : description détaillée du matériel.
	 */
	protected $fillable = [
		'provenance',
		'description'
	];

	/**
	 * Relation belongsToMany vers les événements utilisant ce matériel (pivot `inclure`).
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function evenements()
	{
		return $this->belongsToMany(Evenement::class, 'inclure', 'idMateriel', 'idEvenement');
	}
}
