<?php

namespace App\Http\Requests;

use App\Models\Categorie;
use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return true; // Autoriser toutes les requêtes (ajuster selon les besoins)
    // }
    public function rules(): array
    {
        return [
            'surname' => 'required|string|unique:clients,surname',
            'telephone' => [
                'required',
                'string',
                'unique:clients,telephone',
                'regex:/^((77|76|75|70|78)\d{3}\d{2}\d{2})|(33[8]\d{2}\d{2}\d{2})$/',
                // facultatif avec la valeur par défaut ajoutée
                
            ],
            'adresse' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'surname.required' => 'Le surnom est obligatoire !',
            'surname.unique' => 'Ce surnom est déjas pris !',
            'telephone.required' => 'Le téléphone est obligatoire !',
            'telephone.unique' => 'Ce téléphone est déjà pris !',
            'telephone.regex' => 'Format téléphone invalide. Exp: 771234567',
        ];
    }
}
