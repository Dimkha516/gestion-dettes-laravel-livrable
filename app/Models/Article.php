<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle',
        'prix',
        'qteStock',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'prix' => 'float',
    ];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    // Relation avec les dettes via la table pivot
    public function dettes()
    {
        return $this->belongsToMany(Dette::class, 'article_dette')
            ->withPivot('qte_vente', 'prix_vente')
            ->withTimestamps();
    }

    // Définir un scope pour les articles disponibles:
    public function scopeAvailable(Builder $query)
    {
        return $query->where('qteStock', '>=', 1);
    }

    // Définir un scope pour les articles non disponibles:
    public function scopeNotAvailable(Builder $query)
    {
        return $query->where('qteStock', '=', 0);
    }

}
