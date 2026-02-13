<?php

namespace App\Services;

use App\Models\Etiquette;
use App\Models\Posseder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ActualiteEtiquetteService
{
    /**
     * Assure que la colonne public existe sur la table etiquette.
     */
    public function ensurePublicColumn(): void
    {
        if (! Schema::hasColumn('etiquette', 'public')) {
            Schema::table('etiquette', function ($table) {
                $table->boolean('public')->default(false)->after('nom');
            });
        }
    }

    /**
     * Résout les étiquettes autorisées pour l'utilisateur actuel.
     *
     * @param \Illuminate\Http\Request|null $request
     * @return array{etiquettes: \Illuminate\Database\Eloquent\Collection, allowedIds: array}
     */
    public function resolveAllowedEtiquettes($request = null): array
    {
        $unboundIds = Etiquette::whereNotIn('idEtiquette', Posseder::distinct()->pluck('idEtiquette'))->pluck('idEtiquette')->toArray();

        $hasIsPublic = Schema::hasColumn('etiquette', 'public');
        $publicTagIds = $hasIsPublic
            ? Etiquette::where('public', true)->pluck('idEtiquette')->toArray()
            : [];

        $authUser = ($request !== null && method_exists($request, 'user')) ? $request->user() : Auth::user();

        if (! $authUser) {
            // Invité : seules les étiquettes publiques sont considérées
            $etiquettes = Etiquette::whereIn('idEtiquette', $publicTagIds)->get();
            $allowedIdsArray = $publicTagIds;
        } else {
            $roleIds = $authUser->rolesCustom->pluck('idRole')->toArray();
            $allowedIds = Posseder::whereIn('idRole', $roleIds)->pluck('idEtiquette')->toArray();
            $allowedIdsArray = array_values(array_unique(array_merge($allowedIds, $publicTagIds)));
            $etiquettes = Etiquette::whereIn('idEtiquette', $allowedIdsArray)->get();
        }

        return [
            'etiquettes' => $etiquettes,
            'allowedIds' => $allowedIdsArray,
            'publicTagIds' => $publicTagIds,
            'unboundIds' => $unboundIds,
        ];
    }
}
