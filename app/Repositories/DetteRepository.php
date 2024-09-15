<?php
namespace App\Repositories;

use App\Models\Dette;
use App\Models\Article;
use Carbon\Carbon;
use DB;
use Exception;
use MongoDB\Client as MongoClient;
use MongoDB\Laravel\Eloquent\Casts\ObjectId;
use Validator;


class DetteRepository
{
    protected $mongoClient;
    protected $collection;

    public function __construct()
    {
        // Connexion à MongoDB
        $this->mongoClient = new MongoClient(env('MONGO_DSN'));
        $this->collection = $this->mongoClient->selectCollection('gestion_dettes', 'archived_debts');
    }

    public function createDette(array $data)
    {
        $totalMontant = 0; // Pour stocker la somme des prix des articles vendus

        // Valider les données d'entrée
        $validator = Validator::make($data, [
            'client_id' => 'required|exists:clients,id',
            'montant_paiement' => 'nullable|numeric',
            'articles' => 'required|array',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.qte_vente' => 'required|integer|min:1',
            'dateEcheance' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            throw new Exception('Erreur de validation : ' . implode(', ', $validator->errors()->all()));
        }

        // Définir la date d'échéance
        $dateEcheance = $data['dateEcheance'] ?? Carbon::now()->addDays(3)->toDateString();
        
        
        // Créer la dette sans le montant au départ
        try {
            $dette = Dette::create([
                'client_id' => $data['client_id'],
                'montant' => 0, // Le montant sera calculé ensuite
                'montant_paiement' => $data['montant_paiement'] ?? null,
                'dateEcheance' => $dateEcheance,

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

    // RÉCUPÉRER TOUTES LES DETTES ARCHIVÉES AVEC POSSIBILITÉ DE FILTRES:
    public function getAllArchivedDebts($filters = [])
    {
        // Construire la requête MongoDB en fonction des filtres fournis
        $query = [];

        // Filtrer par client_id
        if (!empty($filters['client_id'])) {
            $query['client_id'] = (int) $filters['client_id'];
        }

        // Filtrer par date
        if (!empty($filters['date'])) {
            $query['date'] = $filters['date'];
        }

        return $this->collection->find($query)->toArray();
    }

    /**
     * Ajouter une nouvelle dette archivée.
     */
    public function archiveDebt($data)
    {
        return $this->collection->insertOne($data);
    }

    /**
     * Trouver une dette par son ID.
     */
    public function findArchivedDebtById($id)
    {
        return $this->collection->findOne(['_id' => new ObjectId]);
    }

    // Méthode pour récupérer les dettes archivées d'un client spécifique
    public function getDettesByClientId($clientId)
    {
        return $this->collection->find(['client_id' => (int) $clientId])->toArray();
    }


    //------------------------ PARTIE RESTAURATION DETTES ARCHIVÉES:

    // Méthode pour récupérer une dette archivée depuis MongoDB 
    public function findDetteById($idDette)
    {
        return DB::connection('mongodb')->collection('archived_debts')
            ->where('dette_id', (int) $idDette)
            ->first();
    }

    // Méthode pour récupérer les dettes archivées d'un client depuis MongoDB
    public function findDettesByClientId($clientId)
    {
        return DB::connection('mongodb')->collection('archived_debts')
            ->where('client_id', (int) $clientId)
            ->get();
    }

    public function findDettesByDate($date)
    {
        return DB::connection('mongodb')->collection('archived_debts')
            ->where('date', $date)
            ->get();
    }



    // Méthode pour restaurer une dette en local (MySQL)
    public function restoreDetteToMySQL($dette)
    {
        return DB::connection('mysql')->table('dettes')->insert([
            'client_id' => (int) $dette['client_id'],
            'montant' => (float) $dette['montant'],
            'montant_paiement' => (float) $dette['montant_paiement'],
            'created_at' => $dette['date'],
        ]);
    }

    // Méthode pour supprimer une dette de MongoDB
    public function deleteDetteFromMongo($idDette)
    {
        return DB::connection('mongodb')->collection('archived_debts')
            ->where('dette_id', (int) $idDette)
            ->delete();
    }








    // // Récupérer les dettes par date
    // public function getDettesByDate($date)
    // {
    //     return $this->collection->find(['date' => $date])->toArray();
    // }

    // // Récupérer une dette par id_dette
    // public function getDetteById($idDette)
    // {
    //     return DB::connection('mongodb')->collection('archived_debts')
    //     ->where('dette_id', (int) $idDette)
    //     ->first();

    // }

    // // Supprimer une dette par id_dette
    // public function deleteDetteById($idDette)
    // {
    //     return $this->collection->deleteOne(['_id' => new ObjectId]);
    // }

    // // Supprimer les dettes par date
    // public function deleteDettesByDate($date)
    // {
    //     return $this->collection->deleteMany(['date' => $date]);
    // }

    // // Supprimer les dettes par client_id
    // public function deleteDettesByClientId($clientId)
    // {
    //     return $this->collection->deleteMany(['client_id' => (int) $clientId]);
    // }
}