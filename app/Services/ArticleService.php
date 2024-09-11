<?php
namespace App\Services;

use App\Models\Article;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ArticleService
{
    /**
     * Get all articles.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllArticles()
    {
        return Article::all();
    }

    /**
     * Get a specific article by ID.
     *
     * @param int $id
     * @return \App\Models\Article|null
     */
    public function getArticleById($id)
    {
        return Article::find($id);
    }

    public function createArticle(array $data)
    {
        return Article::create($data);
    }

    public function updateArticle(Article $article, array $data)
    {
        $article->update($data);
        return $article;
    }

    public function updateStock(Article $article, array $data)
    {
        $article->update($data);
        return $article;
    }

    public function softDeleteArticle(Article $article)
    {
        $article->delete();
        return $article;
    }

    public function restoreArticle($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        $article->restore();
        return $article;
    }

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
            }
            // 
            catch (ModelNotFoundException $e) {
                // Handle specific error for article not found
                $results['errors'][] = [
                    'id' => $item['id'],
                    'message' => 'Article non trouvé'
                ];
            }
            // 
            catch (Exception $e) {
                // Handle errors
                $results['errors'][] = [
                    'id' => $item['id'],
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    // Search article by libelle
    public function searchByLibelle($libelle)
    {
        return Article::where('libelle', $libelle)->first();
    }

    // Filter articles by availability
    public function filterByAvailability($dispo)
    {
        if ($dispo === 'oui') {
            return Article::available()->get();
        } elseif ($dispo === 'non') {
            return Article::notAvailable()->get();
        } else {
            throw new Exception('Paramètre "disponible" non valide. Utilisez "oui" ou "non".');
        }

        // if ($dispo === 'oui') {
        //     return Article::where('qteStock', '>=', 1)->get();
        // } elseif ($dispo === 'non') {
        //     return Article::where('qteStock', '=', 0)->get();
        // } else {
        //     throw new Exception('Paramètre "disponible" non valide. Utilisez "oui" ou "non".');
        // }
    }

}
