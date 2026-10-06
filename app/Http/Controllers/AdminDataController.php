<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
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
            'message' => 'Kategori berhasil disimpan ke database!',
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
            'message' => 'Kategori berhasil diperbarui di database!',
        ]);
    }

    // Menghapus kategori dari database
    public function destroyCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        Category::where('name', trim($validated['name']))->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus dari database!',
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
            'message' => 'Seluruh kategori berhasil disinkronisasi & disimpan ke database!',
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
            'message' => 'Produk berhasil disimpan ke database!',
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
            'message' => 'Stok produk berhasil diperbarui di database!',
            'product' => $product,
        ]);
    }

    // Menghapus produk dari katalog
    public function destroyProduct($id)
    {
        Product::where('id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari database!',
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
            'message' => 'Seluruh data produk & stok berhasil disimpan ke database!',
            'timestamp' => now()->translatedFormat('H:i:s \W\I\B'),
            'products' => Product::all(),
        ]);
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
            'message' => 'Pesanan berhasil disimpan ke database!',
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
            'message' => "Status pesanan {$id} berhasil diperbarui di database!",
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
