<?php

namespace App\Http\Requests;

use App\Models\Utilisateur as User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Accept either 'prenom'+'nom' or a single 'name' field (for compatibility
            // with default Breeze tests which send 'name'). We'll map 'name' -> prenom/nom
            'prenom' => ['required_without:name', 'string', 'max:255'],
            'nom' => ['required_without:name', 'string', 'max:255'],
            'name' => ['required_without:prenom', 'string', 'max:255'],
            'email' => [
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
