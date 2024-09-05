<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function authenticate(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect.', 'status' => 401];
        }

        // Authentification réussie
        $user = Auth::user();
        $token = $user->createToken('LaravelAuthApp')->accessToken;

        return ['success' => true, 'message' => 'Authentification réussie.', 'token' => $token, 'status' => 200];
    }

    public function logout()
    {
        $user = Auth::user();

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return ['success' => true, 'message' => 'Déconnexion réussie.', 'status' => 200];
    }
}
