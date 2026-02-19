<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreActualiteRequest extends FormRequest
{
    /**
     * Méthode pour déterminer si l'utilisateur est autorisé à faire cette requête. Cette méthode vérifie si l'utilisateur est connecté via `Auth::check()`. Si l'utilisateur est connecté, la méthode retourne `true`, permettant à la requête d'être traitée. Si l'utilisateur n'est pas connecté, la méthode retourne `false`, empêchant la requête de se poursuivre. Cela garantit que seuls les utilisateurs authentifiés peuvent créer une actualité.
     * @return bool `true` si l'utilisateur est connecté et autorisé à faire la requête, sinon `false`
     */
    public function authorize(): bool
    {
        return Auth::check(); // Seuls les connectés peuvent poster
    }

    /**
     * Méthode pour préparer les données avant validation. pour le champ "dateP", qui est attendu au format "d/m/Y" (par exemple, "25/12/2025"), cette méthode tente de le convertir en format "Y-m-d" (par exemple, "2025-12-25") qui est compatible avec les types de données de date SQL. Si la conversion réussit, la valeur du champ "dateP" dans la requête est remplacée par la nouvelle valeur formatée. De plus, cette méthode gère la case à cocher "archive" en s'assurant que si elle n'est pas présente dans la requête (ce qui signifie qu'elle est décochée), elle est explicitement définie sur `false`. Cela garantit que les données sont dans le bon format avant que les règles de validation ne soient appliquées.
     * @return void
     */
    protected function prepareForValidation(): void
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
     * Méthode pour récupérer les règles de validation. Cette méthode retourne un tableau de règles de validation pour les différents champs de la requête. Les règles incluent des exigences telles que la présence obligatoire de certains champs, le format de date pour "dateP", la validation d'URL pour "lien", et des contraintes de longueur pour les titres et descriptions en français et en basque. De plus, il y a des règles pour les relations avec les étiquettes et les images, assurant que les étiquettes existent dans la base de données et que les fichiers d'image respectent les types et tailles autorisés. Ces règles garantissent que les données soumises pour créer une actualité sont valides et conformes aux attentes du système.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> Les règles de validation pour les champs de la requête
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
