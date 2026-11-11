<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Bundle;
use App\Models\BundleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'stock' => 'nullable|integer',
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        if ($request->has('delta')) {
            $product->stock = max(0, $product->stock + (int) $validated['delta']);
        } elseif ($request->has('stock')) {
            $product->stock = max(0, (int) $validated['stock']);
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
     * Export seluruh data produk freezer ke format CSV (BOM UTF-8, kompatibel Microsoft Excel)
     */
    public function exportProducts()
    {
        $products = Product::all();
        $filename = 'Etalase_Produk_IcaFrozenFood_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Microsoft Excel

            fputcsv($handle, [
                'Kode SKU',
                'Nama Produk',
                'Kategori',
                'Harga Jual (Rp)',
                'Stok Freezer',
                'Berat Kemasan',
                'Keterangan / Deskripsi',
                'Label Tag',
            ], ',');

            foreach ($products as $p) {
                $tagsStr = is_array($p->tags) ? implode(', ', $p->tags) : ($p->tags ?? '');
                fputcsv($handle, [
                    $p->id,
                    $p->name,
                    $p->category,
                    $p->price,
                    $p->stock,
                    $p->weight ?? '500g',
                    $p->description ?? '',
                    $tagsStr,
                ], ',');
            }

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
     * Export rekap riwayat transaksi pesanan ke format CSV (BOM UTF-8, kompatibel Microsoft Excel)
     */
    public function exportOrders()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        $filename = 'Laporan_Penjualan_IcaFrozenFood_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No Pesanan',
                'Tanggal Transaksi',
                'Channel',
                'Nama Pelanggan',
                'No WhatsApp / HP',
                'Metode Pengantaran',
                'Metode Pembayaran',
                'Status Pesanan',
                'Status Pembayaran',
                'Ringkasan Produk',
                'Subtotal (Rp)',
                'Ongkir / Ice Fee (Rp)',
                'Total Transaksi (Rp)',
            ], ',');

            foreach ($orders as $order) {
                $itemsSummary = [];
                if (is_array($order->items)) {
                    foreach ($order->items as $it) {
                        $name = $it['name'] ?? 'Item';
                        $qty = $it['qty'] ?? 1;
                        $itemsSummary[] = "{$name} ({$qty}x)";
                    }
                }
                $itemStr = implode(' | ', $itemsSummary);

                fputcsv($handle, [
                    $order->id,
                    $order->order_date ?? ($order->created_at ? $order->created_at->format('Y-m-d H:i') : ''),
                    $order->channel ?? 'Online',
                    $order->customer_name,
                    $order->customer_phone ?? '-',
                    $order->method ?? 'Ambil di Toko',
                    $order->payment_method ?? 'QRIS Instan',
                    $order->status ?? 'Diproses',
                    $order->is_paid ? 'Lunas' : 'Belum Lunas',
                    $itemStr,
                    $order->subtotal ?? 0,
                    $order->shipping_fee ?? 0,
                    $order->total ?? 0,
                ], ',');
            }

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

            if ($extension === 'xlsx') {
                $rows = $this->parseXlsxFile($path);
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

        // Header mapping
        $headerRow = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, $rows[0]);

        $colMap = [
            'sku' => -1,
            'name' => -1,
            'category' => -1,
            'price' => -1,
            'stock' => -1,
            'weight' => -1,
            'description' => -1,
        ];

        foreach ($headerRow as $idx => $headerText) {
            $clean = preg_replace('/[^a-z0-9_ ]/', '', $headerText);

            if ($colMap['sku'] === -1 && (
                str_contains($clean, 'sku') || 
                str_contains($clean, 'kode') || 
                str_contains($clean, 'barcode') || 
                $clean === 'id'
            )) {
                $colMap['sku'] = $idx;
            } elseif ($colMap['name'] === -1 && (
                str_contains($clean, 'nama') || 
                str_contains($clean, 'name') || 
                str_contains($clean, 'produk') || 
                str_contains($clean, 'barang') || 
                str_contains($clean, 'item')
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
                str_contains($clean, 'jumlah')
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

        $imported = 0;
        $updated = 0;
        $created = 0;
        $updateExisting = $request->boolean('update_existing', true);

        DB::transaction(function () use ($rows, $colMap, $updateExisting, &$imported, &$updated, &$created) {
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
                $price = (int) preg_replace('/[^0-9]/', '', $priceRaw);

                $stockRaw = $colMap['stock'] !== -1 ? (string)($row[$colMap['stock']] ?? '0') : '0';
                $stock = (int) preg_replace('/[^0-9]/', '', $stockRaw);

                $weight = $colMap['weight'] !== -1 ? trim((string)($row[$colMap['weight']] ?? '500g')) : '500g';
                if ($weight === '') $weight = '500g';

                $desc = $colMap['description'] !== -1 ? trim((string)($row[$colMap['description']] ?? '')) : '';

                $existing = Product::find($sku);
                if ($existing) {
                    if ($updateExisting) {
                        $existing->update([
                            'name' => $name,
                            'category' => $category,
                            'price' => $price > 0 ? $price : $existing->price,
                            'stock' => $stock,
                            'weight' => $weight,
                            'description' => $desc ?: $existing->description,
                        ]);
                        $updated++;
                        $imported++;
                    }
                } else {
                    Product::create([
                        'id' => $sku,
                        'name' => $name,
                        'category' => $category,
                        'price' => $price,
                        'stock' => $stock,
                        'weight' => $weight,
                        'description' => $desc,
                        'image' => '/images/products/nugget.png',
                        'tags' => ['Kasir Pintar'],
                    ]);
                    $created++;
                    $imported++;
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses {$imported} produk ({$created} produk baru, {$updated} diperbarui) dari file spreadsheet!",
            'imported_count' => $imported,
            'created_count' => $created,
            'updated_count' => $updated,
            'products' => Product::all(),
            'categories' => Category::all()->pluck('name'),
        ]);
    }

    /**
     * Parsing file CSV menjadi array baris data
     */
    private function parseCsvFile(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

            while (($data = fgetcsv($handle, 10000, $delimiter)) !== false) {
                if (empty($rows) && isset($data[0])) {
                    $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
                }
                $rows[] = array_map('trim', $data);
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Parsing file Excel .xlsx murni PHP (ZipArchive & SimpleXML) tanpa library pihak ketiga
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
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 1; $i <= 5; $i++) {
                $sheetXml = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
                if ($sheetXml !== false) break;
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

                    $rowArr[] = trim($val);
                    $currentCol++;
                }
                $rows[] = $rowArr;
            }
        }

        $zip->close();
        return $rows;
    }

    // --- 3. Proses Pesanan ---

    // Mengambil riwayat daftar seluruh pesanan pelanggan
    public function getOrders()
    {
        $orders = Order::orderBy('created_at', 'desc')->get()->map(function ($order) {
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
        $id = $request->input('id') ?: ('ORD-ICA-' . rand(1000, 9999));
        $custName = $request->input('customerName') ?: ($request->input('customer_name') ?: 'Pelanggan');
        $custPhone = $request->input('customerPhone') ?: ($request->input('customer_phone') ?: '-');
        $address = $request->input('address') ?: '-';
        $date = $request->input('date') ?: ($request->input('order_date') ?: now()->timezone('Asia/Makassar')->format('Y-m-d H:i'));
        $method = $request->input('method') ?: 'Ambil di Toko / Self Pick-up';
        $payMethod = $request->input('paymentMethod') ?: ($request->input('payment_method') ?: 'QRIS Instan');
        $status = $request->input('status') ?: 'Diproses';
        $items = $request->input('items') ?: [];
        $subtotal = (int) ($request->input('subtotal') ?: 0);
        $shippingFee = (int) ($request->input('shipping_fee') ?: ($request->input('shippingFee') ?: ($request->input('iceFee') ?: ($request->input('ice_fee') ?: 0))));
        $total = (int) ($request->input('total') ?: ($subtotal + $shippingFee));
        $isPaid = $request->has('isPaid') ? $request->boolean('isPaid') : ($request->has('is_paid') ? $request->boolean('is_paid') : false);
        $channel = $request->input('channel') ?: 'Online';

        $order = Order::updateOrCreate(
            ['id' => $id],
            [
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
            ]
        );

        // Deduct stock for each item ordered
        if (is_array($items)) {
            foreach ($items as $item) {
                if (!empty($item['id'])) {
                    $qty = (int) ($item['qty'] ?? 1);
                    $prod = Product::find($item['id']);
                    if ($prod) {
                        $prod->stock = max(0, $prod->stock - $qty);
                        $prod->save();
                    }
                }
            }
        }

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

        $order->status = $validated['status'];
        if ($request->has('is_paid')) {
            $order->is_paid = $request->boolean('is_paid');
        } elseif ($validated['status'] === 'Diproses' || $validated['status'] === 'Selesai') {
            $order->is_paid = true;
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
                'channel'       => $order->channel,
            ],
        ]);
    }
}
