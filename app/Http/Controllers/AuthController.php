<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    /**
     * Authentifier l'utilisateur et retourner un token d'accès.
     */
    public function login(Request $request)
    {
        // Valider les données d'entrée
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:5',
        ]);

        // Vérifier les informations d'identification
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        // Authentification réussie
        $user = Auth::user();
        $token = $user->createToken('LaravelAuthApp')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Authentification réussie.',
            'token' => $token,
        ], 200);
    }

    // CRÉER UN COMPTE POUR UN CLIENT APRES CONNEXION ADMIN|BOUTIQUIER:
    public function createAccount(Request $request, $id)
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
                'pseudo' => 'required|string|unique:users,pseudo',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:5|confirmed|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{5,}$/',
                'password_confirmation' => 'required'
            ],
            [
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
        $client = Client::find($id);
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

        return response()->json([
            'message' => 'Compte utilisateur créé avec succès.',
            'client' => $client,
            'user' => $user
        ], 201);
    }



    public function logout(Request $request)
    {
        $user = Auth::user();

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ], 200);

    }
}
