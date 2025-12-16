<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Role;
use App\Models\Etiquette;
use App\Models\Actualite;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Posseder extends Pivot
{
    use HasFactory;
    protected $table = 'posseder';
    public $timestamps = false;

    protected $casts = [
        'idEtiquette' => 'integer',
        'idRole' => 'integer',
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
