<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    /**
     * Attributs assignables (fillable) pour la configuration de notification.
     *
     * - `title` (string) : titre de la notification.
     * - `description` (string) : description détaillée de la notification.
     * - `recurrence_days` (int) : nombre de jours entre les notifications récurrentes.
     * - `reminder_days` (int) : nombre de jours avant l'événement pour envoyer un rappel.
     * - `is_active` (bool) : indique si la notification est active ou non.
     * - `target_id` (int) : identifiant de la cible de la notification (peut être une famille, un utilisateur, etc.).
     * - `target_type` (string) : type de la cible de la notification (ex: 'Famille', 'Utilisateur', etc.).
     */
    protected $fillable = [
        'title',
        'description',
        'recurrence_days',
        'reminder_days',
        'is_active',
        'target_id',
        'target_type',
    ];

    /**
     * Relation morphTo pour la cible de la notification. Cette relation permet à une notification d'être associée à différents types de modèles (par exemple, une famille, un utilisateur, etc.) en utilisant les champs `target_id` et `target_type` pour déterminer la cible spécifique de la notification.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function target()
    {
        return $this->morphTo();
    }

    /**
     * Relation belongsToMany vers les rôles associés à cette configuration de notification via la table pivot `notification_setting_role`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'notification_setting_role',
            'notification_setting_id',
            'role_id',
            'id',
            'idRole'
        );
    }
    /**
     * Attribut personnalisé pour obtenir le label du module cible de la notification. Cet attribut utilise la fonction `class_basename` pour extraire le nom de classe simple à partir du champ `target_type`, ce qui permet d'obtenir un label lisible pour le type de cible de la notification (par exemple, 'Famille', 'Utilisateur', etc.) sans inclure l'espace de noms complet.
     * @return string Le label du module cible de la notification
     */
    public function getModuleLabelAttribute()
    {
        return class_basename($this->target_type);
    }
}
