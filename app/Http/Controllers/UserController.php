<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
// use Auth;
use Hash;
use Illuminate\Http\Request;


class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    // Ajouter un nouvel utilisateur:
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $user = $this->userService->createUser($data);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès.',
            'data' => $user,
        ], 201); // 201 Created
    }

    public function getUsers()
    {
        $users = $this->userService->getUsers();
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

        // Appel du service pour obtenir les utilisateurs par rôle
        $users = $this->userService->getUsersByRole($role);

        if ($users === null) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre role invalide. Utilisez "client" ou "admin" ou "boutiquier".',
            ], 400); // 400 Bad Request
        }

        return response()->json([
            'success' => true,
            'message' => "Liste des utilisateurs avec le rôle : $role",
            'data' => $users,
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
        $result = $this->userService->deleteUserById($id);
 
        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 200);
        } else {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'],
            ], 500);
        }
    }
}
