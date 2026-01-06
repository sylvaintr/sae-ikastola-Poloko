<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Lier
 *
 * Pivot liant un `Utilisateur` à une `Famille` (indique la relation de parenté / rôle dans la famille).
 *
 * @package App\Models
 *
 * @property int $idUtilisateur Identifiant de l'utilisateur lié.
 * @property int $idFamille Identifiant de la famille liée.
 * @property string|null $parite Rôle / parité dans la famille (ex: parent, tuteur).
 */
class Lier extends Pivot
{
	use HasFactory;

	protected $table = 'lier';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'idUtilisateur' => 'int',
		'idFamille' => 'int'
	];

	/**
	 * Attributs assignables (fillable) pour le pivot lier.
	 *
	 * - `parite` int  parité dans la famille (ex: parent, tuteur) en %.
	 */
	protected $fillable = [
		'parite'
	];

	/**
	 * Relation belongsTo vers l'utilisateur lié à la famille.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class, 'idUtilisateur');
	}

	/**
	 * Relation belongsTo vers la famille liée à l'utilisateur.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function famille()
	{
		return $this->belongsTo(Famille::class, 'idFamille');
	}
}
