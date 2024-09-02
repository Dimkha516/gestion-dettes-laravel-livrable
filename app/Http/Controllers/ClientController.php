<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
// use Hash;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\LazyCollection;


class ClientController extends Controller
{
    // Liste tous les clients
    public function index()
    {
        $clients = Client::all();

        if (!$clients) {
            return response()->json([
                'message' => 'Aucun client existe dans la base de données'
            ], 404);
        }

        return response()->json([
            'message' => 'liste des clients',
            $clients
        ], 200);
    }

    // Affiche un client spécifique par ID
    public function show($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'message' => 'Client non trouvé'
            ], 404);
        }

        return response()->json([
            'message' => 'Client recherché',
            $client
        ], 200);
    }

    // Crée un nouveau client
    public function store(Request $request)
    {
        // Définir les règles de validation
        $validator = Validator::make(
            $request->all(),
            [
                'surname' => 'required|string|unique:clients,surname',
                'telephone' => [
                    'required',
                    'string',
                    'unique:clients,telephone',
                    'regex:/^((77|76|75|70|78)\d{3}\d{2}\d{2})|(33[8]\d{2}\d{2}\d{2})$/'
                ],
                'adresse' => 'nullable|string',
            ],
            [
                'surname.required' => 'Le surnom est obligatoire !',
                'surname.unique' => 'Ce surnom est déjas pris !',
                'telephone.required' => 'Le téléphone est obligatoire !',
                'telephone.unique' => 'Ce téléphone est dajas prix !',
                'telephone.regex' => 'Format téléphone invalide. Exp: 771234567'
            ]
        );

        // Vérifier si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données',
                'data' => $validator->errors()
            ], 422); // 422 Unprocessable Entity
        }

        // Créer le client avec les données validées et le statut par défaut 'actif'
        $client = Client::create([
            'surname' => $request->surname,
            'telephone' => $request->telephone,
            'adresse' => $request->filled('adresse') ? $request->adresse : null, // Utilise l'adresse fournie si elle existe
            'status' => 'actif', // Définir le statut par défaut
        ]);

        // Retourner une réponse de succès
        return response()->json([
            'success' => true,
            'message' => 'Client enregistré avec succès',
            'data' => $client
        ], 200);
    }

    public function storeWithAccount(Request $request)
    {
        // Définir les règles de validation pour le client et l'utilisateur
        $validator = Validator::make(
            $request->all(),
            [
                'surname' => 'required|string|unique:clients,surname',
                'telephone' => [
                    'required',
                    'string',
                    'unique:clients,telephone',
                    'regex:/^((77|76|75|70|78)\d{3}\d{2}\d{2})|(33[8]\d{2}\d{2}\d{2})$/'
                ],
                'adresse' => 'nullable|string',
                'pseudo' => 'required|string|unique:users,pseudo',
                'email' => 'required|string|email|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    'min:5',
                    'regex:/[A-Z]/',       // Au moins une majuscule
                    'regex:/[a-z]/',       // Au moins une minuscule
                    'regex:/[0-9]/',       // Au moins un chiffre
                    'regex:/[@$!%*?&]/',   // Au moins un caractère spécial
                ],

            ],
            [
                'surname.required' => 'Le surnmon est obligatoitoire',
                'surname.unique' => 'Ce surnom est déjas pris !',
                'telephone.required' => 'Le téléphone est obligatoire !',
                'telephone.unique' => 'Ce téléphone est dajas prix !',
                'telephone.regex' => 'Format téléphone invalide. Exp: 771234567',
                'pseudo.required' => 'Le pseudo est obligatoire !',
                'pseudo.unique' => 'Ce pseudo est déjas pris !',
                'email.required' => 'Vous devez saisir email utilisateur',
                'email.email' => 'Email non valide',
                'email.unique' => 'Cet email est dejas pris',
                'password.required' => 'vous devez entrer un mot de passe',
                'password.min' => 'Le mot de passe doit faire 5 caractères',
                'password.regex' => 'Le mot de passe doit comporter maj/min, chiffre et caractère spécial',

            ]
        );

        // Vérifier si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données',
                'data' => $validator->errors()
            ], 422); // 422 Unprocessable Entity
        }

        // Démarrer une transaction pour s'assurer de la cohérence des données
        try {
            \DB::beginTransaction();

            // Créer le client avec les données validées et le statut par défaut 'actif'
            $client = Client::create([
                'surname' => $request->surname,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'status' => 'actif', // Statut par défaut
            ]);

            // Créer l'utilisateur avec les données validées
            $user = User::create([
                'pseudo' => $request->pseudo,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'client' // Rôle par défaut pour un nouveau client
            ]);

            // Associer l'utilisateur créé au client
            $client->user_id = $user->id;
            $client->save();

            // Valider la transaction
            \DB::commit();

            // Retourner une réponse de succès
            return response()->json([
                'success' => true,
                'message' => 'Client et compte utilisateur enregistrés avec succès',
                'data' => [
                    'client' => $client,
                    'user' => $user,
                ]
            ], 200);

        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            \DB::rollBack();
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
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé.',
            ], 404); // 404 Not Found
        }

        // Initialiser les données du compte utilisateur comme vide
        $userData = [];
        $message = 'Le client n\'a pas de compte utilisateur.';

        // Vérifier si le client a un compte utilisateur
        if ($client->user_id) {
            $user = User::find($client->user_id);

            if ($user) {
                $userData = $user;
                $message = 'Client et compte utilisateur récupérés avec succès.';
            }
        }

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
        $client = Client::findOrFail($id);

        // Validation des données
        $validatedData = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'telephone' => 'sometimes|string|unique:clients,telephone,' . $id,
            'adresse' => 'sometimes|string|max:255',
        ]);

        $client->update($validatedData);
        return response()->json($client);
    }

    // Supprime un client
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return response()->json(null, 204);
    }


    //--------------------------------------------------- PARTIE FILTRE ET RECHERCHES 
    // RECHERCHER UN CLIENT PAR TÉLÉPHONE
    public function findByPhone(Request $request)
    {
        $phone = $request->query('phone'); // Récupère le paramètre 'phone' de l'URL

        if (!$phone) {
            return response()->json(['message' => 'Le paramètre téléphone est manquant'], 400);
        }

        // Rechercher le client avec le numéro de téléphone donné
        $client = Client::where('telephone', 'LIKE', "%{$phone}%")->lazy();
        // $user = User::where('telephone', 'LIKE', "%{$phone}%")->first();

        if ($client) {
            return response()->json($client, 200); // Utilisateur trouvé
            // +1-815-284-9472
        }

        return response()->json(['message' => 'Utilisateur non trouvé'], 404); // Utilisateur non trouvé
    }

    // RECHERCHER PLUSIEURS CLIENT PAR TÉLÉPHONE
    public function findUsersByPhones(Request $request)
    {
        // RÉCUPÉRER LES TEL À PARTIR DES PARAMS:
        $phones = $request->query('phones');
        if (!$phones) {
            return response()->json(['message' => 'le paramètre téléphone est obligatoire !']);
        }
        // Convertir la chaine de numéros de téléphones en un tableau
        $phoneArray = explode(',', $phones);
        // Utiliser Lazy Collection pour charger les utilisateurs correspondants
        $clients = LazyCollection::make(function () use ($phoneArray) {
            foreach ($phoneArray as $phone) {
                $phone = trim($phone); // Supprimer les espaces autour du numéro de téléphone
                $foundedClient = Client::where('telephone', 'LIKE', "%{$phone}%")->get();

                foreach ($foundedClient as $client) {
                    yield $client;
                    // Le yield est utilisé pour générer chaque utilisateur
                    // correspondant un par un, ce qui est paresseux par nature
                    // et efficace en termes de mémoire. 
                }
            }
        });
        if ($clients->isEmpty()) {
            return response()->json(['message' => 'Aucun client correspondant trouvé'], 404);
        }
        return response()->json($clients->all(), 200); // Convertir la Lazy Collection en un tableau JSON
    }

    // Méthode pour lister les clients avec ou sans compte utilisateur
    public function listByAccount(Request $request)
    {
        $compte = $request->query('compte');

        if ($compte === 'oui') {
            // Liste des clients avec un compte utilisateur
            $clients = Client::whereNotNull('user_id')->get();
        } elseif ($compte === 'non') {
            // Liste des clients sans compte utilisateur
            $clients = Client::whereNull('user_id')->get();
        } else {
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
            // Liste des clients selon le statut
            $clients = Client::where('status', $status)->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre status invalide. Utilisez "actif" ou "bloque".',
            ], 400); // 400 Bad Request
        }

        return response()->json([
            'success' => true,
            'message' => "Liste des clients Avec status: ",
            $status,
            'data' => $clients,
        ], 200); // 200 OK
    }


}
// public function findUsersByPhonesAndSort(Request $request)
// {
//     // Récupérer les numéros de téléphone à partir des paramètres de requête
//     $phones = $request->query('phones');

