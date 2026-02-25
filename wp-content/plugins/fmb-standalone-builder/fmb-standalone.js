// fmb-standalone.js - VERSION 4.0 - LARGER UI & ADAPTIVE ZONE OVERLAY

(function(){
  'use strict';

  const $  = (s,r=document)=>r.querySelector(s);
  const $$ = (s,r=document)=>Array.from(r.querySelectorAll(s));

  const previewWrap = $('#fmb-preview');
  const viewsWrap   = $('#fmb-views');
  const mockupList  = $('#fmb-mockup-list');
  const priceEl     = $('#fmb-price');
  const qtyEl       = $('#fmb-qty');
  const colorsWrap  = $('#fmb-colors');
  const variantsWrap= $('#fmb-variants');
  const sizesWrap   = $('#fmb-sizes');
  const toolsHost   = $('#fmb-tools-host');
  const designType  = $('#fmb-design-type');
  const cartBadge   = $('#fmb-cart-badge');
  const cartLink    = $('#fmb-go-to-cart');
  const toast       = $('#fmb-toast');
  const descEl      = $('#fmb-p-desc');
  const titleEl     = $('#fmb-p-title');

  let canvas, baseImg, zoneRect;
  
  let userImages = [];
  let activeImageIndex = -1;
  let sortableInitialized = false;
  let sizeFloatTooltip = null;
  let textControlsFloat = null;

  let undoStack = [];
  let redoStack = [];
  const MAX_HISTORY = 50;

  let currentMockup = null;
  let activeViewIdx = 0;
  let activeColorIdx = 0;
  let lastLoadedMockupId = null;
  
  let globalStateStorage = new Map();

  const state = {
    qty: 1, 
    design: 'print',
    variant: null,
    size: null
  };

  const CART_STORAGE_KEY = 'fmb_cart_items';
  const STATE_STORAGE_KEY = 'fmb_builder_state';
  const MOCKUP_STATE_KEY = 'fmb_mockup_settings';
  const PRODUCT_SETTINGS_KEY = 'fmb_product_settings';

  // Maximum canvas height - INCREASED
  const MAX_CANVAS_HEIGHT = 650;

  const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
  const num = (v, def = 0) => { const n = parseFloat(v); return Number.isFinite(n) ? n : def; };

  function ajax(action, data = {}) {
    const body = new URLSearchParams({ action, nonce: FMB_API.nonce, ...data });
    return fetch(FMB_API.ajaxurl, { method: 'POST', body }).then(r => r.json());
  }

  /* =========================
   *  URL PARAMETERS
   * ========================= */
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    return {
      editId: params.get('edit'),
      mockupId: params.get('mockup')
    };
  }

  /* =========================
   *  PERSISTENT STATE STORAGE
   * ========================= */
  
  function saveGlobalStateToStorage() {
    try {
      const obj = {};
      globalStateStorage.forEach((value, key) => {
        obj[key] = value;
      });
      localStorage.setItem(STATE_STORAGE_KEY, JSON.stringify(obj));
      console.log('💾 Global state uložen do localStorage');
    } catch(e) {
      console.error('Failed to save global state:', e);
    }
  }
  
  function loadGlobalStateFromStorage() {
    try {
      const data = localStorage.getItem(STATE_STORAGE_KEY);
      if (data) {
        const obj = JSON.parse(data);
        globalStateStorage = new Map(Object.entries(obj));
        console.log(`📥 Načteno ${globalStateStorage.size} uložených stavů z localStorage`);
      }
    } catch(e) {
      console.error('Failed to load global state:', e);
      globalStateStorage = new Map();
    }
  }
  
  /* =========================
   *  PRODUCT SETTINGS (per mockup)
   * ========================= */
  
  function getProductSettingsKey(mockupId) {
    return `${PRODUCT_SETTINGS_KEY}_${mockupId}`;
  }
  
  function saveProductSettings(mockupId) {
    if (!mockupId) return;
    
    try {
      const settings = {
        mockupId: mockupId,
        colorIdx: activeColorIdx,
        viewIdx: activeViewIdx,
        qty: state.qty,
        design: state.design,
        variant: state.variant,
        size: state.size,
        timestamp: Date.now()
      };
      
      localStorage.setItem(getProductSettingsKey(mockupId), JSON.stringify(settings));
      localStorage.setItem(MOCKUP_STATE_KEY, JSON.stringify(settings));
      
      console.log(`💾 Uloženo nastavení pro mockup ${mockupId}:`, settings);
    } catch(e) {
      console.error('Failed to save product settings:', e);
    }
  }
  
  function loadProductSettings(mockupId) {
    try {
      const data = localStorage.getItem(getProductSettingsKey(mockupId));
      if (data) {
        const settings = JSON.parse(data);
        console.log(`📥 Načteno nastavení pro mockup ${mockupId}:`, settings);
        return settings;
      }
    } catch(e) {
      console.error('Failed to load product settings:', e);
    }
    return null;
  }
  
  function loadLastMockupSettings() {
    try {
      const data = localStorage.getItem(MOCKUP_STATE_KEY);
      if (data) {
        return JSON.parse(data);
      }
    } catch(e) {
      console.error('Failed to load last mockup settings:', e);
    }
    return null;
  }

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
    }, 3000);
  }

  /* =========================
   *  CART FUNCTIONS
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
      updateCartBadge();
    } catch(e) {
      console.error('Failed to save cart:', e);
    }
  }

  function updateCartBadge() {
    const cart = getCart();
    const count = cart.length;
    
    if (cartBadge) {
      cartBadge.textContent = count;
      cartBadge.dataset.count = count;
    }
    
    if (cartLink) {
      cartLink.classList.toggle('has-items', count > 0);
    }
  }

// fmb-standalone.js - VERSION 4.0.1 - přidejte/upravte funkci addToCart

  async function addToCart() {
    if (!currentMockup) {
      showToast('Nejprve vyberte produkt', true);
      return;
    }

    saveCurrentState();

    let hasDesign = false;
    if (currentMockup.views && currentMockup.views.length > 0) {
      for (let i = 0; i < currentMockup.views.length; i++) {
        const savedState = loadState(currentMockup.id, i);
        if (savedState && savedState.images && savedState.images.length > 0) {
          hasDesign = true;
          break;
        }
      }
    }

    if (!hasDesign) {
      showToast('Přidejte nejprve design na produkt', true);
      return;
    }

    // Capture preview images (mockup with design)
    const images = await captureAllViews();
    
    // Capture original images in full quality for printing
    const originalImages = await captureOriginalImages();

    const activeColorBtn = $('.fmb-swatch.active', colorsWrap);
    const colorHex = activeColorBtn ? activeColorBtn.style.background : '#ffffff';
    const colorIdx = activeColorBtn ? parseInt(activeColorBtn.dataset.idx, 10) : 0;
    let colorName = '';
    if (colorIdx >= 0 && currentMockup.colors2 && currentMockup.colors2[colorIdx]) {
      colorName = currentMockup.colors2[colorIdx].label || colorHex;
    }

    const cartItem = {
      id: Date.now() + '_' + Math.random().toString(36).substr(2, 9),
      mockupId: currentMockup.id,
      mockupTitle: currentMockup.title || 'Produkt',
      mockupImage: currentMockup.image || '',
      variant: state.variant || (currentMockup.variants && currentMockup.variants[0]) || 'N/A',
      size: state.size || (currentMockup.sizes && currentMockup.sizes[0]) || 'N/A',
      colorName: colorName,
      colorHex: colorHex,
      colorIdx: colorIdx,
      designType: state.design === 'emb' ? 'Výšivka' : 'Potisk',
      qty: state.qty || 1,
      price: priceEl ? priceEl.textContent : '0 Kč',
      images: images,
      originalImages: originalImages, // Original images in full quality
      addedAt: new Date().toISOString(),
      stateKey: `${currentMockup.id}`
    };

    const cart = getCart();
    cart.push(cartItem);
    saveCart(cart);

    showToast(`✓ ${currentMockup.title} přidán do objednávky`);
    
    console.log('🛒 Položka přidána do košíku:', cartItem);
  }

  /**
   * Capture original user-uploaded images in full quality for printing
   */
  async function captureOriginalImages() {
    if (!currentMockup || !currentMockup.views || !currentMockup.views.length) {
      return [];
    }
    
    const originalImages = [];
    
    // Go through all views and collect original images
    for (let viewIdx = 0; viewIdx < currentMockup.views.length; viewIdx++) {
      const savedState = loadState(currentMockup.id, viewIdx);
      
      if (!savedState || !savedState.images || !Array.isArray(savedState.images)) {
        continue;
      }
      
      const viewLabel = currentMockup.views[viewIdx].label || `View ${viewIdx + 1}`;
      
      for (const imgData of savedState.images) {
        // Only include actual images (not text)
        if (imgData.type === 'i-text' || imgData.type === 'text') {
          continue;
        }
        
        // Get the original source if available
        if (imgData.src) {
          originalImages.push({
            data: imgData.src,
            name: imgData.name || 'Obrázek',
            viewLabel: viewLabel,
            viewIdx: viewIdx
          });
        }
      }
    }
    
    // Also check current view's userImages for any not yet saved
    if (activeViewIdx >= 0 && userImages.length > 0) {
      const viewLabel = currentMockup.views[activeViewIdx]?.label || `View ${activeViewIdx + 1}`;
      
      for (const item of userImages) {
        if (!item || !item.img) continue;
        if (item.img.type === 'i-text' || item.img.type === 'text') continue;
        
        // Check if we already have this image from saved state
        const alreadyAdded = originalImages.some(
          orig => orig.viewIdx === activeViewIdx && orig.name === item.name
        );
        
        if (!alreadyAdded && item.originalSrc) {
          originalImages.push({
            data: item.originalSrc,
            name: item.name || 'Obrázek',
            viewLabel: viewLabel,
            viewIdx: activeViewIdx
          });
        }
      }
    }
    
    console.log(`📸 Zachyceno ${originalImages.length} originálních obrázků pro tisk`);
    
    return originalImages;
  }

  /* =========================
   *  FABRIC.JS CUSTOMIZATION
   * ========================= */
  function customizeControls() {
    fabric.Object.prototype.set({
      borderColor: '#23b1bf',
      cornerColor: '#23b1bf',
      cornerSize: 18,
      transparentCorners: false,
      cornerStyle: 'circle',
      borderScaleFactor: 3,
      padding: 8,
      lockScalingFlip: true,
      lockUniScaling: false
    });

    const rotateIcon = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='%2323b1bf'%3E%3Cpath d='M12 6v3l4-4-4-4v3c-4.42 0-8 3.58-8 8 0 1.57.46 3.03 1.24 4.26L6.7 14.8c-.45-.83-.7-1.79-.7-2.8 0-3.31 2.69-6 6-6zm6.76 1.74L17.3 9.2c.44.84.7 1.79.7 2.8 0 3.31-2.69 6-6 6v-3l-4 4 4 4v-3c4.42 0 8-3.58 8-8 0-1.57-.46-3.03-1.24-4.26z'/%3E%3C/svg%3E";
    
    const rotateImg = document.createElement('img');
    rotateImg.src = rotateIcon;

    fabric.Object.prototype.controls.mtr = new fabric.Control({
      x: 0,
      y: -0.5,
      offsetY: -44,
      cursorStyle: 'crosshair',
      actionHandler: fabric.controlsUtils.rotationWithSnapping,
      actionName: 'rotate',
      render: function(ctx, left, top, styleOverride, fabricObject) {
        const size = 30;
        ctx.save();
        ctx.translate(left, top);
        ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle || 0));
        ctx.drawImage(rotateImg, -size/2, -size/2, size, size);
        ctx.restore();
      },
      cornerSize: 30
    });

    const deleteIcon = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='%23ef4444'%3E%3Cpath d='M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z'/%3E%3C/svg%3E";
    
    const deleteImg = document.createElement('img');
    deleteImg.src = deleteIcon;

    fabric.Object.prototype.controls.deleteControl = new fabric.Control({
      x: 0.5,
      y: -0.5,
      offsetY: -18,
      offsetX: 18,
      cursorStyle: 'pointer',
      mouseUpHandler: function(eventData, transform) {
        const target = transform.target;
        const idx = userImages.findIndex(item => item.img === target);
        if (idx !== -1 && confirm(`Odstranit "${userImages[idx].name}"?`)) {
          removeUserImage(idx);
          recalcPrice();
          saveCurrentState();
          saveToHistory();
        }
        return true;
      },
      render: function(ctx, left, top, styleOverride, fabricObject) {
        const size = 30;
        ctx.save();
        ctx.translate(left, top);
        ctx.drawImage(deleteImg, -size/2, -size/2, size, size);
        ctx.restore();
      },
      cornerSize: 30
    });
  }

  function getImageSizeInCm(fabricImage, zoneRect, mockup) {
    if (!fabricImage || !zoneRect || !mockup) {
      return { width: 0, height: 0, area: 0, dpiUsed: 'N/A' };
    }

    const zonePhysicalWidth = mockup.productWidth || 25;
    const zonePhysicalHeight = mockup.productHeight || 17;

    const zonePixelWidth = zoneRect.getScaledWidth();
    const zonePixelHeight = zoneRect.getScaledHeight();

    if (zonePixelWidth === 0 || zonePixelHeight === 0) {
      return { width: 0, height: 0, area: 0, dpiUsed: 'N/A' };
    }

    const imagePixelWidth = fabricImage.getScaledWidth();
    const imagePixelHeight = fabricImage.getScaledHeight();

    const pixelsPerCmX = zonePixelWidth / zonePhysicalWidth;
    const pixelsPerCmY = zonePixelHeight / zonePhysicalHeight;

    const widthCm = imagePixelWidth / pixelsPerCmX;
    const heightCm = imagePixelHeight / pixelsPerCmY;
    const areaCm2 = widthCm * heightCm;

    return {
      width: Math.round(widthCm * 10) / 10,
      height: Math.round(heightCm * 10) / 10,
      area: Math.round(areaCm2 * 10) / 10,
      dpiUsed: 'vypočítáno'
    };
  }

  function showSizeFloat(fabricImage) {
    if (!sizeFloatTooltip) {
      sizeFloatTooltip = document.createElement('div');
      sizeFloatTooltip.className = 'fmb-size-float';
      previewWrap.appendChild(sizeFloatTooltip);
    }

    if (!fabricImage || !zoneRect || !currentMockup) {
      sizeFloatTooltip.style.display = 'none';
      return;
    }

    if (fabricImage.type === 'i-text' || fabricImage.type === 'text') {
      sizeFloatTooltip.style.display = 'none';
      return;
    }

    const size = getImageSizeInCm(fabricImage, zoneRect, currentMockup);
    const boundingRect = fabricImage.getBoundingRect(true, true);
    
    sizeFloatTooltip.textContent = `${size.width} × ${size.height} cm`;
    sizeFloatTooltip.style.display = 'block';
    sizeFloatTooltip.style.left = (boundingRect.left + boundingRect.width + 10) + 'px';
    sizeFloatTooltip.style.top = (boundingRect.top - 10) + 'px';
  }

  function hideSizeFloat() {
    if (sizeFloatTooltip) {
      sizeFloatTooltip.style.display = 'none';
    }
  }

  function createTextControlsFloat() {
    if (textControlsFloat) return;
    
    textControlsFloat = document.createElement('div');
    textControlsFloat.className = 'fmb-text-float-controls';
    textControlsFloat.style.display = 'none';
    textControlsFloat.innerHTML = `
      <div class="fmb-text-float-group">
        <label>Font:</label>
        <select class="fmb-text-float-font">
          <option value="Arial">Arial</option>
          <option value="Helvetica">Helvetica</option>
          <option value="Times New Roman">Times New Roman</option>
          <option value="Georgia">Georgia</option>
          <option value="Courier New">Courier New</option>
          <option value="Verdana">Verdana</option>
          <option value="Impact">Impact</option>
          <option value="Comic Sans MS">Comic Sans MS</option>
        </select>
      </div>
      <div class="fmb-text-float-group">
        <label>Barva:</label>
        <input type="color" class="fmb-text-float-color" value="#000000">
      </div>
    `;
    
    previewWrap.appendChild(textControlsFloat);
    
    const fontSelect = textControlsFloat.querySelector('.fmb-text-float-font');
    const colorInput = textControlsFloat.querySelector('.fmb-text-float-color');
    
    fontSelect.addEventListener('change', (e) => {
      if (activeImageIndex >= 0 && userImages[activeImageIndex]) {
        const item = userImages[activeImageIndex];
        if (item.img.type === 'i-text' || item.img.type === 'text') {
          item.img.set('fontFamily', e.target.value);
          canvas.requestRenderAll();
          saveCurrentState();
          saveToHistory();
        }
      }
    });
    
    colorInput.addEventListener('change', (e) => {
      if (activeImageIndex >= 0 && userImages[activeImageIndex]) {
        const item = userImages[activeImageIndex];
        if (item.img.type === 'i-text' || item.img.type === 'text') {
          item.img.set('fill', e.target.value);
          canvas.requestRenderAll();
          saveCurrentState();
          saveToHistory();
        }
      }
    });
  }

  function showTextControlsFloat(fabricText) {
    if (!textControlsFloat) {
      createTextControlsFloat();
    }
    
    if (!fabricText || (fabricText.type !== 'i-text' && fabricText.type !== 'text')) {
      textControlsFloat.style.display = 'none';
      return;
    }
    
    const boundingRect = fabricText.getBoundingRect(true, true);
    
    const fontSelect = textControlsFloat.querySelector('.fmb-text-float-font');
    const colorInput = textControlsFloat.querySelector('.fmb-text-float-color');
    
    fontSelect.value = fabricText.fontFamily || 'Arial';
    colorInput.value = fabricText.fill || '#000000';
    
    textControlsFloat.style.display = 'flex';
    textControlsFloat.style.left = (boundingRect.left) + 'px';
    textControlsFloat.style.top = (boundingRect.top + boundingRect.height + 10) + 'px';
  }

  function hideTextControlsFloat() {
    if (textControlsFloat) {
      textControlsFloat.style.display = 'none';
    }
  }

  /* =========================
   *  STATE MANAGEMENT
   * ========================= */
  
  function getStateKey(mockupId, viewIdx) {
    return `${mockupId}:${viewIdx}`;
  }
  
  function serializeCanvasObjects() {
    if (!userImages || userImages.length === 0) {
      return null;
    }
    
    let totalAreaCm2 = 0;
    
    const serialized = userImages.map(item => {
      if (!item || !item.img) return null;
      
      const baseData = {
        name: item.name || 'Objekt',
        thumbnail: item.thumbnail || null,
        left: item.img.left,
        top: item.img.top,
        scaleX: item.img.scaleX,
        scaleY: item.img.scaleY,
        angle: item.img.angle || 0,
        type: item.img.type
      };

      if (item.img.type === 'i-text' || item.img.type === 'text') {
        return {
          ...baseData,
          text: item.img.text,
          fontFamily: item.img.fontFamily,
          fontSize: item.img.fontSize,
          fill: item.img.fill
        };
      } else {
        let src = null;
        if (item.originalSrc) {
          src = item.originalSrc;
        } else if (item.img._originalElement && item.img._originalElement.src) {
          src = item.img._originalElement.src;
        }
        
        if (zoneRect && currentMockup) {
          const size = getImageSizeInCm(item.img, zoneRect, currentMockup);
          totalAreaCm2 += size.area;
        }
        
        return {
          ...baseData,
          src: src
        };
      }
    }).filter(item => item !== null);
    
    if (serialized.length === 0) {
      return null;
    }
    
    return {
      images: serialized,
      totalArea: totalAreaCm2,
      timestamp: Date.now()
    };
  }
  
  function saveCurrentState() {
    if (!currentMockup || activeViewIdx < 0) {
      console.log('⚠️ Nelze uložit - žádný aktivní mockup nebo view');
      return false;
    }
    
    const key = getStateKey(currentMockup.id, activeViewIdx);
    const serialized = serializeCanvasObjects();
    
    if (serialized) {
      globalStateStorage.set(key, serialized);
      console.log(`💾 Uloženo [${key}]: ${serialized.images.length} objektů, plocha ${serialized.totalArea.toFixed(1)} cm²`);
    } else {
      if (globalStateStorage.has(key)) {
        globalStateStorage.delete(key);
        console.log(`🗑️ Prázdný stav odstraněn [${key}]`);
      }
    }
    
    saveGlobalStateToStorage();
    saveProductSettings(currentMockup.id);
    
    return true;
  }
  
  function loadState(mockupId, viewIdx) {
    const key = getStateKey(mockupId, viewIdx);
    const state = globalStateStorage.get(key);
    
    if (state) {
      console.log(`📥 Nalezen stav [${key}]: ${state.images.length} objektů`);
      return state;
    }
    
    console.log(`📭 Žádný stav pro [${key}]`);
    return null;
  }
  
  function clearCanvas() {
    console.log(`🧹 Čištění canvasu (${userImages.length} objektů)`);
    
    userImages.forEach(item => {
      if (canvas && item && item.img) {
        try {
          canvas.remove(item.img);
        } catch(e) {
          console.warn('Chyba při odstraňování objektu:', e);
        }
      }
    });
    
    userImages = [];
    activeImageIndex = -1;
    
    if (canvas) {
      canvas.discardActiveObject();
      canvas.requestRenderAll();
    }
    
    hideSizeFloat();
    hideTextControlsFloat();
  }
  
  async function restoreObjects(stateData) {
    if (!stateData || !stateData.images || !Array.isArray(stateData.images) || stateData.images.length === 0) {
      console.log('📭 Žádné objekty k obnovení');
      updateImagesList();
      setupZoneDrop();
      return;
    }
    
    console.log(`📥 Obnovuji ${stateData.images.length} objektů...`);
    
    for (const objData of stateData.images) {
      await restoreSingleObject(objData);
    }
    
    updateLayerOrder();
    updateImagesList();
    setupZoneDrop();
    
    if (userImages.length > 0) {
      setActiveImage(0);
    }
    
    console.log(`✅ Obnoveno ${userImages.length} objektů`);
  }
  
  async function restoreSingleObject(objData) {
    if (!zoneRect) {
      console.warn('⚠️ Nelze obnovit objekt - zoneRect není k dispozici');
      return;
    }
    
    const z = zoneRect.getBoundingRect(true, true);
    
    if (objData.type === 'i-text' || objData.type === 'text') {
      const text = new fabric.IText(objData.text || 'Text', {
        left: objData.left,
        top: objData.top,
        fontFamily: objData.fontFamily || 'Arial',
        fontSize: objData.fontSize || 40,
        fill: objData.fill || '#000000',
        scaleX: objData.scaleX || 1,
        scaleY: objData.scaleY || 1,
        angle: objData.angle || 0,
        originX: 'center',
        originY: 'center',
        selectable: true,
        editable: true,
        lockScalingFlip: true,
        lockUniScaling: false
      });

      text.set('clipPath', new fabric.Rect({
        left: z.left,
        top: z.top,
        width: z.width,
        height: z.height,
        absolutePositioned: true
      }));

      const newIndex = userImages.length;
      userImages.push({
        img: text,
        name: objData.name || `Text: ${(objData.text || '').substring(0, 20)}`,
        thumbnail: objData.thumbnail,
        uploadTime: new Date().toLocaleString()
      });

      setupImageEvents(text, newIndex);
      canvas.add(text);
      
    } else if (objData.src) {
      await new Promise((resolve) => {
        fabric.Image.fromURL(objData.src, (img) => {
          if (!img) {
            console.warn('⚠️ Nepodařilo se načíst obrázek');
            resolve();
            return;
          }
          
          img.set({
            left: objData.left,
            top: objData.top,
            scaleX: objData.scaleX || 1,
            scaleY: objData.scaleY || 1,
            angle: objData.angle || 0,
            selectable: true,
            hasBorders: true,
            cornerColor: '#23b1bf',
            borderColor: '#23b1bf',
            transparentCorners: false,
            lockScalingFlip: true,
            lockUniScaling: false
          });
          
          img.set('clipPath', new fabric.Rect({
            left: z.left,
            top: z.top,
            width: z.width,
            height: z.height,
            absolutePositioned: true
          }));
          
          const newIndex = userImages.length;
          userImages.push({
            img: img,
            name: objData.name || 'Obrázek',
            thumbnail: objData.thumbnail,
            originalSrc: objData.src,
            uploadTime: new Date().toLocaleString()
          });
          
          setupImageEvents(img, newIndex);
          canvas.add(img);
          resolve();
        }, { crossOrigin: 'anonymous' });
      });
    }
  }

  /* =========================
   *  UNDO/REDO SYSTÉM
   * ========================= */
  
  function saveToHistory() {
    if (!currentMockup || activeViewIdx < 0) return;
    
    const historyState = {
      mockupId: currentMockup.id,
      viewIdx: activeViewIdx,
      serialized: serializeCanvasObjects()
    };
    
    undoStack.push(historyState);
    
    if (undoStack.length > MAX_HISTORY) {
      undoStack.shift();
    }
    
    redoStack = [];
    updateHistoryButtons();
  }

  async function restoreHistoryState(historyState) {
    if (!historyState || !currentMockup || historyState.mockupId !== currentMockup.id) return;
    
    if (historyState.viewIdx !== activeViewIdx) {
      await loadView(historyState.viewIdx);
    }
    
    clearCanvas();
    
    if (historyState.serialized) {
      await restoreObjects(historyState.serialized);
    }
    
    recalcPrice();
  }

  function undo() {
    if (undoStack.length === 0) return;
    
    const currentState = {
      mockupId: currentMockup?.id,
      viewIdx: activeViewIdx,
      serialized: serializeCanvasObjects()
    };
    redoStack.push(currentState);
    
    const previousState = undoStack.pop();
    restoreHistoryState(previousState);
    updateHistoryButtons();
  }

  function redo() {
    if (redoStack.length === 0) return;
    
    const currentState = {
      mockupId: currentMockup?.id,
      viewIdx: activeViewIdx,
      serialized: serializeCanvasObjects()
    };
    undoStack.push(currentState);
    
    const nextState = redoStack.pop();
    restoreHistoryState(nextState);
    updateHistoryButtons();
  }

  function updateHistoryButtons() {
    const undoBtn = $('#fmb-undo');
    const redoBtn = $('#fmb-redo');
    
    if (undoBtn) undoBtn.disabled = undoStack.length === 0;
    if (redoBtn) redoBtn.disabled = redoStack.length === 0;
  }

  /* =========================
   *  EVENTS PRO OBJEKTY
   * ========================= */

  function setupImageEvents(img, idx) {
    const clampToZone = () => {
      if (!zoneRect) return;
      const z2 = zoneRect.getBoundingRect(true, true);
      const b = img.getBoundingRect(true, true);
      let nx = img.left, ny = img.top;
      if (b.left < z2.left) nx += (z2.left - b.left);
      if (b.top < z2.top) ny += (z2.top - b.top);
      if (b.left + b.width > z2.left + z2.width) nx -= (b.left + b.width - (z2.left + z2.width));
      if (b.top + b.height > z2.top + z2.height) ny -= (b.top + b.height - (z2.top + z2.height));
      img.set({ left: nx, top: ny });
    };

    img.on('moving', () => { 
      clampToZone(); 
      recalcPrice();
      if (img.type === 'i-text' || img.type === 'text') {
        showTextControlsFloat(img);
      } else {
        showSizeFloat(img);
      }
    });
    
    img.on('scaling', () => {
      if (!zoneRect) return;
      
      const scale = Math.max(img.scaleX, img.scaleY);
      img.set({ scaleX: scale, scaleY: scale });
      
      const z2 = zoneRect.getBoundingRect(true, true);
      const b = img.getBoundingRect(true, true);
      if (b.width > z2.width || b.height > z2.height) {
        const sx = (z2.width / b.width) * img.scaleX;
        const sy = (z2.height / b.height) * img.scaleY;
        const s = Math.min(sx, sy);
        img.set({ scaleX: s, scaleY: s });
      }
      clampToZone();
      canvas.requestRenderAll();
      recalcPrice();
      if (img.type === 'i-text' || img.type === 'text') {
        showTextControlsFloat(img);
      } else {
        showSizeFloat(img);
      }
    });
    
    img.on('rotating', () => { 
      canvas.requestRenderAll(); 
      recalcPrice();
      if (img.type === 'i-text' || img.type === 'text') {
        showTextControlsFloat(img);
      } else {
        showSizeFloat(img);
      }
    });
    
    img.on('modified', () => { 
      const currentIdx = userImages.findIndex(item => item.img === img);
      if (currentIdx >= 0) {
        updateImageThumbnail(currentIdx);
      }
      recalcPrice();
      saveCurrentState();
      saveToHistory();
    });

    if (img.type === 'i-text' || img.type === 'text') {
      img.on('changed', () => {
        const currentIdx = userImages.findIndex(item => item.img === img);
        if (currentIdx >= 0) {
          userImages[currentIdx].name = `Text: ${img.text.substring(0, 20)}`;
          updateImagesList();
          saveCurrentState();
        }
      });
    }

    img.on('deselected', () => {
      hideSizeFloat();
      hideTextControlsFloat();
    });

    img.on('selected', () => {
      if (img.type === 'i-text' || img.type === 'text') {
        showTextControlsFloat(img);
      } else {
        showSizeFloat(img);
      }
    });
  }

  /* =========================
   *  CANVAS SETUP
   * ========================= */

  function ensureCanvas() {
    if (canvas) { 
      try { canvas.dispose(); } catch(_) {} 
    }
    const el = document.createElement('canvas');
    el.id = 'fmb-canvas';
    previewWrap.innerHTML = '';
    previewWrap.appendChild(el);

    canvas = new fabric.Canvas(el, {
      selection: false, 
      preserveObjectStacking: true,
      controlsAboveOverlay: true, 
      backgroundColor: '#fff',
      uniformScaling: true
    });
    canvas.uniScaleTransform = true;
    
    canvas.on('selection:created', (e) => {
      const obj = e.selected[0];
      const idx = userImages.findIndex(item => item.img === obj);
      if (idx !== -1) {
        setActiveImage(idx);
        if (obj.type === 'i-text' || obj.type === 'text') {
          showTextControlsFloat(obj);
        } else {
          showSizeFloat(obj);
        }
      }
    });
    
    canvas.on('selection:updated', (e) => {
      const obj = e.selected[0];
      const idx = userImages.findIndex(item => item.img === obj);
      if (idx !== -1) {
        setActiveImage(idx);
        if (obj.type === 'i-text' || obj.type === 'text') {
          showTextControlsFloat(obj);
        } else {
          showSizeFloat(obj);
        }
      }
    });

    canvas.on('selection:cleared', () => {
      hideSizeFloat();
      hideTextControlsFloat();
    });

    customizeControls();
  }

  function fitCanvasToBase(img) {
    const wrapW = previewWrap.clientWidth || 700;
    const imgRatio = img.width / img.height;
    
    let canvasW = wrapW;
    let canvasH = Math.round(canvasW / imgRatio);
    
    if (canvasH > MAX_CANVAS_HEIGHT) {
      canvasH = MAX_CANVAS_HEIGHT;
      canvasW = Math.round(canvasH * imgRatio);
    }
    
    if (canvasH < 250) {
      canvasH = 250;
      canvasW = Math.round(canvasH * imgRatio);
    }

    canvas.setWidth(canvasW);
    canvas.setHeight(canvasH);
    previewWrap.style.height = canvasH + 'px';

    const scaleX = canvasW / img.width;
    const scaleY = canvasH / img.height;
    const scale = Math.max(scaleX, scaleY);
    
    baseImg.set({
      scaleX: scale,
      scaleY: scale,
      left: canvasW / 2,
      top: canvasH / 2,
      originX: 'center',
      originY: 'center'
    });

    canvas.requestRenderAll();
  }

  function zoneToPx(view) {
    const W = canvas.getWidth(), H = canvas.getHeight();
    return {
      left: W * (num(view.x) / 100),
      top: H * (num(view.y) / 100),
      width: W * (num(view.w) / 100),
      height: H * (num(view.h) / 100)
    };
  }

  function applyZoneForView(view) {
    const z = zoneToPx(view);
    
    if (zoneRect) {
      canvas.remove(zoneRect);
      zoneRect = null;
    }
    
    zoneRect = new fabric.Rect({
      left: z.left, 
      top: z.top, 
      width: z.width, 
      height: z.height,
      fill: 'rgba(35,177,191,0.05)', 
      stroke: '#23b1bf', 
      strokeDashArray: [8, 4],
      selectable: false, 
      evented: false, 
      strokeWidth: 2
    });
    
    canvas.add(zoneRect);
    
    if (baseImg) {
      canvas.sendToBack(baseImg);
    }
    
    userImages.forEach(item => {
      if (item && item.img) {
        item.img.set('clipPath', new fabric.Rect({
          left: z.left, 
          top: z.top, 
          width: z.width, 
          height: z.height, 
          absolutePositioned: true
        }));
      }
    });
    
    if (zoneRect) {
      canvas.bringToFront(zoneRect);
    }
    updateLayerOrder();
  }

  function setupZoneDrop() {
    // Remove any existing overlay
    const oldOverlay = previewWrap.querySelector('.fmb-sb-zone-overlay');
    if (oldOverlay) oldOverlay.remove();

    if (!zoneRect || !canvas) return;

    // Only show overlay when no images
    if (userImages.length === 0) {
      const z = zoneRect.getBoundingRect(true, true);
      
      // Get canvas container position within preview wrap
      const canvasContainer = previewWrap.querySelector('.canvas-container');
      let offsetLeft = 0;
      let offsetTop = 0;
      
      if (canvasContainer) {
        const containerRect = canvasContainer.getBoundingClientRect();
        const previewRect = previewWrap.getBoundingClientRect();
        offsetLeft = containerRect.left - previewRect.left;
        offsetTop = containerRect.top - previewRect.top;
      }
      
      const overlay = document.createElement('div');
      overlay.className = 'fmb-sb-zone-overlay active';
      
      // Add size classes based on zone dimensions
      if (z.width < 80 || z.height < 60) {
        overlay.classList.add('zone-tiny');
      } else if (z.width < 150 || z.height < 100) {
        overlay.classList.add('zone-small');
      }
      
      overlay.style.left = (z.left + offsetLeft) + 'px';
      overlay.style.top = (z.top + offsetTop) + 'px';
      overlay.style.width = z.width + 'px';
      overlay.style.height = z.height + 'px';
      overlay.innerHTML = `
        <div class="fmb-sb-zone-overlay-icon">📷</div>
        <div class="fmb-sb-zone-overlay-text">Přetáhni nebo klikni<br>pro přidání obrázku</div>
      `;

      previewWrap.appendChild(overlay);

      overlay.addEventListener('click', () => {
        document.getElementById('fmb-file-input')?.click();
      });

      ['dragenter', 'dragover'].forEach(evt => {
        overlay.addEventListener(evt, (e) => {
          e.preventDefault();
          e.stopPropagation();
          overlay.classList.add('dragover');
        });
      });

      ['dragleave'].forEach(evt => {
        overlay.addEventListener(evt, (e) => {
          e.preventDefault();
          e.stopPropagation();
          overlay.classList.remove('dragover');
        });
      });

      overlay.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        overlay.classList.remove('dragover');
        const file = e.dataTransfer.files?.[0];
        if (file) readFile(file);
      });
    }
  }

  /* =========================
   *  PŘIDÁNÍ OBRÁZKU
   * ========================= */

  function addUserImageFromSrc(src, name = null) {
    return new Promise((resolve) => {
      fabric.Image.fromURL(src, (img) => {
        if (!zoneRect) {
          console.warn('No zone rect available');
          resolve();
          return;
        }
        
        const z = zoneRect.getBoundingRect(true, true);
        const scale = Math.min(z.width / img.width, z.height / img.height);
        
        const offset = userImages.length * 15;
        
        img.set({
          left: z.left + (z.width - img.width * scale) / 2 + offset,
          top: z.top + (z.height - img.height * scale) / 2 + offset,
          scaleX: scale, 
          scaleY: scale, 
          selectable: true,
          hasBorders: true, 
          cornerColor: '#23b1bf', 
          borderColor: '#23b1bf',
          transparentCorners: false,
          lockScalingFlip: true,
          lockUniScaling: false
        });
        
        img.set('clipPath', new fabric.Rect({
          left: z.left, 
          top: z.top, 
          width: z.width, 
          height: z.height, 
          absolutePositioned: true
        }));

        const imageName = name || `Obrázek ${userImages.length + 1}`;
        
        const newIndex = userImages.length;
        userImages.push({
          img: img,
          name: imageName,
          thumbnail: null,
          originalSrc: src,
          uploadTime: new Date().toLocaleString()
        });
        
        setupImageEvents(img, newIndex);
        
        canvas.add(img);
        setActiveImage(newIndex);
        updateLayerOrder();
        updateImagesList();
        setupZoneDrop();
        saveCurrentState();
        saveToHistory();
        resolve();
      }, { crossOrigin: 'anonymous' });
    });
  }

  function updateImageThumbnail(index) {
    if (index < 0 || index >= userImages.length) return;
    
    const item = userImages[index];
    if (!item || !item.img) return;
    
    try {
      const originalClipPath = item.img.clipPath;
      item.img.set('clipPath', null);
      
      const dataURL = item.img.toDataURL({
        format: 'png',
        quality: 0.6,
        multiplier: 0.2
      });
      
      item.img.set('clipPath', originalClipPath);
      
      item.thumbnail = dataURL;
      
      const thumbEl = $(`.fmb-image-item[data-idx="${index}"] .fmb-image-thumb`);
      if (thumbEl) {
        thumbEl.src = dataURL;
      }
    } catch(e) {
      console.warn('Failed to generate thumbnail:', e);
    }
  }

  function updateLayerOrder() {
    if (!canvas) return;
    
    if (baseImg) {
      canvas.sendToBack(baseImg);
    }
    
    if (zoneRect) {
      canvas.bringToFront(zoneRect);
    }
    
    userImages.forEach((item) => {
      if (item && item.img) {
        canvas.bringToFront(item.img);
      }
    });
    
    canvas.requestRenderAll();
  }

  function removeUserImage(index = null) { 
    if (index === null) {
      userImages.forEach(item => {
        if (item && item.img) {
          canvas.remove(item.img);
        }
      });
      userImages = [];
      activeImageIndex = -1;
    } else if (index >= 0 && index < userImages.length) {
      if (userImages[index] && userImages[index].img) {
        canvas.remove(userImages[index].img);
      }
      userImages.splice(index, 1);
      if (activeImageIndex >= userImages.length) {
        activeImageIndex = userImages.length - 1;
      }
    }
    canvas.requestRenderAll();
    updateImagesList();
    hideSizeFloat();
    hideTextControlsFloat();
    setupZoneDrop();
    saveCurrentState();
  }
  
  function setActiveImage(index) {
    if (index < 0 || index >= userImages.length) return;
    
    activeImageIndex = index;
    
    userImages.forEach((item, i) => {
      if (item && item.img) {
        item.img.set({
          borderColor: i === index ? '#23b1bf' : '#999',
          cornerColor: i === index ? '#23b1bf' : '#999'
        });
      }
    });
    
    if (userImages[index] && userImages[index].img) {
      canvas.setActiveObject(userImages[index].img);
    }
    canvas.requestRenderAll();
    updateImagesList();
    recalcPrice();
    
    const item = userImages[index];
    if (item && item.img) {
      if (item.img.type === 'i-text' || item.img.type === 'text') {
        showTextControlsFloat(item.img);
      } else {
        showSizeFloat(item.img);
      }
    }
  }
  
  function updateImagesList() {
    const container = $('#fmb-images-list');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (userImages.length === 0) {
      container.innerHTML = '<div class="fmb-images-empty">Žádné obrázky</div>';
      sortableInitialized = false;
      return;
    }
    
    userImages.forEach((item, idx) => {
      if (!item) return;
      
      const div = document.createElement('div');
      div.className = 'fmb-image-item' + (idx === activeImageIndex ? ' active' : '');
      div.dataset.idx = idx;
      
      const thumb = document.createElement('img');
      thumb.className = 'fmb-image-thumb';
      if (item.thumbnail) {
        thumb.src = item.thumbnail;
      } else {
        setTimeout(() => updateImageThumbnail(idx), 100);
        thumb.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40"%3E%3Crect fill="%23eee" width="40" height="40"/%3E%3C/svg%3E';
      }
      thumb.alt = item.name || 'Objekt';
      
      const info = document.createElement('div');
      info.className = 'fmb-image-info';
      
      const nameInput = document.createElement('input');
      nameInput.type = 'text';
      nameInput.className = 'fmb-image-name';
      nameInput.value = item.name || '';
      nameInput.placeholder = 'Název';
      nameInput.addEventListener('click', (e) => e.stopPropagation());
      nameInput.addEventListener('input', (e) => {
        if (userImages[idx]) {
          userImages[idx].name = e.target.value;
          saveCurrentState();
        }
      });
      
      const handle = document.createElement('div');
      handle.className = 'fmb-image-handle';
      handle.innerHTML = '⋮⋮';
      handle.title = 'Přetáhni pro změnu pořadí vrstev';
      
      const deleteBtn = document.createElement('button');
      deleteBtn.type = 'button';
      deleteBtn.className = 'fmb-image-delete';
      deleteBtn.innerHTML = '×';
      deleteBtn.title = 'Odstranit';
      deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (confirm(`Odstranit "${item.name}"?`)) {
          removeUserImage(idx);
          recalcPrice();
        }
      });
      
      info.appendChild(nameInput);
      
      div.appendChild(handle);
      div.appendChild(thumb);
      div.appendChild(info);
      div.appendChild(deleteBtn);
      
      div.addEventListener('click', () => {
        setActiveImage(idx);
      });
      
      container.appendChild(div);
    });
    
    if (!sortableInitialized && typeof jQuery !== 'undefined' && jQuery.fn.sortable) {
      jQuery(container).sortable({
        handle: '.fmb-image-handle',
        axis: 'y',
        containment: 'parent',
        tolerance: 'pointer',
        items: '> .fmb-image-item',
        update: function() {
          const newOrder = [];
          jQuery(container).children('.fmb-image-item').each(function() {
            const oldIdx = parseInt(jQuery(this).data('idx'), 10);
            if (!isNaN(oldIdx) && userImages[oldIdx]) {
              newOrder.push(userImages[oldIdx]);
            }
          });
          
          if (newOrder.length === userImages.length) {
            userImages = newOrder;
            updateLayerOrder();
            saveCurrentState();
            saveToHistory();
          }
        }
      });
      sortableInitialized = true;
    } else if (sortableInitialized && typeof jQuery !== 'undefined' && jQuery.fn.sortable) {
      try {
        jQuery(container).sortable('refresh');
      } catch(e) {}
    }
  }

  /* =========================
   *  VIEW SWITCHING
   * ========================= */

  async function loadView(viewIdx) {
    if (!currentMockup) {
      console.error('❌ Nelze načíst view - žádný mockup');
      return;
    }
    
    if (!currentMockup.views || currentMockup.views.length === 0) {
      console.error('❌ Mockup nemá žádné views');
      return;
    }
    
    if (viewIdx < 0 || viewIdx >= currentMockup.views.length) {
      console.warn(`⚠️ Neplatný view index ${viewIdx}, používám 0`);
      viewIdx = 0;
    }
    
    console.log(`🔄 Načítám View ${viewIdx} pro mockup ${currentMockup.id}...`);
    
    if (activeViewIdx >= 0 && activeViewIdx < currentMockup.views.length && userImages.length > 0) {
      console.log(`💾 Ukládám stav před přepnutím: View ${activeViewIdx}`);
      saveCurrentState();
    }
    
    clearCanvas();
    
    activeViewIdx = viewIdx;
    setActiveViewBtn();
    
    ensureCanvas();
    
    const view = currentMockup.views[viewIdx] || {};
    const baseURL = resolveBaseForColor(view);

    if (!baseURL) {
      console.error('❌ Není dostupný základní obrázek pro view', viewIdx);
      return;
    }

    await new Promise((resolve) => {
      fabric.Image.fromURL(baseURL, (img) => {
        if (!img) {
          console.error('❌ Nepodařilo se načíst základní obrázek');
          resolve();
          return;
        }
        baseImg = img;
        baseImg.set({ selectable: false, evented: false });
        canvas.add(baseImg); 
        fitCanvasToBase(baseImg);
        canvas.sendToBack(baseImg); 
        resolve();
      }, { crossOrigin: 'anonymous' });
    });

    applyZoneForView(view);
    
    const savedState = loadState(currentMockup.id, viewIdx);
    if (savedState) {
      await restoreObjects(savedState);
    } else {
      updateImagesList();
      setupZoneDrop();
    }
    
    const onResize = () => {
      if (!baseImg) return;
      fitCanvasToBase(baseImg);
      applyZoneForView(view);
      setupZoneDrop();
      canvas.requestRenderAll();
    };
    window.removeEventListener('resize', onResize);
    window.addEventListener('resize', onResize, { passive: true });
    
    recalcPrice();
    saveProductSettings(currentMockup.id);
    
    console.log(`✅ View ${viewIdx} načten`);
  }

  async function switchToView(idx) {
    if (!currentMockup) return;
    
    if (idx === activeViewIdx && currentMockup.id === lastLoadedMockupId) {
      console.log('⏭️ Stejný view, přeskakuji');
      return;
    }
    
    await loadView(idx);
    lastLoadedMockupId = currentMockup.id;
  }

  /* =========================
   *  MOCKUP SWITCHING
   * ========================= */

  async function selectMockup(m) {
    if (currentMockup && currentMockup.id === m.id) {
      console.log('⏭️ Stejný mockup, přeskakuji');
      return;
    }
    
    console.log(`🎯 Přepínám na mockup: ${m.title} (ID: ${m.id})`);
    
    if (currentMockup && activeViewIdx >= 0) {
      console.log(`💾 Ukládám stav starého mockupu ${currentMockup.id}`);
      if (userImages.length > 0) {
        saveCurrentState();
      }
      saveProductSettings(currentMockup.id);
    }
    
    clearCanvas();
    
    currentMockup = m;
    
    mergeDefaultConfig();
    
    const savedSettings = loadProductSettings(m.id);
    
    if (savedSettings && savedSettings.mockupId === m.id) {
      activeColorIdx = savedSettings.colorIdx || 0;
      activeViewIdx = savedSettings.viewIdx || 0;
      state.qty = savedSettings.qty || 1;
      state.design = savedSettings.design || 'print';
      state.variant = savedSettings.variant;
      state.size = savedSettings.size;
    } else {
      activeColorIdx = 0;
      activeViewIdx = 0;
      state.qty = 1;
      state.design = 'print';
      state.variant = null;
      state.size = null;
    }
    
    if (titleEl) titleEl.textContent = m.title || 'Produkt';
    if (descEl) descEl.textContent = m.desc || '';
    
    if (qtyEl) qtyEl.value = state.qty;
    
    $$('.fmb-pill', designType).forEach(p => {
      p.classList.toggle('active', p.dataset.type === state.design);
    });
    
    buildPillsWithSaved(variantsWrap, m.variants || [], 'variant', state.variant);
    buildPillsWithSaved(sizesWrap, m.sizes || [], 'size', state.size);
    buildSwatchesWithSaved(m.colors2 || [], m.colors || [], activeColorIdx);
    buildViews(m.views || []);
    
    if (m.views && m.views.length > 0) {
      if (activeViewIdx >= m.views.length) {
        activeViewIdx = 0;
      }
      await loadView(activeViewIdx);
      lastLoadedMockupId = m.id;
    }
    
    recalcPrice();
    
    console.log(`✅ Mockup ${m.title} načten`);
  }

  /* =========================
   *  UI BUILDERS
   * ========================= */

  function buildViews(views) {
    viewsWrap.innerHTML = '';
    
    // Add title
    const title = document.createElement('div');
    title.className = 'fmb-sb-views-title';
    title.textContent = 'Pohledy';
    viewsWrap.appendChild(title);
    
    if (!views || !views.length) return;
    
    views.forEach((v, idx) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'fmb-viewthumb';
      if (idx === activeViewIdx) {
        btn.classList.add('active');
      }
      btn.dataset.idx = idx;
      
      const img = document.createElement('img');
      let imgSrc = '';
      if (currentMockup && currentMockup.colors2 && currentMockup.colors2[activeColorIdx]) {
        imgSrc = currentMockup.colors2[activeColorIdx].per_view[idx] || '';
      }
      img.src = imgSrc || currentMockup.image || '';
      img.alt = v.label || `View ${idx + 1}`;
      
      btn.appendChild(img);
      btn.addEventListener('click', () => switchToView(idx));
      viewsWrap.appendChild(btn);
    });
  }
  
  function setActiveViewBtn() {
    $$('.fmb-viewthumb', viewsWrap).forEach(el => {
      el.classList.toggle('active', parseInt(el.dataset.idx, 10) === activeViewIdx);
    });
  }

  function resolveBaseForColor(view) {
    if (activeColorIdx >= 0 && Array.isArray(currentMockup.colors2) && currentMockup.colors2[activeColorIdx]) {
      const item = currentMockup.colors2[activeColorIdx];
      if (Array.isArray(item.per_view) && item.per_view[activeViewIdx]) {
        return item.per_view[activeViewIdx];
      }
    }
    
    if (Array.isArray(currentMockup.colors2) && currentMockup.colors2[0] && 
        Array.isArray(currentMockup.colors2[0].per_view) && 
        currentMockup.colors2[0].per_view[activeViewIdx]) {
      return currentMockup.colors2[0].per_view[activeViewIdx];
    }
    
    return currentMockup.image || '';
  }

  function buildSwatchesWithSaved(colors2, legacyColors, savedColorIdx) {
    colorsWrap.innerHTML = '';
    
    if (Array.isArray(colors2) && colors2.length) {
      colors2.forEach((c, i) => {
        const b = document.createElement('button');
        b.type = 'button'; 
        b.className = 'fmb-swatch';
        if (i === savedColorIdx) b.classList.add('active');
        b.dataset.idx = i;
        b.style.background = c.hex || '#ffffff';
        b.title = c.label || c.hex;
        b.addEventListener('click', () => handleColorClick(i));
        colorsWrap.appendChild(b);
      });
    } else if (Array.isArray(legacyColors) && legacyColors.length) {
      legacyColors.forEach((hex, i) => {
        const b = document.createElement('button');
        b.type = 'button'; 
        b.className = 'fmb-swatch';
        if (i === savedColorIdx) b.classList.add('active');
        b.dataset.idx = i;
        b.style.background = hex || '#ffffff';
        b.addEventListener('click', () => handleColorClick(i));
        colorsWrap.appendChild(b);
      });
    }
  }

  async function handleColorClick(colorIdx) {
    if (colorIdx === activeColorIdx) return;
    
    if (userImages.length > 0) {
      saveCurrentState();
    }
    
    activeColorIdx = colorIdx;
    
    $$('.fmb-swatch', colorsWrap).forEach(s => s.classList.remove('active'));
    const activeBtn = $(`.fmb-swatch[data-idx="${colorIdx}"]`, colorsWrap);
    if (activeBtn) activeBtn.classList.add('active');
    
    updateViewThumbnails(colorIdx);
    
    const currentViewIdx = activeViewIdx;
    await loadView(currentViewIdx);
    
    saveProductSettings(currentMockup.id);
  }

  function updateViewThumbnails(colorIdx) {
    if (!currentMockup || !currentMockup.colors2 || !currentMockup.colors2[colorIdx]) return;
    
    const color = currentMockup.colors2[colorIdx];
    
    $$('.fmb-viewthumb', viewsWrap).forEach((thumb, viewIdx) => {
      const img = thumb.querySelector('img');
      if (img && color.per_view && color.per_view[viewIdx]) {
        img.src = color.per_view[viewIdx] || currentMockup.image || '';
      }
    });
  }

  function buildPillsWithSaved(wrap, arr, key, savedValue) {
    wrap.innerHTML = '';
    let hasActive = false;
    
    (arr || []).forEach(val => {
      const b = document.createElement('button');
      b.type = 'button'; 
      b.className = 'fmb-pill'; 
      b.textContent = val;
      
      if (savedValue && val === savedValue) {
        b.classList.add('active');
        state[key] = val;
        hasActive = true;
      }
      
      b.addEventListener('click', () => {
        $$('.fmb-pill', wrap).forEach(p => p.classList.remove('active'));
        b.classList.add('active'); 
        state[key] = val;
        saveProductSettings(currentMockup?.id);
      });
      wrap.appendChild(b);
    });
    
    if (!hasActive && arr && arr.length > 0) {
      const first = $('.fmb-pill', wrap);
      if (first) {
        first.classList.add('active');
        state[key] = arr[0];
      }
    }
  }

  function recalcPrice() {
    const design = state.design === 'emb' ? 'emb' : 'print';
    const qty = parseInt(qtyEl.value || '1', 10) || 1;
    
    if (!currentMockup) {
      priceEl.textContent = '—';
      return;
    }

    const mode = currentMockup.price_mode || 'fixed';
    const config = currentMockup.price_config || {};

    let totalPrice = 0;

    if (currentMockup.views && currentMockup.views.length > 0) {
      currentMockup.views.forEach((view, vIdx) => {
        const savedState = loadState(currentMockup.id, vIdx);
        
        let statesArr = null;
        let savedArea = null;
        
        if (savedState && savedState.images) {
          statesArr = savedState.images;
          savedArea = savedState.totalArea;
        }
        
        if (vIdx === activeViewIdx && userImages.length > 0) {
          statesArr = userImages.map(item => ({ type: item.img?.type }));
          savedArea = null;
        }
        
        if (!statesArr || !Array.isArray(statesArr) || statesArr.length === 0) {
          return;
        }

        let viewAreaCm2 = 0;

        if (vIdx === activeViewIdx && userImages.length > 0) {
          userImages.forEach(userImg => {
            if (userImg && userImg.img && zoneRect && (userImg.img.type !== 'i-text' && userImg.img.type !== 'text')) {
              const size = getImageSizeInCm(userImg.img, zoneRect, currentMockup);
              viewAreaCm2 += size.area;
            }
          });
        } else if (savedArea !== null && savedArea > 0) {
          viewAreaCm2 = savedArea;
        } else {
          const avgArea = 425;
          const imageCount = statesArr.filter(s => s.type !== 'i-text' && s.type !== 'text').length;
          viewAreaCm2 = imageCount * avgArea;
        }

        let viewPrice = 0;

        try {
          if (mode === 'fixed') {
            const designPrice = config[mode]?.[design] || (design === 'emb' ? 200 : 100);
            viewPrice = designPrice;
          } else if (mode === 'area') {
            const areaConfig = config[mode]?.[design] || {};
            const base = parseFloat(areaConfig.base) || 100;
            const perCm2 = parseFloat(areaConfig.per_cm2) || 5;
            viewPrice = base + (viewAreaCm2 * perCm2);
          } else if (mode === 'formula') {
            const tokens = config[mode]?.[design]?.tokens || [];
            let expr = '';
            
            tokens.forEach(t => {
              if (t.type === 'var') {
                if (t.val === 'base') {
                  const baseVal = config[mode]?.[design]?.base || 100;
                  expr += String(baseVal);
                } else if (t.val === 'qty') {
                  expr += String(1);
                } else if (t.val === 'area' || t.val === 'area_cm2') {
                  expr += String(viewAreaCm2 > 0 ? Math.round(viewAreaCm2 * 100) / 100 : 0);
                } else if (t.val === 'option_mult') {
                  expr += String(design === 'emb' ? 1.5 : 1.0);
                }
              } else if (t.type === 'num') {
                expr += String(t.val);
              } else if (t.type === 'op' && /^[+\-*/()]$/.test(t.val)) {
                expr += t.val;
              }
            });

            if (expr && /^[0-9+*\-\/().\s]+$/.test(expr)) {
              try {
                viewPrice = Function('"use strict"; return (' + expr + ');')();
              } catch(e) {
                viewPrice = 0;
              }
            } else {
              const base = config[mode]?.[design]?.base || 100;
              viewPrice = base;
            }
          }
        } catch(e) {
          viewPrice = 100;
        }

        totalPrice += viewPrice;
      });
    }

    const finalPrice = totalPrice * qty;
    priceEl.textContent = Math.max(0, Math.round(finalPrice)) + ' Kč';
  }

  function readFile(file) {
    if (!currentMockup) return;
    
    const r = new FileReader();
    r.onload = async e => { 
      const fileData = e.target.result;
      const fileName = file.name.replace(/\.[^/.]+$/, '');
      await addUserImageFromSrc(fileData, fileName); 
      recalcPrice(); 
    };
    r.readAsDataURL(file);
  }

  function addTextToCanvas() {
    if (!zoneRect || !currentMockup) return;

    const z = zoneRect.getBoundingRect(true, true);

    const text = new fabric.IText('Klikni pro úpravu', {
      left: z.left + z.width / 2,
      top: z.top + z.height / 2,
      fontFamily: 'Arial',
      fontSize: 36,
      fill: '#000000',
      originX: 'center',
      originY: 'center',
      selectable: true,
      editable: true,
      lockScalingFlip: true,
      lockUniScaling: false
    });

    text.set('clipPath', new fabric.Rect({
      left: z.left,
      top: z.top,
      width: z.width,
      height: z.height,
      absolutePositioned: true
    }));

    const newIndex = userImages.length;
    userImages.push({
      img: text,
      name: `Text: Klikni pro úpravu`,
      thumbnail: null,
      uploadTime: new Date().toLocaleString()
    });

    setupImageEvents(text, newIndex);
    canvas.add(text);
    setActiveImage(newIndex);
    updateLayerOrder();
    updateImagesList();
    setupZoneDrop();
    saveCurrentState();
    saveToHistory();
    
    canvas.setActiveObject(text);
    text.enterEditing();
    text.selectAll();
  }

  function bindTools() {
    const mk = (t) => { 
      const b = document.createElement('button'); 
      b.type = 'button'; 
      b.className = 'fmb-tool'; 
      b.textContent = t; 
      return b; 
    };
    
    const historyControls = document.createElement('div');
    historyControls.className = 'fmb-history-controls';
    historyControls.innerHTML = `
      <button type="button" class="fmb-history-btn" id="fmb-undo" disabled>
        <span>↶</span> Zpět
      </button>
      <button type="button" class="fmb-history-btn" id="fmb-redo" disabled>
        <span>↷</span> Vpřed
      </button>
    `;

    const center = mk('Vycentrovat');
    const fit = mk('Na šířku');
    const rotM = mk('↶ 15°');
    const rotP = mk('↷ 15°');
    const delAll = mk('Smazat vše');
    
    toolsHost.innerHTML = '';
    
    toolsHost.appendChild(historyControls);
    
    const uploadSection = document.createElement('div');
    uploadSection.className = 'fmb-upload-section';
    
    const uploadDropZone = document.createElement('div');
    uploadDropZone.className = 'fmb-upload-drop-zone';
    uploadDropZone.innerHTML = '📷 Přetáhni nebo klikni<br>pro přidání obrázku';
    
    uploadDropZone.addEventListener('click', () => {
      document.getElementById('fmb-file-input')?.click();
    });

    ['dragenter', 'dragover'].forEach(evt => {
      uploadDropZone.addEventListener(evt, (e) => {
        e.preventDefault();
        e.stopPropagation();
        uploadDropZone.classList.add('dragover');
      });
    });

    ['dragleave'].forEach(evt => {
      uploadDropZone.addEventListener(evt, (e) => {
        e.preventDefault();
        e.stopPropagation();
        uploadDropZone.classList.remove('dragover');
      });
    });

    uploadDropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      e.stopPropagation();
      uploadDropZone.classList.remove('dragover');
      const file = e.dataTransfer.files?.[0];
      if (file) readFile(file);
    });
    
    uploadSection.appendChild(uploadDropZone);
    toolsHost.appendChild(uploadSection);
    
    const addTextBtn = document.createElement('button');
    addTextBtn.type = 'button';
    addTextBtn.className = 'fmb-add-text-btn';
    addTextBtn.id = 'fmb-add-text';
    addTextBtn.innerHTML = '✨ Přidat text';
    toolsHost.appendChild(addTextBtn);
    
    const imagesList = document.createElement('div');
    imagesList.id = 'fmb-images-list';
    imagesList.className = 'fmb-images-list';
    toolsHost.appendChild(imagesList);
    
    const toolsRow = document.createElement('div');
    toolsRow.className = 'fmb-tools-row';
    [center, fit, rotM, rotP, delAll].forEach(b => toolsRow.appendChild(b));
    toolsHost.appendChild(toolsRow);

    $('#fmb-undo')?.addEventListener('click', undo);
    $('#fmb-redo')?.addEventListener('click', redo);
    $('#fmb-add-text')?.addEventListener('click', addTextToCanvas);

    const fileInput = document.getElementById('fmb-file-input');
    if (fileInput) {
      fileInput.addEventListener('change', (e) => { 
        const f = e.target.files[0]; 
        if (f) readFile(f); 
        e.target.value = ''; 
      });
    }

    center.addEventListener('click', () => {
      const item = userImages[activeImageIndex];
      if (!item || !item.img || !zoneRect) return;
      const z = zoneRect.getBoundingRect(true, true);
      const w = item.img.getScaledWidth(), h = item.img.getScaledHeight();
      item.img.set({ left: z.left + (z.width - w) / 2, top: z.top + (z.height - h) / 2 });
      canvas.requestRenderAll(); 
      saveCurrentState(); 
      recalcPrice(); 
      saveToHistory();
    });
    
    fit.addEventListener('click', () => {
      const item = userImages[activeImageIndex];
      if (!item || !item.img || !zoneRect) return;
      if (item.img.type === 'i-text' || item.img.type === 'text') return;
      const z = zoneRect.getBoundingRect(true, true);
      const s = z.width / item.img.width;
      item.img.set({ scaleX: s, scaleY: s, left: z.left, top: z.top });
      canvas.requestRenderAll(); 
      recalcPrice(); 
      saveCurrentState(); 
      saveToHistory();
    });
    
    rotM.addEventListener('click', () => {
      const item = userImages[activeImageIndex];
      if (item && item.img) { 
        item.img.rotate((item.img.angle || 0) - 15); 
        canvas.requestRenderAll(); 
        saveCurrentState(); 
        recalcPrice(); 
        saveToHistory();
      }
    });
    
    rotP.addEventListener('click', () => {
      const item = userImages[activeImageIndex];
      if (item && item.img) { 
        item.img.rotate((item.img.angle || 0) + 15); 
        canvas.requestRenderAll(); 
        saveCurrentState(); 
        recalcPrice(); 
        saveToHistory();
      }
    });
    
    delAll.addEventListener('click', () => {
      if (userImages.length === 0) return;
      if (confirm(`Odstranit všechny objekty (${userImages.length})?`)) {
        removeUserImage();
        recalcPrice();
        saveCurrentState();
      }
    });

    $$('.fmb-stepper-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        let v = parseInt(qtyEl.value || '1', 10) || 1;
        v = btn.dataset.op === '+' ? v + 1 : Math.max(1, v - 1);
        qtyEl.value = v; 
        state.qty = v; 
        recalcPrice();
        saveProductSettings(currentMockup?.id);
      });
    });
    
    qtyEl.addEventListener('input', () => { 
      let v = parseInt(qtyEl.value || '1', 10) || 1; 
      if (v < 1) v = 1; 
      qtyEl.value = v; 
      state.qty = v; 
      recalcPrice();
      saveProductSettings(currentMockup?.id);
    });

    designType?.addEventListener('click', (e) => {
      const btn = e.target.closest('.fmb-pill'); 
      if (!btn) return;
      $$('.fmb-pill', designType).forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      state.design = btn.dataset.type === 'emb' ? 'emb' : 'print';
      recalcPrice();
      saveProductSettings(currentMockup?.id);
    });
    
    updateImagesList();
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Delete' || e.key === 'Backspace') {
      const activeElement = document.activeElement;
      
      if (activeElement && (
        activeElement.tagName === 'INPUT' || 
        activeElement.tagName === 'TEXTAREA' ||
        activeElement.isContentEditable
      )) {
        return;
      }

      if (canvas && canvas.getActiveObject() && canvas.getActiveObject().isEditing) {
        return;
      }

      if (activeImageIndex >= 0 && activeImageIndex < userImages.length) {
        e.preventDefault();
        const item = userImages[activeImageIndex];
        if (item && confirm(`Odstranit "${item.name}"?`)) {
          removeUserImage(activeImageIndex);
          recalcPrice();
          saveCurrentState();
        }
      }
    }
  });

  async function captureAllViews() {
    if (!currentMockup || !currentMockup.views || !currentMockup.views.length) {
      return [];
    }
    
    const savedViewIdx = activeViewIdx;
    const images = [];
    
    try {
      for (let i = 0; i < currentMockup.views.length; i++) {
        await loadView(i);
        await new Promise(resolve => setTimeout(resolve, 150));
        
        const zoneVisible = zoneRect ? zoneRect.visible : false;
        if (zoneRect) zoneRect.set({ visible: false });
        canvas.requestRenderAll();
        
        const dataURL = canvas.toDataURL({
          format: 'jpeg',
          quality: 0.8,
          multiplier: 1
        });
        
        images.push({
          data: dataURL,
          name: currentMockup.views[i].label || `View ${i + 1}`
        });
        
        if (zoneRect) zoneRect.set({ visible: zoneVisible });
        canvas.requestRenderAll();
      }
      
      await loadView(savedViewIdx);
      return images;
    } catch(err) {
      console.error('Capture error:', err);
      await loadView(savedViewIdx);
      return [];
    }
  }

  function mergeDefaultConfig() {
    if (!currentMockup || !currentMockup.price_config) {
      return;
    }

    const DEFAULT_CONFIG = {
      'fixed': {
        'print': 100,
        'emb': 200
      },
      'area': {
        'print': { base: 200, per_cm2: 5, min_cm2: 50, max_cm2: 5000 },
        'emb': { base: 500, per_cm2: 10, min_cm2: 50, max_cm2: 5000 }
      },
      'formula': {
        'print': { tokens: [] },
        'emb': { tokens: [] }
      }
    };

    const config = currentMockup.price_config;

    for (const mode in DEFAULT_CONFIG) {
      if (!config[mode]) {
        config[mode] = JSON.parse(JSON.stringify(DEFAULT_CONFIG[mode]));
      } else {
        for (const type in DEFAULT_CONFIG[mode]) {
          if (!config[mode][type]) {
            config[mode][type] = JSON.parse(JSON.stringify(DEFAULT_CONFIG[mode][type]));
          } else if (typeof config[mode][type] === 'object' && config[mode][type] !== null) {
            const defaults = DEFAULT_CONFIG[mode][type];
            if (typeof defaults === 'object') {
              for (const key in defaults) {
                if (!(key in config[mode][type])) {
                  config[mode][type][key] = defaults[key];
                }
              }
            }
          }
        }
      }
    }
  }

  function loadMockups() {
    mockupList.innerHTML = '<div>Načítám mockupy…</div>';
    ajax('fmb_list_mockups').then(resp => {
      if (!resp?.success) { 
        mockupList.innerHTML = '<div>Chyba načítání.</div>'; 
        return; 
      }
      const items = resp.data || []; 
      if (!items.length) { 
        mockupList.innerHTML = '<div>Žádné mockupy.</div>'; 
        return; 
      }
      mockupList.innerHTML = '';
      items.forEach(m => {
        const card = document.createElement('div'); 
        card.className = 'fmb-sb-card';
        const img = document.createElement('img'); 
        img.src = m.image || ''; 
        img.alt = m.title || 'Mockup';
        const ttl = document.createElement('div'); 
        ttl.className = 'title'; 
        ttl.textContent = m.title || 'Mockup';
        card.append(img, ttl);
        card.addEventListener('click', () => selectMockup(m));
        mockupList.appendChild(card);
      });
      
      const urlParams = getUrlParams();
      const savedSettings = loadLastMockupSettings();
      const selectedMockupId = sessionStorage.getItem('fmb_selected_mockup_id');
      
      let mockupToLoad = null;
      
      if (urlParams.mockupId) {
        mockupToLoad = items.find(m => m.id == urlParams.mockupId);
        console.log('📍 Loading mockup from URL param:', urlParams.mockupId);
      } else if (selectedMockupId) {
        mockupToLoad = items.find(m => m.id == selectedMockupId);
      } else if (savedSettings && savedSettings.mockupId) {
        mockupToLoad = items.find(m => m.id == savedSettings.mockupId);
      }
      
      if (!mockupToLoad && items[0]) {
        mockupToLoad = items[0];
      }
      
      if (mockupToLoad) {
        selectMockup(mockupToLoad).then(() => {
          setTimeout(() => {
            preloadDesignImage();
          }, 300);
        });
      }
    });
  }

  function preloadDesignImage() {
    const image = sessionStorage.getItem('fmb_design_image');
    const imageName = sessionStorage.getItem('fmb_design_name') || 'Váš design';
    const selectedMockupId = sessionStorage.getItem('fmb_selected_mockup_id');

    if (!image || !selectedMockupId) {
      return;
    }

    if (!currentMockup || currentMockup.id != selectedMockupId) {
      return;
    }

    if (!zoneRect) {
      return;
    }

    addUserImageFromSrc(image, imageName)
      .then(() => {
        recalcPrice();
        saveCurrentState();
        
        sessionStorage.removeItem('fmb_design_image');
        sessionStorage.removeItem('fmb_design_name');
        sessionStorage.removeItem('fmb_selected_mockup_id');
        sessionStorage.removeItem('fmb_selected_mockup_title');
      })
      .catch(err => {
        console.error('Failed to load design:', err);
      });
  }

  window.addEventListener('beforeunload', () => {
    if (currentMockup && activeViewIdx >= 0) {
      saveCurrentState();
      saveProductSettings(currentMockup.id);
    }
  });

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden' && currentMockup && activeViewIdx >= 0) {
      saveCurrentState();
      saveProductSettings(currentMockup.id);
    }
  });

  function init() {
    loadGlobalStateFromStorage();
    
    ensureCanvas(); 
    bindTools();
    
    updateCartBadge();
    
    $('#fmb-add-to-cart')?.addEventListener('click', addToCart);
    
    loadMockups();
    
    console.log('🎨 FMB Builder v4.0 initialized with larger UI');
  }
  
  document.addEventListener('DOMContentLoaded', init);
})();