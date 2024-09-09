<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cloudinary\Cloudinary;

class UploadPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $clientId;
    protected $clientData;
    protected $photoPath;
    protected $cloudinary;

    public function __construct(int $clientId, string $photoPath)
    {
        $this->clientId = $clientId;
        $this->photoPath = $photoPath;

        $this->cloudinary = new Cloudinary([
            'cloud_url' => 'cloudinary://247799294424117:d8xcCTIP_coa_JxUOeTQt0Ik2vs@dytchfsin',
            'cloud_name' => 'dytchfsin',
            'api_key' => '247799294424117',
            'api_secret' => 'd8xcCTIP_coa_JxUOeTQt0Ik2vs'
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {   
        $client = Client::find($this->clientId);
        if ($this->photoPath) {
            $uploadResult = $this->cloudinary->uploadApi()->upload($this->photoPath, [
                'folder' => 'clients_photos',
                'public_id' => $this->clientData['surname'] . '_' . time(),
                'resource_type' => 'image'
            ]);
            $client->photo = $uploadResult['secure_url'];
        } else {
            $client->photo = 'https://res.cloudinary.com/dytchfsin/image/upload/v1725465088/xcb8pgm42qc6vkzgwnvd.png';
        }
        $client->save();
    }
}
