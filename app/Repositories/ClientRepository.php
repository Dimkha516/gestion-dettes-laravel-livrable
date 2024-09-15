<?php
namespace App\Repositories;

use App\Models\Client;
use App\Models\User;

class ClientRepository
{
    public function createClient(array $clientData)
    {
        return Client::create($clientData);
    }

    public function createUser(array $userData)
    {
        return User::create($userData);
    }

    public function updateClientWithUser(Client $client, int $userId)
    {
        $client->user_id = $userId;
        $client->save();
    }

    public function findClientById($clientId)
    {
        return Client::find($clientId);
    }
}
