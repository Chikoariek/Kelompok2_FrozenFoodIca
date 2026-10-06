<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'category',
        'price',
        'stock',
        'weight',
        'description',
        'image',
        'tags',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'tags'  => 'array',
    ];
}
