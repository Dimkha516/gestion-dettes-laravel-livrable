<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dette extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'montant', 'montant_paiement'];

    // Relation avec Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relation avec les articles via la table pivot
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_dette')
            ->withPivot('qte_vente', 'prix_vente')
            ->withTimestamps();
    }

    // Relation avec PaiementDette
    public function paiements()
    {
        return $this->hasMany(PaiementDette::class);
    }

}
