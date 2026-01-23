<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = ['title', 'description', 'recurrence_days', 'reminder_days', 'is_active', 'target_id', 'target_type'];

    // Lien vers Evenement ou DocumentObligatoire
    public function target() {
        return $this->morphTo();
    }

    // Lien vers tes Rôles (avec tes clés spécifiques)
    public function roles() {
        return $this->belongsToMany(Role::class, 'notification_setting_role', 'notification_setting_id', 'role_id', 'id', 'idRole');
    }
    
    // Pour afficher "Evenement" proprement dans la liste
    public function getModuleLabelAttribute() {
        return class_basename($this->target_type);
    }
}