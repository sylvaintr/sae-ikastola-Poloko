<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TacheHistorique extends Model
{
    use HasFactory;

    protected $table = 'tache_historique';

    /**
     * Attributs assignables (fillable) pour l'historique des tâches.
     * - `idTache` (int) : identifiant de la tâche associée à cet historique.
     * - `statut` (string) : statut de la tâche au moment de l'historique (ex: "ouverte", "en cours", "fermée").
     * - `titre` (string) : titre de la tâche au moment de l'historique.
     * - `description` (string) : description de la tâche au moment de l'historique.
     * - `modifie_par` (int) : identifiant de l'utilisateur qui a effectué la modification ayant généré cet historique.
     */
    protected $fillable = [
        'idTache',
        'statut',
        'titre',
        'urgence',
        'description',
        'modifie_par',
    ];

    /**
     * Relation avec la tâche associée à cet historique. Cette relation permet d'accéder à la tâche d'origine à partir de l'historique, ce qui est utile pour comprendre le contexte de chaque modification enregistrée dans l'historique.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo La relation belongsTo vers la tâche associée à cet historique.
     */
    public function tache()
    {
        return $this->belongsTo(Tache::class, 'idTache', 'idTache');
    }

    /**
     * Relation avec l'utilisateur qui a modifié la tâche. Cette relation permet d'identifier quel utilisateur a effectué la modification qui a généré cet historique, ce qui est important pour le suivi des changements et la responsabilité des actions dans le système.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo La relation belongsTo vers l'utilisateur qui a modifié la tâche.
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'modifie_par', 'idUtilisateur');
    }

    /**
     * Accesseur pour le nom du responsable de la tâche au moment de l'historique. Cet attribut personnalisé permet d'obtenir le nom de l'utilisateur qui a modifié la tâche, en utilisant la relation `utilisateur` pour accéder à l'utilisateur associé à cet historique. Si l'utilisateur n'est pas disponible (par exemple, si l'utilisateur a été supprimé), il retourne un tiret "—" pour indiquer l'absence d'information.
     * @return string Le nom du responsable de la tâche au moment de l'historique, ou "—" si l'utilisateur n'est pas disponible.
     */
    public function getResponsableAttribute()
    {
        return $this->utilisateur?->name ?? '—';
    }

    /**
     * Cast pour created_at
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
