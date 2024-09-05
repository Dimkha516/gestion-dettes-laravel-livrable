<?php
namespace App\Services;


use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use App\Models\Client;
use App\Models\User;
use Cloudinary\Cloudinary;
use Barryvdh\DomPDF\Facade as PDF;
use App\Mail\ClientFidelityCardMail;
use Illuminate\Support\Facades\Mail;
use Hash;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;


class ClientService
{
    protected $cloudinary;
    public function __construct()
    {
        // dd([
        //     'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
        //     'api_key' => config('filesystems.disks.cloudinary.api_key'),
        //     'api_secret' => config('filesystems.disks.cloudinary.api_secret')
        // ]);

        // $this->cloudinary = new Cloudinary([
        //     'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
        //     'api_key' => config('filesystems.disks.cloudinary.api_key'),
        //     'api_secret' => config('filesystems.disks.cloudinary.api_secret')
        // ]);
        $this->cloudinary = new Cloudinary([
            'cloud_name' => 'dytchfsin',
            'api_key' => '247799294424117',
            'api_secret' => 'd8xcCTIP_coa_JxUOeTQt0Ik2vs'
        ]);

    }

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
        return Client::create([
            'surname' => $data['surname'],
            'telephone' => $data['telephone'],
            'adresse' => $data['adresse'] ?? null,
            'status' => 'actif',
        ]);
    }

    public function createClientWithAccount(array $clientData, array $userData, $photo = null)
    {
        \DB::beginTransaction();

        try {
            // Gérer l'upload de la photo si elle est fournie
            if ($photo) {
                $uploadResult = $this->cloudinary->uploadApi()->upload($photo->getRealPath(), [
                    'folder' => 'clients_photos',
                    'public_id' => $clientData['surname'] . '_' . time(),
                    'resource_type' => 'image'
                ]);

                // Ajouter l'URL de la photo au clientData
                $clientData['photo'] = $uploadResult['secure_url'];
            } else {
                // Si aucune photo n'est fournie, utiliser une photo par défaut
                $clientData['photo'] = 'https://res.cloudinary.com/dytchfsin/image/upload/v1725465088/xcb8pgm42qc6vkzgwnvd.png';
            }

            // Créer le client
            $client = Client::create([
                'surname' => $clientData['surname'],
                'telephone' => $clientData['telephone'],
                'adresse' => $clientData['adresse'] ?? null,
                'photo' => $clientData['photo'],
                // 'photo' => 'https://res.cloudinary.com/dytchfsin/image/upload/v1725465088/xcb8pgm42qc6vkzgwnvd.png',
                'status' => 'actif',
            ]);

            // Créer l'utilisateur
            $user = User::create([
                'pseudo' => $userData['pseudo'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'client',
            ]);

            // Associer l'utilisateur créé au client
            $client->user_id = $user->id;
            $client->save();
            // $client->users->save();

            // Générer le code QR pour le client
            $qrContent = 'Client ID: ' . $client->id . ', Nom: ' . $client->surname;

            // Utiliser le Builder pour générer le QR code
            $qrCode = Builder::create()
                ->writer(new PngWriter())
                ->data($qrContent)
                ->encoding(new Encoding('UTF-8'))
                ->size(300)
                ->build();

            // Chemin pour enregistrer le QR code
            $qrPath = 'qrcodes/clients/' . $client->surname . '_' . $client->id . '.png';
            Storage::disk('local')->put($qrPath, $qrCode->getString());

            // Générer la carte de fidélité en PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('fidelite_card', [
                'pseudo' => $user->pseudo,
                'email' => $user->email,
                'qrCodePath' => $qrPath,
                'photoUrl' => $clientData['photo']
            ]);

            // Sauvegarder le PDF dans un dossier
            $pdfPath = 'fidelite_cards' . $client->surname . '_' . $client->id . '.pdf';
            Storage::disk('local')->put($pdfPath, $pdf->output());

            Mail::to($user->email)->send(new ClientFidelityCardMail($client, $user, $pdfPath));

            \DB::commit();

            return [
                'client' => $client,
                'user' => $user,
                'qr_code_path' => $qrPath,
                'fidelite_card_path' => $pdfPath
            ];
        } catch (\Exception $e) {
            \DB::rollBack();
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
