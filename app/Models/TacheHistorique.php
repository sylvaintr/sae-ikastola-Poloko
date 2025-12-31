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
        'responsable',
        'depense',
        'date_evenement',
        'description',
    ];

    protected $casts = [
        'date_evenement' => 'date',
        'depense' => 'float',
    ];

    public function tache()
    {
        return $this->belongsTo(Tache::class, 'idTache', 'idTache');
    }
}

