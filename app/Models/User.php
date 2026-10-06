<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Model Pengguna (User): Mengelola akun pelanggan & administrator
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Kolom data profil akun yang dapat diisi
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Hak akses (admin / user)
        'phone',
        'address',
        'avatar',
        'is_admin',
    ];

    // Kolom rahasia yang disembunyikan saat data diubah ke JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Format konversi tipe data keamanan & boolean
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // Helper pengecekan hak akses apakah pengguna adalah administrator
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || (bool) ($this->is_admin ?? false);
    }

    /**
     * Accessor is_admin agar backward compatible dengan seluruh kode Blade & Frontend.
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin' || (bool) ($this->attributes['is_admin'] ?? false);
    }
}
