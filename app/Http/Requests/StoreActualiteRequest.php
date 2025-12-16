<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreActualiteRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette demande.
     */
    public function authorize(): bool
    {
        return Auth::check(); // Seuls les connectés peuvent poster
    }

    /**
     * Préparer les données avant validation.
     * C'est ici qu'on transforme la date "25/12/2025" en "2025-12-25".
     */
    protected function prepareForValidation()
    {
        if ($this->has('dateP') && $this->dateP) {
            $d = \DateTime::createFromFormat('d/m/Y', $this->dateP);
            // Si le format est bon, on remplace la valeur dans la requête
            if ($d) {
                $this->merge([
                    'dateP' => $d->format('Y-m-d'),
                ]);
            }
        }
        
        // Gérer la case à cocher 'archive' qui n'est pas envoyée si décochée
        $this->merge([
            'archive' => $this->has('archive'),
        ]);
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [
            'type'           => 'required|string',
            'dateP'          => 'required|date', // Maintenant c'est une date valide SQL
            'lien'           => 'nullable|url',
            'archive'        => 'boolean',
            
            // Français
            'titrefr'        => 'required|string|max:30', // J'ai mis required, ajuste si besoin
            'descriptionfr'  => 'required|string|max:100',
            'contenufr'      => 'required|string',

            // Basque
            'titreeus'       => 'nullable|string|max:30', // Nullable si le basque est optionnel
            'descriptioneus' => 'required|string|max:100',
            'contenueus'     => 'required|string',

            // Relations
            'etiquettes'     => 'nullable|array',
            'etiquettes.*'   => 'integer|exists:etiquette,idEtiquette',
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
