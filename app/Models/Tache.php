<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Tache
 *
 * Représente une tâche / action à réaliser (peut être liée à un événement).
 *
 * @package App\Models
 *
 * @property int $idTache Identifiant de la tâche.
 * @property string $titre Titre de la tâche.
 * @property string $description Description détaillée.
 * @property string $type Catégorie / type de tâche.
 * @property string $urgence Niveau d'urgence (faible / moyen / élevé).
 * @property string $etat État (ex: ouverte, en cours, fermée).
 * @property Carbon|null $dateD Date de début (optionnelle).
 * @property Carbon|null $dateF Date de fin (optionnelle).
 * @property float|null $montantP Montant prévu (optionnel).
 * @property float|null $montantR Montant réel (optionnel).
 * @property int|null $idEvenement Référence à un événement (optionnelle).
 */
class Tache extends Model
{
	use HasFactory;
	protected $table = 'tache';
	protected $primaryKey = 'idTache';
	public $incrementing = true;
	protected $keyType = 'int';
	public $timestamps = false;

	protected $casts = [
		'idTache' => 'int',
		'dateD' => 'datetime',
		'dateF' => 'datetime',
		'montantP' => 'float',
		'montantR' => 'float',
		'idEvenement' => 'int',
		'idRole' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour une tâche.
	 *
	 * - `titre` (string) : titre court de la tâche.
	 * - `description` (string) : description détaillée de la tâche.
	 * - `type` (string) : catégorie ou type de la tâche.
	 * - `etat` (string) : état courant (ex: ouverte, en cours, fermée).
	 * - `dateD` (datetime|null) : date de début prévue.
	 * - `dateF` (datetime|null) : date de fin prévue.
	 * - `montantP` (float|null) : montant prévu.
	 * - `montantR` (float|null) : montant réel.
	 * - `idEvenement` (int|null) : référence vers un événement associé.
	 */
	protected $fillable = [
		'idTache',
		'titre',
		'description',
		'type',
		'urgence',
		'etat',
		'dateD',
		'dateF',
		'montantP',
		'montantR',
		'idEvenement',
		'idRole'
	];

	/**
	 * Relation belongsTo vers l'événement associé à la tâche (optionnel).
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function evenement()
	{
		return $this->belongsTo(Evenement::class, 'idEvenement');
	}

	/**
	 * Relation belongsToMany vers les utilisateurs ayant réalisé la tâche (pivot `realiser`).
	 * Inclut les colonnes pivot `dateM` et `description`.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function realisateurs()
	{
		return $this->belongsToMany(Utilisateur::class, 'realiser', 'idTache', 'idUtilisateur')
		->using(\App\Models\Realiser::class)
		->withPivot('dateM', 'description');
	}

	public function documents()
	{
		return $this->hasMany(Document::class, 'idTache', 'idTache');
	}

	public function historiques()
	{
		return $this->hasMany(\App\Models\DemandeHistorique::class, 'idDemande', 'idTache')
			->orderByDesc('date_evenement')
			->orderByDesc('id');
	}

	/**
	 * Relation belongsTo vers le rôle (commission) assigné à la tâche.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function roleAssigne()
	{
		return $this->belongsTo(Role::class, 'idRole');
	}

}
