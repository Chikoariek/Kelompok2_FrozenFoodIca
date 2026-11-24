<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Bundle;
use App\Models\BundleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Manajemen Data (API Endpoint)
 * Menangani transaksi data asinkron (AJAX) antara frontend dan database:
 * 1. Manajemen Kategori Produk (Ambil, Tambah, Edit, Hapus, Sinkronisasi)
 * 2. Manajemen Data Produk (Inventaris, Pengurangan/Penambahan Stok Freezer)
 * 3. Manajemen Pesanan (Pencatatan Order Checkout & Perubahan Status Transaksi)
 */
class AdminDataController extends Controller
{
    // --- 1. Kategori Produk ---

    // Mengambil daftar nama kategori untuk filter etalase
    public function getCategories()
    {
        $categories = Category::all()->pluck('name');
        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    // Menambah kategori baru ke database
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan!',
            'category' => $category,
        ]);
    }

    // Memperbarui nama kategori dan produk terkait
    public function updateCategory(Request $request)
    {
        $validated = $request->validate([
            'oldName' => 'required|string',
            'newName' => 'required|string|max:100',
        ]);

        $oldName = trim($validated['oldName']);
        $newName = trim($validated['newName']);

        $category = Category::where('name', $oldName)->first();
        if ($category) {
            $category->update(['name' => $newName]);
        } else {
            Category::create(['name' => $newName]);
        }

        // Update produk yang memiliki kategori lama
        Product::where('category', $oldName)->update(['category' => $newName]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui!',
        ]);
    }

    // Menghapus kategori dari database dengan validasi pencegahan relasional
    public function destroyCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $categoryName = trim($validated['name']);

        // Validasi Relasional (ON DELETE RESTRICT): Kategori dilarang dihapus jika masih digunakan oleh produk
        $usedCount = Product::where('category', $categoryName)->count();
        if ($usedCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Kategori '{$categoryName}' tidak dapat dihapus karena masih digunakan oleh {$usedCount} produk. Silakan ubah atau hapus produk terkait terlebih dahulu.",
            ], 422);
        }

        Category::where('name', $categoryName)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus!',
        ]);
    }

    // Sinkronisasi seluruh daftar kategori secara massal
    public function syncCategories(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'required|string',
        ]);

        $names = array_unique(array_map('trim', $validated['categories']));

        // Validasi Relasional: Cek apakah ada kategori terhapus yang masih digunakan produk
        $toDelete = Category::whereNotIn('name', $names)->pluck('name');
        $usedCategories = Product::whereIn('category', $toDelete)->distinct()->pluck('category')->toArray();

        if (!empty($usedCategories)) {
            $catList = implode(', ', $usedCategories);
            return response()->json([
                'success' => false,
                'message' => "Kategori ({$catList}) tidak dapat dihapus karena masih digunakan oleh produk aktif.",
            ], 422);
        }

        DB::transaction(function () use ($names) {
            // Hapus yang tidak ada dalam list baru
            Category::whereNotIn('name', $names)->delete();

            // Insert atau update yang ada
            foreach ($names as $name) {
                Category::firstOrCreate(['name' => $name]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Seluruh kategori berhasil disinkronisasi & disimpan!',
            'timestamp' => now()->translatedFormat('H:i:s \W\I\B'),
            'categories' => Category::all()->pluck('name'),
        ]);
    }

    // --- 2. Data Produk ---

    // Mengambil seluruh data produk untuk katalog etalase & tabel admin
    public function getProducts()
    {
        $products = Product::all();
        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }

    // Menyimpan produk baru atau mengedit produk beserta gambar
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'id'          => 'nullable|string',
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'weight'      => 'nullable|string',
            'description' => 'nullable|string',
            'image'       => 'nullable',
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ], [
            'stock.min'      => 'Jumlah stok tidak boleh kurang dari 0.',
            'stock.integer'  => 'Jumlah stok harus berupa bilangan bulat valid.',
            'stock.required' => 'Jumlah stok wajib diisi.',
            'price.min'      => 'Harga produk tidak boleh kurang dari 0.',
        ]);

        $id = $request->input('id') ?: ('PRD-' . time());
        $existingProduct = Product::find($id);
        $imagePath = $existingProduct ? $existingProduct->image : '/images/products/nugget.png';

        // 1. Jika ada file gambar diunggah langsung lewat form (image_file atau image)
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $uploadDir = public_path('images/products');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imagePath = '/images/products/' . $filename;
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = public_path('images/products');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imagePath = '/images/products/' . $filename;
        } elseif ($request->filled('image')) {
            $imagePath = $request->input('image');
        }

        $product = Product::updateOrCreate(
            ['id' => $id],
            [
                'name'        => $validated['name'],
                'category'    => $validated['category'],
                'price'       => (int) $validated['price'],
                'stock'       => (int) $validated['stock'],
                'weight'      => $validated['weight'] ?? '500g',
                'description' => $validated['description'] ?? '',
                'image'       => $imagePath,
                'tags'        => $request->input('tags', ['Baru']),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil disimpan!',
            'product' => $product,
        ]);
    }

    // Menyesuaikan jumlah stok fisik produk di freezer
    public function updateProductStock(Request $request, $id)
    {
        $validated = $request->validate([
            'delta' => 'nullable|integer',
            'diff'  => 'nullable|integer',
            'stock' => 'nullable|integer',
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        if ($request->has('stock')) {
            $newStock = (int) $request->input('stock');
            if ($newStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah stok tidak boleh kurang dari 0.',
                ], 422);
            }
            $product->stock = $newStock;
        } elseif ($request->has('delta') || $request->has('diff')) {
            $change = (int) ($request->input('delta') ?? $request->input('diff') ?? 0);
            $newStock = $product->stock + $change;
            if ($newStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah stok tidak boleh kurang dari 0.',
                ], 422);
            }
            $product->stock = $newStock;
        }
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Stok produk berhasil diperbarui!',
            'product' => $product,
        ]);
    }

    // Menghapus produk dari katalog
    public function destroyProduct($id)
    {
        Product::where('id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus!',
        ]);
    }

    // --- 2b. Manajemen Paket Hemat Promosi (Bundles) ---

    // Mengambil seluruh paket hemat beserta item relasinya
    public function getBundles()
    {
        $bundles = Bundle::with('items.product')->get();
        return response()->json([
            'success' => true,
            'bundles' => $bundles,
        ]);
    }

    // Menyimpan paket baru atau mengedit paket hemat beserta item relasinya
    public function storeBundle(Request $request)
    {
        $validated = $request->validate([
            'id'             => 'nullable|string',
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'badge_left'     => 'nullable|string|max:50',
            'badge_right'    => 'nullable|string|max:50',
            'bundle_price'   => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|string|exists:products,id',
            'items.*.quantity'   => 'nullable|integer|min:1',
            'image'          => 'nullable|string',
            'image_file'     => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $id = $request->input('id');
        if (!$id) {
            $existingIds = Bundle::pluck('id')->toArray();
            $maxNum = 0;
            foreach ($existingIds as $eid) {
                if (preg_match('/BND-(\d+)/', $eid, $m)) {
                    $maxNum = max($maxNum, (int)$m[1]);
                }
            }
            $id = 'BND-' . str_pad($maxNum + 1, 2, '0', STR_PAD_LEFT);
        }

        $existingBundle = Bundle::find($id);
        $imagePath = $existingBundle ? $existingBundle->image : 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=600&q=80';

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $uploadDir = public_path('images/bundles');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imagePath = '/images/bundles/' . $filename;
        } elseif ($request->filled('image')) {
            $imagePath = $request->input('image');
        }

        // Hitung harga asli otomatis dari total harga produk jika tidak diinput
        $originalPrice = (int) ($validated['original_price'] ?? 0);
        if ($originalPrice <= 0) {
            foreach ($validated['items'] as $item) {
                $p = Product::find($item['product_id']);
                if ($p) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $originalPrice += ((int) $p->price * $qty);
                }
            }
        }

        $bundlePrice = (int) $validated['bundle_price'];
        $badgeRight = $validated['badge_right'] ?? '';
        if (empty($badgeRight) && $originalPrice > $bundlePrice) {
            $hemat = $originalPrice - $bundlePrice;
            $badgeRight = 'Hemat Rp ' . number_format($hemat, 0, ',', '.');
        }

        $bundle = DB::transaction(function () use ($id, $validated, $imagePath, $originalPrice, $bundlePrice, $badgeRight, $request) {
            $bundle = Bundle::updateOrCreate(
                ['id' => $id],
                [
                    'name'           => $validated['name'],
                    'description'    => $validated['description'] ?? '',
                    'badge_left'     => $validated['badge_left'] ?? 'Paling Laris',
                    'badge_right'    => $badgeRight,
                    'original_price' => $originalPrice,
                    'bundle_price'   => $bundlePrice,
                    'image'          => $imagePath,
                    'tags'           => $request->input('tags', ['#Paket Hemat', '#Keluarga']),
                    'is_active'      => true,
                ]
            );

            // Perbarui data relasi di bundle_items
            BundleItem::where('bundle_id', $bundle->id)->delete();
            foreach ($validated['items'] as $item) {
                BundleItem::create([
                    'bundle_id'  => $bundle->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => (int) ($item['quantity'] ?? 1),
                ]);
            }

            return $bundle->load('items.product');
        });

        return response()->json([
            'success' => true,
            'message' => 'Paket hemat berhasil disimpan dan langsung tampil di etalase homepage!',
            'bundle'  => $bundle,
        ]);
    }

    // Menghapus paket hemat
    public function destroyBundle($id)
    {
        $bundle = Bundle::find($id);
        if (!$bundle) {
            return response()->json([
                'success' => false,
                'message' => 'Paket hemat tidak ditemukan.',
            ], 404);
        }

        $bundle->delete(); // Otomatis menghapus baris bundle_items karena cascade

        return response()->json([
            'success' => true,
            'message' => 'Paket hemat berhasil dihapus dari database & homepage.',
        ]);
    }

    // Mengaktifkan / menonaktifkan tampilan paket di homepage
    public function toggleBundleStatus($id)
    {
        $bundle = Bundle::find($id);
        if (!$bundle) {
            return response()->json([
                'success' => false,
                'message' => 'Paket hemat tidak ditemukan.',
            ], 404);
        }

        $bundle->is_active = !$bundle->is_active;
        $bundle->save();

        return response()->json([
            'success' => true,
            'message' => $bundle->is_active ? 'Paket hemat berhasil diaktifkan di homepage.' : 'Paket hemat dinonaktifkan dari homepage.',
            'is_active' => $bundle->is_active,
        ]);
    }

    // Sinkronisasi seluruh inventaris produk secara massal
    public function syncProducts(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
        ]);

        // Validasi stok pada seluruh produk sebelum disimpan
        foreach ($validated['products'] as $prod) {
            if (isset($prod['stock']) && (int)$prod['stock'] < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah stok tidak boleh kurang dari 0.',
                ], 422);
            }
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['products'] as $prod) {
                if (empty($prod['id'])) continue;
                Product::updateOrCreate(
                    ['id' => $prod['id']],
                    [
                        'name'        => $prod['name'],
                        'category'    => $prod['category'] ?? 'Nugget & Sosis',
                        'price'       => (int) ($prod['price'] ?? 0),
                        'stock'       => (int) ($prod['stock'] ?? 0),
                        'weight'      => $prod['weight'] ?? '500g',
                        'description' => $prod['desc'] ?? ($prod['description'] ?? ''),
                        'image'       => $prod['image'] ?? '/images/products/nugget.png',
                        'tags'        => $prod['tags'] ?? [],
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Seluruh data produk & stok berhasil disimpan!',
            'timestamp' => now()->translatedFormat('H:i:s \W\I\B'),
            'products' => Product::all(),
        ]);
    }

    // --- 2b. Ekspor & Impor Data Produk (Integrasi Kasir Pintar / Excel) ---

    /**
     * Export seluruh data produk freezer ke format Spreadsheet Excel Tabel Rapi (.xls)
     */
    public function exportProducts()
    {
        $products = Product::all();
        $filename = 'Etalase_Produk_IcaFrozenFood_' . now()->format('Y-m-d_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Microsoft Excel

            $exportDate = now()->format('d/m/Y H:i') . ' WITA';
            $totalItems = $products->count();
            $totalStock = $products->sum('stock');

            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<style>
                body { font-family: Calibri, Arial, sans-serif; }
                .report-header { font-size: 15pt; font-weight: bold; color: #1E3A8A; margin-bottom: 3px; }
                .report-sub { font-size: 9.5pt; color: #475569; margin-bottom: 12px; }
                table.data-table { border-collapse: collapse; width: 100%; }
                table.data-table th { background-color: #2563EB; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #1D4ED8; padding: 8px 6px; font-size: 10pt; }
                table.data-table td { border: 1px solid #CBD5E1; padding: 6px 8px; font-size: 9.5pt; vertical-align: middle; }
                .zebra { background-color: #F8FAFC; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .num-currency { mso-number-format: "\#\,\#\#0"; text-align: right; font-weight: bold; }
                .text-sku { mso-number-format: "\@"; text-align: center; font-family: monospace; }
                .badge-low { background-color: #FEF3C7; color: #B45309; font-weight: bold; text-align: center; }
                .badge-zero { background-color: #FEE2E2; color: #B91C1C; font-weight: bold; text-align: center; }
            </style></head><body>';

            echo '<div class="report-header">ICA FROZEN FOOD - DATA INVENTARIS PRODUK & STOK FREEZER</div>';
            echo '<div class="report-sub">Guntung Payung, Kec. Landasan Ulin, Kota Banjarbaru | Tanggal Export: ' . e($exportDate) . ' | Total Varian: ' . $totalItems . ' | Total Stok: ' . $totalStock . ' Unit</div>';

            echo '<table class="data-table">';
            echo '<thead><tr>
                <th style="width: 45px;">No</th>
                <th style="width: 90px;">Kode SKU</th>
                <th style="width: 260px;">Nama Produk Makanan Beku</th>
                <th style="width: 150px;">Kategori</th>
                <th style="width: 120px;">Harga Jual (Rp)</th>
                <th style="width: 90px;">Stok Unit</th>
                <th style="width: 90px;">Berat</th>
                <th style="width: 110px;">Status Stok</th>
                <th style="width: 250px;">Keterangan / Deskripsi</th>
            </tr></thead><tbody>';

            foreach ($products as $idx => $p) {
                $zebra = ($idx % 2 === 1) ? ' class="zebra"' : '';
                $stockStatus = 'Tersedia';
                $stockClass = '';
                if ($p->stock <= 0) {
                    $stockStatus = 'Habis';
                    $stockClass = ' class="badge-zero"';
                } elseif ($p->stock <= 5) {
                    $stockStatus = 'Menipis';
                    $stockClass = ' class="badge-low"';
                }

                echo "<tr{$zebra}>";
                echo '<td class="text-center">' . ($idx + 1) . '</td>';
                echo '<td class="text-sku">' . e($p->id) . '</td>';
                echo '<td style="font-weight: bold; color: #0F172A;">' . e($p->name) . '</td>';
                echo '<td>' . e($p->category) . '</td>';
                echo '<td class="num-currency">' . $p->price . '</td>';
                echo '<td class="text-center" style="font-weight: bold;">' . $p->stock . '</td>';
                echo '<td class="text-center">' . e($p->weight ?? '500g') . '</td>';
                echo "<td{$stockClass}>" . $stockStatus . '</td>';
                echo '<td>' . e($p->description ?? '-') . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table></body></html>';
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export seluruh data Paket Hemat (Bundling) ke format Spreadsheet Excel Tabel Rapi (.xls)
     */
    public function exportBundles()
    {
        $bundles = Bundle::with('items.product')->get();
        $filename = 'Paket_Hemat_IcaFrozenFood_' . now()->format('Y-m-d_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($bundles) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            $exportDate = now()->format('d/m/Y H:i') . ' WITA';

            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<style>
                body { font-family: Calibri, Arial, sans-serif; }
                .report-header { font-size: 15pt; font-weight: bold; color: #1E3A8A; margin-bottom: 3px; }
                .report-sub { font-size: 9.5pt; color: #475569; margin-bottom: 12px; }
                table.data-table { border-collapse: collapse; width: 100%; }
                table.data-table th { background-color: #2563EB; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #1D4ED8; padding: 8px 6px; font-size: 10pt; }
                table.data-table td { border: 1px solid #CBD5E1; padding: 6px 8px; font-size: 9.5pt; vertical-align: middle; }
                .zebra { background-color: #F8FAFC; }
                .text-center { text-align: center; }
                .num-currency { mso-number-format: "\#\,\#\#0"; text-align: right; font-weight: bold; }
                .text-code { mso-number-format: "\@"; text-align: center; font-family: monospace; }
                .badge-active { background-color: #DCFCE7; color: #15803D; font-weight: bold; text-align: center; }
                .badge-inactive { background-color: #F3F4F6; color: #4B5563; text-align: center; }
            </style></head><body>';

            echo '<div class="report-header">ICA FROZEN FOOD - DATA PAKET HEMAT (BUNDLING PROMO)</div>';
            echo '<div class="report-sub">Guntung Payung, Kec. Landasan Ulin, Kota Banjarbaru | Tanggal Export: ' . e($exportDate) . ' | Total Paket: ' . count($bundles) . '</div>';

            echo '<table class="data-table">';
            echo '<thead><tr>
                <th style="width: 45px;">No</th>
                <th style="width: 90px;">ID Paket</th>
                <th style="width: 220px;">Nama Paket Hemat</th>
                <th style="width: 120px;">Harga Normal (Rp)</th>
                <th style="width: 130px;">Harga Promo Paket (Rp)</th>
                <th style="width: 100px;">Hemat (Rp)</th>
                <th style="width: 110px;">Label Badge</th>
                <th style="width: 100px;">Status</th>
                <th style="width: 320px;">Isi Produk Relasional (Item Paket)</th>
            </tr></thead><tbody>';

            foreach ($bundles as $idx => $b) {
                $zebra = ($idx % 2 === 1) ? ' class="zebra"' : '';
                $saving = max(0, (int)$b->original_price - (int)$b->bundle_price);
                $statusClass = $b->is_active ? ' class="badge-active"' : ' class="badge-inactive"';
                $statusText = $b->is_active ? 'Aktif' : 'Nonaktif';

                $itemsArr = [];
                if ($b->items) {
                    foreach ($b->items as $it) {
                        $pName = $it->product ? $it->product->name : ($it->product_name ?? 'Produk');
                        $itemsArr[] = ($it->quantity ?? 1) . 'x ' . $pName;
                    }
                }
                $itemsStr = count($itemsArr) > 0 ? implode('; ', $itemsArr) : '-';

                echo "<tr{$zebra}>";
                echo '<td class="text-center">' . ($idx + 1) . '</td>';
                echo '<td class="text-code">' . e($b->id) . '</td>';
                echo '<td style="font-weight: bold; color: #0F172A;">' . e($b->name) . '</td>';
                echo '<td class="num-currency">' . (int)$b->original_price . '</td>';
                echo '<td class="num-currency" style="color: #2563EB;">' . (int)$b->bundle_price . '</td>';
                echo '<td class="num-currency" style="color: #15803D;">' . $saving . '</td>';
                echo '<td class="text-center">' . e($b->badge_right ?? 'Hemat') . '</td>';
                echo "<td{$statusClass}>" . $statusText . '</td>';
                echo '<td>' . e($itemsStr) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table></body></html>';
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download template CSV standar dan Kasir Pintar untuk panduan impor produk
     */
    public function exportProductTemplate()
    {
        $filename = 'Template_Import_Produk_KasirPintar.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Kode SKU',
                'Nama Produk',
                'Kategori',
                'Harga Jual',
                'Stok',
                'Berat Kemasan',
                'Deskripsi',
            ], ',');
            fputcsv($handle, [
                'P01',
                'Dimsum Ayam Premium (Isi 15)',
                'Dimsum & Siomay',
                '35000',
                '25',
                '350g',
                'Dimsum ayam premium dengan olahan daging ayam segar dan saus sambal khas.',
            ], ',');
            fputcsv($handle, [
                'P02',
                'Crispy Chicken Nugget Original',
                'Nugget & Sosis',
                '48000',
                '30',
                '500g',
                'Nugget ayam renyah keemasan, praktis digoreng untuk camilan keluarga.',
            ], ',');
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export rekap riwayat transaksi pesanan ke format Spreadsheet Excel Tabel Rapi (.xls)
     */
    public function exportOrders()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        $filename = 'Laporan_Penjualan_IcaFrozenFood_' . now()->format('Y-m-d_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            $exportDate = now()->format('d/m/Y H:i') . ' WITA';
            $totalIncome = $orders->where('is_paid', true)->sum('total');
            $totalOrders = $orders->count();

            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<style>
                body { font-family: Calibri, Arial, sans-serif; }
                .report-header { font-size: 15pt; font-weight: bold; color: #1E3A8A; margin-bottom: 3px; }
                .report-sub { font-size: 9.5pt; color: #475569; margin-bottom: 12px; }
                table.data-table { border-collapse: collapse; width: 100%; }
                table.data-table th { background-color: #2563EB; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #1D4ED8; padding: 8px 6px; font-size: 10pt; }
                table.data-table td { border: 1px solid #CBD5E1; padding: 6px 8px; font-size: 9pt; vertical-align: middle; }
                .zebra { background-color: #F8FAFC; }
                .text-center { text-align: center; }
                .num-currency { mso-number-format: "\#\,\#\#0"; text-align: right; font-weight: bold; }
                .text-code { mso-number-format: "\@"; text-align: center; font-family: monospace; font-weight: bold; }
                .text-phone { mso-number-format: "\@"; }
                .badge-paid { background-color: #DCFCE7; color: #15803D; font-weight: bold; text-align: center; }
                .badge-unpaid { background-color: #FEE2E2; color: #B91C1C; text-align: center; }
            </style></head><body>';

            echo '<div class="report-header">ICA FROZEN FOOD - REKAP LAPORAN PENJUALAN & TRANSAKSI</div>';
            echo '<div class="report-sub">Guntung Payung, Kec. Landasan Ulin, Kota Banjarbaru | Tanggal Cetak: ' . e($exportDate) . ' | Total Transaksi: ' . $totalOrders . ' | Total Omzet Terbayar: Rp ' . number_format($totalIncome, 0, ',', '.') . '</div>';

            echo '<table class="data-table">';
            echo '<thead><tr>
                <th style="width: 45px;">No</th>
                <th style="width: 100px;">No Pesanan</th>
                <th style="width: 120px;">Tanggal & Waktu</th>
                <th style="width: 80px;">Channel</th>
                <th style="width: 160px;">Nama Pelanggan</th>
                <th style="width: 120px;">No WhatsApp / HP</th>
                <th style="width: 110px;">Pengantaran</th>
                <th style="width: 120px;">Metode Bayar</th>
                <th style="width: 100px;">Status Pesanan</th>
                <th style="width: 95px;">Pembayaran</th>
                <th style="width: 250px;">Item Produk Dipesan</th>
                <th style="width: 100px;">Subtotal (Rp)</th>
                <th style="width: 80px;">Ongkir (Rp)</th>
                <th style="width: 110px;">Total (Rp)</th>
            </tr></thead><tbody>';

            foreach ($orders as $idx => $order) {
                $zebra = ($idx % 2 === 1) ? ' class="zebra"' : '';
                $itemsSummary = [];
                if (is_array($order->items)) {
                    foreach ($order->items as $it) {
                        $name = $it['name'] ?? 'Item';
                        $qty = $it['qty'] ?? 1;
                        $itemsSummary[] = "{$name} ({$qty}x)";
                    }
                }
                $itemStr = count($itemsSummary) > 0 ? implode('; ', $itemsSummary) : '-';
                $paidClass = $order->is_paid ? ' class="badge-paid"' : ' class="badge-unpaid"';
                $paidText = $order->is_paid ? 'Lunas' : 'Belum Lunas';

                echo "<tr{$zebra}>";
                echo '<td class="text-center">' . ($idx + 1) . '</td>';
                echo '<td class="text-code">' . e($order->id) . '</td>';
                echo '<td class="text-center">' . ($order->order_date ?? ($order->created_at ? $order->created_at->format('d/m/Y H:i') : '-')) . '</td>';
                echo '<td class="text-center">' . e($order->channel ?? 'Online') . '</td>';
                echo '<td style="font-weight: bold; color: #0F172A;">' . e($order->customer_name) . '</td>';
                echo '<td class="text-phone">' . e($order->customer_phone ?? '-') . '</td>';
                echo '<td>' . e($order->method ?? 'Ambil di Toko') . '</td>';
                echo '<td>' . e($order->payment_method ?? 'QRIS Instan') . '</td>';
                echo '<td class="text-center">' . e($order->status ?? 'Diproses') . '</td>';
                echo "<td{$paidClass}>" . $paidText . '</td>';
                echo '<td>' . e($itemStr) . '</td>';
                echo '<td class="num-currency">' . (int)($order->subtotal ?? 0) . '</td>';
                echo '<td class="num-currency">' . (int)($order->shipping_fee ?? 0) . '</td>';
                echo '<td class="num-currency" style="color: #2563EB;">' . (int)($order->total ?? 0) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table></body></html>';
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import data produk dari file spreadsheet Excel (.xlsx) atau CSV (Kasir Pintar Pro & Template Standar)
     */
    public function importProducts(Request $request)
    {
        $rows = [];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();

            if (in_array($extension, ['xlsx', 'xls'])) {
                $rows = $this->parseXlsxFile($path);
                // Fallback: Jika file berekstensi .xlsx/.xls namun berisi teks/CSV
                if (empty($rows)) {
                    $rows = $this->parseCsvFile($path);
                }
            } elseif (in_array($extension, ['csv', 'txt'])) {
                $rows = $this->parseCsvFile($path);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Format file tidak didukung. Harap unggah file .xlsx atau .csv.',
                ], 422);
            }
        } elseif ($request->filled('rows')) {
            $rows = $request->input('rows');
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih file spreadsheet atau unggah berkas yang valid.',
            ], 422);
        }

        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File kosong atau tidak memiliki baris data produk yang dapat dibaca.',
            ], 422);
        }

        // Header mapping & pembersihan karakter tersembunyi UTF-8 BOM (\xEF\xBB\xBF)
        $headerRow = array_map(function ($h) {
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', (string) $h);
            $clean = preg_replace('/[\x{FEFF}\x{200B}]/u', '', $clean);
            return strtolower(trim($clean));
        }, $rows[0]);

        $colMap = [
            'sku'         => -1,
            'name'        => -1,
            'category'    => -1,
            'price'       => -1,
            'stock'       => -1,
            'weight'      => -1,
            'description' => -1,
        ];

        foreach ($headerRow as $idx => $headerText) {
            $cleanBOM = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headerText);
            $cleanBOM = preg_replace('/[\x{FEFF}\x{200B}]/u', '', $cleanBOM);
            $clean = preg_replace('/[^a-z0-9_ ]/', '', strtolower(trim($cleanBOM)));

            if ($colMap['sku'] === -1 && (
                str_contains($clean, 'sku') || 
                str_contains($clean, 'kode') || 
                str_contains($clean, 'barcode') || 
                $clean === 'id' ||
                str_contains($clean, 'kodeproduk') ||
                str_contains($clean, 'kodebarang') ||
                str_contains($clean, 'product id')
            )) {
                $colMap['sku'] = $idx;
            } elseif ($colMap['name'] === -1 && (
                str_contains($clean, 'nama') || 
                str_contains($clean, 'name') || 
                str_contains($clean, 'produk') || 
                str_contains($clean, 'barang') || 
                str_contains($clean, 'item') ||
                str_contains($clean, 'product')
            )) {
                $colMap['name'] = $idx;
            } elseif ($colMap['category'] === -1 && (
                str_contains($clean, 'kategori') || 
                str_contains($clean, 'category') || 
                str_contains($clean, 'kelompok') || 
                str_contains($clean, 'group')
            )) {
                $colMap['category'] = $idx;
            } elseif ($colMap['price'] === -1 && (
                str_contains($clean, 'harga') || 
                str_contains($clean, 'price') || 
                str_contains($clean, 'jual')
            )) {
                $colMap['price'] = $idx;
            } elseif ($colMap['stock'] === -1 && (
                str_contains($clean, 'stok') || 
                str_contains($clean, 'stock') || 
                str_contains($clean, 'qty') || 
                str_contains($clean, 'jumlah') ||
                str_contains($clean, 'quantity')
            )) {
                $colMap['stock'] = $idx;
            } elseif ($colMap['weight'] === -1 && (
                str_contains($clean, 'berat') || 
                str_contains($clean, 'weight') || 
                str_contains($clean, 'kemasan') || 
                str_contains($clean, 'satuan')
            )) {
                $colMap['weight'] = $idx;
            } elseif ($colMap['description'] === -1 && (
                str_contains($clean, 'deskripsi') || 
                str_contains($clean, 'keterangan') || 
                str_contains($clean, 'detail') || 
                str_contains($clean, 'desc')
            )) {
                $colMap['description'] = $idx;
            }
        }

        if ($colMap['name'] === -1) {
            $colMap['name'] = isset($headerRow[1]) ? 1 : 0;
        }

        // Pra-validasi integritas seluruh data baris per baris sebelum eksekusi ke database (Atomic Protection)
        $validationErrors = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row) || !is_array($row)) continue;

            $rowNum = $i + 1; // Baris fisik riil spreadsheet (header adalah baris 1)
            $name = $colMap['name'] !== -1 ? trim((string)($row[$colMap['name']] ?? '')) : '';

            $allEmpty = true;
            foreach ($row as $cell) {
                if (trim((string)$cell) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) continue;

            if ($name === '') {
                $validationErrors[] = "Baris {$rowNum}: Kolom nama produk tidak boleh kosong.";
                continue;
            }

            // Validasi Stok Ketat: tidak boleh negatif atau non-numerik tak valid
            if ($colMap['stock'] !== -1) {
                $stockRaw = isset($row[$colMap['stock']]) ? trim((string)$row[$colMap['stock']]) : '';
                if ($stockRaw === '') {
                    $validationErrors[] = "Baris {$rowNum} ('{$name}'): Kolom jumlah stok tidak boleh kosong.";
                } else {
                    if (str_starts_with($stockRaw, '-') || (is_numeric($stockRaw) && (float)$stockRaw < 0)) {
                        $validationErrors[] = "Baris {$rowNum} ('{$name}'): Jumlah stok tidak boleh kurang dari 0.";
                    } elseif (!is_numeric($stockRaw) || floor((float)$stockRaw) != (float)$stockRaw) {
                        $validationErrors[] = "Baris {$rowNum} ('{$name}'): Jumlah stok harus berupa bilangan bulat valid ('{$stockRaw}').";
                    }
                }
            }

            // Validasi Harga Jual (tidak boleh bernilai negatif)
            if ($colMap['price'] !== -1) {
                $priceRaw = isset($row[$colMap['price']]) ? trim((string)$row[$colMap['price']]) : '';
                if ($priceRaw !== '') {
                    $cleanPrice = str_ireplace(['rp', ' ', '.'], '', $priceRaw);
                    $cleanPrice = str_replace(',', '.', $cleanPrice);
                    if (str_starts_with($priceRaw, '-') || (is_numeric($cleanPrice) && (float)$cleanPrice < 0)) {
                        $validationErrors[] = "Baris {$rowNum} ('{$name}'): Harga jual produk tidak boleh bernilai negatif ({$priceRaw}).";
                    } elseif (!is_numeric($cleanPrice)) {
                        $validationErrors[] = "Baris {$rowNum} ('{$name}'): Harga jual harus berupa angka valid ('{$priceRaw}').";
                    }
                }
            }
        }

        // Jika terdapat 1 baris saja yang error, batalkan seluruh proses impor (Atomic protection)
        if (!empty($validationErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Proses impor dibatalkan. Ditemukan ' . count($validationErrors) . ' kesalahan validasi data:',
                'errors' => $validationErrors,
            ], 422);
        }

        $imported = 0;
        $updated = 0;
        $created = 0;
        $createdItems = [];
        $updatedItems = [];

        try {
            DB::transaction(function () use ($rows, $colMap, &$imported, &$updated, &$created, &$createdItems, &$updatedItems) {
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    if (empty($row) || !is_array($row)) continue;

                    $name = $colMap['name'] !== -1 ? trim((string)($row[$colMap['name']] ?? '')) : '';
                    if ($name === '') continue;

                    $sku = $colMap['sku'] !== -1 ? trim((string)($row[$colMap['sku']] ?? '')) : '';
                    if ($sku === '') {
                        $sku = 'PRD-' . time() . '-' . $i;
                    }

                    $category = $colMap['category'] !== -1 ? trim((string)($row[$colMap['category']] ?? '')) : 'Frozen Food';
                    if ($category === '') $category = 'Frozen Food';

                    Category::firstOrCreate(['name' => $category]);

                    $priceRaw = $colMap['price'] !== -1 ? (string)($row[$colMap['price']] ?? '0') : '0';
                    $cleanPrice = str_ireplace(['rp', ' ', '.'], '', trim($priceRaw));
                    $cleanPrice = str_replace(',', '.', $cleanPrice);
                    $price = max(0, (int) round((float) $cleanPrice));

                    $stockRaw = $colMap['stock'] !== -1 ? trim((string)($row[$colMap['stock']] ?? '0')) : '0';
                    $stockVal = (int) $stockRaw;
                    if ($stockVal < 0) {
                        throw new \Exception("Baris " . ($i + 1) . " ('{$name}'): Jumlah stok tidak boleh kurang dari 0.");
                    }

                    $weight = $colMap['weight'] !== -1 ? trim((string)($row[$colMap['weight']] ?? '500g')) : '500g';
                    if ($weight === '') $weight = '500g';

                    $desc = $colMap['description'] !== -1 ? trim((string)($row[$colMap['description']] ?? '')) : '';

                    // Logika Upsert berdasarkan Kode SKU:
                    // • SKU Baru: Simpan sebagai produk baru (Insert).
                    // • SKU Sudah Ada: Timpa (Update/Overwrite) data produk dan jumlah stok lama dengan data baru dari file.
                    $existing = Product::find($sku);
                    if ($existing) {
                        $oldPrice = $existing->price;
                        $oldStock = $existing->stock;
                        $newPrice = $price > 0 ? $price : $existing->price;

                        $existing->update([
                            'name'        => $name,
                            'category'    => $category,
                            'price'       => $newPrice,
                            'stock'       => $stockVal, // Timpa (overwrite) jumlah stok lama
                            'weight'      => $weight,
                            'description' => $desc ?: $existing->description,
                        ]);
                        $updated++;
                        $imported++;
                        $updatedItems[] = [
                            'sku'       => $sku,
                            'name'      => $name,
                            'category'  => $category,
                            'price'     => $newPrice,
                            'old_price' => $oldPrice,
                            'stock'     => $stockVal,
                            'old_stock' => $oldStock,
                        ];
                    } else {
                        Product::create([
                            'id'          => $sku,
                            'name'        => $name,
                            'category'    => $category,
                            'price'       => $price,
                            'stock'       => $stockVal,
                            'weight'      => $weight,
                            'description' => $desc,
                            'image'       => '/images/products/nugget.png',
                            'tags'        => ['Kasir Pintar'],
                        ]);
                        $created++;
                        $imported++;
                        $createdItems[] = [
                            'sku'      => $sku,
                            'name'     => $name,
                            'category' => $category,
                            'price'    => $price,
                            'stock'    => $stockVal,
                        ];
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proses impor dibatalkan: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses {$imported} produk ({$created} produk baru ditambahkan, {$updated} produk diperbarui) dari file spreadsheet!",
            'imported_count' => $imported,
            'created_count' => $created,
            'updated_count' => $updated,
            'created_items' => $createdItems,
            'updated_items' => $updatedItems,
            'products' => Product::all(),
            'categories' => Category::all()->pluck('name'),
        ]);
    }

    /**
     * Parsing file CSV menjadi array baris data (dengan pembersihan UTF-8 BOM otomatis)
     */
    private function parseCsvFile(string $filePath): array
    {
        $rows = [];
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        // 1. Bersihkan UTF-8 BOM (\xEF\xBB\xBF) jika ada di awal file
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        // 2. Deteksi delimiter dari baris pertama (koma, titik koma, atau tab)
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $firstLine = $lines[0] ?? '';
        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');
        $tabCount = substr_count($firstLine, "\t");

        $delimiter = ',';
        if ($semicolonCount > $commaCount && $semicolonCount > $tabCount) {
            $delimiter = ';';
        } elseif ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            $delimiter = "\t";
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        while (($data = fgetcsv($stream, 0, $delimiter)) !== false) {
            if ($data === [null] || empty($data)) continue;

            $cleanedRow = array_map(function ($val) {
                $val = preg_replace('/^\xEF\xBB\xBF/', '', (string) $val);
                $val = preg_replace('/[\x{FEFF}\x{200B}]/u', '', $val);
                return trim($val);
            }, $data);

            // Lewati jika seluruh kolom dalam baris kosong
            $hasContent = false;
            foreach ($cleanedRow as $c) {
                if ($c !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if ($hasContent) {
                $rows[] = $cleanedRow;
            }
        }
        fclose($stream);

        return $rows;
    }

    /**
     * Parsing file Excel .xlsx murni PHP (ZipArchive & SimpleXML) dengan pembersihan BOM
     */
    private function parseXlsxFile(string $filePath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $xml = simplexml_load_string($ssXml);
            if ($xml) {
                foreach ($xml->children() as $item) {
                    $text = '';
                    if (isset($item->t)) {
                        $text = (string) $item->t;
                    } elseif (isset($item->r)) {
                        foreach ($item->r as $run) {
                            $text .= (string) $run->t;
                        }
                    }
                    $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
                    $text = preg_replace('/[\x{FEFF}\x{200B}]/u', '', $text);
                    $sharedStrings[] = trim($text);
                }
            }
        }

        // Cari worksheet sheet1.xml atau sheet xml pertama yang ada
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if ($stat && str_starts_with($stat['name'], 'xl/worksheets/sheet') && str_ends_with($stat['name'], '.xml')) {
                    $sheetXml = $zip->getFromIndex($i);
                    if ($sheetXml !== false) break;
                }
            }
        }

        if ($sheetXml === false) {
            $zip->close();
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        $rows = [];

        if ($xml && isset($xml->sheetData)) {
            foreach ($xml->sheetData->row as $row) {
                $rowArr = [];
                $currentCol = 0;

                foreach ($row->c as $cell) {
                    $ref = (string) $cell['r'];
                    preg_match('/([A-Z]+)/', $ref, $matches);
                    $colStr = $matches[1] ?? 'A';

                    $targetCol = 0;
                    $len = strlen($colStr);
                    for ($k = 0; $k < $len; $k++) {
                        $targetCol = $targetCol * 26 + (ord($colStr[$k]) - 64);
                    }
                    $targetCol -= 1;

                    while ($currentCol < $targetCol) {
                        $rowArr[] = '';
                        $currentCol++;
                    }

                    $type = (string) $cell['t'];
                    $val = isset($cell->v) ? (string) $cell->v : '';

                    if ($type === 's') {
                        $sIndex = (int) $val;
                        $val = $sharedStrings[$sIndex] ?? '';
                    } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                        $val = (string) $cell->is->t;
                    }

                    $val = preg_replace('/^\xEF\xBB\xBF/', '', $val);
                    $val = preg_replace('/[\x{FEFF}\x{200B}]/u', '', $val);
                    $rowArr[] = trim($val);
                    $currentCol++;
                }

                $hasContent = false;
                foreach ($rowArr as $c) {
                    if ($c !== '') {
                        $hasContent = true;
                        break;
                    }
                }
                if ($hasContent) {
                    $rows[] = $rowArr;
                }
            }
        }

        $zip->close();
        return $rows;
    }

    // --- 3. Proses Pesanan ---

    // Mengambil riwayat daftar seluruh pesanan pelanggan (Aman: Admin melihat semua, user melihat miliknya sendiri)
    public function getOrders()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            $ordersQuery = Order::orderBy('created_at', 'desc');
        } elseif (Auth::check()) {
            $user = Auth::user();
            $ordersQuery = Order::where(function ($q) use ($user) {
                $q->where('customer_name', $user->name);
                if (!empty($user->phone)) {
                    $q->orWhere('customer_phone', $user->phone);
                }
            })->orderBy('created_at', 'desc');
        } else {
            return response()->json([
                'success' => true,
                'orders'  => [],
            ]);
        }

        $orders = $ordersQuery->get()->map(function ($order) {
            return [
                'id'            => $order->id,
                'customerName'  => $order->customer_name,
                'customerPhone' => $order->customer_phone,
                'address'       => $order->address,
                'date'          => $order->order_date,
                'method'        => $order->method,
                'paymentMethod' => $order->payment_method,
                'status'        => $order->status,
                'items'         => $order->items,
                'subtotal'      => $order->subtotal,
                'shippingFee'   => $order->shipping_fee ?? $order->ice_fee ?? 0,
                'iceFee'        => $order->shipping_fee ?? $order->ice_fee ?? 0,
                'total'         => $order->total,
                'isPaid'        => (bool) $order->is_paid,
                'is_paid'       => (bool) $order->is_paid,
                'paymentProof'  => $order->payment_proof ? asset($order->payment_proof) : null,
                'payment_proof' => $order->payment_proof ? asset($order->payment_proof) : null,
                'channel'       => $order->channel,
            ];
        });

        return response()->json([
            'success' => true,
            'orders'  => $orders,
        ]);
    }

    // Menyimpan pesanan baru dan otomatis mengurangi stok produk
    public function storeOrder(Request $request)
    {
        // Pastikan ID unik dan tidak menimpa pesanan orang lain jika acak
        $inputId = $request->input('id');
        if (empty($inputId)) {
            do {
                $id = 'ORD-ICA-' . strtoupper(bin2hex(random_bytes(3)));
            } while (Order::where('id', $id)->exists());
        } else {
            $id = $inputId;
            $existingOrder = Order::find($id);
            // Cegah penimpaan (IDOR overwrite) jika bukan admin yang mengupdate pesanan
            if ($existingOrder && (!Auth::check() || !Auth::user()->isAdmin())) {
                do {
                    $id = 'ORD-ICA-' . strtoupper(bin2hex(random_bytes(3)));
                } while (Order::where('id', $id)->exists());
            }
        }
        $custName = $request->input('customerName') ?: ($request->input('customer_name') ?: 'Pelanggan');
        $custPhone = $request->input('customerPhone') ?: ($request->input('customer_phone') ?: '-');
        $address = $request->input('address') ?: '-';
        $date = $request->input('date') ?: ($request->input('order_date') ?: now()->timezone('Asia/Makassar')->format('Y-m-d H:i'));
        $method = $request->input('method') ?: 'Ambil di Toko / Self Pick-up';
        $payMethod = $request->input('paymentMethod') ?: ($request->input('payment_method') ?: 'QRIS Instan');
        $status = $request->input('status') ?: (stripos($payMethod, 'tunai') !== false ? 'Menunggu Bayar' : 'Menunggu Konfirmasi Admin');
        $rawItems = $request->input('items') ?: [];
        if (is_string($rawItems)) {
            $items = json_decode($rawItems, true) ?: [];
        } else {
            $items = is_array($rawItems) ? $rawItems : [];
        }
        $channel = $request->input('channel') ?: 'Online Store';
        $isAdmin = Auth::check() && (Auth::user()->isAdmin() || Auth::user()->is_admin || Auth::user()->role === 'admin');

        // Validasi Toko Tutup Server-Side: Cegah pesanan online baru jika toko sedang tutup
        if (stripos($channel, 'pos') === false && !$isAdmin) {
            $storeStatus = self::getStoreStatusArray();
            if (!$storeStatus['is_open']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon maaf, operasional toko sedang TUTUP SEMENTARA saat ini. Pemesanan tidak dapat diproses sampai toko buka kembali (08.00 – 21.00 WITA).',
                ], 422);
            }
        }

        // Validasi Ketersediaan Stok Sebelum Pembuatan Pesanan
        if (is_array($items) && count($items) > 0) {
            foreach ($items as $item) {
                if (!empty($item['id'])) {
                    $qty = (int) ($item['qty'] ?? 1);
                    if (str_starts_with((string)$item['id'], 'bundle_') || !empty($item['is_bundle'])) {
                        $bundleId = str_replace('bundle_', '', (string)$item['id']);
                        $bundle = Bundle::with('items.product')->find($bundleId);
                        if ($bundle && $bundle->items) {
                            foreach ($bundle->items as $bItem) {
                                $bProd = $bItem->product ?: Product::find($bItem->product_id);
                                $needed = $bItem->quantity * $qty;
                                if (!$bProd || $bProd->stock < $needed) {
                                    $pName = $bProd ? $bProd->name : 'Produk';
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Stok untuk produk '{$pName}' dalam paket hemat tidak mencukupi (tersedia: " . ($bProd ? $bProd->stock : 0) . ").",
                                    ], 422);
                                }
                            }
                        }
                    } else {
                        $prod = Product::find($item['id']);
                        if (!$prod || $prod->stock < $qty) {
                            $pName = $prod ? $prod->name : 'Produk';
                            return response()->json([
                                'success' => false,
                                'message' => "Stok untuk produk '{$pName}' tidak mencukupi (tersedia: " . ($prod ? $prod->stock : 0) . ").",
                            ], 422);
                        }
                    }
                }
            }
        }

        // Validasi & Rekalkulasi Harga Server-Side (Cegah Price / Parameter Tampering)
        $calculatedSubtotal = 0;
        $validatedItems = [];
        if (is_array($items) && count($items) > 0) {
            foreach ($items as $item) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $itemId = $item['id'] ?? null;
                $itemPrice = (int) ($item['price'] ?? 0);
                $itemName = $item['name'] ?? 'Produk';
                $itemImage = $item['image'] ?? null;

                if ($itemId) {
                    if (str_starts_with((string)$itemId, 'bundle_') || !empty($item['is_bundle'])) {
                        $bundleId = str_replace('bundle_', '', (string)$itemId);
                        $bundle = Bundle::find($bundleId);
                        if ($bundle) {
                            $itemPrice = (int) $bundle->price;
                            $itemName = $bundle->name;
                        }
                    } else {
                        $prod = Product::find($itemId);
                        if ($prod) {
                            $itemPrice = (int) $prod->price;
                            $itemName = $prod->name;
                            $itemImage = $prod->image ?: $itemImage;
                        }
                    }
                }

                $calculatedSubtotal += ($itemPrice * $qty);
                $item['qty'] = $qty;
                $item['price'] = $itemPrice;
                $item['name'] = $itemName;
                if ($itemImage) $item['image'] = $itemImage;
                $validatedItems[] = $item;
            }
            $items = $validatedItems;
            $subtotal = $calculatedSubtotal;
        } else {
            $subtotal = (int) ($request->input('subtotal') ?: 0);
        }

        // Tentukan biaya pengiriman (Kurir Toko = 5000, Ambil di Toko = 0)
        $isDelivery = (stripos($method, 'kurir') !== false || stripos($method, 'antar') !== false || stripos($method, 'delivery') !== false);
        $shippingFee = $isDelivery ? 5000 : 0;
        $total = $subtotal + $shippingFee;
        $isPaid = $request->has('isPaid') ? $request->boolean('isPaid') : ($request->has('is_paid') ? $request->boolean('is_paid') : false);
        $channel = $request->input('channel') ?: 'Online';

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $request->validate([
                'proof' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);
            $file = $request->file('proof');
            $uploadDir = self::getProofUploadDir();
            $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
            if (!in_array($ext, ['jpeg', 'png', 'jpg', 'webp'])) {
                $ext = 'jpg';
            }
            $fileName = 'proof_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $id) . '_' . time() . '.' . $ext;
            $file->move($uploadDir, $fileName);
            @chmod($uploadDir . '/' . $fileName, 0644);

            // Simpan salinan ke public_path jika berbeda (antisipasi struktur folder terpisah)
            $altDir = public_path('uploads/proofs');
            if ($altDir !== $uploadDir) {
                if (!file_exists($altDir)) {
                    @mkdir($altDir, 0755, true);
                }
                @copy($uploadDir . '/' . $fileName, $altDir . '/' . $fileName);
                @chmod($altDir . '/' . $fileName, 0644);
            }

            $proofPath = 'uploads/proofs/' . $fileName;
            $status = 'Menunggu Konfirmasi Admin';
            $isPaid = false;
        }

        // Pesanan baru berstatus belum diverifikasi atau belum bayar wajib is_paid = false
        if ($status === 'Menunggu Konfirmasi Admin' || $status === 'Menunggu Bayar' || $status === 'Menunggu Pembayaran') {
            $isPaid = false;
        }

        $orderData = [
            'customer_name'  => $custName,
            'customer_phone' => $custPhone,
            'address'        => $address,
            'order_date'     => $date,
            'method'         => $method,
            'payment_method' => $payMethod,
            'status'         => $status,
            'items'          => $items,
            'subtotal'       => $subtotal,
            'shipping_fee'   => $shippingFee,
            'total'          => $total,
            'is_paid'        => $isPaid,
            'channel'        => $channel,
        ];

        if ($proofPath) {
            $orderData['payment_proof'] = $proofPath;
        }

        // Transaksi Basis Data Atomik (Cegah Race Condition saat Checkout & Pemotongan Stok)
        $order = DB::transaction(function () use ($id, $orderData, $items) {
            $order = Order::updateOrCreate(['id' => $id], $orderData);

            if (is_array($items)) {
                foreach ($items as $item) {
                    if (!empty($item['id'])) {
                        $qty = (int) ($item['qty'] ?? 1);
                        if (str_starts_with((string)$item['id'], 'bundle_') || !empty($item['is_bundle'])) {
                            $bundleId = str_replace('bundle_', '', (string)$item['id']);
                            $bundle = Bundle::with('items')->find($bundleId);
                            if ($bundle && $bundle->items) {
                                foreach ($bundle->items as $bItem) {
                                    $bProd = Product::find($bItem->product_id);
                                    if ($bProd) {
                                        $bProd->stock = max(0, $bProd->stock - ($bItem->quantity * $qty));
                                        $bProd->save();
                                    }
                                }
                            }
                        } else {
                            $prod = Product::find($item['id']);
                            if ($prod) {
                                $prod->stock = max(0, $prod->stock - $qty);
                                $prod->save();
                            }
                        }
                    }
                }
            }

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil disimpan!',
            'order'   => [
                'id'            => $order->id,
                'customerName'  => $order->customer_name,
                'customerPhone' => $order->customer_phone,
                'address'       => $order->address,
                'date'          => $order->order_date,
                'method'        => $order->method,
                'paymentMethod' => $order->payment_method,
                'status'        => $order->status,
                'items'         => $order->items,
                'subtotal'      => $order->subtotal,
                'shippingFee'   => $order->shipping_fee ?? $order->ice_fee ?? 0,
                'iceFee'        => $order->shipping_fee ?? $order->ice_fee ?? 0,
                'total'         => $order->total,
                'isPaid'        => (bool) $order->is_paid,
                'is_paid'       => (bool) $order->is_paid,
                'paymentProof'  => $order->payment_proof ? asset($order->payment_proof) : null,
                'payment_proof' => $order->payment_proof ? asset($order->payment_proof) : null,
                'channel'       => $order->channel,
            ],
        ]);
    }

    // Memperbarui status proses pesanan (Diproses, Dikirim, Selesai)
    public function updateOrderStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'is_paid' => 'nullable|boolean',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // Restorasi stok otomatis jika pesanan dibatalkan
        if ($newStatus === 'Dibatalkan' && $oldStatus !== 'Dibatalkan') {
            $items = is_array($order->items) ? $order->items : (json_decode($order->items, true) ?: []);
            foreach ($items as $item) {
                if (!empty($item['id'])) {
                    $qty = (int) ($item['qty'] ?? 1);
                    if (str_starts_with((string)$item['id'], 'bundle_') || !empty($item['is_bundle'])) {
                        $bundleId = str_replace('bundle_', '', (string)$item['id']);
                        $bundle = Bundle::with('items')->find($bundleId);
                        if ($bundle && $bundle->items) {
                            foreach ($bundle->items as $bItem) {
                                $bProd = Product::find($bItem->product_id);
                                if ($bProd) {
                                    $bProd->stock += ($bItem->quantity * $qty);
                                    $bProd->save();
                                }
                            }
                        }
                    } else {
                        $prod = Product::find($item['id']);
                        if ($prod) {
                            $prod->stock += $qty;
                            $prod->save();
                        }
                    }
                }
            }
        } elseif ($oldStatus === 'Dibatalkan' && $newStatus !== 'Dibatalkan') {
            // Potong kembali stok jika status pesanan yang sebelumnya batal diaktifkan kembali
            $items = is_array($order->items) ? $order->items : (json_decode($order->items, true) ?: []);
            foreach ($items as $item) {
                if (!empty($item['id'])) {
                    $qty = (int) ($item['qty'] ?? 1);
                    if (str_starts_with((string)$item['id'], 'bundle_') || !empty($item['is_bundle'])) {
                        $bundleId = str_replace('bundle_', '', (string)$item['id']);
                        $bundle = Bundle::with('items')->find($bundleId);
                        if ($bundle && $bundle->items) {
                            foreach ($bundle->items as $bItem) {
                                $bProd = Product::find($bItem->product_id);
                                if ($bProd) {
                                    $bProd->stock = max(0, $bProd->stock - ($bItem->quantity * $qty));
                                    $bProd->save();
                                }
                            }
                        }
                    } else {
                        $prod = Product::find($item['id']);
                        if ($prod) {
                            $prod->stock = max(0, $prod->stock - $qty);
                            $prod->save();
                        }
                    }
                }
            }
        }

        $order->status = $newStatus;
        if ($request->has('is_paid')) {
            $order->is_paid = $request->boolean('is_paid');
        } elseif ($validated['status'] === 'Diproses' || $validated['status'] === 'Selesai') {
            $order->is_paid = true;
        } elseif ($validated['status'] === 'Menunggu Konfirmasi Admin' || $validated['status'] === 'Menunggu Bayar' || $validated['status'] === 'Menunggu Pembayaran' || $validated['status'] === 'Dibatalkan') {
            $order->is_paid = false;
        }
        $order->save();

        return response()->json([
            'success' => true,
            'message' => "Status pesanan {$id} berhasil diperbarui!",
            'order'   => [
                'id'            => $order->id,
                'customerName'  => $order->customer_name,
                'customerPhone' => $order->customer_phone,
                'address'       => $order->address,
                'date'          => $order->order_date,
                'method'        => $order->method,
                'paymentMethod' => $order->payment_method,
                'status'        => $order->status,
                'items'         => $order->items,
                'subtotal'      => $order->subtotal,
                'shippingFee'   => $order->shipping_fee ?? $order->ice_fee ?? 0,
                'iceFee'        => $order->shipping_fee ?? $order->ice_fee ?? 0,
                'total'         => $order->total,
                'isPaid'        => (bool) $order->is_paid,
                'is_paid'       => (bool) $order->is_paid,
                'paymentProof'  => $order->payment_proof ? asset($order->payment_proof) : null,
                'payment_proof' => $order->payment_proof ? asset($order->payment_proof) : null,
                'channel'       => $order->channel,
            ],
        ]);
    }

    // --- 4. Sakelar Buka/Tutup Toko (Store Status) ---

    // Helper lokasi direktori upload bukti transfer tahan banting (support shared hosting & local)
    public static function getProofUploadDir(): string
    {
        $publicHtml = dirname(base_path()) . '/public_html';
        if (is_dir($publicHtml)) {
            $dir = $publicHtml . '/uploads/proofs';
        } else {
            $dir = public_path('uploads/proofs');
        }

        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
        @chmod(dirname($dir), 0755);
        @chmod($dir, 0755);

        return $dir;
    }

    // Streaming bukti pembayaran langsung via PHP (bebas blokir 403 Apache shared hosting)
    public function showPaymentProof($id)
    {
        $order = Order::find($id);
        if (!$order || !$order->payment_proof) {
            abort(404, 'Bukti pembayaran tidak ditemukan');
        }

        $filename = basename($order->payment_proof);

        $possiblePaths = [
            dirname(base_path()) . '/public_html/uploads/proofs/' . $filename,
            public_path('uploads/proofs/' . $filename),
            base_path('public/uploads/proofs/' . $filename),
            dirname(base_path()) . '/icafrozenfood/public/uploads/proofs/' . $filename,
            storage_path('app/public/uploads/proofs/' . $filename),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                return response()->file($path, [
                    'Content-Type'  => $mime,
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        abort(404, 'File bukti transfer fisik tidak ditemukan di server');
    }

    public static function getStoreStatusArray()
    {
        $statusFile = storage_path('app/store_status.json');
        if (file_exists($statusFile)) {
            $data = json_decode(file_get_contents($statusFile), true);
            if (is_array($data)) {
                return [
                    'is_open' => (bool) ($data['is_open'] ?? true),
                    'message' => $data['message'] ?? 'Toko Buka (08.00 - 20.00 WIB)',
                ];
            }
        }
        return [
            'is_open' => true,
            'message' => 'Toko Buka (08.00 - 20.00 WIB)',
        ];
    }

    public function getStoreStatus()
    {
        return response()->json([
            'success' => true,
            'store_status' => self::getStoreStatusArray(),
        ]);
    }

    public function toggleStoreStatus(Request $request)
    {
        $statusFile = storage_path('app/store_status.json');
        $current = self::getStoreStatusArray();
        $newStatus = $request->has('is_open') ? $request->boolean('is_open') : !($current['is_open']);
        
        $data = [
            'is_open' => $newStatus,
            'message' => $request->input('message') ?: ($newStatus ? 'Toko Buka (08.00 - 20.00 WIB)' : 'Toko Sedang Tutup. Pesanan online akan diproses saat toko buka.'),
            'updated_at' => now()->toIso8601String(),
        ];

        if (!file_exists(dirname($statusFile))) {
            @mkdir(dirname($statusFile), 0755, true);
        }
        file_put_contents($statusFile, json_encode($data, JSON_PRETTY_PRINT));

        return response()->json([
            'success' => true,
            'message' => $newStatus ? 'Toko berhasil dibuka! Pelanggan dapat memesan normal.' : 'Toko berhasil ditutup! Banner toko tutup akan ditampilkan ke pelanggan.',
            'store_status' => $data,
        ]);
    }

    // --- 5. Upload & Validasi Bukti Pembayaran (Payment Proof) ---

    public function uploadPaymentProof(Request $request, $id)
    {
        $request->validate([
            'proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $uploadDir = self::getProofUploadDir();
            $fileName = 'proof_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $id) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            @chmod($uploadDir . '/' . $fileName, 0644);

            $altDir = public_path('uploads/proofs');
            if ($altDir !== $uploadDir) {
                if (!file_exists($altDir)) {
                    @mkdir($altDir, 0755, true);
                }
                @copy($uploadDir . '/' . $fileName, $altDir . '/' . $fileName);
                @chmod($altDir . '/' . $fileName, 0644);
            }
            
            $order->payment_proof = 'uploads/proofs/' . $fileName;
            $order->status = 'Menunggu Konfirmasi Admin';
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diunggah! Menunggu validasi admin toko.',
                'payment_proof' => asset('uploads/proofs/' . $fileName),
                'status' => $order->status,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'File gambar bukti tidak ditemukan'], 422);
    }

    public function verifyPayment(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accept,reject',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $action = $request->input('action');
        if ($action === 'accept') {
            $order->is_paid = true;
            $order->status = 'Diproses';
            $order->save();
            $msg = "Pembayaran pesanan {$id} berhasil diverifikasi dan disetujui!";
        } else {
            $order->is_paid = false;
            $order->status = 'Menunggu Pembayaran';
            $order->save();
            $msg = "Bukti pembayaran pesanan {$id} ditolak. Status pesanan dikembalikan ke Menunggu Pembayaran.";
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'order' => [
                'id' => $order->id,
                'isPaid' => (bool) $order->is_paid,
                'status' => $order->status,
                'paymentProof' => $order->payment_proof ? asset($order->payment_proof) : null,
            ],
        ]);
    }
}
