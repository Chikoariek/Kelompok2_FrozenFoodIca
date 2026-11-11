<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Paket Hemat (Bundling): Menyimpan master paket promosi rekomendasi toko
class Bundle extends Model
{
    use HasFactory;

    // Primary key menggunakan ID string khusus (contoh: BND-01, BND-02)
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'image',
        'badge_left',
        'badge_right',
        'tags',
        'original_price',
        'bundle_price',
        'is_active',
    ];

    protected $casts = [
        'tags'           => 'array',
        'original_price' => 'integer',
        'bundle_price'   => 'integer',
        'is_active'      => 'boolean',
    ];

    // Relasi One-to-Many ke BundleItem (Detail item produk di dalam paket)
    public function items()
    {
        return $this->hasMany(BundleItem::class, 'bundle_id');
    }

    // Relasi Many-to-Many ke Model Product melalui tabel pivot bundle_items
    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_items', 'bundle_id', 'product_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}

