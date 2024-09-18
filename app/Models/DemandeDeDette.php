<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeDeDette extends Model
{
    use HasFactory;

    protected $table = 'demandes_de_dette';

    protected $fillable = [
        'client_id',
        'articles',
        'montant_total',
        'etat', // Ajout de la colonne `etat`

    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // Définir les valeurs possibles pour l'état
    const ETAT_ENCOURS = 'encours';
    const ETAT_ANNULE = 'annule';
    const ETAT_VALIDE = 'valide';

    // Méthode pour vérifier l'état de la demande
    public function isEncours()
    {
        return $this->etat === self::ETAT_ENCOURS;
    }

    // Un client peut avoir plusieurs demandes
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
