<?php
namespace App\Http\Requests;

use App\Models\Utilisateur as User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Méthode pour récupérer les règles de validation pour la requête de mise à jour de profil. Cette méthode définit les règles de validation pour les champs "prenom", "nom", "name" et "email". Les champs "prenom" et "nom" sont requis si le champ "name" n'est pas présent, et vice versa, pour assurer la compatibilité avec les tests par défaut de Laravel Breeze. Le champ "email" est requis, doit être une adresse e-mail valide, et doit être unique dans la table des utilisateurs, à l'exception de l'utilisateur actuellement connecté (pour permettre à l'utilisateur de conserver son adresse e-mail actuelle sans déclencher une erreur de validation). Les règles de validation garantissent que les données soumises pour la mise à jour du profil sont conformes aux exigences de format et d'unicité.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> Les règles de validation pour les champs de la requête de mise à jour de profil
     */
    public function rules(): array
    {
        return [
            // Accept either 'prenom'+'nom' or a single 'name' field (for compatibility
            // with default Breeze tests which send 'name'). We'll map 'name' -> prenom/nom
            'prenom' => ['required_without:name', 'string', 'max:255'],
            'nom'    => ['required_without:name', 'string', 'max:255'],
            'name'   => ['required_without:prenom', 'string', 'max:255'],
            'email'  => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->idUtilisateur, 'idUtilisateur'),
            ],
        ];
    }
}
