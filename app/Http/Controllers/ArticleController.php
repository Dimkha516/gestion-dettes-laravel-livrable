<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Services\ArticleService;
use App\Services\StockService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Log;



class ArticleController extends Controller
{

    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }


    // LISTER TOUS LES ARTICLES DU STOCK:
    public function index()
    {
        $articles = $this->articleService->getAllArticles();

        if ($articles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun article trouvé dans la base de données',
            ], 404); // 404 Not Found
        }

        return response()->json([
            'success' => true,
            'message' => 'Liste des articles du stock',
            'data' => $articles,
        ], 200); // 200 OK
    }

    // AFFICHER UN ARTICLE:
    public function show($id)
    {
        $article = $this->articleService->getArticleById($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé',
            ], 404); // 404 Not Found
        }

        return response()->json([
            'success' => true,
            'message' => 'Article recherché',
            'data' => $article,
        ], 200); // 200 OK
    }

    // AJOUTER UN ARTICLE:
    public function store(StoreArticleRequest $request)
    {
        $validatedData = $request->validated();

        $article = $this->articleService->createArticle($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Article créé avec succès !',
            'data' => $article
        ], 201); // 201 Created
    }

    // MAJ ARTICLE EXISTANT:
    public function update(UpdateArticleRequest $request, $id)
    {
        $article = Article::findOrFail($id);

        $validatedData = $request->validated();

        $article = $this->articleService->updateArticle($article, $validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Article mis à jour avec succès !',
            'data' => $article
        ], 200); // 200 OK

    }

    // MAJ QTE STOCK D'UN SEUL ARTICLE:
    public function updateOne(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validatedData = $request->validated();

        $article = $this->articleService->updateStock($article, $validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Quantité en stock mise à jour avec succès !',
            'data' => $article
        ], 200); // 200 OK
    }

    // SUPPRESSION SOFT:
    public function destroy($id)
    {
        $article = Article::find($id);
        if (!$article) {
            return response()->json([
                'message' => 'Article non trouvé'
            ], 404);
        }


        $article->delete();
        return response()->json([
            'message' => 'Article Archivé avec succès !',
            $article
        ], 202);
    }

    // RESTORATION ARTICLE SUPPRIMÉ:
    public function restore($id)
    {
        try {
            $article = $this->articleService->restoreArticle($id);

            return response()->json([
                'success' => true,
                'message' => 'Article restauré avec succès !',
                'data' => $article
            ], 200); // 200 OK
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé.',
                'error' => $e->getMessage()
            ], 404); // 404 Not Found
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration de l\'article.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    // ... Autres méthodes CRUD ...

    /**
     * Ajoute la quantité au stock d'un ou plusieurs articles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addStock(Request $request)
    {

        $validatedData = $request->validate(
            [
                'articles' => 'required|array|min:1',
                'articles.*.id' => 'required|integer',
                'articles.*.qte' => 'required|integer|min:1'
            ],
            [
                'articles.required' => 'Vous devez choisir au moins un article',
                'articles.*.id.required' => "L'ID de l'article est requis",
                'articles.*.qte' => "Vous devez entrer une quantité supérieure ou égale à 1"
            ]
        );

        $results = ['updated' => [], 'errors' => []];

        foreach ($validatedData['articles'] as $item) {
            try {
                $article = Article::findOrFail($item['id']);

                // Update stock quantity
                $article->qteStock += $item['qte'];
                $article->save();

                $results['updated'][] = [
                    'id' => $article->id,
                    'libelle' => $article->libelle,
                    'new_stock' => $article->qteStock,
                    'message' => 'Stock mis à jour avec succès'
                ];
            } catch (ModelNotFoundException $e) {
                // Handle specific error for article not found
                $results['errors'][] = [
                    'id' => $item['id'],
                    'message' => 'Article non trouvé'
                ];
            } catch (Exception $e) {
                // Handle other exceptions
                $results['errors'][] = [
                    'id' => $item['id'],
                    'message' => 'Erreur lors de la mise à jour du stock: ' . $e->getMessage()
                ];
            }
        }

        return response()->json($results, 200);

        // $validatedData = $request->validate(
        //     [
        //         'articles' => 'required|array|min:1',
        //         'articles.*.id' => 'required|exists:articles,id',
        //         'articles.*.qte' => 'required|integer|min:1'
        //     ],
        //     [
        //         'articles.required' => 'Vous devez choisir au moins un article',
        //         'articles.*.id.exists' => "Cet article n'existe pas dans la base de données",
        //         'articles.*.qte' => "Vous devez entrer une quantité supérieure ou égale à 1"
        //     ] 
        // );

        // // Use the service to add stock:
        // $results = $this->articleService->addStock($validatedData['articles']);

        // return response()->json($results, 200);
    }

    // Méthode pour rechercher un article par libellé
    public function searchByLibelle(Request $request)
    {
        $libelle = $request->query('libelle');
        Log::info('Libelle reçu:', ['libelle' => $libelle]);

        $article = $this->articleService->searchByLibelle($libelle);

        if ($article) {
            return response()->json([
                'success' => true,
                'message' => 'Article trouvé.',
                'data' => $article
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Libellé article non trouvé.'
            ], 404);
        }
    }

    // Méthode pour filtrer les articles par disponibilité
    public function filterByAvailability(Request $request)
    {
        $dispo = $request->query('disponible');

        try {
            $articles = $this->articleService->filterByAvailability($dispo);
    
            return response()->json([
                'success' => true,
                'message' => $dispo === 'oui' ? 'Articles disponibles trouvés.' : 'Articles non disponibles trouvés.',
                'data' => $articles
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
        // try {
        //     $articles = $this->articleService->filterByAvailability($dispo);

        //     return response()->json([
        //         'success' => true,
        //         'message' => $dispo === 'oui' ? 'Articles disponibles trouvés.' : 'Articles non disponibles trouvés.',
        //         'data' => $articles
        //     ], 200);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $e->getMessage()
        //     ], 400);
        // }
    }

}
