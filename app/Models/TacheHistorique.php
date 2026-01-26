<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TacheHistorique extends Model
{
    use HasFactory;

    protected $table = 'tache_historique';

    protected $fillable = [
        'idTache',
        'statut',
        'titre',
        'urgence',
        'description',
        'modifie_par',
    ];

    /**
     * Relation avec la tâche
     */
    public function tache()
    {
        return $this->belongsTo(Tache::class, 'idTache', 'idTache');
    }

    /**
     * Relation avec l'utilisateur qui a modifié
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'modifie_par', 'idUtilisateur');
    }

    /**
     * Accesseur pour le nom du responsable
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
