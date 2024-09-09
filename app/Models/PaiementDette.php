<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementDette extends Model
{
    use HasFactory;

    protected $fillable = ['dette_id', 'montant'];

     // Ajoutez ceci si le nom de la table est différent du nom attendu par Laravel (paiement_dettes)
     protected $table = 'paiement_dette';

     public function dette()
    {
        return $this->belongsTo(Dette::class);
    }

}
