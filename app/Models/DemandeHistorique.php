<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeHistorique extends Model
{
    use HasFactory;

    protected $table = 'demande_historique';

    /**
     * Attributs assignables (fillable) pour une demande historique.
     *
     * - `idDemande` (int) : identifiant de la demande associée.
     * - `statut` (string) : statut de la demande à ce moment de l'historique (ex: "en cours", "terminée").
     * - `titre` (string) : titre ou résumé de la demande à ce moment de l'historique.
     * - `responsable` (string) : nom ou identifiant du responsable assigné à la demande à ce moment de l'historique.
     * - `depense` (float) : montant de la dépense associée à la demande à ce moment de l'historique.
     * - `dateE` (date) : date à laquelle ce statut de la demande a été enregistré dans l'historique.
     * - `description` (string) : description détaillée ou commentaires associés à ce statut de la demande dans l'historique.
     */
    protected $fillable = [
        'idDemande',
        'statut',
        'titre',
        'responsable',
        'depense',
        'dateE',
        'description',
    ];

    protected $casts = [
        'idDemande' => 'int',
        'dateE'     => 'date',
        'depense'   => 'float',
    ];

    /**
     * Relation belongsTo vers la demande associée à cet historique.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo La relation vers le modèle Tache, indiquant que chaque entrée de l'historique est liée à une demande spécifique.
     */
    public function demande()
    {
        return $this->belongsTo(Tache::class, 'idDemande', 'idTache');
    }
}
