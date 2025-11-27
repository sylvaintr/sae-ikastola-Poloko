<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Posseder extends Pivot
{
    protected $table = 'posseder';

    protected $casts = [
        'idUtilisateur' => 'int',
        'idFamille' => 'int'
    ];

    /**
     * Relation belongsTo vers le rôle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'idRole');
    }

    /**
     * Relation belongsTo vers l'étiquette.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function etiquette()
    {
        return $this->belongsTo(Etiquette::class, 'idEtiquette');
    }
}
