<?php
namespace App\Services;

use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Hash;
use Request;

class UserService
{
    public function createUser(array $data)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent lister les utilisateurs.'
            ], 403);
        }

        return User::create([
            'pseudo' => $data['pseudo'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
    }

    public function getUsers()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent lister les utilisateurs.'
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

    public function getUsersByRole(string $role)
    {
        if (in_array($role, ['client', 'admin', 'boutiquier'])) {
            return User::where('role', $role)->get();
        }

        return null;
    }

    public function deleteUserById($id)
    {
        try {
            // Rechercher l'utilisateur par ID et le supprimer
            $user = User::findOrFail($id);
            $user->delete();

            return ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Une erreur s\'est produite lors de la suppression de l\'utilisateur.', 'error' => $e->getMessage()];
        }
    }


    // Vous pouvez ajouter d'autres méthodes liées aux utilisateurs ici
}
