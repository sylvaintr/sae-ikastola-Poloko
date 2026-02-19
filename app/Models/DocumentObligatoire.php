<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DocumentObligatoire
 *
 * Représente un document requis pour certains rôles ou processus (document obligatoire).
 *
 * @package App\Models
 *
 * @property int $idDocumentObligatoire Identifiant du document obligatoire.
 * @property string|null $nom Nom du document requis (peut être nul).
 * @property bool|null $dateE Indicateur lié à la date d'exigence (vérifier le schéma).
 */
class DocumentObligatoire extends Model
{
    use HasFactory;
    protected $table      = 'documentObligatoire';
    protected $primaryKey = 'idDocumentObligatoire';
    public $incrementing  = true;
    public $timestamps    = false;

    protected $casts = [
        'idDocumentObligatoire' => 'int',
        'dateE'                 => 'bool',
        'delai'                 => 'int',
        'dateExpiration'        => 'date',
    ];

    /**
     * Attributs assignables (fillable) pour un document obligatoire.
     *
     * - `nom` (string|null) : nom du document requis.
     * - `dateE` (bool|null) : indicateur lié à la date d'exigence (vérifier le schéma).
     * - `delai` (int|null) : délai associé (en jours, si applicable).
     * - `dateExpiration` (date|null) : date d'expiration du document.
     */
    protected $fillable = [
        'nom',
        'dateE',
        'delai',
        'dateExpiration',
    ];

    /**
     * Relation belongsToMany vers les rôles associés à ce document obligatoire via la table pivot `attribuer`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */

    public function documents()
    {
        return $this->hasMany(Document::class, 'idDocumentObligatoire', 'idDocumentObligatoire');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'attribuer', 'idDocumentObligatoire', 'idRole');
    }
}
