<?php

namespace App\Models;

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

    protected $hidden = [];

    protected $casts = [
        'prix' => 'float',
    ];

    protected $guarded = [];

    protected $dates = ['deleted_at'];  

}
