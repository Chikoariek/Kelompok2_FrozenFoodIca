<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

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
        'ice_fee',
        'total',
        'is_paid',
        'channel',
    ];

    protected $casts = [
        'items'   => 'array',
        'subtotal'=> 'integer',
        'ice_fee' => 'integer',
        'total'   => 'integer',
        'is_paid' => 'boolean',
    ];
}
