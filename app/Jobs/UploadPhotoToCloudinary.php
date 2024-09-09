<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cloudinary\Cloudinary;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

use Illuminate\Support\Facades\Storage;


class UploadPhotoToCloudinary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cloudinary;

    protected $photo;
    protected $tempPath;

    protected $clientData;

    /**
     * Create a new job instance.
     */
    public function __construct($tempPath, $clientData)
    {
        $this->tempPath = $tempPath;
        $this->clientData = $clientData;

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // Utiliser l'instance Cloudinary pour uploader l'image
        $uploadResult = $this->cloudinary->uploadApi()->upload(Storage::disk('local')->path($this->tempPath), [
            'folder' => 'clients_photos',
            'public_id' => $this->clientData['surname'] . '_' . time(),
            'resource_type' => 'image'
        ]);

          // Assigner l'URL sécurisée à la photo du client
          $this->clientData['photo'] = $uploadResult['secure_url'];

        // Supprimer le fichier temporaire après l'upload
        Storage::disk('local')->delete($this->tempPath);
    }
}
