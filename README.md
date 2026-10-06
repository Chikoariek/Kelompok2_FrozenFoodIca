# Ica Frozen Food — Web E-Commerce & Point of Sale (POS)

Sistem Web E-Commerce dan Manajemen Kasir Pintar (Point of Sale) modern untuk **Ica Frozen Food** yang menyajikan produk makanan beku higienis, lezat, dan praktis bagi keluarga Indonesia.

---

## Daftar Isi
1. [Tentang Proyek](#tentang-proyek)
2. [Fitur Unggulan](#fitur-unggulan)
3. [Arsitektur Skema Database Terbaru](#arsitektur-skema-database-terbaru)
4. [Teknologi yang Digunakan (Tech Stack)](#teknologi-yang-digunakan-tech-stack)
5. [Brand Palette & Estetika Desain](#brand-palette--estetika-desain)
6. [Struktur Direktori Proyek](#struktur-direktori-proyek)
7. [Akun Demo untuk Presentasi & Pengujian](#akun-demo-untuk-presentasi--pengujian)
8. [Panduan Instalasi & Menjalankan Proyek](#panduan-instalasi--menjalankan-proyek)
9. [Pembagian Peran & Kontribusi Tim](#pembagian-peran--kontribusi-tim)

---

## Tentang Proyek
Aplikasi ini dikembangkan untuk mendigitalisasi operasional penjualan ritel dan grosir **Ica Frozen Food** (Outlet Loktabat Utara, Banjarbaru):
- **Sisi Pelanggan (Customer Facing)**: Pengunjung dapat menjelajahi katalog etalase freezer lengkap tanpa harus login. Autentikasi diwajibkan saat pelanggan ingin melakukan checkout pesanan.
- **Sisi Admin & Kasir (Merchant Facing)**: Dashboard analitik penjualan riil, pemantauan status pesanan masuk (*Real-Time Order Tracking*), manajemen inventaris produk & kategori etalase, serta cetak struk thermal kasir digital.

---

## Fitur Unggulan

### 1. Sisi Pelanggan (Storefront Experience)
- **Floating Header Dinamis**:
  - Navbar menempel di atas dan bertransformasi halus menjadi pil mengambang saat digulir.
  - Avatar profil pengguna dengan inisial otomatis atau foto kustom.
- **Katalog & Filter Interaktif**:
  - Pencarian *live* berdasarkan nama produk dan deskripsi.
  - Filter kategori instan (*Dimsum & Siomay, Nugget & Sosis, Olahan Seafood, Daging Beku, Bakso & Pentol*).
  - Modal detail produk interaktif dengan penyesuaian kuantitas (*quantity counter*).
- **Paket Hemat (*Smart Bundling*)**:
  - Pilihan paket bundling hemat dengan harga spesial (*Paket Sarapan Keluarga, Dimsum Party Time, Shabu & Grill Weekend*).
- **Keranjang Belanja (*Cart Drawer*)**:
  - Pilihan metode pengambilan:
    - **Via kurir**: Biaya pengantaran flat **Rp 5.000** ke alamat tujuan.
    - **Ambil di Toko**: Bebas biaya (**Gratis**).
- **Sistem Pembayaran Terpadu**:
  - **QRIS Instan**: Memuat **QRIS resmi Ica Frozen Food** (*NMID: ID1020039281729*) yang mendukung seluruh aplikasi m-Banking dan E-Wallet.
  - **Transfer Bank BCA** (*Rekening: 782-019-2341*).
  - **Bayar Tunai di Kasir Toko** (Khusus opsi Ambil Sendiri).
- **Struk Digital & Salin Teks**:
  - Pratinjau nota thermal otomatis pasca transaksi, tombol cetak struk, dan tombol salin format teks WhatsApp ke kasir.

### 2. Sisi Admin & Kasir (Merchant Dashboard)
- **4 Kartu KPI Analitik Riil (Terkoneksi Database)**:
  - **Total Produk**: Akumulasi produk aktif di etalase.
  - **Total Pesanan**: Jumlah transaksi pesanan aktif.
  - **Total Pelanggan**: Dihitung murni berdasarkan pelanggan unik yang bertransaksi (otomatis `0` jika belum ada transaksi).
  - **Total Pendapatan**: Akumulasi nominal pesanan yang berstatus *Selesai* atau *Lunas*.
- **Peringkat Produk Paling Laris (Dinamis)**:
  - Akumulasi total kuantitas (`qty`) produk yang terjual dari transaksi riil. Dilengkapi *empty state* bersih saat belum ada penjualan.
- **Proses Pesanan & Transaksi**:
  - Pembaruan status pesanan (*Menunggu, Diproses, Selesai, Dibatalkan*) dan status pembayaran (*Lunas / Belum Lunas*).
- **Manajemen Data Produk & Kategori**:
  - Tambah, edit stok, ubah harga, hapus, dan upload gambar produk langsung ke penyimpanan server.
- **Profil Administrator**:
  - Pembaruan data akun pengelola, email, dan kata sandi.

### 3. Autentikasi & Keamanan Multi-Role
- **Laravel Breeze Scaffolding**:
  - Dilengkapi `RoleMiddleware` untuk memisahkan hak akses antara `admin` dan `user`.
  - Admin login langsung diarahkan ke `/admin/dashboard`, sedangkan pelanggan diarahkan ke etalase belanja.
- **Desain Form Login & Register Modern**:
  - Antarmuka *split-screen* responsif dengan *Pure Vanilla CSS*, ikon SVG terpadu, dan fitur *show/hide password*.

---

## Arsitektur Skema Database Terbaru

Skema database telah dioptimalkan dan mengikuti kaidah *Version-Controlled Migrations* Laravel:

| Tabel | Kolom Utama | Keterangan & Penyesuaian Terbaru |
|---|---|---|
| `users` | `id`, `name`, `email`, `role`, `phone`, `address`, `avatar`, `password` | Mendukung role `admin` & `user`, panjang karakter dioptimalkan. |
| `categories` | `id`, `name`, `description`, `created_at` | Kategori etalase freezer dengan *VARCHAR* efisien. |
| `products` | `id`, `name`, `category`, `price`, `stock`, `weight`, `image`, `description`, `tags` | Kolom tidak terpakai (`temperature`, `shelfLife`) telah dihapus secara bersih. |
| `orders` | `id`, `customer_name`, `customer_phone`, `address`, `method`, `payment_method`, `items`, `subtotal`, `shipping_fee`, `total`, `status`, `is_paid`, `channel` | Kolom `ice_fee` resmi diganti menjadi `shipping_fee` (Ongkos Kirim kurir toko). |

### Daftar Migrasi Penyesuaian:
1. `2026_10_06_000001_cleanup_products_table_columns.php` — Menghapus kolom `temperature` & `shelfLife`.
2. `2026_10_06_000002_optimize_columns_char_length.php` — Optimasi panjang karakter *VARCHAR*.
3. `2026_10_06_000003_rename_ice_fee_to_shipping_fee.php` — Mengubah nama kolom `ice_fee` $\rightarrow$ `shipping_fee`.

---

## Teknologi yang Digunakan (Tech Stack)

| Komponen | Teknologi | Keterangan |
|---|---|---|
| **Backend Framework** | Laravel 11.x (PHP 8.2+) | Framework MVC dengan arsitektur RESTful JSON Controller & Eloquent ORM |
| **Autentikasi & Role** | Laravel Breeze + Custom Middleware | Manajemen sesi terenkripsi, CSRF Protection, dan Route Guarding |
| **Frontend Templating** | Laravel Blade Engine | Server-Side Rendering (SSR) dinamis dan responsif |
| **Frontend Styling** | Pure Vanilla CSS | Desain bersih berbasis CSS Custom Properties, Flexbox, dan CSS Grid (Ringan & Cepat) |
| **Frontend Logic** | Pure Vanilla JavaScript | Tanpa framework eksternal; memanfaatkan Native DOM API & Async Fetch API |
| **Database** | MySQL / MariaDB (Laragon) | Basis data relasional ACID-compliant |
| **Aset Visual & QRIS** | Inline SVG & Official QRIS PNG | Gambar tajam, responsif, dan ringan dimuat |

---

## Brand Palette & Estetika Desain

- **Soft Blue (Primary)**: `#677D9E`
- **Dark Navy (Headings)**: `#2C3E50`
- **Light Cream (Background)**: `#F1F0E8`
- **Border & Muted Text**: `#A6B1C3` / `#6B7280`
- **Success Green**: `#059669`
- **Warning Amber**: `#F59E0B`

---

## Struktur Direktori Proyek

```text
Kelompok2_FrozenFoodIca/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminDataController.php        # API CRUD Produk, Kategori, Order, & Stok
│   │   │   ├── Auth/                          # Controller Login, Register, Sesi
│   │   │   └── ProfileController.php          # Update Profil Pengguna
│   │   └── Middleware/
│   │       └── RoleMiddleware.php             # Penjaga rute hak akses (admin / user)
│   └── Models/
│       ├── Category.php                       # Model Kategori
│       ├── Order.php                          # Model Pesanan (dengan accessor shipping_fee)
│       ├── Product.php                        # Model Produk Etalase
│       └── User.php                           # Model Pengguna & Role
│
├── database/
│   ├── migrations/                            # File migrasi skema tabel database
│   └── seeders/
│       ├── RoleSeeder.php                     # Seeder akun demo role admin & user
│       ├── ProductAndCategorySeeder.php       # Seeder data katalog awal & kategori
│       └── DatabaseSeeder.php                 # Runner seeder utama
│
├── public/
│   ├── css/
│   │   ├── style.css                          # Styling Halaman Toko & Keranjang Belanja
│   │   └── admin.css                          # Styling Dashboard Admin & Kasir
│   ├── js/
│   │   ├── store.js                           # Logika Etalase, Keranjang, Checkout, & QRIS
│   │   └── admin.js                           # Logika Dashboard Admin, Kasir, & Modal
│   └── images/
│       ├── ica_logo.png                       # Logo Brand Ica Frozen Food
│       ├── qris-ica-frozen-food.png           # Gambar QRIS Resmi Toko
│       └── products/                          # Folder penyimpanan upload gambar produk
│
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php                # Halaman Login Multi-Role
│       │   └── register.blade.php             # Halaman Registrasi Pelanggan
│       ├── welcome.blade.php                  # Halaman Toko Online & Checkout
│       └── admin.blade.php                    # Halaman Dashboard Admin & Kasir
│
├── routes/
│   ├── web.php                                # Rute navigasi web & endpoint data
│   └── auth.php                               # Rute autentikasi Laravel Breeze
│
└── README.md                                  # Dokumentasi resmi proyek
```

---

## Akun Demo untuk Presentasi & Pengujian

Aplikasi dilengkapi seeder akun demo siap pakai:

| Role / Hak Akses | Email Akun | Password Default | Tujuan & Hak Akses |
|---|---|---|---|
| **Administrator Toko** | `admin@example.com` | `password123` | Akses penuh dashboard, kelola pesanan, katalog, dan kasir POS |
| **Pelanggan Biasa** | `user@example.com` | `password123` | Akses belanja, simpan profil, checkout pesanan online |

---

## Panduan Instalasi & Menjalankan Proyek

### 1. Kebutuhan Sistem
- PHP >= 8.2 (Ekstensi `pdo_mysql`, `mbstring`, `fileinfo` aktif)
- Composer >= 2.x
- Web Server MySQL / MariaDB (Laragon / XAMPP)

### 2. Langkah Instalasi di Komputer Baru

```bash
# 1. Masuk ke direktori proyek
cd d:\laragon\www\Kelompok2_FrozenFoodIca

# 2. Pasang dependensi PHP
composer install

# 3. Buat file .env dan generate kunci enkripsi aplikasi
copy .env.example .env
php artisan key:generate

# 4. Konfigurasi koneksi database di file .env (DB_DATABASE=kelompok2_icafrozenfood)
# 5. Jalankan migrasi dan seeder database
php artisan migrate --seed

# 6. Bersihkan cache aplikasi
php artisan view:clear
php artisan route:clear

# 7. Jalankan server lokal
php artisan serve
```

Aplikasi dapat diakses melalui browser:
* **Halaman Toko / Login**: `http://127.0.0.1:8000`
* **Halaman Login Langsung**: `http://127.0.0.1:8000/login`
* **Dashboard Admin**: `http://127.0.0.1:8000/admin/dashboard`

---

## Pembagian Peran & Kontribusi Tim

Proyek ini dikembangkan secara kolaboratif melalui Git & GitHub:

* **Backend Development (Chikoariek - Owner)**:
  - Perancangan fondasi arsitektur autentikasi Laravel Breeze.
  - Pembuatan *Role Management*, registrasi middleware hak akses (`RoleMiddleware`), dan implementasi seeder awal.
* **Frontend Development & Integrasi Database (rhaazerm - Collaborator)**:
  - Perancangan antarmuka visual UI/UX responsif (Login Page, Register, Etalase Pelanggan, Dashboard Admin).
  - Integrasi alur transaksi, modal pembayaran QRIS resmi, opsi kurir toko, dan struk thermal.
  - Perapian skema database, migrasi penyesuaian kolom `shipping_fee`, serta optimasi panjang tipe data.

---

Dibuat oleh **Kelompok 2 - Ica Frozen Food**.
