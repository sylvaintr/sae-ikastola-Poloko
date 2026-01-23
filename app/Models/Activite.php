<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Activite
 *
 * Modèle Eloquent représentant une activité planifiée ou proposée dans l'application.
 *
 * @package App\Models
 *
 * @property string $activite Identifiant / nom unique de l'activité (clé logique).
 * @property Carbon $dateP Date prévue / programmée de l'activité.
 */
class Activite extends Model
{
	use HasFactory;
	protected $table = 'activite';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'dateP' => 'datetime'
	];
	protected $fillable = ['activite', 'dateP'];
	protected $primaryKey = ['activite', 'dateP'];

	/**
	 * Relation hasMany vers les enregistrements `PRATIQUE` (présences/inscriptions).
	 * La colonne locale `activite` (string) correspond à `PRATIQUE.activite`.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function etres()
	{
		return $this->hasMany(PRATIQUE::class, 'activite', 'activite');
	}
}
