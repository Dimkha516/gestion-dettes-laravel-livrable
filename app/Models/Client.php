<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Client extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'surname',
        'telephone',
        'address',
        'status',
        'categorie_id',
        'montant_max'

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

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
}
