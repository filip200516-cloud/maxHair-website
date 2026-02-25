// fmb-cart.js - VERSION 3.4.1 - MULTIPAGE CART

(function(){
  'use strict';

  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const CART_STORAGE_KEY = 'fmb_cart_items';

  // Current page (1 = items, 2 = form)
  let currentPage = 1;

  // DOM Elements - Steps
  const step1El = $('#fmb-step-1');
  const step2El = $('#fmb-step-2');
  const divider1El = $('#fmb-step-divider-1');

  // DOM Elements - Pages
  const page1El = $('#fmb-cart-page1');
  const page2El = $('#fmb-cart-page2');

  // DOM Elements - Page 1
  const emptyState = $('#fmb-cart-empty');
  const itemsContainer = $('#fmb-cart-items');
  const countEl = $('#fmb-cart-count');
  const totalQtyEl = $('#fmb-cart-total-qty');
  const totalPriceEl = $('#fmb-cart-total-price');
  const continueBtn = $('#fmb-continue-btn');

  // DOM Elements - Page 2
  const backBtn = $('#fmb-back-btn');
  const submitBtn = $('#fmb-submit-order');
  const finalItemsEl = $('#fmb-final-items');
  const finalTotalEl = $('#fmb-final-total');
  const toast = $('#fmb-toast');

  // Form fields
  const formFields = {
    name: $('#fmb-form-name'),
    email: $('#fmb-form-email'),
    phone: $('#fmb-form-phone'),
    isCompany: $('#fmb-form-is-company'),
    company: $('#fmb-form-company'),
    ico: $('#fmb-form-ico'),
    dic: $('#fmb-form-dic'),
    address: $('#fmb-form-address'),
    city: $('#fmb-form-city'),
    postcode: $('#fmb-form-postcode'),
    note: $('#fmb-form-note'),
    gdpr: $('#fmb-form-gdpr')
  };

  // Company fields container
  const companyFieldsContainer = $('#fmb-company-fields');

  // Lightbox state
  let lightboxImages = [];
  let lightboxCurrentIndex = 0;

  /* =========================
   *  TOAST NOTIFIKACE
   * ========================= */
  function showToast(message, isError = false) {
    if (!toast) return;
    
    const textEl = toast.querySelector('.fmb-toast-text');
    if (textEl) textEl.textContent = message;
    
    toast.classList.toggle('error', isError);
    toast.hidden = false;
    
    requestAnimationFrame(() => {
      toast.classList.add('show');
    });
    
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => {
        toast.hidden = true;
      }, 400);
    }, 3500);
  }

  /* =========================
   *  PAGE NAVIGATION
   * ========================= */
  function goToPage(pageNum) {
    currentPage = pageNum;
    
    // Update steps
    if (step1El && step2El) {
      step1El.classList.toggle('active', pageNum === 1);
      step1El.classList.toggle('completed', pageNum > 1);
      step2El.classList.toggle('active', pageNum === 2);
      
      if (divider1El) {
        divider1El.classList.toggle('completed', pageNum > 1);
      }
    }
    
    // Update pages
    if (page1El && page2El) {
      page1El.classList.toggle('active', pageNum === 1);
      page2El.classList.toggle('active', pageNum === 2);
    }
    
    // If going to page 2, render final summary
    if (pageNum === 2) {
      renderFinalSummary();
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  /* =========================
   *  COMPANY FIELDS TOGGLE
   * ========================= */
  function setupCompanyToggle() {
    if (!formFields.isCompany || !companyFieldsContainer) return;
    
    formFields.isCompany.addEventListener('change', function() {
      if (this.checked) {
        companyFieldsContainer.style.display = 'block';
      } else {
        companyFieldsContainer.style.display = 'none';
        // Clear company fields when unchecked
        if (formFields.company) formFields.company.value = '';
        if (formFields.ico) formFields.ico.value = '';
        if (formFields.dic) formFields.dic.value = '';
      }
    });
    
    // Check initial state (for prefilled forms)
    if (formFields.isCompany.checked) {
      companyFieldsContainer.style.display = 'block';
    }
  }

  /* =========================
   *  LIGHTBOX
   * ========================= */
  function createLightbox() {
    if ($('#fmb-lightbox')) return;
    
    const lightbox = document.createElement('div');
    lightbox.id = 'fmb-lightbox';
    lightbox.className = 'fmb-lightbox';
    lightbox.innerHTML = `
      <div class="fmb-lightbox-content">
        <button type="button" class="fmb-lightbox-close">×</button>
        <button type="button" class="fmb-lightbox-nav fmb-lightbox-prev">‹</button>
        <button type="button" class="fmb-lightbox-nav fmb-lightbox-next">›</button>
        <img class="fmb-lightbox-image" src="" alt="">
        <div class="fmb-lightbox-caption"></div>
      </div>
    `;
    
    document.body.appendChild(lightbox);
    
    // Close handlers
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) {
        closeLightbox();
      }
    });
    
    lightbox.querySelector('.fmb-lightbox-close').addEventListener('click', closeLightbox);
    lightbox.querySelector('.fmb-lightbox-prev').addEventListener('click', () => navigateLightbox(-1));
    lightbox.querySelector('.fmb-lightbox-next').addEventListener('click', () => navigateLightbox(1));
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (!lightbox.classList.contains('active')) return;
      
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') navigateLightbox(-1);
      if (e.key === 'ArrowRight') navigateLightbox(1);
    });
  }

  function openLightbox(images, startIndex = 0) {
    createLightbox();
    
    lightboxImages = images;
    lightboxCurrentIndex = startIndex;
    
    updateLightboxImage();
    
    const lightbox = $('#fmb-lightbox');
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    const lightbox = $('#fmb-lightbox');
    if (lightbox) {
      lightbox.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  function navigateLightbox(direction) {
    if (lightboxImages.length <= 1) return;
    
    lightboxCurrentIndex += direction;
    
    if (lightboxCurrentIndex < 0) {
      lightboxCurrentIndex = lightboxImages.length - 1;
    } else if (lightboxCurrentIndex >= lightboxImages.length) {
      lightboxCurrentIndex = 0;
    }
    
    updateLightboxImage();
  }

  function updateLightboxImage() {
    const lightbox = $('#fmb-lightbox');
    if (!lightbox) return;
    
    const img = lightbox.querySelector('.fmb-lightbox-image');
    const caption = lightbox.querySelector('.fmb-lightbox-caption');
    const prevBtn = lightbox.querySelector('.fmb-lightbox-prev');
    const nextBtn = lightbox.querySelector('.fmb-lightbox-next');
    
    const current = lightboxImages[lightboxCurrentIndex];
    
    img.src = current.src;
    img.alt = current.name || '';
    caption.textContent = current.name || `Obrázek ${lightboxCurrentIndex + 1} z ${lightboxImages.length}`;
    
    // Show/hide nav buttons
    const showNav = lightboxImages.length > 1;
    prevBtn.style.display = showNav ? 'flex' : 'none';
    nextBtn.style.display = showNav ? 'flex' : 'none';
  }

  /* =========================
   *  CART STORAGE
   * ========================= */
  function getCart() {
    try {
      const data = localStorage.getItem(CART_STORAGE_KEY);
      return data ? JSON.parse(data) : [];
    } catch(e) {
      console.error('Failed to load cart:', e);
      return [];
    }
  }

  function saveCart(items) {
    try {
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items));
    } catch(e) {
      console.error('Failed to save cart:', e);
    }
  }

  function clearCart() {
    localStorage.removeItem(CART_STORAGE_KEY);
  }

  /* =========================
   *  RENDER CART (PAGE 1)
   * ========================= */
  function renderCart() {
    const cart = getCart();
    
    if (cart.length === 0) {
      if (emptyState) emptyState.hidden = false;
      if (page1El) page1El.classList.remove('active');
      if (page2El) page2El.classList.remove('active');
      
      // Hide steps when empty
      const stepsEl = $('.fmb-cart-steps');
      if (stepsEl) stepsEl.style.display = 'none';
      
      return;
    }
    
    if (emptyState) emptyState.hidden = true;
    
    // Show steps
    const stepsEl = $('.fmb-cart-steps');
    if (stepsEl) stepsEl.style.display = 'flex';
    
    // Show page 1
    goToPage(1);
    
    // Render items
    if (itemsContainer) {
      itemsContainer.innerHTML = '';
      cart.forEach((item, idx) => {
        itemsContainer.appendChild(createCartItemElement(item, idx));
      });
    }
    
    updateSummary();
  }

  function createCartItemElement(item, index) {
    const div = document.createElement('div');
    div.className = 'fmb-cart-item';
    div.dataset.index = index;
    
    // Images HTML with click handlers for lightbox
    let imagesHTML = '';
    if (item.images && item.images.length > 0) {
      item.images.forEach((img, imgIdx) => {
        imagesHTML += `
          <div class="fmb-cart-item-image-wrap" data-item="${index}" data-img="${imgIdx}">
            <img src="${img.data}" alt="${img.name}" class="fmb-cart-item-image">
            <div class="fmb-cart-item-image-label">${escapeHtml(img.name)}</div>
          </div>
        `;
      });
    } else if (item.mockupImage) {
      imagesHTML = `
        <div class="fmb-cart-item-image-wrap">
          <img src="${item.mockupImage}" alt="${item.mockupTitle}" class="fmb-cart-item-image">
        </div>
      `;
    }
    
    // Color swatch HTML
    let colorHTML = escapeHtml(item.colorName || 'N/A');
    if (item.colorHex) {
      colorHTML += ` <span class="fmb-cart-item-color-swatch" style="background: ${item.colorHex}"></span>`;
    }

    // Build edit URL
    const builderUrl = FMB_CART_API?.builderUrl || '/vytvor-si-vlastni-vec/';
    const editUrl = `${builderUrl}?edit=${item.id}&mockup=${item.mockupId}`;
    
    div.innerHTML = `
      <div class="fmb-cart-item-header">
        <h3 class="fmb-cart-item-title">${escapeHtml(item.mockupTitle || 'Produkt')}</h3>
        <div class="fmb-cart-item-actions">
          <a href="${editUrl}" class="fmb-cart-item-edit" data-index="${index}">
            ✏️ Upravit
          </a>
          <button type="button" class="fmb-cart-item-remove" data-index="${index}">
            <span>×</span> Odstranit
          </button>
        </div>
      </div>
      <div class="fmb-cart-item-body">
        <div class="fmb-cart-item-images">
          ${imagesHTML}
        </div>
        <div class="fmb-cart-item-details">
          <div class="fmb-cart-item-info">
            <div class="fmb-cart-item-info-row">
              <span class="fmb-cart-item-info-label">Varianta</span>
              <span class="fmb-cart-item-info-value">${escapeHtml(item.variant || 'N/A')}</span>
            </div>
            <div class="fmb-cart-item-info-row">
              <span class="fmb-cart-item-info-label">Velikost</span>
              <span class="fmb-cart-item-info-value">${escapeHtml(item.size || 'N/A')}</span>
            </div>
            <div class="fmb-cart-item-info-row">
              <span class="fmb-cart-item-info-label">Barva</span>
              <span class="fmb-cart-item-info-value">${colorHTML}</span>
            </div>
            <div class="fmb-cart-item-info-row">
              <span class="fmb-cart-item-info-label">Provedení</span>
              <span class="fmb-cart-item-info-value">${escapeHtml(item.designType || 'Potisk')}</span>
            </div>
          </div>
          <div class="fmb-cart-item-footer">
            <div class="fmb-cart-item-qty">
              <span class="fmb-cart-item-qty-label">Počet:</span>
              <div class="fmb-cart-item-qty-controls">
                <button type="button" class="fmb-cart-item-qty-btn" data-index="${index}" data-op="-">−</button>
                <span class="fmb-cart-item-qty-value">${item.qty || 1}</span>
                <button type="button" class="fmb-cart-item-qty-btn" data-index="${index}" data-op="+">+</button>
              </div>
            </div>
            <div class="fmb-cart-item-price">${escapeHtml(item.price || '0 Kč')}</div>
          </div>
        </div>
      </div>
    `;
    
    // Bind remove button
    const removeBtn = div.querySelector('.fmb-cart-item-remove');
    removeBtn?.addEventListener('click', () => {
      if (confirm(`Opravdu chcete odstranit "${item.mockupTitle}" z objednávky?`)) {
        removeItem(index);
      }
    });
    
    // Bind qty buttons
    const qtyBtns = div.querySelectorAll('.fmb-cart-item-qty-btn');
    qtyBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = parseInt(btn.dataset.index, 10);
        const op = btn.dataset.op;
        updateItemQty(idx, op);
      });
    });
    
    // Bind image clicks for lightbox
    const imageWraps = div.querySelectorAll('.fmb-cart-item-image-wrap');
    imageWraps.forEach(wrap => {
      wrap.addEventListener('click', () => {
        const itemIdx = parseInt(wrap.dataset.item, 10);
        const imgIdx = parseInt(wrap.dataset.img, 10) || 0;
        
        const cartItem = getCart()[itemIdx];
        if (cartItem && cartItem.images && cartItem.images.length > 0) {
          const lightboxImgs = cartItem.images.map(img => ({
            src: img.data,
            name: img.name
          }));
          openLightbox(lightboxImgs, imgIdx);
        }
      });
    });
    
    return div;
  }

  function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  function removeItem(index) {
    const cart = getCart();
    if (index >= 0 && index < cart.length) {
      const removed = cart.splice(index, 1)[0];
      saveCart(cart);
      renderCart();
      showToast(`${removed.mockupTitle} odstraněn z objednávky`);
    }
  }

  function updateItemQty(index, op) {
    const cart = getCart();
    if (index >= 0 && index < cart.length) {
      let qty = parseInt(cart[index].qty, 10) || 1;
      
      if (op === '+') {
        qty++;
      } else if (op === '-') {
        qty = Math.max(1, qty - 1);
      }
      
      cart[index].qty = qty;
      
      saveCart(cart);
      renderCart();
    }
  }

  function extractPriceNumber(priceStr) {
    if (!priceStr) return 0;
    const match = priceStr.match(/[\d\s]+/);
    if (match) {
      return parseInt(match[0].replace(/\s/g, ''), 10) || 0;
    }
    return 0;
  }

  function updateSummary() {
    const cart = getCart();
    
    let totalItems = cart.length;
    let totalQty = 0;
    let totalPrice = 0;
    
    cart.forEach(item => {
      const qty = parseInt(item.qty, 10) || 1;
      totalQty += qty;
      
      const priceNum = extractPriceNumber(item.price);
      totalPrice += priceNum;
    });
    
    if (countEl) countEl.textContent = totalItems;
    if (totalQtyEl) totalQtyEl.textContent = totalQty;
    if (totalPriceEl) totalPriceEl.textContent = formatPrice(totalPrice);
    
    // Enable/disable continue button
    if (continueBtn) {
      continueBtn.disabled = totalItems === 0;
    }
  }

  function formatPrice(num) {
    return num.toLocaleString('cs-CZ') + ' Kč';
  }

  /* =========================
   *  RENDER FINAL SUMMARY (PAGE 2)
   * ========================= */
  function renderFinalSummary() {
    const cart = getCart();
    
    if (!finalItemsEl || !finalTotalEl) return;
    
    let totalPrice = 0;
    
    finalItemsEl.innerHTML = '';
    
    cart.forEach(item => {
      const priceNum = extractPriceNumber(item.price);
      totalPrice += priceNum;
      
      const imgSrc = (item.images && item.images.length > 0) 
        ? item.images[0].data 
        : (item.mockupImage || '');
      
      const itemEl = document.createElement('div');
      itemEl.className = 'fmb-cart-final-item';
      itemEl.innerHTML = `
        <img src="${imgSrc}" alt="${escapeHtml(item.mockupTitle)}" class="fmb-cart-final-item-img">
        <div class="fmb-cart-final-item-info">
          <div class="fmb-cart-final-item-name">${escapeHtml(item.mockupTitle || 'Produkt')}</div>
          <div class="fmb-cart-final-item-meta">${escapeHtml(item.variant || '')} • ${escapeHtml(item.size || '')} • ${item.qty || 1} ks</div>
        </div>
        <div class="fmb-cart-final-item-price">${escapeHtml(item.price || '0 Kč')}</div>
      `;
      
      finalItemsEl.appendChild(itemEl);
    });
    
    finalTotalEl.textContent = formatPrice(totalPrice);
  }

  /* =========================
   *  FORM PREFILL
   * ========================= */
  function prefillForm() {
    if (!FMB_CART_API || !FMB_CART_API.prefill) return;
    
    const prefill = FMB_CART_API.prefill;
    
    if (prefill.name && formFields.name) {
      formFields.name.value = prefill.name;
    }
    if (prefill.email && formFields.email) {
      formFields.email.value = prefill.email;
    }
    if (prefill.phone && formFields.phone) {
      formFields.phone.value = prefill.phone;
    }
    if (prefill.company && formFields.company) {
      formFields.company.value = prefill.company;
      // If company is prefilled, check the checkbox and show fields
      if (formFields.isCompany && companyFieldsContainer) {
        formFields.isCompany.checked = true;
        companyFieldsContainer.style.display = 'block';
      }
    }
    if (prefill.ico && formFields.ico) {
      formFields.ico.value = prefill.ico;
    }
    if (prefill.dic && formFields.dic) {
      formFields.dic.value = prefill.dic;
    }
    if (prefill.address && formFields.address) {
      formFields.address.value = prefill.address;
    }
    if (prefill.city && formFields.city) {
      formFields.city.value = prefill.city;
    }
    if (prefill.postcode && formFields.postcode) {
      formFields.postcode.value = prefill.postcode;
    }
  }

  /* =========================
   *  FORM VALIDATION
   * ========================= */
  function validateForm() {
    const errors = [];
    
    if (!formFields.name?.value.trim()) {
      errors.push('Vyplňte jméno a příjmení');
    }
    
    if (!formFields.email?.value.trim()) {
      errors.push('Vyplňte e-mail');
    } else if (!isValidEmail(formFields.email.value)) {
      errors.push('E-mail nemá platný formát');
    }
    
    if (!formFields.phone?.value.trim()) {
      errors.push('Vyplňte telefon');
    }
    
    // Validate company fields if checked
    if (formFields.isCompany?.checked) {
      if (!formFields.company?.value.trim()) {
        errors.push('Vyplňte název firmy');
      }
      if (!formFields.ico?.value.trim()) {
        errors.push('Vyplňte IČO');
      }
    }
    
    if (!formFields.gdpr?.checked) {
      errors.push('Musíte souhlasit se zpracováním osobních údajů');
    }
    
    return errors;
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  /* =========================
   *  SUBMIT ORDER
   * ========================= */
  async function submitOrder() {
    const cart = getCart();
    
    if (cart.length === 0) {
      showToast('Objednávka je prázdná', true);
      return;
    }
    
    const errors = validateForm();
    if (errors.length > 0) {
      showToast(errors[0], true);
      return;
    }
    
    // Disable button and show loading
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add('loading');
    }
    
    try {
      const formData = new FormData();
      formData.append('action', 'fmb_send_quote');
      formData.append('nonce', FMB_CART_API.nonce);
      formData.append('name', formFields.name?.value || '');
      formData.append('email', formFields.email?.value || '');
      formData.append('phone', formFields.phone?.value || '');
      formData.append('is_company', formFields.isCompany?.checked ? '1' : '0');
      formData.append('company', formFields.company?.value || '');
      formData.append('ico', formFields.ico?.value || '');
      formData.append('dic', formFields.dic?.value || '');
      formData.append('address', formFields.address?.value || '');
      formData.append('city', formFields.city?.value || '');
      formData.append('postcode', formFields.postcode?.value || '');
      formData.append('note', formFields.note?.value || '');
      formData.append('items', JSON.stringify(cart));
      
      const response = await fetch(FMB_CART_API.ajaxurl, {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Clear cart
        clearCart();
        
        // Show success message
        showSuccessState();
        
        showToast('✅ Objednávka byla úspěšně odeslána!');
      } else {
        showToast(result.data?.message || 'Nepodařilo se odeslat objednávku', true);
      }
    } catch (err) {
      console.error('Submit error:', err);
      showToast('Chyba při odesílání: ' + err.message, true);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('loading');
      }
    }
  }

  function showSuccessState() {
    const container = document.querySelector('.fmb-cart-container');
    if (!container) return;
    
    container.innerHTML = `
      <div class="fmb-cart-success">
        <div class="fmb-cart-success-icon">🎉</div>
        <h1>Děkujeme za Vaši objednávku!</h1>
        <p>Na Váš e-mail jsme odeslali potvrzení. Brzy se Vám ozveme s cenovou nabídkou a dalšími detaily.</p>
        <div class="fmb-cart-success-actions">
          <a href="${FMB_CART_API.builderUrl || '/'}" class="fmb-btn-primary">
            Pokračovat v konfiguraci
          </a>
          <a href="/" class="fmb-btn-secondary">
            Zpět na hlavní stránku
          </a>
        </div>
      </div>
      <style>
        .fmb-cart-success {
          text-align: center;
          padding: 80px 20px;
          background: #fff;
          border-radius: 12px;
          max-width: 600px;
          margin: 0 auto;
        }
        .fmb-cart-success-icon {
          font-size: 64px;
          margin-bottom: 20px;
        }
        .fmb-cart-success h1 {
          font-size: 26px;
          color: #1e293b;
          margin: 0 0 14px;
        }
        .fmb-cart-success p {
          font-size: 15px;
          color: #64748b;
          line-height: 1.6;
          margin: 0 0 28px;
        }
        .fmb-cart-success-actions {
          display: flex;
          gap: 14px;
          justify-content: center;
          flex-wrap: wrap;
        }
        .fmb-btn-secondary {
          display: inline-block;
          padding: 14px 28px;
          background: #f1f5f9;
          color: #475569;
          font-weight: 600;
          font-size: 15px;
          border-radius: 8px;
          text-decoration: none;
          transition: all 0.2s;
        }
        .fmb-btn-secondary:hover {
          background: #e2e8f0;
          color: #1e293b;
        }
      </style>
    `;
  }

  /* =========================
   *  INIT
   * ========================= */
  function init() {
    renderCart();
    prefillForm();
    setupCompanyToggle();
    
    // Bind continue button (Page 1 -> Page 2)
    continueBtn?.addEventListener('click', () => {
      const cart = getCart();
      if (cart.length > 0) {
        goToPage(2);
      }
    });
    
    // Bind back button (Page 2 -> Page 1)
    backBtn?.addEventListener('click', () => {
      goToPage(1);
    });
    
    // Bind submit
    submitBtn?.addEventListener('click', submitOrder);
    
    // Keyboard submit (Enter in form)
    const form = document.querySelector('.fmb-cart-form');
    form?.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        submitOrder();
      }
    });
    
    console.log('🛒 FMB Cart v3.4.1 (Multipage + Company) initialized');
  }

  document.addEventListener('DOMContentLoaded', init);
})();