<?php
namespace App\Services;

// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

use App\Events\ClientCreatedEvent;
use App\Jobs\GenerateFidelityCard;
use App\Jobs\ProcessClientCreation;
use App\Jobs\UploadPhoto;
use App\Jobs\UploadPhotoToCloudinary;
use App\Models\Categorie;
use App\Repositories\ClientRepository;
use Cloudinary\Cloudinary;
use Cloudinary\Uploader;

use DB;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use App\Models\Client;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use App\Mail\ClientFidelityCardMail;
use Illuminate\Support\Facades\Mail;
use Hash;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;


class ClientService
{
    protected $clientRepo;
    protected $cloudinary;
    public function __construct(ClientRepository $clientRepo, Cloudinary $cloudinary)
    {
        $this->clientRepo = $clientRepo;
        $this->cloudinary = $cloudinary;
    }

    // dd([
    //     'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
    //     'api_key' => config('filesystems.disks.cloudinary.api_key'),
    //     'api_secret' => config('filesystems.disks.cloudinary.api_secret')
    // ]);
    public function getAllClients()
    {
        $clients = Client::all();

        if ($clients->isEmpty()) {
            return ['success' => false, 'message' => 'Aucun client existe dans la base de données', 'status' => 404];
        }

        return ['success' => true, 'message' => 'Liste des clients', 'data' => $clients, 'status' => 200];
    }

    public function getClientById($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return ['success' => false, 'message' => 'Client non trouvé', 'status' => 404];
        }

        return ['success' => true, 'message' => 'Client recherché', 'data' => $client, 'status' => 200];
    }

    public function createClient(array $data)
    {

        // Si la catégorie est fournie, utiliser celle-ci, sinon, définir la catégorie par défaut 'Bronze'
        if (empty($data['categorie_id'])) {
            $data['categorie_id'] = Categorie::where('libelle', 'Bronze')->value('id');
        }

        // Si la catégorie est Silver, le montant_max doit être défini, sinon on le laisse null
        $categorieSilverId = Categorie::where('libelle', 'Silver')->value('id');
        if ($data['categorie_id'] == $categorieSilverId && empty($data['montant_max'])) {
            throw new \Exception('Le montant_max est requis pour la catégorie Silver.');
            // return "Le montant_max est requis pour la catégorie Silver.";
        }

        return $this->clientRepo->createClient([
            'surname' => $data['surname'],
            'telephone' => $data['telephone'],
            'adresse' => $data['adresse'] ?? null,
            'status' => $data['status'] ?? 'actif',
            'categorie_id' => $data['categorie_id'], // toujours défini ici
            'montant_max' => $data['montant_max'] ?? null,
        ]);

    }


    public function createClientWithAccount(array $clientData, array $userData, $photo)
    {
        DB::beginTransaction();

        try {
            if ($photo) {
                $tempPath = $photo->store('temp');
                UploadPhotoToCloudinary::dispatch($tempPath, $clientData, $photo);
            }
            // 
            else {
                $clientData['photo'] = 'https://res.cloudinary.com/dytchfsin/image/upload/v1725465088/xcb8pgm42qc6vkzgwnvd.png';
            }


            // Créer le client et l'utilisateur:
            $client = $this->clientRepo->createClient($clientData);
            if (!$client) {
                throw new \Exception('Failed to create client');
            }
            $user = $this->clientRepo->createUser([
                'pseudo' => $userData['pseudo'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'client',
            ]);
            if (!$user) {
                throw new \Exception('Failed to create user');
            }
            // Lier le client à l'utilisateur
            $this->clientRepo->updateClientWithUser($client, $user->id);



            DB::commit();

            return [
                'client' => $client,
                'user' => $user,
            ];
        }
        // 
        catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }
    public function updateClient($id, $data)
    {
        $client = Client::findOrFail($id);
        $client->update($data);
        return $client;
    }

    public function deleteClient($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return $client;
    }

    public function findClientByPhone($phone)
    {
        return Client::where('telephone', 'LIKE', "%{$phone}%")->get();
    }

    public function findClientsByPhones(array $phoneArray): LazyCollection
    {
        return LazyCollection::make(function () use ($phoneArray) {
            foreach ($phoneArray as $phone) {
                $phone = trim($phone); // Supprimer les espaces autour du numéro de téléphone
                $clients = Client::where('telephone', 'LIKE', "%{$phone}%")->get();

                foreach ($clients as $client) {
                    yield $client;
                }
            }
        });
    }


    public function listClientsByAccount(string $compte)
    {
        if ($compte === 'oui') {
            // Liste des clients avec un compte utilisateur
            return Client::whereNotNull('user_id')->get();
        } elseif ($compte === 'non') {
            // Liste des clients sans compte utilisateur
            return Client::whereNull('user_id')->get();
        }

        return null;
    }

    public function listClientsByStatus(string $status)
    {
        return Client::where('status', $status)->get();
    }

}
