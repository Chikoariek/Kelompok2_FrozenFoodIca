<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Detail Paket Hemat: Menghubungkan paket bundling dengan produk (Pivot/Junction Table)
class BundleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relasi Many-to-One ke Master Bundle
    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    // Relasi Many-to-One ke Master Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

