<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'surname',
        'telephone',
        'address',
        'status' // Ajoutez le statut ici

    ];

    protected $hidden = [];

    protected $casts = [];

    protected $guarded = [
        'id',
    ];
}
