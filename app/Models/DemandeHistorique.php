<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeHistorique extends Model
{
    use HasFactory;

    protected $table = 'demande_historique';

    protected $fillable = [
        'idDemande',
        'statut',
        'titre',
        'responsable',
        'depense',
        'date_evenement',
        'description',
    ];

    protected $casts = [
        'idDemande' => 'int',
        'date_evenement' => 'date',
        'depense' => 'float',
    ];

    public function demande()
    {
        return $this->belongsTo(Tache::class, 'idDemande', 'idTache');
    }
}

