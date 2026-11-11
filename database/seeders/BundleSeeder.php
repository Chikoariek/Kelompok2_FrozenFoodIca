<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bundle;
use App\Models\BundleItem;

class BundleSeeder extends Seeder
{
    /**
     * Seed 3 Paket Hemat Rekomendasi Ica Frozen Food
     */
    public function run(): void
    {
        $bundles = [
            [
                'id' => 'BND-01',
                'name' => 'Paket Sarapan Praktis Keluarga',
                'description' => 'Kombinasi Dimsum Ayam + Crispy Chicken Nugget + Sosis Cocktail untuk sarapan cepat bergizi.',
                'image' => 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=600&q=80',
                'badge_left' => 'Paling Laris',
                'badge_right' => 'Hemat Rp 14.000',
                'tags' => ['#Cukup 3-4 Orang', '#Hemat 12%'],
                'original_price' => 119000,
                'bundle_price' => 105000,
                'is_active' => true,
                'items' => [
                    ['product_id' => 'P01', 'quantity' => 1],
                    ['product_id' => 'P05', 'quantity' => 1],
                    ['product_id' => 'P07', 'quantity' => 1],
                ],
            ],
            [
                'id' => 'BND-02',
                'name' => 'Paket Dimsum Party Time',
                'description' => 'Lengkap dengan Siomay Udang, Hakau Kristal, dan Lumpia Kulit Tahu untuk santai sore seru.',
                'image' => 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?auto=format&fit=crop&w=600&q=80',
                'badge_left' => 'Rekomendasi Chef',
                'badge_right' => 'Hemat Rp 15.000',
                'tags' => ['#Dimsum Lovers', '#Kukus / Goreng'],
                'original_price' => 125000,
                'bundle_price' => 110000,
                'is_active' => true,
                'items' => [
                    ['product_id' => 'P02', 'quantity' => 1],
                    ['product_id' => 'P03', 'quantity' => 1],
                    ['product_id' => 'P04', 'quantity' => 1],
                ],
            ],
            [
                'id' => 'BND-03',
                'name' => 'Paket Shabu & Grill Weekend',
                'description' => 'US Beef Shortplate 500g + Bakso Seafood Shabu Mix 500g + Snow Crabstick untuk pesta keluarga.',
                'image' => 'https://images.unsplash.com/photo-1547928576-a4a33237cbc3?auto=format&fit=crop&w=600&q=80',
                'badge_left' => 'Super Hemat',
                'badge_right' => 'Hemat Rp 17.000',
                'tags' => ['#Pesta Barbeque', '#Weekend Special'],
                'original_price' => 165000,
                'bundle_price' => 148000,
                'is_active' => true,
                'items' => [
                    ['product_id' => 'P12', 'quantity' => 1],
                    ['product_id' => 'P09', 'quantity' => 1],
                    ['product_id' => 'P11', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($bundles as $b) {
            $items = $b['items'];
            unset($b['items']);

            $bundle = Bundle::updateOrCreate(['id' => $b['id']], $b);

            // Bersihkan items lama jika ada, lalu isi ulang item relasi
            BundleItem::where('bundle_id', $bundle->id)->delete();
            foreach ($items as $item) {
                BundleItem::create([
                    'bundle_id'  => $bundle->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }
        }
    }
}

