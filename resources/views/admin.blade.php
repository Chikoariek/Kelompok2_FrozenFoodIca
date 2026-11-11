<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ica Frozen Food</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon (Brand Icon Toko di Tab Browser) -->
    <link rel="icon" type="image/png" href="{{ asset('images/ica_logo.png') }}?v={{ filemtime(public_path('images/ica_logo.png')) }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/ica_logo.png') }}?v={{ filemtime(public_path('images/ica_logo.png')) }}">
    <link rel="apple-touch-icon" href="{{ asset('images/ica_logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Pure Vanilla CSS for Admin -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">

    <!-- Chart.js CDN for Visual Sales Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Pencegahan Cache / Tombol Back Browser Setelah Logout -->
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.replace("{{ route('home') }}");
            }
        });
    </script>
</head>
<body>
    @php
        $validTabs = ['dashboard', 'profile', 'orders', 'inventory', 'categories', 'reports'];
        $activeTab = request('tab');
        if (!$activeTab || !in_array($activeTab, $validTabs)) {
            $activeTab = 'dashboard';
        }
        $tabMetaServer = [
            'dashboard' => ['title' => 'Dashboard Overview', 'sub' => 'Ringkasan data operasional, stok freezer, dan transaksi toko'],
            'profile' => ['title' => 'Profil Administrator', 'sub' => 'Kelola informasi akun administrator toko, foto profil, dan kata sandi'],
            'orders' => ['title' => 'Proses Pesanan Pelanggan', 'sub' => 'Pantau transaksi online & kasir walk-in, validasi pembayaran, dan cetak struk kasir.'],
            'inventory' => ['title' => 'Data Produk & Inventaris Freezer', 'sub' => 'Kelola stok freezer, tambah produk baru, dan pantau ketersediaan barang'],
            'categories' => ['title' => 'Manajemen Kategori Produk', 'sub' => 'Kelola master kategori produk untuk mengelompokkan etalase beku'],
            'reports' => ['title' => 'Laporan Penjualan & Analisis Toko', 'sub' => 'Pantau grafik tren omzet pendapatan, performa transaksi harian, dan analisis performa toko'],
        ];
    @endphp

    <!-- Mobile Sidebar Drawer Backdrop -->
    <div id="adminSidebarBackdrop" class="admin-sidebar-backdrop"></div>

    <!-- 1. Sidebar Navigasi Menu Admin -->
    <aside id="adminSidebar" class="admin-sidebar">
        <!-- Logo & Close Button for Mobile -->
        <div class="sidebar-logo-header">
            <!-- Logo Toko (Klik untuk buka Halaman Utama / Homepage) -->
            <a href="{{ route('home') }}" title="Buka Halaman Utama Toko (Homepage)" style="display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('images/ica_logo.png') }}" alt="Ica Frozen Food" class="sidebar-logo-img" onerror="this.style.display='none'">
            </a>
            <button id="btnAdminMobileClose" class="sidebar-mobile-close-btn" title="Tutup Menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- 5 Menu Navigasi Utama Operasional Toko -->
        <nav class="sidebar-nav-menu">
            <button class="sidebar-nav-btn {{ $activeTab === 'dashboard' ? 'active' : '' }}" data-tab="dashboard">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                <span>Dashboard</span>
            </button>
            <button class="sidebar-nav-btn {{ $activeTab === 'orders' ? 'active' : '' }}" data-tab="orders">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>Proses Pesanan</span>
            </button>
            <button class="sidebar-nav-btn {{ $activeTab === 'inventory' ? 'active' : '' }}" data-tab="inventory">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                <span>Data Produk</span>
            </button>
            <button class="sidebar-nav-btn {{ $activeTab === 'categories' ? 'active' : '' }}" data-tab="categories">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/></svg>
                <span>Kategori Produk</span>
            </button>
            <button class="sidebar-nav-btn {{ $activeTab === 'reports' ? 'active' : '' }}" data-tab="reports">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="4" height="12" x="2" y="9" rx="1"/><rect width="4" height="18" x="10" y="3" rx="1"/><rect width="4" height="8" x="18" y="13" rx="1"/></svg>
                <span>Laporan Penjualan</span>
            </button>
        </nav>

        <!-- Sidebar Footer Status Toko (Tombol logout dipindahkan ke menu dropdown profil kanan atas) -->
        <div class="sidebar-footer-info" style="padding: 1.25rem 1.25rem 0; margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.15);">
            <div class="sidebar-footer-row" style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="sidebar-status-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; box-shadow: 0 0 8px #10B981;"></span>
                <span class="sidebar-footer-title" style="font-size: 0.8125rem; font-weight: 800; color: #FFFFFF;">Operasional Aktif</span>
            </div>
            <p class="sidebar-footer-sub" style="font-size: 0.6875rem; color: rgba(255, 255, 255, 0.65); margin: 0.35rem 0 0;">Sistem Ica Frozen Food v1.0</p>
        </div>
    </aside>

    <!-- 2. Area Konten Utama Panel Admin -->
    <div class="admin-main-wrap">
        
        <!-- Global Top Bar Header (Sesuai AdminDashboardPage.jsx) -->
        <header class="admin-topbar">
            <!-- Left: Dynamic Title & Subtitle + Mobile Hamburger -->
            <div class="topbar-left-col">
                <button id="btnAdminMobileToggle" class="topbar-mobile-menu-btn" title="Buka Menu Navigasi">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div>
                    <h1 id="topbarTitle" class="topbar-title-text">{{ $tabMetaServer[$activeTab]['title'] }}</h1>
                    <p id="topbarSubtitle" class="topbar-subtitle-text">{{ $tabMetaServer[$activeTab]['sub'] }}</p>
                </div>
            </div>

            <!-- Right: Date Badge & Admin Profile Dropdown -->
            <div class="topbar-right-col">
                <div class="topbar-date-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span id="currentDateText">
                        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j M Y') }}
                    </span>
                </div>

                <!-- Wadah Menu Profil Admin & Dropdown Melayang -->
                <div class="topbar-profile-container" style="position: relative;">
                    <!-- Tombol Trigger Pembuka Dropdown Profil -->
                    <button id="btnAdminProfileTrigger" class="topbar-profile-trigger" type="button" title="Buka Menu Profil Administrator">
                        <div id="topbarAvatarBox" class="topbar-avatar-circle">
                            @if($user && $user->avatar)
                                <img id="topbarAvatarImg" src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <span id="topbarAvatarInitials">{{ strtoupper(substr($user->name ?? 'AD', 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="topbar-profile-details">
                            <p id="topbarAdminName" class="topbar-profile-name">{{ $user->name ?? 'Administrator' }}</p>
                            <p class="topbar-profile-role">Administrator Toko</p>
                        </div>
                        <svg id="topbarChevron" class="topbar-chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    <!-- Dropdown Melayang (Floating Modal Menu) -->
                    <div id="adminProfileDropdown" class="topbar-profile-dropdown">
                        <div class="dropdown-user-card">
                            <p class="dropdown-user-name">{{ $user->name ?? 'Administrator' }}</p>
                            <p class="dropdown-user-email">{{ $user->email ?? 'admin@icafrozenfood.com' }}</p>
                            <span class="dropdown-badge-pill">Administrator Toko</span>
                        </div>
                        <div class="profile-dropdown-menu">
                            <!-- Link Pintas ke Homepage / Katalog Toko -->
                            <a href="{{ route('home') }}" class="dropdown-menu-item" style="text-decoration: none; color: #0284C7; font-weight: 700;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                <span>Lihat Toko (Homepage)</span>
                            </a>

                            <!-- Link ke Tab Kelola Profil -->
                            <button id="btnSwitchToProfile" class="dropdown-menu-item" type="button">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>Kelola Profil &amp; Password</span>
                            </button>
                            
                            <div class="dropdown-divider"></div>
                            
                            <!-- Tombol Logout Resmi -->
                            <form method="POST" action="{{ route('logout') }}" onsubmit="try{sessionStorage.removeItem('ica_admin_active_tab');}catch(e){}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-menu-item logout" title="Keluar dari sesi admin">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    <span>Keluar (Logout)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Body Scroll Area dengan Scroll Restoration -->
        <main id="adminScrollBody" class="admin-scroll-body">

            <!-- Tab 1: Dashboard Ringkasan & Statistik Toko -->
            <section id="tab-dashboard" class="tab-pane {{ $activeTab === 'dashboard' ? 'active' : '' }}">
                @php
                    // Perhitungan data statistik ringkasan langsung dari database
                    
                    // 1. Total Produk Aktif di Etalase
                    $totalProducts = count($products);
                    $totalCategoriesCount = isset($categories) ? count($categories) : \App\Models\Category::count();

                    // 2. Total Pesanan & Pesanan yang Membutuhkan Tindakan (Diproses / Menunggu)
                    $totalOrders = count($orders);
                    $pendingOrdersCount = 0;
                    $totalRevenue = 0;

                    foreach($orders as $o) {
                        // Pendapatan dihitung dari pesanan yang sudah lunas atau selesai
                        if($o->status === 'Selesai' || $o->is_paid) {
                            $totalRevenue += (int) $o->total;
                        }
                        if($o->status === 'Diproses' || $o->status === 'Menunggu') {
                            $pendingOrdersCount++;
                        }
                    }

                    // 3. Total Pelanggan Riil (Berdasarkan nama pelanggan unik dari riwayat pesanan)
                    $uniqueCustomerNames = $orders->pluck('customer_name')->unique()->filter(function($val) {
                        return !empty($val) && $val !== '-';
                    });
                    $uniqueCustomers = $uniqueCustomerNames->count();
                @endphp

                <!-- Welcome Banner with Polar Bear Illustration -->
                <div class="admin-welcome-banner" style="background-image: url('{{ asset('images/admin_banner.png') }}');">
                    <div class="admin-banner-overlay"></div>
                    <div class="admin-banner-content">
                        <h2 class="admin-banner-headline">
                            Halo, <span id="bannerAdminName">{{ $user->name ?? 'Admin Ica Frozen Food' }}</span> 👋
                        </h2>
                        <p class="admin-banner-sub">
                            Pantau stok freezer, pesanan masuk, dan layanan ramah untuk pelanggan hari ini.
                        </p>
                    </div>
                </div>

                <!-- 4 KPI Stat Cards Dinamis (Menampilkan data riil dari database) -->
                <div class="admin-stats-grid">
                    <!-- Kartu 1: Total Produk Riil -->
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <p class="admin-stat-label">Total Produk</p>
                            <div class="admin-stat-icon-wrap">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                            </div>
                        </div>
                        <div class="admin-stat-body">
                            <p class="admin-stat-value">{{ $totalProducts }}</p>
                            <p class="admin-stat-subtext">{{ $totalCategoriesCount }} Kategori di etalase</p>
                        </div>
                    </div>

                    <!-- Kartu 2: Total Pesanan Riil -->
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <p class="admin-stat-label">Total Pesanan</p>
                            <div class="admin-stat-icon-wrap">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                            </div>
                        </div>
                        <div class="admin-stat-body">
                            <p class="admin-stat-value">{{ $totalOrders }}</p>
                            <p class="admin-stat-subtext">{{ $pendingOrdersCount }} Pesanan aktif</p>
                        </div>
                    </div>

                    <!-- Kartu 3: Total Pelanggan Riil (Berdasarkan data pesanan nyata) -->
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <p class="admin-stat-label">Total Pelanggan</p>
                            <div class="admin-stat-icon-wrap">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                        </div>
                        <div class="admin-stat-body">
                            <p class="admin-stat-value">{{ $uniqueCustomers }}</p>
                            <p class="admin-stat-subtext">Pelanggan bertransaksi</p>
                        </div>
                    </div>

                    <!-- Kartu 4: Total Pendapatan Riil (Pesanan Lunas / Selesai) -->
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <p class="admin-stat-label">Total Pendapatan</p>
                            <div class="admin-stat-icon-wrap">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                            </div>
                        </div>
                        <div class="admin-stat-body">
                            <p class="admin-stat-value long-text">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                            <p class="admin-stat-subtext">Pesanan lunas &amp; selesai</p>
                        </div>
                    </div>
                </div>

                <!-- Main Grid: Pesanan Terbaru (8 cols) + Produk Paling Laris (4 cols) -->
                <div class="admin-overview-grid">
                    <!-- Left: Recent Orders Table -->
                    <div class="admin-card-box">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">PESANAN TERBARU</h3>
                            <button id="btnOverviewSeeAllOrders" class="admin-card-action-link" type="button">
                                <span>Lihat semua pesanan →</span>
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="admin-data-table">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Struk Belanja</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders->take(5) as $order)
                                        @php
                                             $badgeClass = 'menunggu';
                                             $statusLabel = 'Menunggu';
                                             if ($order->status === 'Selesai') {
                                                 $badgeClass = 'selesai';
                                                 $statusLabel = 'Dikirim';
                                             } elseif ($order->status === 'Diproses') {
                                                 $badgeClass = 'diproses';
                                                 $statusLabel = 'Diproses';
                                             }
                                        @endphp
                                        <tr>
                                            <td style="font-family: monospace; color: var(--soft-blue); font-weight: 700;">
                                                {{ $order->id }}
                                            </td>
                                            <td style="font-weight: 600; color: var(--navy);">
                                                {{ $order->customer_name }}
                                            </td>
                                            <td style="font-weight: 800; color: var(--navy);">
                                                Rp {{ number_format($order->total, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <span class="badge-status {{ $badgeClass }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>
                                                <button class="btn-table-receipt btn-open-receipt" data-order-id="{{ $order->id }}" type="button">
                                                    Lihat Struk
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="text-align: center; color: #9CA3AF; padding: 2rem;">
                                                Belum ada transaksi pesanan masuk.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right: Popular Products (Produk Paling Laris) -->
                    <div class="admin-card-box">
                        <div class="admin-card-header">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg>
                                <h3 class="admin-card-title">Produk Paling Laris</h3>
                            </div>
                            <button id="btnOverviewSeeInventory" class="admin-card-action-link" type="button">
                                <span>Katalog →</span>
                            </button>
                        </div>
                        <div class="popular-products-list">
                            @php
                                $productSales = [];
                                foreach ($orders as $ord) {
                                    // Hanya hitung pesanan yang bukan berstatus 'Dibatalkan'
                                    if ($ord->status === 'Dibatalkan') {
                                        continue;
                                    }
                                    $orderItems = is_array($ord->items) ? $ord->items : (json_decode($ord->items, true) ?: []);
                                    if (is_array($orderItems)) {
                                        foreach ($orderItems as $it) {
                                            $name = $it['name'] ?? null;
                                            $qty = (int) ($it['qty'] ?? 1);
                                            if ($name) {
                                                $productSales[$name] = ($productSales[$name] ?? 0) + $qty;
                                            }
                                        }
                                    }
                                }

                                // Urutkan dari produk dengan jumlah terjual terbanyak
                                arsort($productSales);

                                $popularItems = [];
                                $rank = 1;
                                foreach ($productSales as $pName => $totalSold) {
                                    if ($totalSold > 0) {
                                        $popularItems[] = [
                                            'rank' => $rank++,
                                            'name' => $pName,
                                            'sold' => $totalSold,
                                        ];
                                    }
                                    if (count($popularItems) >= 5) break;
                                }
                            @endphp

                            @if(count($popularItems) > 0)
                                @foreach($popularItems as $item)
                                    <div class="popular-product-item">
                                        <span class="popular-rank-badge {{ $item['rank'] === 1 ? 'rank-1' : '' }}">
                                            {{ $item['rank'] }}
                                        </span>
                                        <div class="popular-product-info">
                                            <p class="popular-product-name">{{ $item['name'] }}</p>
                                            <p class="popular-product-stock">{{ $item['sold'] }} pcs terjual</p>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div style="padding: 2.25rem 1rem; text-align: center; color: #94A3B8;">
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.5rem; opacity: 0.6;"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                    <p style="font-size: 0.8125rem; font-weight: 700; color: var(--navy); margin-bottom: 0.25rem;">Belum Ada Produk Terjual</p>
                                    <p style="font-size: 0.6875rem; color: #64748B; margin: 0;">Data produk terlaris akan otomatis terhitung saat ada pesanan masuk.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Diagram Ringkas Penjualan 7 Hari Terakhir (Chart.js) -->
                <div class="admin-card-box" style="margin-top: 1.5rem;">
                    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <div style="width: 32px; height: 32px; border-radius: 10px; background: rgba(103, 125, 158, 0.12); display: flex; align-items: center; justify-content: center; color: var(--soft-blue);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                            <div>
                                <h3 class="admin-card-title" style="margin: 0; font-size: 0.9375rem;">Grafik Ringkasan Tren Omzet Penjualan (7 Hari Terakhir)</h3>
                                <p style="font-size: 0.6875rem; color: #64748B; margin: 2px 0 0;">Visualisasi fluktuasi nilai pendapatan harian transaksi toko</p>
                            </div>
                        </div>
                        <div>
                            <button id="btnOverviewSeeReports" class="admin-card-action-link" type="button" title="Buka menu laporan lengkap">
                                <span>Laporan Lengkap →</span>
                            </button>
                        </div>
                    </div>
                    <div style="padding: 1.25rem; position: relative; min-height: 280px;">
                        <canvas id="dashboardSalesChart" style="max-height: 280px; width: 100%;"></canvas>
                    </div>
                </div>
            </section>


            <!-- Tab 2: Profil & Pengaturan Akun Admin -->
            <section id="tab-profile" class="tab-pane {{ $activeTab === 'profile' ? 'active' : '' }}">
                <!-- Top Header Card (Konsisten dengan Pesanan, Produk & Kategori) -->
                <div class="panel-top-header-card">
                    <div>
                        <div class="panel-header-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Profil Pengelola</span>
                        </div>
                        <h2 class="panel-header-title">Profil Administrator</h2>
                        <p class="panel-header-desc">
                            Kelola informasi akun administrator toko, foto profil, dan kata sandi.
                        </p>
                    </div>
                    <div class="panel-header-actions">
                        <span style="padding: 0.5rem 1rem; border-radius: 12px; background: #F3F4F6; color: var(--navy); font-size: 0.75rem; font-weight: 700; border: 1px solid #E5E7EB; display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>Status: <strong>Administrator Aktif</strong></span>
                        </span>
                    </div>
                </div>

                <!-- Top Profile Banner Card with Avatar -->
                <div class="admin-profile-banner-card">
                    <div class="admin-profile-banner-decor"></div>
                    <div class="admin-profile-banner-flex">
                        <div class="profile-avatar-large-wrap">
                            <div id="profileAvatarLarge" class="profile-avatar-large">
                                @if($user && $user->avatar)
                                    <img id="profileAvatarLargeImg" src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <span id="profileAvatarLargeInitials">{{ strtoupper(substr($user->name ?? 'AD', 0, 2)) }}</span>
                                @endif
                            </div>
                            <button id="btnPencilAvatar" class="btn-avatar-pencil" title="Ganti Foto Profil Admin" type="button">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            </button>
                            <input type="file" id="adminAvatarFileInput" accept="image/*" style="display: none;">
                        </div>

                        <div>
                            <div class="panel-header-badge">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                                <span>Administrator Toko</span>
                            </div>
                            <h2 id="profileBannerAdminName" style="font-size: 1.5rem; font-weight: 900; color: var(--navy); margin-bottom: 0.25rem;">
                                {{ $user->name ?? 'Administrator Toko' }}
                            </h2>
                            <p id="profileBannerAdminEmail" style="font-size: 0.8125rem; color: var(--text-gray-500); margin: 0 0 0.5rem;">
                                {{ $user->email ?? 'admin@icafrozenfood.com' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Forms Grid: Profil Info & Ubah Password -->
                <div class="profile-grid-forms">
                    <!-- Card 1: Informasi Profil Toko -->
                    <div class="profile-card-form">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--navy); margin: 0;">Informasi Profil Toko</h3>
                        </div>

                        <form id="formAdminProfile" method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PATCH')
                            <div class="profile-form-fields">
                                <div class="form-group-item">
                                    <label for="profileName">Nama Lengkap Admin</label>
                                    <input type="text" id="profileName" name="name" value="{{ $user->name ?? '' }}" required>
                                </div>
                                <div class="form-group-item">
                                    <label for="profileEmail">Alamat Email</label>
                                    <input type="email" id="profileEmail" name="email" value="{{ $user->email ?? '' }}" required>
                                </div>
                                <div class="form-group-item">
                                    <label for="profilePhone">No. WhatsApp / Telepon Toko</label>
                                    <input type="text" id="profilePhone" name="phone" value="{{ $user->phone ?? '0878-5451-3770' }}">
                                </div>
                                <div class="form-group-item">
                                    <label for="profileAddress">Alamat Gerai / Toko Fisik</label>
                                    <textarea id="profileAddress" name="address" rows="3">{{ $user->address ?? 'Loktabat Utara, Banjarbaru Utara, Kalimantan Selatan' }}</textarea>
                                </div>
                            </div>
                            <button type="submit" id="btnSubmitProfile" class="btn-primary-blue btn-profile-submit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan Profil</span>
                            </button>
                        </form>
                    </div>

                    <!-- Card 2: Ubah Kata Sandi -->
                    <div class="profile-card-form">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--navy); margin: 0;">Keamanan &amp; Kata Sandi</h3>
                        </div>

                        <form id="formAdminPassword" method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="profile-form-fields">
                                <div class="form-group-item">
                                    <label for="current_password">Kata Sandi Saat Ini</label>
                                    <div class="form-input-with-eye">
                                        <input type="password" id="current_password" name="current_password" required placeholder="Masukkan kata sandi saat ini">
                                        <button type="button" class="btn-pwd-eye" data-target="current_password" aria-label="Lihat / Sembunyikan sandi" title="Lihat / Sembunyikan sandi">
                                            <svg class="eye-open-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg class="eye-off-icon" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group-item">
                                    <label for="password">Kata Sandi Baru</label>
                                    <div class="form-input-with-eye">
                                        <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi baru (min. 8 karakter)">
                                        <button type="button" class="btn-pwd-eye" data-target="password" aria-label="Lihat / Sembunyikan sandi" title="Lihat / Sembunyikan sandi">
                                            <svg class="eye-open-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg class="eye-off-icon" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group-item">
                                    <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                                    <div class="form-input-with-eye">
                                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi baru">
                                        <button type="button" class="btn-pwd-eye" data-target="password_confirmation" aria-label="Lihat / Sembunyikan sandi" title="Lihat / Sembunyikan sandi">
                                            <svg class="eye-open-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg class="eye-off-icon" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" id="btnSubmitPassword" class="btn-primary-blue btn-profile-submit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                <span>Perbarui Kata Sandi</span>
                            </button>
                        </form>
                    </div>
                </div>
            </section>


            <!-- Tab 3: Manajemen & Proses Pesanan Pelanggan -->
            <section id="tab-orders" class="tab-pane {{ $activeTab === 'orders' ? 'active' : '' }}">
                <!-- Top Header Card -->
                <div class="panel-top-header-card">
                    <div>
                        <div class="panel-header-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            <span>Manajemen Transaksi</span>
                        </div>
                        <h2 class="panel-header-title">Proses Pesanan Pelanggan</h2>
                        <p class="panel-header-desc">
                            Pantau transaksi online &amp; kasir walk-in, validasi pembayaran, dan cetak struk kasir.
                        </p>
                    </div>
                    <div class="panel-header-actions">
                        <a href="{{ route('admin.export.orders') }}" id="btnExportOrdersExcel" class="btn-excel-green" title="Download Rekap Transaksi Penjualan Excel/CSV">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="8 13 12 17 16 13"/><line x1="12" y1="17" x2="12" y2="9"/></svg>
                            <span>Export Laporan Penjualan</span>
                        </a>
                        <button id="btnOpenOfflineOrderModal" class="btn-primary-blue" type="button" title="Tambah Pesanan Kasir Walk-in / Offline">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Tambah Pesanan</span>
                        </button>
                        <span class="order-total-header-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Total: <strong id="orderCountBadge">{{ count($orders) }} Pesanan</strong></span>
                        </span>
                    </div>
                </div>

                <!-- Filter & Search Toolbar (Ukuran dan posisi 100% konsisten) -->
                <div class="admin-toolbar-card">
                    <!-- Status Filter Buttons -->
                    <div class="toolbar-filters-group">
                        <span style="color: #9CA3AF; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            Filter:
                        </span>
                        <button class="filter-btn-pill active order-filter-btn" data-status="Semua" type="button">Semua</button>
                        <button class="filter-btn-pill order-filter-btn" data-status="Menunggu Pembayaran" type="button">Menunggu Pembayaran</button>
                        <button class="filter-btn-pill order-filter-btn" data-status="Diproses" type="button">Diproses</button>
                        <button class="filter-btn-pill order-filter-btn" data-status="Selesai" type="button">Selesai</button>
                    </div>

                    <!-- Search Box (Ukuran 20rem konsisten) -->
                    <div class="toolbar-search-wrap">
                        <svg class="toolbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="orderSearchInput" class="toolbar-search-input" placeholder="Cari no. order / nama pelanggan...">
                    </div>
                </div>

                <!-- Orders Table Card -->
                <div class="admin-table-container">
                    <div class="table-responsive">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>NO. ORDER</th>
                                    <th>PELANGGAN</th>
                                    <th>METODE PENGAMBILAN</th>
                                    <th>DAFTAR ITEM</th>
                                    <th>TOTAL BAYAR</th>
                                    <th>STATUS</th>
                                    <th style="text-align: center;">AKSI OPERASIONAL</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                @forelse($orders as $order)
                                    @php
                                        $statusSlug = strtolower(str_replace(' ', '-', $order->status));
                                        $itemsList = is_array($order->items) ? $order->items : json_decode($order->items, true);
                                    @endphp
                                    <tr data-order-row="{{ $order->id }}" data-status="{{ $order->status }}">
                                        <!-- ID & Date -->
                                        <td class="order-id-cell">
                                            <div class="order-id-code">{{ $order->id }}</div>
                                            <span class="order-date-text">
                                                {{ $order->order_date ?? $order->created_at->timezone('Asia/Makassar')->format('Y-m-d H:i') }}
                                            </span>
                                        </td>

                                        <!-- Customer -->
                                        <td>
                                            <p class="order-cust-name">{{ $order->customer_name }}</p>
                                            @php
                                                $rawPhone = $order->customer_phone ?? '';
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                                                if (str_starts_with($cleanPhone, '08')) {
                                                    $cleanPhone = '628' . substr($cleanPhone, 2);
                                                } elseif (str_starts_with($cleanPhone, '8')) {
                                                    $cleanPhone = '62' . $cleanPhone;
                                                }
                                                $isValidPhone = (strlen($cleanPhone) >= 10 && str_starts_with($cleanPhone, '628'));
                                                $waGreeting = "Halo kak " . ($order->customer_name ?: 'Pelanggan') . ", kami dari Ica Frozen Food mengonfirmasi pesanan #" . $order->id . " (" . ($order->status ?? 'Diproses') . "). Apakah ada yang perlu kami bantu?";
                                                $waLink = "https://wa.me/{$cleanPhone}?text=" . rawurlencode($waGreeting);
                                            @endphp
                                            <div class="order-cust-sub-row">
                                                @if($isValidPhone)
                                                    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="order-cust-phone-wa" title="Hubungi via WhatsApp ({{ $order->customer_phone }})">
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="#25D366" style="flex-shrink: 0;"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                        <span>{{ $order->customer_phone }}</span>
                                                    </a>
                                                @else
                                                    <span class="order-cust-phone">{{ $order->customer_phone ?? '-' }}</span>
                                                @endif
                                                <span class="order-channel-badge">{{ $order->channel ?? 'Online' }}</span>
                                            </div>
                                        </td>

                                        <!-- Method -->
                                        <td>
                                            <div class="order-pickup-row">
                                                @if(str_contains(strtolower($order->method ?? ''), 'kurir'))
                                                    <span class="motor-delivery-icon" style="width: 14px; height: 14px; color: var(--soft-blue); display: inline-flex;"></span>
                                                    <span>Kurir Toko</span>
                                                @else
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"/></svg>
                                                    <span>Ambil di Toko</span>
                                                @endif
                                            </div>
                                            <span class="order-paymethod-sub">{{ $order->payment_method ?? 'QRIS Instan' }}</span>
                                        </td>

                                        <!-- Items List Preview -->
                                        <td class="order-items-cell">
                                            @if(!empty($itemsList))
                                                @php
                                                    $itemCount = count($itemsList);
                                                    $initialItems = array_slice($itemsList, 0, 2);
                                                    $moreItems = array_slice($itemsList, 2);
                                                    $moreCount = count($moreItems);
                                                @endphp
                                                <div class="order-items-expandable">
                                                    <div class="order-items-initial">
                                                        @foreach($initialItems as $it)
                                                            <p class="order-item-bullet">• {{ $it['qty'] ?? 1 }}x {{ $it['name'] ?? 'Produk' }}</p>
                                                        @endforeach
                                                    </div>
                                                    @if($moreCount > 0)
                                                        <div class="order-items-more-wrapper">
                                                            <div class="order-items-more-inner">
                                                                @foreach($moreItems as $it)
                                                                    <p class="order-item-bullet">• {{ $it['qty'] ?? 1 }}x {{ $it['name'] ?? 'Produk' }}</p>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-toggle-order-items" data-more-count="{{ $moreCount }}">
                                                            <span class="toggle-items-text">Lihat {{ $moreCount }} item lainnya</span>
                                                            <svg class="toggle-items-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <span style="color: #9CA3AF; font-size: 11px;">• 1x Paket Makanan Beku</span>
                                            @endif
                                        </td>

                                        <!-- Total -->
                                        <td>
                                            <span class="order-total-amount">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                            @if($order->is_paid)
                                                <span class="order-paid-status paid">✓ Lunas</span>
                                            @else
                                                <span class="order-paid-status unpaid">Belum Lunas</span>
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td>
                                            <span class="badge-status-pill status-pill-{{ $statusSlug }} status-badge-target">
                                                {{ $order->status }}
                                            </span>
                                        </td>

                                        <!-- Action Buttons -->
                                        <td style="text-align: center;">
                                            <div class="order-actions-flex">
                                                @if(!$order->is_paid && $order->status === 'Menunggu Pembayaran')
                                                    <button class="btn-action-validate-pay btn-validate-order" data-order-id="{{ $order->id }}" type="button" title="Validasi Pembayaran Pelanggan">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                                        <span>Validasi Bayar</span>
                                                    </button>
                                                @elseif($order->status === 'Diproses')
                                                    <button class="btn-action-ready-order btn-ready-order" data-order-id="{{ $order->id }}" type="button" title="Selesaikan & Tandai Pesanan Siap">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                        <span>Pesanan Siap</span>
                                                    </button>
                                                @endif
                                                <button class="btn-action-print-receipt btn-open-receipt" data-order-id="{{ $order->id }}" type="button" title="Buka & Cetak Struk Belanja">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                                    <span>Cetak Struk</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: #9CA3AF; padding: 2.5rem;">
                                            Tidak ada data pesanan pelanggan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>


            <!-- Tab 4: Inventaris Data Produk & Stok Freezer -->
            <section id="tab-inventory" class="tab-pane {{ $activeTab === 'inventory' ? 'active' : '' }}">
                @php
                    $bundles = $bundles ?? \App\Models\Bundle::with('items.product')->get();
                @endphp

                <!-- Sub-tab 1: Produk Satuan -->
                <div id="subtabContentProducts" class="inv-subtab-content">
                    <!-- Top Header Card -->
                <div class="panel-top-header-card">
                    <div>
                        <div class="panel-header-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                            <span>Inventaris Toko</span>
                        </div>
                        <h2 class="panel-header-title">Data Produk &amp; Stok Freezer</h2>
                        <p class="panel-header-desc">
                            Kelola stok di freezer, pembaruan harga berkala, dan detail kemasan produk.
                        </p>
                    </div>

                    <!-- Action Buttons: Import Excel, Export Excel & Tambah Produk Baru -->
                    <div class="panel-header-actions">
                        <button id="btnOpenImportProductModal" class="btn-import-teal" title="Import Data Excel / CSV dari Kasir Pintar" type="button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>Import Excel</span>
                        </button>
                        <a href="{{ route('admin.export.products') }}" id="btnExportProductsExcel" class="btn-excel-green" title="Export Backup Stok ke Excel/CSV">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span>Export Excel</span>
                        </a>
                        <button id="btnOpenAddProductModal" class="btn-primary-blue" title="Tambah Data Produk Baru" type="button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Tambah Produk Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Filter & Search Toolbar (Opsi Tampilan: Produk Satuan vs Paket Hemat & Filter Stok) -->
                <div class="admin-toolbar-card">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <!-- Segmented Switcher Toggle: Produk Satuan vs Paket Hemat (Style Identik Menu Kategori) -->
                        <div class="category-view-toggle-wrap">
                            <button class="btn-toggle-view active btn-switch-subtab" data-subtab="products" type="button" title="Katalog Produk Satuan">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                                <span>Produk Satuan</span>
                            </button>
                            <button class="btn-toggle-view btn-switch-subtab" data-subtab="bundles" type="button" title="Paket Hemat Promo (Bundling)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/></svg>
                                <span>Paket Hemat</span>
                            </button>
                        </div>

                        <!-- Filter Status Stok Produk Satuan -->
                        <div class="toolbar-filters-group">
                            <button id="filterAllProducts" class="filter-btn-pill active prod-filter-btn" data-filter="all" type="button">
                                Semua ({{ count($products) }})
                            </button>
                            <button id="filterLowStockProducts" class="filter-btn-pill warning prod-filter-btn" data-filter="low" type="button">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                <span>Stok Menipis &lt; 5 Unit</span>
                            </button>
                        </div>
                    </div>

                    <!-- Search Box (Konsisten) -->
                    <div class="toolbar-search-wrap">
                        <svg class="toolbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="inventorySearchInput" class="toolbar-search-input" placeholder="Cari nama produk / kategori / SKU...">
                    </div>
                </div>

                <!-- Products Table Card -->
                <div class="admin-table-container">
                    <div class="table-responsive">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Produk / Foto</th>
                                    <th>Kategori</th>
                                    <th>Harga Jual</th>
                                    <th>Berat</th>
                                    <th>Status Stok</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">
                                @forelse($products as $prod)
                                    @php
                                        $isLow = $prod->stock <= 5 && $prod->stock > 0;
                                        $isZero = $prod->stock <= 0;
                                    @endphp
                                    <tr data-prod-row="{{ $prod->id }}" data-category="{{ $prod->category }}" data-stock="{{ $prod->stock }}">
                                        <!-- Product info -->
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <img src="{{ $prod->image }}" alt="{{ $prod->name }}" style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid #E5E7EB; flex-shrink: 0;" onerror="this.src='{{ asset('images/products/nugget.png') }}'">
                                                <div>
                                                    <p style="font-weight: 800; color: var(--navy); margin: 0; line-height: 1.2;">{{ $prod->name }}</p>
                                                    <p style="font-size: 10px; color: #9CA3AF; font-family: monospace; margin: 2px 0 0;">SKU: {{ $prod->id }}</p>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Category -->
                                        <td style="font-weight: 700; color: var(--soft-blue);">
                                            {{ $prod->category }}
                                        </td>

                                        <!-- Price -->
                                        <td style="font-weight: 900; font-family: monospace; color: var(--navy); font-size: 0.85rem;">
                                            Rp {{ number_format($prod->price, 0, ',', '.') }}
                                        </td>

                                        <!-- Spec / Weight -->
                                        <td>
                                            <span style="color: #4B5563; font-weight: 600;">{{ $prod->weight ?? '500g' }}</span>
                                        </td>

                                        <!-- Stock Stepper & Status Badge -->
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <div class="stock-stepper-wrap">
                                                    <button class="btn-stepper btn-stock-dec" data-id="{{ $prod->id }}" type="button">−</button>
                                                    <span class="stepper-val stock-val-target" data-id="{{ $prod->id }}">{{ $prod->stock }}</span>
                                                    <button class="btn-stepper btn-stock-inc" data-id="{{ $prod->id }}" type="button">+</button>
                                                </div>

                                                @if($isZero)
                                                    <span class="badge-status" style="background: var(--red-bg); color: var(--red);">Habis</span>
                                                @elseif($isLow)
                                                    <span class="badge-status" style="background: var(--amber-bg); color: var(--amber-text);">Menipis</span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Actions -->
                                        <td style="text-align: center;">
                                            <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                                                <button class="btn-table-action edit btn-edit-product" data-id="{{ $prod->id }}" title="Edit Detail Produk" type="button">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                    <span>Edit</span>
                                                </button>
                                                <button class="btn-table-action delete btn-delete-product" data-id="{{ $prod->id }}" title="Hapus Produk" type="button">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #9CA3AF; padding: 2.5rem;">
                                            Belum ada data produk di freezer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                </div> <!-- /#subtabContentProducts -->

                <!-- Sub-tab 2: Paket Hemat Promosi (Bundling) -->
                <div id="subtabContentBundles" class="inv-subtab-content" style="display: none;">
                    <!-- Top Header Card Paket Hemat -->
                    <div class="panel-top-header-card">
                        <div>
                            <div class="panel-header-badge">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/></svg>
                                <span>Inventaris Toko</span>
                            </div>
                            <h2 class="panel-header-title">Manajemen Paket Hemat (Bundling)</h2>
                            <p class="panel-header-desc">
                                Atur promo bundling produk dengan harga spesial relasional. Paket yang aktif otomatis tampil di etalase rekomendasi cerdas homepage.
                            </p>
                        </div>
                        <div class="panel-header-actions">
                            <button id="btnOpenAddBundleModal" class="btn-primary-blue" title="Tambah Paket Hemat Baru" type="button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Tambah Paket Hemat</span>
                            </button>
                        </div>
                    </div>

                    <!-- Toolbar / Search Bundles -->
                    <div class="admin-toolbar-card">
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <!-- Segmented Switcher Toggle: Produk Satuan vs Paket Hemat (Style Identik Menu Kategori) -->
                            <div class="category-view-toggle-wrap">
                                <button class="btn-toggle-view btn-switch-subtab" data-subtab="products" type="button" title="Katalog Produk Satuan">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                                    <span>Produk Satuan</span>
                                </button>
                                <button class="btn-toggle-view active btn-switch-subtab" data-subtab="bundles" type="button" title="Paket Hemat Promo (Bundling)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/></svg>
                                    <span>Paket Hemat</span>
                                </button>
                            </div>

                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--navy); padding-left: 0.5rem; border-left: 1px solid #E5E7EB;">
                                Total: <strong id="totalBundlesCount">{{ count($bundles) }}</strong> Paket Hemat
                            </div>
                        </div>

                        <div class="toolbar-search-wrap">
                            <svg class="toolbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" id="bundleSearchInput" class="toolbar-search-input" placeholder="Cari nama atau ID paket hemat...">
                        </div>
                    </div>

                    <!-- Table List Bundles -->
                    <div class="admin-table-container">
                        <div class="table-responsive">
                            <table class="admin-data-table" id="tableBundles">
                                <thead>
                                    <tr>
                                        <th style="width: 28%;">Paket Promo</th>
                                        <th style="width: 16%;">Badge Label</th>
                                        <th style="width: 26%;">Produk Relasional (Isi Paket)</th>
                                        <th style="width: 14%;">Harga Normal vs Promo</th>
                                        <th style="width: 8%; text-align: center;">Status</th>
                                        <th style="width: 8%; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBundlesBody">
                                    @forelse($bundles as $b)
                                        <tr class="bundle-row-item" data-id="{{ $b->id }}" data-name="{{ strtolower($b->name) }}">
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <img src="{{ $b->image }}" alt="{{ $b->name }}" style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid #E5E7EB; flex-shrink: 0;" onerror="this.src='/images/products/nugget.png'">
                                                    <div>
                                                        <p style="font-weight: 800; color: var(--navy); margin: 0; line-height: 1.2;">{{ $b->name }}</p>
                                                        <p style="font-size: 10px; color: #9CA3AF; font-family: monospace; margin: 2px 0 0;">ID: {{ $b->id }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                    <span style="font-size: 0.72rem; font-weight: 700; background: var(--soft-blue-light, #EBF0F6); color: var(--soft-blue, #677D9E); padding: 2px 8px; border-radius: 6px;">{{ $b->badge_left ?? 'Paket Promo' }}</span>
                                                    @if(!empty($b->badge_right))
                                                        <span style="font-size: 0.72rem; font-weight: 700; background: #F3F4F6; color: var(--navy, #2C3E50); padding: 2px 8px; border-radius: 6px; border: 1px solid #E5E7EB;">{{ $b->badge_right }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                                    @if($b->items && $b->items->isNotEmpty())
                                                        @foreach($b->items as $bi)
                                                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #F8FAFC; color: #4B5563; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; border: 1px solid #E5E7EB;">
                                                                <strong style="color: var(--navy);">{{ $bi->quantity }}x</strong> {{ $bi->product ? $bi->product->name : $bi->product_id }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span style="font-size: 0.75rem; color: #94A3B8; font-style: italic;">Tidak ada produk</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div style="font-size: 0.75rem; color: #9CA3AF; text-decoration: line-through; font-family: monospace;">
                                                        Rp {{ number_format($b->original_price, 0, ',', '.') }}
                                                    </div>
                                                    <div style="font-size: 0.875rem; font-weight: 800; color: var(--navy); font-family: monospace;">
                                                        Rp {{ number_format($b->bundle_price, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <button type="button" class="btn-toggle-bundle-status" data-id="{{ $b->id }}" data-active="{{ $b->is_active ? '1' : '0' }}" title="Klik untuk mengaktifkan/menonaktifkan paket" style="background: none; border: none; cursor: pointer; padding: 4px;">
                                                    @if($b->is_active)
                                                        <span class="badge-status selesai">● Aktif</span>
                                                    @else
                                                        <span class="badge-status" style="background: #F3F4F6; color: #6B7280;">● Nonaktif</span>
                                                    @endif
                                                </button>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                                                    <button class="btn-table-action edit btn-edit-bundle" data-id="{{ $b->id }}" title="Edit Paket Hemat" type="button">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                        <span>Edit</span>
                                                    </button>
                                                    <button class="btn-table-action delete btn-delete-bundle" data-id="{{ $b->id }}" title="Hapus Paket" type="button">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                        <span>Hapus</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: #9CA3AF; padding: 2.5rem;">
                                                Belum ada paket hemat promo. Klik tombol <strong>Tambah Paket Hemat</strong> untuk membuat paket baru.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- /#subtabContentBundles -->
            </section>


            <!-- Tab 5: Master Data Kategori Produk -->
            <section id="tab-categories" class="tab-pane {{ $activeTab === 'categories' ? 'active' : '' }}">
                <!-- Top Header Card (Posisi Tombol & Elemen Konsisten dengan Data Produk) -->
                <div class="panel-top-header-card">
                    <div>
                        <div class="panel-header-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/></svg>
                            <span>Master Data</span>
                        </div>
                        <h2 class="panel-header-title">Manajemen Kategori Produk</h2>
                        <p class="panel-header-desc">
                            Kelola pengelompokan jenis makanan beku pada katalog etalase belanja pelanggan.
                        </p>
                    </div>

                    <!-- Action Buttons: Simpan Perubahan & Tambah Kategori (Persis sejajar seperti Data Produk) -->
                    <div class="panel-header-actions">
                        <button id="btnSyncCategories" class="btn-primary-green" title="Simpan Seluruh Data Kategori ke Server" type="button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                            <span>Simpan Perubahan</span>
                        </button>
                        <button id="btnOpenAddCategoryModal" class="btn-primary-blue" title="Tambah Kategori Baru" type="button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Tambah Kategori Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Filter & Search Toolbar (Opsi Tampilan: List Baris / Card Grid & Pencarian) -->
                <div class="admin-toolbar-card">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <!-- Opsi Pilihan Tampilan: List Baris vs Card Grid -->
                        <div class="category-view-toggle-wrap">
                            <button id="btnCatViewList" class="btn-toggle-view active" type="button" title="Tampilan Tabel / List Baris">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                <span>List Baris</span>
                            </button>
                            <button id="btnCatViewGrid" class="btn-toggle-view" type="button" title="Tampilan Kotak / Card Grid">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                                <span>Card Grid</span>
                            </button>
                        </div>

                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--navy); padding-left: 0.5rem; border-left: 1px solid #E5E7EB;">
                            Total: <strong id="categoriesCountBadge">{{ count($categories) }}</strong> Kategori
                        </div>
                    </div>

                    <div class="toolbar-search-wrap">
                        <svg class="toolbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="categorySearchInput" class="toolbar-search-input" placeholder="Cari nama kategori...">
                    </div>
                </div>

                <!-- 1. Opsi Tampilan List Baris (Table View) -->
                <div id="categoriesListViewContainer" class="admin-table-container">
                    <div class="table-responsive">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th style="width: 8%; text-align: center;">No</th>
                                    <th style="width: 44%;">Nama Kategori</th>
                                    <th style="width: 24%; text-align: center;">Jumlah Produk Terkait</th>
                                    <th style="width: 24%; text-align: center;">Aksi Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                @forelse($categories as $idx => $cat)
                                    @php
                                        $prodCount = $products->where('category', $cat->name)->count();
                                    @endphp
                                    <tr data-cat-row="{{ $cat->name }}" data-cat-name="{{ $cat->name }}">
                                        <td style="text-align: center; font-weight: 700; color: #9CA3AF;">{{ $idx + 1 }}</td>
                                        <td style="font-weight: 800; font-size: 0.875rem; color: var(--navy);">
                                            <span>{{ $cat->name }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.75rem; border-radius: 9999px; background: #EFF6FF; color: var(--soft-blue); font-size: 0.75rem; font-weight: 700;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                                <span>{{ $prodCount }} Produk</span>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                                                <button class="btn-table-action edit btn-edit-category" data-name="{{ $cat->name }}" title="Ubah Nama Kategori" type="button">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                    <span>Edit</span>
                                                </button>
                                                <button class="btn-table-action delete btn-delete-category" data-name="{{ $cat->name }}" title="Hapus Kategori" type="button">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #9CA3AF; padding: 2.5rem;">
                                            Belum ada kategori yang dibuat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Opsi Tampilan Card Grid (Grid View) -->
                <div id="categoriesGridViewContainer" style="display: none; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem;">
                    @forelse($categories as $cat)
                        @php
                            $prodCount = $products->where('category', $cat->name)->count();
                        @endphp
                        <div class="admin-card-box category-item-card" data-cat-name="{{ $cat->name }}" style="padding: 1.25rem;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: #F1F0E8; color: var(--soft-blue); display: flex; align-items: center; justify-content: center;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/></svg>
                                </div>
                                <div style="display: flex; gap: 0.25rem;">
                                    <button class="btn-table-action edit btn-edit-category" data-name="{{ $cat->name }}" title="Edit Kategori" type="button">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button class="btn-table-action delete btn-delete-category" data-name="{{ $cat->name }}" title="Hapus Kategori" type="button">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </div>
                            <h4 style="font-size: 0.95rem; font-weight: 800; color: var(--navy); margin: 0 0 0.25rem;">
                                {{ $cat->name }}
                            </h4>
                            <p style="font-size: 0.72rem; color: var(--soft-blue); font-weight: 600; margin: 0;">
                                {{ $prodCount }} Produk terdaftar
                            </p>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; color: #9CA3AF; padding: 2.5rem;">
                            Belum ada kategori yang dibuat.
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Tab 6: Laporan Penjualan & Analisis Finansial Toko -->
            <section id="tab-reports" class="tab-pane {{ $activeTab === 'reports' ? 'active' : '' }}">
                <!-- Top Header Card -->
                <div class="panel-top-header-card">
                    <div>
                        <div class="panel-header-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            <span>Analisis &amp; Rekapitulasi</span>
                        </div>
                        <h2 class="panel-header-title">Laporan Penjualan &amp; Analisis Toko</h2>
                        <p class="panel-header-desc">
                            Pantau visual grafik tren omzet pendapatan, performa transaksi harian, dan analisis metode pembayaran pelanggan.
                        </p>
                    </div>

                    <!-- Tombol Ekspor Spreadsheet -->
                    <div class="panel-header-actions">
                        <a href="{{ route('admin.export.orders') }}" class="btn-excel-green" title="Download Rekap Transaksi Penjualan Excel/CSV">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="8 13 12 17 16 13"/><line x1="12" y1="17" x2="12" y2="9"/></svg>
                            <span>Export Laporan Penjualan</span>
                        </a>
                    </div>
                </div>

                <!-- Filter Toolbar: Pemilihan Rentang Waktu (7 Hari, 30 Hari, Bulan Ini, Semua) -->
                <div class="admin-toolbar-card">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--navy); margin-right: 0.25rem;">Periode Analisis:</span>
                        <div class="toolbar-filters-group">
                            <button type="button" class="filter-btn-pill active report-range-btn" data-range="7">7 Hari Terakhir</button>
                            <button type="button" class="filter-btn-pill report-range-btn" data-range="30">30 Hari Terakhir</button>
                            <button type="button" class="filter-btn-pill report-range-btn" data-range="month">Bulan Ini</button>
                            <button type="button" class="filter-btn-pill report-range-btn" data-range="all">Semua Waktu</button>
                        </div>
                    </div>
                    <div id="reportActiveRangeText" style="font-size: 0.75rem; font-weight: 700; color: #64748B; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 0.45rem 0.85rem; border-radius: var(--radius-md);">
                        Rentang: Memuat data...
                    </div>
                </div>

                <!-- 3 KPI Cards Finansial Khusus Periode Terpilih -->
                <div class="admin-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 1.5rem;">
                    <!-- Kartu 1: Omzet Penjualan Periode -->
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <p class="admin-stat-label">Total Omzet (Pendapatan)</p>
                            <div class="admin-stat-icon-wrap" style="color: #059669; background: #ECFDF5;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                            </div>
                        </div>
                        <div class="admin-stat-body">
                            <p id="reportTotalRevenue" class="admin-stat-value long-text" style="color: #059669;">Rp 0</p>
                            <p id="reportRevenueSubtext" class="admin-stat-subtext">Dari transaksi lunas/selesai</p>
                        </div>
                    </div>

                    <!-- Kartu 2: Total Transaksi Berhasil -->
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <p class="admin-stat-label">Transaksi Berhasil</p>
                            <div class="admin-stat-icon-wrap" style="color: var(--soft-blue); background: #EFF6FF;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="m9 12 2 2 4-4"/></svg>
                            </div>
                        </div>
                        <div class="admin-stat-body">
                            <p id="reportTotalOrders" class="admin-stat-value">0 Pesanan</p>
                            <p id="reportOrdersSubtext" class="admin-stat-subtext">Tingkat keberhasilan transaksi</p>
                        </div>
                    </div>

                    <!-- Kartu 3: Rata-Rata Nilai Belanja (AOV) -->
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <p class="admin-stat-label">Rata-Rata Belanja (AOV)</p>
                            <div class="admin-stat-icon-wrap" style="color: #7C3AED; background: #F5F3FF;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            </div>
                        </div>
                        <div class="admin-stat-body">
                            <p id="reportAverageOrderValue" class="admin-stat-value long-text">Rp 0</p>
                            <p class="admin-stat-subtext">Rata-rata omzet per transaksi</p>
                        </div>
                    </div>
                </div>

                <!-- Main Card: Grafik Visual Tren Omzet Penjualan (Chart.js) -->
                <div class="admin-card-box" style="margin-bottom: 1.5rem;">
                    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <div style="width: 32px; height: 32px; border-radius: 10px; background: rgba(103, 125, 158, 0.12); display: flex; align-items: center; justify-content: center; color: var(--soft-blue);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                            <div>
                                <h3 class="admin-card-title" style="margin: 0; font-size: 0.9375rem;">Grafik Tren Omzet Pendapatan Harian</h3>
                                <p style="font-size: 0.6875rem; color: #64748B; margin: 2px 0 0;">Visualisasi fluktuasi nilai pendapatan untuk melihat pola hari teramai pesanan</p>
                            </div>
                        </div>
                        <div id="reportChartStatusBadge" style="font-size: 0.75rem; font-weight: 700; color: var(--navy); background: #F1F5F9; border: 1px solid #CBD5E1; padding: 4px 12px; border-radius: 8px;">
                            Memuat grafik...
                        </div>
                    </div>
                    <div style="padding: 1.25rem; position: relative; min-height: 320px;">
                        <canvas id="salesTrendsChart" style="max-height: 320px; width: 100%;"></canvas>
                    </div>
                </div>

                <!-- 2 Analytical Widget Cards (Side-by-side Grid) -->
                <div class="admin-overview-grid" style="margin-bottom: 1.5rem;">
                    <!-- Widget 1: Metode Pembayaran Pelanggan -->
                    <div class="admin-card-box">
                        <div class="admin-card-header">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                <h3 class="admin-card-title">Metode Pembayaran Terpopuler</h3>
                            </div>
                            <span id="reportPaymentTotalCount" style="font-size: 0.6875rem; font-weight: 700; color: #64748B;">0 Transaksi</span>
                        </div>
                        <div id="reportPaymentMethodsList" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                            <!-- Dinamis di-render via JS -->
                        </div>
                    </div>

                    <!-- Widget 2: Kontribusi Kategori Produk Terlaris -->
                    <div class="admin-card-box">
                        <div class="admin-card-header">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/></svg>
                                <h3 class="admin-card-title">Kontribusi Kategori Terlaris</h3>
                            </div>
                            <span id="reportCategoryTotalItems" style="font-size: 0.6875rem; font-weight: 700; color: #64748B;">0 Pcs Terjual</span>
                        </div>
                        <div id="reportCategoriesList" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                            <!-- Dinamis di-render via JS -->
                        </div>
                    </div>
                </div>

                <!-- Riwayat Transaksi Penjualan Terkini Periode Terpilih -->
                <div class="admin-card-box">
                    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="admin-card-title">RINCIAN TRANSAKSI PERIODE TERPILIH</h3>
                        <span id="reportTableCountBadge" style="font-size: 0.725rem; font-weight: 700; color: #64748B;">0 Pesanan</span>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal &amp; Waktu</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Metode Bayar</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Total Omzet</th>
                                </tr>
                            </thead>
                            <tbody id="reportTransactionsTableBody">
                                <!-- Dinamis via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- 3. Kumpulan Modal Aksi (Tambah/Edit Produk, Kategori & Struk) -->

    <!-- Modal Tambah Produk Baru -->
    <div id="modalAddProduct" class="admin-modal-backdrop">
        <div class="admin-modal-box">
            <div class="modal-header-row">
                <h3 class="modal-header-title">Tambah Produk Frozen Food Baru</h3>
                <button type="button" class="btn-modal-close" aria-label="Tutup" title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="formAddProduct">
                <div class="modal-body-pad">
                    <div class="form-group-item">
                        <label for="addProdName">Nama Produk *</label>
                        <input type="text" id="addProdName" required placeholder="Contoh: Dimsum Mentai Bakar 10 pcs">
                    </div>
                    <div class="form-group-item">
                        <label for="addProdCategory">Kategori *</label>
                        <select id="addProdCategory" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group-item">
                            <label for="addProdPrice">Harga Jual (Rp) *</label>
                            <input type="number" id="addProdPrice" required min="1000" placeholder="35000">
                        </div>
                        <div class="form-group-item">
                            <label for="addProdStock">Stok Unit Awal *</label>
                            <input type="number" id="addProdStock" required min="0" value="20">
                        </div>
                    </div>
                    <div class="form-group-item">
                        <label for="addProdWeight">Berat / Porsi Kemasan</label>
                        <input type="text" id="addProdWeight" value="500 gr" placeholder="500 gr">
                    </div>
                    <div class="form-group-item">
                        <label>Foto / Gambar Produk</label>
                        <div class="product-image-uploader-box" id="addProdUploadBox">
                            <!-- Preview Foto Terpilih -->
                            <div class="product-image-preview-wrap" id="addProdPreviewWrap" style="display: none;">
                                <img id="addProdPreviewImg" src="" alt="Preview Produk" class="product-image-preview-thumb">
                                <div class="product-image-preview-info">
                                    <p id="addProdPreviewName" class="product-image-file-name">foto-produk.jpg</p>
                                    <button type="button" class="btn-remove-preview" id="btnRemoveAddProdImg">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        <span>Hapus / Ganti Foto</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Area Dropzone / Tombol Upload File -->
                            <div class="product-image-dropzone" id="addProdDropzone">
                                <input type="file" id="addProdImageFile" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;">
                                <button type="button" class="btn-choose-product-img" id="btnChooseAddProdImg">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    <span>Pilih File Foto Gambar</span>
                                </button>
                                <p class="product-image-hint">Format PNG, JPG, JPEG, WEBP (Maksimal 5MB). Jika belum ada foto, sistem otomatis memakai gambar default.</p>
                            </div>
                            <input type="hidden" id="addProdImage" value="">
                        </div>
                    </div>
                    <div class="form-group-item">
                        <label for="addProdDesc">Deskripsi Ringkas</label>
                        <textarea id="addProdDesc" rows="2" placeholder="Deskripsi olahan dan cara penyajian...">Makanan beku higienis dan praktis siap goreng/kukus.</textarea>
                    </div>
                </div>
                <div class="modal-footer-row">
                    <button type="button" class="btn-modal-cancel">Batal</button>
                    <button type="submit" class="btn-primary-blue">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div id="modalEditProduct" class="admin-modal-backdrop">
        <div class="admin-modal-box">
            <div class="modal-header-row">
                <h3 class="modal-header-title">Edit Detail Produk</h3>
                <button type="button" class="btn-modal-close" aria-label="Tutup" title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="formEditProduct">
                <input type="hidden" id="editProdId">
                <div class="modal-body-pad">
                    <div class="form-group-item">
                        <label for="editProdName">Nama Produk *</label>
                        <input type="text" id="editProdName" required>
                    </div>
                    <div class="form-group-item">
                        <label for="editProdCategory">Kategori *</label>
                        <select id="editProdCategory" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group-item">
                            <label for="editProdPrice">Harga Jual (Rp) *</label>
                            <input type="number" id="editProdPrice" required min="1000">
                        </div>
                        <div class="form-group-item">
                            <label for="editProdStock">Stok Freezer *</label>
                            <input type="number" id="editProdStock" required min="0">
                        </div>
                    </div>
                    <div class="form-group-item">
                        <label for="editProdWeight">Berat / Porsi Kemasan</label>
                        <input type="text" id="editProdWeight">
                    </div>
                    <div class="form-group-item">
                        <label>Foto / Gambar Produk</label>
                        <div class="product-image-uploader-box" id="editProdUploadBox">
                            <!-- Preview Foto Saat Ini / Foto Baru -->
                            <div class="product-image-preview-wrap" id="editProdPreviewWrap">
                                <img id="editProdPreviewImg" src="/images/products/nugget.png" alt="Preview Produk" class="product-image-preview-thumb">
                                <div class="product-image-preview-info">
                                    <p id="editProdPreviewName" class="product-image-file-name">Foto Produk Saat Ini</p>
                                    <input type="file" id="editProdImageFile" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;">
                                    <button type="button" class="btn-choose-product-img" id="btnChooseEditProdImg" style="align-self: flex-start; padding: 4px 10px; font-size: 11px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/></svg>
                                        <span>Ganti Foto Produk</span>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="editProdImage" value="">
                        </div>
                    </div>
                    <div class="form-group-item">
                        <label for="editProdDesc">Deskripsi Ringkas</label>
                        <textarea id="editProdDesc" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer-row">
                    <button type="button" class="btn-modal-cancel">Batal</button>
                    <button type="submit" class="btn-primary-blue">Perbarui Data di Database</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Kategori -->
    <div id="modalAddCategory" class="admin-modal-backdrop">
        <div class="admin-modal-box" style="max-width: 420px;">
            <div class="modal-header-row">
                <h3 class="modal-header-title">Tambah Kategori Baru</h3>
                <button type="button" class="btn-modal-close" aria-label="Tutup" title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="formAddCategory">
                <div class="modal-body-pad">
                    <div class="form-group-item">
                        <label for="addCatName">Nama Kategori *</label>
                        <input type="text" id="addCatName" required placeholder="Contoh: Olahan Kepiting">
                    </div>
                </div>
                <div class="modal-footer-row">
                    <button type="button" class="btn-modal-cancel">Batal</button>
                    <button type="submit" class="btn-primary-blue">Simpan ke Database</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Kategori -->
    <div id="modalEditCategory" class="admin-modal-backdrop">
        <div class="admin-modal-box" style="max-width: 420px;">
            <div class="modal-header-row">
                <h3 class="modal-header-title">Edit Nama Kategori</h3>
                <button type="button" class="btn-modal-close" aria-label="Tutup" title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="formEditCategory">
                <input type="hidden" id="editCatOldName">
                <div class="modal-body-pad">
                    <div class="form-group-item">
                        <label for="editCatNewName">Nama Kategori Baru *</label>
                        <input type="text" id="editCatNewName" required>
                    </div>
                </div>
                <div class="modal-footer-row">
                    <button type="button" class="btn-modal-cancel">Batal</button>
                    <button type="submit" class="btn-primary-blue">Perbarui di Database</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Paket Hemat (Bundling) -->
    <div id="modalAddBundle" class="admin-modal-backdrop">
        <div class="admin-modal-box" style="max-width: 640px;">
            <div class="modal-header-row">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="background: #FEF3C7; color: #D97706; padding: 6px; border-radius: 8px; display: flex;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/></svg>
                    </span>
                    <h3 class="modal-header-title">Tambah Paket Hemat (Bundling) Baru</h3>
                </div>
                <button type="button" class="btn-modal-close" aria-label="Tutup" title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="formAddBundle">
                <div class="modal-body-pad">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.75rem;">
                        <div class="form-group-item">
                            <label for="addBundleName">Nama Paket Hemat *</label>
                            <input type="text" id="addBundleName" required placeholder="Contoh: Paket Sahur Kenyang">
                        </div>
                        <div class="form-group-item">
                            <label for="addBundleBadgeLeft">Label Badge Kiri</label>
                            <input type="text" id="addBundleBadgeLeft" value="Paling Laris" placeholder="Contoh: Paling Laris">
                        </div>
                    </div>

                    <div class="form-group-item">
                        <label for="addBundleDesc">Deskripsi Paket</label>
                        <textarea id="addBundleDesc" rows="2" placeholder="Contoh: Kombinasi nugget renyah + dimsum ayam lembut siap saji keluarga."></textarea>
                    </div>

                    <!-- Pilihan Produk Relasional yang Masuk ke Paket -->
                    <div class="form-group-item">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label style="margin: 0; font-weight: 700;">Pilih Produk Relasional (Isi Paket) *</label>
                            <span style="font-size: 0.75rem; color: #0284C7; font-weight: 700;" id="addBundleCalcNote">Pilih minimal 1 produk</span>
                        </div>
                        <div class="bundle-products-selector" style="border: 1px solid #E2E8F0; border-radius: 8px; max-height: 180px; overflow-y: auto; padding: 0.5rem; background: #F8FAFC;">
                            @foreach($products as $p)
                                <div class="bundle-prod-choice-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.35rem 0.5rem; border-radius: 6px; margin-bottom: 4px; background: #FFFFFF; border: 1px solid #E2E8F0;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0; cursor: pointer; flex: 1; font-weight: 500; font-size: 0.8125rem;">
                                        <input type="checkbox" class="cb-bundle-prod" data-id="{{ $p->id }}" data-price="{{ $p->price }}" style="cursor: pointer; width: 16px; height: 16px; accent-color: #0284C7;">
                                        <img src="{{ $p->image }}" alt="{{ $p->name }}" style="width: 28px; height: 28px; border-radius: 4px; object-fit: cover;" onerror="this.src='/images/products/nugget.png'">
                                        <span>{{ $p->name }}</span>
                                        <span style="font-size: 0.75rem; color: #64748B;">(Rp {{ number_format($p->price, 0, ',', '.') }})</span>
                                    </label>
                                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                                        <span style="font-size: 0.7rem; color: #64748B;">Qty:</span>
                                        <input type="number" class="qty-bundle-prod" data-id="{{ $p->id }}" value="1" min="1" max="99" style="width: 52px; padding: 2px 4px; font-size: 0.75rem; border: 1px solid #CBD5E1; border-radius: 4px; text-align: center;" disabled>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <div class="form-group-item">
                            <label for="addBundleOriginalPrice">Total Harga Normal (Otomatis)</label>
                            <div style="position: relative;">
                                <input type="number" id="addBundleOriginalPrice" readonly style="background: #F1F5F9; font-weight: 700; color: #475569;" placeholder="0">
                                <span style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94A3B8;">Auto-calc</span>
                            </div>
                        </div>
                        <div class="form-group-item">
                            <label for="addBundlePrice">Harga Paket Promo (Rp) *</label>
                            <input type="number" id="addBundlePrice" required placeholder="Contoh: 59000" min="0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem;">
                        <div class="form-group-item">
                            <label for="addBundleBadgeRight">Label Diskon Kanan</label>
                            <input type="text" id="addBundleBadgeRight" placeholder="Kosongkan untuk otomatis dihitung: Hemat Rp X">
                        </div>
                    </div>

                    <!-- Upload Foto Paket -->
                    <div class="form-group-item">
                        <label>Foto Paket Hemat (Opsional)</label>
                        <div class="product-image-uploader-box">
                            <div class="product-image-preview-wrap" id="addBundlePreviewWrap" style="display: none;">
                                <img id="addBundlePreviewImg" src="" alt="Preview Paket" class="product-image-preview-thumb">
                                <div class="product-image-preview-info">
                                    <p id="addBundlePreviewName" class="product-image-file-name">foto-paket.jpg</p>
                                    <button type="button" class="btn-remove-preview" id="btnRemoveAddBundleImg">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        <span>Ganti Foto</span>
                                    </button>
                                </div>
                            </div>
                            <div class="product-image-dropzone" id="addBundleDropzone">
                                <input type="file" id="addBundleImageFile" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;">
                                <button type="button" class="btn-choose-product-img" id="btnChooseAddBundleImg">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    <span>Pilih Foto Gambar Paket</span>
                                </button>
                                <p class="product-image-hint">Format PNG/JPG (Maks 5MB). Jika kosong, menggunakan foto default bundle.</p>
                            </div>
                            <input type="hidden" id="addBundleImage" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer-row">
                    <button type="button" class="btn-modal-cancel">Batal</button>
                    <button type="submit" class="btn-primary-blue">Simpan Paket</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Paket Hemat (Bundling) -->
    <div id="modalEditBundle" class="admin-modal-backdrop">
        <div class="admin-modal-box" style="max-width: 640px;">
            <div class="modal-header-row">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="background: #DBEAFE; color: #1D4ED8; padding: 6px; border-radius: 8px; display: flex;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </span>
                    <h3 class="modal-header-title">Edit Paket Hemat (Bundling)</h3>
                </div>
                <button type="button" class="btn-modal-close" aria-label="Tutup" title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="formEditBundle">
                <input type="hidden" id="editBundleId">
                <div class="modal-body-pad">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.75rem;">
                        <div class="form-group-item">
                            <label for="editBundleName">Nama Paket Hemat *</label>
                            <input type="text" id="editBundleName" required>
                        </div>
                        <div class="form-group-item">
                            <label for="editBundleBadgeLeft">Label Badge Kiri</label>
                            <input type="text" id="editBundleBadgeLeft">
                        </div>
                    </div>

                    <div class="form-group-item">
                        <label for="editBundleDesc">Deskripsi Paket</label>
                        <textarea id="editBundleDesc" rows="2"></textarea>
                    </div>

                    <!-- Pilihan Produk Relasional yang Masuk ke Paket -->
                    <div class="form-group-item">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label style="margin: 0; font-weight: 700;">Pilih Produk Relasional (Isi Paket) *</label>
                            <span style="font-size: 0.75rem; color: #0284C7; font-weight: 700;" id="editBundleCalcNote">Pilih minimal 1 produk</span>
                        </div>
                        <div class="bundle-products-selector" id="editBundleProductsContainer" style="border: 1px solid #E2E8F0; border-radius: 8px; max-height: 180px; overflow-y: auto; padding: 0.5rem; background: #F8FAFC;">
                            @foreach($products as $p)
                                <div class="bundle-prod-choice-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.35rem 0.5rem; border-radius: 6px; margin-bottom: 4px; background: #FFFFFF; border: 1px solid #E2E8F0;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0; cursor: pointer; flex: 1; font-weight: 500; font-size: 0.8125rem;">
                                        <input type="checkbox" class="cb-edit-bundle-prod" data-id="{{ $p->id }}" data-price="{{ $p->price }}" style="cursor: pointer; width: 16px; height: 16px; accent-color: #0284C7;">
                                        <img src="{{ $p->image }}" alt="{{ $p->name }}" style="width: 28px; height: 28px; border-radius: 4px; object-fit: cover;" onerror="this.src='/images/products/nugget.png'">
                                        <span>{{ $p->name }}</span>
                                        <span style="font-size: 0.75rem; color: #64748B;">(Rp {{ number_format($p->price, 0, ',', '.') }})</span>
                                    </label>
                                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                                        <span style="font-size: 0.7rem; color: #64748B;">Qty:</span>
                                        <input type="number" class="qty-edit-bundle-prod" data-id="{{ $p->id }}" value="1" min="1" max="99" style="width: 52px; padding: 2px 4px; font-size: 0.75rem; border: 1px solid #CBD5E1; border-radius: 4px; text-align: center;" disabled>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <div class="form-group-item">
                            <label for="editBundleOriginalPrice">Total Harga Normal (Otomatis)</label>
                            <div style="position: relative;">
                                <input type="number" id="editBundleOriginalPrice" readonly style="background: #F1F5F9; font-weight: 700; color: #475569;">
                                <span style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94A3B8;">Auto-calc</span>
                            </div>
                        </div>
                        <div class="form-group-item">
                            <label for="editBundlePrice">Harga Paket Promo (Rp) *</label>
                            <input type="number" id="editBundlePrice" required min="0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem;">
                        <div class="form-group-item">
                            <label for="editBundleBadgeRight">Label Diskon Kanan</label>
                            <input type="text" id="editBundleBadgeRight" placeholder="Kosongkan untuk otomatis dihitung: Hemat Rp X">
                        </div>
                    </div>

                    <!-- Upload Foto Paket -->
                    <div class="form-group-item">
                        <label>Foto Paket Hemat</label>
                        <div class="product-image-uploader-box">
                            <div class="product-image-preview-wrap" id="editBundlePreviewWrap" style="display: flex;">
                                <img id="editBundlePreviewImg" src="" alt="Preview Paket" class="product-image-preview-thumb">
                                <div class="product-image-preview-info">
                                    <p id="editBundlePreviewName" class="product-image-file-name">foto-paket.jpg</p>
                                    <input type="file" id="editBundleImageFile" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;">
                                    <button type="button" class="btn-choose-product-img" id="btnChooseEditBundleImg" style="align-self: flex-start; padding: 4px 10px; font-size: 11px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/></svg>
                                        <span>Ganti Foto Paket</span>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="editBundleImage" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer-row">
                    <button type="button" class="btn-modal-cancel">Batal</button>
                    <button type="submit" class="btn-primary-blue">Perbarui Paket di Database</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Official Thermal Receipt Modal (ReceiptModal.jsx) -->
    <div id="modalReceipt" class="admin-modal-backdrop">
        <div class="receipt-paper-box">
            <div class="receipt-header-center">
                <h2 style="font-size: 1rem; font-weight: 900; margin: 0; color: #111827;">ICA FROZEN FOOD</h2>
                <p style="font-size: 10px; margin: 2px 0; color: #4B5563;">Makanan Beku Higienis &amp; Halal</p>
                <p style="font-size: 10px; margin: 0; color: #4B5563;">Loktabat Utara, Banjarbaru Utara, Kalimantan Selatan</p>
                <p style="font-size: 10px; margin: 0; color: #4B5563;">WhatsApp: 0878-5451-3770</p>
            </div>

            <div style="font-size: 11px; margin-bottom: 0.5rem; line-height: 1.4;">
                <div>No. Nota: <strong id="receiptOrderId">-</strong></div>
                <div>Tanggal : <span id="receiptOrderDate">-</span></div>
                <div>Kasir   : <span id="receiptCashier">{{ $user->name ?? 'Admin Toko' }}</span></div>
                <div>Customer: <strong id="receiptCustName">-</strong></div>
                <div>Metode  : <span id="receiptMethod">-</span></div>
            </div>

            <div class="receipt-divider-dash"></div>

            <table class="receipt-items-table">
                <thead>
                    <tr style="border-bottom: 1px dashed #9CA3AF;">
                        <th style="text-align: left;">Item</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody id="receiptItemsBody">
                    <!-- Dynamic -->
                </tbody>
            </table>

            <div class="receipt-divider-dash"></div>

            <div style="font-size: 11px; line-height: 1.5;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Subtotal:</span>
                    <span id="receiptSubtotal">Rp 0</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Ongkos Kirim:</span>
                    <span id="receiptIceFee">Rp 0</span>
                </div>
                <div class="receipt-total-row">
                    <span>TOTAL BAYAR:</span>
                    <span id="receiptGrandTotal">Rp 0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                    <span>Status Bayar:</span>
                    <strong id="receiptPayStatus" style="color: #059669;">LUNAS (CASH/TRANSFER)</strong>
                </div>
            </div>

            <div class="receipt-divider-dash"></div>

            <div style="text-align: center; font-size: 10px; color: #4B5563; margin-top: 0.75rem;">
                <p style="margin: 0;">Terima kasih telah berbelanja!</p>
                <p style="margin: 2px 0 0;">Simpan struk ini sebagai bukti pembayaran resmi.</p>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" class="btn-modal-cancel" style="flex: 1;" onclick="document.getElementById('modalReceipt').classList.remove('open')">Tutup</button>
                <button type="button" class="btn-primary-blue" style="flex: 1; justify-content: center;" onclick="window.print()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    <span>Cetak Struk</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Impor Data Produk dari Excel / CSV (Kasir Pintar) -->
    <div id="modalImportExcel" class="admin-modal-backdrop">
        <div class="admin-modal-box" style="max-width: 540px;">
            <div class="modal-header-row">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #DCFCE7; color: #166534; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <div>
                        <h3 class="modal-title" style="font-size: 1.05rem;">Impor Data Produk (Kasir Pintar)</h3>
                        <p style="font-size: 0.75rem; color: #64748B; margin: 0;">Sinkronisasi etalase &amp; stok freezer dari file spreadsheet.</p>
                    </div>
                </div>
                <button type="button" class="btn-modal-close btn-close-import-modal" aria-label="Tutup" title="Tutup Modal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Petunjuk Format Kasir Pintar -->
            <div class="import-format-banner">
                <div class="import-format-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Kompatibel dengan Format Kasir Pintar Pro &amp; Excel</span>
                </div>
                <p class="import-format-desc">
                    Sistem mengenali kolom: <em>Kode Barang/SKU, Nama Produk, Kategori, Harga Jual, Stok</em>, dan <em>Berat</em>. Kategori baru akan otomatis ditambahkan ke toko.
                </p>
                <a href="{{ route('admin.export.template') }}" class="import-template-pill-link">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Unduh Contoh Template CSV</span>
                </a>
            </div>

            <form id="formImportExcel" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body-pad" style="padding-top: 0;">
                    <!-- Area Upload Dropzone -->
                    <div id="importDropzone" class="import-dropzone">
                        <input type="file" id="importFileInput" name="file" accept=".xlsx,.xls,.csv" style="display: none;" required>
                        <div class="import-dropzone-icon-circle">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <h4 class="import-dropzone-title">Pilih file Excel (.xlsx) atau CSV dari Kasir Pintar</h4>
                        <p class="import-dropzone-subtitle">Klik area ini atau seret file Anda ke sini (Maks. 10MB)</p>
                        <span class="import-dropzone-limit-badge">Format Didukung: .xlsx, .xls, .csv</span>
                    </div>

                    <!-- Box Preview File Terpilih -->
                    <div id="importFilePreview" class="import-file-selected-box" style="display: none;">
                        <div class="import-file-info-col">
                            <div class="import-excel-file-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div style="min-width: 0;">
                                <p id="importFileName" class="import-file-name-text">file_kasir_pintar.xlsx</p>
                                <div class="import-file-meta-row">
                                    <span id="importFileSize" class="import-file-size-text">0 KB</span>
                                    <span class="import-file-ready-pill">Siap Diimpor</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="btnRemoveImportFile" class="btn-remove-import-file" title="Ganti File">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            <span>Batal</span>
                        </button>
                    </div>

                    <div class="import-sync-option-row">
                        <label class="import-sync-label">
                            <input type="checkbox" name="update_existing" value="1" checked>
                            <span>Perbarui harga &amp; stok jika produk/SKU sudah ada di database</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer-row">
                    <button type="button" class="btn-modal-cancel btn-close-import-modal">Batal</button>
                    <button type="submit" id="btnSubmitImport" class="btn-primary-blue" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span>Mulai Impor Produk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Kasir Walk-in / Tambah Pesanan Offline -->
    <div id="modalOfflineOrder" class="admin-modal-backdrop">
        <div class="admin-modal-box pos-modal-wide">
            <!-- Topbar Header -->
            <div class="pos-topbar-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div class="pos-header-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <div>
                        <h3 class="pos-header-title">Kasir POS - Tambah Pesanan Walk-in</h3>
                        <p class="pos-header-sub">Catat transaksi langsung pelanggan toko, potong stok otomatis, dan cetak struk kasir.</p>
                    </div>
                </div>
                <button type="button" class="btn-modal-close" onclick="document.getElementById('modalOfflineOrder').classList.remove('open')" aria-label="Tutup" title="Tutup Modal Kasir">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Main POS Split: Left (Katalog Produk) & Right (Keranjang & Checkout) -->
            <div class="pos-main-split">
                <!-- SISI KIRI: KATALOG PRODUK FREEZER -->
                <div class="pos-catalog-col">
                    <!-- Toolbar Filter & Search -->
                    <div class="pos-catalog-toolbar">
                        <div class="pos-search-box">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" id="posSearchInput" placeholder="Cari nama produk, SKU, atau kategori...">
                            <button type="button" id="btnClearPosSearch" class="pos-btn-clear-search" style="display: none;" title="Hapus pencarian">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        <div class="pos-category-container">
                            <div class="pos-category-scroll" id="posCategoryScroll">
                                <button type="button" class="pos-cat-chip active" data-cat="all">
                                    <span>Semua</span>
                                    <span class="pos-cat-count">{{ count($products) }}</span>
                                </button>
                                @foreach($categories as $cat)
                                    @php
                                        $catCount = $products->where('category', $cat->name)->count();
                                    @endphp
                                    <button type="button" class="pos-cat-chip" data-cat="{{ $cat->name }}">
                                        <span>{{ $cat->name }}</span>
                                        <span class="pos-cat-count">{{ $catCount }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <button type="button" id="btnPosToggleAllCategories" class="pos-cat-toggle-btn" title="Tampilkan semua kategori">
                                <svg class="cat-grid-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="7" height="7" x="3" y="3" rx="1.5"/><rect width="7" height="7" x="14" y="3" rx="1.5"/><rect width="7" height="7" x="14" y="14" rx="1.5"/><rect width="7" height="7" x="3" y="14" rx="1.5"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Grid Card Produk -->
                    <div class="pos-products-grid" id="posProductsGrid"></div>
                </div>

                <!-- SISI KANAN: KERANJANG KASIR & CHECKOUT -->
                <div class="pos-cart-col">
                    <!-- Cart Header -->
                    <div class="pos-cart-header">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 0.875rem; font-weight: 800; color: var(--navy);">Keranjang Pesanan</span>
                            <span id="posCartItemCountBadge" class="pos-cart-count-pill">0 Item</span>
                        </div>
                        <button type="button" id="btnResetPosCart" class="pos-btn-reset-cart" title="Kosongkan Keranjang">Kosongkan</button>
                    </div>

                    <!-- Customer Info Input -->
                    <div class="pos-customer-box">
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="posCustName" class="pos-cust-input" placeholder="Nama Pelanggan (opsional)" value="Pelanggan Toko (Walk-in)">
                            <input type="text" id="posCustPhone" class="pos-cust-input" placeholder="No. WhatsApp / HP (opsional)">
                        </div>
                    </div>

                    <!-- Cart Item Rows List -->
                    <div class="pos-cart-items-wrapper" id="posCartItemsWrapper">
                        <div class="pos-cart-empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.5rem; color: #CBD5E1;"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                            <p style="font-size: 0.8125rem; font-weight: 700; margin: 0; color: #64748B;">Keranjang Kasir Kosong</p>
                            <p style="font-size: 11px; margin: 4px 0 0; color: #94A3B8;">Klik produk freezer di sebelah kiri untuk menambahkan ke pesanan.</p>
                        </div>
                    </div>

                    <!-- Total & Checkout Form -->
                    <div class="pos-checkout-panel">
                        <div class="pos-summary-row" style="margin-bottom: 0.6rem;">
                            <span style="font-size: 0.8125rem; font-weight: 700; color: #64748B;">Total Tagihan Kasir:</span>
                            <span id="posGrandTotalDisplay" style="font-size: 1.25rem; font-weight: 900; color: var(--navy);">Rp 0</span>
                        </div>

                        <!-- Payment Method Toggle -->
                        <div style="margin-bottom: 0.6rem;">
                            <label style="font-size: 11px; font-weight: 700; color: #64748B; display: block; margin-bottom: 0.35rem;">Metode Pembayaran:</label>
                            <div class="pos-payment-method-tabs" id="posPaymentMethodTabs">
                                <button type="button" class="pos-pay-tab active" data-method="Tunai (Cash)">Tunai (Cash)</button>
                                <button type="button" class="pos-pay-tab" data-method="QRIS Instan">QRIS Instan</button>
                                <button type="button" class="pos-pay-tab" data-method="Transfer BCA">Transfer BCA</button>
                                <button type="button" class="pos-pay-tab" data-method="Kartu Debit">Kartu Debit</button>
                            </div>
                        </div>

                        <!-- Cash Payment Calculator (Hanya muncul bila metode Tunai) -->
                        <div id="posCashCalculationBox" class="pos-cash-calc-box">
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <div style="flex: 1;">
                                    <label style="font-size: 10px; font-weight: 700; color: #64748B; display: block; margin-bottom: 2px;">Uang Diterima (Rp):</label>
                                    <input type="number" id="posCashReceived" class="pos-cash-input" placeholder="0">
                                </div>
                                <div style="flex: 1; text-align: right;">
                                    <span style="font-size: 10px; font-weight: 700; color: #64748B; display: block; margin-bottom: 2px;">Kembalian:</span>
                                    <strong id="posCashChangeDisplay" style="font-size: 0.95rem; font-weight: 800; color: #059669;">Rp 0</strong>
                                </div>
                            </div>
                            <!-- Quick Cash Pills -->
                            <div class="pos-quick-cash-row">
                                <button type="button" class="pos-btn-quick-cash" data-cash="exact">Uang Pas</button>
                                <button type="button" class="pos-btn-quick-cash" data-cash="50000">50.000</button>
                                <button type="button" class="pos-btn-quick-cash" data-cash="100000">100.000</button>
                                <button type="button" class="pos-btn-quick-cash" data-cash="200000">200.000</button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                            <button type="button" class="btn-modal-cancel" style="padding: 0.65rem 1rem;" onclick="document.getElementById('modalOfflineOrder').classList.remove('open')">Batal</button>
                            <button type="button" id="btnSubmitPosOrder" class="btn-primary-blue pos-btn-finish" style="flex: 1; justify-content: center; padding: 0.65rem 1rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Selesaikan &amp; Cetak Struk</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.__BASE_URL__ = "{{ url('/') }}";
        window.__API_URL__ = "{{ url('api') }}";
        window.__ADMIN_PRODUCTS__ = @json($products ?? \App\Models\Product::all());
        window.__ADMIN_BUNDLES__ = @json($bundles ?? \App\Models\Bundle::with('items.product')->get());
        window.__ADMIN_CATEGORIES__ = @json(isset($categories) ? (is_array($categories) ? $categories : $categories->pluck('name')) : \App\Models\Category::pluck('name'));
        window.__ADMIN_ORDERS__ = @json($orders ?? \App\Models\Order::orderBy('created_at', 'desc')->get());
        window.__ADMIN_USER__ = @json($user ?? \Illuminate\Support\Facades\Auth::user());
        window.__EXAM_MODE__ = {{ env('EXAM_MODE', true) ? 'true' : 'false' }};
    </script>

    <!-- Pure Vanilla JS for Admin -->
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
</body>
</html>