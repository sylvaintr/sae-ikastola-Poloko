<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Activite
 * 
 * @property string $activite Identifiant / nom de l'activité (clé logique). Chaîne unique servant à référencer l'activité depuis d'autres tables.
 * @property Carbon $dateP Date prévue / programmée de l'activité (utilisé pour trier et filtrer par date).
 *
 * @package App\Models
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

	public function etres()
	{
		// Etre uses column `activite` (string) to reference Activite.activite
		return $this->hasMany(Etre::class, 'activite', 'activite');
	}
}
