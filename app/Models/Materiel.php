<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Materiel
 * 
 * @property int $idMateriel Identifiant du matériel.
 * @property string $provenance Provenance du matériel (achat, don, prêt, etc.).
 * @property string $description Description détaillée du matériel.
 *
 * @package App\Models
 */
class Materiel extends Model
{
	use HasFactory;
	protected $table = 'materiel';
	protected $primaryKey = 'idMateriel';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idMateriel' => 'int'
	];

	protected $fillable = [
		'provenance',
		'description'
	];

	public function evenements()
	{
		return $this->belongsToMany(Evenement::class, 'inclure', 'idMateriel', 'idEvenement');
	}
}
