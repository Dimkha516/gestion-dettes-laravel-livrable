<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;

class DetteRequest extends FormRequest
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
            'client_id' => [
                'required',
                'exists:clients,id',
            ],
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => [
                'required',
                'exists:articles,id',
            ],
            'articles.*.qte_vente' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $articleId = $this->input('articles')[explode('.', $attribute)[1]]['article_id'];
                    $article = Article::find($articleId);

                    if ($article && $value > $article->qteStock) {
                        $fail('La quantité vendue doit être inférieure ou égale à la quantité en stock.');
                    }
                },
            ],
            'dateEcheance' => 'nullable|date|after:today',
            'montant_paiement' => 'nullable|numeric|lt:montant', // Paiement optionnel, toujours inférieur au montant
        ];
    }

    public function messages()
    {
        return [
            'client_id.exists' => 'Le client doit exister dans la base de données.',
            'articles.required' => 'Vous devez fournir au moins un article.',
            'articles.*.article_id.exists' => 'L\'article doit exister dans la base de données.',
            'articles.*.qte_vente.min' => 'La quantité vendue doit être au moins de 1.',
            'dateEcheance.date' => 'Le format de la date échéance est invalide !',
            'montant_paiement.lt' => 'Le montant du paiement doit être inférieur au montant de la dette.',
            'dateEcheance.after' => "La date échéance doit venir après la date du jour !"
        ];
    }
}
