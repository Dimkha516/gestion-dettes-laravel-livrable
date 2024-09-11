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

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [];

    protected $guarded = [
        'id',
    ];

    public function user()
{   
    // AVANT MODIF
    // return $this->belongsTo(User::class);

    // APRES MODIF
    return $this->belongsTo(User::class, 'user_id');
}
}
