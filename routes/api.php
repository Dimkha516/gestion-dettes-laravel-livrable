<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DetteController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\UserController;
use App\Mail\ClientFidelityCardMail;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    //--------------------- ROUTES USERS SANS CONTROLLER:
    //  Lister tous les users: 
    // Route::get('users', function () {
    //     $users = \App\Models\User::all();

    //     if ($users->isEmpty()) {
    //         return response()->json([
    //             'message' => 'Aucun utilisateur trouvé dans la base de données'
    //         ], 404);
    //     }
    //     return response()->json([
    //         'message' => 'Liste des utilisateurs',
    //         'data' => $users
    //     ], 200);

    // })->name('users.index');

    // Afficher un user par son ID
    Route::get('users/{id}', function ($id) {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé dans la base de données'
            ], 404);
        }
        return response()->json([
            'message' => 'Détails de l\'utilisateur',
            'data' => $user
        ], 200);

    })->name('users.show');


    //--------------------- ROUTES AVEC CONTROLLER:



    //----------------------- USERS: 
    // AJOUTER UN NOUVEL UTILISATEUR:
    // Route::post('/store', [UserController::class, 'register'])
    // ->name('users.store');

    Route::prefix('users')->group(function () {

        // LISTER TOUS LES UTILISATEURS:
        Route::middleware('auth:api')->get('/', [UserController::class, 'getUsers'])->name('users.idenx');

        // LISTER LES UTILISATEUR PAR ROLE:
        Route::middleware('auth:api')->get("/role/filter", [UserController::class, 'filterByRole'])->name('users.role');

        // AJOUTER UN NOUVEL UTILISATEUR:
        Route::middleware('auth:api')->post('/register', [UserController::class, 'store'])->name('users.store');

        // Route::post('/login', [UserController::class, 'login'])->name('user.login');
        Route::post('/login', [AuthController::class, 'login'])->name('user.login');
        Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout'])->name('user.logout');

        // Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout'])->name('users.logout');

        // METTRE A JOUR UN USER:
        Route::middleware('auth:api')->put('/{id}', [UserController::class, 'update'])
            ->name('users.update');

        Route::middleware('auth:api')->patch('/{id}', [UserController::class, 'update'])
            ->name('users.update');

            Route::middleware('auth:api')->delete('/{id}', [UserController::class, 'deleteUser'])
            ->name('user.delete');

        // CRÉER UN COMPTE POUR UN CLIENT APRES CONNEXION ADMIN|BOUTIQUIER:
        Route::middleware('auth:api')->post('/clientAccount', [AuthController::class, 'createAccount'])->name('createClient.account');
    });


    //------------ ROUTES CLIENTS:

    Route::prefix('clients')->group(function () {

        // Route pour lister tous les clients
        Route::middleware('auth:api')->get('/', [ClientController::class, 'index'])->name('clients.index');
        // });

        // Route pour lister un client par ID
        Route::middleware('auth:api')->get('/{id}', [ClientController::class, 'show'])->name('clients.show');

        // Route pour afficher un client avec son compte:
        Route::middleware('auth:api')->get("/{id}/user", [ClientController::class, 'showClientWithUser'])->name('client.account');

        // Route pour rechercher un client par téléphone:
        Route::middleware('auth:api')->get('/phone', [ClientController::class, 'findByPhone'])->name('client.phone');

        // Route pour rechercher plusieurs clients par téléphone:
        Route::middleware('auth:api')->get('/phones', [ClientController::class, 'findUsersByPhones'])->name('clients.phones');

        // FILTRE PAR CLIENT AVEC OU SANS COMPTE:
        Route::middleware('auth:api')->get("/account/filter", [ClientController::class, 'listByAccount'])->name('clients.accounts');

        // FILTRE PAR STATUS ACTIF OU BLOQUE:
        Route::middleware('auth:api')->get("/status/filter", [ClientController::class, 'listByStatus'])->name('clients.status');

        // Route pour ajouter un nouveau client sans compte
        Route::middleware('auth:api')->post('/', [ClientController::class, 'store'])->name('clients.store');

        // Route pour ajouter un nouveau client avec compte
        Route::middleware('auth:api')->post('/client/user', [ClientController::class, 'storeWithAccount'])->name('clientUser.store');

        Route::middleware('auth:api')->post('/mailTest', [ClientController::class, 'testSendMail']);

    });

    // -------------- ARTICLES:
    Route::prefix('articles')->group(function () {
        Route::middleware('auth:api')->get("/", [ArticleController::class, 'index'])->name('articles.index');
        Route::middleware('auth:api')->get('/{id}', [ArticleController::class, 'show'])->name('articles.show');
        Route::middleware('auth:api')->post("/search/libelle", [ArticleController::class, 'searchByLibelle'])->name('articles.search');
        Route::middleware('auth:api')->get("/search/dispo", [ArticleController::class, 'filterByAvailability'])->name('articles.dispo');


        // FONCTIONS EN STAND BY: ELLES PERMETTENT DE MODIFIER UN OU PLUSIEURS ATTRIBUTS:
        // Route::put('/{id}', [ArticleController::class, 'update'])->name('articles.update');
        // Route::patch('/patch/{id}', [ArticleController::class, 'update'])->name('articles.update');

        Route::middleware('auth:api')->patch("/{id}", [ArticleController::class, 'updateOne'])->name('articleQte.update');

        Route::middleware('auth:api')->post('/', [ArticleController::class, 'store'])->name('articles.store');
        Route::middleware('auth:api')->post('/restore/{id}', [ArticleController::class, 'restore'])->name('articles.restore');
        Route::middleware('auth:api')->post('/all', [ArticleController::class, 'addStock'])->name('articles.addStock');
        Route::middleware('auth:api')->delete('/delete/{id}', [ArticleController::class, 'destroy'])->name('articles.destroy');
    });

    // -------------- DETTES:
    Route::prefix('dettes')->group(function () {
        
        // LISTER TOUTES LES DETTES:
        Route::middleware('auth:api')->get('/', [DetteController::class, 'index'])->name('dettes-list');

        // FILTRER DETTES PAR STATUS(SOLDÉ / NON SOLDÉ):
        Route::middleware('auth:api')->get('/solde', [DetteController::class, 'filterDettes'])->name('dette-solde');
        
          // LISTER LES PAIEMENTS D'UNE DETTE:
          Route::middleware('auth:api')->get('/paymentsList/{id}', [DetteController::class, 'getPaiementsByDette'])->name('dettte-payments');

        // LISTER LES ARTICLES D'UNE DETTE:
        Route::middleware('auth:api')->get('/{id}/articles', [DetteController::class, 'getArticles'])->name('dette-articles');

        // AJOUTER NOUVELLE DETTE:
        Route::middleware('auth:api')->post('/', [DetteController::class, 'store'])->name('add-dette');

        // LISTER UNE DETTE PAR SON ID:

        
        // LISTER LES PAIEMENTS D'UNE DETTE:


    });

    // -------------- PAIEMENTS:
    Route::prefix('paiements')->group(function () {
         
        // LISTER TOUS LES PAIEMENTS:
        Route::middleware('auth:api')->get("/", [PaiementController::class, 'index'])->name('payments-liste');

        // AJOUTER UN PAEIMENT À UNE DETTE:
        Route::middleware('auth:api')->post("/", [PaiementController::class, 'store'])->name('add-payment');


    });

});
