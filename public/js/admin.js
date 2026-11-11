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
    if (type === 'error') {
      iconSvg = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
    } else {
      iconSvg = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
    }

    toast.innerHTML = `${iconSvg} <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(15px)';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  };

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
      subtitle: 'Ringkasan data operasional, stok freezer, dan transaksi toko',
    },
    profile: {
      title: 'Profil Administrator',
      subtitle: 'Kelola informasi akun administrator toko, foto profil, dan kata sandi',
    },
    orders: {
      title: 'Proses Pesanan Pelanggan',
      subtitle: 'Pantau transaksi pembayaran, status pengiriman kurir, dan kelola pesanan',
    },
    inventory: {
      title: 'Data Produk & Inventaris Freezer',
      subtitle: 'Kelola stok di freezer, pembaruan harga berkala, dan detail kemasan produk',
    },
    categories: {
      title: 'Manajemen Kategori Produk',
      subtitle: 'Kelola pengelompokan jenis makanan beku pada katalog etalase belanja pelanggan',
    },
    reports: {
      title: 'Laporan Penjualan & Analisis Toko',
      subtitle: 'Pantau grafik tren omzet pendapatan, performa transaksi harian, dan analisis performa toko',
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

  // Quick cross-tab links
  document.getElementById('btnOverviewSeeAllOrders')?.addEventListener('click', () => switchTab('orders'));
  document.getElementById('btnOverviewSeeInventory')?.addEventListener('click', () => switchTab('inventory'));
  document.getElementById('btnOverviewSeeReports')?.addEventListener('click', () => switchTab('reports'));
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

  // --- 5. Manajemen Pesanan, Status Transaksi & Cetak Nota (Tab Orders) ---
  const orderFilterBtns = document.querySelectorAll('.order-filter-btn');
  const orderSearchInput = document.getElementById('orderSearchInput');
  const orderRows = document.querySelectorAll('#ordersTableBody tr[data-order-row]');
  let activeOrderStatusFilter = 'Semua';

  function filterOrders() {
    const q = (orderSearchInput?.value || '').toLowerCase().trim();

    orderRows.forEach((row) => {
      const status = row.getAttribute('data-status') || '';
      const text = row.textContent.toLowerCase();

      const matchStatus = activeOrderStatusFilter === 'Semua' || status === activeOrderStatusFilter;
      const matchSearch = !q || text.includes(q);

      row.style.display = matchStatus && matchSearch ? '' : 'none';
    });
  }

  orderFilterBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      orderFilterBtns.forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
      activeOrderStatusFilter = this.getAttribute('data-status') || 'Semua';
      filterOrders();
    });
  });

  orderSearchInput?.addEventListener('input', filterOrders);

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
            ord.isPaid = true;
          }

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
            ord.isPaid = true;
          }

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
      if (order.status === 'Selesai' || order.is_paid) {
        receiptPayStatus.textContent = 'LUNAS (SELESAI)';
        receiptPayStatus.style.color = '#059669';
      } else {
        receiptPayStatus.textContent = `STATUS: ${order.status.toUpperCase()}`;
        receiptPayStatus.style.color = '#F59E0B';
      }
    }

    modalReceipt?.classList.add('open');
  };

  document.addEventListener('click', function (e) {
    const btnReceipt = e.target.closest('.btn-open-receipt');
    if (btnReceipt) {
      const id = btnReceipt.getAttribute('data-order-id');
      if (id) openOrderReceipt(id);
    }
  });

  // --- 6. Manajemen Inventaris Produk & Filter Pencarian (Tab Inventory) ---
  const inventorySearchInput = document.getElementById('inventorySearchInput');
  const prodFilterBtns = document.querySelectorAll('.prod-filter-btn');
  const inventoryRows = document.querySelectorAll('#inventoryTableBody tr[data-prod-row]');
  let activeInventoryFilter = 'all';

  function filterInventory() {
    const q = (inventorySearchInput?.value || '').toLowerCase().trim();

    inventoryRows.forEach((row) => {
      const stock = parseInt(row.getAttribute('data-stock') || '0', 10);
      const text = row.textContent.toLowerCase();

      const matchFilter = activeInventoryFilter === 'all' || stock <= 5;
      const matchSearch = !q || text.includes(q);

      row.style.display = matchFilter && matchSearch ? '' : 'none';
    });
  }

  prodFilterBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      prodFilterBtns.forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
      activeInventoryFilter = this.getAttribute('data-filter') || 'all';
      filterInventory();
    });
  });

  inventorySearchInput?.addEventListener('input', filterInventory);

  // Stock Steppers (+ / -) -> Langsung tersimpan di database
  document.addEventListener('click', async function (e) {
    const btnInc = e.target.closest('.btn-stock-inc');
    const btnDec = e.target.closest('.btn-stock-dec');

    if (btnInc || btnDec) {
      const id = (btnInc || btnDec).getAttribute('data-id');
      const diff = btnInc ? 1 : -1;
      const prod = products.find((p) => p.id === id);

      if (prod) {
        const nextStock = Math.max(0, (prod.stock || 0) + diff);
        prod.stock = nextStock;

        // Update stepper UI target immediately
        document.querySelectorAll(`.stock-val-target[data-id="${id}"]`).forEach((el) => {
          el.textContent = nextStock;
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
            body: JSON.stringify({ diff: diff }),
          });
          const resData = await res.json();
          if (res.ok && resData.success) {
            showToast(`Stok "${prod.name}" berhasil diperbarui: ${nextStock} unit`);
          }
        } catch (err) {
          console.warn('Background stock sync failed');
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
  bundleSearchInput?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.bundle-row-item').forEach((row) => {
      const name = (row.getAttribute('data-name') || '').toLowerCase();
      const id = (row.getAttribute('data-id') || '').toLowerCase();
      if (!q || name.includes(q) || id.includes(q)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });

  // Modal Tambah Paket Hemat
  const modalAddBundle = document.getElementById('modalAddBundle');
  const btnOpenAddBundleModal = document.getElementById('btnOpenAddBundleModal');
  const formAddBundle = document.getElementById('formAddBundle');

  btnOpenAddBundleModal?.addEventListener('click', function () {
    formAddBundle?.reset();
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
    if (priceInput && priceInput.value && badgeRightInput && !badgeRightInput.dataset.manual) {
      const p = parseInt(priceInput.value || '0', 10);
      if (totalNormal > p) {
        badgeRightInput.value = `Hemat Rp ${(totalNormal - p).toLocaleString('id-ID')}`;
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
    if (badgeRightInput && !badgeRightInput.dataset.manual && totalNormal > p) {
      badgeRightInput.value = `Hemat Rp ${(totalNormal - p).toLocaleString('id-ID')}`;
    }
  });

  document.getElementById('addBundleBadgeRight')?.addEventListener('input', function () {
    this.dataset.manual = 'true';
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
        setTimeout(() => window.location.reload(), 600);
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

  document.addEventListener('click', function (e) {
    const btnEdit = e.target.closest('.btn-edit-bundle');
    if (btnEdit) {
      const id = btnEdit.getAttribute('data-id');
      const bundle = bundles.find((b) => b.id === id);
      if (bundle) {
        if (editBundleId) editBundleId.value = bundle.id;
        if (editBundleName) editBundleName.value = bundle.name;
        if (editBundleBadgeLeft) editBundleBadgeLeft.value = bundle.badge_left || '';
        if (editBundleBadgeRight) editBundleBadgeRight.value = bundle.badge_right || '';
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
        setTimeout(() => window.location.reload(), 600);
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
          setTimeout(() => window.location.reload(), 500);
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
          setTimeout(() => window.location.reload(), 500);
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

  categorySearchInput?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    
    // Filter Card Grid
    const categoryCards = document.querySelectorAll('.category-item-card');
    categoryCards.forEach((card) => {
      const name = (card.getAttribute('data-cat-name') || '').toLowerCase();
      card.style.display = !q || name.includes(q) ? '' : 'none';
    });

    // Filter List Table Rows
    const categoryRows = document.querySelectorAll('#categoriesTableBody tr[data-cat-row]');
    categoryRows.forEach((row) => {
      const name = (row.getAttribute('data-cat-name') || '').toLowerCase();
      row.style.display = !q || name.includes(q) ? '' : 'none';
    });
  });

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

    let filtered = products.filter((p) => {
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

    if (filtered.length === 0) {
      posProductsGrid.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem; color: #94A3B8;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.5rem; color: #CBD5E1;">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p style="font-size: 0.8125rem; font-weight: 700; margin: 0; color: #64748B;">Tidak ada produk yang cocok</p>
          <p style="font-size: 11px; margin: 4px 0 0.85rem;">Coba kata kunci pencarian atau kategori lain</p>
          <button type="button" id="btnPosResetFilter" class="btn-primary-blue" style="margin: 0 auto; padding: 0.4rem 0.9rem; font-size: 11px; border-radius: 9999px; cursor: pointer;">Tampilkan Semua Produk</button>
        </div>
      `;
      document.getElementById('btnPosResetFilter')?.addEventListener('click', () => {
        resetPosFilters();
      });
      return;
    }

    posProductsGrid.innerHTML = filtered.map((p) => {
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

      return `
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
    }).join('');
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
            <button type="button" class="pos-qty-pill-btn btn-pos-qty-minus" data-id="${it.id}" title="Kurangi">-</button>
            <span class="pos-qty-pill-val">${it.qty}</span>
            <button type="button" class="pos-qty-pill-btn btn-pos-qty-plus" data-id="${it.id}" ${isMax ? 'disabled' : ''} title="Tambah">+</button>
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

    const received = parseInt(posCashReceived?.value || 0, 10);
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

  function addToPosCart(prodId) {
    const prod = products.find((p) => p.id === prodId);
    if (!prod) return;

    const currentStock = parseInt(prod.stock, 10) || 0;
    if (currentStock <= 0) {
      showToast(`Stok ${prod.name} habis di freezer!`, 'error');
      return;
    }

    const existing = posCart.find((it) => it.id === prodId);
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
      const prodId = card.getAttribute('data-prod-id');
      if (prodId) addToPosCart(prodId);
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

  // Cash Received Input & Quick Cash Buttons
  posCashReceived?.addEventListener('input', () => {
    const total = posCart.reduce((sum, it) => sum + (it.price * it.qty), 0);
    calculatePosChange(total);
  });

  document.querySelectorAll('.pos-btn-quick-cash').forEach((btn) => {
    btn.addEventListener('click', function () {
      const total = posCart.reduce((sum, it) => sum + (it.price * it.qty), 0);
      const val = this.getAttribute('data-cash');
      if (val === 'exact') {
        if (posCashReceived) posCashReceived.value = total;
      } else {
        if (posCashReceived) posCashReceived.value = parseInt(val, 10);
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
    const cashVal = parseInt(posCashReceived?.value || 0, 10);

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
        if (data.order?.id && typeof window.openReceiptModal === 'function') {
          setTimeout(() => {
            window.openReceiptModal(data.order.id);
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
          showToast(data.message || 'Impor data produk berhasil!');
          modalImportExcel.classList.remove('open');
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          showToast(data.message || 'Gagal mengimpor data produk.', 'error');
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

  // ==========================================
  // 11. MODUL LAPORAN PENJUALAN & ANALISIS TOKO (CHART.JS)
  // ==========================================
  let salesChartInstance = null;
  let dashboardSalesChartInstance = null;
  let activeReportRange = '7'; // '7', '30', 'month', 'all'

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
      if (o.status !== 'Selesai' && !o.is_paid) return false;
      const oDate = new Date(o.order_date || o.created_at || now);
      return oDate >= startDate;
    });

    validOrders.forEach((o) => {
      const d = new Date(o.order_date || o.created_at);
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

    // Filter valid orders: not 'Dibatalkan'
    const periodOrders = orders.filter((o) => {
      if (o.status === 'Dibatalkan') return false;
      const oDate = new Date(o.order_date || o.created_at || now);
      if (startDate && oDate < startDate) return false;
      return true;
    });

    const successfulOrders = periodOrders.filter((o) => o.status === 'Selesai' || o.is_paid);

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
      // Semua waktu
      const sorted = [...successfulOrders].sort((a, b) => new Date(a.order_date || a.created_at) - new Date(b.order_date || b.created_at));
      if (sorted.length > 0) {
        sorted.forEach((o) => {
          const d = new Date(o.order_date || o.created_at);
          const y = d.getFullYear();
          const m = String(d.getMonth() + 1).padStart(2, '0');
          const day = String(d.getDate()).padStart(2, '0');
          const key = `${y}-${m}-${day}`;
          if (!dailyMap.hasOwnProperty(key)) {
            dailyMap[key] = 0;
            chartLabels.push(d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
          }
        });
      } else {
        const key = now.toISOString().split('T')[0];
        dailyMap[key] = 0;
        chartLabels.push(now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
      }
    }

    // Populate daily revenues
    successfulOrders.forEach((o) => {
      const d = new Date(o.order_date || o.created_at);
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const key = `${y}-${m}-${day}`;
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
            label: 'Omzet Harian',
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
          const catName = prodObj ? prodObj.category : (it.category || 'Lainnya');
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

    // 5. Transactions Table for Period
    if (tableCountBadge) tableCountBadge.textContent = `${periodOrders.length} Transaksi`;

    if (tableBodyEl) {
      if (periodOrders.length === 0) {
        tableBodyEl.innerHTML = `
          <tr>
            <td colspan="6" style="text-align: center; color: #9CA3AF; padding: 2.5rem;">
              Tidak ada riwayat transaksi pada rentang periode ini.
            </td>
          </tr>
        `;
      } else {
        let rows = '';
        periodOrders.slice(0, 15).forEach((o) => {
          const isPaid = o.is_paid || o.status === 'Selesai';
          const statusBadge = isPaid
            ? `<span class="badge-status selesai">Selesai</span>`
            : `<span class="badge-status ${o.status === 'Diproses' ? 'diproses' : 'menunggu'}">${o.status || 'Menunggu'}</span>`;

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
      }
    }
  }

  // Setup range buttons listener
  document.querySelectorAll('.report-range-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.report-range-btn').forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
      activeReportRange = this.getAttribute('data-range') || '7';
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
});

  // Mode Ujian: Cegah klik Lihat Toko hanya jika EXAM_MODE aktif di .env
  if (window.__EXAM_MODE__) {
    document.querySelector('.dropdown-menu-item[href]')?.addEventListener('click', function(e) {
      e.preventDefault();
    });
  }

