<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ClientFidelityCardMail;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\Client;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;




class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }
    // Liste tous les clients
    public function index(): JsonResponse
    {
        $result = $this->clientService->getAllClients();

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $result['status']);
    }

    // Affiche un client spécifique par ID
    public function show($id): JsonResponse
    {
        $result = $this->clientService->getClientById($id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $result['status']);
    }

    // Crée un nouveau client
    public function store(StoreClientRequest $request): JsonResponse
    {
        // Utiliser les données validées pour créer le client
        $client = $this->clientService->createClient($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Client enregistré avec succès',
            'data' => $client
        ], 200);
    }


    public function testSendMail(): JsonResponse
    {
        try {
            // Remplacez par un client existant
            $client = Client::find(1);
            if (!$client) {
                throw new \Exception('Client not found');
            }

            // Remplacez par un utilisateur existant
            // $user = $client->user;
            $user = User::find(122);
            if (!$user) {
                throw new \Exception('User not found');
            }
            // Remplacez par un chemin de fichier PDF existant
            $pdfPath = 'fidelite_cards/Pol_123.pdf';
            if (!Storage::disk('local')->exists($pdfPath)) {
                throw new \Exception('PDF file not found');
            }

            // Envoyer l'email
            Mail::to($user->email)->send(new ClientFidelityCardMail($client, $user, $pdfPath));

            return response()->json([
                'success' => true,
                'message' => 'Mail sent successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send mail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeWithAccount(StoreClientRequest $clientRequest, StoreUserRequest $userRequest): JsonResponse
    {
        try {
            // Valider les données du client et de l'utilisateur
            $validatedClientData = $clientRequest->validate([
                'surname' => 'required|string|max:255',
                'telephone' => 'required|string|max:20',
                'adresse' => 'nullable|string|max:255',
                'photo' => 'nullable|file|mimes:jpeg,png,svg|max:40960', // 40 KB
            ]);

            $validatedUserData = $userRequest->validate([
                'pseudo' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $photo = $clientRequest->file('photo'); // Récupérer la photo du formulaire

            // // Passer les données validées et le fichier photo au service:
            // $result = $this->clientService->createClientWithAccount(
            //     $clientRequest->$validatedClientData,
            //     $userRequest->$validatedUserData,
            //     $photo
            // );
            // Passer les données validées et le fichier photo au service:
            $result = $this->clientService->createClientWithAccount(
                $clientRequest->validated(),
                $userRequest->validated(),
                $photo
            );


            return response()->json([
                'success' => true,
                'message' => 'Client et compte utilisateur enregistrés avec succès',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du client et de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }



    // AFFICHER UN CLIENT AVEC SON COMPTE UTILISATEUR:
    // Méthode pour afficher un client avec son compte utilisateur
    public function showClientWithUser($id)
    {
        // Récupérer le client par son ID
        $client = Client::with('user')->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé.',
            ], 404); // 404 Not Found
        }

        // Préparer les données utilisateur
        $userData = $client->user;
        $message = $userData ? 'Client et compte utilisateur récupérés avec succès.' : 'Le client n\'a pas de compte utilisateur.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'client' => $client,
            'user' => $userData,
        ], 200); // 200 OK
    }

    // Met à jour un client existant
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'telephone' => 'sometimes|string|unique:clients,telephone,' . $id,
            'adresse' => 'sometimes|string|max:255',
        ]);

        $client = $this->clientService->updateClient($id, $validatedData);

        return response()->json($client);
    }

    // Supprime un client
    public function destroy($id)
    {
        $this->clientService->deleteClient($id);

        return response()->json([
            'message' => 'Client supprimé avec succès.',
        ], 200);
    }


    //--------------------------------------------------- PARTIE FILTRE ET RECHERCHES 
    // RECHERCHER UN CLIENT PAR TÉLÉPHONE
    public function findByPhone(Request $request)
    {
        $phone = $request->query('phone'); // Récupère le paramètre 'phone' de l'URL

        if (!$phone) {
            return response()->json(['message' => 'Le paramètre téléphone est manquant'], 400);
        }

        // Utiliser le service pour rechercher le client
        $clients = $this->clientService->findClientByPhone($phone);

        if ($clients->isNotEmpty()) {
            return response()->json([
                'message' => 'Clients trouvés',
                'data' => $clients
            ], 200); // Clients trouvés
        }

        return response()->json(['message' => 'Aucun client trouvé'], 404); // Aucun client trouvé
    }

    // RECHERCHER PLUSIEURS CLIENT PAR TÉLÉPHONE
    public function findUsersByPhones(Request $request)
    {
        $phones = $request->query('phones');
        if (!$phones) {
            return response()->json(['message' => 'Le paramètre téléphone est obligatoire !'], 400);
        }

        // Convertir la chaîne de numéros de téléphone en un tableau
        $phoneArray = explode(',', $phones);

        // Utiliser le service pour rechercher les clients correspondant aux numéros de téléphone
        $clients = $this->clientService->findClientsByPhones($phoneArray);

        if ($clients->isEmpty()) {
            return response()->json(['message' => 'Aucun client correspondant trouvé'], 404);
        }

        return response()->json($clients->all(), 200); // Convertir la Lazy Collection en un tableau JSON

    }

    // Méthode pour lister les clients avec ou sans compte utilisateur
    public function listByAccount(Request $request)
    {
        $compte = $request->query('compte');

        if ($compte !== 'oui' && $compte !== 'non') {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre compte invalide. Utilisez "oui" ou "non".',
            ], 400); // 400 Bad Request
        }

        $clients = $this->clientService->listClientsByAccount($compte);

        if ($clients === null) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre compte invalide. Utilisez "oui" ou "non".',
            ], 400); // 400 Bad Request
        }

        return response()->json([
            'success' => true,
            'message' => 'Liste des clients récupérée avec succès.',
            'data' => $clients,
        ], 200); // 200 OK
    }

    // Méthode pour lister les clients par statut
    public function listByStatus(Request $request)
    {
        $status = $request->query('status');

        if ($status === 'actif' || $status === 'bloque') {
            $clients = $this->clientService->listClientsByStatus($status);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre status invalide. Utilisez "actif" ou "bloque".',
            ], 400); // 400 Bad Request
        }

        return response()->json([
            'success' => true,
            'message' => "Liste des clients avec statut : {$status}",
            'data' => $clients,
        ], 200); // 200 OK
    }


}