//     // Vérifier si le paramètre de téléphone est présent
//     if (!$phones) {
//         return response()->json(['message' => 'Le paramètre de téléphones est manquant'], 400);
//     }

//     // Convertir la chaîne de numéros de téléphone en un tableau
//     $phoneArray = explode(',', $phones);

//     // Récupérer le paramètre de tri
//     $sortParam = $request->query('params', 'pseudo'); // Par défaut, tri croissant par pseudo

//     // Utiliser une Lazy Collection pour charger les utilisateurs correspondants
//     $users = LazyCollection::make(function () use ($phoneArray) {
//         foreach ($phoneArray as $phone) {
//             $phone = trim($phone); // Supprimer les espaces autour du numéro de téléphone
//             $matchingUsers = User::where('telephone', 'LIKE', "%{$phone}%")->get();

//             foreach ($matchingUsers as $user) {
//                 yield $user;
//             }
//         }
//     });

//     // Trier les utilisateurs en fonction du paramètre `params`
//     if ($sortParam === 'pseudo') {
//         $users = $users->sortBy('pseudo'); // Tri croissant par pseudo (A-Z)
//     } elseif ($sortParam === '-pseudo') {
//         $users = $users->sortByDesc('pseudo'); // Tri décroissant par pseudo (Z-A)
//     }

//     // Convertir la Lazy Collection triée en tableau et retourner en JSON
//     return response()->json($users->values()->all(), 200);
// }



