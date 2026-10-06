<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Pesanan: Menyimpan riwayat transaksi pelanggan dan data struk kasir
class Order extends Model
{
    use HasFactory;

    // Primary key menggunakan format teks acak (contoh: ORD-ICA-1234)
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom tabel yang diizinkan untuk diisi massal (mass-assignment)
    protected $fillable = [
        'id',
        'customer_name',
        'customer_phone',
        'address',
        'order_date',
        'method',
        'payment_method',
        'status',
        'items',
        'subtotal',
        'shipping_fee',
        'ice_fee',
        'total',
        'is_paid',
        'channel',
    ];

    // Konversi tipe data otomatis (JSON items jadi array, nominal jadi integer)
    protected $casts = [
        'items'        => 'array',
        'subtotal'     => 'integer',
        'shipping_fee' => 'integer',
        'ice_fee'      => 'integer',
        'total'        => 'integer',
        'is_paid'      => 'boolean',
    ];

    // Accessor cadangan: Memastikan pemanggilan $order->ice_fee dialihkan ke shipping_fee
    public function getIceFeeAttribute()
    {
        return $this->attributes['shipping_fee'] ?? $this->attributes['ice_fee'] ?? 0;
    }

    // Mutator cadangan: Menyimpan nilai ice_fee langsung ke kolom shipping_fee
    public function setIceFeeAttribute($value)
    {
        $this->attributes['shipping_fee'] = (int) $value;
    }
}
