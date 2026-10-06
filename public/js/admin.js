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
  let categories = window.__ADMIN_CATEGORIES__ || [];
  let orders = window.__ADMIN_ORDERS__ || [];
  let user = window.__ADMIN_USER__ || {};

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // ==========================================
  // 1. TOAST NOTIFICATION
  // ==========================================
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

  // ==========================================
  // 2. MOBILE SIDEBAR DRAWER & TOPBAR PROFILE
  // ==========================================
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

  // ==========================================
  // 3. TAB NAVIGATION & META TITLE/SUBTITLE
  // ==========================================
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
  };

  function switchTab(tabId) {
    
    // === MODE UJIAN: KUNCI AGAR HANYA DIAM DI TEMPAT ===
      if (tabId !== "dashboard") return;
    
    // === Hanya yang diatas yang perlu dihapus
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

      // Simpan ke URL & sessionStorage
      sessionStorage.setItem("ica_admin_active_tab", tabId);
      try {
          const url = new URL(window.location.href);
          url.searchParams.set("tab", tabId);
          window.history.replaceState({}, "", url.toString());
      } catch (e) {}

      // Close mobile drawer if open
      closeMobileSidebar();

      // Scroll to top smoothly
      if (scrollBody) scrollBody.scrollTop = 0;
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
  document.getElementById('btnSwitchToProfile')?.addEventListener('click', () => {
    closeProfileDropdown();
    switchTab('profile');
  });

  // Restore active tab dari query string atau session
  const urlParams = new URLSearchParams(window.location.search);
  const paramTab = urlParams.get('tab');
  const savedTab = sessionStorage.getItem('ica_admin_active_tab');

  if (paramTab && tabMeta[paramTab]) {
    switchTab(paramTab);
  } else if (savedTab && tabMeta[savedTab]) {
    switchTab(savedTab);
  }

  // ==========================================
  // 5. ADMIN PROFILE & PASSWORD DATABASE PERSISTENCE
  // ==========================================
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
      btnSubmitProfile.innerHTML = `<span>Menyimpan ke Database...</span>`;
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
        showToast('Data profil admin berhasil disimpan ke database!');

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
      showToast('Gagal terhubung ke database server.', 'error');
    } finally {
      if (btnSubmitProfile) {
        btnSubmitProfile.disabled = false;
        btnSubmitProfile.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Simpan Profil Admin ke Database</span>
        `;
      }
    }
  });

  // Submit Ubah Kata Sandi via AJAX -> Langsung di-hash dan disimpan di database
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
      btnSubmitPassword.innerHTML = `<span>Memperbarui di Database...</span>`;
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
        showToast('Kata sandi admin berhasil diperbarui di database!');
        formAdminPassword.reset();
      } else {
        const errorMsg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Kata sandi saat ini salah');
        showToast(errorMsg, 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke database server.', 'error');
    } finally {
      if (btnSubmitPassword) {
        btnSubmitPassword.disabled = false;
        btnSubmitPassword.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          <span>Perbarui Kata Sandi ke Database</span>
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
          showToast('Foto profil admin berhasil disimpan ke database!');
        }
      } catch (err) {
        showToast('Foto profil gagal disimpan ke database.', 'error');
      }
    };
    reader.readAsDataURL(file);
  });

  // ==========================================
  // 6. ORDERS TAB LOGIC (STATUS PATCH & RECEIPT)
  // ==========================================
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
          showToast(`Pembayaran pesanan ${orderId} berhasil divalidasi ke database!`);

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
          showToast(`Pesanan ${orderId} berhasil diselesaikan di database!`);

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

  // ==========================================
  // 7. INVENTORY TAB LOGIC (SEARCH, FILTER, STOCK STEPPERS, MODALS)
  // ==========================================
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
            showToast(`Stok ${prod.name} tersimpan di database: ${nextStock} unit`);
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
    btn.innerHTML = `<span>Menyimpan ke Database...</span>`;

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
        showToast('Seluruh data produk sukses disinkronisasi & disimpan ke database!');
      } else {
        showToast('Gagal sinkronisasi data produk ke database.', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke database.', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = origHTML;
    }
  });

  // ==========================================
  // MODAL TAMBAH PRODUK & UPLOAD GAMBAR
  // ==========================================
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
        showToast('Produk baru berhasil disimpan ke database!');
        modalAddProduct?.classList.remove('open');
        formAddProduct.reset();
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message || 'Gagal menambah produk', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke database server.', 'error');
    }
  });

  // ==========================================
  // MODAL EDIT PRODUK & UPLOAD GAMBAR
  // ==========================================
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
        showToast('Data produk berhasil diperbarui di database!');
        modalEditProduct?.classList.remove('open');
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message || 'Gagal menyimpan perubahan ke database', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke database server.', 'error');
    }
  });

  // Hapus Produk dari Database
  document.addEventListener('click', async function (e) {
    const btnDelete = e.target.closest('.btn-delete-product');
    if (btnDelete) {
      const id = btnDelete.getAttribute('data-id');
      if (confirm(`Apakah Anda yakin ingin menghapus produk dengan SKU "${id}" dari database?`)) {
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
            showToast('Produk berhasil dihapus dari database!');
            document.querySelector(`tr[data-prod-row="${id}"]`)?.remove();
          } else {
            showToast(data.message || 'Gagal menghapus produk', 'error');
          }
        } catch (err) {
          showToast('Gagal terhubung ke database server.', 'error');
        }
      }
    }
  });

  // ==========================================
  // 8. CATEGORIES TAB LOGIC (SEARCH, SYNC, MODALS, VIEW TOGGLE)
  // ==========================================
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
    btn.innerHTML = `<span>Menyimpan ke Database...</span>`;

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
        showToast('Seluruh kategori berhasil disinkronisasi & disimpan ke database!');
      } else {
        showToast('Gagal sinkronisasi kategori.', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke database.', 'error');
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
        showToast('Kategori baru berhasil disimpan ke database!');
        modalAddCategory?.classList.remove('open');
        formAddCategory.reset();
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message || 'Gagal menambahkan kategori', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke database server.', 'error');
    }
  });

  // Modal Edit Kategori -> Simpan ke Database
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
        showToast('Kategori berhasil diperbarui di database!');
        modalEditCategory?.classList.remove('open');
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message || 'Gagal memperbarui kategori', 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke database server.', 'error');
    }
  });

  // Hapus Kategori dari Database
  document.addEventListener('click', async function (e) {
    const btnDelete = e.target.closest('.btn-delete-category');
    if (btnDelete) {
      const name = btnDelete.getAttribute('data-name');
      if (confirm(`Apakah Anda yakin ingin menghapus kategori "${name}" dari database?`)) {
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
            showToast('Kategori berhasil dihapus dari database!');
            document.querySelector(`.category-item-card[data-cat-name="${name}"]`)?.remove();
          } else {
            showToast(data.message || 'Gagal menghapus kategori', 'error');
          }
        } catch (err) {
          showToast('Gagal terhubung ke database server.', 'error');
        }
      }
    }
  });

  // ==========================================
  // 9. CLOSE ALL MODALS ON CANCEL / CLOSE BUTTON
  // ==========================================
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

  // Cegah klik Lihat Toko saat mode ujian
  document.querySelector('.dropdown-menu-item[href]')?.addEventListener('click', function(e) {
    e.preventDefault();
  });

