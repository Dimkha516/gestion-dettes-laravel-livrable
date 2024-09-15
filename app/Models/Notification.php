<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'content', 'is_read'];

    // Définir la relation avec le modèle Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
