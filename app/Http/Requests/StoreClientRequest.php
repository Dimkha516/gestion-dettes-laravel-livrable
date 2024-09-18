<?php

namespace App\Http\Requests;

use App\Models\Categorie;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'surname' => 'required|string|unique:clients,surname',
            'telephone' => [
                'required',
                'string',
                'unique:clients,telephone',
                'regex:/^((77|76|75|70|78)\d{3}\d{2}\d{2})|(33[8]\d{2}\d{2}\d{2})$/',
            ],
            // 'categorie_id' => 'sometimes|exists:categories,id', // Si fourni, il doit exister dans la table categories
            'categorie_id' => 'sometimes|exists:categories,id',
            'montant_max' => [
            'nullable',
            'numeric',
            'min:0',
            function ($attribute, $value, $fail) {
                $categorieSilverId = Categorie::where('libelle', 'Silver')->value('id');

                if ($this->input('categorie_id') == $categorieSilverId && empty($value)) {
                    $fail('Le montant_max est requis pour la catégorie Silver.');
                }
            },
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
