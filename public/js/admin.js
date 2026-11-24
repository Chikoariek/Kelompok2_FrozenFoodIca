/**
 * ICA FROZEN FOOD - ADMIN JAVASCRIPT (VANILLA JS)
 * Logika 100% Terhubung Langsung ke Database & Konsisten di Setiap Menu:
 * - AdminSidebar & AdminDashboardPage
 * - AdminProfilePanel (Simpan Profil & Password ke Database)
 * - AdminTable (Proses Pesanan & Patch Status ke Database)
 * - InventoryTable (Data Produk & Sinkronisasi ke Database)
 * - CategoryManagementPanel (Kategori & Sinkronisasi ke Database)
 * - ReceiptModal (Struk Termal & Cetak)
 */

document.addEventListener('DOMContentLoaded', function () {
  const baseUrl = window.__BASE_URL__ || '';
  const apiUrl = window.__API_URL__ || (baseUrl + '/api');
  let products = window.__ADMIN_PRODUCTS__ || [];
  let bundles = window.__ADMIN_BUNDLES__ || [];
  let categories = window.__ADMIN_CATEGORIES__ || [];
  let orders = window.__ADMIN_ORDERS__ || [];
  let user = window.__ADMIN_USER__ || {};

  setTimeout(() => {
    const bottomOrdersBadge = document.getElementById('adminBottomOrdersBadge');
    if (bottomOrdersBadge && orders) {
      const pendingCount = orders.filter(o => o.status === 'Menunggu Bayar' || o.status === 'Menunggu Konfirmasi Admin' || o.status === 'Diproses').length;
      if (pendingCount > 0) {
        bottomOrdersBadge.textContent = pendingCount;
        bottomOrdersBadge.style.display = 'flex';
      }
    }
  }, 100);

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // --- 1. Notifikasi Pesan Popup (Toast) ---
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
      toast.style.transform = 'translateY(15px)';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  };

  // Interceptor Otomatis: Tangani Sesi Habis (Session Expired 401/419) pada Pemanggilan Fetch API
  const originalAdminFetch = window.fetch;
  window.fetch = async function (...args) {
    try {
      const response = await originalAdminFetch.apply(this, args);
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

  // --- Universal Client-Side Pagination Renderer (Impeccable UI/UX) ---
  function renderPaginationControls({ container, totalItems, pageSize, currentPage, onPageChange }) {
    if (!container) return;
    const totalPages = Math.ceil(totalItems / pageSize) || 1;

    if (totalItems === 0) {
      container.innerHTML = '';
      container.style.display = 'none';
      return;
    }

    container.style.display = 'flex';
    const startItem = (currentPage - 1) * pageSize + 1;
    const endItem = Math.min(currentPage * pageSize, totalItems);

    let html = `
      <p class="pagination-info-text">Menampilkan <b>${startItem}-${endItem}</b> dari <b>${totalItems}</b> data</p>
      <ul class="pagination-controls-list">
        <li>
          <button type="button" class="pagination-btn btn-page-prev" ${currentPage <= 1 ? 'disabled' : ''} title="Halaman Sebelumnya">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
        </li>
    `;

    let pages = [];
    if (totalPages <= 7) {
      for (let p = 1; p <= totalPages; p++) pages.push(p);
    } else {
      if (currentPage <= 4) {
        pages = [1, 2, 3, 4, 5, '...', totalPages];
      } else if (currentPage >= totalPages - 3) {
        pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
      } else {
        pages = [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages];
      }
    }

    pages.forEach((p) => {
      if (p === '...') {
        html += `<li><span class="pagination-ellipsis">&hellip;</span></li>`;
      } else {
        const isActive = p === currentPage;
        html += `<li><button type="button" class="pagination-btn btn-page-num ${isActive ? 'active' : ''}" data-page="${p}">${p}</button></li>`;
      }
    });

    html += `
        <li>
          <button type="button" class="pagination-btn btn-page-next" ${currentPage >= totalPages ? 'disabled' : ''} title="Halaman Berikutnya">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </li>
      </ul>
    `;

    container.innerHTML = html;

    container.querySelectorAll('.btn-page-num').forEach((btn) => {
      btn.addEventListener('click', () => {
        const p = parseInt(btn.getAttribute('data-page'), 10);
        if (p && p !== currentPage) onPageChange(p);
      });
    });

    const btnPrev = container.querySelector('.btn-page-prev');
    if (btnPrev && currentPage > 1) {
      btnPrev.addEventListener('click', () => onPageChange(currentPage - 1));
    }

    const btnNext = container.querySelector('.btn-page-next');
    if (btnNext && currentPage < totalPages) {
      btnNext.addEventListener('click', () => onPageChange(currentPage + 1));
    }
  }

  // --- 2. Navigasi Sidebar Mobile & Dropdown Profil Admin ---
  const adminSidebar = document.getElementById('adminSidebar');
  const adminSidebarBackdrop = document.getElementById('adminSidebarBackdrop');
  const btnAdminMobileToggle = document.getElementById('btnAdminMobileToggle');
  const btnAdminMobileClose = document.getElementById('btnAdminMobileClose');

  function openMobileSidebar() {
    adminSidebar?.classList.add('open');
    adminSidebarBackdrop?.classList.add('open');
  }

  function closeMobileSidebar() {
    adminSidebar?.classList.remove('open');
    adminSidebarBackdrop?.classList.remove('open');
  }

  btnAdminMobileToggle?.addEventListener('click', openMobileSidebar);
  btnAdminMobileClose?.addEventListener('click', closeMobileSidebar);
  adminSidebarBackdrop?.addEventListener('click', closeMobileSidebar);

  // Topbar Admin Profile Button & Dropdown
  const btnAdminProfileTrigger = document.getElementById('btnAdminProfileTrigger');
  const adminProfileDropdown = document.getElementById('adminProfileDropdown');
  const topbarChevron = document.getElementById('topbarChevron');

  function toggleProfileDropdown() {
    const isOpen = adminProfileDropdown?.classList.toggle('open');
    btnAdminProfileTrigger?.classList.toggle('active', isOpen);
    if (topbarChevron) {
      topbarChevron.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    }
  }

  function closeProfileDropdown() {
    adminProfileDropdown?.classList.remove('open');
    btnAdminProfileTrigger?.classList.remove('active');
    if (topbarChevron) topbarChevron.style.transform = 'rotate(0deg)';
  }

  btnAdminProfileTrigger?.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    toggleProfileDropdown();
  });

  document.addEventListener('click', function (e) {
    if (!adminProfileDropdown?.contains(e.target) && !btnAdminProfileTrigger?.contains(e.target)) {
      closeProfileDropdown();
    }
  });

  // --- 3. Navigasi Tab Admin & Sinkronisasi Judul Halaman ---
  const sidebarBtns = document.querySelectorAll('.sidebar-nav-btn[data-tab]');
  const tabPanes = document.querySelectorAll('.tab-pane');
  const topbarTitle = document.getElementById('topbarTitle');
  const topbarSubtitle = document.getElementById('topbarSubtitle');
  const scrollBody = document.getElementById('adminScrollBody');

  const tabMeta = {
    dashboard: {
      title: 'Dashboard Overview',
      subtitle: 'Ringkasan operasional toko & stok freezer',
    },
    profile: {
      title: 'Profil Administrator',
      subtitle: 'Kelola informasi akun & kata sandi',
    },
    orders: {
      title: 'Pesanan Pelanggan',
      subtitle: 'Validasi pembayaran & kelola pesanan',
    },
    inventory: {
      title: 'Inventaris Produk',
      subtitle: 'Kelola stok freezer & etalase makanan beku',
    },
    categories: {
      title: 'Kategori Produk',
      subtitle: 'Kelola master kategori etalase',
    },
    reports: {
      title: 'Laporan Penjualan',
      subtitle: 'Rekap omzet & performa transaksi',
    },
  };

  function switchTab(tabId) {
    // Mode Ujian: Jika EXAM_MODE aktif di .env, kunci agar tetap di tab dashboard
    if (window.__EXAM_MODE__ && tabId !== "dashboard") return;

    if (!tabMeta[tabId]) return;

      sidebarBtns.forEach((btn) => {
          btn.classList.toggle(
              "active",
              btn.getAttribute("data-tab") === tabId,
          );
      });

      // Sync Admin Mobile Bottom Nav & Efek Getaran Visual Tab Aktif
      const mainBottomTabs = ['dashboard', 'orders', 'inventory'];
      document.querySelectorAll('.admin-bottom-nav-item[data-tab]').forEach((btn) => {
          const isMatch = btn.getAttribute("data-tab") === tabId;
          const wasActive = btn.classList.contains("active");
          btn.classList.toggle("active", isMatch);
          if (isMatch && !wasActive) {
            btn.classList.remove("tab-just-switched");
            void btn.offsetWidth;
            btn.classList.add("tab-just-switched");
          }
      });
      const bottomMenuBtn = document.getElementById('adminBottomBtnMenu');
      if (bottomMenuBtn) {
          const isMenuMatch = !mainBottomTabs.includes(tabId);
          const wasMenuActive = bottomMenuBtn.classList.contains("active");
          bottomMenuBtn.classList.toggle('active', isMenuMatch);
          if (isMenuMatch && !wasMenuActive) {
            bottomMenuBtn.classList.remove("tab-just-switched");
            void bottomMenuBtn.offsetWidth;
            bottomMenuBtn.classList.add("tab-just-switched");
          }
      }

      tabPanes.forEach((pane) => {
          pane.classList.toggle("active", pane.id === `tab-${tabId}`);
      });

      if (topbarTitle) topbarTitle.textContent = tabMeta[tabId].title;
      if (topbarSubtitle) topbarSubtitle.textContent = tabMeta[tabId].subtitle;

      // Simpan menu aktif sidebar ke sessionStorage (Tab non-sidebar seperti 'profile' direset ke 'dashboard')
      const sidebarTabs = ['dashboard', 'orders', 'inventory', 'categories', 'reports'];
      if (sidebarTabs.includes(tabId)) {
        try {
          sessionStorage.setItem("ica_admin_active_tab", tabId);
        } catch (e) {}
      } else {
        try {
          sessionStorage.setItem("ica_admin_active_tab", "dashboard");
        } catch (e) {}
      }

      try {
          const url = new URL(window.location.href);
          if (tabId === 'dashboard') {
            url.searchParams.delete("tab");
          } else {
            url.searchParams.set("tab", tabId);
          }
          window.history.replaceState({}, "", url.toString());
      } catch (e) {}

      // Close mobile drawer if open
      closeMobileSidebar();

      // Scroll to top smoothly
      if (scrollBody) scrollBody.scrollTop = 0;

      // Render / update chart jika membuka tab reports atau dashboard
      if (tabId === 'reports' && typeof updateSalesReport === 'function') {
        setTimeout(updateSalesReport, 60);
      }
      if (tabId === 'dashboard' && typeof updateDashboardSalesChart === 'function') {
        setTimeout(updateDashboardSalesChart, 60);
      }
  }

  sidebarBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      const tab = this.getAttribute('data-tab');
      if (tab) switchTab(tab);
    });
  });

  // Haptic feedback & getaran lembut saat berpindah menu di mobile (seperti navigasi WhatsApp mobile)
  function triggerNavHaptic() {
    if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
      try {
        navigator.vibrate(20);
      } catch (e) {}
    }
  }

  // Admin Mobile Bottom Nav Click Handlers
  document.querySelectorAll('.admin-bottom-nav-item[data-tab]').forEach((btn) => {
    btn.addEventListener('click', function () {
      triggerNavHaptic();
      const tab = this.getAttribute('data-tab');
      if (tab) {
        closeMobileSidebar();
        switchTab(tab);
      }
    });
  });

  document.getElementById('adminBottomBtnPos')?.addEventListener('click', () => {
    triggerNavHaptic();
    closeMobileSidebar();
    const btn = document.getElementById('btnOpenOfflineOrderModal');
    if (btn) {
      btn.click();
    } else {
      const modal = document.getElementById('modalOfflineOrder');
      if (modal) {
        if (typeof resetPosFilters === 'function') resetPosFilters();
        if (typeof renderPosCart === 'function') renderPosCart();
        modal.classList.add('open');
      }
    }
  });

  document.getElementById('adminBottomBtnMenu')?.addEventListener('click', () => {
    triggerNavHaptic();
    openMobileSidebar();
  });

  // Quick cross-tab links
  document.getElementById('btnOverviewSeeAllOrders')?.addEventListener('click', () => switchTab('orders'));
  document.getElementById('btnOverviewSeeInventory')?.addEventListener('click', () => switchTab('inventory'));
  document.getElementById('btnOverviewSeeReports')?.addEventListener('click', () => {
    switchTab('reports');
    const scrollBody = document.querySelector('.admin-scroll-body') || window;
    if (scrollBody.scrollTo) {
      scrollBody.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      scrollBody.scrollTop = 0;
    }
  });
  document.getElementById('btnSwitchToProfile')?.addEventListener('click', () => {
    closeProfileDropdown();
    switchTab('profile');
  });

  // Restore active tab dari query string URL atau sesi kerja terakhir di sidebar
  const urlParams = new URLSearchParams(window.location.search);
  const paramTab = urlParams.get('tab');
  const savedTab = sessionStorage.getItem('ica_admin_active_tab');
  const sidebarTabs = ['dashboard', 'orders', 'inventory', 'categories', 'reports'];

  if (paramTab && tabMeta[paramTab]) {
    switchTab(paramTab);
  } else if (savedTab && sidebarTabs.includes(savedTab) && tabMeta[savedTab]) {
    switchTab(savedTab);
  } else {
    // Tanpa parameter ?tab dan tanpa savedTab (misal setelah logout / login baru) -> selalu dashboard
    switchTab('dashboard');
  }

  // Event listener pembersihan sesi admin saat logout
  document.querySelectorAll('form[action*="logout"]').forEach((form) => {
    form.addEventListener('submit', () => {
      try {
        sessionStorage.removeItem('ica_admin_active_tab');
      } catch (e) {}
    });
  });

  // --- 4. Kelola Data Profil Admin & Ganti Password ---
  const formAdminProfile = document.getElementById('formAdminProfile');
  const btnSubmitProfile = document.getElementById('btnSubmitProfile');
  const formAdminPassword = document.getElementById('formAdminPassword');
  const btnSubmitPassword = document.getElementById('btnSubmitPassword');

  // Submit Profil Admin via AJAX -> Langsung tersimpan di database
  formAdminProfile?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const name = document.getElementById('profileName')?.value.trim();
    const email = document.getElementById('profileEmail')?.value.trim();
    const phone = document.getElementById('profilePhone')?.value.trim();
    const address = document.getElementById('profileAddress')?.value.trim();

    if (!name || !email) {
      showToast('Nama dan email admin wajib diisi!', 'error');
      return;
    }

    if (btnSubmitProfile) {
      btnSubmitProfile.disabled = true;
      btnSubmitProfile.innerHTML = `<span>Menyimpan...</span>`;
    }

    try {
      const res = await fetch(`${baseUrl}/profile`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-HTTP-Method-Override': 'PATCH',
        },
        body: JSON.stringify({
          name: name,
          email: email,
          phone: phone,
          address: address,
        }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Profil admin berhasil disimpan!');

        // Update UI langsung tanpa refresh
        const initials = (name.substring(0, 2) || 'AD').toUpperCase();
        document.getElementById('topbarAdminName') && (document.getElementById('topbarAdminName').textContent = name);
        document.getElementById('dropdownAdminName') && (document.getElementById('dropdownAdminName').textContent = name);
        document.getElementById('dropdownAdminEmail') && (document.getElementById('dropdownAdminEmail').textContent = email);
        document.getElementById('bannerAdminName') && (document.getElementById('bannerAdminName').textContent = name);
        document.getElementById('profileBannerAdminName') && (document.getElementById('profileBannerAdminName').textContent = name);
        document.getElementById('profileBannerAdminEmail') && (document.getElementById('profileBannerAdminEmail').textContent = email);
        
        const topbarInitials = document.getElementById('topbarAvatarInitials');
        if (topbarInitials) topbarInitials.textContent = initials;
      } else {
        const errorMsg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Gagal menyimpan profil');
        showToast(errorMsg, 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    } finally {
      if (btnSubmitProfile) {
        btnSubmitProfile.disabled = false;
        btnSubmitProfile.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Simpan Profil</span>
        `;
      }
    }
  });

  // Submit Ubah Kata Sandi via AJAX
  formAdminPassword?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const currentPassword = document.getElementById('current_password')?.value;
    const password = document.getElementById('password')?.value;
    const passwordConfirmation = document.getElementById('password_confirmation')?.value;

    if (!currentPassword || !password || !passwordConfirmation) {
      showToast('Semua kolom kata sandi wajib diisi!', 'error');
      return;
    }

    if (password !== passwordConfirmation) {
      showToast('Konfirmasi kata sandi baru tidak cocok!', 'error');
      return;
    }

    if (password.length < 8) {
      showToast('Kata sandi baru minimal 8 karakter!', 'error');
      return;
    }

    if (btnSubmitPassword) {
      btnSubmitPassword.disabled = true;
      btnSubmitPassword.innerHTML = `<span>Memperbarui...</span>`;
    }

    try {
      const res = await fetch(`${baseUrl}/password`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-HTTP-Method-Override': 'PUT',
        },
        body: JSON.stringify({
          current_password: currentPassword,
          password: password,
          password_confirmation: passwordConfirmation,
        }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Kata sandi admin berhasil diperbarui!');
        formAdminPassword.reset();
        formAdminPassword.querySelectorAll('.btn-pwd-eye').forEach(btn => {
          const targetId = btn.getAttribute('data-target');
          const input = document.getElementById(targetId);
          if (input) input.type = 'password';
          const eyeOpen = btn.querySelector('.eye-open-icon');
          const eyeOff = btn.querySelector('.eye-off-icon');
          if (eyeOpen) eyeOpen.style.display = 'block';
          if (eyeOff) eyeOff.style.display = 'none';
        });
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
      if (btnSubmitPassword) {
        btnSubmitPassword.disabled = false;
        btnSubmitPassword.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          <span>Perbarui Kata Sandi</span>
        `;
      }
    }
  });

  // Avatar Upload via Pencil Button -> Langsung disimpan ke database
  const btnPencilAvatar = document.getElementById('btnPencilAvatar');
  const adminAvatarFileInput = document.getElementById('adminAvatarFileInput');

  btnPencilAvatar?.addEventListener('click', () => adminAvatarFileInput?.click());

  adminAvatarFileInput?.addEventListener('change', function () {
    const file = this.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async function (e) {
      const base64Image = e.target.result;

      // Update avatar visually immediately
      const profileAvatarBox = document.getElementById('profileAvatarLarge');
      const topbarAvatarBox = document.getElementById('topbarAvatarBox');

      if (profileAvatarBox) {
        profileAvatarBox.innerHTML = `<img src="${base64Image}" alt="Admin Avatar" style="width: 100%; height: 100%; object-fit: cover;">`;
      }
      if (topbarAvatarBox) {
        topbarAvatarBox.innerHTML = `<img src="${base64Image}" alt="Admin Avatar" style="width: 100%; height: 100%; object-fit: cover;">`;
      }

      // Save avatar to database
      try {
        const res = await fetch(`${baseUrl}/profile`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-HTTP-Method-Override': 'PATCH',
          },
          body: JSON.stringify({
            name: document.getElementById('profileName')?.value || 'Admin Ica',
            email: document.getElementById('profileEmail')?.value || 'admin@icafrozenfood.com',
            avatar: base64Image,
          }),
        });

        const data = await res.json();
        if (res.ok && data.success) {
          showToast('Foto profil admin berhasil disimpan!');
        }
      } catch (err) {
        showToast('Foto profil gagal disimpan.', 'error');
      }
    };
    reader.readAsDataURL(file);
  });

  // Dukungan scroll horizontal halus menggunakan mouse wheel untuk semua toolbar filter pills
  document.querySelectorAll('.toolbar-filters-group').forEach((grp) => {
    grp.addEventListener('wheel', (e) => {
      if (e.deltaY !== 0 && grp.scrollWidth > grp.clientWidth) {
        e.preventDefault();
        grp.scrollLeft += e.deltaY;
      }
    }, { passive: false });
  });

  // --- 5. Manajemen Pesanan, Status Transaksi & Cetak Nota (Tab Orders) ---
  const orderFilterBtns = document.querySelectorAll('.order-filter-btn');
  const orderSearchInput = document.getElementById('orderSearchInput');
  const orderRows = document.querySelectorAll('#ordersTableBody tr[data-order-row]');
  const ordersEmptyFilterRow = document.getElementById('ordersEmptyFilterRow');
  const ordersEmptyTitle = document.getElementById('ordersEmptyTitle');
  const ordersEmptyDesc = document.getElementById('ordersEmptyDesc');
  const btnResetOrderFilter = document.getElementById('btnResetOrderFilter');
  const ordersPaginationWrap = document.getElementById('ordersPaginationWrap');
  let activeOrderStatusFilter = 'Semua';
  let orderCurrentPage = 1;
  const orderPageSize = 10;

  function filterOrders() {
    const q = (orderSearchInput?.value || '').toLowerCase().trim();
    const matchedRows = [];

    orderRows.forEach((row) => {
      let status = row.getAttribute('data-status') || '';
      if (status === 'Menunggu Bayar' || status === 'Menunggu Konfirmasi Admin') {
        status = 'Menunggu Pembayaran';
      }
      const text = row.textContent.toLowerCase();

      const matchStatus = activeOrderStatusFilter === 'Semua' || status === activeOrderStatusFilter;
      const matchSearch = !q || text.includes(q);

      if (matchStatus && matchSearch) {
        matchedRows.push(row);
      } else {
        row.style.display = 'none';
      }
    });

    const totalItems = matchedRows.length;
    const totalPages = Math.ceil(totalItems / orderPageSize) || 1;
    if (orderCurrentPage > totalPages) orderCurrentPage = 1;

    const startIndex = (orderCurrentPage - 1) * orderPageSize;
    const endIndex = startIndex + orderPageSize;

    matchedRows.forEach((row, idx) => {
      if (idx >= startIndex && idx < endIndex) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });

    if (ordersEmptyFilterRow) {
      if (totalItems === 0 && orderRows.length > 0) {
        ordersEmptyFilterRow.style.display = '';
        if (ordersEmptyTitle && ordersEmptyDesc) {
          if (q && activeOrderStatusFilter !== 'Semua') {
            ordersEmptyTitle.textContent = `Tidak Ada Pesanan ${activeOrderStatusFilter}`;
            ordersEmptyDesc.textContent = `Tidak ditemukan pesanan berstatus "${activeOrderStatusFilter}" dengan pencarian "${q}".`;
          } else if (q) {
            ordersEmptyTitle.textContent = 'Pesanan Tidak Ditemukan';
            ordersEmptyDesc.textContent = `Tidak ditemukan transaksi dengan kata kunci "${q}". Silakan periksa kembali no. order atau nama pelanggan.`;
          } else if (activeOrderStatusFilter === 'Dibatalkan') {
            ordersEmptyTitle.textContent = 'Tidak Ada Pesanan Dibatalkan';
            ordersEmptyDesc.textContent = 'Hebat! Belum ada riwayat transaksi pelanggan yang dibatalkan.';
          } else if (activeOrderStatusFilter === 'Menunggu Pembayaran') {
            ordersEmptyTitle.textContent = 'Tidak Ada Antrean Pembayaran';
            ordersEmptyDesc.textContent = 'Semua pesanan saat ini sudah terbayar atau belum ada tagihan tertunda.';
          } else if (activeOrderStatusFilter === 'Diproses') {
            ordersEmptyTitle.textContent = 'Tidak Ada Pesanan Sedang Diproses';
            ordersEmptyDesc.textContent = 'Semua pesanan operasional saat ini sudah disiapkan.';
          } else if (activeOrderStatusFilter === 'Selesai') {
            ordersEmptyTitle.textContent = 'Belum Ada Pesanan Selesai';
            ordersEmptyDesc.textContent = 'Pesanan yang telah diserahkan atau selesai akan tercatat di sini.';
          } else {
            ordersEmptyTitle.textContent = `Tidak Ada Pesanan (${activeOrderStatusFilter})`;
            ordersEmptyDesc.textContent = 'Tidak ada transaksi yang cocok dengan kriteria filter yang dipilih.';
          }
        }
      } else {
        ordersEmptyFilterRow.style.display = 'none';
      }
    }

    renderPaginationControls({
      container: ordersPaginationWrap,
      totalItems: totalItems,
      pageSize: orderPageSize,
      currentPage: orderCurrentPage,
      onPageChange: (newPage) => {
        orderCurrentPage = newPage;
        filterOrders();
        document.getElementById('tab-orders')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  orderFilterBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      orderFilterBtns.forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
      activeOrderStatusFilter = this.getAttribute('data-status') || 'Semua';
      orderCurrentPage = 1;
      filterOrders();
    });
  });

  orderSearchInput?.addEventListener('input', () => {
    orderCurrentPage = 1;
    filterOrders();
  });

  btnResetOrderFilter?.addEventListener('click', function () {
    if (orderSearchInput) orderSearchInput.value = '';
    orderFilterBtns.forEach((b) => {
      if (b.getAttribute('data-status') === 'Semua') {
        b.classList.add('active');
      } else {
        b.classList.remove('active');
      }
    });
    activeOrderStatusFilter = 'Semua';
    orderCurrentPage = 1;
    filterOrders();
  });

  // Inisialisasi awal tabel pesanan & pagination
  filterOrders();

  // Handle Action Buttons in Orders Table -> Langsung tersimpan di database
  document.addEventListener('click', async function (e) {
    // 1. Validasi Bayar
    const btnValidate = e.target.closest('.btn-validate-order');
    if (btnValidate) {
      const orderId = btnValidate.getAttribute('data-order-id');
      const origText = btnValidate.innerHTML;
      btnValidate.disabled = true;
      btnValidate.innerHTML = `<span>Validasi...</span>`;

      try {
        const res = await fetch(`${apiUrl}/orders/${orderId}/status`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ status: 'Diproses', is_paid: true }),
        });

        const data = await res.json();
        if (res.ok && data.success) {
          showToast(`Pembayaran pesanan ${orderId} berhasil divalidasi!`);

          // Update local memory
          const ord = orders.find((o) => o.id === orderId);
          if (ord) {
            ord.status = 'Diproses';
            ord.is_paid = true;
            ord.isPaid = true;
          }
          if (typeof updateDashboardSalesChart === 'function') updateDashboardSalesChart();
          if (typeof updateSalesReport === 'function') updateSalesReport();

          // Update row UI
          const row = document.querySelector(`tr[data-order-row="${orderId}"]`);
          if (row) {
            row.setAttribute('data-status', 'Diproses');
            
            // Update status badge
            const badge = row.querySelector('.status-badge-target');
            if (badge) {
              badge.textContent = 'Diproses';
              badge.className = 'badge-status-pill status-pill-diproses status-badge-target';
            }

            // Update paid badge
            const paidSpan = row.querySelector('.order-paid-status');
            if (paidSpan) {
              paidSpan.textContent = '✓ Lunas';
              paidSpan.className = 'order-paid-status paid';
            }

            // Replace Validasi Bayar with Pesanan Siap button
            const actionsWrap = row.querySelector('.order-actions-flex');
            if (actionsWrap) {
              btnValidate.remove();
              const readyBtn = document.createElement('button');
              readyBtn.className = 'btn-action-ready-order btn-ready-order';
              readyBtn.setAttribute('data-order-id', orderId);
              readyBtn.type = 'button';
              readyBtn.title = 'Selesaikan & Tandai Pesanan Siap';
              readyBtn.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Pesanan Siap</span>
              `;
              actionsWrap.insertBefore(readyBtn, actionsWrap.firstChild);
            }
          }
        } else {
          showToast(data.message || 'Gagal memvalidasi pembayaran', 'error');
          btnValidate.disabled = false;
          btnValidate.innerHTML = origText;
        }
      } catch (err) {
        showToast('Terjadi kesalahan jaringan.', 'error');
        btnValidate.disabled = false;
        btnValidate.innerHTML = origText;
      }
      return;
    }

    // 2. Pesanan Siap
    const btnReady = e.target.closest('.btn-ready-order');
    if (btnReady) {
      const orderId = btnReady.getAttribute('data-order-id');
      const origText = btnReady.innerHTML;
      btnReady.disabled = true;
      btnReady.innerHTML = `<span>Menyimpan...</span>`;

      try {
        const res = await fetch(`${apiUrl}/orders/${orderId}/status`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ status: 'Selesai' }),
        });

        const data = await res.json();
        if (res.ok && data.success) {
          showToast(`Pesanan ${orderId} berhasil diselesaikan!`);

          // Update local memory
          const ord = orders.find((o) => o.id === orderId);
          if (ord) {
            ord.status = 'Selesai';
            ord.is_paid = true;
            ord.isPaid = true;
          }
          if (typeof updateDashboardSalesChart === 'function') updateDashboardSalesChart();
          if (typeof updateSalesReport === 'function') updateSalesReport();

          // Update row UI
          const row = document.querySelector(`tr[data-order-row="${orderId}"]`);
          if (row) {
            row.setAttribute('data-status', 'Selesai');
            
            // Update status badge
            const badge = row.querySelector('.status-badge-target');
            if (badge) {
              badge.textContent = 'Selesai';
              badge.className = 'badge-status-pill status-pill-selesai status-badge-target';
            }

            // Remove Pesanan Siap button (leaving only Cetak Struk)
            btnReady.remove();
          }
        } else {
          showToast(data.message || 'Gagal menyelesaikan pesanan', 'error');
          btnReady.disabled = false;
          btnReady.innerHTML = origText;
        }
      } catch (err) {
        showToast('Terjadi kesalahan jaringan.', 'error');
        btnReady.disabled = false;
        btnReady.innerHTML = origText;
      }
      return;
    }

    // 3. Cetak Struk
    const btnReceipt = e.target.closest('.btn-open-receipt');
    if (btnReceipt) {
      const orderId = btnReceipt.getAttribute('data-order-id');
      if (orderId) openOrderReceipt(orderId);
      return;
    }
  });

  // --- 5b. Toggle Buka/Tutup Item Pesanan Tambahan & Auto-Collapse on Scroll ---
  document.addEventListener('click', function (e) {
    const btnToggleItems = e.target.closest('.btn-toggle-order-items');
    if (btnToggleItems) {
      e.stopPropagation();
      const wrap = btnToggleItems.closest('.order-items-expandable');
      if (!wrap) return;

      const isExpanded = wrap.classList.contains('is-expanded');
      const textSpan = btnToggleItems.querySelector('.toggle-items-text');
      const remainingCount = btnToggleItems.getAttribute('data-more-count') || '';

      if (isExpanded) {
        wrap.classList.remove('is-expanded');
        if (textSpan) textSpan.textContent = `Lihat ${remainingCount} item lainnya`;
      } else {
        wrap.classList.add('is-expanded');
        if (textSpan) textSpan.textContent = 'Tutup item lainnya';
      }
    }
  });

  // Otomatis menutup daftar item yang terbuka & dropdown profil saat halaman discroll
  let orderItemsScrollTimer = null;
  function collapseAllOrderItems() {
    document.querySelectorAll('.order-items-expandable.is-expanded').forEach((wrap) => {
      wrap.classList.remove('is-expanded');
      const btn = wrap.querySelector('.btn-toggle-order-items');
      const textSpan = btn?.querySelector('.toggle-items-text');
      const remainingCount = btn?.getAttribute('data-more-count') || '';
      if (textSpan) textSpan.textContent = `Lihat ${remainingCount} item lainnya`;
    });
  }

  function handleUserScroll() {
    // 1. Tutup dropdown profil jika terbuka saat discroll
    if (adminProfileDropdown?.classList.contains('open')) {
      closeProfileDropdown();
    }

    // 2. Tutup daftar item yang sedang dibuka setelah jeda scroll stabil
    clearTimeout(orderItemsScrollTimer);
    orderItemsScrollTimer = setTimeout(collapseAllOrderItems, 250);
  }

  // Gunakan capture: true agar menangkap scroll di window, .admin-scroll-body, dan .table-responsive
  window.addEventListener('scroll', handleUserScroll, { passive: true, capture: true });

  // Toggle Lihat/Sembunyikan Kata Sandi pada Profil Admin
  document.querySelectorAll('.btn-pwd-eye').forEach((btn) => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const targetId = this.getAttribute('data-target');
      const input = document.getElementById(targetId);
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

  // Thermal Receipt Modal Opener
  const modalReceipt = document.getElementById('modalReceipt');
  const receiptOrderId = document.getElementById('receiptOrderId');
  const receiptOrderDate = document.getElementById('receiptOrderDate');
  const receiptCustName = document.getElementById('receiptCustName');
  const receiptMethod = document.getElementById('receiptMethod');
  const receiptItemsBody = document.getElementById('receiptItemsBody');
  const receiptSubtotal = document.getElementById('receiptSubtotal');
  const receiptIceFee = document.getElementById('receiptIceFee');
  const receiptGrandTotal = document.getElementById('receiptGrandTotal');
  const receiptPayStatus = document.getElementById('receiptPayStatus');

  window.openOrderReceipt = function (orderId) {
    const order = orders.find((o) => o.id === orderId);
    if (!order) {
      showToast('Data pesanan tidak ditemukan!', 'error');
      return;
    }

    if (receiptOrderId) receiptOrderId.textContent = order.id;
    if (receiptOrderDate) receiptOrderDate.textContent = order.order_date || (order.created_at ? new Date(order.created_at).toLocaleString('id-ID') : '-');
    if (receiptCustName) receiptCustName.textContent = order.customer_name || 'Pelanggan Walk-In';
    if (receiptMethod) receiptMethod.textContent = order.method || 'Ambil di Toko';

    const items = Array.isArray(order.items) ? order.items : (typeof order.items === 'string' ? JSON.parse(order.items) : []);
    if (receiptItemsBody) {
      receiptItemsBody.innerHTML = items
        .map(
          (item) => `
        <tr>
          <td style="font-weight: 600;">${item.name}</td>
          <td style="text-align: center;">${item.qty || 1}x</td>
          <td style="text-align: right; font-weight: 700;">Rp ${((item.price || 0) * (item.qty || 1)).toLocaleString('id-ID')}</td>
        </tr>
      `
        )
        .join('');
    }

    const sub = order.subtotal || order.total || 0;
    const ice = order.shipping_fee !== undefined ? order.shipping_fee : (order.shippingFee !== undefined ? order.shippingFee : (order.ice_fee || order.iceFee || 0));
    const grand = order.total || (sub + ice);

    if (receiptSubtotal) receiptSubtotal.textContent = `Rp ${sub.toLocaleString('id-ID')}`;
    if (receiptIceFee) receiptIceFee.textContent = `Rp ${ice.toLocaleString('id-ID')}`;
    if (receiptGrandTotal) receiptGrandTotal.textContent = `Rp ${grand.toLocaleString('id-ID')}`;

    if (receiptPayStatus) {
      if (order.status === 'Dibatalkan') {
        receiptPayStatus.textContent = 'DIBATALKAN';
        receiptPayStatus.style.color = '#DC2626';
      } else if (order.status === 'Selesai' || order.is_paid) {
        receiptPayStatus.textContent = 'LUNAS (SELESAI)';
        receiptPayStatus.style.color = '#059669';
      } else {
        receiptPayStatus.textContent = `STATUS: ${(order.status || 'Menunggu Pembayaran').toUpperCase()}`;
        receiptPayStatus.style.color = '#D97706';
      }
    }

    modalReceipt?.classList.add('open');
  };
  window.openReceiptModal = window.openOrderReceipt;

  window.printThermalReceipt = function () {
    document.body.classList.remove('is-printing-report');
    document.body.classList.add('is-printing-receipt');
    window.print();
  };

  window.addEventListener('afterprint', () => {
    document.body.classList.remove('is-printing-report', 'is-printing-receipt');
  });

  document.addEventListener('click', function (e) {
    const btnReceipt = e.target.closest('.btn-open-receipt');
    if (btnReceipt) {
      const id = btnReceipt.getAttribute('data-order-id');
      if (id) openOrderReceipt(id);
    }
  });

  // --- 6. Manajemen Inventaris Produk & Filter Pencarian (Tab Inventory) ---
  const inventorySearchInput = document.getElementById('inventorySearchInput');
  const inventoryCategoryFilter = document.getElementById('inventoryCategoryFilter');
  const prodFilterBtns = document.querySelectorAll('.prod-filter-btn');
  const inventoryRows = document.querySelectorAll('#inventoryTableBody tr[data-prod-row]');
  const inventoryEmptyFilterRow = document.getElementById('inventoryEmptyFilterRow');
  const inventoryEmptyTitle = document.getElementById('inventoryEmptyTitle');
  const inventoryEmptyDesc = document.getElementById('inventoryEmptyDesc');
  const btnResetInventoryFilter = document.getElementById('btnResetInventoryFilter');
  const inventoryPaginationWrap = document.getElementById('inventoryPaginationWrap');
  let activeInventoryFilter = 'all';
  let inventoryCurrentPage = 1;
  const inventoryPageSize = 10;

  function filterInventory() {
    const q = (inventorySearchInput?.value || '').toLowerCase().trim();
    const selCat = (inventoryCategoryFilter?.value || 'all').toLowerCase().trim();
    const matchedRows = [];

    inventoryRows.forEach((row) => {
      const stock = parseInt(row.getAttribute('data-stock') || '0', 10);
      const rowCat = (row.getAttribute('data-category') || '').toLowerCase().trim();
      const text = row.textContent.toLowerCase();

      // Category filter match
      const matchCat = selCat === 'all' || rowCat === selCat;

      // Stock status filter match
      let matchFilter = true;
      if (activeInventoryFilter === 'in_stock') {
        matchFilter = stock > 5;
      } else if (activeInventoryFilter === 'low') {
        matchFilter = stock <= 5 && stock > 0;
      } else if (activeInventoryFilter === 'zero') {
        matchFilter = stock <= 0;
      }

      // Keyword search match
      const matchSearch = !q || text.includes(q);

      if (matchCat && matchFilter && matchSearch) {
        matchedRows.push(row);
      } else {
        row.style.display = 'none';
      }
    });

    const totalItems = matchedRows.length;
    const totalPages = Math.ceil(totalItems / inventoryPageSize) || 1;
    if (inventoryCurrentPage > totalPages) inventoryCurrentPage = 1;

    const startIndex = (inventoryCurrentPage - 1) * inventoryPageSize;
    const endIndex = startIndex + inventoryPageSize;

    matchedRows.forEach((row, idx) => {
      if (idx >= startIndex && idx < endIndex) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });

    if (inventoryEmptyFilterRow) {
      if (totalItems === 0 && inventoryRows.length > 0) {
        inventoryEmptyFilterRow.style.display = '';
        if (inventoryEmptyTitle && inventoryEmptyDesc) {
          if (q && activeInventoryFilter === 'low') {
            inventoryEmptyTitle.textContent = 'Tidak Ada Produk Stok Menipis';
            inventoryEmptyDesc.textContent = `Tidak ditemukan produk berstok menipis yang sesuai dengan kata kunci "${q}".`;
          } else if (q && activeInventoryFilter === 'zero') {
            inventoryEmptyTitle.textContent = 'Tidak Ada Produk Stok Habis';
            inventoryEmptyDesc.textContent = `Tidak ditemukan produk berstok habis yang sesuai dengan kata kunci "${q}".`;
          } else if (q) {
            inventoryEmptyTitle.textContent = 'Produk Tidak Ditemukan';
            inventoryEmptyDesc.textContent = `Tidak ada produk yang cocok dengan kata kunci "${q}". Silakan periksa kembali nama produk atau SKU.`;
          } else if (activeInventoryFilter === 'low') {
            inventoryEmptyTitle.textContent = 'Semua Stok Aman';
            inventoryEmptyDesc.textContent = 'Hebat! Saat ini tidak ada produk dengan stok menipis (1-5 unit).';
          } else if (activeInventoryFilter === 'zero') {
            inventoryEmptyTitle.textContent = 'Tidak Ada Stok Habis';
            inventoryEmptyDesc.textContent = 'Bagus! Semua produk freezer saat ini memiliki stok.';
          } else {
            inventoryEmptyTitle.textContent = 'Produk Tidak Ditemukan';
            inventoryEmptyDesc.textContent = 'Tidak ada produk yang memenuhi kriteria filter saat ini.';
          }
        }
      } else {
        inventoryEmptyFilterRow.style.display = 'none';
      }
    }

    renderPaginationControls({
      container: inventoryPaginationWrap,
      totalItems: totalItems,
      pageSize: inventoryPageSize,
      currentPage: inventoryCurrentPage,
      onPageChange: (newPage) => {
        inventoryCurrentPage = newPage;
        filterInventory();
        document.getElementById('tab-inventory')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  prodFilterBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      prodFilterBtns.forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
      activeInventoryFilter = this.getAttribute('data-filter') || 'all';
      inventoryCurrentPage = 1;
      filterInventory();
    });
  });

  inventoryCategoryFilter?.addEventListener('change', () => {
    inventoryCurrentPage = 1;
    filterInventory();
  });

  inventorySearchInput?.addEventListener('input', () => {
    inventoryCurrentPage = 1;
    filterInventory();
  });

  btnResetInventoryFilter?.addEventListener('click', function () {
    if (inventorySearchInput) inventorySearchInput.value = '';
    if (inventoryCategoryFilter) inventoryCategoryFilter.value = 'all';
    
    // Reset custom category dropdown display
    if (customCategoryCurrentText) {
      const defaultItem = document.querySelector('.custom-category-item[data-value="all"]');
      if (defaultItem) {
        customCategoryItems.forEach((i) => {
          i.classList.remove('active');
          i.setAttribute('aria-selected', 'false');
        });
        defaultItem.classList.add('active');
        defaultItem.setAttribute('aria-selected', 'true');
        customCategoryCurrentText.textContent = defaultItem.querySelector('span')?.textContent || 'Semua Kategori';
      }
    }

    prodFilterBtns.forEach((b) => {
      if (b.getAttribute('data-filter') === 'all') {
        b.classList.add('active');
      } else {
        b.classList.remove('active');
      }
    });
    activeInventoryFilter = 'all';
    inventoryCurrentPage = 1;
    filterInventory();
  });

  // --- Custom Category Dropdown (Konsisten dengan Filter Dropdown Homepage) ---
  const customCategoryDropdown = document.getElementById('customCategoryDropdown');
  const customCategoryTrigger = document.getElementById('customCategoryTrigger');
  const customCategoryMenu = document.getElementById('customCategoryMenu');
  const customCategoryCurrentText = document.getElementById('customCategoryCurrentText');
  const customCategoryItems = document.querySelectorAll('.custom-category-item');

  if (customCategoryDropdown && customCategoryTrigger && customCategoryMenu) {
    customCategoryTrigger.addEventListener('click', function (e) {
      e.stopPropagation();
      const isOpen = customCategoryMenu.classList.contains('show');
      if (isOpen) {
        customCategoryMenu.classList.remove('show');
        customCategoryTrigger.classList.remove('active');
        customCategoryTrigger.setAttribute('aria-expanded', 'false');
      } else {
        customCategoryMenu.classList.add('show');
        customCategoryTrigger.classList.add('active');
        customCategoryTrigger.setAttribute('aria-expanded', 'true');
      }
    });

    customCategoryItems.forEach((item) => {
      item.addEventListener('click', function (e) {
        e.stopPropagation();
        const val = this.getAttribute('data-value') || 'all';
        const labelText = this.querySelector('span')?.textContent || val;

        customCategoryItems.forEach((i) => {
          i.classList.remove('active');
          i.setAttribute('aria-selected', 'false');
        });
        this.classList.add('active');
        this.setAttribute('aria-selected', 'true');

        if (customCategoryCurrentText) {
          customCategoryCurrentText.textContent = labelText;
        }

        if (inventoryCategoryFilter) {
          inventoryCategoryFilter.value = val;
          inventoryCategoryFilter.dispatchEvent(new Event('change'));
        }

        customCategoryMenu.classList.remove('show');
        customCategoryTrigger.classList.remove('active');
        customCategoryTrigger.setAttribute('aria-expanded', 'false');
      });
    });

    document.addEventListener('click', function (e) {
      if (!customCategoryDropdown.contains(e.target)) {
        customCategoryMenu.classList.remove('show');
        customCategoryTrigger.classList.remove('active');
        customCategoryTrigger.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Inisialisasi awal tabel produk freezer & pagination
  filterInventory();

  // Stock Steppers (+ / -) -> Langsung tersimpan di database
  document.addEventListener('click', async function (e) {
    const btnInc = e.target.closest('.btn-stock-inc');
    const btnDec = e.target.closest('.btn-stock-dec');

    if (btnInc || btnDec) {
      const id = (btnInc || btnDec).getAttribute('data-id');
      const diff = btnInc ? 1 : -1;
      const prod = products.find((p) => p.id === id);

      if (prod) {
        if (btnDec && (prod.stock || 0) <= 0) {
          showToast('Jumlah stok tidak boleh kurang dari 0.', 'error');
          return;
        }
        const nextStock = (prod.stock || 0) + diff;
        if (nextStock < 0) {
          showToast('Jumlah stok tidak boleh kurang dari 0.', 'error');
          return;
        }
        const prevStock = prod.stock || 0;
        prod.stock = nextStock;

        // Update stepper UI target immediately
        document.querySelectorAll(`.stock-val-target[data-id="${id}"]`).forEach((el) => {
          el.textContent = nextStock;
        });

        // Update badge status (Habis, Menipis, atau Cukup)
        document.querySelectorAll(`.stock-badge-container[data-id="${id}"]`).forEach((badgeWrap) => {
          if (nextStock === 0) {
            badgeWrap.innerHTML = `<span class="badge-status" style="background: var(--red-bg); color: var(--red);">Habis</span>`;
          } else if (nextStock < 5) {
            badgeWrap.innerHTML = `<span class="badge-status" style="background: var(--amber-bg); color: var(--amber-text);">Menipis</span>`;
          } else {
            badgeWrap.innerHTML = '';
          }
        });

        const row = document.querySelector(`tr[data-prod-row="${id}"]`);
        if (row) row.setAttribute('data-stock', nextStock);

        // Sync to server via API database
        try {
          const res = await fetch(`${apiUrl}/products/${id}/stock`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ diff: diff, delta: diff, stock: nextStock }),
          });
          const resData = await res.json();
          if (res.ok && resData.success) {
            showToast(`Stok "${prod.name}" berhasil diperbarui: ${nextStock} unit`);
          } else {
            // Revert state jika server menolak
            prod.stock = prevStock;
            document.querySelectorAll(`.stock-val-target[data-id="${id}"]`).forEach((el) => {
              el.textContent = prevStock;
            });
            showToast(resData?.message || 'Jumlah stok tidak boleh kurang dari 0.', 'error');
          }
        } catch (err) {
          console.warn('Background stock sync failed', err);
        }
      }
    }
  });

  // Tombol "Simpan Perubahan" Data Produk -> Bulk Sync ke Database
  document.getElementById('btnSyncProducts')?.addEventListener('click', async function () {
    const btn = this;
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span>Menyimpan...</span>`;

    try {
      const res = await fetch(`${apiUrl}/products/sync`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ products: products }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Seluruh data produk berhasil disimpan!');
      } else {
        showToast('Gagal menyimpan data produk.', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = origHTML;
    }
  });

  // --- 7. Modal Tambah Produk Baru & Pratinjau Gambar ---
  const modalAddProduct = document.getElementById('modalAddProduct');
  const formAddProduct = document.getElementById('formAddProduct');
  const addProdImageFile = document.getElementById('addProdImageFile');
  const addProdPreviewWrap = document.getElementById('addProdPreviewWrap');
  const addProdPreviewImg = document.getElementById('addProdPreviewImg');
  const addProdPreviewName = document.getElementById('addProdPreviewName');
  const addProdDropzone = document.getElementById('addProdDropzone');
  const btnChooseAddProdImg = document.getElementById('btnChooseAddProdImg');
  const btnRemoveAddProdImg = document.getElementById('btnRemoveAddProdImg');

  btnChooseAddProdImg?.addEventListener('click', () => {
    addProdImageFile?.click();
  });

  addProdImageFile?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      showToast('File yang dipilih harus berupa gambar (PNG, JPG, WEBP)!', 'error');
      addProdImageFile.value = '';
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      showToast('Ukuran gambar maksimal 5MB!', 'error');
      addProdImageFile.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = function (evt) {
      if (addProdPreviewImg) addProdPreviewImg.src = evt.target.result;
      if (addProdPreviewName) addProdPreviewName.textContent = file.name;
      if (addProdPreviewWrap) addProdPreviewWrap.style.display = 'flex';
      if (addProdDropzone) addProdDropzone.style.display = 'none';
    };
    reader.readAsDataURL(file);
  });

  btnRemoveAddProdImg?.addEventListener('click', () => {
    if (addProdImageFile) addProdImageFile.value = '';
    if (addProdPreviewImg) addProdPreviewImg.src = '';
    if (addProdPreviewWrap) addProdPreviewWrap.style.display = 'none';
    if (addProdDropzone) addProdDropzone.style.display = 'flex';
  });

  document.getElementById('btnOpenAddProductModal')?.addEventListener('click', () => {
    // Reset form dan preview
    formAddProduct?.reset();
    if (addProdImageFile) addProdImageFile.value = '';
    if (addProdPreviewImg) addProdPreviewImg.src = '';
    if (addProdPreviewWrap) addProdPreviewWrap.style.display = 'none';
    if (addProdDropzone) addProdDropzone.style.display = 'flex';
    modalAddProduct?.classList.add('open');
  });

  formAddProduct?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const name = document.getElementById('addProdName')?.value.trim();
    const category = document.getElementById('addProdCategory')?.value;
    const price = Number(document.getElementById('addProdPrice')?.value);
    const stock = Number(document.getElementById('addProdStock')?.value);
    const weight = document.getElementById('addProdWeight')?.value.trim() || '500 gr';
    const image = document.getElementById('addProdImage')?.value.trim() || `${baseUrl}/images/products/nugget.png`;
    const desc = document.getElementById('addProdDesc')?.value.trim();

    if (!name || price <= 0) {
      showToast('Nama dan harga valid harus diisi!', 'error');
      return;
    }

    if (isNaN(stock) || stock < 0) {
      showToast('Jumlah stok tidak boleh kurang dari 0.', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('category', category);
    formData.append('price', price);
    formData.append('stock', stock);
    formData.append('weight', weight);
    formData.append('description', desc);

    if (addProdImageFile && addProdImageFile.files[0]) {
      formData.append('image_file', addProdImageFile.files[0]);
    } else {
      formData.append('image', image);
    }

    try {
      const res = await fetch(`${apiUrl}/products`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: formData,
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Produk baru berhasil disimpan!');
        modalAddProduct?.classList.remove('open');
        formAddProduct.reset();
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message || 'Gagal menambah produk', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    }
  });

  // --- 8. Modal Edit Produk & Pratinjau Gambar ---
  const modalEditProduct = document.getElementById('modalEditProduct');
  const formEditProduct = document.getElementById('formEditProduct');
  const editProdId = document.getElementById('editProdId');
  const editProdName = document.getElementById('editProdName');
  const editProdCategory = document.getElementById('editProdCategory');
  const editProdPrice = document.getElementById('editProdPrice');
  const editProdStock = document.getElementById('editProdStock');
  const editProdWeight = document.getElementById('editProdWeight');
  const editProdImage = document.getElementById('editProdImage');
  const editProdDesc = document.getElementById('editProdDesc');
  const editProdImageFile = document.getElementById('editProdImageFile');
  const editProdPreviewImg = document.getElementById('editProdPreviewImg');
  const editProdPreviewName = document.getElementById('editProdPreviewName');
  const btnChooseEditProdImg = document.getElementById('btnChooseEditProdImg');

  btnChooseEditProdImg?.addEventListener('click', () => {
    editProdImageFile?.click();
  });

  editProdImageFile?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      showToast('File yang dipilih harus berupa gambar (PNG, JPG, WEBP)!', 'error');
      editProdImageFile.value = '';
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      showToast('Ukuran gambar maksimal 5MB!', 'error');
      editProdImageFile.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = function (evt) {
      if (editProdPreviewImg) editProdPreviewImg.src = evt.target.result;
      if (editProdPreviewName) editProdPreviewName.textContent = file.name + ' (Foto Baru)';
    };
    reader.readAsDataURL(file);
  });

  document.addEventListener('click', function (e) {
    const btnEdit = e.target.closest('.btn-edit-product');
    if (btnEdit) {
      const id = btnEdit.getAttribute('data-id');
      const prod = products.find((p) => p.id === id);
      if (prod) {
        if (editProdId) editProdId.value = prod.id;
        if (editProdName) editProdName.value = prod.name;
        if (editProdCategory) editProdCategory.value = prod.category;
        if (editProdPrice) editProdPrice.value = prod.price;
        if (editProdStock) editProdStock.value = prod.stock;
        if (editProdWeight) editProdWeight.value = prod.weight || '500 gr';
        if (editProdImage) editProdImage.value = prod.image || '';
        if (editProdDesc) editProdDesc.value = prod.description || '';

        // Reset file input dan muat foto tersimpan
        if (editProdImageFile) editProdImageFile.value = '';
        if (editProdPreviewImg) editProdPreviewImg.src = prod.image || `${baseUrl}/images/products/nugget.png`;
        if (editProdPreviewName) {
          const fileName = prod.image ? (prod.image.split('/').pop() || 'Foto Produk') : 'Foto Default';
          editProdPreviewName.textContent = fileName;
        }

        modalEditProduct?.classList.add('open');
      }
    }
  });

  formEditProduct?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const id = editProdId?.value;
    const name = editProdName?.value.trim();
    const category = editProdCategory?.value;
    const price = Number(editProdPrice?.value);
    const stock = Number(editProdStock?.value);
    const weight = editProdWeight?.value.trim();
    const image = editProdImage?.value.trim();
    const desc = editProdDesc?.value.trim();

    if (!name || price <= 0) {
      showToast('Nama dan harga valid harus diisi!', 'error');
      return;
    }

    if (isNaN(stock) || stock < 0) {
      showToast('Jumlah stok tidak boleh kurang dari 0.', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('name', name);
    formData.append('category', category);
    formData.append('price', price);
    formData.append('stock', stock);
    formData.append('weight', weight);
    formData.append('description', desc);

    if (editProdImageFile && editProdImageFile.files[0]) {
      formData.append('image_file', editProdImageFile.files[0]);
    } else {
      formData.append('image', image);
    }

    try {
      const res = await fetch(`${apiUrl}/products`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: formData,
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Perubahan produk berhasil disimpan!');
        modalEditProduct?.classList.remove('open');
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message || 'Gagal menyimpan perubahan produk', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    }
  });

  // Hapus Produk
  document.addEventListener('click', async function (e) {
    const btnDelete = e.target.closest('.btn-delete-product');
    if (btnDelete) {
      const id = btnDelete.getAttribute('data-id');
      const prodObj = products.find((p) => p.id === id);
      const prodTitle = prodObj ? prodObj.name : `SKU "${id}"`;
      if (confirm(`Apakah Anda yakin ingin menghapus produk "${prodTitle}"?`)) {
        try {
          const res = await fetch(`${apiUrl}/products/${id}`, {
            method: 'DELETE',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
          });

          const data = await res.json();
          if (res.ok && data.success) {
            showToast('Produk berhasil dihapus!');
            document.querySelector(`tr[data-prod-row="${id}"]`)?.remove();
          } else {
            showToast(data.message || 'Gagal menghapus produk', 'error');
          }
        } catch (err) {
          showToast('Gagal terhubung ke server.', 'error');
        }
      }
    }
  });

  // --- 8b. Manajemen Paket Hemat Promo (Bundling) di Sub-tab Inventaris ---
  const subtabBtnProducts = document.getElementById('subtabBtnProducts');
  const subtabBtnBundles = document.getElementById('subtabBtnBundles');
  const subtabContentProducts = document.getElementById('subtabContentProducts');
  const subtabContentBundles = document.getElementById('subtabContentBundles');

  function switchInventorySubtab(tabName) {
    const isBundles = tabName === 'bundles';

    document.querySelectorAll('.btn-switch-subtab').forEach((btn) => {
      const match = btn.getAttribute('data-subtab') === tabName;
      btn.classList.toggle('active', match);
      btn.setAttribute('aria-selected', match ? 'true' : 'false');
    });

    if (subtabContentProducts) subtabContentProducts.style.display = isBundles ? 'none' : 'block';
    if (subtabContentBundles) subtabContentBundles.style.display = isBundles ? 'block' : 'none';
    document.documentElement.classList.toggle('init-subtab-bundles', isBundles);
    sessionStorage.setItem('ica_inventory_subtab', isBundles ? 'bundles' : 'products');
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-switch-subtab');
    if (btn) {
      const subtab = btn.getAttribute('data-subtab');
      if (subtab) switchInventorySubtab(subtab);
    }
  });

  // Restore subtab pilihan terakhir dari session storage atau URL param
  const currentUrlParams = new URLSearchParams(window.location.search);
  if (currentUrlParams.get('subtab') === 'bundles' || sessionStorage.getItem('ica_inventory_subtab') === 'bundles') {
    switchInventorySubtab('bundles');
  }

  // Filter / Search Paket Hemat di Tabel
  const bundleSearchInput = document.getElementById('bundleSearchInput');
  const bundlesEmptyFilterRow = document.getElementById('bundlesEmptyFilterRow');
  const btnResetBundleFilter = document.getElementById('btnResetBundleFilter');
  const bundlesPaginationWrap = document.getElementById('bundlesPaginationWrap');
  const bundleStatusFilterBtns = document.querySelectorAll('.bundle-filter-btn, .btn-bundle-status-filter');
  let activeBundleStatus = 'all';
  let bundleCurrentPage = 1;
  const bundlePageSize = 10;

  function filterBundles() {
    const q = (bundleSearchInput?.value || '').toLowerCase().trim();
    const bundleRows = document.querySelectorAll('.bundle-row-item');
    const matchedRows = [];

    bundleRows.forEach((row) => {
      const name = (row.getAttribute('data-name') || '').toLowerCase();
      const id = (row.getAttribute('data-id') || '').toLowerCase();
      const isActive = row.getAttribute('data-active') === '1';

      let matchStatus = true;
      if (activeBundleStatus === 'active') matchStatus = isActive;
      else if (activeBundleStatus === 'inactive') matchStatus = !isActive;

      const matchSearch = !q || name.includes(q) || id.includes(q);

      if (matchStatus && matchSearch) {
        matchedRows.push(row);
      } else {
        row.style.display = 'none';
      }
    });

    const totalItems = matchedRows.length;
    const totalPages = Math.ceil(totalItems / bundlePageSize) || 1;
    if (bundleCurrentPage > totalPages) bundleCurrentPage = 1;

    const startIndex = (bundleCurrentPage - 1) * bundlePageSize;
    const endIndex = startIndex + bundlePageSize;

    matchedRows.forEach((row, idx) => {
      if (idx >= startIndex && idx < endIndex) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });

    if (bundlesEmptyFilterRow) {
      if (totalItems === 0 && bundleRows.length > 0) {
        bundlesEmptyFilterRow.style.display = '';
      } else {
        bundlesEmptyFilterRow.style.display = 'none';
      }
    }

    renderPaginationControls({
      container: bundlesPaginationWrap,
      totalItems: totalItems,
      pageSize: bundlePageSize,
      currentPage: bundleCurrentPage,
      onPageChange: (newPage) => {
        bundleCurrentPage = newPage;
        filterBundles();
        document.getElementById('inventorySubtabBundles')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  bundleStatusFilterBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      bundleStatusFilterBtns.forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
      activeBundleStatus = this.getAttribute('data-status') || this.getAttribute('data-filter') || 'all';
      bundleCurrentPage = 1;
      filterBundles();
    });
  });

  bundleSearchInput?.addEventListener('input', () => {
    bundleCurrentPage = 1;
    filterBundles();
  });

  btnResetBundleFilter?.addEventListener('click', function () {
    if (bundleSearchInput) bundleSearchInput.value = '';
    bundleStatusFilterBtns.forEach((b) => {
      const st = b.getAttribute('data-status') || b.getAttribute('data-filter') || '';
      if (st === 'all') {
        b.classList.add('active');
      } else {
        b.classList.remove('active');
      }
    });
    activeBundleStatus = 'all';
    bundleCurrentPage = 1;
    filterBundles();
  });

  // Inisialisasi awal tabel paket hemat & pagination
  filterBundles();

  // Search Produk di Modal Tambah & Edit Paket Hemat
  const searchAddBundleProdInput = document.getElementById('searchAddBundleProdInput');
  searchAddBundleProdInput?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#modalAddBundle .bundle-prod-choice-row').forEach((row) => {
      const name = (row.getAttribute('data-prod-name') || row.textContent).toLowerCase();
      row.style.display = !q || name.includes(q) ? 'flex' : 'none';
    });
  });

  const searchEditBundleProdInput = document.getElementById('searchEditBundleProdInput');
  searchEditBundleProdInput?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#modalEditBundle .bundle-prod-choice-row').forEach((row) => {
      const name = (row.getAttribute('data-prod-name') || row.textContent).toLowerCase();
      row.style.display = !q || name.includes(q) ? 'flex' : 'none';
    });
  });

  // Modal Tambah Paket Hemat
  const modalAddBundle = document.getElementById('modalAddBundle');
  const btnOpenAddBundleModal = document.getElementById('btnOpenAddBundleModal');
  const formAddBundle = document.getElementById('formAddBundle');

  btnOpenAddBundleModal?.addEventListener('click', function () {
    formAddBundle?.reset();
    if (searchAddBundleProdInput) {
      searchAddBundleProdInput.value = '';
      document.querySelectorAll('#modalAddBundle .bundle-prod-choice-row').forEach((r) => r.style.display = 'flex');
    }
    const badgeRight = document.getElementById('addBundleBadgeRight');
    if (badgeRight) {
      badgeRight.value = '';
      delete badgeRight.dataset.manual;
    }
    document.querySelectorAll('.cb-bundle-prod').forEach((cb) => {
      cb.checked = false;
      const qtyInput = document.querySelector(`.qty-bundle-prod[data-id="${cb.getAttribute('data-id')}"]`);
      if (qtyInput) {
        qtyInput.value = 1;
        qtyInput.disabled = true;
      }
    });
    updateAddBundleCalculations();
    const previewWrap = document.getElementById('addBundlePreviewWrap');
    const dropzone = document.getElementById('addBundleDropzone');
    if (previewWrap) previewWrap.style.display = 'none';
    if (dropzone) dropzone.style.display = 'flex';
    modalAddBundle?.classList.add('open');
  });

  function updateAddBundleCalculations() {
    let totalNormal = 0;
    let selectedCount = 0;
    document.querySelectorAll('.cb-bundle-prod:checked').forEach((cb) => {
      selectedCount++;
      const price = parseInt(cb.getAttribute('data-price') || '0', 10);
      const qtyInput = document.querySelector(`.qty-bundle-prod[data-id="${cb.getAttribute('data-id')}"]`);
      const qty = parseInt(qtyInput?.value || '1', 10);
      totalNormal += price * qty;
    });

    const origInput = document.getElementById('addBundleOriginalPrice');
    if (origInput) origInput.value = totalNormal;

    const note = document.getElementById('addBundleCalcNote');
    if (note) {
      note.textContent = selectedCount > 0
        ? `${selectedCount} produk terpilih (Total Normal: Rp ${totalNormal.toLocaleString('id-ID')})`
        : 'Pilih minimal 1 produk';
    }

    const priceInput = document.getElementById('addBundlePrice');
    const badgeRightInput = document.getElementById('addBundleBadgeRight');
    if (priceInput && badgeRightInput && (!badgeRightInput.dataset.manual || badgeRightInput.value.trim() === '')) {
      const p = parseInt(priceInput.value || '0', 10);
      if (totalNormal > p && p > 0) {
        badgeRightInput.value = `Hemat Rp ${(totalNormal - p).toLocaleString('id-ID')}`;
      } else if (p >= totalNormal && !badgeRightInput.dataset.manual) {
        badgeRightInput.value = '';
      }
    }
  }

  document.querySelectorAll('.cb-bundle-prod').forEach((cb) => {
    cb.addEventListener('change', function () {
      const qtyInput = document.querySelector(`.qty-bundle-prod[data-id="${this.getAttribute('data-id')}"]`);
      if (qtyInput) {
        qtyInput.disabled = !this.checked;
        if (this.checked && parseInt(qtyInput.value || '0', 10) < 1) qtyInput.value = 1;
      }
      updateAddBundleCalculations();
    });
  });

  document.querySelectorAll('.qty-bundle-prod').forEach((qty) => {
    qty.addEventListener('input', updateAddBundleCalculations);
  });

  document.getElementById('addBundlePrice')?.addEventListener('input', function () {
    const badgeRightInput = document.getElementById('addBundleBadgeRight');
    const totalNormal = parseInt(document.getElementById('addBundleOriginalPrice')?.value || '0', 10);
    const p = parseInt(this.value || '0', 10);
    if (badgeRightInput && (!badgeRightInput.dataset.manual || badgeRightInput.value.trim() === '')) {
      if (totalNormal > p && p > 0) {
        badgeRightInput.value = `Hemat Rp ${(totalNormal - p).toLocaleString('id-ID')}`;
      } else if (p >= totalNormal && !badgeRightInput.dataset.manual) {
        badgeRightInput.value = '';
      }
    }
  });

  document.getElementById('addBundleBadgeRight')?.addEventListener('input', function () {
    if (this.value.trim() === '') {
      delete this.dataset.manual;
      updateAddBundleCalculations();
    } else {
      this.dataset.manual = 'true';
    }
  });

  document.getElementById('btnAutoCalcAddBadge')?.addEventListener('click', function () {
    const badge = document.getElementById('addBundleBadgeRight');
    if (badge) {
      delete badge.dataset.manual;
      updateAddBundleCalculations();
    }
  });

  // Upload Foto Paket Baru
  const addBundleImageFile = document.getElementById('addBundleImageFile');
  const btnChooseAddBundleImg = document.getElementById('btnChooseAddBundleImg');
  const addBundlePreviewWrap = document.getElementById('addBundlePreviewWrap');
  const addBundlePreviewImg = document.getElementById('addBundlePreviewImg');
  const addBundlePreviewName = document.getElementById('addBundlePreviewName');
  const addBundleDropzone = document.getElementById('addBundleDropzone');
  const btnRemoveAddBundleImg = document.getElementById('btnRemoveAddBundleImg');

  btnChooseAddBundleImg?.addEventListener('click', () => addBundleImageFile?.click());
  btnRemoveAddBundleImg?.addEventListener('click', () => {
    if (addBundleImageFile) addBundleImageFile.value = '';
    if (addBundlePreviewWrap) addBundlePreviewWrap.style.display = 'none';
    if (addBundleDropzone) addBundleDropzone.style.display = 'flex';
  });

  addBundleImageFile?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showToast('File harus berupa gambar (PNG, JPG, WEBP)!', 'error');
      addBundleImageFile.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function (evt) {
      if (addBundlePreviewImg) addBundlePreviewImg.src = evt.target.result;
      if (addBundlePreviewName) addBundlePreviewName.textContent = file.name;
      if (addBundleDropzone) addBundleDropzone.style.display = 'none';
      if (addBundlePreviewWrap) addBundlePreviewWrap.style.display = 'flex';
    };
    reader.readAsDataURL(file);
  });

  formAddBundle?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const name = document.getElementById('addBundleName')?.value.trim();
    const badgeLeft = document.getElementById('addBundleBadgeLeft')?.value.trim() || 'Paling Laris';
    const badgeRight = document.getElementById('addBundleBadgeRight')?.value.trim();
    const desc = document.getElementById('addBundleDesc')?.value.trim();
    const bundlePrice = document.getElementById('addBundlePrice')?.value;
    const origPrice = document.getElementById('addBundleOriginalPrice')?.value;

    const checkedBoxes = Array.from(document.querySelectorAll('.cb-bundle-prod:checked'));
    if (checkedBoxes.length === 0) {
      showToast('Pilih minimal 1 produk relasional untuk isi paket!', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('badge_left', badgeLeft);
    if (badgeRight) formData.append('badge_right', badgeRight);
    if (desc) formData.append('description', desc);
    formData.append('bundle_price', bundlePrice);
    if (origPrice) formData.append('original_price', origPrice);

    checkedBoxes.forEach((cb, idx) => {
      const pid = cb.getAttribute('data-id');
      const qtyInput = document.querySelector(`.qty-bundle-prod[data-id="${pid}"]`);
      const qty = qtyInput?.value || 1;
      formData.append(`items[${idx}][product_id]`, pid);
      formData.append(`items[${idx}][quantity]`, qty);
    });

    if (addBundleImageFile?.files[0]) {
      formData.append('image_file', addBundleImageFile.files[0]);
    }

    try {
      const res = await fetch(`${apiUrl}/bundles`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: formData,
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Paket hemat berhasil dibuat dan langsung tampil di homepage!');
        modalAddBundle?.classList.remove('open');
        sessionStorage.setItem('ica_inventory_subtab', 'bundles');
        setTimeout(() => {
          window.location.href = `${window.location.pathname}?tab=inventory&subtab=bundles`;
        }, 350);
      } else {
        showToast(data.message || 'Gagal menyimpan paket hemat.', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    }
  });

  // Modal Edit Paket Hemat
  const modalEditBundle = document.getElementById('modalEditBundle');
  const formEditBundle = document.getElementById('formEditBundle');
  const editBundleId = document.getElementById('editBundleId');
  const editBundleName = document.getElementById('editBundleName');
  const editBundleBadgeLeft = document.getElementById('editBundleBadgeLeft');
  const editBundleBadgeRight = document.getElementById('editBundleBadgeRight');
  const editBundleDesc = document.getElementById('editBundleDesc');
  const editBundlePrice = document.getElementById('editBundlePrice');
  const editBundleOriginalPrice = document.getElementById('editBundleOriginalPrice');
  const editBundleImageFile = document.getElementById('editBundleImageFile');
  const editBundlePreviewImg = document.getElementById('editBundlePreviewImg');
  const editBundlePreviewName = document.getElementById('editBundlePreviewName');
  const btnChooseEditBundleImg = document.getElementById('btnChooseEditBundleImg');

  btnChooseEditBundleImg?.addEventListener('click', () => editBundleImageFile?.click());

  editBundleImageFile?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showToast('File harus berupa gambar (PNG, JPG, WEBP)!', 'error');
      editBundleImageFile.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function (evt) {
      if (editBundlePreviewImg) editBundlePreviewImg.src = evt.target.result;
      if (editBundlePreviewName) editBundlePreviewName.textContent = file.name + ' (Foto Baru)';
    };
    reader.readAsDataURL(file);
  });

  function updateEditBundleCalculations() {
    let totalNormal = 0;
    let selectedCount = 0;
    document.querySelectorAll('.cb-edit-bundle-prod:checked').forEach((cb) => {
      selectedCount++;
      const price = parseInt(cb.getAttribute('data-price') || '0', 10);
      const qtyInput = document.querySelector(`.qty-edit-bundle-prod[data-id="${cb.getAttribute('data-id')}"]`);
      const qty = parseInt(qtyInput?.value || '1', 10);
      totalNormal += price * qty;
    });

    if (editBundleOriginalPrice) editBundleOriginalPrice.value = totalNormal;
    const note = document.getElementById('editBundleCalcNote');
    if (note) {
      note.textContent = selectedCount > 0
        ? `${selectedCount} produk terpilih (Total Normal: Rp ${totalNormal.toLocaleString('id-ID')})`
        : 'Pilih minimal 1 produk';
    }

    if (editBundlePrice && editBundleBadgeRight && (!editBundleBadgeRight.dataset.manual || editBundleBadgeRight.value.trim() === '')) {
      const p = parseInt(editBundlePrice.value || '0', 10);
      if (totalNormal > p && p > 0) {
        editBundleBadgeRight.value = `Hemat Rp ${(totalNormal - p).toLocaleString('id-ID')}`;
      } else if (p >= totalNormal && !editBundleBadgeRight.dataset.manual) {
        editBundleBadgeRight.value = '';
      }
    }
  }

  document.querySelectorAll('.cb-edit-bundle-prod').forEach((cb) => {
    cb.addEventListener('change', function () {
      const qtyInput = document.querySelector(`.qty-edit-bundle-prod[data-id="${this.getAttribute('data-id')}"]`);
      if (qtyInput) {
        qtyInput.disabled = !this.checked;
        if (this.checked && parseInt(qtyInput.value || '0', 10) < 1) qtyInput.value = 1;
      }
      updateEditBundleCalculations();
    });
  });

  document.querySelectorAll('.qty-edit-bundle-prod').forEach((qty) => {
    qty.addEventListener('input', updateEditBundleCalculations);
  });

  editBundlePrice?.addEventListener('input', function () {
    const totalNormal = parseInt(editBundleOriginalPrice?.value || '0', 10);
    const p = parseInt(this.value || '0', 10);
    if (editBundleBadgeRight && (!editBundleBadgeRight.dataset.manual || editBundleBadgeRight.value.trim() === '')) {
      if (totalNormal > p && p > 0) {
        editBundleBadgeRight.value = `Hemat Rp ${(totalNormal - p).toLocaleString('id-ID')}`;
      } else if (p >= totalNormal && !editBundleBadgeRight.dataset.manual) {
        editBundleBadgeRight.value = '';
      }
    }
  });

  editBundleBadgeRight?.addEventListener('input', function () {
    if (this.value.trim() === '') {
      delete this.dataset.manual;
      updateEditBundleCalculations();
    } else {
      this.dataset.manual = 'true';
    }
  });

  document.getElementById('btnAutoCalcEditBadge')?.addEventListener('click', function () {
    if (editBundleBadgeRight) {
      delete editBundleBadgeRight.dataset.manual;
      updateEditBundleCalculations();
    }
  });

  document.addEventListener('click', function (e) {
    const btnEdit = e.target.closest('.btn-edit-bundle');
    if (btnEdit) {
      const id = btnEdit.getAttribute('data-id');
      const bundle = bundles.find((b) => b.id === id);
      if (bundle) {
        if (editBundleId) editBundleId.value = bundle.id;
        if (editBundleName) editBundleName.value = bundle.name;
        if (editBundleBadgeLeft) editBundleBadgeLeft.value = bundle.badge_left || '';
        if (editBundleBadgeRight) {
          const badgeVal = bundle.badge_right || '';
          editBundleBadgeRight.value = badgeVal;
          // Jika kosong atau diawali "Hemat Rp", biarkan auto-calc tetap aktif saat diedit
          if (!badgeVal || /^hemat\s*rp/i.test(badgeVal.trim())) {
            delete editBundleBadgeRight.dataset.manual;
          } else {
            editBundleBadgeRight.dataset.manual = 'true';
          }
        }
        if (editBundleDesc) editBundleDesc.value = bundle.description || '';
        if (editBundlePrice) editBundlePrice.value = bundle.bundle_price;
        if (editBundleOriginalPrice) editBundleOriginalPrice.value = bundle.original_price;

        if (editBundlePreviewImg) editBundlePreviewImg.src = bundle.image || '/images/products/nugget.png';
        if (editBundlePreviewName) editBundlePreviewName.textContent = bundle.name;
        if (editBundleImageFile) editBundleImageFile.value = '';

        // Reset dan centang checkbox produk sesuai relasi paket
        const itemMap = {};
        if (bundle.items) {
          bundle.items.forEach((it) => {
            itemMap[it.product_id] = it.quantity || 1;
          });
        }

        document.querySelectorAll('.cb-edit-bundle-prod').forEach((cb) => {
          const pid = cb.getAttribute('data-id');
          const isIncluded = Object.prototype.hasOwnProperty.call(itemMap, pid);
          cb.checked = isIncluded;
          const qtyInput = document.querySelector(`.qty-edit-bundle-prod[data-id="${pid}"]`);
          if (qtyInput) {
            qtyInput.disabled = !isIncluded;
            qtyInput.value = isIncluded ? itemMap[pid] : 1;
          }
        });

        if (searchEditBundleProdInput) {
          searchEditBundleProdInput.value = '';
          document.querySelectorAll('#modalEditBundle .bundle-prod-choice-row').forEach((r) => r.style.display = 'flex');
        }

        updateEditBundleCalculations();
        modalEditBundle?.classList.add('open');
      }
    }
  });

  formEditBundle?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const id = editBundleId?.value;
    const name = editBundleName?.value.trim();
    const badgeLeft = editBundleBadgeLeft?.value.trim() || 'Paling Laris';
    const badgeRight = editBundleBadgeRight?.value.trim();
    const desc = editBundleDesc?.value.trim();
    const bundlePrice = editBundlePrice?.value;
    const origPrice = editBundleOriginalPrice?.value;

    const checkedBoxes = Array.from(document.querySelectorAll('.cb-edit-bundle-prod:checked'));
    if (checkedBoxes.length === 0) {
      showToast('Pilih minimal 1 produk relasional untuk isi paket!', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('name', name);
    formData.append('badge_left', badgeLeft);
    if (badgeRight) formData.append('badge_right', badgeRight);
    if (desc) formData.append('description', desc);
    formData.append('bundle_price', bundlePrice);
    if (origPrice) formData.append('original_price', origPrice);

    checkedBoxes.forEach((cb, idx) => {
      const pid = cb.getAttribute('data-id');
      const qtyInput = document.querySelector(`.qty-edit-bundle-prod[data-id="${pid}"]`);
      const qty = qtyInput?.value || 1;
      formData.append(`items[${idx}][product_id]`, pid);
      formData.append(`items[${idx}][quantity]`, qty);
    });

    if (editBundleImageFile?.files[0]) {
      formData.append('image_file', editBundleImageFile.files[0]);
    }

    try {
      const res = await fetch(`${apiUrl}/bundles`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: formData,
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Paket hemat berhasil diperbarui!');
        modalEditBundle?.classList.remove('open');
        sessionStorage.setItem('ica_inventory_subtab', 'bundles');
        setTimeout(() => {
          window.location.href = `${window.location.pathname}?tab=inventory&subtab=bundles`;
        }, 350);
      } else {
        showToast(data.message || 'Gagal memperbarui paket.', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    }
  });

  // Toggle status paket aktif / nonaktif
  document.addEventListener('click', async function (e) {
    const btnToggle = e.target.closest('.btn-toggle-bundle-status');
    if (btnToggle) {
      const id = btnToggle.getAttribute('data-id');
      try {
        const res = await fetch(`${apiUrl}/bundles/${id}/toggle`, {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast(data.message || 'Status paket berhasil diubah!');
          sessionStorage.setItem('ica_inventory_subtab', 'bundles');
          setTimeout(() => {
            window.location.href = `${window.location.pathname}?tab=inventory&subtab=bundles`;
          }, 350);
        } else {
          showToast(data.message || 'Gagal mengubah status paket.', 'error');
        }
      } catch (err) {
        showToast('Gagal terhubung ke server.', 'error');
      }
    }
  });

  // Hapus paket hemat
  document.addEventListener('click', async function (e) {
    const btnDelete = e.target.closest('.btn-delete-bundle');
    if (btnDelete) {
      const id = btnDelete.getAttribute('data-id');
      if (!confirm(`Apakah Anda yakin ingin menghapus paket promo ${id}?`)) {
        return;
      }
      try {
        const res = await fetch(`${apiUrl}/bundles/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast('Paket hemat berhasil dihapus dari sistem!');
          const row = document.querySelector(`.bundle-row-item[data-id="${id}"]`);
          if (row) row.remove();
          sessionStorage.setItem('ica_inventory_subtab', 'bundles');
          setTimeout(() => {
            window.location.href = `${window.location.pathname}?tab=inventory&subtab=bundles`;
          }, 350);
        } else {
          showToast(data.message || 'Gagal menghapus paket.', 'error');
        }
      } catch (err) {
        showToast('Gagal terhubung ke server.', 'error');
      }
    }
  });

  // --- 9. Manajemen Master Data Kategori (Tab Categories) ---
  const categorySearchInput = document.getElementById('categorySearchInput');
  const btnCatViewList = document.getElementById('btnCatViewList');
  const btnCatViewGrid = document.getElementById('btnCatViewGrid');
  const catListView = document.getElementById('categoriesListViewContainer');
  const catGridView = document.getElementById('categoriesGridViewContainer');

  function setCategoryView(mode) {
    if (mode === 'grid') {
      btnCatViewGrid?.classList.add('active');
      btnCatViewList?.classList.remove('active');
      if (catGridView) catGridView.style.display = 'grid';
      if (catListView) catListView.style.display = 'none';
      sessionStorage.setItem('ica_cat_view_mode', 'grid');
    } else {
      btnCatViewList?.classList.add('active');
      btnCatViewGrid?.classList.remove('active');
      if (catListView) catListView.style.display = 'block';
      if (catGridView) catGridView.style.display = 'none';
      sessionStorage.setItem('ica_cat_view_mode', 'list');
    }
  }

  btnCatViewList?.addEventListener('click', () => setCategoryView('list'));
  btnCatViewGrid?.addEventListener('click', () => setCategoryView('grid'));

  const savedCatView = sessionStorage.getItem('ica_cat_view_mode');
  if (savedCatView === 'grid') {
    setCategoryView('grid');
  }

  const categoriesEmptyFilterRow = document.getElementById('categoriesEmptyFilterRow');
  const btnResetCategoryFilter = document.getElementById('btnResetCategoryFilter');
  const categoriesGridEmptyFilterBox = document.getElementById('categoriesGridEmptyFilterBox');
  const btnResetCategoryGridFilter = document.getElementById('btnResetCategoryGridFilter');
  const categoriesPaginationWrap = document.getElementById('categoriesPaginationWrap');
  let categoryCurrentPage = 1;
  const categoryPageSize = 10;

  function filterCategories() {
    const q = (categorySearchInput?.value || '').toLowerCase().trim();
    
    // Filter Card Grid
    const categoryCards = document.querySelectorAll('.category-item-card');
    const matchedCards = [];
    categoryCards.forEach((card) => {
      const name = (card.getAttribute('data-cat-name') || '').toLowerCase();
      const match = !q || name.includes(q);
      if (match) matchedCards.push(card);
      else card.style.display = 'none';
    });

    // Filter List Table Rows
    const categoryRows = document.querySelectorAll('#categoriesTableBody tr[data-cat-row]');
    const matchedRows = [];
    categoryRows.forEach((row) => {
      const name = (row.getAttribute('data-cat-name') || '').toLowerCase();
      const match = !q || name.includes(q);
      if (match) matchedRows.push(row);
      else row.style.display = 'none';
    });

    const totalItems = Math.max(matchedCards.length, matchedRows.length);
    const totalPages = Math.ceil(totalItems / categoryPageSize) || 1;
    if (categoryCurrentPage > totalPages) categoryCurrentPage = 1;

    const startIndex = (categoryCurrentPage - 1) * categoryPageSize;
    const endIndex = startIndex + categoryPageSize;

    matchedCards.forEach((card, idx) => {
      card.style.display = (idx >= startIndex && idx < endIndex) ? '' : 'none';
    });

    matchedRows.forEach((row, idx) => {
      row.style.display = (idx >= startIndex && idx < endIndex) ? '' : 'none';
    });

    if (categoriesGridEmptyFilterBox) {
      categoriesGridEmptyFilterBox.style.display = (matchedCards.length === 0 && categoryCards.length > 0) ? 'block' : 'none';
    }

    if (categoriesEmptyFilterRow) {
      categoriesEmptyFilterRow.style.display = (matchedRows.length === 0 && categoryRows.length > 0) ? '' : 'none';
    }

    renderPaginationControls({
      container: categoriesPaginationWrap,
      totalItems: totalItems,
      pageSize: categoryPageSize,
      currentPage: categoryCurrentPage,
      onPageChange: (newPage) => {
        categoryCurrentPage = newPage;
        filterCategories();
        document.getElementById('tab-categories')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  categorySearchInput?.addEventListener('input', () => {
    categoryCurrentPage = 1;
    filterCategories();
  });

  function resetCategorySearch() {
    if (categorySearchInput) {
      categorySearchInput.value = '';
      categoryCurrentPage = 1;
      filterCategories();
    }
  }
  btnResetCategoryFilter?.addEventListener('click', resetCategorySearch);
  btnResetCategoryGridFilter?.addEventListener('click', resetCategorySearch);

  // Inisialisasi awal master kategori & pagination
  filterCategories();

  // Tombol "Simpan Perubahan" Kategori -> Bulk Sync ke Database
  document.getElementById('btnSyncCategories')?.addEventListener('click', async function () {
    const btn = this;
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span>Menyimpan...</span>`;

    try {
      const res = await fetch(`${apiUrl}/categories/sync`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ categories: categories }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Seluruh kategori berhasil disinkronisasi & disimpan!');
      } else {
        showToast('Gagal sinkronisasi kategori.', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = origHTML;
    }
  });

  // Modal Tambah Kategori -> Simpan ke Database
  const modalAddCategory = document.getElementById('modalAddCategory');
  const formAddCategory = document.getElementById('formAddCategory');
  document.getElementById('btnOpenAddCategoryModal')?.addEventListener('click', () => {
    modalAddCategory?.classList.add('open');
  });

  formAddCategory?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const name = document.getElementById('addCatName')?.value.trim();
    if (!name) return;

    try {
      const res = await fetch(`${apiUrl}/categories`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ name }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Kategori baru berhasil ditambahkan!');
        modalAddCategory?.classList.remove('open');
        formAddCategory.reset();
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message || 'Gagal menambahkan kategori', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    }
  });

  // Modal Edit Kategori -> Simpan
  const modalEditCategory = document.getElementById('modalEditCategory');
  const formEditCategory = document.getElementById('formEditCategory');
  const editCatOldName = document.getElementById('editCatOldName');
  const editCatNewName = document.getElementById('editCatNewName');

  document.addEventListener('click', function (e) {
    const btnEdit = e.target.closest('.btn-edit-category');
    if (btnEdit) {
      const name = btnEdit.getAttribute('data-name');
      if (editCatOldName) editCatOldName.value = name;
      if (editCatNewName) editCatNewName.value = name;
      modalEditCategory?.classList.add('open');
    }
  });

  formEditCategory?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const oldName = editCatOldName?.value;
    const newName = editCatNewName?.value.trim();
    if (!newName) return;

    try {
      const res = await fetch(`${apiUrl}/categories/update`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ oldName, newName }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast('Perubahan kategori berhasil disimpan!');
        modalEditCategory?.classList.remove('open');
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message || 'Gagal memperbarui kategori', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server.', 'error');
    }
  });

  // Hapus Kategori
  document.addEventListener('click', async function (e) {
    const btnDelete = e.target.closest('.btn-delete-category');
    if (btnDelete) {
      const name = btnDelete.getAttribute('data-name');
      if (confirm(`Apakah Anda yakin ingin menghapus kategori "${name}"?`)) {
        try {
          const res = await fetch(`${apiUrl}/categories/delete`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ name }),
          });

          const data = await res.json();
          if (res.ok && data.success) {
            showToast('Kategori berhasil dihapus!');
            document.querySelector(`.category-item-card[data-cat-name="${name}"]`)?.remove();
          } else {
            showToast(data.message || 'Gagal menghapus kategori', 'error');
          }
        } catch (err) {
          showToast('Gagal terhubung ke server.', 'error');
        }
      }
    }
  });

  // --- 9b. Mesin Kasir Toko Modern (Point of Sale / POS Walk-in) ---
  const modalOfflineOrder = document.getElementById('modalOfflineOrder');
  const btnOpenOfflineOrderModal = document.getElementById('btnOpenOfflineOrderModal');
  const posSearchInput = document.getElementById('posSearchInput');
  const btnClearPosSearch = document.getElementById('btnClearPosSearch');
  const posCategoryScroll = document.getElementById('posCategoryScroll');
  const posProductsGrid = document.getElementById('posProductsGrid');
  const posCartItemsWrapper = document.getElementById('posCartItemsWrapper');
  const posCartItemCountBadge = document.getElementById('posCartItemCountBadge');
  const posGrandTotalDisplay = document.getElementById('posGrandTotalDisplay');
  const posPaymentMethodTabs = document.getElementById('posPaymentMethodTabs');
  const posCashCalculationBox = document.getElementById('posCashCalculationBox');
  const posCashReceived = document.getElementById('posCashReceived');
  const posCashChangeDisplay = document.getElementById('posCashChangeDisplay');
  const btnResetPosCart = document.getElementById('btnResetPosCart');
  const btnSubmitPosOrder = document.getElementById('btnSubmitPosOrder');

  let posCart = [];
  let posActiveCategory = 'all';
  let posSearchTerm = '';
  let posActivePayMethod = 'Tunai (Cash)';

  function renderPosProducts() {
    if (!posProductsGrid) return;

    const activeCat = (posActiveCategory || 'all').trim().toLowerCase();
    const activeCatDecoded = activeCat.replace(/&amp;/g, '&');
    const q = (posSearchTerm || '').trim().toLowerCase();

    // 1. Filter Produk Satuan
    let filteredProds = [];
    if (activeCat !== 'paket-hemat') {
      filteredProds = products.filter((p) => {
        const pCat = (p.category || '').trim().toLowerCase();
        const pCatDecoded = pCat.replace(/&amp;/g, '&');

        const matchCat = activeCat === 'all' || 
                         activeCat === 'semua' || 
                         pCat === activeCat || 
                         pCatDecoded === activeCatDecoded;

        const pName = (p.name || '').toLowerCase();
        const pId = (p.id || '').toLowerCase();
        const matchSearch = !q || pName.includes(q) || pId.includes(q) || pCat.includes(q);

        return matchCat && matchSearch;
      });
    }

    // 2. Filter Paket Hemat (Bundles)
    let filteredBundles = [];
    if (activeCat === 'all' || activeCat === 'semua' || activeCat === 'paket-hemat') {
      filteredBundles = (bundles || []).filter((b) => {
        const isActive = b.is_active !== false && b.is_active !== 0 && b.is_active !== '0';
        if (!isActive) return false;
        const bName = (b.name || '').toLowerCase();
        const bId = String(b.id || '').toLowerCase();
        return !q || bName.includes(q) || bId.includes(q) || 'paket hemat'.includes(q);
      });
    }

    if (filteredProds.length === 0 && filteredBundles.length === 0) {
      posProductsGrid.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem; color: #94A3B8;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.5rem; color: #CBD5E1;">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p style="font-size: 0.8125rem; font-weight: 700; margin: 0; color: #64748B;">Tidak ada produk atau paket yang cocok</p>
          <p style="font-size: 11px; margin: 4px 0 0.85rem;">Coba kata kunci pencarian atau kategori lain</p>
          <button type="button" id="btnPosResetFilter" class="btn-primary-blue" style="margin: 0 auto; padding: 0.4rem 0.9rem; font-size: 11px; border-radius: 9999px; cursor: pointer;">Tampilkan Semua Produk</button>
        </div>
      `;
      document.getElementById('btnPosResetFilter')?.addEventListener('click', () => {
        resetPosFilters();
      });
      return;
    }

    let html = '';

    // Render Produk Satuan Cards
    filteredProds.forEach((p) => {
      const rawStock = parseInt(p.stock, 10);
      const stock = isNaN(rawStock) ? 0 : Math.max(0, rawStock);
      const inCart = posCart.find((it) => it.id === p.id);
      const isOut = stock <= 0;
      const price = parseInt(p.price, 10) || 0;

      let stockBadgeHtml = '';
      if (stock > 5) {
        stockBadgeHtml = `<span class="pos-stock-badge in-stock">Stok: ${stock}</span>`;
      } else if (stock > 0) {
        stockBadgeHtml = `<span class="pos-stock-badge low-stock">Stok: ${stock}</span>`;
      } else {
        stockBadgeHtml = `<span class="pos-stock-badge out-stock">Stok: 0</span>`;
      }

      const inCartBadge = inCart ? `<span class="pos-card-in-cart-pill">${inCart.qty}</span>` : '';

      html += `
        <div class="pos-product-card ${isOut ? 'out-of-stock' : ''}" data-prod-id="${p.id}">
          ${inCartBadge}
          <div class="pos-product-meta-row">
            <span class="pos-product-sku">SKU: ${p.id}</span>
            <span class="pos-product-weight">${p.weight || '500g'}</span>
          </div>
          <h4 class="pos-product-title" title="${p.name}">${p.name}</h4>
          <div class="pos-product-price-row">
            <span class="pos-product-price">Rp ${price.toLocaleString('id-ID')}</span>
            ${stockBadgeHtml}
          </div>
        </div>
      `;
    });

    // Render Paket Hemat Bundles Cards
    filteredBundles.forEach((b) => {
      const bInCart = posCart.find((it) => it.id === 'bundle_' + b.id);
      const bPrice = parseInt(b.bundle_price || b.price || 0, 10);

      // Hitung stok tersedia paket berdasarkan sisa bahan di freezer
      let bStock = 99;
      if (b.items && b.items.length > 0) {
        b.items.forEach(bi => {
          const prod = products.find(p => p.id === bi.product_id);
          const pStock = prod ? Math.floor((prod.stock || 0) / (bi.quantity || 1)) : 0;
          if (pStock < bStock) bStock = pStock;
        });
      }
      const isOut = bStock <= 0;
      const inCartBadge = bInCart ? `<span class="pos-card-in-cart-pill">${bInCart.qty}</span>` : '';

      html += `
        <div class="pos-product-card ${isOut ? 'out-of-stock' : ''}" data-bundle-id="${b.id}" data-is-bundle="true" style="border-color: #FCD34D;">
          ${inCartBadge}
          <div class="pos-product-meta-row">
            <span class="pos-bundle-indicator">★ Paket Hemat</span>
            <span class="pos-product-weight">${b.items ? b.items.length + ' Produk' : 'Paket'}</span>
          </div>
          <h4 class="pos-product-title" title="${b.name}">${b.name}</h4>
          <div class="pos-product-price-row">
            <span class="pos-product-price">Rp ${bPrice.toLocaleString('id-ID')}</span>
            <span class="pos-stock-badge ${bStock > 5 ? 'in-stock' : (bStock > 0 ? 'low-stock' : 'out-stock')}">Stok: ${bStock}</span>
          </div>
        </div>
      `;
    });

    posProductsGrid.innerHTML = html;
  }

  function renderPosCart() {
    if (!posCartItemsWrapper) return;

    if (posCart.length === 0) {
      posCartItemsWrapper.innerHTML = `
        <div class="pos-cart-empty-state">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
          </svg>
          <p style="font-size: 0.8125rem; font-weight: 700; margin: 0; color: #64748B;">Keranjang Kasir Kosong</p>
          <p style="font-size: 11px; margin: 4px 0 0;">Klik produk freezer di sebelah kiri untuk menambahkan ke pesanan.</p>
        </div>
      `;
      if (posCartItemCountBadge) posCartItemCountBadge.textContent = '0 Item';
      if (posGrandTotalDisplay) posGrandTotalDisplay.textContent = 'Rp 0';
      if (posCashChangeDisplay) posCashChangeDisplay.textContent = 'Rp 0';
      return;
    }

    let grandTotal = 0;
    let totalItems = 0;

    posCartItemsWrapper.innerHTML = posCart.map((it) => {
      const subtotal = it.price * it.qty;
      grandTotal += subtotal;
      totalItems += it.qty;
      const isMax = it.qty >= it.stock;

      return `
        <div class="pos-cart-item-row" data-prod-id="${it.id}">
          <div class="pos-cart-item-info">
            <h5 class="pos-cart-item-name" title="${it.name}">${it.name}</h5>
            <span class="pos-cart-item-price">Rp ${it.price.toLocaleString('id-ID')} &bull; Stok: ${it.stock}</span>
          </div>
          <div class="pos-qty-pill-wrap">
            <button type="button" class="pos-qty-pill-btn btn-pos-qty-minus" data-id="${it.id}" title="Kurangi" style="display:inline-flex;align-items:center;justify-content:center;padding:0;">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
            <span class="pos-qty-pill-val" style="line-height:1;">${it.qty}</span>
            <button type="button" class="pos-qty-pill-btn btn-pos-qty-plus" data-id="${it.id}" ${isMax ? 'disabled' : ''} title="Tambah" style="display:inline-flex;align-items:center;justify-content:center;padding:0;">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
          </div>
          <div class="pos-item-subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
          <button type="button" class="pos-btn-remove-item btn-pos-remove-item" data-id="${it.id}" title="Hapus dari kasir">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </div>
      `;
    }).join('');

    if (posCartItemCountBadge) posCartItemCountBadge.textContent = `${totalItems} Item`;
    if (posGrandTotalDisplay) posGrandTotalDisplay.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;

    calculatePosChange(grandTotal);
  }

  function calculatePosChange(total) {
    if (!posCashChangeDisplay) return;
    if (posActivePayMethod !== 'Tunai (Cash)') {
      posCashChangeDisplay.textContent = 'Rp 0';
      return;
    }

    const rawVal = (posCashReceived?.value || '').replace(/[^0-9]/g, '');
    const received = parseInt(rawVal || 0, 10);
    if (!total || total === 0) {
      posCashChangeDisplay.textContent = 'Rp 0';
      posCashChangeDisplay.style.color = '#059669';
      return;
    }

    if (received <= 0) {
      posCashChangeDisplay.textContent = 'Rp 0';
      posCashChangeDisplay.style.color = '#64748B';
    } else if (received >= total) {
      const change = received - total;
      posCashChangeDisplay.textContent = `Rp ${change.toLocaleString('id-ID')}`;
      posCashChangeDisplay.style.color = '#059669';
    } else {
      const lack = total - received;
      posCashChangeDisplay.textContent = `Kurang Rp ${lack.toLocaleString('id-ID')}`;
      posCashChangeDisplay.style.color = '#DC2626';
    }
  }

  function addToPosCart(id) {
    if (typeof id === 'string' && id.startsWith('bundle_')) {
      const bId = id.replace('bundle_', '');
      const bundle = (bundles || []).find((b) => String(b.id) === String(bId));
      if (!bundle) return;

      let bStock = 99;
      if (bundle.items && bundle.items.length > 0) {
        bundle.items.forEach((bi) => {
          const prod = products.find((p) => p.id === bi.product_id);
          const pStock = prod ? Math.floor((prod.stock || 0) / (bi.quantity || 1)) : 0;
          if (pStock < bStock) bStock = pStock;
        });
      }

      if (bStock <= 0) {
        showToast(`Stok bahan untuk paket "${bundle.name}" habis di freezer!`, 'error');
        return;
      }

      const existing = posCart.find((it) => it.id === id);
      if (existing) {
        if (existing.qty + 1 > bStock) {
          showToast(`Maksimal ${bStock} paket sesuai sisa stok freezer!`, 'error');
          return;
        }
        existing.qty += 1;
      } else {
        posCart.push({
          id: id,
          name: bundle.name + ' (Paket Hemat)',
          price: parseInt(bundle.bundle_price || bundle.price || 0, 10),
          stock: bStock,
          qty: 1,
          is_bundle: true,
        });
      }

      renderPosCart();
      renderPosProducts();
      return;
    }

    const prod = products.find((p) => p.id === id);
    if (!prod) return;

    const currentStock = parseInt(prod.stock, 10) || 0;
    if (currentStock <= 0) {
      showToast(`Stok ${prod.name} habis di freezer!`, 'error');
      return;
    }

    const existing = posCart.find((it) => it.id === id);
    if (existing) {
      if (existing.qty + 1 > currentStock) {
        showToast(`Maksimal ${currentStock} unit sesuai stok freezer!`, 'error');
        return;
      }
      existing.qty += 1;
    } else {
      posCart.push({
        id: prod.id,
        name: prod.name,
        price: parseInt(prod.price, 10) || 0,
        stock: currentStock,
        qty: 1,
      });
    }

    renderPosCart();
    renderPosProducts();
  }

  function updatePosItemQty(prodId, newQty) {
    const item = posCart.find((it) => it.id === prodId);
    if (!item) return;

    if (newQty <= 0) {
      posCart = posCart.filter((it) => it.id !== prodId);
    } else if (newQty > item.stock) {
      item.qty = item.stock;
      showToast(`Jumlah disesuaikan dengan sisa stok (${item.stock} unit)`, 'error');
    } else {
      item.qty = newQty;
    }

    renderPosCart();
    renderPosProducts();
  }

  // Event Listeners for POS Grid
  posProductsGrid?.addEventListener('click', (e) => {
    const card = e.target.closest('.pos-product-card');
    if (card && !card.classList.contains('out-of-stock')) {
      const bundleId = card.getAttribute('data-bundle-id');
      const prodId = card.getAttribute('data-prod-id');
      if (bundleId) {
        addToPosCart('bundle_' + bundleId);
      } else if (prodId) {
        addToPosCart(prodId);
      }
    }
  });

  // Event Listeners for POS Cart Controls
  posCartItemsWrapper?.addEventListener('click', (e) => {
    const btnMinus = e.target.closest('.btn-pos-qty-minus');
    if (btnMinus) {
      const prodId = btnMinus.getAttribute('data-id');
      const item = posCart.find((it) => it.id === prodId);
      if (item) updatePosItemQty(prodId, item.qty - 1);
      return;
    }

    const btnPlus = e.target.closest('.btn-pos-qty-plus');
    if (btnPlus) {
      const prodId = btnPlus.getAttribute('data-id');
      const item = posCart.find((it) => it.id === prodId);
      if (item) updatePosItemQty(prodId, item.qty + 1);
      return;
    }

    const btnRemove = e.target.closest('.btn-pos-remove-item');
    if (btnRemove) {
      const prodId = btnRemove.getAttribute('data-id');
      if (prodId) updatePosItemQty(prodId, 0);
      return;
    }
  });

  posCartItemsWrapper?.addEventListener('change', (e) => {
    const inputQty = e.target.closest('.pos-input-qty-val');
    if (inputQty) {
      const prodId = inputQty.getAttribute('data-id');
      const val = parseInt(inputQty.value || 1, 10);
      if (prodId) updatePosItemQty(prodId, isNaN(val) || val < 1 ? 1 : val);
    }
  });

  // Category filter chips
  posCategoryScroll?.addEventListener('click', (e) => {
    const chip = e.target.closest('.pos-cat-chip');
    if (chip) {
      posCategoryScroll.querySelectorAll('.pos-cat-chip').forEach((c) => c.classList.remove('active'));
      chip.classList.add('active');
      posActiveCategory = chip.getAttribute('data-cat') || 'all';
      renderPosProducts();
    }
  });

  // Toggle expand all categories (icon-only serupa dengan homepage)
  const btnPosToggleAllCategories = document.getElementById('btnPosToggleAllCategories');

  btnPosToggleAllCategories?.addEventListener('click', () => {
    if (!posCategoryScroll) return;
    const isExpanded = posCategoryScroll.classList.toggle('expanded');
    btnPosToggleAllCategories.classList.toggle('expanded', isExpanded);
    btnPosToggleAllCategories.title = isExpanded ? 'Sembunyikan kategori' : 'Tampilkan semua kategori';
  });

  // Search input live filter
  posSearchInput?.addEventListener('input', () => {
    posSearchTerm = (posSearchInput.value || '').trim().toLowerCase();
    if (btnClearPosSearch) {
      btnClearPosSearch.style.display = posSearchTerm ? 'block' : 'none';
    }
    renderPosProducts();
  });

  btnClearPosSearch?.addEventListener('click', () => {
    if (posSearchInput) posSearchInput.value = '';
    posSearchTerm = '';
    btnClearPosSearch.style.display = 'none';
    renderPosProducts();
  });

  // Payment Method Tabs
  posPaymentMethodTabs?.addEventListener('click', (e) => {
    const tab = e.target.closest('.pos-pay-tab');
    if (tab) {
      posPaymentMethodTabs.querySelectorAll('.pos-pay-tab').forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');
      posActivePayMethod = tab.getAttribute('data-method') || 'Tunai (Cash)';

      if (posCashCalculationBox) {
        posCashCalculationBox.style.display = posActivePayMethod === 'Tunai (Cash)' ? 'block' : 'none';
      }

      const total = posCart.reduce((sum, it) => sum + (it.price * it.qty), 0);
      calculatePosChange(total);
    }
  });

  // Cash Received Input (Otomatis Titik Pemisah Ribuan) & Quick Cash Buttons
  posCashReceived?.addEventListener('input', () => {
    const rawVal = (posCashReceived.value || '').replace(/[^0-9]/g, '');
    const num = parseInt(rawVal, 10);
    posCashReceived.value = isNaN(num) || num === 0 ? '' : num.toLocaleString('id-ID');
    const total = posCart.reduce((sum, it) => sum + (it.price * it.qty), 0);
    calculatePosChange(total);
  });

  document.querySelectorAll('.pos-btn-quick-cash').forEach((btn) => {
    btn.addEventListener('click', function () {
      const total = posCart.reduce((sum, it) => sum + (it.price * it.qty), 0);
      const val = this.getAttribute('data-cash');
      if (val === 'exact') {
        if (posCashReceived) posCashReceived.value = total > 0 ? total.toLocaleString('id-ID') : '';
      } else {
        const num = parseInt(val, 10);
        if (posCashReceived) posCashReceived.value = !isNaN(num) && num > 0 ? num.toLocaleString('id-ID') : '';
      }
      calculatePosChange(total);
    });
  });

  // Reset Cart
  btnResetPosCart?.addEventListener('click', () => {
    if (posCart.length === 0) return;
    if (confirm('Kosongkan semua barang dari keranjang kasir?')) {
      posCart = [];
      if (posCashReceived) posCashReceived.value = '';
      renderPosCart();
      renderPosProducts();
    }
  });

  function resetPosFilters() {
    posActiveCategory = 'all';
    posSearchTerm = '';
    if (posSearchInput) posSearchInput.value = '';
    if (btnClearPosSearch) btnClearPosSearch.style.display = 'none';
    if (posCategoryScroll) {
      posCategoryScroll.querySelectorAll('.pos-cat-chip').forEach((c) => {
        c.classList.toggle('active', c.getAttribute('data-cat') === 'all');
      });
      posCategoryScroll.scrollLeft = 0;
    }
    renderPosProducts();
  }

  // Open POS Modal Button
  btnOpenOfflineOrderModal?.addEventListener('click', () => {
    resetPosFilters();
    renderPosCart();
    modalOfflineOrder?.classList.add('open');
    setTimeout(() => posSearchInput?.focus(), 150);
  });

  // Finish Order & Print Receipt
  btnSubmitPosOrder?.addEventListener('click', async function () {
    if (posCart.length === 0) {
      showToast('Keranjang kasir masih kosong! Pilih produk terlebih dahulu.', 'error');
      return;
    }

    const total = posCart.reduce((sum, it) => sum + (it.price * it.qty), 0);
    const rawCash = (posCashReceived?.value || '').replace(/[^0-9]/g, '');
    const cashVal = parseInt(rawCash || 0, 10);

    if (posActivePayMethod === 'Tunai (Cash)' && cashVal > 0 && cashVal < total) {
      showToast(`Uang tunai yang diterima kurang Rp ${(total - cashVal).toLocaleString('id-ID')}!`, 'error');
      return;
    }

    const custName = document.getElementById('posCustName')?.value.trim() || 'Pelanggan Toko (Walk-in)';
    const custPhone = document.getElementById('posCustPhone')?.value.trim() || '-';

    const payload = {
      customerName: custName,
      customerPhone: custPhone,
      method: 'Beli Langsung di Toko',
      paymentMethod: posActivePayMethod,
      status: 'Selesai',
      isPaid: true,
      channel: 'Kasir POS',
      subtotal: total,
      total: total,
      items: posCart.map((it) => ({
        id: it.id,
        name: it.name,
        price: it.price,
        qty: it.qty,
        total: it.price * it.qty,
      })),
    };

    btnSubmitPosOrder.disabled = true;
    btnSubmitPosOrder.innerHTML = `
      <svg class="admin-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
      <span>Menyimpan Transaksi...</span>
    `;

    try {
      const res = await fetch(`${apiUrl}/orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast(`Transaksi kasir berhasil dicatat! Total: Rp ${total.toLocaleString('id-ID')}`);
        
        // Update local stock in products array
        posCart.forEach((it) => {
          const p = products.find((x) => x.id === it.id);
          if (p) p.stock = Math.max(0, (parseInt(p.stock, 10) || 0) - it.qty);
        });

        // Add order to local orders array
        if (data.order) {
          orders.unshift(data.order);
        }

        // Close POS Modal & Reset Cart
        modalOfflineOrder?.classList.remove('open');
        posCart = [];
        if (posCashReceived) posCashReceived.value = '';

        // Automatically open thermal receipt ready to print
        const openReceiptFn = window.openReceiptModal || window.openOrderReceipt;
        if (data.order?.id && typeof openReceiptFn === 'function') {
          setTimeout(() => {
            openReceiptFn(data.order.id);
          }, 250);
        } else {
          setTimeout(() => window.location.reload(), 800);
        }
      } else {
        showToast(data.message || 'Gagal menyimpan transaksi kasir', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke server kasir.', 'error');
    } finally {
      btnSubmitPosOrder.disabled = false;
      btnSubmitPosOrder.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <span>Selesaikan &amp; Cetak Struk</span>
      `;
    }
  });

  // --- 9b. Integrasi Kasir Pintar (Modal Import Excel/CSV Produk) ---
  const btnOpenImportProductModal = document.getElementById('btnOpenImportProductModal');
  const modalImportExcel = document.getElementById('modalImportExcel');
  const formImportExcel = document.getElementById('formImportExcel');
  const importDropzone = document.getElementById('importDropzone');
  const importFileInput = document.getElementById('importFileInput');
  const importFilePreview = document.getElementById('importFilePreview');
  const importFileName = document.getElementById('importFileName');
  const importFileSize = document.getElementById('importFileSize');
  const btnRemoveImportFile = document.getElementById('btnRemoveImportFile');
  const btnSubmitImport = document.getElementById('btnSubmitImport');

  if (btnOpenImportProductModal && modalImportExcel) {
    btnOpenImportProductModal.addEventListener('click', () => {
      modalImportExcel.classList.add('open');
      resetImportForm();
    });
  }

  function resetImportForm() {
    if (formImportExcel) formImportExcel.reset();
    if (importFileInput) importFileInput.value = '';
    if (importFilePreview) importFilePreview.style.display = 'none';
    if (importDropzone) importDropzone.style.display = 'block';
    const importErrorAlert = document.getElementById('importErrorAlert');
    if (importErrorAlert) importErrorAlert.style.display = 'none';
    const importErrorList = document.getElementById('importErrorList');
    if (importErrorList) importErrorList.innerHTML = '';
    if (btnSubmitImport) {
      btnSubmitImport.disabled = true;
      btnSubmitImport.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <span>Mulai Impor Produk</span>
      `;
    }
  }

  function handleFileSelection(file) {
    if (!file) return;
    const validExts = ['xlsx', 'xls', 'csv'];
    const ext = file.name.split('.').pop().toLowerCase();
    if (!validExts.includes(ext)) {
      showToast('Format file tidak didukung. Harap pilih file .xlsx atau .csv.', 'error');
      return;
    }

    if (file.size > 10 * 1024 * 1024) {
      showToast('Ukuran file maksimal 10MB.', 'error');
      return;
    }

    if (importFileName) importFileName.textContent = file.name;
    if (importFileSize) {
      const kb = Math.round(file.size / 1024);
      importFileSize.textContent = kb > 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${kb} KB`;
    }
    if (importDropzone) importDropzone.style.display = 'none';
    if (importFilePreview) importFilePreview.style.display = 'flex';
    if (btnSubmitImport) btnSubmitImport.disabled = false;
  }

  if (importDropzone && importFileInput) {
    importDropzone.addEventListener('click', () => importFileInput.click());

    importDropzone.addEventListener('dragover', (e) => {
      e.preventDefault();
      importDropzone.classList.add('dragover');
    });

    ['dragleave', 'dragend'].forEach((ev) => {
      importDropzone.addEventListener(ev, () => {
        importDropzone.classList.remove('dragover');
      });
    });

    importDropzone.addEventListener('drop', (e) => {
      e.preventDefault();
      importDropzone.classList.remove('dragover');
      if (e.dataTransfer?.files?.length) {
        importFileInput.files = e.dataTransfer.files;
        handleFileSelection(e.dataTransfer.files[0]);
      }
    });

    importFileInput.addEventListener('change', () => {
      if (importFileInput.files?.length) {
        handleFileSelection(importFileInput.files[0]);
      }
    });
  }

  if (btnRemoveImportFile) {
    btnRemoveImportFile.addEventListener('click', (e) => {
      e.stopPropagation();
      resetImportForm();
    });
  }

  if (formImportExcel) {
    formImportExcel.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!importFileInput?.files?.length) {
        showToast('Pilih file Excel atau CSV terlebih dahulu.', 'error');
        return;
      }

      btnSubmitImport.disabled = true;
      btnSubmitImport.innerHTML = `
        <svg class="admin-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        <span>Mengimpor Data...</span>
      `;

      try {
        const formData = new FormData(formImportExcel);
        const res = await fetch(`${apiUrl}/products/import`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
          body: formData,
        });

        const data = await res.json();
        if (res.ok && data.success) {
          modalImportExcel.classList.remove('open');

          const modalImportResult = document.getElementById('modalImportResult');
          const createdCountEl = document.getElementById('importSummaryCreatedCount');
          const updatedCountEl = document.getElementById('importSummaryUpdatedCount');
          const createdSection = document.getElementById('importCreatedSection');
          const createdList = document.getElementById('importCreatedList');
          const updatedSection = document.getElementById('importUpdatedSection');
          const updatedList = document.getElementById('importUpdatedList');

          if (modalImportResult && (data.created_items || data.updated_items)) {
            if (createdCountEl) createdCountEl.textContent = data.created_count || 0;
            if (updatedCountEl) updatedCountEl.textContent = data.updated_count || 0;

            if (data.created_items && data.created_items.length > 0) {
              createdSection.style.display = 'block';
              createdList.innerHTML = data.created_items.map((it) => `
                <div class="import-result-item-card">
                  <div>
                    <p class="import-item-title">${it.name}</p>
                    <div class="import-item-meta">
                      <span>SKU: <b>${it.sku}</b></span>
                      <span>Kategori: ${it.category}</span>
                      <span>Stok Awal: <b>${it.stock} unit</b></span>
                    </div>
                  </div>
                  <span class="import-pill-badge import-pill-new">+ Baru (Rp ${(it.price || 0).toLocaleString('id-ID')})</span>
                </div>
              `).join('');
            } else {
              createdSection.style.display = 'none';
            }

            if (data.updated_items && data.updated_items.length > 0) {
              updatedSection.style.display = 'block';
              updatedList.innerHTML = data.updated_items.map((it) => `
                <div class="import-result-item-card">
                  <div>
                    <p class="import-item-title">${it.name}</p>
                    <div class="import-item-meta">
                      <span>SKU: <b>${it.sku}</b></span>
                      <span>Stok: ${it.old_stock} &rarr; <b>${it.stock} unit</b></span>
                    </div>
                  </div>
                  <span class="import-pill-badge import-pill-update">Update (Rp ${(it.price || 0).toLocaleString('id-ID')})</span>
                </div>
              `).join('');
            } else {
              updatedSection.style.display = 'none';
            }

            modalImportResult.classList.add('open');
          } else {
            showToast(data.message || 'Impor data produk berhasil!');
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          }
        } else {
          const importErrorAlert = document.getElementById('importErrorAlert');
          const importErrorList = document.getElementById('importErrorList');
          const importErrorTitle = document.getElementById('importErrorTitle');

          if (data.errors && Array.isArray(data.errors) && data.errors.length > 0 && importErrorAlert && importErrorList) {
            importErrorTitle.textContent = data.message || 'Terdapat kesalahan validasi data pada file:';
            importErrorList.innerHTML = data.errors.map((err) => `<li>${err}</li>`).join('');
            importErrorAlert.style.display = 'block';
            modalImportExcel?.querySelector('.admin-modal-box')?.scrollTo({ top: 0, behavior: 'smooth' });
            showToast(data.errors[0], 'error');
          } else {
            showToast(data.message || 'Gagal mengimpor data produk.', 'error');
          }
          btnSubmitImport.disabled = false;
          btnSubmitImport.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span>Mulai Impor Produk</span>
          `;
        }
      } catch (err) {
        showToast('Terjadi kesalahan saat mengunggah file spreadsheet.', 'error');
        btnSubmitImport.disabled = false;
        btnSubmitImport.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span>Mulai Impor Produk</span>
        `;
      }
    });
  }

  // Tutup Modal Laporan Hasil Impor & Reload Dashboard
  document.querySelectorAll('.btn-close-import-result').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.getElementById('modalImportResult')?.classList.remove('open');
      window.location.reload();
    });
  });

  // ==========================================
  // 11. MODUL LAPORAN PENJUALAN & ANALISIS TOKO (CHART.JS)
  // ==========================================
  let salesChartInstance = null;
  let dashboardSalesChartInstance = null;
  let activeReportRange = '7'; // '7', '30', 'month', 'all'
  let lastReportPeriodOrders = [];

  // Diagram Ringkas Omzet 7 Hari Terakhir di Tab Dashboard Overview
  function updateDashboardSalesChart() {
    const chartCanvas = document.getElementById('dashboardSalesChart');
    if (!chartCanvas) return;

    const now = new Date();
    const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6, 0, 0, 0);

    const dailyMap = {};
    const chartLabels = [];
    const chartData = [];

    for (let i = 6; i >= 0; i--) {
      const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - i);
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const key = `${y}-${m}-${day}`;
      dailyMap[key] = 0;
      chartLabels.push(d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }));
    }

    const validOrders = (orders || []).filter((o) => {
      if (o.status === 'Dibatalkan') return false;
      if (o.status !== 'Selesai' && !o.is_paid && !o.isPaid) return false;
      const oDate = new Date(o.order_date || o.created_at || now);
      return oDate >= startDate;
    });

    validOrders.forEach((o) => {
      const d = new Date(o.order_date || o.created_at);
      if (isNaN(d.getTime())) return;
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const key = `${y}-${m}-${day}`;
      const amount = parseInt(o.total, 10) || 0;
      if (dailyMap.hasOwnProperty(key)) {
        dailyMap[key] += amount;
      }
    });

    Object.keys(dailyMap).forEach((k) => {
      chartData.push(dailyMap[k]);
    });

    if (typeof Chart !== 'undefined') {
      const ctx = chartCanvas.getContext('2d');
      if (dashboardSalesChartInstance) {
        dashboardSalesChartInstance.destroy();
      }

      const gradient = ctx.createLinearGradient(0, 0, 0, 260);
      gradient.addColorStop(0, 'rgba(103, 125, 158, 0.35)');
      gradient.addColorStop(1, 'rgba(103, 125, 158, 0.01)');

      dashboardSalesChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: chartLabels,
          datasets: [{
            label: 'Omzet Harian',
            data: chartData,
            borderColor: '#677D9E',
            backgroundColor: gradient,
            borderWidth: 2.5,
            fill: true,
            tension: 0.32,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#FFFFFF',
            pointBorderColor: '#677D9E',
            pointBorderWidth: 2,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#1E293B',
              padding: 10,
              cornerRadius: 8,
              titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: '700' },
              bodyFont: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
              displayColors: false,
              callbacks: {
                label: function (tooltipItem) {
                  return 'Omzet: Rp ' + (tooltipItem.parsed.y || 0).toLocaleString('id-ID');
                }
              }
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: {
                font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                color: '#64748B',
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 12,
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: '#F1F5F9',
                borderDash: [4, 4],
              },
              ticks: {
                font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                color: '#64748B',
                callback: function (val) {
                  if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(val % 1000000 === 0 ? 0 : 1) + 'jt';
                  if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'rb';
                  return 'Rp ' + val;
                }
              }
            }
          }
        }
      });
    }
  }

  function updateSalesReport() {
    const rangeText = document.getElementById('reportActiveRangeText');
    const revenueEl = document.getElementById('reportTotalRevenue');
    const ordersEl = document.getElementById('reportTotalOrders');
    const aovEl = document.getElementById('reportAverageOrderValue');
    const chartBadge = document.getElementById('reportChartStatusBadge');
    const payListEl = document.getElementById('reportPaymentMethodsList');
    const catListEl = document.getElementById('reportCategoriesList');
    const tableBodyEl = document.getElementById('reportTransactionsTableBody');
    const tableCountBadge = document.getElementById('reportTableCountBadge');
    const payTotalCount = document.getElementById('reportPaymentTotalCount');
    const catTotalItems = document.getElementById('reportCategoryTotalItems');

    const chartCanvas = document.getElementById('salesTrendsChart');
    if (!chartCanvas) return;

    // Filter range calculation
    const now = new Date();
    let startDate = null;
    let rangeLabel = '';

    if (activeReportRange === '7') {
      startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6, 0, 0, 0);
      rangeLabel = '7 Hari Terakhir';
    } else if (activeReportRange === '30') {
      startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 29, 0, 0, 0);
      rangeLabel = '30 Hari Terakhir';
    } else if (activeReportRange === 'month') {
      startDate = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
      rangeLabel = 'Bulan Ini (' + now.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) + ')';
    } else {
      startDate = null; // Semua Waktu
      rangeLabel = 'Semua Riwayat Waktu';
    }

    if (rangeText) {
      if (startDate) {
        rangeText.textContent = `Rentang: ${startDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })} - ${now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}`;
      } else {
        rangeText.textContent = `Rentang: Seluruh Transaksi Toko`;
      }
    }

    // Filter seluruh pesanan pada rentang tanggal untuk tabel riwayat transaksi
    const periodOrders = orders.filter((o) => {
      const oDate = new Date(o.order_date || o.created_at || now);
      if (startDate && oDate < startDate) return false;
      return true;
    });

    // Simpan referensi seluruh pesanan periode untuk kebutuhan Cetak PDF
    lastReportPeriodOrders = periodOrders;

    // Omzet & KPI hanya menghitung transaksi berhasil / lunas (bukan yang dibatalkan)
    const successfulOrders = periodOrders.filter((o) => (o.status === 'Selesai' || o.is_paid || o.isPaid) && o.status !== 'Dibatalkan');

    // 1. KPI Stats
    const totalRev = successfulOrders.reduce((sum, o) => sum + (parseInt(o.total, 10) || 0), 0);
    const totalSuccCount = successfulOrders.length;
    const aov = totalSuccCount > 0 ? Math.round(totalRev / totalSuccCount) : 0;

    if (revenueEl) revenueEl.textContent = 'Rp ' + totalRev.toLocaleString('id-ID');
    if (ordersEl) ordersEl.textContent = `${totalSuccCount} Pesanan`;
    if (aovEl) aovEl.textContent = 'Rp ' + aov.toLocaleString('id-ID');

    // 2. Timeline Daily Revenue for Line Chart
    const dailyMap = {};
    const chartLabels = [];
    const chartData = [];

    if (activeReportRange === '7' || activeReportRange === '30') {
      const daysCount = activeReportRange === '7' ? 7 : 30;
      for (let i = daysCount - 1; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - i);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const key = `${y}-${m}-${day}`;
        dailyMap[key] = 0;
        chartLabels.push(d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }));
      }
    } else if (activeReportRange === 'month') {
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
      for (let dayNum = 1; dayNum <= lastDay; dayNum++) {
        const d = new Date(now.getFullYear(), now.getMonth(), dayNum);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(dayNum).padStart(2, '0');
        const key = `${y}-${m}-${day}`;
        dailyMap[key] = 0;
        chartLabels.push(d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
      }
    } else {
      // Semua waktu: Kelompokkan per Bulan (YYYY-MM) agar visualisasi tren jangka panjang rapi dan informatif
      const sorted = [...successfulOrders].sort((a, b) => new Date(a.order_date || a.created_at) - new Date(b.order_date || b.created_at));
      if (sorted.length > 0) {
        sorted.forEach((o) => {
          const d = new Date(o.order_date || o.created_at);
          const y = d.getFullYear();
          const m = String(d.getMonth() + 1).padStart(2, '0');
          const key = `${y}-${m}`;
          if (!dailyMap.hasOwnProperty(key)) {
            dailyMap[key] = 0;
            chartLabels.push(d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }));
          }
        });
      } else {
        const key = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
        dailyMap[key] = 0;
        chartLabels.push(now.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }));
      }
    }

    // Populate daily / monthly revenues
    successfulOrders.forEach((o) => {
      const d = new Date(o.order_date || o.created_at);
      if (isNaN(d.getTime())) return;
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const key = activeReportRange === 'all' ? `${y}-${m}` : `${y}-${m}-${day}`;
      if (dailyMap.hasOwnProperty(key)) {
        dailyMap[key] += parseInt(o.total, 10) || 0;
      }
    });

    Object.keys(dailyMap).forEach((k) => {
      chartData.push(dailyMap[k]);
    });

    if (chartBadge) {
      chartBadge.textContent = `${rangeLabel} • Rp ${totalRev.toLocaleString('id-ID')}`;
    }

    // Render Chart.js
    if (typeof Chart !== 'undefined') {
      const ctx = chartCanvas.getContext('2d');
      if (salesChartInstance) {
        salesChartInstance.destroy();
      }

      const gradient = ctx.createLinearGradient(0, 0, 0, 280);
      gradient.addColorStop(0, 'rgba(103, 125, 158, 0.35)');
      gradient.addColorStop(1, 'rgba(103, 125, 158, 0.01)');

      salesChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: chartLabels,
          datasets: [{
            label: activeReportRange === 'all' ? 'Omzet Bulanan' : 'Omzet Harian',
            data: chartData,
            borderColor: '#677D9E',
            backgroundColor: gradient,
            borderWidth: 2.5,
            fill: true,
            tension: 0.32,
            pointRadius: chartData.length > 20 ? 2 : 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#FFFFFF',
            pointBorderColor: '#677D9E',
            pointBorderWidth: 2,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#1E293B',
              padding: 10,
              cornerRadius: 8,
              titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: '700' },
              bodyFont: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
              displayColors: false,
              callbacks: {
                label: function (tooltipItem) {
                  return 'Omzet: Rp ' + (tooltipItem.parsed.y || 0).toLocaleString('id-ID');
                }
              }
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: {
                font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                color: '#64748B',
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 12,
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: '#F1F5F9',
                borderDash: [4, 4],
              },
              ticks: {
                font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                color: '#64748B',
                callback: function (val) {
                  if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(val % 1000000 === 0 ? 0 : 1) + 'jt';
                  if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'rb';
                  return 'Rp ' + val;
                }
              }
            }
          }
        }
      });
    }

    // 3. Payment Methods Breakdown
    const payMap = {};
    successfulOrders.forEach((o) => {
      const m = o.payment_method || 'QRIS Instan';
      payMap[m] = (payMap[m] || 0) + (parseInt(o.total, 10) || 0);
    });

    if (payTotalCount) payTotalCount.textContent = `${totalSuccCount} Transaksi`;

    if (payListEl) {
      const payKeys = Object.keys(payMap);
      if (payKeys.length === 0) {
        payListEl.innerHTML = `<p style="font-size: 0.75rem; color: #94A3B8; text-align: center; margin: 1rem 0;">Belum ada data pembayaran di periode ini.</p>`;
      } else {
        const colors = ['#677D9E', '#059669', '#8B5CF6', '#F59E0B', '#3B82F6'];
        let html = '';
        payKeys.sort((a, b) => payMap[b] - payMap[a]).forEach((key, idx) => {
          const val = payMap[key];
          const pct = totalRev > 0 ? Math.round((val / totalRev) * 100) : 0;
          const color = colors[idx % colors.length];
          html += `
            <div class="report-metric-row">
              <div class="report-metric-header">
                <span class="report-metric-name">
                  <span style="width: 8px; height: 8px; border-radius: 50%; background: ${color};"></span>
                  ${key}
                </span>
                <span class="report-metric-val">Rp ${val.toLocaleString('id-ID')} <span class="report-metric-sub">(${pct}%)</span></span>
              </div>
              <div class="report-progress-track">
                <div class="report-progress-fill" style="width: ${pct}%; background: ${color};"></div>
              </div>
            </div>
          `;
        });
        payListEl.innerHTML = html;
      }
    }

    // 4. Category Contribution Breakdown
    const catMap = {};
    let totalItemsSold = 0;

    successfulOrders.forEach((o) => {
      let itemsList = o.items;
      if (typeof itemsList === 'string') {
        try { itemsList = JSON.parse(itemsList); } catch (e) { itemsList = []; }
      }
      if (Array.isArray(itemsList)) {
        itemsList.forEach((it) => {
          const qty = parseInt(it.qty || 1, 10);
          totalItemsSold += qty;
          const prodObj = products.find((p) => p.name === it.name || p.id === it.id);
          const bundleObj = !prodObj ? bundles.find((b) => b.name === it.name || ('bundle_' + b.id) === String(it.id)) : null;
          const catName = prodObj ? prodObj.category : (bundleObj ? 'Paket Hemat' : (it.category || 'Lainnya'));
          catMap[catName] = (catMap[catName] || 0) + qty;
        });
      }
    });

    if (catTotalItems) catTotalItems.textContent = `${totalItemsSold} Pcs Terjual`;

    if (catListEl) {
      const catKeys = Object.keys(catMap);
      if (catKeys.length === 0) {
        catListEl.innerHTML = `<p style="font-size: 0.75rem; color: #94A3B8; text-align: center; margin: 1rem 0;">Belum ada data penjualan kategori di periode ini.</p>`;
      } else {
        const catColors = ['#059669', '#677D9E', '#F59E0B', '#8B5CF6', '#EC4899'];
        let html = '';
        catKeys.sort((a, b) => catMap[b] - catMap[a]).forEach((catName, idx) => {
          const qty = catMap[catName];
          const pct = totalItemsSold > 0 ? Math.round((qty / totalItemsSold) * 100) : 0;
          const color = catColors[idx % catColors.length];
          html += `
            <div class="report-metric-row">
              <div class="report-metric-header">
                <span class="report-metric-name">
                  <span style="width: 8px; height: 8px; border-radius: 50%; background: ${color};"></span>
                  ${catName}
                </span>
                <span class="report-metric-val">${qty} pcs <span class="report-metric-sub">(${pct}%)</span></span>
              </div>
              <div class="report-progress-track">
                <div class="report-progress-fill" style="width: ${pct}%; background: ${color};"></div>
              </div>
            </div>
          `;
        });
        catListEl.innerHTML = html;
      }
    }

    // 5. Transactions Table for Period with Pagination
    if (tableCountBadge) tableCountBadge.textContent = `${periodOrders.length} Transaksi`;

    const reportPaginationWrap = document.getElementById('reportPaginationWrap');
    if (tableBodyEl) {
      if (periodOrders.length === 0) {
        tableBodyEl.innerHTML = `
          <tr>
            <td colspan="6" style="text-align: center; color: #9CA3AF; padding: 2.5rem;">
              Tidak ada riwayat transaksi pada rentang periode ini.
            </td>
          </tr>
        `;
        if (reportPaginationWrap) reportPaginationWrap.style.display = 'none';
      } else {
        const totalItems = periodOrders.length;
        const totalPages = Math.ceil(totalItems / reportPageSize) || 1;
        if (reportCurrentPage > totalPages) reportCurrentPage = 1;

        const startIndex = (reportCurrentPage - 1) * reportPageSize;
        const pageItems = periodOrders.slice(startIndex, startIndex + reportPageSize);

        let rows = '';
        pageItems.forEach((o) => {
          let badgeClass = 'menunggu-pembayaran';
          let badgeLabel = 'Menunggu Pembayaran';
          if (o.status === 'Selesai') {
            badgeClass = 'selesai';
            badgeLabel = 'Selesai';
          } else if (o.status === 'Diproses') {
            badgeClass = 'diproses';
            badgeLabel = 'Diproses';
          } else if (o.status === 'Dibatalkan') {
            badgeClass = 'dibatalkan';
            badgeLabel = 'Dibatalkan';
          } else if (o.status && o.status !== 'Menunggu Bayar' && o.status !== 'Menunggu Konfirmasi Admin') {
            badgeLabel = o.status;
          }
          const statusBadge = `<span class="badge-status ${badgeClass}">${badgeLabel}</span>`;

          rows += `
            <tr>
              <td style="font-family: monospace; font-weight: 700; color: var(--soft-blue);">${o.id}</td>
              <td style="color: #64748B; font-size: 0.725rem;">${o.order_date || (o.created_at ? new Date(o.created_at).toLocaleString('id-ID') : '-')}</td>
              <td style="font-weight: 700; color: var(--navy);">${o.customer_name || 'Pelanggan'}</td>
              <td style="color: #475569; font-weight: 600;">${o.payment_method || 'QRIS Instan'}</td>
              <td>${statusBadge}</td>
              <td style="text-align: right; font-weight: 800; color: var(--navy);">Rp ${(parseInt(o.total, 10) || 0).toLocaleString('id-ID')}</td>
            </tr>
          `;
        });
        tableBodyEl.innerHTML = rows;

        renderPaginationControls({
          container: reportPaginationWrap,
          totalItems: totalItems,
          pageSize: reportPageSize,
          currentPage: reportCurrentPage,
          onPageChange: (newPage) => {
            reportCurrentPage = newPage;
            updateSalesReport();
          }
        });
      }
    }
  }

  let reportCurrentPage = 1;
  const reportPageSize = 10;

  // Setup range buttons listener
  document.querySelectorAll('.report-range-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.report-range-btn').forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
      activeReportRange = this.getAttribute('data-range') || '7';
      reportCurrentPage = 1;
      updateSalesReport();
    });
  });

  // Export function to global scope for switchTab trigger
  window.updateSalesReport = updateSalesReport;
  window.updateDashboardSalesChart = updateDashboardSalesChart;

  // Initial call based on active tab
  const initialTabParam = new URLSearchParams(window.location.search).get('tab');
  if (initialTabParam === 'reports') {
    setTimeout(updateSalesReport, 100);
  } else if (!initialTabParam || initialTabParam === 'dashboard') {
    setTimeout(updateDashboardSalesChart, 100);
  }

  // --- 10. Handler Penutup Semua Jendela Modal Dialog & Scroll Lock Halaman ---
  function syncModalBodyLock() {
    const hasOpenModal = Boolean(document.querySelector('.admin-modal-backdrop.open'));
    if (hasOpenModal) {
      if (!document.body.classList.contains('admin-modal-open')) {
        const scrollbarW = window.innerWidth - document.documentElement.clientWidth;
        document.body.style.paddingRight = scrollbarW > 0 ? `${scrollbarW}px` : '';
        document.body.classList.add('admin-modal-open');
      }
    } else {
      document.body.classList.remove('admin-modal-open');
      document.body.style.paddingRight = '';
    }
  }

  const modalClassObserver = new MutationObserver(syncModalBodyLock);
  document.querySelectorAll('.admin-modal-backdrop').forEach((backdrop) => {
    modalClassObserver.observe(backdrop, { attributes: true, attributeFilter: ['class'] });
  });
  syncModalBodyLock();

  document.querySelectorAll('.btn-modal-close, .btn-modal-cancel').forEach((btn) => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.admin-modal-backdrop').forEach((m) => m.classList.remove('open'));
    });
  });

  document.querySelectorAll('.admin-modal-backdrop').forEach((backdrop) => {
    backdrop.addEventListener('click', function (e) {
      if (e.target === this) {
        this.classList.remove('open');
      }
    });
  });

  // --- 11. Audio Chime Notifikasi Pesanan Baru (Web Audio API) ---
  window.playOrderAlertChime = function () {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      if (ctx.state === 'suspended') {
        ctx.resume();
      }
      const now = ctx.currentTime;

      // Nada 1: E5 (659.25 Hz)
      const osc1 = ctx.createOscillator();
      const gain1 = ctx.createGain();
      osc1.type = 'sine';
      osc1.frequency.setValueAtTime(659.25, now);
      gain1.gain.setValueAtTime(0.2, now);
      gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
      osc1.connect(gain1);
      gain1.connect(ctx.destination);
      osc1.start(now);
      osc1.stop(now + 0.3);

      // Nada 2: B5 (987.77 Hz) - Chime Lonceng Kasir
      const osc2 = ctx.createOscillator();
      const gain2 = ctx.createGain();
      osc2.type = 'sine';
      osc2.frequency.setValueAtTime(987.77, now + 0.12);
      gain2.gain.setValueAtTime(0.25, now + 0.12);
      gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
      osc2.connect(gain2);
      gain2.connect(ctx.destination);
      osc2.start(now + 0.12);
      osc2.stop(now + 0.5);
    } catch (e) {
      console.log('Audio chime error:', e);
    }
  };

  // Background Order Polling (Memeriksa pesanan baru tiap 20 detik)
  let lastKnownOrdersCount = null;
  async function pollNewOrders() {
    try {
      const res = await fetch('/api/orders');
      if (!res.ok) return;
      const data = await res.json();
      const fetchedOrders = Array.isArray(data) ? data : (data.orders || []);
      const count = fetchedOrders.length;

      if (lastKnownOrdersCount !== null && count > lastKnownOrdersCount) {
        window.playOrderAlertChime();
        if (typeof window.showToast === 'function') {
          window.showToast(`Pesanan baru masuk! Total: ${count} pesanan`, 'success');
        }
        const badge = document.getElementById('orderCountBadge');
        if (badge) badge.textContent = `${count} Pesanan`;

        // Update global orders reference & charts
        orders = fetchedOrders;
        if (typeof updateDashboardSalesChart === 'function') updateDashboardSalesChart();
        if (typeof updateSalesReport === 'function') updateSalesReport();
      } else if (lastKnownOrdersCount === null) {
        orders = fetchedOrders;
      }
      const pendingCount = orders.filter(o => o.status === 'Menunggu Bayar' || o.status === 'Menunggu Konfirmasi Admin' || o.status === 'Diproses').length;
      const bottomOrdersBadge = document.getElementById('adminBottomOrdersBadge');
      if (bottomOrdersBadge) {
        if (pendingCount > 0) {
          bottomOrdersBadge.textContent = pendingCount;
          bottomOrdersBadge.style.display = 'flex';
        } else {
          bottomOrdersBadge.style.display = 'none';
        }
      }
      lastKnownOrdersCount = count;
    } catch (err) {}
  }
  setInterval(pollNewOrders, 20000);
  setTimeout(pollNewOrders, 2000);

  // --- 12. Pengontrol Status Operasional Toko (Segmented Control Buka / Tutup) ---
  const btnSetStoreOpen = document.getElementById('btnSetStoreOpen');
  const btnSetStoreClosed = document.getElementById('btnSetStoreClosed');
  const sidebarStatusBadge = document.getElementById('sidebarStatusBadge');
  const sidebarStatusFootnote = document.getElementById('sidebarStatusFootnote');

  async function updateStoreOperationalStatus(targetOpen) {
    if (btnSetStoreOpen) btnSetStoreOpen.disabled = true;
    if (btnSetStoreClosed) btnSetStoreClosed.disabled = true;

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const res = await fetch('/api/store-status/toggle', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ is_open: targetOpen }),
      });
      const data = await res.json();
      if (data.success) {
        const isOpen = Boolean(data.store_status && data.store_status.is_open);

        // Update Segmented Control Buttons
        if (btnSetStoreOpen) {
          btnSetStoreOpen.classList.toggle('active-open', isOpen);
          btnSetStoreOpen.disabled = isOpen;
        }
        if (btnSetStoreClosed) {
          btnSetStoreClosed.classList.toggle('active-closed', !isOpen);
          btnSetStoreClosed.disabled = !isOpen;
        }

        // Update Badge & Footnote di sidebar
        if (sidebarStatusBadge) {
          sidebarStatusBadge.className = `store-status-pill ${isOpen ? 'is-open' : 'is-closed'}`;
          sidebarStatusBadge.textContent = isOpen ? 'Buka' : 'Tutup';
        }
        if (sidebarStatusFootnote) {
          sidebarStatusFootnote.textContent = isOpen ? 'Menerima pesanan online' : 'Toko tutup sementara';
        }

        if (typeof window.showToast === 'function') {
          window.showToast(data.message);
        }
      }
    } catch (e) {
      console.error('Update store status error:', e);
      if (typeof window.showToast === 'function') {
        window.showToast('Gagal mengubah status toko. Coba lagi.', 'error');
      }
    } finally {
      // Re-enable tombol yang tidak aktif
      const curOpen = btnSetStoreOpen?.classList.contains('active-open');
      if (btnSetStoreOpen) btnSetStoreOpen.disabled = curOpen;
      if (btnSetStoreClosed) btnSetStoreClosed.disabled = !curOpen;
    }
  }

  btnSetStoreOpen?.addEventListener('click', () => updateStoreOperationalStatus(true));
  btnSetStoreClosed?.addEventListener('click', () => updateStoreOperationalStatus(false));

  // --- 13. Cetak Laporan Penjualan Resmi (PDF) ---
  const btnPrintSalesReportPdf = document.getElementById('btnPrintSalesReportPdf');
  if (btnPrintSalesReportPdf) {
    btnPrintSalesReportPdf.addEventListener('click', () => {
      document.body.classList.add('is-printing-report');
      document.body.classList.remove('is-printing-receipt');
      const repRev = document.getElementById('reportTotalRevenue')?.textContent || 'Rp 0';
      const repOrders = document.getElementById('reportTotalOrders')?.textContent || '0 Pesanan';
      const repAov = document.getElementById('reportAverageOrderValue')?.textContent || 'Rp 0';
      const repRangeText = document.getElementById('reportActiveRangeText')?.textContent || 'Rentang Aktif';

      const pRev = document.getElementById('printTotalRevenue');
      const pOrders = document.getElementById('printTotalOrders');
      const pAov = document.getElementById('printAverageOrder');
      const pPeriod = document.getElementById('printDocPeriod');
      const pDate = document.getElementById('printDocDate');
      const pTableBody = document.getElementById('printOrdersTableBody');

      if (pRev) pRev.textContent = repRev;
      if (pOrders) pOrders.textContent = repOrders;
      if (pAov) pAov.textContent = repAov;
      if (pPeriod) pPeriod.textContent = repRangeText.replace('Rentang: ', 'Periode: ');
      if (pDate) {
        const now = new Date();
        pDate.textContent = `Dicetak pada: ${now.toLocaleDateString('id-ID')} ${now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} WITA`;
      }

      // Render SELURUH transaksi pada periode terpilih ke dokumen cetak (tidak terpotong paginasi 10 baris web)
      if (pTableBody) {
        const ordersToPrint = Array.isArray(lastReportPeriodOrders) && lastReportPeriodOrders.length > 0
          ? lastReportPeriodOrders
          : (orders || []);

        if (ordersToPrint.length > 0) {
          pTableBody.innerHTML = ordersToPrint.map((o) => {
            const dateStr = o.order_date || (o.created_at ? new Date(o.created_at).toLocaleString('id-ID') : '-');
            const totalVal = (parseInt(o.total, 10) || 0).toLocaleString('id-ID');
            return `
              <tr>
                <td style="font-family: monospace; font-weight: 700;">${o.id || '-'}</td>
                <td>${dateStr}</td>
                <td style="font-weight: 700;">${o.customer_name || 'Pelanggan'}</td>
                <td>${o.payment_method || 'QRIS Instan'}</td>
                <td>${o.status || 'Diproses'}</td>
                <td style="text-align: right; font-weight: 700;">Rp ${totalVal}</td>
              </tr>
            `;
          }).join('');
        } else {
          pTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94A3B8; padding: 1.5rem;">Tidak ada riwayat transaksi pada periode ini</td></tr>';
        }
      }

      // Berikan jeda kecil agar styling media print selesai diterapkan oleh browser
      setTimeout(() => {
        window.print();
      }, 80);
    });
  }

  // --- 14. Modal Validasi Bukti Pembayaran Pelanggan ---
  const modalViewPaymentProof = document.getElementById('modalViewPaymentProof');
  const proofPreviewImg = document.getElementById('proofPreviewImg');
  const proofCustomerName = document.getElementById('proofCustomerName');
  const proofTotalAmount = document.getElementById('proofTotalAmount');
  const proofModalTitle = document.getElementById('proofModalTitle');
  const proofActionButtonsWrap = document.getElementById('proofActionButtonsWrap');
  const proofVerifiedBadgeWrap = document.getElementById('proofVerifiedBadgeWrap');
  const proofRejectedBadgeWrap = document.getElementById('proofRejectedBadgeWrap');
  const proofVerifiedDesc = document.getElementById('proofVerifiedDesc');
  const btnAcceptPaymentProof = document.getElementById('btnAcceptPaymentProof');
  const btnRejectPaymentProof = document.getElementById('btnRejectPaymentProof');
  let activeProofOrderId = null;

  document.addEventListener('click', (e) => {
    const proofBtn = e.target.closest('.btn-view-payment-proof');
    if (proofBtn) {
      activeProofOrderId = proofBtn.getAttribute('data-order-id');
      const proofUrl = proofBtn.getAttribute('data-proof-url');
      const custName = proofBtn.getAttribute('data-cust-name') || '-';
      const total = proofBtn.getAttribute('data-total') || 'Rp 0';
      const isPaid = proofBtn.getAttribute('data-is-paid') === '1';
      const status = proofBtn.getAttribute('data-status') || '';

      if (proofPreviewImg) {
        const fallbackMsg = document.getElementById('proofFallbackMsg');
        const extLink = document.getElementById('proofExternalLink');
        if (extLink) extLink.href = proofUrl;
        if (fallbackMsg) fallbackMsg.style.display = 'none';

        proofPreviewImg.style.display = 'block';
        proofPreviewImg.onerror = () => {
          proofPreviewImg.style.display = 'none';
          if (fallbackMsg) fallbackMsg.style.display = 'flex';
        };
        proofPreviewImg.onload = () => {
          proofPreviewImg.style.display = 'block';
          if (fallbackMsg) fallbackMsg.style.display = 'none';
        };
        proofPreviewImg.src = proofUrl;
      }
      if (proofCustomerName) proofCustomerName.textContent = custName;
      if (proofTotalAmount) proofTotalAmount.textContent = total;

      // State-Aware Dynamic Footer
      if (status === 'Dibatalkan') {
        if (proofModalTitle) proofModalTitle.textContent = `Bukti Transfer (Ditolak) #${activeProofOrderId}`;
        if (proofActionButtonsWrap) proofActionButtonsWrap.style.display = 'none';
        if (proofVerifiedBadgeWrap) proofVerifiedBadgeWrap.style.display = 'none';
        if (proofRejectedBadgeWrap) proofRejectedBadgeWrap.style.display = 'flex';
      } else if (isPaid || status === 'Diproses' || status === 'Selesai') {
        if (proofModalTitle) proofModalTitle.textContent = `Bukti Transfer (Lunas) #${activeProofOrderId}`;
        if (proofActionButtonsWrap) proofActionButtonsWrap.style.display = 'none';
        if (proofRejectedBadgeWrap) proofRejectedBadgeWrap.style.display = 'none';
        if (proofVerifiedBadgeWrap) proofVerifiedBadgeWrap.style.display = 'flex';
        if (proofVerifiedDesc) {
          proofVerifiedDesc.textContent = status === 'Selesai'
            ? 'Pesanan telah selesai dan pembayaran telah diverifikasi.'
            : 'Pesanan telah divalidasi dan sedang diproses toko.';
        }
      } else {
        // Pending status (Menunggu Pembayaran / Menunggu Konfirmasi Admin)
        if (proofModalTitle) proofModalTitle.textContent = `Validasi Bukti #${activeProofOrderId}`;
        if (proofActionButtonsWrap) proofActionButtonsWrap.style.display = 'flex';
        if (proofVerifiedBadgeWrap) proofVerifiedBadgeWrap.style.display = 'none';
        if (proofRejectedBadgeWrap) proofRejectedBadgeWrap.style.display = 'none';
      }

      if (modalViewPaymentProof) modalViewPaymentProof.classList.add('open');
    }
  });

  async function handleVerifyPaymentProof(action) {
    if (!activeProofOrderId) return;
    const btn = action === 'accept' ? btnAcceptPaymentProof : btnRejectPaymentProof;
    if (btn) btn.disabled = true;

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const res = await fetch(`/api/orders/${activeProofOrderId}/verify-payment`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ action }),
      });
      const data = await res.json();
      if (data.success) {
        if (modalViewPaymentProof) modalViewPaymentProof.classList.remove('open');
        if (typeof window.showToast === 'function') {
          window.showToast(data.message);
        }

        // Update baris tabel terkait di DOM
        const targetRow = document.querySelector(`tr[data-order-row="${activeProofOrderId}"]`);
        if (targetRow) {
          const statusBadge = targetRow.querySelector('.status-badge-target');
          if (statusBadge) {
            statusBadge.textContent = action === 'accept' ? 'Diproses' : 'Menunggu Pembayaran';
            statusBadge.className = `badge-status-pill status-pill-${action === 'accept' ? 'diproses' : 'menunggu-pembayaran'} status-badge-target`;
          }
          const paidStatus = targetRow.querySelector('.order-paid-status');
          if (paidStatus) {
            paidStatus.className = `order-paid-status ${action === 'accept' ? 'paid' : 'unpaid'}`;
            paidStatus.textContent = action === 'accept' ? '✓ Lunas' : 'Belum Lunas';
          }
          const validateBtn = targetRow.querySelector('.btn-validate-order');
          if (validateBtn && action === 'accept') {
            validateBtn.remove();
          }

          // Sinkronisasi data-attributes tombol bukti bayar agar modal langsung State-Aware tanpa reload
          const viewProofBtn = targetRow.querySelector('.btn-view-payment-proof');
          if (viewProofBtn) {
            viewProofBtn.setAttribute('data-is-paid', action === 'accept' ? '1' : '0');
            viewProofBtn.setAttribute('data-status', action === 'accept' ? 'Diproses' : 'Menunggu Pembayaran');
          }
        }

        // Sinkronisasi memori lokal orders array & perbarui grafik / laporan
        const ord = orders.find((o) => o.id === activeProofOrderId);
        if (ord) {
          ord.status = action === 'accept' ? 'Diproses' : 'Menunggu Pembayaran';
          ord.is_paid = action === 'accept';
          ord.isPaid = action === 'accept';
        }
        if (typeof updateDashboardSalesChart === 'function') updateDashboardSalesChart();
        if (typeof updateSalesReport === 'function') updateSalesReport();
      } else {
        alert(data.message || 'Gagal memproses verifikasi');
      }
    } catch (err) {
      alert('Terjadi kesalahan saat memproses verifikasi');
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  if (btnAcceptPaymentProof) {
    btnAcceptPaymentProof.addEventListener('click', () => handleVerifyPaymentProof('accept'));
  }
  if (btnRejectPaymentProof) {
    btnRejectPaymentProof.addEventListener('click', () => handleVerifyPaymentProof('reject'));
  }
});

  // Mode Ujian: Cegah klik Lihat Toko hanya jika EXAM_MODE aktif di .env
  if (window.__EXAM_MODE__) {
    document.querySelector('.dropdown-menu-item[href]')?.addEventListener('click', function(e) {
      e.preventDefault();
    });
  }

