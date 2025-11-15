<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tache
 * 
 * @property int $idTache Identifiant de la tâche.
 * @property string $titre Titre de la tâche.
 * @property string $description Description de la tâche.
 * @property string $type Type / catégorie de la tâche.
 * @property string $urgence Niveau d'urgence (faible / moyen / élevé).
 * @property string $etat État actuel de la tâche (ex: ouverte, en cours, fermée).
 * @property Carbon|null $dateD Date de début (peut être nulle).
 * @property Carbon|null $dateF Date de fin (peut être nulle).
 * @property float|null $montantP Montant prévu (optionnel).
 * @property float|null $montantR Montant réel (optionnel).
 * @property int|null $idEvenement Référence optionnelle à un événement associé.
 *
 * @package App\Models
 */
class Tache extends Model
{
	protected $table = 'tache';
	protected $primaryKey = 'idTache';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idTache' => 'int',
		'dateD' => 'datetime',
		'dateF' => 'datetime',
		'montantP' => 'float',
		'montantR' => 'float',
		'idEvenement' => 'int'
	];

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
		'idEvenement'
	];

	public function evenement()
	{
		return $this->belongsTo(Evenement::class, 'idEvenement');
	}

	public function realisateurs()
	{
		return $this->belongsToMany(Utilisateur::class, 'realiser', 'idTache', 'idUtilisateur')->withPivot('dateM', 'description');
	}

	public function documents()
	{
		return $this->hasMany(Document::class, 'idTache', 'idTache');
	}

	public function historiques()
	{
		return $this->hasMany(TacheHistorique::class, 'idTache', 'idTache')->orderByDesc('date_evenement')->orderByDesc('id');
	}
}
