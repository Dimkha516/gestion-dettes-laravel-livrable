<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ArticleController extends Controller
{

    // LISTER TOUS LES ARTICLES DU STOCK:
    public function index()
    {
        $articles = Article::all();

        if (!$articles) {
            return response()->json([
                'message' => 'Aucun article trouvé dans la base de données'
            ]);
        }

        return response()->json([
            'message' => 'liste des articles du stock',
            $articles
        ]);
    }

    // AFFICHER UN ARTICLE:
    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'message' => 'Article non trouvé'
            ], 404);
        }

        return response()->json([
            'message' => 'Article recherché',
            $article
        ]);
    }

    // AJOUTER UN ARTICLE:
    public function store(Request $request)
    {
        $validateData = $request->validate(
            [
                'libelle' => 'required|string|max:255|unique:articles,libelle',
                'prix' => 'required|numeric',
                'qteStock' => 'required|integer'
            ],
            [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
                'libelle.max' => 'Le libellé ne doit pas dépasser 255 caractères.',
                'libelle.unique' => 'ce libelle existe dejas',
                'prix.required' => 'Le prix est obligatoire.',
                'prix.numeric' => 'Le prix doit être un nombre.',
                'qteStock.required' => 'La quantité en stock est obligatoire.',
                'qteStock.integer' => 'La quantité en stock doit être un entier.',
            ]

        );

        $article = Article::create($validateData);

        return response()->json([
            'message' => 'Article créé avec succès !',
            $article
        ], 201);
    }

    // MAJ ARTICLE EXISTANT:
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validateData = $request->validate(
            [
                'libelle' => 'sometimes|string|max:255',
                'prix' => 'sometimes|numeric',
                'qteStock' => 'sometimes|integer'
            ],
            [
                'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
                'libelle.max' => 'Le libellé ne doit pas dépasser 255 caractères.',
                'prix.numeric' => 'Le prix doit être un nombre.',
                'qteStock.integer' => 'La quantité en stock doit être un entier.',
            ]

        );

        $article->update($validateData);
        return response()->json([
            'message' => 'article mis à jour avec succès !',
            $validateData
        ], 200);
    }

    // MAJ QTE STOCK D'UN SEUL ARTICLE:
    public function updateOne(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validateData = $request->validate(
            [
                'qteStock' => 'required|integer|min:0'
            ],
            [   
                'qteStock.required' => 'Vous devez saisir une qté valide',
                'qteStock.integer' => 'La quantité en stock doit être un entier positif ou nul.',
            ]

        );
        return response()->json([
            'message' => 'article mis à jour avec succès !',
            $validateData
        ], 200);
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
            // Rechercher l'article, y compris les articles supprimés
            $article = Article::withTrashed()->findOrFail($id);

            // Restaurer l'article
            $article->restore();

            return response()->json([
                'message' => 'Article restauré avec succès !',
                'data' => $article
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Gérer l'exception si l'article n'est pas trouvé

            return response()->json([
                'message' => 'Article non trouvé.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            // Gérer les autres exceptions éventuelles
            return response()->json([
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
                'articles.*.id' => 'required|exists:articles,id',
                'articles.*.qte' => 'required|integer|min:1'
            ],

            [
                'articles.required' => 'Vous devez choisir au moins un article',
                'articles.*.id.exists' => "Cet article n'existe pas dans la base de données",
                'articles.*.qte' => "Vous devez entrez une quantité supérieure ou égale à 1"
            ]
        );

        $results = ['updated' => [], 'errors' => []];

        foreach ($validatedData['articles'] as $item) {
            try {
                $article = Article::findOrFail($item['id']);

                // Mise à jour de la quantité
                $article->qteStock += $item['qte'];
                $article->save();

                $results['updated'][] = [
                    'id' => $article->id,
                    'libelle' => $article->libelle,
                    'new_stock' => $article->qteStock
                ];
            } catch (\Exception $e) {
                // Gestion des erreurs, par exemple si l'article n'existe pas ou une autre erreur
                $results['errors'][] = [
                    'id' => $item['id'],
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json($results, 200);
    }

    // Méthode pour rechercher un article par libellé
    public function searchByLibelle(Request $request)
    {
        // Ajoutez des logs pour voir ce que vous recevez
    \Log::info('Libelle reçu:', ['libelle' => $request->query('libelle')]);
        $libelle = $request->query('libelle');

        // Rechercher l'article par son libellé
        $article = Article::where('libelle', $libelle)->first();

        if ($article) {
            // Article trouvé, retourner l'article avec le code 200
            return response()->json([
                'success' => true,
                'message' => 'Article trouvé.',
                'data' => $article
            ], 200);
        } else {
            // Article non trouvé, retourner un message d'erreur avec le code 400
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

        if ($dispo === 'oui') {
            // Lister les articles avec qteStock >= 1
            $articles = Article::where('qteStock', '>=', 1)->get();

            return response()->json([
                'success' => true,
                'message' => 'Articles disponibles trouvés.',
                'data' => $articles
            ], 200);
        } elseif ($dispo === 'non') {
            // Lister les articles avec qteStock = 0
            $articles = Article::where('qteStock', '=', 0)->get();

            return response()->json([
                'success' => true,
                'message' => 'Articles non disponibles trouvés.',
                'data' => $articles
            ], 200);
        } else {
            // Paramètre incorrect
            return response()->json([
                'success' => false,
                'message' => 'Paramètre "disponible" non valide. Utilisez "oui" ou "non".'
            ], 400);
        }
    }

}
