<?php
namespace App\Repositories;

use App\Models\Dette;
use App\Models\Article;
use DB;
use Exception;

class DetteRepository
{
    public function createDette(array $data)
    {
        $totalMontant = 0; // Pour stocker la somme des prix des articles vendus


        // Créer la dette sans le montant au départ
        try {
            $dette = Dette::create([
                'client_id' => $data['client_id'],
                'montant' => 0, // Le montant sera calculé ensuite
                'montant_paiement' => $data['montant_paiement'] ?? null,
            ]);

            // Associer les articles à la dette et mettre à jour le stock
            foreach ($data['articles'] as $articleData) {
                $article = Article::find($articleData['article_id']);

                // Vérification de l'existence de l'article et de son prix
                if (!$article || !isset($article->prix)) {
                    throw new Exception('L\'article ou le prix est introuvable.');
                }

                // Calculer le prix total pour cet article (prix unitaire * quantité vendue)
                $prixVente = $article->prix * $articleData['qte_vente'];
                $totalMontant += $prixVente;

                // Associer l'article à la dette dans la table pivot
                $dette->articles()->attach($article->id, [
                    'qte_vente' => $articleData['qte_vente'],
                    // 'prix_vente' => $articleData['prix_vente']
                    'prix_vente' => $prixVente
                ]);

                // Mettre à jour la quantité en stock
                $article->qteStock -= $articleData['qte_vente'];
                $article->save();

                // Mettre à jour le montant total de la dette
                $dette->montant = $totalMontant;
                $dette->save();
            }
            return $dette;

        } catch (Exception $e) {
            throw new Exception('Erreur lors de la création de la dette : ' . $e->getMessage());
        }

    }

    // Méthode pour filtrer les dettes par état 'solde'
    public function getDettesBySolde($solde)
    {
        if ($solde === 'oui') {
            // Détail si la dette est soldée (montant == montant_paiement)
            return Dette::whereRaw('COALESCE(montant, 0) = COALESCE(montant_paiement, 0)')->get();
        } else if ($solde === 'non') {
            // Détail si la dette n'est pas soldée (montant > montant_paiement)
            return Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')->get();
        }

        return collect(); // Si aucun paramètre valide n'est fourni

    }

    // Méthode pour obtenir les articles d'une dette
    public function getArticlesByDetteId($detteId)
    {
        $articles = DB::table('article_dette')
            ->join('articles', 'article_dette.article_id', '=', 'articles.id')
            ->select('articles.id', 'articles.libelle', 'articles.prix', 'article_dette.qte_vente', 'article_dette.prix_vente')
            ->where('article_dette.dette_id', $detteId)
            ->get();

        return $articles;
    }


}