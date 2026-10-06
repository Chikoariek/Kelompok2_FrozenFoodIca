# 🧊 Ica Frozen Food — Web E-Commerce & Point of Sale (POS)

Sistem Web E-Commerce dan Kasir Pintar (Point of Sale) modern untuk **Ica Frozen Food** yang menyajikan produk makanan beku higienis, lezat, dan praktis bagi keluarga Indonesia.

---

## 📌 Daftar Isi
1. [Tentang Proyek](#-tentang-proyek)
2. [Fitur Unggulan](#-fitur-unggulan)
3. [Teknologi yang Digunakan (Tech Stack)](#-teknologi-yang-digunakan-tech-stack)
4. [Struktur Direktori & Arsitektur](#-struktur-direktori--arsitektur)
5. [Akun Demo untuk Presentasi & Pengujian](#-akun-demo-untuk-presentasi--pengujian)
6. [Panduan Instalasi & Menjalankan Proyek](#-panduan-instalasi--menjalankan-proyek)
7. [Panduan Pemeliharaan (Maintenance Guide)](#-panduan-pemeliharaan-maintenance-guide)

---

## 📖 Tentang Proyek
Aplikasi ini dikembangkan untuk mendigitalisasi operasional penjualan ritel dan grosir **Ica Frozen Food**:
- **Sisi Pelanggan (Customer Facing)**: Pengunjung dapat melihat katalog lengkap tanpa harus login. Autentikasi hanya diwajibkan saat pelanggan ingin melakukan checkout/order.
- **Sisi Kasir & Admin (Merchant Facing)**: Panel kasir cepat (*POS Quick Checkout*), inventaris stok barang real-time, pencatatan transaksi struk nota otomatis, dan dashboard analisis penjualan.

---

## ✨ Fitur Unggulan

### 1. 🛒 Sisi Pelanggan (Customer Experience)
- **Floating Navbar Dinamis**:
  - Navbar menempel di atas saat posisi awal dan bertransformasi halus (*smooth transition*) menjadi pil mengambang saat halaman digulir (*scrolled*).
  - Tampilan profil pengguna berbentuk lingkaran dengan 2 huruf inisial nama otomatis (contoh: "Budi Doremi" → `BD`).
- **Katalog & Filter Interaktif**:
  - Filter kategori cepat (*Semua, Dimsum & Siomay, Nugget & Sosis, Olahan Seafood, Daging Beku*).
  - Pencarian real-time berdasarkan nama produk, kategori, dan deskripsi.
- **Smart Recommendations & Paket Hemat**:
  - Bundle hemat otomatis lengkap dengan perayaan animasi konfeti saat ditambahkan ke keranjang.
- **Keranjang Belanja (*Cart Drawer*)**:
  - Manajemen kuantitas produk, validasi batas stok otomatis, ringkasan subtotal, dan integrasi checkout.
- **Manajemen Profil & Alamat Pelanggan**:
  - Pelanggan dapat mengubah data diri (Nama, Email, Nomor WhatsApp, dan Alamat Pengiriman lengkap) serta memperbarui kata sandi secara mandiri.

### 2. 🛡️ Sisi Admin & Kasir (Merchant Dashboard & POS)
- **Dashboard Analisis**:
  - Ringkasan omzet harian, total transaksi, unit produk terjual, dan status persediaan stok menipis.
- **Kasir Cepat POS (*Point of Sale*)**:
  - Antarmuka kasir cepat berbasis barcode/pencarian, perhitungan uang kembalian otomatis, dan cetak nota struk transaksi fisik/modal.
- **Manajemen Inventaris & Data Produk**:
  - Pemantauan stok produk, status ketersediaan, serta filter kategori.
- **Profil Administrator**:
  - Halaman khusus admin untuk memperbarui profil, nomor telepon operasional toko, alamat outlet, dan kata sandi keamanan.

### 3. 🔐 Autentikasi & Keamanan
- **Halaman Login & Registrasi Responsif**:
  - Desain *split-screen* modern dengan showcase freezer di sisi kiri dan form interaktif di sisi kanan.
  - Ikon input sesuai warna tema brand (`#677D9E`).
  - Fitur toggle tombol mata (*show/hide password*) pada setiap kolom sandi.
  - Perlindungan CSRF token dan validasi terenkripsi.

---

## 🛠️ Teknologi yang Digunakan (Tech Stack)

| Bagian | Teknologi | Keterangan |
|---|---|---|
| **Backend** | Laravel 11.x (PHP 8.2+) | Framework MVC handal dengan Laravel Breeze Auth & Eloquent ORM |
| **Frontend Template** | Laravel Blade Engine | Server-Side Templating murni tanpa framework frontend |
| **Frontend Logic** | Pure Vanilla JavaScript | Logika interaktif native (DOM API, Fetch API, tanpa library/framework JS eksternal) |
| **Styling** | Pure Vanilla CSS | Desain responsif tulisan tangan (CSS Custom Properties, Flexbox, CSS Grid, tanpa Tailwind/Bootstrap) |
| **Database** | MySQL / SQLite | Penyimpanan persisten data pengguna, produk, kategori, dan pesanan |
| **Icons** | Pure Vector SVG Icons | Ikon inline SVG yang tajam, ringan, dan cepat dimuat |
| **Animations** | Native CSS Keyframes & Transitions | Efek mikro-animasi visual yang halus, proporsional, dan elegan |

---

## 🎨 Brand Palette Warna

Aplikasi ini menggunakan palet warna khusus yang serasi dan profesional:
- **Primary / Brand Blue**: `#677D9E`
- **Dark Text / Heading**: `#2C3E50`
- **Light Cream Background**: `#F1F0E8`
- **Border & Muted Gray**: `#A6B1C3`
- **Dark Accent**: `#546885`

---

## 📁 Struktur Direktori & Arsitektur

```text
Kelompok2_FrozenFoodIca/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminDataController.php# API CRUD Produk, Kategori, Status Pesanan
│   │   │   ├── Auth/                  # Controller Login, Register, Password
│   │   │   └── ProfileController.php  # Update Profil & Alamat
│   │   ├── Middleware/
│   │   │   └── AdminMiddleware.php    # Proteksi rute khusus role admin
│   │   └── Requests/                  # Validasi form request profil & registrasi
│   └── Models/
│       ├── Category.php               # Model Kategori Produk
│       ├── Order.php                  # Model Pesanan & Transaksi Kasir
│       ├── Product.php                # Model Produk Makanan Beku
│       └── User.php                   # Model pengguna (Role, Phone, Address)
│
├── database/
│   ├── migrations/                    # Skema tabel database (Users, Products, Categories, Orders)
│   └── seeders/
│       ├── AdminSeeder.php            # Seeder akun demo (Admin & Pelanggan)
│       └── DatabaseSeeder.php         # Entry point seeder database
│
├── public/
│   ├── css/
│   │   ├── style.css                  # Pure Vanilla CSS untuk Halaman Pelanggan & Katalog
│   │   └── admin.css                  # Pure Vanilla CSS untuk Dashboard Admin & POS Kasir
│   └── js/
│       ├── store.js                   # Pure Vanilla JS untuk Toko, Keranjang, Checkout, & Animasi
│       └── admin.js                   # Pure Vanilla JS untuk Dashboard Admin, POS Kasir, CRUD & Stok
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── guest.blade.php        # Layout split-screen login & register (Pure CSS)
│       ├── auth/
│       │   ├── login.blade.php        # Halaman login
│       │   └── register.blade.php     # Halaman registrasi baru
│       ├── welcome.blade.php          # Tampilan Utama Pelanggan, Katalog, & Keranjang
│       └── admin.blade.php            # Tampilan Dashboard Admin, POS Kasir, & Inventaris
│
├── routes/
│   ├── web.php                        # Routing utama aplikasi & API endpoints database
│   └── auth.php                       # Routing autentikasi Breeze
│
└── README.md                          # Dokumentasi proyek
```

---

## 🔑 Akun Demo untuk Presentasi & Pengujian

Aplikasi telah dilengkapi seeder akun demo siap pakai:

| Role / Akses | Email | Password | Hak Akses |
|---|---|---|---|
| **Super Admin** | `admin@icafrozenfood.com` | `admin123` | Akses penuh dashboard, inventaris, POS, dan profil admin |
| **Kasir Toko** | `kasir@icafrozenfood.com` | `kasir123` | Akses penjualan POS dan cetak struk |
| **Pelanggan** | `budi@example.com` | `password123` | Akses belanja, simpan alamat pengiriman, ubah profil |

---

## 🚀 Panduan Instalasi & Menjalankan Proyek

### 1. Kebutuhan Sistem
- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18.x & NPM
- Laragon / XAMPP / MySQL

### 2. Langkah Instalasi

```bash
# 1. Clone repository (atau buka folder proyek)
cd d:\laragon\www\Kelompok2_FrozenFoodIca

# 2. Install dependensi PHP
composer install

# 3. Install dependensi JavaScript (React & Tailwind)
npm install

# 4. Salin file environment & generate App Key
copy .env.example .env
php artisan key:generate

# 5. Jalankan migrasi dan seeder database
php artisan migrate --seed

# 6. Kompilasi asset frontend
npm run build
# (atau jalankan `npm run dev` untuk hot reload saat development)

# 7. Jalankan server lokal Laravel
php artisan serve
```

Aplikasi dapat dibuka di browser:
- Halaman Pelanggan / Katalog: **http://127.0.0.1:8000**
- Halaman Login: **http://127.0.0.1:8000/login**
- Halaman Registrasi: **http://127.0.0.1:8000/register**
- Dashboard Admin (setelah login admin): **http://127.0.0.1:8000/admin**

---

## 🔧 Panduan Pemeliharaan (Maintenance Guide)

### Menambah atau Mengubah Data Produk
- Data katalog awal disimpan pada [`resources/js/data/mockData.js`](file:///d:/laragon/www/Kelompok2_FrozenFoodIca/resources/js/data/mockData.js).
- Setiap produk memiliki properti `id`, `name`, `category`, `price`, `stock`, `rating`, `sold`, `image`, dan `desc`.

### Menyesuaikan Warna Tema
- Jika ingin memodifikasi palet warna, periksa file [`resources/css/app.css`](file:///d:/laragon/www/Kelompok2_FrozenFoodIca/resources/css/app.css) dan sesuaikan kode hex warna utama:
  - `#677D9E` untuk tombol dan elemen aksen biru.
  - `#F1F0E8` untuk latar belakang halaman.

### Pembaruan Skrip Frontend
Setiap kali melakukan perubahan pada komponen React di folder `resources/js/`:
```bash
npm run build
php artisan view:clear
```

---

Dibuat dengan ❤️ oleh **Kelompok 2 - Ica Frozen Food**.
