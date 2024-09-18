<?php
namespace App\Services;
use App\Models\Article;
use App\Models\Client;
use App\Models\DemandeDeDette;
use App\Models\Dette;
use Auth;
use Illuminate\Support\Collection;


class DebtRequestService
{
    //------------------ FAIRE UNE DEMANDE DE DETTE:
    public function createDebtRequest(array $data)
    {

        // Récupérer l'utilisateur connecté
        // $user = auth()->user();

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        // if (!in_array($user->role, ['admin', 'boutiquier'])) {
        if (!in_array($user->role, ['client'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les clients peuvent demander une dette !.'
            ], 403);
        }


        // Trouver le client lié à cet utilisateur
        $client = Client::where('user_id', $user->id)->firstOrFail();

        if (!$client) {
            throw new \Exception('Vous devez avoir un compte utilisateur pour demander une dette !');
        }

        if ($client->categorie->libelle === 'Silver') {
            // Règle pour les clients Silver : Vérifier que le montant total des dettes n'a pas atteint le montant_max
            $totalDetteClient = Dette::where('client_id', $client->id)
                ->selectRaw('SUM(COALESCE(montant, 0) - COALESCE(montant_paiement, 0)) as total_dette')
                ->first()
                ->total_dette;

            if ($totalDetteClient >= $client->montant_max) {
                throw new \Exception('Vous avez atteint votre montant maximum de dettes. Vous ne pouvez plus faire de demandes.');
            }
        } elseif ($client->categorie->libelle === 'Bronze') {
            // Règle pour les clients Bronze : Vérifier qu'ils n'ont aucune dette restante
            $detteRestante = Dette::where('client_id', $client->id)
                ->whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')
                ->exists();

            if ($detteRestante) {
                throw new \Exception('Vous avez des dettes impayées. Veuillez les régler avant de faire une nouvelle demande.');
            }
        }

        // Clients Gold n'ont pas de restriction, pas de vérification supplémentaire nécessaire

        // Calcul du montant total en fonction des articles et quantités
        $montantTotal = collect($data['articles'])->reduce(function ($carry, $article) {
            $articleData = Article::findOrFail($article['id']);
            return $carry + ($articleData->prix * $article['quantite']);
        }, 0);

        // Créer la demande de dette
        return DemandeDeDette::create([
            'client_id' => $client->id,
            'articles' => json_encode($data['articles']),
            'montant_total' => $montantTotal,
            'etat' => DemandeDeDette::ETAT_ENCOURS, // Etat par défaut
        ]);
    }

    //--------------------LISTER MES DEMANDES DE DETTES EN TANT QUE CLIENT CONNECTÉ:
    public function getClientDebts(int $clientId, string $etat): Collection
    {
        // Valider que l'état est valide
        $validEtats = ['encours', 'annule', 'valide'];
        if (!in_array($etat, $validEtats)) {
            throw new \InvalidArgumentException('État invalide. Les valeurs acceptées sont : encours, annule, valide.');
        }

        // Récupérer les dettes du client avec filtrage par état
        return DemandeDeDette::where('client_id', $clientId)
            ->where('etat', $etat)
            ->get();
    }


    //--------------------LISTER MES DEMANDES DE DETTES EN TANT QUE CLIENT CONNECTÉ:
    public function getAllClientDebts(string $etat)
    {   
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        // if (!in_array($user->role, ['admin', 'boutiquier'])) {
        if (!in_array($user->role, ['boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les boutiquiers peuvent lister toutes les demandes de dettes.'
            ], 403);
        }

        // Valider que l'état est valide
        $validEtats = ['encours', 'annule', 'valide'];
        if (!in_array($etat, $validEtats)) {
            throw new \InvalidArgumentException('État invalide. Les valeurs acceptées sont : encours, annule, valide.');
        }

        // Récupérer les dettes des client avec filtrage par état
        return DemandeDeDette::where('etat', $etat)
            ->get();
    }
}