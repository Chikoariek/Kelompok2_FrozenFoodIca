<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Database\Seeder;

class ProductAndCategorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categories = [
            ['name' => 'Dimsum & Siomay', 'description' => 'Aneka dimsum lezat, siomay, hakau udang kristal, dan lumpia.'],
            ['name' => 'Nugget & Sosis', 'description' => 'Nugget ayam renyah, sosis bratwurst, cocktail, dan olahan praktis.'],
            ['name' => 'Olahan Seafood', 'description' => 'Bakso seafood shabu-shabu, ebi furai, crabstick kualitas premium.'],
            ['name' => 'Daging Beku Pilihan', 'description' => 'Daging sapi slice US shortplate, sukiyaki wagyu, dan dada ayam fillet.'],
            ['name' => 'Bakso & Pentol', 'description' => 'Bakso sapi urat, pentol pedas mercon, bakso halus kenyal gurih.'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // 2. Products
        $products = [
            [
                'id' => 'P01',
                'name' => 'Dimsum Ayam Premium (Isi 15)',
                'category' => 'Dimsum & Siomay',
                'price' => 35000,
                'stock' => 18,
                'weight' => '350g',
                'description' => 'Olahan daging ayam segar dibalut kulit dimsum lembut dengan topping wortel, sudah termasuk saus pedas manis gurih.',
                'image' => 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Best Seller', 'Praktis'],
                'temperature' => '-18°C',
                'shelfLife' => '6 Bulan',
            ],
            [
                'id' => 'P02',
                'name' => 'Siomay Udang Raja (Isi 12)',
                'category' => 'Dimsum & Siomay',
                'price' => 42000,
                'stock' => 12,
                'weight' => '300g',
                'description' => 'Siomay dengan cincangan udang laut segar bertekstur kenyal renyah, lezat dikukus maupun digoreng.',
                'image' => 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Seafood Pilihan'],
                'temperature' => '-18°C',
                'shelfLife' => '6 Bulan',
            ],
            [
                'id' => 'P03',
                'name' => 'Hakau Udang Kristal (Isi 10)',
                'category' => 'Dimsum & Siomay',
                'price' => 45000,
                'stock' => 4,
                'weight' => '250g',
                'description' => 'Kulit transparan kristal dengan isian udang utuh segar bercita rasa autentik oriental restoran bintang lima.',
                'image' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Stok Menipis', 'Premium'],
                'temperature' => '-18°C',
                'shelfLife' => '5 Bulan',
            ],
            [
                'id' => 'P04',
                'name' => 'Lumpia Kulit Tahu Ayam Udang (Isi 10)',
                'category' => 'Dimsum & Siomay',
                'price' => 38000,
                'stock' => 15,
                'weight' => '320g',
                'description' => 'Kombinasi ayam dan udang berbalut lembaran kulit tahu renyah saat digoreng kering.',
                'image' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Crispy'],
                'temperature' => '-18°C',
                'shelfLife' => '6 Bulan',
            ],
            [
                'id' => 'P05',
                'name' => 'Crispy Chicken Nugget Original',
                'category' => 'Nugget & Sosis',
                'price' => 48000,
                'stock' => 25,
                'weight' => '500g',
                'description' => 'Nugget dada ayam asli dengan lapisan bubble crumb renyah tahan lama, favorit bekal anak & sarapan cepat.',
                'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Favorit Anak', 'Praktis'],
                'temperature' => '-18°C',
                'shelfLife' => '12 Bulan',
            ],
            [
                'id' => 'P06',
                'name' => 'Cheesy Bratwurst Beef Sausage (Isi 6)',
                'category' => 'Nugget & Sosis',
                'price' => 55000,
                'stock' => 14,
                'weight' => '500g',
                'description' => 'Sosis sapi ala Jerman isi lelehan keju cheddar premium dengan sensasi juicy kenyal saat digigit.',
                'image' => 'https://images.unsplash.com/photo-1597733153203-a54d0fbc47de?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Juicy & Keju'],
                'temperature' => '-18°C',
                'shelfLife' => '8 Bulan',
            ],
            [
                'id' => 'P07',
                'name' => 'Sosis Sapi Cocktail Mini (Isi 24)',
                'category' => 'Nugget & Sosis',
                'price' => 36000,
                'stock' => 3,
                'weight' => '400g',
                'description' => 'Ukuran mini sekali suap beraroma asap alami, cocok untuk campuran sup, mie, tumisan, atau camilan tusuk.',
                'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Stok Menipis', 'Mini Pack'],
                'temperature' => '-18°C',
                'shelfLife' => '8 Bulan',
            ],
            [
                'id' => 'P08',
                'name' => 'Nugget Keju Leleh Melted (Isi 15)',
                'category' => 'Nugget & Sosis',
                'price' => 43000,
                'stock' => 9,
                'weight' => '400g',
                'description' => 'Daging ayam cincang lembut dengan melted cheese di dalamnya yang lumer saat digoreng panas.',
                'image' => 'https://images.unsplash.com/photo-1585325701165-351af916e581?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Melted Cheese'],
                'temperature' => '-18°C',
                'shelfLife' => '6 Bulan',
            ],
            [
                'id' => 'P09',
                'name' => 'Bakso Seafood Mix Shabu-Shabu',
                'category' => 'Olahan Seafood',
                'price' => 49000,
                'stock' => 20,
                'weight' => '500g',
                'description' => 'Kombinasi bakso ikan, fish cake, chikuwa, dan crab claw lezat untuk kuah tomyam, suki, atau steamboat.',
                'image' => 'https://images.unsplash.com/photo-1547928576-a4a33237cbc3?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Shabu & Suki'],
                'temperature' => '-18°C',
                'shelfLife' => '9 Bulan',
            ],
            [
                'id' => 'P10',
                'name' => 'Ebi Furai Udang Roti Jepang (Isi 8)',
                'category' => 'Olahan Seafood',
                'price' => 52000,
                'stock' => 11,
                'weight' => '280g',
                'description' => 'Udang utuh segar lurus berbalut tepung roti panko Jepang super renyah dan gurih.',
                'image' => 'https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Ala Resto'],
                'temperature' => '-18°C',
                'shelfLife' => '6 Bulan',
            ],
            [
                'id' => 'P11',
                'name' => 'Snow Crabstick Premium',
                'category' => 'Olahan Seafood',
                'price' => 32000,
                'stock' => 2,
                'weight' => '250g',
                'description' => 'Olahan surimi ikan berkualitas tinggi dengan serat daging kepiting yang manis dan empuk.',
                'image' => 'https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Stok Menipis'],
                'temperature' => '-18°C',
                'shelfLife' => '8 Bulan',
            ],
            [
                'id' => 'P12',
                'name' => 'US Beef Shortplate Slice (500gr)',
                'category' => 'Daging Beku Pilihan',
                'price' => 68000,
                'stock' => 16,
                'weight' => '500g',
                'description' => 'Irisan daging sapi impor shortplate tipis 1.5mm dengan marbling seimbang, sempurna untuk grill, sukiyaki, atau teriyaki.',
                'image' => 'https://images.unsplash.com/photo-1588168333986-5078d3ae3976?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Favorit Grill', '100% Halal'],
                'temperature' => '-20°C',
                'shelfLife' => '12 Bulan',
            ],
            [
                'id' => 'P13',
                'name' => 'Daging Sukiyaki Wagyu Slice (300gr)',
                'category' => 'Daging Beku Pilihan',
                'price' => 95000,
                'stock' => 8,
                'weight' => '300g',
                'description' => 'Daging sapi wagyu grade MB 4-5 dipotong presisi, super empuk dan lumer di mulut.',
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Wagyu Melt', 'Mewah'],
                'temperature' => '-20°C',
                'shelfLife' => '10 Bulan',
            ],
            [
                'id' => 'P14',
                'name' => 'Fillet Dada Ayam Boneless Skinless',
                'category' => 'Daging Beku Pilihan',
                'price' => 39000,
                'stock' => 22,
                'weight' => '500g',
                'description' => 'Dada ayam segar tanpa tulang dan tanpa kulit yang dibekukan cepat (IQF) untuk menjaga nutrisi dan kesegaran.',
                'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=600&q=80',
                'tags' => ['Tinggi Protein', 'Higienis'],
                'temperature' => '-18°C',
                'shelfLife' => '9 Bulan',
            ],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(['id' => $prod['id']], $prod);
        }

        // 3. Orders
        $orders = [
            [
                'id' => 'ORD-ICA-1001',
                'customer_name' => 'Ibu Ratna Dewi',
                'customer_phone' => '0812-9876-5432',
                'address' => 'Jl. Flamboyan No. 12, Banjarbaru',
                'order_date' => '2026-09-28 09:15',
                'method' => 'Kurir Toko (Diantar ke Rumah)',
                'payment_method' => 'QRIS Instan',
                'status' => 'Diproses',
                'items' => [
                    ['id' => 'P01', 'name' => 'Dimsum Ayam Premium (Isi 15)', 'qty' => 2, 'price' => 35000],
                    ['id' => 'P05', 'name' => 'Crispy Chicken Nugget Original', 'qty' => 1, 'price' => 48000],
                ],
                'subtotal' => 118000,
                'ice_fee' => 5000,
                'total' => 123000,
                'is_paid' => true,
                'channel' => 'Online',
            ],
            [
                'id' => 'ORD-ICA-1002',
                'customer_name' => 'Bapak Hendra Saputra',
                'customer_phone' => '0857-1122-3344',
                'address' => 'Ambil di Toko',
                'order_date' => '2026-09-28 09:40',
                'method' => 'Ambil di Toko / Self Pick-up',
                'payment_method' => 'Transfer Bank (BCA)',
                'status' => 'Menunggu Pembayaran',
                'items' => [
                    ['id' => 'P12', 'name' => 'US Beef Shortplate Slice (500gr)', 'qty' => 2, 'price' => 68000],
                    ['id' => 'P06', 'name' => 'Cheesy Bratwurst Beef Sausage (Isi 6)', 'qty' => 1, 'price' => 55000],
                ],
                'subtotal' => 191000,
                'ice_fee' => 0,
                'total' => 191000,
                'is_paid' => false,
                'channel' => 'Online',
            ],
            [
                'id' => 'ORD-ICA-1003',
                'customer_name' => 'Siti Nurhaliza',
                'customer_phone' => '0813-4455-6677',
                'address' => 'Komp. Permata Hijau Blok C No. 8',
                'order_date' => '2026-09-28 10:10',
                'method' => 'Kurir Toko (Diantar ke Rumah)',
                'payment_method' => 'QRIS Instan',
                'status' => 'Selesai',
                'items' => [
                    ['id' => 'P09', 'name' => 'Bakso Seafood Mix Shabu-Shabu', 'qty' => 1, 'price' => 49000],
                    ['id' => 'P10', 'name' => 'Ebi Furai Udang Roti Jepang (Isi 8)', 'qty' => 1, 'price' => 52000],
                ],
                'subtotal' => 101000,
                'ice_fee' => 5000,
                'total' => 106000,
                'is_paid' => true,
                'channel' => 'Online',
            ],
            [
                'id' => 'POS-ICA-9001',
                'customer_name' => 'Pelanggan Walk-In #12',
                'customer_phone' => '-',
                'address' => 'Kasir Toko Ica Frozen Food',
                'order_date' => '2026-09-28 10:25',
                'method' => 'Ambil di Toko / Self Pick-up',
                'payment_method' => 'Tunai (Cash)',
                'status' => 'Selesai',
                'items' => [
                    ['id' => 'P01', 'name' => 'Dimsum Ayam Premium (Isi 15)', 'qty' => 1, 'price' => 35000],
                    ['id' => 'P02', 'name' => 'Siomay Udang Raja (Isi 12)', 'qty' => 1, 'price' => 42000],
                ],
                'subtotal' => 77000,
                'ice_fee' => 0,
                'total' => 77000,
                'is_paid' => true,
                'channel' => 'Kasir POS',
            ],
        ];

        foreach ($orders as $order) {
            Order::updateOrCreate(['id' => $order['id']], $order);
        }
    }
}
