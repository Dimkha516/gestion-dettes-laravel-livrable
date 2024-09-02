<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class UserRequest extends FormRequest
{
    /**
     * Valider les données utilisateur.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validateUser($data)
    {
        // Définir les règles de validation
        $rules = [
            'pseudo' => 'required|string|max:255|unique:users,pseudo',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&]/',
        ];

        // Créer un validateur avec les règles et les données
        return Validator::make($data, $rules);
    }

    public static function validateUserUpdate($data, $id)
    {
        $rules = [
            'pseudo' => 'required|string|max:255|unique:users,pseudo,' . $id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&]/',
        ];

        return Validator::make($data, $rules);
    }
}
