@php
    $products = $products ?? \App\Models\Product::all();
    $categories = $categories ?? \App\Models\Category::all();
    $user = $user ?? \Illuminate\Support\Facades\Auth::user();
    $isLoggedIn = $isLoggedIn ?? \Illuminate\Support\Facades\Auth::check();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ica Frozen Food - Makanan Beku Higienis &amp; Praktis untuk Keluarga</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="Ica Frozen Food - Pilihan makanan beku higienis, lezat, dan praktis untuk keluarga Indonesia. Dimsum, nugget, olahan seafood, dan daging slice pilihan.">
    <meta name="keywords" content="frozen food, makanan beku, nugget, dimsum, otak-otak, banjarbaru, ica frozen food">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/ica_logo.png">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Pure Vanilla CSS Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
</head>

<body>

    <!-- 1. Banner Info Pengantaran Kurir & Jam Operasional Toko -->
    <div class="top-announcement-bar">
        <span class="motor-delivery-icon" style="width: 1rem; height: 1rem; color: #6EE7B7;"></span>
        <span>Buka Setiap Hari (08.00 - 21.00 WITA) • Bisa diantar oleh kurir langsung ke rumah anda!</span>
        <span class="top-announcement-badge">Banjarbaru</span>
    </div>

    <!-- 2. Navbar Navigasi Utama & Menu Profil Pengguna -->
    <header class="navbar-header-sticky">
        <div id="navbarWrapper" class="navbar-outer-wrapper">
            <div class="navbar-inner-box">
                <div class="navbar-flex-row">

                    <!-- Brand Logo -->
                    <a href="{{ route('home') }}" class="brand-link-wrap">
                        <div class="brand-logo-box">
                            <img src="/images/ica_logo.png" alt="Ica Frozen Food Logo" class="brand-logo-img"
                                onerror="this.style.display='none'">
                        </div>
                        <div class="brand-text-col">
                            <span class="brand-title">Ica Frozen Food</span>
                            <p id="brandSubtitle" class="brand-subtitle">Makanan Beku Higienis &amp; Praktis</p>
                        </div>
                    </a>

                    <!-- Desktop Search Bar -->
                    <div class="desktop-search-wrap">
                        <div class="search-input-box">
                            <input type="text" id="searchInput" class="search-input-field" placeholder="Cari produk...">
                            <svg class="search-icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <button id="btnClearSearch" class="search-clear-btn" style="display: none;"
                                title="Hapus pencarian">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Navigation Links -->
                    <nav class="desktop-nav-menu">
                        <button class="nav-item-btn active" data-target="home">Home</button>
                        <button class="nav-item-btn" data-target="katalog-section">Katalog</button>
                        <button class="nav-item-btn" data-target="rekomendasi-section">Paket Hemat</button>
                        <button class="nav-item-btn" data-target="kontak-section">Kontak</button>
                    </nav>

                    <!-- Right Action Icons: Cart & Login -->
                    <div class="navbar-right-actions">

                        <!-- Cart Button -->
                        <button id="btnOpenCart" class="btn-open-cart-box" title="Buka Keranjang Belanja">
                            <div class="cart-icon-pos">
                                <svg class="cart-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="8" cy="21" r="1" />
                                    <circle cx="19" cy="21" r="1" />
                                    <path
                                        d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                </svg>
                                <span id="cartCountBadge" class="cart-bounce-badge" style="display: none;">0</span>
                            </div>
                            <div class="cart-text-details">
                                <span class="cart-label-text">Keranjang</span>
                                <span id="cartSubtotalText" class="cart-amount-text">Rp 0</span>
                            </div>
                        </button>

                        <!-- User State / Login Button -->
                        @if(!$isLoggedIn || !$user)
                            <a href="{{ route('login') }}" class="btn-login-box" title="Login khusus pemesanan">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#BAE6FD"
                                    stroke-width="2">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                    <polyline points="10 17 15 12 10 7" />
                                    <line x1="15" y1="12" x2="3" y2="12" />
                                </svg>
                                <div style="display: flex; flex-direction: column; text-align: left; line-height: 1.1;">
                                    <span style="font-weight: 800; font-size: 0.75rem;">Masuk Akun</span>
                                    <span style="font-size: 9px; color: #E0F2FE;">Khusus Order</span>
                                </div>
                            </a>
                        @else
                            <div class="user-avatar-btn-wrap">
                                <button id="btnUserMenu" class="user-avatar-circle-btn"
                                    title="Akun: {{ $user->name }} ({{ $user->is_admin ? 'Admin' : 'Pelanggan' }})"
                                    type="button">
                                    <div class="user-avatar-inner-box">
                                        @if($user->avatar)
                                            <img id="navbarAvatarImg" src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <span id="navbarAvatarInitials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                </button>

                                <div id="userProfileDropdown" class="user-profile-dropdown-card">
                                    <div class="dropdown-header-user-card">
                                        <div class="dropdown-role-row">
                                            <span class="dropdown-role-label">Akun Saya</span>
                                            @if($user->is_admin)
                                                <span class="dropdown-badge-admin">Admin</span>
                                            @else
                                                <span class="dropdown-badge-customer">Pelanggan</span>
                                            @endif
                                        </div>
                                        <p id="dropdownCustomerName" class="dropdown-user-fullname">{{ $user->name }}</p>
                                        <p id="dropdownCustomerEmail" class="dropdown-user-email">{{ $user->email }}</p>
                                        @if($user->phone)
                                            <p id="dropdownCustomerPhone" class="dropdown-user-phone">{{ $user->phone }}</p>
                                        @endif
                                    </div>

                                    <!-- Edit Profil & Alamat Button -->
                                    <button id="btnOpenCustomerProfileModal" class="user-dropdown-btn-item" type="button">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#677D9E"
                                            stroke-width="2">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                        <span>Edit Profil &amp; Alamat</span>
                                    </button>

                                    <!-- Admin Link -->
                                    @if($user->is_admin)
                                        <a href="{{ route('admin.dashboard') }}" class="user-dropdown-btn-item admin-highlight">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path
                                                    d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                                <path d="m9 12 2 2 4-4" />
                                            </svg>
                                            <span>Dashboard Admin</span>
                                        </a>
                                    @endif

                                    <div class="dropdown-separator-line"></div>

                                    <!-- Logout Form -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="user-dropdown-btn-item logout">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#EF4444"
                                                stroke-width="2">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                                <polyline points="16 17 21 12 16 7" />
                                                <line x1="21" y1="12" x2="9" y2="12" />
                                            </svg>
                                            <span>Keluar (Logout)</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        <!-- Mobile Hamburger Button -->
                        <button id="btnMobileMenuToggle" class="btn-mobile-menu-toggle" aria-label="Toggle Menu"
                            type="button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="3" y1="12" x2="21" y2="12" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <line x1="3" y1="18" x2="21" y2="18" />
                            </svg>
                        </button>
                    </div>

                </div>

                <!-- Mobile Search Input Bar -->
                <div class="mobile-search-row">
                    <div class="search-input-box">
                        <input type="text" id="mobileSearchInput" class="search-input-field"
                            placeholder="Cari dimsum, nugget, seafood...">
                        <svg class="search-icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </div>
                </div>

                <!-- Mobile Drawer Menu (Identik dengan Navbar.jsx) -->
                <div id="mobileDrawerMenu" class="mobile-drawer-wrap" style="display: none;">
                    <div class="mobile-drawer-card">
                        @if(!$isLoggedIn || !$user)
                            <div class="mobile-guest-header">
                                <div>
                                    <p class="mobile-guest-title">Halo, Pelanggan!</p>
                                    <p class="mobile-guest-sub">Masuk untuk simpan data &amp; pesan instan</p>
                                </div>
                                <a href="{{ route('login') }}" class="mobile-btn-login">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                        <polyline points="10 17 15 12 10 7" />
                                        <line x1="15" y1="12" x2="3" y2="12" />
                                    </svg>
                                    <span>Masuk Akun</span>
                                </a>
                            </div>
                        @else
                            <div class="mobile-user-header">
                                <div class="mobile-user-avatar">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                            style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    @endif
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <p
                                            style="font-weight: 800; font-size: 0.875rem; color: var(--navy); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $user->name }}</p>
                                        @if($user->is_admin)
                                            <span
                                                style="font-size: 10px; font-weight: 800; background: #ECFDF5; color: #065F46; padding: 2px 6px; border-radius: 9999px;">Admin</span>
                                        @else
                                            <span
                                                style="font-size: 10px; font-weight: 800; background: #E0F2FE; color: #075985; padding: 2px 6px; border-radius: 9999px;">Pelanggan</span>
                                        @endif
                                    </div>
                                    <p
                                        style="font-size: 0.75rem; color: #6B7280; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $user->email }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Navigation Links -->
                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            <span
                                style="font-size: 10px; font-weight: 700; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.05em; padding: 0 0.5rem; margin-bottom: 0.25rem;">Navigasi
                                Menu</span>
                            <button class="mobile-nav-link" data-target="home" type="button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    <polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                                <span>Home</span>
                            </button>
                            <button class="mobile-nav-link" data-target="katalog-section" type="button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <line x1="2" y1="12" x2="22" y2="12" />
                                    <line x1="12" y1="2" x2="12" y2="22" />
                                    <path d="m20 16-4-4 4-4M4 8l4 4-4 4M16 4l-4 4-4-4M8 20l4-4 4 4" />
                                </svg>
                                <span>Katalog</span>
                            </button>
                            <button class="mobile-nav-link" data-target="rekomendasi-section" type="button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z" />
                                </svg>
                                <span>Paket Hemat</span>
                            </button>
                            <button class="mobile-nav-link" data-target="kontak-section" type="button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                </svg>
                                <span>Kontak</span>
                            </button>
                        </div>

                        @if($user)
                            <div
                                style="border-top: 1px solid #E5E7EB; padding-top: 0.5rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                <span
                                    style="font-size: 10px; font-weight: 700; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.05em; padding: 0 0.5rem; margin-bottom: 0.25rem;">Akun
                                    Pengguna</span>
                                <button id="btnMobileEditProfile" class="mobile-nav-link" type="button">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    <span>Edit Profil &amp; Alamat</span>
                                </button>
                                @if($user->is_admin)
                                    <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link admin-highlight">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                            <path d="m9 12 2 2 4-4" />
                                        </svg>
                                        <span>Dashboard Admin &amp; Kasir POS</span>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="mobile-nav-link logout">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#EF4444"
                                            stroke-width="2">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                            <polyline points="16 17 21 12 16 7" />
                                            <line x1="21" y1="12" x2="9" y2="12" />
                                        </svg>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </header>

    <main style="flex: 1;">
        <!-- 3. Hero Section (Banner Promosi & Ajakan Belanja) -->
        <section id="home" class="hero-section-wrap">
            <div class="max-w-7xl">
                <div class="hero-cols-grid">

                    <!-- Left: Headlines & CTAs -->
                    <div class="hero-left-col">
                        <h1 class="hero-headline">
                            Stok Makanan Beku <span>Higienis &amp; Praktis</span> untuk Keluarga
                        </h1>
                        <p class="hero-subhead">
                            Solusi bekal praktis dan hidangan lezat siap saji dalam hitungan menit. Dimsum premium,
                            nugget renyah, olahan seafood, hingga daging sukiyaki terjamin 100% Halal dengan pengemasan
                            kedap udara (vacuum sealed).
                        </p>
                        <div class="hero-cta-buttons-row">
                            <a href="#katalog-section" class="btn-cta-blue">
                                <span>Belanja Sekarang</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </a>
                            <a href="#rekomendasi-section" class="btn-cta-white">
                                <span>Cek Paket Hemat</span>
                            </a>
                        </div>
                        <div class="hero-three-pills">
                            <div class="hero-pill-item">
                                <div class="hero-pill-icon-box" style="color: var(--soft-blue);">
                                    <span class="motor-delivery-icon" style="width: 22px; height: 14px;"></span>
                                </div>
                                <div>
                                    <h4>Kurir Motor</h4>
                                    <p>Langsung ke Rumah</p>
                                </div>
                            </div>
                            <div class="hero-pill-item">
                                <div class="hero-pill-icon-box" style="color: #059669;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                        <path d="m9 12 2 2 4-4" />
                                    </svg>
                                </div>
                                <div>
                                    <h4>100% Halal</h4>
                                    <p>Higienis &amp; Bersih</p>
                                </div>
                            </div>
                            <div class="hero-pill-item">
                                <div class="hero-pill-icon-box" style="color: #D97706;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <circle cx="12" cy="8" r="6" />
                                        <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11" />
                                    </svg>
                                </div>
                                <div>
                                    <h4>Tanpa Pengawet</h4>
                                    <p>Beku Alami IQF</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Showcase Product Single Clean Card -->
                    <div class="hero-right-col">
                        <div id="heroShowcaseCard" class="hero-showcase-single-card">
                            <div class="hero-showcase-img-box">
                                <img id="heroSlideImg"
                                    src="https://images.unsplash.com/photo-1496116218417-1a781b1c416c?auto=format&fit=crop&w=800&q=80"
                                    alt="Showcase Produk" class="hero-showcase-img">
                                <div class="floating-slide-arrows">
                                    <button id="btnHeroPrev" class="btn-arrow-slide" title="Sebelumnya">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <polyline points="15 18 9 12 15 6" />
                                        </svg>
                                    </button>
                                    <button id="btnHeroNext" class="btn-arrow-slide" title="Berikutnya">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <polyline points="9 18 15 12 9 6" />
                                        </svg>
                                    </button>
                                </div>
                                <div id="heroSlideDotsWrap" class="floating-slide-dots"></div>
                            </div>
                            <div class="hero-showcase-bottom">
                                <h3 id="heroSlideTitle" class="hero-showcase-title">Dimsum Ayam Premium (Isi 15)</h3>
                                <div class="hero-showcase-price-row">
                                    <span id="heroSlidePrice" class="hero-showcase-price">Rp 35.000</span>
                                    <button id="btnHeroAddCart" class="btn-showcase-add" title="Tambah ke Keranjang">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                        <span>Keranjang</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- 4. Katalog Produk & Filter Kategori Makanan Beku -->
        <section id="katalog-section" class="catalog-section-wrap">
            <div class="max-w-7xl">

                <!-- Section Header -->
                <div class="catalog-header-wrap">
                    <div>
                        <div class="catalog-section-tag">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <line x1="2" y1="12" x2="22" y2="12" />
                                <line x1="12" y1="2" x2="12" y2="22" />
                                <path d="m20 16-4-4 4-4M4 8l4 4-4 4M16 4l-4 4-4-4M8 20l4-4 4 4" />
                            </svg>
                            <span>Katalog Freezer Segar</span>
                        </div>
                        <h2 class="catalog-section-title">Pilihan Makanan Beku Berkualitas</h2>
                        <p class="catalog-section-desc">Dikemas higienis dengan teknologi vacuum seal, siap santap kapan
                            saja</p>
                    </div>
                    <div class="catalog-counter-text">
                        Menampilkan <strong id="catalogCountText">{{ count($products) }}</strong> produk makanan beku
                    </div>
                </div>

                <!-- Category Filter Pills (Desain Bersih & Elegan: Teks Kategori + Badge Angka) -->
                <div id="categoryPillContainer" class="category-filter-scroll-row">
                    <button class="btn-category-pill selected" data-cat="Semua">
                        <span>Semua</span>
                        <span class="pill-count-badge">{{ count($products) }}</span>
                    </button>
                    @foreach($categories as $c)
                        @php
                            $pCount = $products->where('category', $c->name)->count();
                        @endphp
                        <button class="btn-category-pill" data-cat="{{ $c->name }}">
                            <span>{{ $c->name }}</span>
                            <span class="pill-count-badge">{{ $pCount }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Products Cards Grid -->
                <div id="catalogGrid" class="products-cards-grid">
                    @foreach($products as $prod)
                        <div class="product-card-single">
                            <!-- Image Box -->
                            <div class="card-img-container" onclick="window.openDetailModal('{{ $prod->id }}')">
                                <img src="{{ $prod->image ?: '/images/ica_logo.png' }}" alt="{{ $prod->name }}"
                                    class="card-product-img" loading="lazy" onerror="this.src='/images/ica_logo.png'">
                                <div class="card-top-badges-wrap">
                                    <span class="badge-cat-tag">{{ $prod->category }}</span>
                                    @if($prod->stock <= 0)
                                        <span class="badge-out-tag">Stok Habis</span>
                                    @elseif($prod->stock <= 5)
                                        <span class="badge-low-tag">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5">
                                                <path
                                                    d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                                <line x1="12" y1="9" x2="12" y2="13" />
                                                <line x1="12" y1="17" x2="12.01" y2="17" />
                                            </svg>
                                            Tersisa sedikit lagi
                                        </span>
                                    @endif
                                </div>
                                <button class="card-quick-eye-btn" title="Lihat Detail Produk"
                                    onclick="event.stopPropagation(); window.openDetailModal('{{ $prod->id }}')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Details Body -->
                            <div class="card-details-body">
                                <div>
                                    <div class="card-meta-line">
                                        <span class="card-weight-label">{{ $prod->weight ?: '500g' }}</span>
                                        @if($prod->stock <= 0)
                                            <span style="color: var(--red); font-weight: 700; font-size: 11px;">Stok
                                                Habis</span>
                                        @endif
                                    </div>
                                    <h3 class="card-title-heading" onclick="window.openDetailModal('{{ $prod->id }}')">
                                        {{ $prod->name }}
                                    </h3>
                                    <p class="card-desc-snippet">
                                        {{ $prod->description ?: 'Makanan beku higienis kemasan praktis siap santap.' }}
                                    </p>
                                </div>

                                <div class="card-bottom-actions-row">
                                    <div>
                                        <span class="card-special-price-label">Harga Spesial</span>
                                        <span class="card-bold-price">Rp
                                            {{ number_format($prod->price, 0, ',', '.') }}</span>
                                    </div>
                                    <button class="btn-card-add-cart" {{ $prod->stock <= 0 ? 'disabled' : '' }}
                                        onclick="window.addToCartById('{{ $prod->id }}', 1)">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                        <span>Keranjang</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- 5. Rekomendasi Cerdas & Paket Hemat Keluarga -->
        <section id="rekomendasi-section" class="smart-rec-section-wrap">
            <div class="max-w-7xl">

                <!-- Amber Recommendation Banner -->
                <div class="rec-banner-amber">
                    <div>
                        <h2 class="rec-banner-title">Paket Hemat &amp; Rekomendasi Menu Hari Ini</h2>
                        <p class="rec-banner-desc">
                            Bingung mau masak apa hari ini? Kami sudah meracik paket kombinasi makanan beku favorit
                            pelanggan dengan harga bundling lebih hemat 10-15%.
                        </p>
                    </div>
                    <div class="rec-flame-badge-box">
                        <svg class="flame-icon-anim" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                                d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
                        </svg>
                        <div>
                            <p style="font-size: 0.75rem; font-weight: 700; color: var(--navy);">Hemat Hingga Rp 17.000
                            </p>
                            <p style="font-size: 11px; color: var(--text-gray-500);">Otomatis Masuk Keranjang</p>
                        </div>
                    </div>
                </div>

                <!-- 3 Bundles Grid -->
                <div class="bundles-cards-grid">
                    <!-- Bundle 1 -->
                    <div class="bundle-card-single">
                        <div>
                            <div class="bundle-img-top-wrap">
                                <img src="https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=600&q=80"
                                    alt="Paket Sarapan Praktis Keluarga" class="bundle-img-cover">
                                <div class="bundle-badge-left">Paling Laris</div>
                                <div class="bundle-badge-right">Hemat Rp 14.000</div>
                            </div>
                            <div class="bundle-card-middle-content">
                                <div class="bundle-tags-row">
                                    <span class="bundle-tag-pill">#Cukup 3-4 Orang</span>
                                    <span class="bundle-tag-pill">#Hemat 12%</span>
                                </div>
                                <h3 class="bundle-title-text">Paket Sarapan Praktis Keluarga</h3>
                                <p class="bundle-desc-text">Kombinasi Dimsum Ayam + Crispy Chicken Nugget + Sosis
                                    Cocktail untuk sarapan cepat bergizi.</p>
                                <div class="bundle-items-bullets">
                                    <p class="bundle-items-title">Isi Dalam Paket:</p>
                                    <div class="bullet-line">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                            stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span>Produk beku terverifikasi higienis</span>
                                    </div>
                                    <div class="bullet-line">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                            stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span>Termasuk ice gel &amp; kemasan vakum</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bundle-card-bottom-action">
                            <div class="bundle-price-row">
                                <div>
                                    <span class="bundle-old-price">Rp 119.000</span>
                                    <span class="bundle-current-price">Rp 105.000</span>
                                </div>
                                <span class="bundle-best-value-tag">Best Value</span>
                            </div>
                            <button class="btn-bundle-take" onclick="window.addBundleToCart('BND-01')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="8" cy="21" r="1" />
                                    <circle cx="19" cy="21" r="1" />
                                    <path
                                        d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                </svg>
                                <span>Ambil Paket Hemat Ini</span>
                            </button>
                        </div>
                    </div>

                    <!-- Bundle 2 -->
                    <div class="bundle-card-single">
                        <div>
                            <div class="bundle-img-top-wrap">
                                <img src="https://images.unsplash.com/photo-1496116218417-1a781b1c416c?auto=format&fit=crop&w=600&q=80"
                                    alt="Paket Dimsum Party Time" class="bundle-img-cover">
                                <div class="bundle-badge-left">Rekomendasi Chef</div>
                                <div class="bundle-badge-right">Hemat Rp 15.000</div>
                            </div>
                            <div class="bundle-card-middle-content">
                                <div class="bundle-tags-row">
                                    <span class="bundle-tag-pill">#Dimsum Lovers</span>
                                    <span class="bundle-tag-pill">#Kukus / Goreng</span>
                                </div>
                                <h3 class="bundle-title-text">Paket Dimsum Party Time</h3>
                                <p class="bundle-desc-text">Lengkap dengan Siomay Udang, Hakau Kristal, dan Lumpia Kulit
                                    Tahu untuk santai sore seru.</p>
                                <div class="bundle-items-bullets">
                                    <p class="bundle-items-title">Isi Dalam Paket:</p>
                                    <div class="bullet-line">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                            stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span>Produk beku terverifikasi higienis</span>
                                    </div>
                                    <div class="bullet-line">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                            stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span>Termasuk ice gel &amp; kemasan vakum</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bundle-card-bottom-action">
                            <div class="bundle-price-row">
                                <div>
                                    <span class="bundle-old-price">Rp 125.000</span>
                                    <span class="bundle-current-price">Rp 110.000</span>
                                </div>
                                <span class="bundle-best-value-tag">Best Value</span>
                            </div>
                            <button class="btn-bundle-take" onclick="window.addBundleToCart('BND-02')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="8" cy="21" r="1" />
                                    <circle cx="19" cy="21" r="1" />
                                    <path
                                        d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                </svg>
                                <span>Ambil Paket Hemat Ini</span>
                            </button>
                        </div>
                    </div>

                    <!-- Bundle 3 -->
                    <div class="bundle-card-single">
                        <div>
                            <div class="bundle-img-top-wrap">
                                <img src="https://images.unsplash.com/photo-1547928576-a4a33237cbc3?auto=format&fit=crop&w=600&q=80"
                                    alt="Paket Shabu & Grill Weekend" class="bundle-img-cover">
                                <div class="bundle-badge-left">Super Hemat</div>
                                <div class="bundle-badge-right">Hemat Rp 17.000</div>
                            </div>
                            <div class="bundle-card-middle-content">
                                <div class="bundle-tags-row">
                                    <span class="bundle-tag-pill">#Pesta Barbeque</span>
                                    <span class="bundle-tag-pill">#Weekend Special</span>
                                </div>
                                <h3 class="bundle-title-text">Paket Shabu &amp; Grill Weekend</h3>
                                <p class="bundle-desc-text">US Beef Shortplate 500g + Bakso Seafood Shabu Mix 500g +
                                    Snow Crabstick untuk pesta keluarga.</p>
                                <div class="bundle-items-bullets">
                                    <p class="bundle-items-title">Isi Dalam Paket:</p>
                                    <div class="bullet-line">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                            stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span>Produk beku terverifikasi higienis</span>
                                    </div>
                                    <div class="bullet-line">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                            stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span>Termasuk ice gel &amp; kemasan vakum</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bundle-card-bottom-action">
                            <div class="bundle-price-row">
                                <div>
                                    <span class="bundle-old-price">Rp 149.000</span>
                                    <span class="bundle-current-price">Rp 132.000</span>
                                </div>
                                <span class="bundle-best-value-tag">Best Value</span>
                            </div>
                            <button class="btn-bundle-take" onclick="window.addBundleToCart('BND-03')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="8" cy="21" r="1" />
                                    <circle cx="19" cy="21" r="1" />
                                    <path
                                        d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                </svg>
                                <span>Ambil Paket Hemat Ini</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- 6. Keunggulan Layanan & Kualitas Produk Ica -->
        <section class="why-us-section-wrap">
            <div class="max-w-7xl">
                <div class="why-us-header">
                    <h2 class="why-us-title">Mengapa Memilih Ica Frozen Food?</h2>
                    <p class="why-us-subtitle">Standar pengolahan higienis tanpa bahan pengawet kimia berbahaya</p>
                </div>
                <div class="why-us-grid">
                    <div class="why-us-card">
                        <div class="why-us-icon-box" style="background-color: var(--soft-blue);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m15 9-6 6" />
                                <path d="M9 9h.01" />
                                <path d="M15 15h.01" />
                            </svg>
                        </div>
                        <h3 class="why-us-card-title">Harga Murah &amp; Terjangkau</h3>
                        <p class="why-us-card-desc">Pilihan makanan beku berkualitas tinggi dengan harga ramah kantong,
                            hemat untuk konsumsi harian keluarga.</p>
                    </div>

                    <div class="why-us-card">
                        <div class="why-us-icon-box" style="background-color: #059669;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="why-us-card-title">100% Halal, Segar &amp; Higienis</h3>
                        <p class="why-us-card-desc">Menyediakan aneka produk makanan beku berkualitas tinggi, terjamin
                            kehalalannya, serta dikemas secara higienis dan terjaga kesegarannya.</p>
                    </div>

                    <div class="why-us-card">
                        <div class="why-us-icon-box" style="background-color: var(--amber);">
                            <span class="motor-delivery-icon" style="width: 26px; height: 17px; color: #FFFFFF;"></span>
                        </div>
                        <h3 class="why-us-card-title">Bisa Diantar Via Kurir</h3>
                        <p class="why-us-card-desc">Pesanan siap diantar langsung oleh kurir ke rumah Anda di wilayah
                            Banjarbaru, Martapura, dan sekitarnya.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. Kontak Outlet & Pemesanan Cepat via WhatsApp -->
        <section id="kontak-section" class="contact-section-wrap">
            <div class="max-w-7xl">
                <div class="contact-blue-box">
                    <div class="contact-left-text">
                        <span class="contact-tag-eyebrow">Konsultasi &amp; Pemesanan Cepat</span>
                        <h2 class="contact-headline">Mau Tanya Varian Produk atau Pesan Cepat via WA?</h2>
                        <p class="contact-desc">Hubungi tim layanan pelanggan kami untuk konsultasi varian makanan beku,
                            ketersediaan stok, pesanan jumlah besar, maupun kemitraan reseller.</p>
                        <div class="contact-check-pills">
                            <span class="contact-check-badge">✓ Harga Khusus Reseller</span>
                            <span class="contact-check-badge">✓ Pesanan Katering &amp; Arisan</span>
                            <span class="contact-check-badge">✓ Rekomendasi Produk</span>
                        </div>
                    </div>
                    <a href="https://wa.me/6287854513770?text=Halo%20Ica%20Frozen%20Food,%20saya%20ingin%20bertanya%20produk..."
                        target="_blank" rel="noreferrer" class="btn-chat-wa-white">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <span>Chat WhatsApp Sekarang</span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- 8. Footer Informasi Alamat, Kontak & Hak Cipta -->
    <footer class="site-footer-white">
        <div class="max-w-7xl">
            <div class="footer-cols-three">

                <!-- Kolom 1: Brand & Profil -->
                <div class="footer-col-1">
                    <div class="footer-logo-title-row">
                        <img src="/images/ica_logo.png" alt="Ica Frozen Food Logo" class="footer-logo-img">
                        <div>
                            <h3 class="footer-store-name">Ica Frozen Food</h3>
                            <p class="footer-store-tag">Halal, Segar &amp; Higienis</p>
                        </div>
                    </div>
                    <p style="font-size: 11px; color: #6B7280; line-height: 1.625;">
                        Menyediakan aneka makanan beku praktis berkualitas tinggi: dimsum, nugget, sosis, olahan
                        seafood, hingga daging sukiyaki dengan pengemasan kedap udara (vacuum sealed).
                    </p>
                </div>

                <!-- Kolom 2: Alamat Toko Kami -->
                <div class="footer-col-2">
                    <h4 class="footer-col-header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                            stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span>Alamat Toko Kami</span>
                    </h4>
                    <p style="font-weight: 500; color: #4B5563; line-height: 1.625;">
                        Loktabat Utara, Banjarbaru Utara, Kalimantan Selatan
                    </p>
                    <a href="https://maps.app.goo.gl/KRidQNPjqjr8N3ut9" target="_blank" rel="noreferrer"
                        style="display: inline-flex; align-items: center; gap: 0.375rem; color: var(--soft-blue); font-weight: 700; font-size: 0.75rem; text-decoration: underline; text-underline-offset: 4px;">
                        <span>Buka di Google Maps</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                            <polyline points="15 3 21 3 21 9" />
                            <line x1="10" y1="14" x2="21" y2="3" />
                        </svg>
                    </a>
                </div>

                <!-- Kolom 3: Jam Operasional & Kontak -->
                <div class="footer-col-3">
                    <h4 class="footer-col-header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span>Jam Operasional &amp; Kontak</span>
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                        <p style="display: flex; align-items: center; gap: 0.5rem;">
                            <span
                                style="width: 8px; height: 8px; border-radius: 9999px; background-color: #10B981;"></span>
                            <span>Buka Setiap Hari (08.00 - 21.00 WITA)</span>
                        </p>
                        <p style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                stroke-width="2.5">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            <a href="https://wa.me/6287854513770" target="_blank" rel="noreferrer"
                                style="font-weight: 600; color: #374151;">+0878-5451-3770 (WhatsApp Admin)</a>
                        </p>
                        <p style="display: flex; align-items: center; gap: 0.5rem; font-size: 11px; color: #6B7280;">
                            <span class="motor-delivery-icon"
                                style="width: 14px; height: 14px; color: var(--soft-blue);"></span>
                            <span>Bisa diantar oleh kurir langsung ke rumah anda (Banjarbaru, Martapura &amp;
                                Sekitarnya)</span>
                        </p>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="footer-bottom-copy-row">
                <p>&copy; {{ date('Y') }} Ica Frozen Food. Solusi Makanan Beku Praktis, Higienis &amp; Halal.</p>
                <p>Banjarbaru, Kalimantan Selatan</p>
            </div>
        </div>
    </footer>

    <!-- 9. Modal Detail Informasi & Spesifikasi Produk -->
    <div id="productDetailModal" class="modal-product-backdrop">
        <div class="modal-product-card-wrap">
            <button id="detailModalClose"
                style="position: absolute; top: 1rem; right: 1rem; z-index: 10; padding: 0.5rem; border-radius: 9999px; background: rgba(255,255,255,0.9); color: #6B7280; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); cursor: pointer;"
                title="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>

            <!-- Left Image Column -->
            <div
                style="position: relative; background-color: var(--bg-cream); min-height: 260px; display: flex; align-items: center; justify-content: center; overflow: hidden; width: 100%;">
                <img id="detailImage" src="/images/ica_logo.png" alt="Detail Produk"
                    style="width: 100%; height: 100%; object-fit: contain;">
                <div
                    style="position: absolute; top: 1rem; left: 1rem; display: flex; flex-direction: column; gap: 0.375rem;">
                    <span id="detailCategory"
                        style="background-color: var(--soft-blue); color: #FFFFFF; font-size: 11px; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">Kategori</span>
                    <span id="detailStockBadge"
                        style="display: none; background-color: var(--red); color: #FFFFFF; font-size: 10px; font-weight: 800; padding: 0.125rem 0.625rem; border-radius: 9999px;">Stok
                        Habis</span>
                </div>
                <div
                    style="position: absolute; bottom: 0.75rem; left: 0.75rem; right: 0.75rem; background: rgba(255,255,255,0.9); backdrop-filter: blur(4px); padding: 0.375rem 0.75rem; border-radius: 12px; display: flex; align-items: center; justify-content: flex-end; font-size: 0.75rem;">
                    <span style="display: flex; align-items: center; gap: 0.25rem; font-weight: 600; color: #047857;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        100% Halal
                    </span>
                </div>
            </div>

            <!-- Right Details Column -->
            <div
                style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; gap: 1rem; width: 100%;">
                <div>
                    <div
                        style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; font-size: 0.75rem;">
                        <span id="detailCategorySub" style="font-weight: 600; color: var(--soft-blue);">Kategori</span>
                        <span style="color: #D1D5DB;">•</span>
                        <span id="detailSku" style="color: #6B7280; font-family: monospace;">SKU: -</span>
                    </div>
                    <h2 id="detailTitle"
                        style="font-size: 1.25rem; font-weight: 800; color: var(--navy); line-height: 1.35;">Nama Produk
                    </h2>
                    <div id="detailPrice"
                        style="margin-top: 0.5rem; font-size: 1.5rem; font-weight: 900; color: var(--soft-blue);">Rp 0
                    </div>
                    <p id="detailDesc"
                        style="margin-top: 0.75rem; font-size: 0.875rem; color: #4B5563; line-height: 1.625;">Deskripsi
                        produk...</p>

                    <!-- Spec Badges -->
                    <div
                        style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #F3F4F6;">
                        <div
                            style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background-color: rgba(241, 240, 232, 0.7); border-radius: 12px; font-size: 0.75rem; color: var(--navy);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2">
                                <path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v1" />
                                <line x1="18" y1="8" x2="23" y2="13" />
                                <line x1="23" y1="13" x2="16" y2="13" />
                            </svg>
                            <div>
                                <p style="font-size: 10px; color: #6B7280;">Berat Bersih</p>
                                <p id="detailWeight" style="font-weight: 700;">500g</p>
                            </div>
                        </div>
                        <div
                            style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background-color: rgba(241, 240, 232, 0.7); border-radius: 12px; font-size: 0.75rem; color: var(--navy);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <div>
                                <p style="font-size: 10px; color: #6B7280;">Masa Simpan</p>
                                <p id="detailShelfLife" style="font-weight: 700;">6 Bulan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Features Check -->
                    <ul
                        style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.375rem; font-size: 0.75rem; color: #4B5563; list-style: none;">
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>Kemasan kedap udara (vacuum sealed food grade)</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>Bebas bahan pengawet berbahaya &amp; pewarna kimia</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669"
                                stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>Bisa diantar oleh kurir langsung ke rumah anda</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Footer (Counter + Button) -->
                <div style="padding-top: 1rem; border-top: 1px solid #F3F4F6;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div
                            style="display: flex; align-items: center; border: 1px solid #A6B1C3; border-radius: 12px; background: #F9FAFB; padding: 4px;">
                            <button id="btnDetailMinus"
                                style="width: 2rem; height: 2rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #374151; font-weight: 700; cursor: pointer;">-</button>
                            <span id="detailQtyDisplay"
                                style="width: 2rem; text-align: center; font-weight: 700; font-size: 0.875rem; color: var(--navy);">1</span>
                            <button id="btnDetailPlus"
                                style="width: 2rem; height: 2rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #374151; font-weight: 700; cursor: pointer;">+</button>
                        </div>
                        <button id="btnDetailAddToCart"
                            style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1rem; background-color: var(--soft-blue); color: #FFFFFF; font-weight: 700; font-size: 0.875rem; border-radius: 12px; box-shadow: var(--shadow-md); cursor: pointer;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <circle cx="8" cy="21" r="1" />
                                <circle cx="19" cy="21" r="1" />
                                <path
                                    d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                            </svg>
                            <span id="detailBtnText">+ Keranjang (1)</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 10. Keranjang Belanja (Drawer Samping / Slide-Over) -->
    <div id="cartDrawerOverlay" class="cart-drawer-backdrop"></div>
    <div id="cartDrawer" class="cart-drawer-slide-box">

        <!-- Header -->
        <div class="cart-drawer-top-header">
            <div style="display: flex; align-items: center; gap: 0.625rem;">
                <div
                    style="width: 2.5rem; height: 2.5rem; border-radius: 12px; background-color: rgba(103, 125, 158, 0.1); display: flex; align-items: center; justify-content: center; color: var(--soft-blue);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="8" cy="21" r="1" />
                        <circle cx="19" cy="21" r="1" />
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                    </svg>
                </div>
                <div>
                    <h2 style="font-weight: 800; font-size: 1rem; color: var(--navy);">Keranjang Belanja</h2>
                    <p id="drawerCartCountLabel" style="font-size: 0.75rem; color: #6B7280;">0 Produk Makanan Beku</p>
                </div>
            </div>
            <button id="btnCloseCart" style="padding: 0.5rem; border-radius: 12px; color: #9CA3AF; cursor: pointer;"
                title="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <!-- Body: Cart Items & Checkout -->
        <div id="cartBodyScroll"
            style="flex: 1; overflow-y: auto; padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">

            <!-- Empty State Container (Displayed ONLY when cart is empty - matching user design) -->
            <div id="cartEmptyStateWrap" class="cart-empty-container" style="display: none;">
                <div class="cart-empty-card">
                    <div class="cart-empty-icon-box">
                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <h3 class="cart-empty-title">Keranjang Masih Kosong</h3>
                    <p class="cart-empty-desc">Silakan pilih dimsum, nugget, seafood, atau paket hemat favorit Anda dari
                        katalog.</p>
                    <button type="button" id="btnEmptyStartShopping" class="btn-empty-start-shopping">
                        Mulai Belanja
                    </button>
                </div>
            </div>

            <!-- Active Cart Wrapper (Items, Method, Data Pemesan - hidden when cart is empty) -->
            <div id="cartActiveContentWrap" style="display: flex; flex-direction: column; gap: 1.25rem;">

                <!-- Items Container -->
                <div id="cartItemsWrap"></div>

                <!-- Delivery Method Options -->
                <div id="deliveryMethodSection"
                    style="background-color: #FFFFFF; padding: 1rem; border-radius: 16px; border: 1px solid rgba(166, 177, 195, 0.6); display: flex; flex-direction: column; gap: 0.75rem;">
                    <h3 style="font-weight: 700; font-size: 0.75rem; color: var(--navy);">Pilih Metode Pengambilan</h3>

                    <!-- Option 1: Via Kurir -->
                    <label id="deliveryRadioKurir"
                        style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border-radius: 12px; border: 1px solid var(--soft-blue); background-color: rgba(235, 240, 246, 0.6); cursor: pointer;">
                        <input type="radio" name="delivery_method" value="kurir" checked
                            style="margin-top: 2px; accent-color: var(--soft-blue);">
                        <div style="flex: 1; font-size: 0.75rem;">
                            <div
                                style="display: flex; justify-content: space-between; font-weight: 700; color: var(--navy);">
                                <span style="display: flex; align-items: center; gap: 0.375rem;">
                                    <span class="motor-delivery-icon"
                                        style="width: 16px; height: 16px; color: var(--soft-blue);"></span>
                                    Via kurir
                                </span>
                                <span style="color: var(--soft-blue);">+Rp 5.000</span>
                            </div>
                            <p style="font-size: 11px; color: #6B7280; margin-top: 2px;">diantar oleh kurir ke alamat
                                anda</p>
                        </div>
                    </label>

                    <!-- Option 2: Ambil di Toko -->
                    <label id="deliveryRadioAmbil"
                        style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border-radius: 12px; border: 1px solid #E5E7EB; cursor: pointer;">
                        <input type="radio" name="delivery_method" value="ambil_sendiri"
                            style="margin-top: 2px; accent-color: var(--soft-blue);">
                        <div style="flex: 1; font-size: 0.75rem;">
                            <div
                                style="display: flex; justify-content: space-between; font-weight: 700; color: var(--navy);">
                                <span style="display: flex; align-items: center; gap: 0.375rem;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="var(--soft-blue)" stroke-width="2">
                                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7" />
                                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4" />
                                        <rect width="20" height="5" x="2" y="7" />
                                    </svg>
                                    Ambil di Toko
                                </span>
                                <span style="color: #059669; font-weight: 700;">Gratis</span>
                            </div>
                            <p style="font-size: 11px; color: #6B7280; margin-top: 2px;">Ambil sendiri di toko Ica Froze
                                Food</p>
                        </div>
                    </label>
                </div>

                <!-- Customer Checkout Info Form -->
                <div id="checkoutFormSection"
                    style="background-color: #FFFFFF; padding: 1rem; border-radius: 16px; border: 1px solid rgba(166, 177, 195, 0.6); display: flex; flex-direction: column; gap: 0.75rem;">
                    <h3 style="font-weight: 700; font-size: 0.75rem; color: var(--navy);">Data Pemesan</h3>
                    <div>
                        <label
                            style="font-size: 11px; font-weight: 700; color: var(--navy); display: block; margin-bottom: 2px;">Nama
                            Lengkap *</label>
                        <input type="text" id="custNameInput" placeholder="Contoh: Ibu Rina"
                            value="{{ $user ? $user->name : '' }}"
                            style="width: 100%; padding: 8px 12px; font-size: 0.75rem; border: 1px solid #A6B1C3; border-radius: 10px;">
                    </div>
                    <div>
                        <label
                            style="font-size: 11px; font-weight: 700; color: var(--navy); display: block; margin-bottom: 2px;">Nomor
                            WhatsApp *</label>
                        <input type="tel" id="custPhoneInput" placeholder="08xxxxxxxxxx"
                            value="{{ $user ? ($user->phone ?? '') : '' }}"
                            style="width: 100%; padding: 8px 12px; font-size: 0.75rem; border: 1px solid #A6B1C3; border-radius: 10px;">
                    </div>
                    <div id="addressFieldWrap">
                        <label
                            style="font-size: 11px; font-weight: 700; color: var(--navy); display: block; margin-bottom: 2px;">Alamat
                            Pengantaran Kurir *</label>
                        <textarea id="custAddressInput" rows="2" placeholder="Nama jalan, nomor rumah, patokan..."
                            style="width: 100%; padding: 8px 12px; font-size: 0.75rem; border: 1px solid #A6B1C3; border-radius: 10px;">{{ $user ? ($user->address ?? '') : '' }}</textarea>
                    </div>
                    <div>
                        <label
                            style="font-size: 11px; font-weight: 700; color: var(--navy); display: block; margin-bottom: 2px;">Metode
                            Pembayaran</label>
                        <select id="custPaymentInput"
                            style="width: 100%; padding: 8px 12px; font-size: 0.75rem; border: 1px solid #A6B1C3; border-radius: 10px; background-color: #FFFFFF; color: var(--navy);">
                            <option value="QRIS Instan">QRIS Instan (BCA / GoPay / OVO / Dana / ShopeePay)</option>
                            <option value="Transfer Bank (BCA)">Transfer Bank (BCA: 782-019-2341)</option>
                            <option value="Bayar Tunai di Kasir Toko" id="optPayCash">Bayar Tunai di Kasir Toko (Khusus
                                Ambil Sendiri)</option>
                        </select>
                        <p id="courierPaymentNotice"
                            style="display: none; font-size: 11px; color: #0284C7; background-color: #F0F9FF; border: 1px solid #BAE6FD; padding: 6px 10px; border-radius: 8px; margin-top: 6px; line-height: 1.4;">
                            ℹ️ Pengantaran kurir hanya menerima pembayaran non-tunai (QRIS / Transfer Bank).
                        </p>
                    </div>
                </div>

            </div>

        </div>

        <!-- Drawer Footer Summary & Checkout Button (Hidden when empty) -->
        <div id="cartFooterSection"
            style="padding: 1rem 1.25rem; background-color: #FFFFFF; border-top: 1px solid rgba(166, 177, 195, 0.4); display: flex; flex-direction: column; gap: 0.5rem; box-shadow: 0 -4px 12px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #6B7280;">
                <span>Subtotal Belanja</span>
                <span id="drawerSubtotalAmount" style="font-weight: 700; color: var(--navy);">Rp 0</span>
            </div>
            <div id="drawerIceFeeRow"
                style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #6B7280;">
                <span style="display: flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                        stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                    <span>Ongkos Kirim</span>
                </span>
                <span id="drawerIceFeeAmount" style="font-weight: 700; color: var(--navy);">Rp 5.000</span>
            </div>
            <div
                style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 900; color: var(--navy); border-top: 1px dashed rgba(166, 177, 195, 0.6); padding-top: 0.5rem;">
                <span>Total Pembayaran</span>
                <span id="drawerGrandTotalAmount">Rp 0</span>
            </div>
            <button id="btnSubmitOrder"
                style="width: 100%; padding: 0.75rem; background-color: var(--soft-blue); color: #FFFFFF; font-weight: 800; font-size: 0.875rem; border-radius: 12px; box-shadow: var(--shadow-md); margin-top: 0.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <span id="btnSubmitOrderText">Lanjut ke Pembayaran</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="5" y1="12" x2="19" y2="12" />
                    <polyline points="12 5 19 12 12 19" />
                </svg>
            </button>
            <p
                style="font-size: 11px; text-align: center; color: #9CA3AF; display: flex; align-items: center; justify-content: center; gap: 6px; margin: 4px 0 0;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <span>Transaksi aman • Terhubung langsung ke sistem kasir freezer</span>
            </p>
        </div>

    </div>

    <!-- 11. Modal Pembayaran (QRIS Instan & Transfer Bank) -->
    <div id="paymentModal" class="modal-product-backdrop">
        <div class="payment-modal-card">

            <!-- Modal Header -->
            <div class="payment-modal-header">
                <div style="display: flex; align-items: center; gap: 0.625rem;">
                    <span class="payment-header-icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <rect width="20" height="14" x="2" y="5" rx="2" />
                            <line x1="2" y1="10" x2="22" y2="10" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="payment-modal-title">Selesaikan Pembayaran</h3>
                        <p id="paymentSubtitleText" class="payment-modal-subtitle">Scan &amp; Konfirmasi Pembayaran Anda
                        </p>
                    </div>
                </div>
                <button type="button" id="btnPaymentClose" class="payment-modal-close-btn" title="Kembali ke Keranjang">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <!-- Body Container -->
            <div class="payment-modal-body">

                <!-- Bill Summary Banner -->
                <div class="payment-total-banner">
                    <div>
                        <span class="payment-total-label">Total Tagihan Pesanan</span>
                        <div id="paymentModalAmount" class="payment-total-value">Rp 0</div>
                    </div>
                    <div id="paymentModalMethodBadge" class="payment-method-badge">
                        QRIS Instan
                    </div>
                </div>

                <!-- Dynamic Payment Method Content (QRIS / Bank Transfer / Cash) -->
                <div id="paymentDynamicContent"></div>

                <!-- Verification Progress Box (Shown during verification) -->
                <div id="paymentVerifyingBox" class="payment-verifying-container" style="display: none;">
                    <div class="payment-spinner-ring"></div>
                    <h4 class="payment-verifying-title">Sistem Sedang Memverifikasi Pembayaran...</h4>
                    <p class="payment-verifying-desc">Mohon tunggu sebentar, sistem sedang mencocokkan mutasi transaksi
                        Anda dengan server perbankan.</p>
                    <div class="payment-verifying-bar-wrap">
                        <div class="payment-verifying-bar-fill"></div>
                    </div>
                </div>

                <!-- Payment Success Banner (Briefly shown before opening receipt) -->
                <div id="paymentSuccessNotice" class="payment-success-container" style="display: none;">
                    <div class="payment-success-icon-box">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                    <h4 style="font-weight: 800; color: #065F46; font-size: 1.05rem; margin-top: 0.5rem;">Pembayaran
                        Berhasil Divalidasi!</h4>
                    <p style="font-size: 0.8rem; color: #047857; margin-top: 0.25rem;">Pesanan tersimpan di sistem.
                        Menyiapkan struk belanja Anda...</p>
                </div>

            </div>

            <!-- Modal Footer Action -->
            <div id="paymentModalFooter" class="payment-modal-footer">
                <button type="button" id="btnConfirmPayment" class="btn-confirm-payment">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span id="btnConfirmPaymentText">Konfirmasi Bahwa Sudah Bayar</span>
                </button>
                <p class="payment-secure-notice">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                    <span>Verifikasi pembayaran aman &amp; terhubung otomatis ke kasir</span>
                </p>
            </div>

        </div>
    </div>

    <!-- 12. Modal Struk Pembelian (Thermal Receipt Preview) -->
    <div id="receiptModal" class="modal-product-backdrop">
        <div
            style="width: 100%; max-width: 400px; background-color: #FFFFFF; border-radius: 24px; padding: 1.5rem; box-shadow: var(--shadow-xl); border: 1px solid rgba(166, 177, 195, 0.6); display: flex; flex-direction: column; gap: 1rem;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #F3F4F6; padding-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="padding: 6px; border-radius: 8px; background-color: #ECFDF5; color: #059669;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </span>
                    <div>
                        <h3 style="font-weight: 800; font-size: 0.875rem; color: var(--navy);">Struk Pembelian</h3>
                        <p style="font-size: 11px; color: #6B7280;">Thermal Receipt Preview</p>
                    </div>
                </div>
                <button id="btnReceiptClose" style="color: #9CA3AF; cursor: pointer;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <!-- Receipt Paper -->
            <div
                style="background-color: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 11px; color: #1F2937; line-height: 1.5;">
                <div
                    style="text-align: center; border-bottom: 1px dashed #D1D5DB; padding-bottom: 8px; margin-bottom: 8px;">
                    <div style="font-weight: 900; font-size: 13px; letter-spacing: 1px;">ICA FROZEN FOOD</div>
                    <div>Makanan Beku Higienis &amp; Halal</div>
                    <div>Loktabat Utara, Banjarbaru • WA: 0878-5451-3770</div>
                </div>
                <div style="border-bottom: 1px dashed #D1D5DB; padding-bottom: 6px; margin-bottom: 6px;">
                    <div>No. Order : <span id="recNoOrder" style="font-weight: 700;">-</span></div>
                    <div>Waktu : <span id="recDate">-</span></div>
                    <div>Pemesan : <span id="recCustomer">-</span></div>
                    <div>Metode : <span id="recMethod">-</span></div>
                    <div>Bayar : <span id="recPayment">-</span></div>
                </div>
                <div id="recItemsList"
                    style="border-bottom: 1px dashed #D1D5DB; padding-bottom: 6px; margin-bottom: 6px;"></div>
                <div style="border-bottom: 1px dashed #D1D5DB; padding-bottom: 6px; margin-bottom: 6px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal:</span>
                        <span id="recSubtotal">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Ongkos Kirim:</span>
                        <span id="recIceFee">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: 900; font-size: 12px;">
                        <span>TOTAL AKHIR:</span>
                        <span id="recTotal">Rp 0</span>
                    </div>
                </div>
                <div style="text-align: center; color: #6B7280; font-size: 10px; padding-top: 4px;">
                    <div>* Simpan segera di freezer agar tetap segar *</div>
                    <div>Terima kasih atas pesanan Anda!</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.5rem;">
                <button id="btnPrintReceipt"
                    style="flex: 1; padding: 0.625rem; background-color: #F3F4F6; color: var(--navy); border-radius: 10px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.375rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect width="12" height="8" x="6" y="14" />
                    </svg>
                    <span>Cetak Struk</span>
                </button>
                <button id="btnCopyReceipt"
                    style="flex: 1; padding: 0.625rem; background-color: var(--soft-blue); color: #FFFFFF; border-radius: 10px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.375rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
                        <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
                    </svg>
                    <span>Salin Teks</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 13. Modal Pengaturan Profil Pengguna & Akun -->
    <div id="userProfileModal" class="modal-product-backdrop" style="display: none;">
        <div class="user-profile-modal-card">
            <!-- Modal Header -->
            <div class="user-modal-header">
                <button id="btnCloseProfileModal" class="user-modal-close-btn" type="button" aria-label="Tutup">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <div class="user-modal-header-content">
                    <div id="profileModalAvatar" class="user-modal-avatar">
                        @if($user && $user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                        @endif
                    </div>
                    <div style="min-width: 0; color: #FFFFFF;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <h3 id="profileModalName"
                                style="font-size: 1.125rem; font-weight: 800; margin: 0; color: #FFFFFF;">
                                {{ $user->name ?? '' }}</h3>
                            @if($user && $user->is_admin)
                                <span
                                    style="display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 800; background: rgba(5, 150, 105, 0.9); color: #FFFFFF; padding: 2px 8px; border-radius: 9999px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 13 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                        <path d="m9 12 2 2 4-4" />
                                    </svg> Admin
                                </span>
                            @else
                                <span
                                    style="font-size: 10px; font-weight: 800; background: rgba(14, 165, 233, 0.9); color: #FFFFFF; padding: 2px 8px; border-radius: 9999px;">
                                    Pelanggan
                                </span>
                            @endif
                        </div>
                        <p id="profileModalEmail"
                            style="font-size: 0.75rem; color: rgba(255,255,255,0.8); margin: 0.15rem 0 0;">
                            {{ $user->email ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="user-modal-tabs">
                <button id="tabBtnProfileData" class="user-modal-tab-btn active" type="button">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span>Data Profil</span>
                </button>
                <button id="tabBtnPasswordData" class="user-modal-tab-btn" type="button">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <span>Ubah Kata Sandi</span>
                </button>
            </div>

            <!-- Tab 1: Profile Form -->
            <div id="modalProfileTabContent" class="user-modal-body">
                <form id="formCustomerProfile" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--navy); margin-bottom: 0.35rem;">
                            Nama Lengkap <span style="color: #EF4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="text" id="custProfileName" required value="{{ $user->name ?? '' }}"
                                placeholder="Masukkan nama lengkap"
                                style="width: 100%; padding: 0.625rem 0.875rem 0.625rem 2.25rem; font-size: 0.78rem; border: 1px solid rgba(166,177,195,0.6); border-radius: 12px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2"
                                style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--navy); margin-bottom: 0.35rem;">
                            Email Akun <span style="color: #EF4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="email" id="custProfileEmail" required value="{{ $user->email ?? '' }}"
                                placeholder="Masukkan email aktif"
                                style="width: 100%; padding: 0.625rem 0.875rem 0.625rem 2.25rem; font-size: 0.78rem; border: 1px solid rgba(166,177,195,0.6); border-radius: 12px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2"
                                style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);">
                                <rect width="20" height="16" x="2" y="4" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--navy); margin-bottom: 0.35rem;">
                            Nomor Telepon / WhatsApp
                        </label>
                        <div style="position: relative;">
                            <input type="tel" id="custProfilePhone" value="{{ $user->phone ?? '' }}"
                                placeholder="Contoh: 081234567890"
                                style="width: 100%; padding: 0.625rem 0.875rem 0.625rem 2.25rem; font-size: 0.78rem; border: 1px solid rgba(166,177,195,0.6); border-radius: 12px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2"
                                style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--navy); margin-bottom: 0.35rem;">
                            Alamat Lengkap Pengiriman
                        </label>
                        <div style="position: relative;">
                            <textarea id="custProfileAddress" rows="3"
                                placeholder="Tuliskan jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                                style="width: 100%; padding: 0.625rem 0.875rem 0.625rem 2.25rem; font-size: 0.78rem; border: 1px solid rgba(166,177,195,0.6); border-radius: 12px; resize: none;">{{ $user->address ?? '' }}</textarea>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2" style="position: absolute; left: 0.75rem; top: 0.75rem;">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
                        <button id="btnCancelProfileModal" type="button"
                            style="padding: 0.625rem 1rem; border-radius: 12px; border: 1px solid rgba(166,177,195,0.6); font-size: 0.75rem; font-weight: 700; color: #4B5563; cursor: pointer;">
                            Batal
                        </button>
                        <button id="btnSubmitCustProfile" type="submit"
                            style="padding: 0.625rem 1.25rem; border-radius: 12px; background: var(--soft-blue); color: #FFFFFF; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: var(--shadow-xs);">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            <span>Simpan Profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 2: Password Form -->
            <div id="modalPasswordTabContent" class="user-modal-body" style="display: none;">
                <form id="formCustomerPassword" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--navy); margin-bottom: 0.35rem;">
                            Kata Sandi Saat Ini <span style="color: #EF4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="custCurrentPassword" required
                                placeholder="Masukkan sandi saat ini"
                                style="width: 100%; padding: 0.625rem 2.25rem 0.625rem 2.25rem; font-size: 0.78rem; border: 1px solid rgba(166,177,195,0.6); border-radius: 12px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2"
                                style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <button type="button" class="btn-pwd-eye" data-target="custCurrentPassword"
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--soft-blue); cursor: pointer;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--navy); margin-bottom: 0.35rem;">
                            Kata Sandi Baru <span style="color: #EF4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="custNewPassword" required minlength="8"
                                placeholder="Minimal 8 karakter"
                                style="width: 100%; padding: 0.625rem 2.25rem 0.625rem 2.25rem; font-size: 0.78rem; border: 1px solid rgba(166,177,195,0.6); border-radius: 12px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2"
                                style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <button type="button" class="btn-pwd-eye" data-target="custNewPassword"
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--soft-blue); cursor: pointer;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--navy); margin-bottom: 0.35rem;">
                            Konfirmasi Kata Sandi Baru <span style="color: #EF4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="custConfirmPassword" required minlength="8"
                                placeholder="Ulangi kata sandi baru"
                                style="width: 100%; padding: 0.625rem 2.25rem 0.625rem 2.25rem; font-size: 0.78rem; border: 1px solid rgba(166,177,195,0.6); border-radius: 12px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)"
                                stroke-width="2"
                                style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <button type="button" class="btn-pwd-eye" data-target="custConfirmPassword"
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--soft-blue); cursor: pointer;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
                        <button id="btnCancelPasswordModal" type="button"
                            style="padding: 0.625rem 1rem; border-radius: 12px; border: 1px solid rgba(166,177,195,0.6); font-size: 0.75rem; font-weight: 700; color: #4B5563; cursor: pointer;">
                            Batal
                        </button>
                        <button id="btnSubmitCustPassword" type="submit"
                            style="padding: 0.625rem 1.25rem; border-radius: 12px; background: var(--soft-blue); color: #FFFFFF; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: var(--shadow-xs);">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="m9 11 3 3L22 4" />
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                            </svg>
                            <span>Perbarui Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Data Injection -->
    <script>
        window.__BASE_URL__ = "{{ url('/') }}";
        window.__API_URL__ = "{{ url('api') }}";
        window.__CURRENT_USER__ = @json($user);
        window.__IS_LOGGED_IN__ = @json($isLoggedIn);
        window.__LOGIN_URL__ = "{{ route('login') }}";
        window.__ICA_PRODUCTS__ = @json($products);
        window.__ICA_BUNDLES__ = [
            {
                id: 'BND-01',
                name: 'Paket Sarapan Praktis Keluarga',
                items: ['P01', 'P05', 'P07'],
            },
            {
                id: 'BND-02',
                name: 'Paket Dimsum Party Time',
                items: ['P02', 'P03', 'P04'],
            },
            {
                id: 'BND-03',
                name: 'Paket Shabu & Grill Weekend',
                items: ['P12', 'P09', 'P11'],
            }
        ];
    </script>

    <!-- Pure Vanilla JS Storefront Logic -->
    <script src="{{ asset('js/store.js') }}?v={{ filemtime(public_path('js/store.js')) }}"></script>
</body>

</html>