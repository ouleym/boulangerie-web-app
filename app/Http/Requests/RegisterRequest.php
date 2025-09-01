<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Déterminer si l’utilisateur est autorisé à faire cette requête
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour l’inscription
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom'       => 'required|string|min:2|max:100',
            'prenom'    => 'required|string|min:2|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
            'telephone' => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:255',
            'ville'     => 'nullable|string|max:100',
        ];
    }
}
