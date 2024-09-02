<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
// use Auth;
use Hash;
use Illuminate\Http\Request;


class UserController extends Controller
{
    // Ajouter un nouvel utilisateur:
    public function store(StoreUserRequest $request)
    {
        // Validation réussie, créer un nouvel utilisateur
        $user = User::create([
            'pseudo' => $request->pseudo,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès.',
            'data' => $user,
        ], 201); // 201 Created
    }

    public function getUsers() {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }

        $users = User::all();
        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'Aucun utilisateur trouvé dans la base de données'
            ], 404);
        }
        return response()->json([
            'message' => 'Liste des utilisateurs',
            'data' => $users
        ], 200);
    }

    

    // AFFICHER LES UTILISATEURS PAR ROLE:
    public function filterByRole(Request $request)
    {
        $role = $request->query('role');

        if ($role === 'client' || $role === 'admin' || $role === 'boutiquier') {
            $users = User::where('role', $role)->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre status invalide. Utilisez "client" ou "admin" ou "boutiquier".',
            ], 400); // 400 Bad Request
        }

        return response()->json([
            'sucess' => true,
            'message' => "liste des article avec le role : ",
            $role,
            'data' => $users
        ], 200);
    }

    // Mettre à jour un utilisateur existant
    public function update(Request $request, $id)
    {
        // Valider les données avec CustomValidator
        $validator = UserRequest::validateUserUpdate($request->all(), $id);

        // Vérifier si la validation a échoué
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les données fournies ne sont pas valides.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Les données sont validées
        $validatedData = $validator->validated();

        $user = User::findOrFail($id);

        // Mettre à jour les champs spécifiés
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        // Réponse JSON avec l'utilisateur mis à jour
        return response()->json($user, 200);
    }

    public function deleteUser($id)
    {
        try {
            // Rechercher l'utilisateur par ID
            $user = User::findOrFail($id);

            // Supprimer l'utilisateur
            $user->delete();

            // Réponse JSON en cas de succès
            return response()->json([
                'message' => 'Utilisateur supprimé avec succès.',
            ], 200);
        } catch (\Exception $e) {
            // Réponse JSON en cas d'erreur
            return response()->json([
                'message' => 'Une erreur s\'est produite lors de la suppression de l\'utilisateur.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
