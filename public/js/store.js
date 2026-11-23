/**
 * ICA FROZEN FOOD - STOREFRONT JAVASCRIPT (VANILLA JS)
 * Logika murni 100% identik dengan alur kerja sebelumnya tanpa React
 */

document.addEventListener('DOMContentLoaded', function () {
  // --- 1. Inisialisasi Data & State Awal Toko ---
  const baseUrl = window.__BASE_URL__ || '';
  const apiUrl = window.__API_URL__ || (baseUrl + '/api');
  let currentUser = window.__CURRENT_USER__ || null;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const products = window.__ICA_PRODUCTS__ || [];
  const bundles = window.__ICA_BUNDLES__ || [];
  let cart = [];
  let selectedCategory = 'Semua';
  let searchQuery = '';
  let activeDetailProduct = null;
  let detailQty = 1;

  // Global Toast Function (Sistem Notifikasi Konsisten & Matang)
  window.showToast = function (message, type = 'success') {
    let container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-msg ${type}`;

    let iconSvg = '';
    const isLogoutMsg = message && (message.toLowerCase().includes('keluar') || message.toLowerCase().includes('logout'));

    if (type === 'error') {
      iconSvg = `
        <div class="toast-icon-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
        </div>`;
    } else if (type === 'warning') {
      iconSvg = `
        <div class="toast-icon-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <circle cx="12" cy="17" r="1" fill="currentColor" stroke="none"></circle>
          </svg>
        </div>`;
    } else if (isLogoutMsg) {
      // Simbol resmi Logout yang matang & konsisten dengan desain web
      iconSvg = `
        <div class="toast-icon-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </div>`;
    } else if (type === 'info') {
      iconSvg = `
        <div class="toast-icon-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <circle cx="12" cy="8" r="1" fill="currentColor" stroke="none"></circle>
          </svg>
        </div>`;
    } else {
      iconSvg = `
        <div class="toast-icon-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="16 9 10.5 14.5 8 12"></polyline>
          </svg>
        </div>`;
    }

    toast.innerHTML = `${iconSvg}<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(12px)';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  };

  // Interceptor Otomatis: Tangani Sesi Habis (Session Expired 401/419) pada Fetch API
  const originalStoreFetch = window.fetch;
  window.fetch = async function (...args) {
    try {
      const response = await originalStoreFetch.apply(this, args);
      if (response && (response.status === 401 || response.status === 419)) {
        if (typeof window.showToast === 'function') {
          window.showToast('Sesi Anda telah berakhir demi keamanan. Mengalihkan ke login...', 'warning');
        }
        setTimeout(() => {
          window.location.href = '/login';
        }, 1500);
      }
      return response;
    } catch (err) {
      throw err;
    }
  };

  // Sinkronisasi status buka/tutup toko dari server
  async function syncStoreStatus() {
    try {
      const res = await fetch('/api/store-status');
      if (res.ok) {
        const data = await res.json();
        if (data && data.store_status) {
          window.__STORE_STATUS__ = data.store_status;
        }
      }
    } catch (e) {}
  }
  syncStoreStatus();
  setInterval(syncStoreStatus, 25000);

  // --- 5 Produk Paling Banyak Terjual (Sinkron dengan Panel Admin) ---
  const orders = window.__ICA_ORDERS__ || [];
  const productSales = {};

  orders.forEach((ord) => {
    // Abaikan pesanan yang berstatus Dibatalkan (identik dengan admin.blade.php)
    if (ord.status === 'Dibatalkan') return;

    let items = ord.items;
    if (typeof items === 'string') {
      try { items = JSON.parse(items); } catch (e) { items = []; }
    }
    if (Array.isArray(items)) {
      items.forEach((it) => {
        const id = it.id || null;
        const name = it.name || null;
        const qty = parseInt(it.qty || 1, 10);
        if (id) {
          productSales[id] = (productSales[id] || 0) + qty;
        } else if (name) {
          productSales[name] = (productSales[name] || 0) + qty;
        }
      });
    }
  });

  // Urutkan berdasarkan total terjual terbanyak (Top 5 Best Seller)
  let bestSellerProducts = [];
  const sortedSales = Object.entries(productSales).sort((a, b) => b[1] - a[1]);

  sortedSales.forEach(([key, qty]) => {
    if (qty > 0) {
      const found = products.find((p) => p.id === key || p.name === key);
      if (found && !bestSellerProducts.some((p) => p.id === found.id)) {
        bestSellerProducts.push({ ...found, totalSold: qty });
      }
    }
  });

  // Batasi maksimal 5 produk terlaris
  bestSellerProducts = bestSellerProducts.slice(0, 5);

  // Jika produk terjual belum mencapai 5 (misal transaksi masih sedikit), lengkapi dari katalog produk aktif
  if (bestSellerProducts.length < 5 && products.length > 0) {
    products.forEach((p) => {
      if (bestSellerProducts.length < 5 && !bestSellerProducts.some((bp) => bp.id === p.id)) {
        bestSellerProducts.push({ ...p, totalSold: 0 });
      }
    });
  }

  const heroProducts = bestSellerProducts;

  let currentHeroSlide = 0;
  let heroTimer = null;
  let isHeroHovered = false;

  // --- PENTING: Pengelolaan Keranjang Belanja Berdasarkan Sesi Akun ---
  // Jika pengunjung belum login (tamu) atau baru saja logout, kosongkan data keranjang
  // agar notifikasi angka pada keranjang otomatis bersih/hilang dan tidak tertinggal data lama.
  if (!window.__IS_LOGGED_IN__ || !currentUser) {
    try {
      localStorage.removeItem('ica_cart');
    } catch (e) {}
    cart = [];
  } else {
    // Jika pengguna sedang login, muat data keranjang yang tersimpan di browser
    try {
      const saved = localStorage.getItem('ica_cart');
      if (saved) cart = JSON.parse(saved);
    } catch (e) {
      cart = [];
    }
  }

  // Event Listener: Bersihkan data keranjang & sesi admin saat tombol logout ditekan
  document.querySelectorAll('form[action*="logout"]').forEach((form) => {
    form.addEventListener('submit', () => {
      try {
        localStorage.removeItem('ica_cart');
        sessionStorage.removeItem('ica_admin_active_tab');
      } catch (e) {}
    });
  });

  // --- 2. Efek Scroll Navbar & Scrollspy Menu Aktif ---
  const navbarWrapper = document.getElementById('navbarWrapper');
  const mainHeader = document.getElementById('mainHeader') || document.querySelector('.navbar-header-sticky');
  const navBtns = document.querySelectorAll('.nav-item-btn');
  const mobileDrawerMenu = document.getElementById('mobileDrawerMenu');
  const mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
  const mobileSearchRow = document.getElementById('mobileSearchRow');
  let isScrollTicking = false;
  let lastNavbarScrollY = window.scrollY || 0;

  function handleNavbarScroll() {
    if (!isScrollTicking) {
      window.requestAnimationFrame(() => {
        try {
          const y = window.scrollY || window.pageYOffset || 0;
          // Hysteresis threshold: status scrolled
          if (y > 45) {
            navbarWrapper?.classList.add('scrolled');
          } else if (y < 15) {
            navbarWrapper?.classList.remove('scrolled');
          }

          // Smart Auto-Hide (Headroom) khusus layar Mobile (< 768px)
          if (window.innerWidth < 768 && mainHeader) {
            const deltaY = y - lastNavbarScrollY;
            const isDrawerOpen = mobileDrawerMenu?.classList.contains('active');
            const isSearchOpen = mobileSearchRow?.classList.contains('open') || (mobileSearchRow && mobileSearchRow.style.display === 'block');

            if (isDrawerOpen || isSearchOpen || y <= 60) {
              // Selalu tampilkan jika dekat puncak halaman atau jika drawer/search sedang dibuka
              mainHeader.classList.remove('nav-hidden');
            } else if (deltaY > 6 && y > 80) {
              // Scroll ke bawah dengan jelas -> Sembunyikan navbar
              mainHeader.classList.add('nav-hidden');
            } else if (deltaY < -2) {
              // Scroll ke atas (bahkan sedikit saja) -> Munculkan navbar kembali secara instan!
              mainHeader.classList.remove('nav-hidden');
            }
          } else if (mainHeader) {
            mainHeader.classList.remove('nav-hidden');
          }

          lastNavbarScrollY = Math.max(0, y);

          // Scrollspy highlight
          const sections = ['home', 'katalog-section', 'rekomendasi-section', 'kontak-section'];
          let currentSec = 'home';
          const offset = 180;
          const isAtBottom = (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 140);

          if (isAtBottom) {
            currentSec = 'kontak-section';
          } else {
            sections.forEach((secId) => {
              const el = document.getElementById(secId);
              if (el) {
                const top = el.getBoundingClientRect().top + window.scrollY - offset;
                if (y >= top) {
                  currentSec = secId;
                }
              }
            });
          }

          navBtns.forEach((btn) => {
            const target = btn.getAttribute('data-target');
            btn.classList.toggle('active', target === currentSec);
          });
        } catch (err) {
          console.warn('handleNavbarScroll error:', err);
        } finally {
          isScrollTicking = false;
        }
      });
      isScrollTicking = true;
    }
  }

  window.addEventListener('scroll', handleNavbarScroll, { passive: true });
  handleNavbarScroll();

  // Nav Links click scroll
  navBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      const targetId = this.getAttribute('data-target');
      if (targetId) {
        const el = document.getElementById(targetId);
        if (el) {
          const yOffset = -90;
          const y = el.getBoundingClientRect().top + window.scrollY + yOffset;
          window.scrollTo({ top: y, behavior: 'smooth' });
        }
      }
    });
  });

  // --- 3. Dropdown Menu Pengguna & Drawer Mobile ---
  const btnUserMenu = document.getElementById('btnUserMenu');
  const userProfileDropdown = document.getElementById('userProfileDropdown');
  const btnMobileMenuToggle = document.getElementById('btnMobileMenuToggle');

  // Toggle User Profile Dropdown on Desktop & Auto-Close on Scroll
  if (btnUserMenu && userProfileDropdown) {
    btnUserMenu.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const isOpen = userProfileDropdown.classList.toggle('open');
      userProfileDropdown.style.display = isOpen ? 'block' : 'none';
    });

    document.addEventListener('click', (e) => {
      if (!userProfileDropdown.contains(e.target) && !btnUserMenu.contains(e.target)) {
        userProfileDropdown.classList.remove('open');
        userProfileDropdown.style.display = 'none';
      }
    });

    // Otomatis menutup dropdown profil saat halaman luar discroll
    window.addEventListener('scroll', () => {
      if (userProfileDropdown.classList.contains('open') || userProfileDropdown.style.display === 'block') {
        userProfileDropdown.classList.remove('open');
        userProfileDropdown.style.display = 'none';
      }
    }, { passive: true });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && (userProfileDropdown.classList.contains('open') || userProfileDropdown.style.display === 'block')) {
        userProfileDropdown.classList.remove('open');
        userProfileDropdown.style.display = 'none';
      }
    });
  }

  // Toggle Mobile Menu Drawer (Off-Canvas Slide-Over)
  const btnCloseMobileDrawer = document.getElementById('btnCloseMobileDrawer');

  function openMobileDrawer() {
    if (!mobileDrawerMenu) return;
    if (mobileSearchRow) {
      mobileSearchRow.classList.remove('open');
      mobileSearchRow.style.display = 'none';
      btnMobileSearchToggle?.classList.remove('active');
    }
    mobileDrawerMenu.classList.add('active');
    if (mobileDrawerOverlay) {
      mobileDrawerOverlay.style.display = 'block';
      mobileDrawerOverlay.classList.add('active');
    }
    document.body.style.overflow = 'hidden';
  }

  function closeMobileDrawer() {
    if (!mobileDrawerMenu) return;
    mobileDrawerMenu.classList.remove('active');
    if (mobileDrawerOverlay) {
      mobileDrawerOverlay.classList.remove('active');
      mobileDrawerOverlay.style.display = 'none';
    }
    document.body.style.overflow = '';
  }

  if (btnMobileMenuToggle && mobileDrawerMenu) {
    btnMobileMenuToggle.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (mobileDrawerMenu.classList.contains('active')) {
        closeMobileDrawer();
      } else {
        openMobileDrawer();
      }
    });

    if (btnCloseMobileDrawer) {
      btnCloseMobileDrawer.addEventListener('click', (e) => {
        e.preventDefault();
        closeMobileDrawer();
      });
    }

    if (mobileDrawerOverlay) {
      mobileDrawerOverlay.addEventListener('click', () => {
        closeMobileDrawer();
      });
    }

    // Close mobile drawer on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mobileDrawerMenu.classList.contains('active')) {
        closeMobileDrawer();
      }
    });

    // Mobile nav links click & smooth scroll
    mobileDrawerMenu.querySelectorAll('.mobile-nav-link[data-target]').forEach((link) => {
      link.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        closeMobileDrawer();
        if (targetId) {
          const el = document.getElementById(targetId);
          if (el) {
            const yOffset = -70;
            const y = el.getBoundingClientRect().top + window.scrollY + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
          }
        }
      });
    });
  }

  // --- 4. Modal Edit Profil & Sinkronisasi Database ---
  const userProfileModal = document.getElementById('userProfileModal');
  const btnOpenCustomerProfileModal = document.getElementById('btnOpenCustomerProfileModal');
  const btnMobileEditProfile = document.getElementById('btnMobileEditProfile');
  const btnCloseProfileModal = document.getElementById('btnCloseProfileModal');
  const btnCancelProfileModal = document.getElementById('btnCancelProfileModal');
  const btnCancelPasswordModal = document.getElementById('btnCancelPasswordModal');

  const tabBtnProfileData = document.getElementById('tabBtnProfileData');
  const tabBtnPasswordData = document.getElementById('tabBtnPasswordData');
  const modalProfileTabContent = document.getElementById('modalProfileTabContent');
  const modalPasswordTabContent = document.getElementById('modalPasswordTabContent');

  function openCustomerProfileModal() {
    closeMobileDrawer();
    if (userProfileDropdown) {
      userProfileDropdown.classList.remove('open');
      userProfileDropdown.style.display = 'none';
    }
    if (mobileDrawerMenu) {
      mobileDrawerMenu.style.display = 'none';
    }
    if (userProfileModal) {
      userProfileModal.style.display = 'flex';
    }
  }

  function closeCustomerProfileModal() {
    if (userProfileModal) {
      userProfileModal.style.display = 'none';
      if (formCustomerPassword) {
        formCustomerPassword.reset();
        formCustomerPassword.querySelectorAll('.btn-pwd-eye').forEach(btn => {
          const targetId = btn.getAttribute('data-target');
          const input = document.getElementById(targetId);
          if (input) input.type = 'password';
          const eyeOpen = btn.querySelector('.eye-open-icon');
          const eyeOff = btn.querySelector('.eye-off-icon');
          if (eyeOpen) eyeOpen.style.display = 'block';
          if (eyeOff) eyeOff.style.display = 'none';
        });
      }
    }
  }

  btnOpenCustomerProfileModal?.addEventListener('click', openCustomerProfileModal);
  btnMobileEditProfile?.addEventListener('click', openCustomerProfileModal);
  btnCloseProfileModal?.addEventListener('click', closeCustomerProfileModal);
  btnCancelProfileModal?.addEventListener('click', closeCustomerProfileModal);
  btnCancelPasswordModal?.addEventListener('click', closeCustomerProfileModal);

  // Close modal when clicking outside backdrop
  userProfileModal?.addEventListener('click', function (e) {
    if (e.target === userProfileModal) {
      closeCustomerProfileModal();
    }
  });

  // Tab switching in modal
  tabBtnProfileData?.addEventListener('click', function () {
    tabBtnProfileData.classList.add('active');
    tabBtnPasswordData?.classList.remove('active');
    if (modalProfileTabContent) modalProfileTabContent.style.display = 'block';
    if (modalPasswordTabContent) modalPasswordTabContent.style.display = 'none';
  });

  tabBtnPasswordData?.addEventListener('click', function () {
    tabBtnPasswordData.classList.add('active');
    tabBtnProfileData?.classList.remove('active');
    if (modalPasswordTabContent) modalPasswordTabContent.style.display = 'block';
    if (modalProfileTabContent) modalProfileTabContent.style.display = 'none';
  });

  // Password visibility eye toggles
  document.querySelectorAll('.btn-pwd-eye').forEach((btn) => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const targetInputId = this.getAttribute('data-target');
      const input = document.getElementById(targetInputId);
      if (!input) return;

      const isPwd = input.type === 'password';
      input.type = isPwd ? 'text' : 'password';

      const eyeOpen = this.querySelector('.eye-open-icon');
      const eyeOff = this.querySelector('.eye-off-icon');

      if (eyeOpen && eyeOff) {
        eyeOpen.style.display = isPwd ? 'none' : 'block';
        eyeOff.style.display = isPwd ? 'block' : 'none';
      }
    });
  });

  // Submit Profile Form via AJAX
  const formCustomerProfile = document.getElementById('formCustomerProfile');
  const btnSubmitCustProfile = document.getElementById('btnSubmitCustProfile');

  formCustomerProfile?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const name = document.getElementById('custProfileName')?.value.trim();
    const email = document.getElementById('custProfileEmail')?.value.trim();
    const phone = document.getElementById('custProfilePhone')?.value.trim();
    const address = document.getElementById('custProfileAddress')?.value.trim();

    if (!name || !email) {
      showToast('Nama dan email wajib diisi!', 'error');
      return;
    }

    if (btnSubmitCustProfile) {
      btnSubmitCustProfile.disabled = true;
      btnSubmitCustProfile.innerHTML = `<span>Menyimpan...</span>`;
    }

    try {
      const res = await fetch(`${baseUrl}/profile`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          _method: 'PATCH',
          name: name,
          email: email,
          phone: phone,
          address: address,
        }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Profil berhasil disimpan!');

        // Update UI dynamically
        const initials = (name.substring(0, 2) || 'U').toUpperCase();
        const navInitials = document.getElementById('navbarAvatarInitials');
        if (navInitials) navInitials.textContent = initials;
        const custFullName = document.getElementById('dropdownCustomerName');
        if (custFullName) custFullName.textContent = name;
        const custEmail = document.getElementById('dropdownCustomerEmail');
        if (custEmail) custEmail.textContent = email;
        const custPhone = document.getElementById('dropdownCustomerPhone');
        if (custPhone) custPhone.textContent = phone;
        const modalName = document.getElementById('profileModalName');
        if (modalName) modalName.textContent = name;
        const modalEmail = document.getElementById('profileModalEmail');
        if (modalEmail) modalEmail.textContent = email;

        // Auto-fill checkout fields
        const checkoutName = document.getElementById('custNameInput');
        if (checkoutName) checkoutName.value = name;
        const checkoutPhone = document.getElementById('custPhoneInput');
        if (checkoutPhone) checkoutPhone.value = phone;
        const checkoutAddr = document.getElementById('custAddressInput');
        if (checkoutAddr) checkoutAddr.value = address;

        closeCustomerProfileModal();
      } else {
        const errorMsg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Gagal menyimpan profil');
        showToast(errorMsg, 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    } finally {
      if (btnSubmitCustProfile) {
        btnSubmitCustProfile.disabled = false;
        btnSubmitCustProfile.innerHTML = `
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Simpan Profil</span>
        `;
      }
    }
  });

  // Submit Password Form via AJAX to Database
  const formCustomerPassword = document.getElementById('formCustomerPassword');
  const btnSubmitCustPassword = document.getElementById('btnSubmitCustPassword');

  formCustomerPassword?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const currentPassword = document.getElementById('custCurrentPassword')?.value;
    const newPassword = document.getElementById('custNewPassword')?.value;
    const confirmPassword = document.getElementById('custConfirmPassword')?.value;

    if (!currentPassword || !newPassword || !confirmPassword) {
      showToast('Semua kolom kata sandi wajib diisi!', 'error');
      return;
    }

    if (newPassword !== confirmPassword) {
      showToast('Konfirmasi kata sandi baru tidak cocok!', 'error');
      return;
    }

    if (newPassword.length < 8) {
      showToast('Kata sandi baru minimal 8 karakter!', 'error');
      return;
    }

    if (btnSubmitCustPassword) {
      btnSubmitCustPassword.disabled = true;
      btnSubmitCustPassword.innerHTML = `<span>Memperbarui...</span>`;
    }

    try {
      const res = await fetch(`${baseUrl}/password`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          _method: 'PUT',
          current_password: currentPassword,
          password: newPassword,
          password_confirmation: confirmPassword,
        }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Kata sandi berhasil diperbarui!');
        formCustomerPassword.reset();
        formCustomerPassword.querySelectorAll('.btn-pwd-eye').forEach(btn => {
          const targetId = btn.getAttribute('data-target');
          const input = document.getElementById(targetId);
          if (input) input.type = 'password';
          const eyeOpen = btn.querySelector('.eye-open-icon');
          const eyeOff = btn.querySelector('.eye-off-icon');
          if (eyeOpen) eyeOpen.style.display = 'block';
          if (eyeOff) eyeOff.style.display = 'none';
        });
        closeCustomerProfileModal();
      } else {
        let errorMsg = 'Kata sandi saat ini tidak cocok atau salah.';
        if (data.errors && data.errors.current_password) {
          const firstErr = data.errors.current_password[0];
          errorMsg = firstErr.includes('validation.current_password') || firstErr.toLowerCase().includes('current password')
            ? 'Kata sandi saat ini tidak cocok atau salah.'
            : firstErr;
        } else if (data.errors && data.errors.password) {
          errorMsg = data.errors.password[0];
        } else if (data.errors) {
          errorMsg = Object.values(data.errors).flat().join(', ');
        } else if (data.message) {
          errorMsg = data.message;
        }

        if (errorMsg.includes('validation.current_password')) {
          errorMsg = 'Kata sandi saat ini tidak cocok atau salah.';
        } else if (errorMsg.includes('validation.confirmed')) {
          errorMsg = 'Konfirmasi kata sandi baru tidak cocok.';
        } else if (errorMsg.includes('validation.min')) {
          errorMsg = 'Kata sandi baru minimal 8 karakter.';
        }

        showToast(errorMsg, 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    } finally {
      if (btnSubmitCustPassword) {
        btnSubmitCustPassword.disabled = false;
        btnSubmitCustPassword.innerHTML = `
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          <span>Perbarui Sandi</span>
        `;
      }
    }
  });

  // --- 5. Carousel Slider Banner Promosi (Hero Section) ---
  const heroSlideImg = document.getElementById('heroSlideImg');
  const heroSlideTitle = document.getElementById('heroSlideTitle');
  const heroSlidePrice = document.getElementById('heroSlidePrice');
  const heroSlideDotsWrap = document.getElementById('heroSlideDotsWrap');
  const btnHeroPrev = document.getElementById('btnHeroPrev');
  const btnHeroNext = document.getElementById('btnHeroNext');
  const btnHeroAddCart = document.getElementById('btnHeroAddCart');
  const heroShowcaseCard = document.getElementById('heroShowcaseCard');

  function renderHeroSlide(index) {
    if (heroProducts.length === 0) return;
    const p = heroProducts[index];
    if (!p) return;

    if (heroSlideImg) heroSlideImg.src = p.image || 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?auto=format&fit=crop&w=800&q=80';
    if (heroSlideTitle) heroSlideTitle.textContent = p.name;
    if (heroSlidePrice) heroSlidePrice.textContent = 'Rp ' + Number(p.price).toLocaleString('id-ID');

    if (heroSlideDotsWrap) {
      heroSlideDotsWrap.querySelectorAll('.slide-dot').forEach((dot, dIdx) => {
        dot.classList.toggle('active', dIdx === index);
      });
    }
  }

  function startHeroAutoPlay() {
    if (heroProducts.length <= 1) return;
    clearInterval(heroTimer);
    heroTimer = setInterval(() => {
      if (!isHeroHovered) {
        currentHeroSlide = (currentHeroSlide + 1) % heroProducts.length;
        renderHeroSlide(currentHeroSlide);
      }
    }, 5000);
  }

  if (heroProducts.length > 0) {
    if (heroSlideDotsWrap) {
      heroSlideDotsWrap.innerHTML = '';
      heroProducts.forEach((_, idx) => {
        const dot = document.createElement('button');
        dot.className = `slide-dot ${idx === 0 ? 'active' : ''}`;
        dot.addEventListener('click', () => {
          currentHeroSlide = idx;
          renderHeroSlide(currentHeroSlide);
        });
        heroSlideDotsWrap.appendChild(dot);
      });
    }

    renderHeroSlide(0);
    startHeroAutoPlay();

    if (heroShowcaseCard) {
      heroShowcaseCard.addEventListener('mouseenter', () => (isHeroHovered = true));
      heroShowcaseCard.addEventListener('mouseleave', () => (isHeroHovered = false));
    }

    if (btnHeroPrev) {
      btnHeroPrev.addEventListener('click', () => {
        currentHeroSlide = (currentHeroSlide - 1 + heroProducts.length) % heroProducts.length;
        renderHeroSlide(currentHeroSlide);
      });
    }

    if (btnHeroNext) {
      btnHeroNext.addEventListener('click', () => {
        currentHeroSlide = (currentHeroSlide + 1) % heroProducts.length;
        renderHeroSlide(currentHeroSlide);
      });
    }

    if (btnHeroAddCart) {
      btnHeroAddCart.addEventListener('click', () => {
        const prod = heroProducts[currentHeroSlide];
        if (prod) addToCart(prod, 1);
      });
    }
  }

  // --- 6. Pencarian Produk, Filter Kategori & Smart Pagination Katalog ---
  const PAGE_SIZE = 12;
  let displayedLimit = PAGE_SIZE;
  let currentSort = 'popular';

  const searchInput = document.getElementById('searchInput');
  const mobileSearchInput = document.getElementById('mobileSearchInput');
  const btnClearSearch = document.getElementById('btnClearSearch');
  const btnMobileSearchToggle = document.getElementById('btnMobileSearchToggle');
  const btnClearMobileSearch = document.getElementById('btnClearMobileSearch');
  const btnCloseMobileSearch = document.getElementById('btnCloseMobileSearch');
  const catalogGrid = document.getElementById('catalogGrid');
  const catalogCountText = document.getElementById('catalogCountText');
  const categoryPillContainer = document.getElementById('categoryPillContainer');
  const catalogSortSelect = document.getElementById('catalogSortSelect');
  const catalogEmptyState = document.getElementById('catalogEmptyState');
  const defaultEmptyTitle = catalogEmptyState?.querySelector('.catalog-empty-title')?.textContent?.trim() || 'Produk Segera Hadir!';
  const defaultEmptyDesc = catalogEmptyState?.querySelector('.catalog-empty-desc')?.textContent?.trim() || 'Stok kategori ini sedang disiapkan. Silakan pilih kategori lainnya.';
  const btnResetCatalogFilter = document.getElementById('btnResetCatalogFilter');
  const catalogLoadMoreWrap = document.getElementById('catalogLoadMoreWrap');
  const btnCatalogLoadMore = document.getElementById('btnCatalogLoadMore');
  const catalogProgressBar = document.getElementById('catalogProgressBar');
  const catalogProgressText = document.getElementById('catalogProgressText');
  const catalogShowingCount = document.getElementById('catalogShowingCount');
  const catalogTotalCount = document.getElementById('catalogTotalCount');

  // --- Skeleton Shimmer Helpers for Catalog Grid ---
  function renderCatalogSkeletons(count = 8) {
    return Array.from({ length: count })
      .map(
        () => `
      <div class="product-card-skeleton">
        <div class="skeleton-img-box skeleton-shimmer">
          <div class="skeleton-badge-tag skeleton-shimmer"></div>
        </div>
        <div class="skeleton-body-details">
          <div class="skeleton-text-block">
            <div class="skeleton-line title skeleton-shimmer"></div>
            <div class="skeleton-line subtitle skeleton-shimmer"></div>
          </div>
          <div class="skeleton-footer-row">
            <div class="skeleton-line price skeleton-shimmer"></div>
            <div class="skeleton-btn-pill skeleton-shimmer"></div>
          </div>
        </div>
      </div>
    `
      )
      .join('');
  }

  let filterShimmerTimer = null;
  function showCatalogShimmerFilter(callback) {
    if (!catalogGrid) {
      callback();
      return;
    }
    const originalHeight = catalogGrid.offsetHeight;
    if (originalHeight > 120) {
      catalogGrid.style.minHeight = `${originalHeight}px`;
    }

    const existingCards = Array.from(catalogGrid.querySelectorAll('.product-card-single'));
    existingCards.forEach((c) => {
      c.style.display = 'none';
      c.classList.remove('animate-in');
    });

    let skeletonWrap = document.getElementById('catalogSkeletonContainer');
    if (!skeletonWrap) {
      skeletonWrap = document.createElement('div');
      skeletonWrap.id = 'catalogSkeletonContainer';
      skeletonWrap.style.display = 'contents';
      catalogGrid.prepend(skeletonWrap);
    }
    skeletonWrap.innerHTML = renderCatalogSkeletons(8);
    skeletonWrap.style.display = 'contents';

    if (catalogEmptyState) catalogEmptyState.style.display = 'none';

    clearTimeout(filterShimmerTimer);
    filterShimmerTimer = setTimeout(() => {
      if (skeletonWrap) {
        skeletonWrap.innerHTML = '';
        skeletonWrap.style.display = 'none';
      }
      catalogGrid.style.minHeight = '';
      callback();
    }, 200);
  }

  function filterCatalog(resetPage = false) {
    if (resetPage) {
      displayedLimit = PAGE_SIZE;
    }

    const q = searchQuery.toLowerCase().trim();
    const allCards = Array.from(catalogGrid?.querySelectorAll('.product-card-single') || []);
    const matchingCards = [];

    // 1. Filter Kategori & Kata Kunci Pencarian
    allCards.forEach((card) => {
      const title = card.getAttribute('data-name') || card.querySelector('.card-title-heading')?.textContent.toLowerCase() || '';
      const cat = card.getAttribute('data-category') || card.querySelector('.badge-cat-tag')?.textContent.trim() || '';
      const desc = card.querySelector('.card-desc-snippet')?.textContent.toLowerCase() || '';

      const matchCategory = selectedCategory === 'Semua' || cat === selectedCategory;
      const matchSearch = !q || title.includes(q) || cat.toLowerCase().includes(q) || desc.includes(q);

      if (matchCategory && matchSearch) {
        matchingCards.push(card);
      } else {
        card.style.display = 'none';
        card.classList.remove('animate-in');
      }
    });

    // 2. Sortir Produk Sesuai Pilihan Pengguna
    matchingCards.sort((a, b) => {
      const priceA = parseFloat(a.getAttribute('data-price') || 0);
      const priceB = parseFloat(b.getAttribute('data-price') || 0);
      const nameA = a.getAttribute('data-name') || '';
      const nameB = b.getAttribute('data-name') || '';

      if (currentSort === 'price_asc') {
        return priceA - priceB;
      }
      if (currentSort === 'price_desc') {
        return priceB - priceA;
      }
      if (currentSort === 'name_asc') {
        return nameA.localeCompare(nameB);
      }
      if (currentSort === 'name_desc') {
        return nameB.localeCompare(nameA);
      }

      // 'popular': Utamakan produk dengan total penjualan tertinggi (dari productSales)
      const idA = a.getAttribute('data-id') || '';
      const idB = b.getAttribute('data-id') || '';
      const salesA = productSales[idA] || 0;
      const salesB = productSales[idB] || 0;
      if (salesB !== salesA) {
        return salesB - salesA;
      }

      // Jika penjualan sama, prioritaskan produk yang masih ada stok
      const stockA = parseInt(a.getAttribute('data-stock') || 0, 10);
      const stockB = parseInt(b.getAttribute('data-stock') || 0, 10);
      return stockB - stockA;
    });

    const totalMatches = matchingCards.length;

    // 3. Render Ulang Urutan & Batasi Tampilan (Batas displayedLimit)
    matchingCards.forEach((card, idx) => {
      if (catalogGrid) catalogGrid.appendChild(card); // Reorder node di DOM

      if (idx < displayedLimit) {
        if (card.style.display === 'none') {
          card.classList.add('animate-in');
        }
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
        card.classList.remove('animate-in');
      }
    });

    // 4. Update Header Counter
    if (catalogCountText) {
      catalogCountText.textContent = totalMatches;
    }

    // 5. Update Empty State (Jika 0 produk cocok)
    if (catalogEmptyState) {
      if (totalMatches === 0) {
        catalogEmptyState.style.display = 'block';
        const emptyTitle = catalogEmptyState.querySelector('.catalog-empty-title');
        const emptyDesc = catalogEmptyState.querySelector('.catalog-empty-desc');
        const trimmedSearch = searchQuery.trim();
        const isSearchMode = trimmedSearch.length > 0;

        if (isSearchMode) {
          if (emptyTitle) emptyTitle.textContent = 'Produk Tidak Ditemukan';
          if (emptyDesc) {
            emptyDesc.replaceChildren();
            emptyDesc.append('Tidak ada menu yang cocok dengan kata kunci "');
            const strong = document.createElement('strong');
            strong.textContent = trimmedSearch;
            emptyDesc.append(strong, '".');
          }
        } else {
          if (emptyTitle) emptyTitle.textContent = defaultEmptyTitle;
          if (emptyDesc) emptyDesc.textContent = defaultEmptyDesc;
        }
      } else {
        catalogEmptyState.style.display = 'none';
      }
    }

    // 6. Update Load More & Progress Bar
    if (catalogLoadMoreWrap) {
      if (totalMatches === 0) {
        catalogLoadMoreWrap.style.display = 'none';
      } else {
        catalogLoadMoreWrap.style.display = 'flex';
        const currentlyShown = Math.min(displayedLimit, totalMatches);
        if (catalogShowingCount) catalogShowingCount.textContent = currentlyShown;
        if (catalogTotalCount) catalogTotalCount.textContent = totalMatches;

        const percentage = Math.round((currentlyShown / totalMatches) * 100);
        if (catalogProgressBar) catalogProgressBar.style.width = percentage + '%';

        if (currentlyShown >= totalMatches) {
          if (btnCatalogLoadMore) btnCatalogLoadMore.style.display = 'none';
          if (catalogProgressText) {
            catalogProgressText.innerHTML = `✓ Semua <strong>${totalMatches}</strong> produk dalam kategori ini telah ditampilkan`;
          }
        } else {
          if (btnCatalogLoadMore) {
            btnCatalogLoadMore.style.display = 'inline-flex';
            const remaining = totalMatches - currentlyShown;
            const nextBatch = Math.min(PAGE_SIZE, remaining);
            const btnText = btnCatalogLoadMore.querySelector('span');
            if (btnText) btnText.textContent = `Muat Lebih Banyak Produk (+${nextBatch})`;
          }
          if (catalogProgressText) {
            catalogProgressText.innerHTML = `Menampilkan <strong>${currentlyShown}</strong> dari <strong>${totalMatches}</strong> produk`;
          }
        }
      }
    }
  }

  let searchScrollTimer = null;
  function handleSearchInput(val, immediateScroll = false) {
    searchQuery = val;
    if (searchInput) searchInput.value = val;
    if (mobileSearchInput) mobileSearchInput.value = val;
    if (btnClearSearch) btnClearSearch.style.display = val ? 'flex' : 'none';
    if (btnClearMobileSearch) btnClearMobileSearch.style.display = val ? 'flex' : 'none';

    if (val && val.trim().length > 0) {
      btnMobileSearchToggle?.classList.add('has-query');
    } else {
      btnMobileSearchToggle?.classList.remove('has-query');
    }

    filterCatalog(true); // Reset ke batch pertama saat mencari

    // Gulir otomatis ke bagian katalog saat pengguna sedang mencari produk
    if (val.trim().length > 0) {
      clearTimeout(searchScrollTimer);
      const doScroll = () => {
        const catalogSec = document.getElementById('katalog-section');
        if (catalogSec) {
          const rect = catalogSec.getBoundingClientRect();
          // Hanya gulir jika katalog belum berada di posisi nyaman pandangan mata
          if (rect.top < -50 || rect.top > 250) {
            const topPos = window.pageYOffset + rect.top - 85;
            window.scrollTo({ top: topPos, behavior: 'smooth' });
          }
        }
      };

      if (immediateScroll) {
        doScroll();
      } else {
        searchScrollTimer = setTimeout(doScroll, 400);
      }
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        handleSearchInput(e.target.value, true);
      }
    });
  }
  if (mobileSearchInput) {
    mobileSearchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
    mobileSearchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        handleSearchInput(e.target.value, true);
      }
    });
  }
  if (btnClearSearch) {
    btnClearSearch.addEventListener('click', () => handleSearchInput(''));
  }

  // Toggle & Controls untuk Mobile Search Bar yang Sejajar di Navbar
  if (btnMobileSearchToggle && mobileSearchRow) {
    btnMobileSearchToggle.addEventListener('click', function () {
      const isOpen = mobileSearchRow.classList.contains('open');
      if (isOpen) {
        mobileSearchRow.classList.remove('open');
        mobileSearchRow.style.display = 'none';
        btnMobileSearchToggle.classList.remove('active');
      } else {
        if (mobileDrawerMenu) {
          mobileDrawerMenu.style.display = 'none';
        }
        mobileSearchRow.style.display = 'block';
        mobileSearchRow.classList.add('open');
        btnMobileSearchToggle.classList.add('active');
        if (mobileSearchInput) {
          mobileSearchInput.focus();
          if (mobileSearchInput.value) {
            mobileSearchInput.select();
          }
        }
      }
    });
  }

  if (btnCloseMobileSearch && mobileSearchRow) {
    btnCloseMobileSearch.addEventListener('click', function () {
      mobileSearchRow.classList.remove('open');
      mobileSearchRow.style.display = 'none';
      btnMobileSearchToggle?.classList.remove('active');
    });
  }

  if (btnClearMobileSearch) {
    btnClearMobileSearch.addEventListener('click', function () {
      handleSearchInput('');
      mobileSearchInput?.focus();
    });
  }

  // Category Navigation: Mode Ringkas (1 Baris Scroll) vs Mode Tersebar/Dijabarkan (Expand)
  const categoryNavSection = document.getElementById('categoryNavSection');
  const categoryNavHeaderRow = document.getElementById('categoryNavHeaderRow');
  const categoryToggleWrap = document.getElementById('categoryToggleWrap');
  const btnToggleCategories = document.getElementById('btnToggleCategories');
  const btnToggleCategoriesTop = document.getElementById('btnToggleCategoriesTop');

  function setCategoriesExpanded(expanded) {
    if (!categoryNavSection) return;
    categoryNavSection.classList.toggle('is-expanded', expanded);
    if (categoryNavHeaderRow) {
      categoryNavHeaderRow.style.display = expanded ? 'flex' : 'none';
    }
    if (categoryToggleWrap) {
      categoryToggleWrap.style.display = expanded ? 'none' : 'block';
    }
    if (btnToggleCategories) {
      btnToggleCategories.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }
    if (btnToggleCategoriesTop) {
      btnToggleCategoriesTop.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }

    // Jika ditutup kembali (collapsed), auto-center pill kategori yang sedang aktif
    if (!expanded && categoryPillContainer) {
      updateCategoryScrollMask();
      const activePill = categoryPillContainer.querySelector('.btn-category-pill.selected');
      if (activePill) {
        setTimeout(() => {
          activePill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }, 60);
      }
    }
  }

  if (btnToggleCategories) {
    btnToggleCategories.addEventListener('click', () => setCategoriesExpanded(true));
  }
  if (btnToggleCategoriesTop) {
    btnToggleCategoriesTop.addEventListener('click', () => setCategoriesExpanded(false));
  }

  // Deteksi posisi geser agar efek blur/samar adaptif di kiri dan kanan saat mode 1 baris
  function updateCategoryScrollMask() {
    if (!categoryPillContainer) return;
    if (categoryNavSection && categoryNavSection.classList.contains('is-expanded')) {
      categoryPillContainer.classList.remove('is-scrolled', 'is-at-end');
      return;
    }
    const scrollLeft = categoryPillContainer.scrollLeft;
    const maxScroll = categoryPillContainer.scrollWidth - categoryPillContainer.clientWidth;
    const isScrolled = scrollLeft > 8;
    const isAtEnd = maxScroll > 0 && scrollLeft >= (maxScroll - 8);

    categoryPillContainer.classList.toggle('is-scrolled', isScrolled && !isAtEnd);
    categoryPillContainer.classList.toggle('is-at-end', isAtEnd);
  }

  // Pilih kategori via pill
  if (categoryPillContainer) {
    categoryPillContainer.querySelectorAll('.btn-category-pill').forEach((pill) => {
      pill.addEventListener('click', function () {
        categoryPillContainer.querySelectorAll('.btn-category-pill').forEach((p) => p.classList.remove('selected'));
        this.classList.add('selected');
        selectedCategory = this.getAttribute('data-cat') || 'Semua';
        showCatalogShimmerFilter(() => filterCatalog(true)); // Shimmer transisi halus saat ganti kategori

        // Jika dalam mode 1 baris, auto-center pill yang diklik
        if (!categoryNavSection || !categoryNavSection.classList.contains('is-expanded')) {
          this.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
      });
    });

    // Geser horizontal dengan roda mouse (Mouse Wheel Horizontal Scroll)
    categoryPillContainer.addEventListener('wheel', (e) => {
      if (categoryNavSection && categoryNavSection.classList.contains('is-expanded')) return;
      if (e.deltaY !== 0) {
        e.preventDefault();
        categoryPillContainer.scrollBy({ left: e.deltaY * 1.5, behavior: 'smooth' });
      }
    }, { passive: false });

    categoryPillContainer.addEventListener('scroll', updateCategoryScrollMask, { passive: true });
    window.addEventListener('resize', updateCategoryScrollMask, { passive: true });
    updateCategoryScrollMask();
  }

  // Custom Styled Sort Dropdown Component
  const customSortDropdown = document.getElementById('customSortDropdown');
  const customSortTrigger = document.getElementById('customSortTrigger');
  const customSortMenu = document.getElementById('customSortMenu');
  const customSortCurrentText = document.getElementById('customSortCurrentText');

  if (customSortTrigger && customSortMenu) {
    customSortTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = customSortMenu.classList.contains('show');
      customSortMenu.classList.toggle('show', !isOpen);
      customSortTrigger.classList.toggle('active', !isOpen);
      customSortTrigger.setAttribute('aria-expanded', !isOpen);
    });

    customSortMenu.querySelectorAll('.custom-sort-item').forEach((item) => {
      item.addEventListener('click', function () {
        const val = this.getAttribute('data-value');
        const text = this.querySelector('span')?.textContent || '';

        customSortMenu.querySelectorAll('.custom-sort-item').forEach((it) => {
          it.classList.remove('active');
          it.setAttribute('aria-selected', 'false');
        });
        this.classList.add('active');
        this.setAttribute('aria-selected', 'true');

        if (customSortCurrentText) customSortCurrentText.textContent = text;
        if (catalogSortSelect) {
          catalogSortSelect.value = val;
        }
        currentSort = val;
        filterCatalog(false);

        customSortMenu.classList.remove('show');
        customSortTrigger.classList.remove('active');
        customSortTrigger.setAttribute('aria-expanded', 'false');
      });
    });

    document.addEventListener('click', (e) => {
      if (customSortDropdown && !customSortDropdown.contains(e.target)) {
        customSortMenu.classList.remove('show');
        customSortTrigger.classList.remove('active');
        customSortTrigger.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Native Sort Dropdown Fallback Change
  if (catalogSortSelect) {
    catalogSortSelect.addEventListener('change', function () {
      currentSort = this.value;
      if (customSortCurrentText) {
        const selectedOpt = this.options[this.selectedIndex];
        if (selectedOpt) customSortCurrentText.textContent = selectedOpt.textContent;
      }
      filterCatalog(false); // Urutkan ulang produk yang sedang aktif
    });
  }

  // Button Load More Click (Dengan Shimmer Skeletons Placeholder)
  if (btnCatalogLoadMore) {
    btnCatalogLoadMore.addEventListener('click', function () {
      if (!catalogGrid) return;

      const skeletonWrap = document.createElement('div');
      skeletonWrap.id = 'loadMoreSkeletonWrap';
      skeletonWrap.style.display = 'contents';
      skeletonWrap.innerHTML = renderCatalogSkeletons(4);
      catalogGrid.appendChild(skeletonWrap);

      const btnSpan = btnCatalogLoadMore.querySelector('span');
      const originalText = btnSpan ? btnSpan.textContent : btnCatalogLoadMore.textContent;
      btnCatalogLoadMore.disabled = true;
      btnCatalogLoadMore.style.opacity = '0.7';
      if (btnSpan) btnSpan.textContent = 'Memuat Produk...';

      setTimeout(() => {
        skeletonWrap.remove();
        displayedLimit += PAGE_SIZE;
        filterCatalog(false);
        btnCatalogLoadMore.disabled = false;
        btnCatalogLoadMore.style.opacity = '1';
        if (btnSpan) btnSpan.textContent = originalText;
      }, 260);
    });
  }

  // Reset Filter Button on Empty State ("Lihat Semua Produk")
  if (btnResetCatalogFilter) {
    btnResetCatalogFilter.addEventListener('click', function () {
      searchQuery = '';
      if (searchInput) searchInput.value = '';
      if (mobileSearchInput) mobileSearchInput.value = '';
      if (btnClearSearch) btnClearSearch.style.display = 'none';
      if (btnClearMobileSearch) btnClearMobileSearch.style.display = 'none';
      btnMobileSearchToggle?.classList.remove('has-query');

      selectedCategory = 'Semua';
      if (categoryPillContainer) {
        categoryPillContainer.querySelectorAll('.btn-category-pill').forEach((p) => {
          p.classList.toggle('selected', p.getAttribute('data-cat') === 'Semua');
        });
        const semuaPill = categoryPillContainer.querySelector('.btn-category-pill[data-cat="Semua"]');
        if (semuaPill && (!categoryNavSection || !categoryNavSection.classList.contains('is-expanded'))) {
          semuaPill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
      }

      if (catalogSortSelect) {
        catalogSortSelect.value = 'popular';
      }
      if (customSortCurrentText) {
        customSortCurrentText.textContent = 'Paling Laris (Rekomendasi)';
      }
      if (customSortMenu) {
        customSortMenu.querySelectorAll('.custom-sort-item').forEach((it) => {
          it.classList.toggle('active', it.getAttribute('data-value') === 'popular');
          it.setAttribute('aria-selected', it.getAttribute('data-value') === 'popular');
        });
      }
      currentSort = 'popular';

      showCatalogShimmerFilter(() => filterCatalog(true));
    });
  }

  // Inisialisasi awal katalog saat halaman dimuat
  filterCatalog(true);

  // --- 7. Modal Detail Spesifikasi Produk ---
  const productDetailModal = document.getElementById('productDetailModal');
  const detailModalClose = document.getElementById('detailModalClose');
  const detailImage = document.getElementById('detailImage');
  const detailCategory = document.getElementById('detailCategory');
  const detailCategorySub = document.getElementById('detailCategorySub');
  const detailSku = document.getElementById('detailSku');
  const detailTitle = document.getElementById('detailTitle');
  const detailPrice = document.getElementById('detailPrice');
  const detailDesc = document.getElementById('detailDesc');
  const detailWeight = document.getElementById('detailWeight');
  const detailStockBadge = document.getElementById('detailStockBadge');
  const detailQtyDisplay = document.getElementById('detailQtyDisplay');
  const detailBtnText = document.getElementById('detailBtnText');
  const btnDetailMinus = document.getElementById('btnDetailMinus');
  const btnDetailPlus = document.getElementById('btnDetailPlus');
  const btnDetailAddToCart = document.getElementById('btnDetailAddToCart');

  window.openDetailModal = function (productId) {
    const prod = products.find((p) => p.id === productId);
    if (!prod) return;

    activeDetailProduct = prod;
    detailQty = 1;
    if (detailQtyDisplay) detailQtyDisplay.textContent = detailQty;
    if (detailBtnText) detailBtnText.textContent = `+ Keranjang (${detailQty})`;

    if (detailImage) detailImage.src = prod.image || '/images/ica_logo.png';
    if (detailCategory) detailCategory.textContent = prod.category;
    if (detailCategorySub) detailCategorySub.textContent = prod.category;
    if (detailSku) detailSku.textContent = 'SKU: ' + prod.id;
    if (detailTitle) detailTitle.textContent = prod.name;
    if (detailPrice) detailPrice.textContent = 'Rp ' + Number(prod.price).toLocaleString('id-ID');
    if (detailDesc) detailDesc.textContent = prod.description || 'Makanan beku berkualitas tinggi, dikemas kedap udara (vacuum sealed food grade).';
    if (detailWeight) detailWeight.textContent = prod.weight || '500g';

    if (detailStockBadge) {
      if (prod.stock <= 0) {
        detailStockBadge.style.display = 'block';
        detailStockBadge.textContent = 'Stok Habis';
      } else {
        detailStockBadge.style.display = 'none';
      }
    }

    if (productDetailModal) productDetailModal.classList.add('active');
  };

  window.closeDetailModal = function () {
    if (productDetailModal) productDetailModal.classList.remove('active');
    activeDetailProduct = null;
  };

  if (detailModalClose) detailModalClose.addEventListener('click', window.closeDetailModal);
  if (productDetailModal) {
    productDetailModal.addEventListener('click', (e) => {
      if (e.target === productDetailModal) window.closeDetailModal();
    });
  }

  if (btnDetailMinus) {
    btnDetailMinus.addEventListener('click', () => {
      if (detailQty > 1) {
        detailQty--;
        if (detailQtyDisplay) detailQtyDisplay.textContent = detailQty;
        if (detailBtnText) detailBtnText.textContent = `+ Keranjang (${detailQty})`;
      }
    });
  }

  if (btnDetailPlus) {
    btnDetailPlus.addEventListener('click', () => {
      if (activeDetailProduct && detailQty < activeDetailProduct.stock) {
        detailQty++;
        if (detailQtyDisplay) detailQtyDisplay.textContent = detailQty;
        if (detailBtnText) detailBtnText.textContent = `+ Keranjang (${detailQty})`;
      } else if (activeDetailProduct) {
        window.showToast(`Jumlah melebihi stok yang tersedia (${activeDetailProduct.stock} unit)`, 'error');
      }
    });
  }

  if (btnDetailAddToCart) {
    btnDetailAddToCart.addEventListener('click', () => {
      if (activeDetailProduct) {
        const ok = addToCart(activeDetailProduct, detailQty);
        if (ok) window.closeDetailModal();
      }
    });
  }

  // --- 8. Manajemen Keranjang Belanja & Drawer Samping ---
  const cartDrawerOverlay = document.getElementById('cartDrawerOverlay');
  const cartDrawer = document.getElementById('cartDrawer');
  const btnOpenCart = document.getElementById('btnOpenCart');
  const btnCloseCart = document.getElementById('btnCloseCart');
  const cartCountBadge = document.getElementById('cartCountBadge');
  const cartSubtotalText = document.getElementById('cartSubtotalText');
  const drawerCartCountLabel = document.getElementById('drawerCartCountLabel');
  const cartItemsWrap = document.getElementById('cartItemsWrap');
  const cartEmptyStateWrap = document.getElementById('cartEmptyStateWrap');
  const cartActiveContentWrap = document.getElementById('cartActiveContentWrap');
  const cartFooterSection = document.getElementById('cartFooterSection');
  const btnEmptyStartShopping = document.getElementById('btnEmptyStartShopping');
  const drawerSubtotalAmount = document.getElementById('drawerSubtotalAmount');
  const drawerIceFeeAmount = document.getElementById('drawerIceFeeAmount');
  const drawerGrandTotalAmount = document.getElementById('drawerGrandTotalAmount');
  const btnSubmitOrder = document.getElementById('btnSubmitOrder');
  const btnSubmitOrderText = document.getElementById('btnSubmitOrderText');

  const deliveryRadioKurir = document.getElementById('deliveryRadioKurir');
  const deliveryRadioAmbil = document.getElementById('deliveryRadioAmbil');
  const addressFieldWrap = document.getElementById('addressFieldWrap');
  const custNameInput = document.getElementById('custNameInput');
  const custPhoneInput = document.getElementById('custPhoneInput');
  const custAddressInput = document.getElementById('custAddressInput');
  const custPaymentInput = document.getElementById('custPaymentInput');
  const optPayCash = document.getElementById('optPayCash');
  const courierPaymentNotice = document.getElementById('courierPaymentNotice');

  // Payment Modal Elements
  const paymentModal = document.getElementById('paymentModal');
  const btnPaymentClose = document.getElementById('btnPaymentClose');
  const paymentModalAmount = document.getElementById('paymentModalAmount');
  const btnCopyPaymentAmount = document.getElementById('btnCopyPaymentAmount');
  const paymentModalMethodBadge = document.getElementById('paymentModalMethodBadge');
  const paymentDynamicContent = document.getElementById('paymentDynamicContent');
  const paymentVerifyingBox = document.getElementById('paymentVerifyingBox');
  const paymentSuccessNotice = document.getElementById('paymentSuccessNotice');
  const paymentModalFooter = document.getElementById('paymentModalFooter');
  const btnConfirmPayment = document.getElementById('btnConfirmPayment');
  const btnConfirmPaymentText = document.getElementById('btnConfirmPaymentText');

  if (btnCopyPaymentAmount) {
    btnCopyPaymentAmount.addEventListener('click', function () {
      if (!activePendingOrder || !activePendingOrder.total) return;
      window.copyAmount(activePendingOrder.total, this);
    });
  }

  let activePendingOrder = null;

  // Sync Payment Options based on Delivery Method (Courier delivery cannot pay cash in store)
  function syncPaymentMethodsWithDelivery() {
    const isKurir = document.querySelector('input[name="delivery_method"]:checked')?.value === 'kurir';
    if (isKurir) {
      if (optPayCash) {
        optPayCash.disabled = true;
        optPayCash.hidden = true;
      }
      if (custPaymentInput && custPaymentInput.value === 'Bayar Tunai di Kasir Toko') {
        custPaymentInput.value = 'QRIS Instan';
      }
      if (courierPaymentNotice) courierPaymentNotice.style.display = 'block';
    } else {
      if (optPayCash) {
        optPayCash.disabled = false;
        optPayCash.hidden = false;
      }
      if (courierPaymentNotice) courierPaymentNotice.style.display = 'none';
    }
  }

  function saveCart() {
    try {
      localStorage.setItem('ica_cart', JSON.stringify(cart));
    } catch (e) {}
    renderCart();
  }

  window.addToCartById = function (productId, qty = 1) {
    const prod = products.find((p) => p.id === productId);
    if (prod) addToCart(prod, qty);
  };

  // --- Modal Konfirmasi Login Tamu (Guest Auth In-App Modal) ---
  const guestLoginModal = document.getElementById('guestLoginModal');
  const btnCloseGuestLoginModal = document.getElementById('btnCloseGuestLoginModal');
  const btnCancelGuestLogin = document.getElementById('btnCancelGuestLogin');
  const guestLoginModalDesc = document.getElementById('guestLoginModalDesc');

  window.promptGuestLogin = function (customMessage) {
    if (!guestLoginModal) {
      if (confirm(customMessage || 'Silakan masuk ke akun Anda terlebih dahulu untuk mulai memesan produk di Ica Frozen Food.')) {
        window.location.href = window.__LOGIN_URL__ || '/login';
      }
      return;
    }
    if (guestLoginModalDesc && customMessage) {
      guestLoginModalDesc.textContent = customMessage;
    }
    guestLoginModal.style.display = 'flex';
    guestLoginModal.classList.add('active');
  };

  window.closeGuestLoginModal = function () {
    if (guestLoginModal) {
      guestLoginModal.classList.remove('active');
      guestLoginModal.style.display = 'none';
    }
  };

  if (btnCloseGuestLoginModal) {
    btnCloseGuestLoginModal.addEventListener('click', window.closeGuestLoginModal);
  }
  if (btnCancelGuestLogin) {
    btnCancelGuestLogin.addEventListener('click', window.closeGuestLoginModal);
  }
  if (guestLoginModal) {
    guestLoginModal.addEventListener('click', (e) => {
      if (e.target === guestLoginModal) {
        window.closeGuestLoginModal();
      }
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && guestLoginModal && guestLoginModal.classList.contains('active')) {
      window.closeGuestLoginModal();
    }
  });

  function addToCart(product, qty = 1, showNotif = true) {
    // Validasi Toko Tutup: Blokir penambahan produk ke keranjang sejak awal
    if (window.__STORE_STATUS__ && window.__STORE_STATUS__.is_open === false) {
      window.showToast('Mohon maaf, operasional toko sedang TUTUP SEMENTARA saat ini. Pemesanan dibuka kembali pada jam 08.00 – 21.00 WITA.', 'error');
      return false;
    }

    // Validasi Tamu: Wajib login sebelum dapat membeli produk
    if (!window.__CURRENT_USER__ || !window.__IS_LOGGED_IN__) {
      window.promptGuestLogin('Silakan masuk ke akun Anda terlebih dahulu untuk menambahkan produk ke keranjang belanja.');
      return false;
    }

    if (product.stock <= 0) {
      window.showToast('Maaf, produk ini sedang habis!', 'error');
      return false;
    }

    const idx = cart.findIndex((it) => it.product.id === product.id);
    if (idx > -1) {
      const newQty = cart[idx].qty + qty;
      if (newQty > product.stock) {
        window.showToast(`Stok maksimal produk hanya ${product.stock} unit`, 'error');
        return false;
      }
      cart[idx].qty = newQty;
    } else {
      cart.push({ product, qty });
    }

    saveCart();
    // Notification feedback & pastikan navbar mobile terlihat untuk feedback keranjang
    mainHeader?.classList.remove('nav-hidden');
    btnOpenCart?.classList.add('animate-bounce');
    setTimeout(() => btnOpenCart?.classList.remove('animate-bounce'), 800);

    // Tampilkan notifikasi toast dengan style konsisten web
    if (showNotif) {
      window.showToast(`${product.name} berhasil ditambahkan ke keranjang!`);
    }
    return true;
  }

  window.updateCartQty = function (productId, delta) {
    const item = cart.find((it) => it.product.id === productId);
    if (!item) return;

    const newQty = item.qty + delta;
    if (newQty <= 0) {
      window.removeCartItem(productId);
      return;
    }
    if (newQty > item.product.stock) {
      window.showToast(`Stok maksimal hanya ${item.product.stock} unit`, 'error');
      return;
    }

    item.qty = newQty;
    saveCart();
  };

  window.removeCartItem = function (productId) {
    cart = cart.filter((it) => it.product.id !== productId);
    saveCart();
  };

  window.clearCart = function () {
    if (confirm('Kosongkan semua item dari keranjang?')) {
      cart = [];
      saveCart();
    }
  };

  function getCartSubtotal() {
    return cart.reduce((sum, it) => sum + it.product.price * it.qty, 0);
  }

  function getCartTotalCount() {
    return cart.reduce((sum, it) => sum + it.qty, 0);
  }

  function renderCart() {
    const count = getCartTotalCount();
    const subtotal = getCartSubtotal();

    // Update Navbar elements
    if (cartCountBadge) {
      cartCountBadge.textContent = count;
      cartCountBadge.style.display = count > 0 ? 'flex' : 'none';
    }
    const bottomNavCartBadge = document.getElementById('bottomNavCartBadge');
    if (bottomNavCartBadge) {
      bottomNavCartBadge.textContent = count;
      bottomNavCartBadge.style.display = count > 0 ? 'flex' : 'none';
    }
    if (cartSubtotalText) {
      cartSubtotalText.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    }
    if (drawerCartCountLabel) {
      drawerCartCountLabel.textContent = `${count} Produk Makanan Beku`;
    }

    syncPaymentMethodsWithDelivery();

    // EMPTY CART STATE (Point 1: Cukup menampilkan info keranjang kosong seperti gambar)
    if (cart.length === 0) {
      if (cartEmptyStateWrap) cartEmptyStateWrap.style.display = 'flex';
      if (cartActiveContentWrap) cartActiveContentWrap.style.display = 'none';
      if (cartFooterSection) cartFooterSection.style.display = 'none';
      return;
    }

    // ACTIVE CART STATE (Items, Delivery Method, Customer Data, and Footer visible)
    if (cartEmptyStateWrap) cartEmptyStateWrap.style.display = 'none';
    if (cartActiveContentWrap) cartActiveContentWrap.style.display = 'flex';
    if (cartFooterSection) cartFooterSection.style.display = 'flex';

    // Delivery method fee calculation
    const isKurir = document.querySelector('input[name="delivery_method"]:checked')?.value === 'kurir';
    const iceFee = isKurir ? 5000 : 0;
    const grandTotal = subtotal + iceFee;

    if (drawerSubtotalAmount) drawerSubtotalAmount.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    if (drawerIceFeeAmount) drawerIceFeeAmount.textContent = isKurir ? 'Rp 5.000' : 'Gratis';
    if (drawerGrandTotalAmount) drawerGrandTotalAmount.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

    // Notifikasi & status tombol checkout jika toko sedang tutup
    const isStoreOpen = !window.__STORE_STATUS__ || window.__STORE_STATUS__.is_open !== false;
    let drawerClosedNotice = document.getElementById('drawerClosedNotice');
    if (!isStoreOpen) {
      if (!drawerClosedNotice && cartFooterSection) {
        drawerClosedNotice = document.createElement('div');
        drawerClosedNotice.id = 'drawerClosedNotice';
        drawerClosedNotice.className = 'drawer-store-closed-banner';
        drawerClosedNotice.innerHTML = `
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Toko sedang TUTUP sementara. Checkout dinonaktifkan hingga toko buka kembali (08.00 – 21.00 WITA).</span>
        `;
        cartFooterSection.insertBefore(drawerClosedNotice, cartFooterSection.firstChild);
      }
      if (btnSubmitOrder) {
        btnSubmitOrder.disabled = true;
        btnSubmitOrder.classList.add('disabled-store-closed');
      }
      if (btnSubmitOrderText) {
        btnSubmitOrderText.textContent = 'Toko Sedang Tutup';
      }
    } else {
      if (drawerClosedNotice) drawerClosedNotice.remove();
      if (btnSubmitOrder) {
        btnSubmitOrder.disabled = false;
        btnSubmitOrder.classList.remove('disabled-store-closed');
      }
      if (btnSubmitOrderText) {
        btnSubmitOrderText.textContent = 'Lanjut ke Pembayaran';
      }
    }

    if (!cartItemsWrap) return;

    let itemsHtml = `
      <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; font-weight: 700; color: #6B7280; margin-bottom: 0.5rem;">
        <span>DAFTAR ITEM (${count})</span>
        <button onclick="window.clearCart()" style="color: var(--red); font-size: 11px; cursor: pointer; background: transparent; border: none; font-weight: 600;">Kosongkan</button>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.75rem;">
    `;

    cart.forEach((it) => {
      itemsHtml += `
        <div style="background-color: #FFFFFF; padding: 0.75rem; border-radius: 16px; border: 1px solid rgba(166, 177, 195, 0.5); box-shadow: var(--shadow-2xs); display: flex; gap: 0.75rem; align-items: center;">
          <img src="${it.product.image || '/images/ica_logo.png'}" alt="${it.product.name}" style="width: 3.75rem; height: 3.75rem; border-radius: 12px; object-fit: cover; flex-shrink: 0;" onerror="this.src='/images/ica_logo.png'">
          <div style="flex: 1; min-width: 0;">
            <h4 style="font-weight: 700; font-size: 0.8125rem; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;">${it.product.name}</h4>
            <p style="font-size: 11.5px; color: var(--soft-blue); font-weight: 700; margin-top: 2px;">Rp ${Number(it.product.price).toLocaleString('id-ID')}</p>
            <div style="display: inline-flex; align-items: center; gap: 0.45rem; margin-top: 0.45rem;">
              <button onclick="window.updateCartQty('${it.product.id}', -1)" style="width: 1.75rem; height: 1.75rem; border-radius: 8px; background-color: #F8FAFC; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; justify-content: center; color: var(--navy); padding: 0; cursor: pointer; touch-action: manipulation; transition: all 0.15s ease;" title="Kurangi 1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
              </button>
              <span style="font-size: 0.8125rem; font-weight: 800; color: var(--navy); min-width: 1.5rem; text-align: center; line-height: 1;">${it.qty}</span>
              <button onclick="window.updateCartQty('${it.product.id}', 1)" style="width: 1.75rem; height: 1.75rem; border-radius: 8px; background-color: #F8FAFC; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; justify-content: center; color: var(--navy); padding: 0; cursor: pointer; touch-action: manipulation; transition: all 0.15s ease;" title="Tambah 1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              </button>
            </div>
          </div>
          <div style="text-align: right;">
            <p style="font-weight: 700; font-size: 0.75rem; color: var(--navy);">Rp ${(it.qty * it.product.price).toLocaleString('id-ID')}</p>
            <button onclick="window.removeCartItem('${it.product.id}')" style="color: #9CA3AF; width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center; margin-top: 0.25rem; cursor: pointer; background: transparent; border: none; border-radius: 8px;" title="Hapus item">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
          </div>
        </div>
      `;
    });

    itemsHtml += `</div>`;
    cartItemsWrap.innerHTML = itemsHtml;
  }

  window.openCartDrawer = function () {
    // Validasi Toko Tutup: Blokir akses keranjang sejak awal saat toko tutup
    if (window.__STORE_STATUS__ && window.__STORE_STATUS__.is_open === false) {
      window.showToast('Mohon maaf, operasional toko sedang TUTUP SEMENTARA saat ini. Keranjang belanja & pemesanan dibuka kembali pada jam 08.00 – 21.00 WITA.', 'error');
      return;
    }

    // Validasi Tamu: Wajib login sebelum dapat mengakses keranjang (disamakan dengan saat klik icon keranjang di katalog)
    if (!window.__CURRENT_USER__ || !window.__IS_LOGGED_IN__) {
      window.promptGuestLogin('Silakan masuk ke akun Anda terlebih dahulu untuk membuka keranjang belanja.');
      return;
    }

    renderCart();
    if (cartDrawerOverlay) cartDrawerOverlay.classList.add('active');
    if (cartDrawer) cartDrawer.classList.add('active');
  };

  window.closeCartDrawer = function () {
    if (cartDrawerOverlay) cartDrawerOverlay.classList.remove('active');
    if (cartDrawer) cartDrawer.classList.remove('active');
  };

  if (btnOpenCart) btnOpenCart.addEventListener('click', window.openCartDrawer);
  if (btnCloseCart) btnCloseCart.addEventListener('click', window.closeCartDrawer);
  if (cartDrawerOverlay) cartDrawerOverlay.addEventListener('click', window.closeCartDrawer);

  // Empty cart button: Mulai Belanja -> close drawer & scroll to catalog
  if (btnEmptyStartShopping) {
    btnEmptyStartShopping.addEventListener('click', () => {
      window.closeCartDrawer();
      const katalogEl = document.getElementById('katalog-section');
      if (katalogEl) {
        const yOffset = -90;
        const y = katalogEl.getBoundingClientRect().top + window.scrollY + yOffset;
        window.scrollTo({ top: y, behavior: 'smooth' });
      }
    });
  }

  // Delivery Radio events
  document.querySelectorAll('input[name="delivery_method"]').forEach((radio) => {
    radio.addEventListener('change', () => {
      const isKurir = radio.value === 'kurir';
      if (deliveryRadioKurir && deliveryRadioAmbil) {
        if (isKurir) {
          deliveryRadioKurir.style.borderColor = 'var(--soft-blue)';
          deliveryRadioKurir.style.backgroundColor = 'rgba(235, 240, 246, 0.6)';
          deliveryRadioAmbil.style.borderColor = '#E5E7EB';
          deliveryRadioAmbil.style.backgroundColor = 'transparent';
        } else {
          deliveryRadioAmbil.style.borderColor = 'var(--soft-blue)';
          deliveryRadioAmbil.style.backgroundColor = 'rgba(235, 240, 246, 0.6)';
          deliveryRadioKurir.style.borderColor = '#E5E7EB';
          deliveryRadioKurir.style.backgroundColor = 'transparent';
        }
      }
      if (addressFieldWrap) {
        addressFieldWrap.style.display = isKurir ? 'block' : 'none';
      }
      syncPaymentMethodsWithDelivery();
      renderCart();
    });
  });

  // --- 9. Alur Checkout & Modal Pembayaran (QRIS, Transfer, Tunai) ---

  // Step 1: User clicks "Lanjut ke Pembayaran" in Cart Drawer
  if (btnSubmitOrder) {
    btnSubmitOrder.addEventListener('click', () => {
      // Validasi Tamu: Wajib login sebelum checkout
      if (!window.__CURRENT_USER__ || !window.__IS_LOGGED_IN__) {
        window.promptGuestLogin('Silakan masuk ke akun Anda terlebih dahulu untuk menyelesaikan pemesanan produk.');
        return;
      }

      if (cart.length === 0) return;

      // Validasi Toko Tutup: Blokir checkout
      if (window.__STORE_STATUS__ && window.__STORE_STATUS__.is_open === false) {
        window.showToast('Mohon maaf, toko sedang TUTUP saat ini. Checkout belum dapat dilakukan sampai toko buka kembali (08.00 – 21.00 WITA).', 'error');
        return;
      }

      const name = custNameInput?.value.trim();
      const phone = custPhoneInput?.value.trim();
      const isKurir = document.querySelector('input[name="delivery_method"]:checked')?.value === 'kurir';
      const address = custAddressInput?.value.trim();
      const paymentMethod = custPaymentInput?.value || 'QRIS Instan';

      if (!name) {
        alert('Mohon masukkan nama lengkap pemesan');
        custNameInput?.focus();
        return;
      }
      if (!phone) {
        alert('Mohon masukkan nomor WhatsApp pemesan');
        custPhoneInput?.focus();
        return;
      }
      if (isKurir && !address) {
        alert('Mohon masukkan alamat tujuan pengantaran kurir');
        custAddressInput?.focus();
        return;
      }

      // Point 3: Validation: If Courier delivery, cash in store is strictly forbidden
      if (isKurir && paymentMethod === 'Bayar Tunai di Kasir Toko') {
        alert('Metode diantar kurir tidak bisa bayar di toko. Silakan pilih QRIS atau Transfer Bank.');
        if (custPaymentInput) custPaymentInput.value = 'QRIS Instan';
        return;
      }

      const subtotal = getCartSubtotal();
      const shippingFee = isKurir ? 5000 : 0;
      const total = subtotal + shippingFee;

      const orderPayload = {
        id: 'ORD-ICA-' + Math.floor(1000 + Math.random() * 9000),
        customer_name: name,
        customer_phone: phone,
        address: isKurir ? address : 'Ambil di Toko Ica Frozen Food',
        method: isKurir ? 'Via kurir' : 'Ambil di Toko',
        payment_method: paymentMethod,
        items: cart.map((it) => ({
          id: it.product.id,
          name: it.product.name,
          price: it.product.price,
          qty: it.qty,
        })),
        subtotal: subtotal,
        shipping_fee: shippingFee,
        ice_fee: shippingFee,
        total: total,
        channel: 'Online Store',
      };

      openPaymentModal(orderPayload);
    });
  }

  let selectedProofFile = null;
  let paymentCountdownInterval = null;
  const paymentCountdownTimerEl = document.getElementById('paymentCountdownTimer');
  const paymentTimerBanner = document.getElementById('paymentTimerBanner');

  function startPaymentCountdown(durationMinutes = 15) {
    if (paymentCountdownInterval) clearInterval(paymentCountdownInterval);
    if (!paymentCountdownTimerEl || !paymentTimerBanner) return;

    paymentTimerBanner.style.display = 'flex';
    let remainingSeconds = durationMinutes * 60;

    function tick() {
      const mins = Math.floor(remainingSeconds / 60);
      const secs = remainingSeconds % 60;
      paymentCountdownTimerEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

      if (remainingSeconds <= 0) {
        clearInterval(paymentCountdownInterval);
        paymentCountdownTimerEl.textContent = '00:00';
        window.showToast('Batas waktu pembayaran telah habis. Silakan buat pesanan ulang.', 'error');
        if (btnConfirmPayment) {
          btnConfirmPayment.disabled = true;
          btnConfirmPayment.style.opacity = '0.5';
          btnConfirmPayment.style.cursor = 'not-allowed';
        }
        return;
      }
      remainingSeconds--;
    }

    tick();
    paymentCountdownInterval = setInterval(tick, 1000);
  }

  function stopPaymentCountdown() {
    if (paymentCountdownInterval) {
      clearInterval(paymentCountdownInterval);
      paymentCountdownInterval = null;
    }
    if (paymentTimerBanner) paymentTimerBanner.style.display = 'none';
    if (btnConfirmPayment) {
      btnConfirmPayment.disabled = false;
      btnConfirmPayment.style.opacity = '1';
      btnConfirmPayment.style.cursor = 'pointer';
    }
  }

  function renderProofUploadView() {
    return `
      <div class="payment-proof-upload-box">
        <div class="payment-proof-header">
          <div style="display: flex; align-items: center; gap: 0.4rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)" stroke-width="2.2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
            <strong style="color: var(--navy); font-size: 0.8rem;">Unggah Bukti Transfer / Pembayaran</strong>
          </div>
          <span class="proof-required-tag">*Wajib Diunggah</span>
        </div>
        <p class="payment-proof-sub">Silakan unggah tangkapan layar (screenshot) atau foto struk pembayaran Anda agar divalidasi oleh kasir.</p>

        <input type="file" id="modalPaymentProofInput" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;">

        <div id="paymentProofDropzone" class="payment-proof-dropzone">
          <div id="paymentProofPlaceholder" class="payment-proof-placeholder">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--soft-blue)" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span style="font-size: 11px; font-weight: 700; color: var(--navy); margin-top: 3px;">Pilih Gambar Bukti Transfer</span>
            <span style="font-size: 9.5px; color: #94A3B8;">Format JPG, PNG, WEBP (Maksimal 5MB)</span>
          </div>
          <div id="paymentProofPreviewWrap" class="payment-proof-preview-wrap" style="display: none;">
            <img id="paymentProofPreviewImg" src="" alt="Bukti Transfer" class="payment-proof-thumb">
            <div class="payment-proof-file-info">
              <span id="paymentProofFileName" class="proof-file-name">-</span>
              <button type="button" id="btnChangePaymentProof" class="btn-change-proof">Ganti Foto Bukti</button>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function initPaymentProofUploader() {
    selectedProofFile = null;
    const proofInput = document.getElementById('modalPaymentProofInput');
    const proofDropzone = document.getElementById('paymentProofDropzone');
    const proofPlaceholder = document.getElementById('paymentProofPlaceholder');
    const proofPreviewWrap = document.getElementById('paymentProofPreviewWrap');
    const proofPreviewImg = document.getElementById('paymentProofPreviewImg');
    const proofFileName = document.getElementById('paymentProofFileName');
    const btnChangeProof = document.getElementById('btnChangePaymentProof');

    if (proofInput && proofDropzone) {
      proofDropzone.onclick = () => proofInput.click();
      if (btnChangeProof) {
        btnChangeProof.onclick = (e) => {
          e.stopPropagation();
          proofInput.click();
        };
      }
      proofInput.onchange = () => {
        if (proofInput.files && proofInput.files[0]) {
          selectedProofFile = proofInput.files[0];
          proofFileName.textContent = selectedProofFile.name;
          const reader = new FileReader();
          reader.onload = (e) => {
            proofPreviewImg.src = e.target.result;
            proofPlaceholder.style.display = 'none';
            proofPreviewWrap.style.display = 'flex';
          };
          reader.readAsDataURL(selectedProofFile);
        }
      };
    }
  }

  function renderQrisView(total) {
    return `
      <div class="qris-display-box">
        <div class="qris-header-strip">
          <span class="qris-tag-badge">QRIS</span>
          <div class="qris-merchant-info">
            <strong style="color: var(--navy); display: block;">ICA FROZEN FOOD</strong>
            <span>NMID: ID1020039281729</span>
          </div>
        </div>

        <div class="qris-qr-code-wrapper" style="text-align: center; padding: 0.75rem; background: #FFFFFF; border-radius: 16px; border: 1.5px solid #E2E8F0; margin: 0 auto; max-width: 250px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
          <img src="${baseUrl}/images/qris-ica-frozen-food.png" alt="QRIS Ica Frozen Food" style="width: 100%; height: auto; border-radius: 10px; display: block;" onerror="this.src='/images/qris-ica-frozen-food.png'">
        </div>

        <p style="font-size: 11px; color: #64748B; text-align: center; margin-top: 0.65rem; margin-bottom: 0.25rem;">
          Pindai kode QRIS di atas via m-Banking atau e-Wallet sesuai <strong>Total Tagihan</strong> di atas.
        </p>

        <ul class="qris-instructions-list">
          <li class="qris-step-item">
            <span class="qris-step-number">1</span>
            <span>Buka m-Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, Dana, ShopeePay).</span>
          </li>
          <li class="qris-step-item">
            <span class="qris-step-number">2</span>
            <span>Pilih menu <strong>Scan / Bayar QRIS</strong> lalu arahkan kamera ke kode di atas.</span>
          </li>
          <li class="qris-step-item">
            <span class="qris-step-number">3</span>
            <span>Pastikan nama merchant <strong>ICA FROZEN FOOD</strong> dan selesaikan pembayaran.</span>
          </li>
          <li class="qris-step-item">
            <span class="qris-step-number">4</span>
            <span>Simpan tangkapan layar (screenshot) bukti transfer Anda, lalu unggah pada form di bawah.</span>
          </li>
        </ul>

        ${renderProofUploadView()}
      </div>
    `;
  }

  // --- Data Master Rekening Bank Transfer Resmi Ica Frozen Food ---
  const BANK_ACCOUNTS = [
    {
      id: 'mandiri',
      name: 'Mandiri',
      fullName: 'Bank Mandiri (Livin\')',
      accountNumber: '0310010940644',
      accountHolder: 'Rissa Ariyani',
      color: '#003D79',
      badgeBg: '#003D79',
      badgeText: '#FFFFFF',
      tag: 'Mandiri',
      mBankingApp: 'Livin\' by Mandiri',
    },
    {
      id: 'bca',
      name: 'BCA',
      fullName: 'Bank Central Asia (BCA)',
      accountNumber: '7895329339',
      accountHolder: 'Rissa Ariyani',
      color: '#0060AF',
      badgeBg: '#0060AF',
      badgeText: '#FFFFFF',
      tag: 'BCA',
      mBankingApp: 'BCA Mobile / myBCA',
    },
    {
      id: 'bri',
      name: 'BRI',
      fullName: 'Bank Rakyat Indonesia (BRI)',
      accountNumber: '451601013957530',
      accountHolder: 'Rissa Ariyani',
      color: '#00529C',
      badgeBg: '#00529C',
      badgeText: '#FFFFFF',
      tag: 'BRI',
      mBankingApp: 'BRImo BRI',
    },
    {
      id: 'bni',
      name: 'BNI',
      fullName: 'Bank Negara Indonesia (BNI)',
      accountNumber: '1129575579',
      accountHolder: 'Rissa Ariyani',
      color: '#005E6A',
      badgeBg: '#005E6A',
      badgeText: '#FFFFFF',
      tag: 'BNI',
      mBankingApp: 'BNI Mobile / wondr',
    },
    {
      id: 'kalsel',
      name: 'Bank Kalsel',
      fullName: 'Bank Pembangunan Daerah Kalsel',
      accountNumber: '2002213489',
      accountHolder: 'Rissa Ariyani',
      color: '#C8102E',
      badgeBg: '#C8102E',
      badgeText: '#FFFFFF',
      tag: 'Kalsel',
      mBankingApp: 'Aksel by Bank Kalsel',
    },
    {
      id: 'bsi',
      name: 'BSI',
      fullName: 'Bank Syariah Indonesia (BSI)',
      accountNumber: '7294884399',
      accountHolder: 'Rissa Ariyani',
      color: '#00A39D',
      badgeBg: '#00A39D',
      badgeText: '#FFFFFF',
      tag: 'BSI',
      mBankingApp: 'BSI Mobile',
    },
  ];

  let selectedBankId = 'mandiri';

  function renderActiveBankCard(bankId, total) {
    const bank = BANK_ACCOUNTS.find((b) => b.id === bankId) || BANK_ACCOUNTS[0];

    return `
      <div class="bank-detail-card">
        <div class="bank-detail-header">
          <div class="bank-brand-badge">
            <div class="bank-logo-box" style="background-color: ${bank.color};">
              ${bank.tag}
            </div>
            <div>
              <h4 style="font-weight: 800; font-size: 0.875rem; color: var(--navy); line-height: 1.2;">${bank.fullName}</h4>
              <p style="font-size: 11px; color: #64748B;">Transfer via ${bank.mBankingApp} / ATM</p>
            </div>
          </div>
          <span style="font-size: 10px; font-weight: 700; color: #0284C7; background-color: #F0F9FF; border: 1px solid #BAE6FD; padding: 2px 8px; border-radius: 9999px;">Manual</span>
        </div>

        <div class="bank-acc-row">
          <div>
            <span style="font-size: 11px; color: #64748B; display: block; font-weight: 600;">Nomor Rekening Tujuan (${bank.name}):</span>
            <strong class="bank-acc-digits">${bank.accountNumber}</strong>
            <span style="font-size: 11px; color: #475569; display: block; margin-top: 2px;">a/n <strong>${bank.accountHolder}</strong></span>
          </div>
          <button type="button" class="btn-copy-acc" onclick="window.copyAccountNumber('${bank.accountNumber}', this)">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
            <span>Salin No. Rek</span>
          </button>
        </div>

        <!-- Pesan Khusus Sesuai Arahan Toko -->
        <div class="bank-greeting-notice">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <div>
            <strong style="color: var(--navy); font-weight: 700; display: block; font-size: 11px; margin-bottom: 2px;">Catatan Pembayaran:</strong>
            <p style="margin: 0; color: #334155; font-size: 11px; line-height: 1.4;">Mohon konfirmasi kembali apabila sudah Transfer &amp; Terimakasih sudah berbelanja di Ica Frozen Food 🙏</p>
          </div>
        </div>

        <ul class="qris-instructions-list" style="margin-top: 0.25rem;">
          <li class="qris-step-item">
            <span class="qris-step-number">1</span>
            <span>Buka aplikasi <strong>${bank.mBankingApp}</strong> atau ATM terdekat.</span>
          </li>
          <li class="qris-step-item">
            <span class="qris-step-number">2</span>
            <span>Transfer sesuai <strong>Total Tagihan</strong> di atas ke rekening <strong>${bank.name}: ${bank.accountNumber}</strong> a/n <strong>${bank.accountHolder}</strong>.</span>
          </li>
          <li class="qris-step-item">
            <span class="qris-step-number">3</span>
            <span>Simpan tangkapan layar (screenshot) bukti transfer sukses, lalu unggah pada form di bawah.</span>
          </li>
        </ul>
      </div>
    `;
  }

  function renderTransferView(total) {
    return `
      <div class="bank-transfer-box">
        <div>
          <span class="bank-tabs-selector-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="20" height="14" x="2" y="5" rx="2" /><line x1="2" y1="10" x2="22" y2="10" /></svg>
            Pilih Rekening Bank Tujuan Transfer:
          </span>
          <div class="bank-tabs-grid" style="margin-top: 0.45rem;">
            ${BANK_ACCOUNTS.map(
              (b) => `
              <button type="button" class="bank-tab-btn ${b.id === selectedBankId ? 'active' : ''}" data-bank-id="${b.id}" onclick="window.selectTransferBank('${b.id}')">
                <span class="bank-tab-mini-badge" style="background: ${b.badgeBg}; color: ${b.badgeText};">${b.tag}</span>
                <span>${b.name}</span>
              </button>
            `
            ).join('')}
          </div>
        </div>

        <div id="bankDetailCardContainer">
          ${renderActiveBankCard(selectedBankId, total)}
        </div>

        ${renderProofUploadView()}
      </div>
    `;
  }

  function renderCashView(total) {
    return `
      <div class="cash-store-box">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <div style="width: 2.75rem; height: 2.75rem; border-radius: 12px; background-color: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
          </div>
          <div>
            <h4 style="font-weight: 800; font-size: 0.9rem; color: var(--navy);">Bayar Tunai di Kasir Outlet</h4>
            <p style="font-size: 11px; color: #64748B;">Khusus metode ambil sendiri di toko</p>
          </div>
        </div>

        <div style="padding: 0.875rem 1rem; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; display: flex; flex-direction: column; gap: 0.65rem;">
          <div style="font-size: 11.5px; color: #334155; line-height: 1.45; display: flex; align-items: flex-start; gap: 0.55rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1.5px;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            <div><strong>Lokasi Pengambilan:</strong> Outlet Ica Frozen Food, Loktabat Utara Banjarbaru (Buka 08.00 - 21.00 WITA).</div>
          </div>
          <div style="font-size: 11.5px; color: #0369A1; background: #F0F9FF; border: 1px solid #BAE6FD; padding: 0.6rem 0.75rem; border-radius: 8px; line-height: 1.45; display: flex; align-items: flex-start; gap: 0.55rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1.5px;"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
            <div>Harap siapkan uang pas sesuai <strong>Total Tagihan</strong> di atas saat pengambilan pesanan di kasir toko.</div>
          </div>
        </div>

        <p style="font-size: 0.75rem; color: #64748B; line-height: 1.45;">
          Klik tombol <strong>"Konfirmasi Pesanan Saya"</strong> di bawah agar pesanan langsung masuk ke antrean kasir freezer dan disiapkan sebelum kedatangan Anda.
        </p>
      </div>
    `;
  }

  window.selectTransferBank = function (bankId) {
    selectedBankId = bankId;
    const bank = BANK_ACCOUNTS.find((b) => b.id === bankId) || BANK_ACCOUNTS[0];

    document.querySelectorAll('.bank-tab-btn').forEach((btn) => {
      const isCurrent = btn.getAttribute('data-bank-id') === bankId;
      btn.classList.toggle('active', isCurrent);
    });

    const container = document.getElementById('bankDetailCardContainer');
    if (container && activePendingOrder) {
      container.innerHTML = renderActiveBankCard(bankId, activePendingOrder.total);
    }

    if (activePendingOrder) {
      activePendingOrder.payment_method = `Transfer Bank (${bank.name})`;
    }
    if (paymentModalMethodBadge) {
      paymentModalMethodBadge.textContent = `Transfer Bank (${bank.name})`;
    }
  };

  window.copyAccountNumber = function (accNumber, btnEl) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(accNumber).then(() => {
        const origContent = btnEl.innerHTML;
        btnEl.innerHTML = `
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <span>✓ Tersalin</span>
        `;
        btnEl.style.backgroundColor = '#059669';
        btnEl.style.color = '#FFFFFF';
        setTimeout(() => {
          btnEl.innerHTML = origContent;
          btnEl.style.backgroundColor = '';
          btnEl.style.color = '';
        }, 1800);
      });
    }
  };

  window.copyAmount = function (amount, btnEl) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(String(amount)).then(() => {
        const origContent = btnEl.innerHTML;
        btnEl.innerHTML = `
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <span>✓ Tersalin</span>
        `;
        btnEl.style.backgroundColor = '#059669';
        btnEl.style.color = '#FFFFFF';
        btnEl.style.borderColor = '#059669';
        setTimeout(() => {
          btnEl.innerHTML = origContent;
          btnEl.style.backgroundColor = '';
          btnEl.style.color = '';
          btnEl.style.borderColor = '';
        }, 1800);
      });
    }
  };

  function openPaymentModal(orderPayload) {
    activePendingOrder = orderPayload;
    selectedProofFile = null;
    const isCash = orderPayload.payment_method.includes('Tunai');
    const totalFormatted = 'Rp ' + Number(orderPayload.total).toLocaleString('id-ID');

    if (paymentModalAmount) paymentModalAmount.textContent = totalFormatted;
    if (paymentModalMethodBadge) paymentModalMethodBadge.textContent = orderPayload.payment_method;

    // Reset visual components
    if (paymentVerifyingBox) paymentVerifyingBox.style.display = 'none';
    if (paymentSuccessNotice) paymentSuccessNotice.style.display = 'none';
    if (paymentDynamicContent) paymentDynamicContent.style.display = 'block';
    if (paymentModalFooter) paymentModalFooter.style.display = 'flex';
    if (btnConfirmPayment) {
      btnConfirmPayment.disabled = false;
      btnConfirmPayment.style.opacity = '1';
      btnConfirmPayment.style.cursor = 'pointer';
      if (btnConfirmPaymentText) {
        btnConfirmPaymentText.textContent = isCash
          ? 'Konfirmasi Pesanan Saya'
          : 'Kirim Bukti & Selesaikan Pesanan';
      }
    }

    if (orderPayload.payment_method.includes('QRIS')) {
      paymentDynamicContent.innerHTML = renderQrisView(orderPayload.total);
      initPaymentProofUploader();
      startPaymentCountdown(15);
    } else if (orderPayload.payment_method.includes('Transfer Bank')) {
      selectedBankId = 'mandiri';
      activePendingOrder.payment_method = 'Transfer Bank (Mandiri)';
      if (paymentModalMethodBadge) paymentModalMethodBadge.textContent = 'Transfer Bank (Mandiri)';
      paymentDynamicContent.innerHTML = renderTransferView(orderPayload.total);
      initPaymentProofUploader();
      startPaymentCountdown(15);
    } else {
      paymentDynamicContent.innerHTML = renderCashView(orderPayload.total);
      stopPaymentCountdown();
    }

    if (paymentModal) paymentModal.classList.add('active');
  }

  function closePaymentModal() {
    stopPaymentCountdown();
    selectedProofFile = null;
    if (paymentModal) paymentModal.classList.remove('active');
  }

  if (btnPaymentClose) btnPaymentClose.addEventListener('click', closePaymentModal);
  if (paymentModal) {
    paymentModal.addEventListener('click', (e) => {
      if (e.target === paymentModal && !btnConfirmPayment?.disabled) {
        closePaymentModal();
      }
    });
  }

  // Step 2 & 3: User clicks "Kirim Bukti & Selesaikan Pesanan"
  if (btnConfirmPayment) {
    btnConfirmPayment.addEventListener('click', async () => {
      if (!activePendingOrder) return;

      const isCash = activePendingOrder.payment_method.includes('Tunai');

      // Validasi wajib upload bukti untuk metode non-tunai
      if (!isCash && !selectedProofFile) {
        window.showToast('Harap pilih foto bukti transfer terlebih dahulu!', 'error');
        const dropzone = document.getElementById('paymentProofDropzone');
        if (dropzone) {
          dropzone.scrollIntoView({ behavior: 'smooth', block: 'center' });
          dropzone.style.borderColor = '#EF4444';
          dropzone.style.backgroundColor = '#FEF2F2';
          setTimeout(() => {
            dropzone.style.borderColor = '#E2E8F0';
            dropzone.style.backgroundColor = '#FFFFFF';
          }, 2000);
        }
        return;
      }

      // 1. Enter Verification / Submitting State
      btnConfirmPayment.disabled = true;
      if (paymentDynamicContent) paymentDynamicContent.style.display = 'none';
      if (paymentModalFooter) paymentModalFooter.style.display = 'none';
      if (paymentVerifyingBox) paymentVerifyingBox.style.display = 'flex';

      // 2. Prepare FormData payload
      const formData = new FormData();
      formData.append('customerName', activePendingOrder.customer_name || activePendingOrder.customerName || 'Pelanggan');
      formData.append('customerPhone', activePendingOrder.customer_phone || activePendingOrder.customerPhone || '-');
      formData.append('address', activePendingOrder.address || '-');
      formData.append('method', activePendingOrder.method || 'Ambil di Toko');
      formData.append('paymentMethod', activePendingOrder.payment_method || 'QRIS Instan');
      formData.append('items', JSON.stringify(activePendingOrder.items || []));
      formData.append('subtotal', activePendingOrder.subtotal || 0);
      formData.append('shipping_fee', activePendingOrder.shipping_fee || 0);
      formData.append('total', activePendingOrder.total || 0);
      formData.append('channel', 'Online Store');
      formData.append('is_paid', '0');
      formData.append('status', isCash ? 'Menunggu Bayar' : 'Menunggu Konfirmasi Admin');
      if (selectedProofFile) {
        formData.append('proof', selectedProofFile);
      }

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/api/orders', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf || '',
          },
          body: formData,
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
          window.showToast(data.message || 'Gagal menyimpan pesanan.', 'error');
          if (paymentDynamicContent) paymentDynamicContent.style.display = 'block';
          if (paymentModalFooter) paymentModalFooter.style.display = 'flex';
          if (paymentSuccessNotice) paymentSuccessNotice.style.display = 'none';
          if (paymentVerifyingBox) paymentVerifyingBox.style.display = 'none';
          btnConfirmPayment.disabled = false;
          return;
        }

        const savedOrder = data.order || activePendingOrder;

        // Simpan ID pesanan baru ke localStorage agar muncul di Pesanan Saya
        try {
          let myIds = JSON.parse(localStorage.getItem('ica_my_order_ids') || '[]');
          if (savedOrder && savedOrder.id && !myIds.includes(savedOrder.id)) {
            myIds.unshift(savedOrder.id);
            localStorage.setItem('ica_my_order_ids', JSON.stringify(myIds));
          }
        } catch (e) {}

        // Brief simulated check for UX polish
        await new Promise((r) => setTimeout(r, 1200));

        // 3. Show Verification Success Notice
        if (paymentVerifyingBox) paymentVerifyingBox.style.display = 'none';
        if (paymentSuccessNotice) {
          const successTitle = paymentSuccessNotice.querySelector('h4');
          const successDesc = paymentSuccessNotice.querySelector('p');
          if (successTitle) {
            successTitle.textContent = isCash ? 'Pesanan Berhasil Dicatat!' : 'Pesanan & Bukti Terkirim!';
          }
          if (successDesc) {
            successDesc.textContent = isCash
              ? 'Silakan bayar tunai di kasir outlet saat pengambilan.'
              : 'Admin kasir akan segera memvalidasi mutasi pembayaran Anda.';
          }
          paymentSuccessNotice.style.display = 'flex';
        }

        await new Promise((r) => setTimeout(r, 700));

        // 4. Reset & finish
        stopPaymentCountdown();
        cart = [];
        saveCart();
        closePaymentModal();
        window.closeCartDrawer();
        window.showReceiptModal(savedOrder);
        window.showToast(isCash ? 'Pesanan berhasil dibuat! Silakan bayar di kasir.' : 'Pesanan dan bukti pembayaran berhasil dikirim!');

      } catch (err) {
        console.error(err);
        alert('Terjadi kendala jaringan saat menyimpan pesanan.');
        if (paymentDynamicContent) paymentDynamicContent.style.display = 'block';
        if (paymentModalFooter) paymentModalFooter.style.display = 'flex';
        if (paymentSuccessNotice) paymentSuccessNotice.style.display = 'none';
        if (paymentVerifyingBox) paymentVerifyingBox.style.display = 'none';
        btnConfirmPayment.disabled = false;
      }
    });
  }

  // --- 10. Modal Nota Struk Digital Kasir ---
  const receiptModal = document.getElementById('receiptModal');
  const btnReceiptClose = document.getElementById('btnReceiptClose');
  const recNoOrder = document.getElementById('recNoOrder');
  const recDate = document.getElementById('recDate');
  const recCustomer = document.getElementById('recCustomer');
  const recMethod = document.getElementById('recMethod');
  const recPayment = document.getElementById('recPayment');
  const recItemsList = document.getElementById('recItemsList');
  const recSubtotal = document.getElementById('recSubtotal');
  const recIceFee = document.getElementById('recIceFee');
  const recTotal = document.getElementById('recTotal');
  const btnPrintReceipt = document.getElementById('btnPrintReceipt');
  const btnCopyReceipt = document.getElementById('btnCopyReceipt');

  let activeOrderReceipt = null;

  window.showReceiptModal = function (order) {
    activeOrderReceipt = order;

    if (recNoOrder) recNoOrder.textContent = order.id;
    if (recDate) recDate.textContent = order.order_date || order.date || new Date().toLocaleString('id-ID');
    if (recCustomer) recCustomer.textContent = order.customer_name || order.customerName || 'Pelanggan';
    if (recMethod) recMethod.textContent = order.method || 'Diantar Kurir';
    if (recPayment) recPayment.textContent = order.payment_method || order.paymentMethod || 'QRIS Instan';

    const items = Array.isArray(order.items) ? order.items : (typeof order.items === 'string' ? JSON.parse(order.items) : []);
    if (recItemsList) {
      recItemsList.innerHTML = items
        .map(
          (it) => `
          <tr>
            <td style="font-weight: 600;">${it.name}</td>
            <td style="text-align: center;">${it.qty || 1}x</td>
            <td style="text-align: right; font-weight: 700;">Rp ${(((it.price || 0) * (it.qty || 1))).toLocaleString('id-ID')}</td>
          </tr>
        `
        )
        .join('');
    }

    if (recSubtotal) recSubtotal.textContent = 'Rp ' + Number(order.subtotal || 0).toLocaleString('id-ID');
    const ice = order.ice_fee !== undefined ? order.ice_fee : (order.iceFee || 0);
    if (recIceFee) recIceFee.textContent = 'Rp ' + Number(ice).toLocaleString('id-ID');
    if (recTotal) recTotal.textContent = 'Rp ' + Number(order.total || 0).toLocaleString('id-ID');

    // Status Pembayaran Dinamis pada Struk Digital
    const recPayStatusText = document.getElementById('recPayStatusText');
    if (recPayStatusText) {
      const status = String(order.status || '').trim();
      const hasProof = Boolean(order.paymentProof || order.payment_proof);
      const isPaidFlag = order.is_paid === true || order.is_paid === 1 || order.isPaid === true || order.isPaid === 1;

      if (status === 'Menunggu Konfirmasi Admin' || (hasProof && status !== 'Selesai' && status !== 'Diproses')) {
        recPayStatusText.textContent = 'MENUNGGU VERIFIKASI ADMIN';
        recPayStatusText.style.color = '#D97706';
      } else if (status === 'Selesai' || (status === 'Diproses' && isPaidFlag)) {
        recPayStatusText.textContent = 'LUNAS';
        recPayStatusText.style.color = '#059669';
      } else if (status === 'Dibatalkan') {
        recPayStatusText.textContent = 'DIBATALKAN';
        recPayStatusText.style.color = '#DC2626';
      } else {
        recPayStatusText.textContent = 'BELUM LUNAS';
        recPayStatusText.style.color = '#DC2626';
      }
    }

    // Simpan order ID ke penyimpanan lokal browser sebagai riwayat pesanan pelanggan
    try {
      const mySavedIds = JSON.parse(localStorage.getItem('ica_my_order_ids') || '[]');
      if (!mySavedIds.includes(order.id)) {
        mySavedIds.unshift(order.id);
        localStorage.setItem('ica_my_order_ids', JSON.stringify(mySavedIds));
      }
    } catch (e) {}

    if (receiptModal) receiptModal.classList.add('active');
  };

  window.closeReceiptModal = function () {
    if (receiptModal) receiptModal.classList.remove('active');
    activeOrderReceipt = null;
  };

  if (btnReceiptClose) btnReceiptClose.addEventListener('click', window.closeReceiptModal);
  if (receiptModal) {
    receiptModal.addEventListener('click', (e) => {
      if (e.target === receiptModal) window.closeReceiptModal();
    });
  }

  if (btnPrintReceipt) {
    btnPrintReceipt.addEventListener('click', () => {
      document.body.classList.add('is-printing-receipt');
      window.print();
    });
  }

  window.addEventListener('afterprint', () => {
    document.body.classList.remove('is-printing-receipt');
  });

  if (btnCopyReceipt) {
    btnCopyReceipt.addEventListener('click', () => {
      if (!activeOrderReceipt) return;
      const o = activeOrderReceipt;
      const text = `
========================================
         *** ICA FROZEN FOOD ***
  Spesialis Makanan Beku Higienis & Halal
  Loktabat Utara, Banjarbaru • WA: 0878-5451-3770
========================================
No. Transaksi : ${o.id}
Waktu         : ${o.order_date || new Date().toLocaleString('id-ID')}
Pelanggan     : ${o.customer_name}
Metode Kirim  : ${o.method}
Pembayaran    : ${o.payment_method}
Status Bayar  : ${(o.status === 'Selesai' || o.is_paid) ? 'LUNAS' : (o.status === 'Menunggu Konfirmasi Admin' ? 'MENUNGGU VERIFIKASI ADMIN' : 'BELUM LUNAS')}
----------------------------------------
ITEM:
${(o.items || []).map((it) => `${it.name} (${it.qty}x) = Rp ${(it.qty * it.price).toLocaleString('id-ID')}`).join('\n')}
----------------------------------------
Subtotal      : Rp ${Number(o.subtotal).toLocaleString('id-ID')}
Ongkos Kirim  : Rp ${Number(o.shipping_fee !== undefined ? o.shipping_fee : (o.ice_fee || 0)).toLocaleString('id-ID')}
TOTAL AKHIR   : Rp ${Number(o.total).toLocaleString('id-ID')}
========================================
Terima kasih atas kunjungan Anda!
      `.trim();

      navigator.clipboard.writeText(text).then(() => {
        alert('Teks struk berhasil disalin ke clipboard!');
      });
    });
  }

  // --- 11. Paket Rekomendasi Hemat (Smart Bundles) ---
  window.addBundleToCart = function (bundleId) {
    // Validasi Tamu: Wajib login sebelum mengambil paket hemat
    if (!window.__CURRENT_USER__ || !window.__IS_LOGGED_IN__) {
      window.promptGuestLogin('Silakan masuk ke akun Anda terlebih dahulu untuk memesan paket hemat di Ica Frozen Food.');
      return;
    }

    // Validasi Toko Tutup: Jangan masukkan ke keranjang, langsung beri peringatan
    if (window.__STORE_STATUS__ && window.__STORE_STATUS__.is_open === false) {
      window.showToast('Mohon maaf, operasional toko sedang TUTUP SEMENTARA saat ini. Pemesanan paket hemat dibuka kembali pada jam 08.00 – 21.00 WITA.', 'error');
      return;
    }

    const bundle = bundles.find((b) => b.id === bundleId);
    if (!bundle) return;

    let countAdded = 0;
    bundle.items.forEach((pid) => {
      const p = products.find((prod) => prod.id === pid);
      if (p && p.stock > 0) {
        addToCart(p, 1, false);
        countAdded++;
      }
    });

    if (countAdded > 0) {
      window.showToast(`Paket "${bundle.name}" berhasil ditambahkan ke keranjang!`);
      window.openCartDrawer();
    } else {
      window.showToast('Maaf, produk dalam paket ini sedang tidak tersedia.', 'error');
    }
  };

  // --- 12. Modal Riwayat Pesanan Saya & Lacak Status ---
  const customerOrdersModal = document.getElementById('customerOrdersModal');
  const btnOpenCustomerOrdersModal = document.getElementById('btnOpenCustomerOrdersModal');
  const btnMobileCustomerOrders = document.getElementById('btnMobileCustomerOrders');
  const btnCloseCustomerOrdersModal = document.getElementById('btnCloseCustomerOrdersModal');
  const customerOrdersListContainer = document.getElementById('customerOrdersListContainer');

  function renderOrderHistorySkeletons(count = 3) {
    return Array.from({ length: count })
      .map(
        () => `
      <div class="order-skeleton-card">
        <div class="order-skeleton-header">
          <div style="display: flex; flex-direction: column; gap: 6px; width: 55%;">
            <div class="skeleton-line title skeleton-shimmer" style="height: 14px; width: 75%; border-radius: 6px;"></div>
            <div class="skeleton-line subtitle skeleton-shimmer" style="height: 10px; width: 45%; border-radius: 4px;"></div>
          </div>
          <div class="skeleton-line skeleton-shimmer" style="height: 22px; width: 85px; border-radius: 9999px;"></div>
        </div>
        <div class="order-skeleton-items">
          <div class="order-skeleton-thumb skeleton-shimmer"></div>
          <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
            <div class="skeleton-line skeleton-shimmer" style="height: 13px; width: 68%; border-radius: 5px;"></div>
            <div class="skeleton-line skeleton-shimmer" style="height: 11px; width: 35%; border-radius: 4px;"></div>
          </div>
        </div>
        <div class="order-skeleton-footer">
          <div class="skeleton-line skeleton-shimmer" style="height: 12px; width: 32%; border-radius: 5px;"></div>
          <div class="skeleton-line skeleton-shimmer" style="height: 16px; width: 38%; border-radius: 6px;"></div>
        </div>
      </div>
    `
      )
      .join('');
  }

  window.openCustomerOrdersModal = async function () {
    if (!customerOrdersModal || !customerOrdersListContainer) return;

    // Tampilkan loading skeleton realistis dan pastikan display flex aktif
    customerOrdersModal.style.display = 'flex';
    customerOrdersModal.classList.add('active');
    customerOrdersListContainer.innerHTML = renderOrderHistorySkeletons(3);

    try {
      const res = await fetch('/api/orders');
      if (!res.ok) throw new Error('Gagal memuat data pesanan');
      const data = await res.json();
      const allOrders = Array.isArray(data) ? data : (data.orders || []);

      // Dapatkan data user saat ini & ID pesanan tersimpan di browser
      const currentUser = window.__CURRENT_USER__ || null;
      let savedOrderIds = [];
      try {
        savedOrderIds = JSON.parse(localStorage.getItem('ica_my_order_ids') || '[]');
      } catch (e) {}

      // Filter pesanan milik user ini berdasarkan:
      // 1. Phone number user
      // 2. Nama user
      // 3. ID yang ada di localStorage
      const myOrders = allOrders.filter((order) => {
        const orderIdMatch = savedOrderIds.includes(order.id);
        const orderPhone = order.customer_phone || order.customerPhone || '';
        const orderName = order.customer_name || order.customerName || '';
        const phoneMatch = currentUser && currentUser.phone && orderPhone &&
          String(currentUser.phone).replace(/[^0-9]/g, '') === String(orderPhone).replace(/[^0-9]/g, '');
        const nameMatch = currentUser && currentUser.name && orderName &&
          currentUser.name.trim().toLowerCase() === orderName.trim().toLowerCase();
        return orderIdMatch || phoneMatch || nameMatch;
      });

      if (myOrders.length === 0) {
        customerOrdersListContainer.innerHTML = `
          <div class="customer-orders-empty-wrap" style="text-align: center; padding: 2.75rem 1.5rem; color: #64748B;">
            <img src="/images/ica_logo.png" alt="Ica Frozen Food" class="catalog-empty-watermark-logo" style="width: 100px; height: auto; margin: 0 auto 1rem auto; display: block;" aria-hidden="true">
            <h4 style="font-size: 1.0625rem; font-weight: 800; color: var(--navy); margin-bottom: 0.35rem; letter-spacing: -0.015em;">Belum Ada Riwayat Pesanan</h4>
            <p style="font-size: 0.8125rem; max-width: 280px; margin: 0 auto 1.35rem auto; color: var(--text-gray-500); line-height: 1.55;">
              Pesanan yang Anda buat melalui website akan otomatis tercatat dan dapat Anda pantau statusnya di sini.
            </p>
            <button type="button" class="btn-cta-blue" style="padding: 0.65rem 1.4rem; font-size: 0.8125rem; border-radius: 12px; display: inline-flex; margin: 0 auto; align-items: center; gap: 0.45rem;" onclick="window.closeCustomerOrdersModal(); (document.getElementById('katalog-section') || document.getElementById('katalogSection'))?.scrollIntoView({ behavior: 'smooth' });">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
              <span>Mulai Belanja Sekarang</span>
            </button>
          </div>
        `;
        return;
      }

      // Render daftar pesanan bersih & terstruktur
      customerOrdersListContainer.innerHTML = myOrders.map((order) => {
        let badgeClass = 'waiting';
        let badgeLabel = order.status || 'Menunggu Pembayaran';

        if (order.status === 'Menunggu Konfirmasi Admin') {
          badgeClass = 'waiting';
          badgeLabel = 'Menunggu Verifikasi Admin';
        } else if (order.status === 'Diproses') {
          badgeClass = 'process';
          badgeLabel = 'Diproses';
        } else if (order.status === 'Selesai') {
          badgeClass = 'completed';
          badgeLabel = 'Selesai';
        } else if (order.status === 'Dibatalkan') {
          badgeClass = 'cancelled';
          badgeLabel = 'Dibatalkan';
        }

        const hasProof = Boolean(order.paymentProof || order.payment_proof);
        const paymentMethod = order.payment_method || order.paymentMethod || 'QRIS Instan';
        const isNonCash = !String(paymentMethod).toLowerCase().includes('tunai');
        const deliveryMethod = order.delivery_method || order.deliveryMethod || 'Ambil di Toko';

        const items = Array.isArray(order.items) ? order.items : [];
        const itemsSummary = items.slice(0, 3).map((it) => {
          const pName = it.name || (it.product && it.product.name) || 'Produk Frozen';
          const pQty = it.qty || it.quantity || 1;
          return `
            <div class="cust-order-item-row">
              <span class="cust-order-item-name">${pName}</span>
              <span class="cust-order-item-qty">${pQty}x</span>
            </div>
          `;
        }).join('');

        const moreItemsCount = items.length > 3 ? items.length - 3 : 0;
        const totalFormatted = (parseInt(order.total, 10) || 0).toLocaleString('id-ID');
        const orderDateVal = order.order_date || order.date || order.created_at;
        const dateFormatted = orderDateVal ? new Date(orderDateVal).toLocaleDateString('id-ID', {
          day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
        }) : '-';

        return `
          <div class="cust-order-card" data-order-id="${order.id}">
            <!-- Header Kartu: Kode & Tanggal + Status Badge Tanpa Icon -->
            <div class="cust-order-card-header">
              <div class="cust-order-id-col">
                <span class="cust-order-id">${order.id}</span>
                <span class="cust-order-date">${dateFormatted} WITA</span>
              </div>
              <span class="cust-order-status-badge ${badgeClass}">${badgeLabel}</span>
            </div>

            <!-- List Item Produk yang Rapi -->
            <div class="cust-order-items-wrap">
              ${itemsSummary}
              ${moreItemsCount > 0 ? `<div class="cust-order-more-tag">+ ${moreItemsCount} produk lainnya</div>` : ''}
            </div>

            <!-- Footer: Total & Tombol Aksi Bersih -->
            <div class="cust-order-card-footer">
              <div class="cust-order-total-col">
                <span class="cust-order-total-label">Total Pembayaran</span>
                <span class="cust-order-total-val">Rp ${totalFormatted}</span>
                <span class="cust-order-meta-info">${deliveryMethod} • ${paymentMethod}</span>
                ${hasProof ? '<span class="proof-badge-success">Bukti transfer terkirim</span>' : ''}
              </div>
              <div class="cust-order-actions-col">
                ${(isNonCash && (order.status === 'Menunggu Konfirmasi Admin' || order.status === 'Menunggu Pembayaran' || order.status === 'Menunggu Bayar')) ? (
                  hasProof ? `
                    <button type="button" class="btn-cust-action btn-change-proof" onclick="window.triggerReuploadProof('${order.id}')" title="Ganti foto bukti transfer jika salah upload">
                      Ganti Bukti
                    </button>
                  ` : `
                    <button type="button" class="btn-cust-action btn-upload-proof" onclick="window.triggerReuploadProof('${order.id}')" title="Upload foto bukti transfer Anda">
                      Upload Bukti
                    </button>
                  `
                ) : ''}
                <button type="button" class="btn-cust-action btn-view-receipt" onclick="window.viewOrderReceiptFromHistory('${order.id}')">
                  Lihat Struk
                </button>
              </div>
            </div>
          </div>
        `;
      }).join('');

    } catch (err) {
      console.error('Error openCustomerOrdersModal:', err);
      customerOrdersListContainer.innerHTML = `
        <div style="text-align: center; padding: 2rem 1rem; color: #EF4444;">
          <p style="font-weight: 700; margin-bottom: 0.5rem;">Gagal memuat pesanan</p>
          <p style="font-size: 0.8125rem; color: #64748B; margin-bottom: 1rem;">Pastikan koneksi internet stabil lalu coba lagi.</p>
          <button type="button" class="btn-outline" style="padding: 0.4rem 1rem; font-size: 0.75rem;" onclick="window.openCustomerOrdersModal()">Coba Lagi</button>
        </div>
      `;
    }
  };

  window.closeCustomerOrdersModal = function () {
    if (customerOrdersModal) {
      customerOrdersModal.classList.remove('active');
      customerOrdersModal.style.display = 'none';
    }
  };

  // Helper untuk membuka struk dari daftar riwayat
  window.viewOrderReceiptFromHistory = async function (orderId) {
    try {
      const res = await fetch('/api/orders');
      if (!res.ok) return;
      const data = await res.json();
      const allOrders = Array.isArray(data) ? data : (data.orders || []);
      const target = allOrders.find((o) => o.id === orderId);
      if (target) {
        window.closeCustomerOrdersModal();
        if (typeof window.showReceiptModal === 'function') {
          window.showReceiptModal(target);
        }
      }
    } catch (e) {}
  };

  // Helper untuk ganti/unggah ulang foto bukti transfer dari kartu Pesanan Saya
  window.triggerReuploadProof = function (orderId) {
    let input = document.getElementById('globalCustomerProofInput');
    if (!input) {
      input = document.createElement('input');
      input.type = 'file';
      input.id = 'globalCustomerProofInput';
      input.accept = 'image/*';
      input.style.display = 'none';
      document.body.appendChild(input);
    }
    input.onchange = async () => {
      if (!input.files || !input.files[0]) return;
      const file = input.files[0];
      if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file maksimal 5MB');
        input.value = '';
        return;
      }
      const formData = new FormData();
      formData.append('proof', file);

      if (typeof window.showToast === 'function') {
        window.showToast('Sedang mengunggah bukti pembayaran...', 'info');
      }
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch(`/api/orders/${orderId}/payment-proof`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken },
          body: formData,
        });
        const data = await res.json();
        if (res.ok && data.success) {
          if (typeof window.showToast === 'function') {
            window.showToast('Bukti transfer berhasil diperbarui! Menunggu validasi admin.', 'success');
          }
          if (typeof window.openCustomerOrdersModal === 'function') {
            window.openCustomerOrdersModal();
          }
        } else {
          alert(data.message || 'Gagal mengunggah bukti transfer.');
        }
      } catch (err) {
        alert('Terjadi kendala jaringan saat mengunggah bukti transfer.');
      } finally {
        input.value = '';
      }
    };
    input.click();
  };

  if (btnOpenCustomerOrdersModal) {
    btnOpenCustomerOrdersModal.addEventListener('click', (e) => {
      e.preventDefault();
      if (userProfileDropdown) {
        userProfileDropdown.classList.remove('open');
        userProfileDropdown.style.display = 'none';
      }
      window.openCustomerOrdersModal();
    });
  }

  if (btnMobileCustomerOrders) {
    btnMobileCustomerOrders.addEventListener('click', (e) => {
      e.preventDefault();
      closeMobileDrawer();
      window.openCustomerOrdersModal();
    });
  }

  if (btnCloseCustomerOrdersModal) {
    btnCloseCustomerOrdersModal.addEventListener('click', window.closeCustomerOrdersModal);
  }

  if (customerOrdersModal) {
    customerOrdersModal.addEventListener('click', (e) => {
      if (e.target === customerOrdersModal) {
        window.closeCustomerOrdersModal();
      }
    });
  }

  // Registrasi Service Worker PWA (Progressive Web App)
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js').then((reg) => {
        console.log('PWA ServiceWorker terdaftar:', reg.scope);
      }).catch((err) => {
        console.log('PWA ServiceWorker gagal daftar:', err);
      });
    });
  }

  // Initial cart render
  renderCart();
});
