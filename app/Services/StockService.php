<?php
namespace App\Services;

use App\Models\Article;
use Exception;

class StockService
{
    /**
     * Add stock to articles.
     *
     * @param array $articles
     * @return array
     */
    public function addStock(array $articles)
    {
        $results = ['updated' => [], 'errors' => []];

        foreach ($articles as $item) {
            try {
                $article = Article::findOrFail($item['id']);

                // Update stock quantity
                $article->qteStock += $item['qte'];
                $article->save();

                $results['updated'][] = [
                    'id' => $article->id,
                    'libelle' => $article->libelle,
                    'new_stock' => $article->qteStock
                ];
            } catch (Exception $e) {
                // Handle errors
                $results['errors'][] = [
                    'id' => $item['id'],
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
