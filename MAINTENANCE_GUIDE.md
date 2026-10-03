# 📘 Panduan Perawatan & Pemeliharaan (Maintenance Guide)
### Aplikasi Web Ica Frozen Food — Toko & Kasir Frozen Food Modern

Dokumen ini disusun untuk memudahkan pemilik dan pengembang dalam memelihara (*maintenance*), memperbarui konten, mengelola produk, dan memastikan web tetap berjalan prima serta responsif di berbagai perangkat (Smartphone, Tablet, Laptop, dan Desktop).

---

## 📑 Daftar Isi
1. [Struktur File Penting & Konfigurasi Cepat](#1-struktur-file-penting--konfigurasi-cepat)
2. [Cara Cepat Mengubah Informasi Toko (WhatsApp, Maps, Rekening, dll)](#2-cara-cepat-mengubah-informasi-toko)
3. [Manajemen Produk & Kategori](#3-manajemen-produk--kategori)
4. [Cara Menjalankan Server & Memperbarui Tampilan](#4-cara-menjalankan-server--memperbarui-tampilan)
5. [Desain Responsif Lintas Media (Multi-Device)](#5-desain-responsif-lintas-media)
6. [Backup & Pemeliharaan Database](#6-backup--pemeliharaan-database)
7. [Troubleshooting & Solusi Masalah Umum](#7-troubleshooting--solusi-masalah-umum)

---

## 1. Struktur File Penting & Konfigurasi Cepat

Berikut adalah lokasi file-file utama yang paling sering diakses saat melakukan *maintenance*:

| Kategori | Lokasi File | Fungsi Utama |
| :--- | :--- | :--- |
| **Halaman Utama Pelanggan (View)** | `resources/views/welcome.blade.php` | Template Blade halaman etalase katalog, hero banner, floating navbar, modal, dan struk |
| **Styling Pelanggan (CSS Murni)** | `public/css/style.css` | Seluruh desain antarmuka pembeli (Vanilla CSS dengan CSS variables dan animasi) |
| **Logika Toko (JS Murni)** | `public/js/store.js` | Logika interaktif: keranjang belanja, floating navbar scroll, checkout, filter produk, modal |
| **Halaman Dashboard Admin (View)** | `resources/views/admin.blade.php` | Template Blade dashboard admin, kasir POS, tabel inventaris, dan modal |
| **Styling Admin (CSS Murni)** | `public/css/admin.css` | Seluruh styling admin (Vanilla CSS: tata letak sidebar, panel POS kasir, tabel) |
| **Logika Admin (JS Murni)** | `public/js/admin.js` | Logika kasir POS cepat, kalkulator kembalian, cetak struk, CRUD produk & kategori, sinkronisasi database |
| **Autentikasi Login & Register** | `resources/views/layouts/guest.blade.php` | Desain split-screen login/register dengan Vanilla CSS murni dan toggle show/hide password |
| **Database & API Controller** | `app/Http/Controllers/AdminDataController.php` | Endpoint penyimpanan dan sinkronisasi data produk, kategori, dan pesanan |

---

## 2. Cara Cepat Mengubah Informasi Toko

Seluruh informasi toko dapat disesuaikan langsung di file template Blade dan JavaScript:
- **Teks Banner, Alamat, Jam Operasional, & Kontak:** Dapat diubah langsung di [resources/views/welcome.blade.php](file:///d:/laragon/www/Kelompok2_FrozenFoodIca/resources/views/welcome.blade.php) pada bagian footer, announcement bar, dan modal pembayaran.
- **Konfigurasi Ongkir & Pembayaran Pelanggan:** Dikelola langsung di [public/js/store.js](file:///d:/laragon/www/Kelompok2_FrozenFoodIca/public/js/store.js).
- **Format Struk Nota & Rekening:** Dikelola di [public/js/admin.js](file:///d:/laragon/www/Kelompok2_FrozenFoodIca/public/js/admin.js) dan modal struk di Blade.

Perubahan pada file CSS dan JS langsung aktif saat halaman di-refresh di browser tanpa perlu proses compile/build.

---

## 3. Manajemen Produk & Kategori

### A. Melalui Tampilan Admin Web (Paling Praktis & Disarankan)
1. Buka browser dan masuk ke Dashboard Admin di `/admin`.
2. **Tambah Produk Baru**:
   - Klik tab **Data Produk**.
   - Klik tombol hijau **+ Tambah Produk**.
   - Isi Nama Produk, Kategori, Harga, Berat (contoh: 500 gr), Stok awal, dan URL/Path gambar.
   - Klik **Simpan Produk**.
3. **Simpan Perubahan ke Database**:
   - Data otomatis tersimpan atau klik tombol **"Simpan Perubahan"** berikon database di kanan atas.
   - Notifikasi sukses akan muncul saat data berhasil tersimpan ke database MySQL/SQLite.
4. **Kelola Kategori**:
   - Buka tab **Kategori Produk**.
   - Anda dapat menambah kategori baru, memilih tampilan grid/list, mengedit nama kategori lama, atau menghapus kategori.

### B. Foto & Gambar Produk
- Gambar produk disimpan di folder: `public/images/products/`
- Format yang direkomendasikan: `.jpg`, `.jpeg`, `.png`, atau `.webp` dengan resolusi ideal 600x600 px hingga 800x800 px.

---

## 4. Cara Menjalankan Server

Karena aplikasi ini menggunakan **Laravel Blade, Vanilla CSS, dan Vanilla JS murni** (tanpa ketergantungan npm compile saat runtime), Anda cukup menjalankan:

```bash
php artisan serve
```
*(Server akan berjalan di `http://127.0.0.1:8000`)*

Anda **tidak perlu** menjalankan `npm run dev` atau `npm run build` karena semua styling dan script sudah berupa aset statis murni yang langsung dibaca oleh browser melalui Laravel.

---

## 5. Desain Responsif Lintas Media

Web ini telah dioptimalkan secara menyeluruh untuk kenyamanan pengguna di semua ukuran layar:

1. **Smartphone (Layar < 640px)**:
   - **Drawer Navigasi Mobile**: Sidebar admin dapat dibuka dan ditutup dengan tombol ikon Hamburger menu (☰), dilengkapi backdrop blur dan tombol tutup (X).
   - **Tabel Geser Horizontal**: Tabel pesanan, inventaris, dan kategori dibungkus dengan `overflow-x-auto` dan batas lebar minimum (`min-w`), sehingga baris data tidak tertekan atau teks terpotong.
   - **Header Ringkas**: Badge tanggal yang panjang otomatis disembunyikan di layar ponsel agar judul halaman tetap rapi dan tidak tumpang tindih.
   - **Hero Section Minimalis**: Gambar hero makanan diperluas, teks kategori ditiadakan, dan tombol aksi `[ + Keranjang ]` tampil ringkas dan mudah disentuh jari.

2. **Tablet & iPad (Layar 640px – 1024px)**:
   - Kartu statistik bergeser otomatis menjadi 2 kolom (2x2 grid).
   - Panel POS (Kasir) menyesuaikan proporsi tombol sentuh dan daftar belanja.

3. **Laptop & Komputer Desktop (Layar > 1024px)**:
   - Layout penuh dengan 4 kolom kartu ringkasan, sidebar navigasi tetap di kiri, serta tampilan multi-kolom di kasir POS.

4. **Fitur Scroll & Tab Persistence**:
   - Saat pengguna me-refresh halaman (tekan F5 / Refresh biasa), posisi scroll etalase toko dan scroll tabel admin **tidak akan melompat ke paling atas**, melainkan tetap berada di posisi terakhir pengguna membaca.

---

## 6. Backup & Pemeliharaan Database

### Lokasi Database:
- Jika menggunakan SQLite: File database terletak di `database/database.sqlite`.
- Jika menggunakan MySQL: Konfigurasi koneksi diatur di file `.env` root (`DB_CONNECTION=mysql`, `DB_DATABASE=...`).

### Rutinitas Backup:
- **Backup Cepat SQLite**: Salin file `database/database.sqlite` ke tempat aman (misal Google Drive atau flashdisk) seminggu sekali atau sebelum update besar.
- **Export Data Produk**: Anda dapat mendownload tabel produk atau mengekspor tabel `products` dan `orders` dari phpMyAdmin (jika menggunakan MySQL).

---

## 7. Troubleshooting & Solusi Masalah Umum

| Masalah | Penyebab Umum | Solusi Cepat |
| :--- | :--- | :--- |
| Perubahan CSS/JS tidak muncul di browser | Cache browser masih menyimpan file lama | Tekan `Ctrl + F5` (Hard Reload) di browser, atau jalankan `npm run build` |
| Halaman Admin blank putih | Error pada sintaks JavaScript | Buka Console browser (tekan F12 -> tab Console) untuk melihat pesan error |
| Port 8000 sudah terpakai | Ada proses Laravel lama yang belum dimatikan | Jalankan dengan port lain: `php artisan serve --port=8080` |
| Nomor WA di struk atau web masih yang lama | Belum diubah di file konfigurasi | Buka `resources/js/config/storeConfig.js`, perbarui `whatsappNumber`, lalu jalankan `npm run build` |

---

*Panduan ini dibuat khusus untuk mempermudah operasional dan keberlanjutan aplikasi Ica Frozen Food.* 🚀
