<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ica Frozen Food</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/ica_logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Pure Vanilla CSS for Admin -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>

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

        <!-- 5 Menu Navigasi -->
        <nav class="sidebar-nav-menu">
            <button class="sidebar-nav-btn active" data-tab="dashboard">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                <span>Dashboard</span>
            </button>
            <button class="sidebar-nav-btn" data-tab="profile">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Profil Admin</span>
            </button>
            <button class="sidebar-nav-btn" data-tab="orders">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>Proses Pesanan</span>
            </button>
            <button class="sidebar-nav-btn" data-tab="inventory">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                <span>Data Produk</span>
            </button>
            <button class="sidebar-nav-btn" data-tab="categories">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/></svg>
                <span>Kategori Produk</span>
            </button>
        </nav>

        <!-- Sidebar Footer Status Toko (Tombol logout dipindahkan ke menu dropdown profil kanan atas) -->
        <div class="sidebar-footer-info" style="padding: 1.25rem 1.25rem 0; margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.15);">
            <div class="sidebar-footer-row" style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="sidebar-status-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; box-shadow: 0 0 8px #10B981;"></span>
                <span class="sidebar-footer-title" style="font-size: 0.8125rem; font-weight: 800; color: #FFFFFF;">Toko Online Aktif</span>
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
                    <h1 id="topbarTitle" class="topbar-title-text">Dashboard Overview</h1>
                    <p id="topbarSubtitle" class="topbar-subtitle-text">Ringkasan data operasional, stok freezer, dan transaksi toko</p>
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
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
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
            <section id="tab-dashboard" class="tab-pane active">
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
                            Selamat bertugas, Tim Ica Frozen Food! Pantau stok freezer, pesanan masuk, dan layanan ramah untuk pelanggan hari ini.
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
                                        <th>Struk / Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders->take(7) as $order)
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
            </section>


            <!-- Tab 2: Profil & Pengaturan Akun Admin -->
            <section id="tab-profile" class="tab-pane">
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
                                <span>Simpan Profil Admin</span>
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
                                    <input type="password" id="current_password" name="current_password" required placeholder="Masukkan kata sandi saat ini">
                                </div>
                                <div class="form-group-item">
                                    <label for="password">Kata Sandi Baru</label>
                                    <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi baru (min. 8 karakter)">
                                </div>
                                <div class="form-group-item">
                                    <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi baru">
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
            <section id="tab-orders" class="tab-pane">
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
                        <span style="padding: 0.5rem 1rem; border-radius: 12px; background: #F3F4F6; color: var(--navy); font-size: 0.75rem; font-weight: 700; border: 1px solid #E5E7EB; display: flex; align-items: center; gap: 0.5rem;">
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
                                            <p class="order-cust-phone">{{ $order->customer_phone ?? '-' }}</p>
                                            <span class="order-channel-badge">{{ $order->channel ?? 'Online' }}</span>
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
                                                <div class="order-items-list-wrap">
                                                    @foreach($itemsList as $it)
                                                        <p class="order-item-bullet">• {{ $it['qty'] ?? 1 }}x {{ $it['name'] ?? 'Produk' }}</p>
                                                    @endforeach
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
            <section id="tab-inventory" class="tab-pane">
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

                    <!-- Action Buttons: Simpan Perubahan & Tambah Produk (Posisi Konsisten) -->
                    <div class="panel-header-actions">
                        <button id="btnSyncProducts" class="btn-primary-green" title="Simpan Seluruh Data Produk ke Server" type="button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                            <span>Simpan Perubahan</span>
                        </button>
                        <button id="btnOpenAddProductModal" class="btn-primary-blue" title="Tambah Data Produk Baru" type="button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Tambah Produk Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Filter & Search Toolbar (Posisi & Lebar Search Konsisten: sm:w-80) -->
                <div class="admin-toolbar-card">
                    <div class="toolbar-filters-group">
                        <button id="filterAllProducts" class="filter-btn-pill active prod-filter-btn" data-filter="all" type="button">
                            Semua Produk ({{ count($products) }})
                        </button>
                        <button id="filterLowStockProducts" class="filter-btn-pill warning prod-filter-btn" data-filter="low" type="button">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>Stok Menipis &lt; 5 Unit</span>
                        </button>
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
            </section>


            <!-- Tab 5: Master Data Kategori Produk -->
            <section id="tab-categories" class="tab-pane">
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

        </main>
    </div>

    <!-- 3. Kumpulan Modal Aksi (Tambah/Edit Produk, Kategori & Struk) -->

    <!-- Modal Tambah Produk Baru -->
    <div id="modalAddProduct" class="admin-modal-backdrop">
        <div class="admin-modal-box">
            <div class="modal-header-row">
                <h3 class="modal-header-title">Tambah Produk Frozen Food Baru</h3>
                <button class="btn-modal-close" style="color: #9CA3AF; font-size: 1.25rem;" type="button">✕</button>
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
                    <button type="submit" class="btn-primary-blue">Simpan Produk ke Database</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div id="modalEditProduct" class="admin-modal-backdrop">
        <div class="admin-modal-box">
            <div class="modal-header-row">
                <h3 class="modal-header-title">Edit Detail Produk</h3>
                <button class="btn-modal-close" style="color: #9CA3AF; font-size: 1.25rem;" type="button">✕</button>
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
                <button class="btn-modal-close" style="color: #9CA3AF; font-size: 1.25rem;" type="button">✕</button>
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
                <button class="btn-modal-close" style="color: #9CA3AF; font-size: 1.25rem;" type="button">✕</button>
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

    <!-- Initial Data for Vanilla JS with Base and API URL -->
    <script>
        window.__BASE_URL__ = "{{ url('/') }}";
        window.__API_URL__ = "{{ url('api') }}";
        window.__ADMIN_PRODUCTS__ = @json($products ?? \App\Models\Product::all());
        window.__ADMIN_CATEGORIES__ = @json(isset($categories) ? (is_array($categories) ? $categories : $categories->pluck('name')) : \App\Models\Category::pluck('name'));
        window.__ADMIN_ORDERS__ = @json($orders ?? \App\Models\Order::orderBy('created_at', 'desc')->get());
        window.__ADMIN_USER__ = @json($user ?? \Illuminate\Support\Facades\Auth::user());
        window.__EXAM_MODE__ = {{ env('EXAM_MODE', true) ? 'true' : 'false' }};
    </script>

    <!-- Pure Vanilla JS for Admin -->
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
</body>
</html>