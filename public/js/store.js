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

  // Global Toast Function
  window.showToast = function (message, type = 'success') {
    let container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.style.cssText = 'position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none;';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
      display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem;
      border-radius: 14px; font-size: 0.8125rem; font-weight: 700; color: #FFFFFF;
      background: ${type === 'error' ? '#EF4444' : '#10B981'};
      box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); pointer-events: auto;
      transition: all 0.3s ease; transform: translateY(0); opacity: 1;
    `;
    const icon = type === 'error'
      ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`
      : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;

    toast.innerHTML = `${icon}<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(12px)';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  };

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

  // Load Cart from LocalStorage
  try {
    const saved = localStorage.getItem('ica_cart');
    if (saved) cart = JSON.parse(saved);
  } catch (e) {
    cart = [];
  }

  // --- 2. Efek Scroll Navbar & Scrollspy Menu Aktif ---
  const navbarWrapper = document.getElementById('navbarWrapper');
  const navBtns = document.querySelectorAll('.nav-item-btn');
  let isScrollTicking = false;

  function handleNavbarScroll() {
    if (!isScrollTicking) {
      window.requestAnimationFrame(() => {
        const y = window.scrollY;
        // Hysteresis threshold: mencegah getar/loop bolak-balik
        if (y > 45) {
          navbarWrapper?.classList.add('scrolled');
        } else if (y < 15) {
          navbarWrapper?.classList.remove('scrolled');
        }

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

        isScrollTicking = false;
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
  const mobileDrawerMenu = document.getElementById('mobileDrawerMenu');

  // Toggle User Profile Dropdown on Desktop
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
  }

  // Toggle Mobile Menu Drawer
  if (btnMobileMenuToggle && mobileDrawerMenu) {
    btnMobileMenuToggle.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const isVisible = mobileDrawerMenu.style.display === 'block';
      mobileDrawerMenu.style.display = isVisible ? 'none' : 'block';
    });

    // Mobile nav links click & smooth scroll
    mobileDrawerMenu.querySelectorAll('.mobile-nav-link[data-target]').forEach((link) => {
      link.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        mobileDrawerMenu.style.display = 'none';
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
    btn.addEventListener('click', function () {
      const targetInputId = this.getAttribute('data-target');
      const input = document.getElementById(targetInputId);
      if (input) {
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
      }
    });
  });

  // Submit Profile Form via AJAX to Database
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
      btnSubmitCustProfile.innerHTML = `<span>Menyimpan ke Database...</span>`;
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
        showToast('Data profil berhasil disimpan ke database!');

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
      showToast('Gagal terhubung ke database server.', 'error');
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
      btnSubmitCustPassword.innerHTML = `<span>Menyimpan ke Database...</span>`;
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
        showToast('Kata sandi berhasil diperbarui di database!');
        formCustomerPassword.reset();
        closeCustomerProfileModal();
      } else {
        const errorMsg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Kata sandi saat ini salah');
        showToast(errorMsg, 'error');
      }
    } catch (err) {
      showToast('Gagal terhubung ke database server.', 'error');
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

  // --- 6. Pencarian Produk & Filter Kategori Katalog ---
  const searchInput = document.getElementById('searchInput');
  const mobileSearchInput = document.getElementById('mobileSearchInput');
  const btnClearSearch = document.getElementById('btnClearSearch');
  const catalogGrid = document.getElementById('catalogGrid');
  const catalogCountText = document.getElementById('catalogCountText');
  const categoryPillContainer = document.getElementById('categoryPillContainer');

  function filterCatalog() {
    const q = searchQuery.toLowerCase().trim();
    const cards = catalogGrid?.querySelectorAll('.product-card-single');
    let visibleCount = 0;

    cards?.forEach((card) => {
      const title = card.querySelector('.card-title-heading')?.textContent.toLowerCase() || '';
      const cat = card.querySelector('.badge-cat-tag')?.textContent.trim() || '';
      const desc = card.querySelector('.card-desc-snippet')?.textContent.toLowerCase() || '';

      const matchCategory = selectedCategory === 'Semua' || cat === selectedCategory;
      const matchSearch = !q || title.includes(q) || cat.toLowerCase().includes(q) || desc.includes(q);

      if (matchCategory && matchSearch) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (catalogCountText) {
      catalogCountText.textContent = visibleCount;
    }
  }

  function handleSearchInput(val) {
    searchQuery = val;
    if (searchInput) searchInput.value = val;
    if (mobileSearchInput) mobileSearchInput.value = val;
    if (btnClearSearch) btnClearSearch.style.display = val ? 'flex' : 'none';
    filterCatalog();
  }

  if (searchInput) {
    searchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
  }
  if (mobileSearchInput) {
    mobileSearchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
  }
  if (btnClearSearch) {
    btnClearSearch.addEventListener('click', () => handleSearchInput(''));
  }

  // Category Pills
  if (categoryPillContainer) {
    categoryPillContainer.querySelectorAll('.btn-category-pill').forEach((pill) => {
      pill.addEventListener('click', function () {
        categoryPillContainer.querySelectorAll('.btn-category-pill').forEach((p) => p.classList.remove('selected'));
        this.classList.add('selected');
        selectedCategory = this.getAttribute('data-cat') || 'Semua';
        filterCatalog();
      });
    });
  }

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
  const detailShelfLife = document.getElementById('detailShelfLife');
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
    if (detailShelfLife) detailShelfLife.textContent = prod.shelfLife || '6 Bulan';

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
      } else {
        alert('Jumlah melebihi stok yang tersedia');
      }
    });
  }

  if (btnDetailAddToCart) {
    btnDetailAddToCart.addEventListener('click', () => {
      if (activeDetailProduct) {
        addToCart(activeDetailProduct, detailQty);
        window.closeDetailModal();
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
  const paymentModalMethodBadge = document.getElementById('paymentModalMethodBadge');
  const paymentDynamicContent = document.getElementById('paymentDynamicContent');
  const paymentVerifyingBox = document.getElementById('paymentVerifyingBox');
  const paymentSuccessNotice = document.getElementById('paymentSuccessNotice');
  const paymentModalFooter = document.getElementById('paymentModalFooter');
  const btnConfirmPayment = document.getElementById('btnConfirmPayment');
  const btnConfirmPaymentText = document.getElementById('btnConfirmPaymentText');

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

  function addToCart(product, qty = 1) {
    // Validasi Tamu: Wajib login sebelum dapat membeli produk
    if (!window.__CURRENT_USER__ || !window.__IS_LOGGED_IN__) {
      if (confirm('Silakan masuk ke akun Anda terlebih dahulu untuk mulai memesan produk di Ica Frozen Food.\n\nKlik "OK" untuk menuju halaman Login sekarang.')) {
        window.location.href = window.__LOGIN_URL__ || '/login';
      }
      return;
    }

    if (product.stock <= 0) {
      alert('Maaf, produk ini sedang habis!');
      return;
    }

    const idx = cart.findIndex((it) => it.product.id === product.id);
    if (idx > -1) {
      const newQty = cart[idx].qty + qty;
      if (newQty > product.stock) {
        alert(`Stok maksimal hanya ${product.stock}`);
        return;
      }
      cart[idx].qty = newQty;
    } else {
      cart.push({ product, qty });
    }

    saveCart();
    // Notification feedback
    btnOpenCart?.classList.add('animate-bounce');
    setTimeout(() => btnOpenCart?.classList.remove('animate-bounce'), 800);
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
      alert(`Stok maksimal hanya ${item.product.stock}`);
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
            <h4 style="font-weight: 700; font-size: 0.75rem; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${it.product.name}</h4>
            <p style="font-size: 11px; color: var(--soft-blue); font-weight: 700;">Rp ${Number(it.product.price).toLocaleString('id-ID')}</p>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
              <button onclick="window.updateCartQty('${it.product.id}', -1)" style="width: 1.5rem; height: 1.5rem; border-radius: 6px; background-color: #F3F4F6; border: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: center; color: #4B5563; font-weight: 700; cursor: pointer;">-</button>
              <span style="font-size: 0.75rem; font-weight: 700; color: var(--navy); width: 1.25rem; text-align: center;">${it.qty}</span>
              <button onclick="window.updateCartQty('${it.product.id}', 1)" style="width: 1.5rem; height: 1.5rem; border-radius: 6px; background-color: #F3F4F6; border: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: center; color: #4B5563; font-weight: 700; cursor: pointer;">+</button>
            </div>
          </div>
          <div style="text-align: right;">
            <p style="font-weight: 700; font-size: 0.75rem; color: var(--navy);">Rp ${(it.qty * it.product.price).toLocaleString('id-ID')}</p>
            <button onclick="window.removeCartItem('${it.product.id}')" style="color: #9CA3AF; padding: 0.25rem; margin-top: 0.25rem; cursor: pointer; background: transparent; border: none;" title="Hapus">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
          </div>
        </div>
      `;
    });

    itemsHtml += `</div>`;
    cartItemsWrap.innerHTML = itemsHtml;
  }

  window.openCartDrawer = function () {
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
        if (confirm('Silakan masuk ke akun Anda terlebih dahulu untuk menyelesaikan pemesanan produk.\n\nKlik "OK" untuk menuju halaman Login sekarang.')) {
          window.location.href = window.__LOGIN_URL__ || '/login';
        }
        return;
      }

      if (cart.length === 0) return;

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

  function renderQrisView(total) {
    const formatted = 'Rp ' + Number(total).toLocaleString('id-ID');
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

        <div style="margin-top: 0.75rem; text-align: center;">
          <p style="font-size: 11px; color: #64748B;">Nominal Pembayaran:</p>
          <p style="font-size: 1.15rem; font-weight: 900; color: var(--navy);">${formatted}</p>
        </div>

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
            <span>Pastikan nama merchant <strong>ICA FROZEN FOOD</strong> dan nominal sesuai, lalu selesaikan pembayaran.</span>
          </li>
          <li class="qris-step-item">
            <span class="qris-step-number">4</span>
            <span>Setelah berhasil, klik tombol <strong>"Konfirmasi Bahwa Sudah Bayar"</strong> di bawah.</span>
          </li>
        </ul>
      </div>
    `;
  }

  function renderTransferView(total) {
    const formatted = 'Rp ' + Number(total).toLocaleString('id-ID');
    return `
      <div class="bank-transfer-box">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 10px; background-color: #0060AF; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 11px;">
              BCA
            </div>
            <div>
              <h4 style="font-weight: 800; font-size: 0.85rem; color: var(--navy);">Bank Central Asia</h4>
              <p style="font-size: 11px; color: #64748B;">Transfer Antar Rekening / Antar Bank</p>
            </div>
          </div>
          <span style="font-size: 10px; font-weight: 700; color: #0284C7; background-color: #F0F9FF; border: 1px solid #BAE6FD; padding: 2px 8px; border-radius: 9999px;">Otomatis</span>
        </div>

        <div class="bank-acc-row">
          <div>
            <span style="font-size: 11px; color: #64748B; display: block;">Nomor Rekening:</span>
            <strong id="bcaAccNumber" style="font-family: monospace; font-size: 1.15rem; color: var(--navy); letter-spacing: 1px;">782-019-2341</strong>
            <span style="font-size: 11px; color: #475569; display: block;">a/n Ica Frozen Food</span>
          </div>
          <button type="button" class="btn-copy-acc" onclick="window.copyAccountNumber('7820192341', this)">
            Salin No. Rek
          </button>
        </div>

        <div style="padding: 0.75rem 1rem; background-color: #F1F5F9; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #475569;">Nominal Ditransfer:</span>
          <span style="font-size: 1rem; font-weight: 800; color: var(--navy);">${formatted}</span>
        </div>

        <ul class="qris-instructions-list">
          <li class="qris-step-item">
            <span class="qris-step-number">1</span>
            <span>Buka aplikasi BCA Mobile / KlikBCA / ATM BCA terdekat.</span>
          </li>
          <li class="qris-step-item">
            <span class="qris-step-number">2</span>
            <span>Transfer ke rekening di atas dengan nominal tepat <strong>${formatted}</strong>.</span>
          </li>
          <li class="qris-step-item">
            <span class="qris-step-number">3</span>
            <span>Tekan tombol <strong>"Konfirmasi Bahwa Sudah Bayar"</strong> agar sistem memvalidasi mutasi.</span>
          </li>
        </ul>
      </div>
    `;
  }

  function renderCashView(total) {
    const formatted = 'Rp ' + Number(total).toLocaleString('id-ID');
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

        <div style="padding: 0.875rem; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
          <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748B; margin-bottom: 4px;">
            <span>Total Bayar di Kasir:</span>
            <strong style="color: var(--navy); font-size: 0.95rem;">${formatted}</strong>
          </div>
          <div style="font-size: 11px; color: #475569; line-height: 1.4; border-top: 1px dashed #E2E8F0; padding-top: 6px; margin-top: 6px;">
            📍 <strong>Lokasi Pengambilan:</strong> Outlet Ica Frozen Food, Loktabat Utara Banjarbaru (Buka 08.00 - 21.00 WITA).
          </div>
        </div>

        <p style="font-size: 0.75rem; color: #64748B; line-height: 1.45;">
          Klik tombol <strong>"Konfirmasi Pesanan Saya"</strong> di bawah agar pesanan langsung masuk ke antrean kasir freezer dan disiapkan sebelum kedatangan Anda.
        </p>
      </div>
    `;
  }

  window.copyAccountNumber = function (accNumber, btnEl) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(accNumber).then(() => {
        const origText = btnEl.textContent;
        btnEl.textContent = '✓ Tersalin';
        btnEl.style.backgroundColor = '#059669';
        setTimeout(() => {
          btnEl.textContent = origText;
          btnEl.style.backgroundColor = 'var(--soft-blue)';
        }, 1800);
      });
    }
  };

  function openPaymentModal(orderPayload) {
    activePendingOrder = orderPayload;
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
      if (btnConfirmPaymentText) {
        btnConfirmPaymentText.textContent = orderPayload.payment_method.includes('Tunai')
          ? 'Konfirmasi Pesanan Saya'
          : 'Konfirmasi Bahwa Sudah Bayar';
      }
    }

    if (orderPayload.payment_method.includes('QRIS')) {
      paymentDynamicContent.innerHTML = renderQrisView(orderPayload.total);
    } else if (orderPayload.payment_method.includes('Transfer Bank')) {
      paymentDynamicContent.innerHTML = renderTransferView(orderPayload.total);
    } else {
      paymentDynamicContent.innerHTML = renderCashView(orderPayload.total);
    }

    if (paymentModal) paymentModal.classList.add('active');
  }

  function closePaymentModal() {
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

  // Step 2 & 3: User clicks "Konfirmasi Bahwa Sudah Bayar" -> System verifies -> Receipt modal appears
  if (btnConfirmPayment) {
    btnConfirmPayment.addEventListener('click', async () => {
      if (!activePendingOrder) return;

      const isCash = activePendingOrder.payment_method.includes('Tunai');

      // 1. Enter Verification State
      btnConfirmPayment.disabled = true;
      if (paymentDynamicContent) paymentDynamicContent.style.display = 'none';
      if (paymentModalFooter) paymentModalFooter.style.display = 'none';
      if (paymentVerifyingBox) paymentVerifyingBox.style.display = 'flex';

      // 2. Simulate authentic verification check (1.8s)
      await new Promise((r) => setTimeout(r, 1800));

      // 3. Show Verification Success Notice
      if (paymentVerifyingBox) paymentVerifyingBox.style.display = 'none';
      if (paymentSuccessNotice) paymentSuccessNotice.style.display = 'flex';

      // 4. Save Order to Database via AJAX
      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/api/orders', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf || '',
          },
          body: JSON.stringify({
            ...activePendingOrder,
            is_paid: !isCash,
            status: isCash ? 'Menunggu Bayar' : 'Diproses',
          }),
        });

        const data = await res.json();
        const savedOrder = (res.ok && data.success && data.order) ? data.order : {
          ...activePendingOrder,
          is_paid: !isCash,
          order_date: new Date().toLocaleString('id-ID'),
        };

        // Brief delay for success feedback
        await new Promise((r) => setTimeout(r, 700));

        // 5. Empty Cart, close payment modal & drawer, and display receipt
        cart = [];
        saveCart();
        closePaymentModal();
        window.closeCartDrawer();
        window.showReceiptModal(savedOrder);

      } catch (err) {
        console.error(err);
        alert('Terjadi kendala jaringan saat menyimpan pesanan.');
        if (paymentDynamicContent) paymentDynamicContent.style.display = 'block';
        if (paymentModalFooter) paymentModalFooter.style.display = 'flex';
        if (paymentSuccessNotice) paymentSuccessNotice.style.display = 'none';
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

    if (recItemsList && order.items) {
      recItemsList.innerHTML = order.items
        .map(
          (it) => `
          <div style="display: flex; justify-content: space-between;">
            <span>${it.name} (${it.qty}x)</span>
            <span>Rp ${(it.qty * it.price).toLocaleString('id-ID')}</span>
          </div>
        `
        )
        .join('');
    }

    if (recSubtotal) recSubtotal.textContent = 'Rp ' + Number(order.subtotal || 0).toLocaleString('id-ID');
    const ice = order.ice_fee !== undefined ? order.ice_fee : (order.iceFee || 0);
    if (recIceFee) recIceFee.textContent = 'Rp ' + Number(ice).toLocaleString('id-ID');
    if (recTotal) recTotal.textContent = 'Rp ' + Number(order.total || 0).toLocaleString('id-ID');

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
    btnPrintReceipt.addEventListener('click', () => window.print());
  }

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
      if (confirm('Silakan masuk ke akun Anda terlebih dahulu untuk memesan paket hemat di Ica Frozen Food.\n\nKlik "OK" untuk menuju halaman Login sekarang.')) {
        window.location.href = window.__LOGIN_URL__ || '/login';
      }
      return;
    }

    const bundle = bundles.find((b) => b.id === bundleId);
    if (!bundle) return;

    let countAdded = 0;
    bundle.items.forEach((pid) => {
      const p = products.find((prod) => prod.id === pid);
      if (p && p.stock > 0) {
        addToCart(p, 1);
        countAdded++;
      }
    });

    if (countAdded > 0) {
      window.openCartDrawer();
    } else {
      alert('Maaf, produk dalam paket ini sedang tidak tersedia.');
    }
  };

  // Initial cart render
  renderCart();
});
