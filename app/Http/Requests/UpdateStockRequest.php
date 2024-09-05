<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Autoriser tout utilisateur pour cette démonstration
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'qteStock' => 'required|integer|min:0'
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'qteStock.required' => 'Vous devez saisir une quantité valide.',
            'qteStock.integer' => 'La quantité en stock doit être un entier positif ou nul.',
        ];
    }
}
