<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model data kategori produk makanan beku
class Category extends Model
{
    use HasFactory;

    // Kolom tabel yang dapat diisi secara massal (Mass Assignment)
    protected $fillable = [
        'name',
        'description',
    ];
}
