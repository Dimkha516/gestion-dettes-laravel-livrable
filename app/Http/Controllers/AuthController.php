<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Jobs\MailToNewUserJob;
use App\Models\Client;
use App\Models\User;
use App\Services\AuthService;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    /**
     * Authentifier l'utilisateur et retourner un token d'accès.
     */

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $result = $this->authService->authenticate($credentials);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'token' => $result['token'] ?? null,
        ], $result['status']);
    }

    // CRÉER UN COMPTE POUR UN CLIENT APRES CONNEXION ADMIN|BOUTIQUIER:
    public function createAccount(Request $request)
    {
        // Vérifiez si l'utilisateur a le rôle requis
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins et boutiquiers peuvent créer un compte pour client.'
            ], 403);
        }

        // Validation des données
        $validatedData = $request->validate(
            [
                'id' => 'required|integer',
                'pseudo' => 'required|string|unique:users,pseudo',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:5|confirmed|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{5,}$/',
                'password_confirmation' => 'required',
            ],
            [
                'id.required' => 'Vous devez saisir ID du client !',
                'id.integer' => 'ID saisi non valide !',
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

        // Trouvez le client
        // $client = Client::find($id);
        $client = Client::find($validatedData['id']);
        if (!$client) {
            return response()->json([
                'message' => 'Client non trouvé.'
            ], 404);
        }

        // Vérifiez si le client a déjà un compte
        if ($client->user_id) {
            return response()->json([
                'message' => 'Ce client a déjà un compte.'
            ], 400);
        }

        // Création du compte utilisateur
        $user = User::create([
            'pseudo' => $validatedData['pseudo'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'client'
        ]);

        // Associez l'ID du user au client
        $client->user_id = $user->id;
        $client->save();
        
        MailToNewUserJob::dispatch($user);

        return response()->json([
            'message' => 'Compte utilisateur créé avec succès.',
            'client' => $client,
            'user' => $user
        ], 201);

    }



    public function logout(Request $request)
    {
        $result = $this->authService->logout();

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['status']);
    }
}
