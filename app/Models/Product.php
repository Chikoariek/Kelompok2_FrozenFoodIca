<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Produk: Menyimpan data barang makanan beku, stok freezer, dan harga jual
class Product extends Model
{
    use HasFactory;

    // Primary key menggunakan ID string khusus (contoh: P01, P02)
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom produk yang dapat diisi melalui form atau API
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

    // Konversi tipe data numerik dan array label tags
    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'tags'  => 'array',
    ];
}
