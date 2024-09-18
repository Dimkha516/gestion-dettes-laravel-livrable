<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class StoreDebtRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {   
        if (auth()->user()->role !== 'client') {
            throw new AuthorizationException('Vous devez être un client pour effectuer cette action.');
        }
        
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
            'articles' => 'required|array',
            'articles.*.id' => 'required|exists:articles,id',
            'articles.*.quantite' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'articles.required' => 'Vous devez choisir au moins un article valide !',
            'articles.exists:articles,id' => 'cet article choisi est invalide !$',
        ];
    }
}
