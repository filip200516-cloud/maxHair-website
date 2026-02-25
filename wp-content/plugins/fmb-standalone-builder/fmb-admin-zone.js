// fmb-admin-zone.js - OPRAVENO

(function ($) {
  'use strict';

  console.log('🚀 FMB Admin Zone JS loading...');
  console.log('FMB_ADMIN data:', FMB_ADMIN);

  /* =========================
   *  Helpers
   * ========================= */
  const round1 = (v) => Math.round((parseFloat(v || 0) + Number.EPSILON) * 10) / 10;

  function openMedia() {
    return new Promise((resolve) => {
      const frame = wp.media({
        title: FMB_ADMIN?.i18n?.chooseImage || 'Vybrat obrázek',
        button: { text: FMB_ADMIN?.i18n?.chooseImage || 'Vybrat obrázek' },
        multiple: false,
        library: { type: ['image'] }
      });
      frame.on('select', function () {
        const att = frame.state().get('selection').first().toJSON();
        resolve({ id: att.id, url: att.url });
      });
      frame.open();
    });
  }

  /* =========================
   *  Unified Price System
   * ========================= */
  
  const DEFAULT_PRICE_CONFIG = {
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

  let COLORS = Array.isArray(FMB_ADMIN?.colors2) ? JSON.parse(JSON.stringify(FMB_ADMIN.colors2)) : [];
  let PRICE_MODE = FMB_ADMIN?.priceMode || 'fixed';
  
  let PRICE_CONFIG = JSON.parse(JSON.stringify(FMB_ADMIN?.priceConfig || {}));
  
  // HLUBOKÉ SLOUČENÍ S VÝCHOZÍMI HODNOTAMI
  for (const mode in DEFAULT_PRICE_CONFIG) {
    if (!PRICE_CONFIG[mode]) {
      PRICE_CONFIG[mode] = JSON.parse(JSON.stringify(DEFAULT_PRICE_CONFIG[mode]));
    } else {
      for (const type in DEFAULT_PRICE_CONFIG[mode]) {
        if (!PRICE_CONFIG[mode][type]) {
          PRICE_CONFIG[mode][type] = JSON.parse(JSON.stringify(DEFAULT_PRICE_CONFIG[mode][type]));
        } else if (typeof PRICE_CONFIG[mode][type] === 'object' && PRICE_CONFIG[mode][type] !== null) {
          const defaults = DEFAULT_PRICE_CONFIG[mode][type];
          if (typeof defaults === 'object') {
            for (const key in defaults) {
              if (!(key in PRICE_CONFIG[mode][type])) {
                PRICE_CONFIG[mode][type][key] = defaults[key];
              }
            }
          }
        }
      }
    }
  }

  console.log('📊 Initialized PRICE_CONFIG:', PRICE_CONFIG);

  const $colorsHidden = $('#fmb_colors2_field');
  const $priceModeField = $('#fmb_price_mode_field');
  const $priceConfigField = $('#fmb_price_config_field');

  function persistColors() {
    try {
      $colorsHidden.val(JSON.stringify(COLORS || []));
      console.log('💾 Saved colors:', COLORS);
    } catch(e) {
      console.error('Failed to save colors:', e);
    }
  }

  function persistPricing() {
    try {
      $priceModeField.val(PRICE_MODE);
      const configJson = JSON.stringify(PRICE_CONFIG || {});
      $priceConfigField.val(configJson);
      console.log('💾 Saved pricing mode:', PRICE_MODE);
      console.log('💾 Saved pricing config:', configJson);
    } catch(e) {
      console.error('Failed to save pricing:', e);
    }
  }

  /* =========================
   *  Price Mode Tab Switching
   * ========================= */
  $('.fmb-price-mode-btn').on('click', function() {
    const newMode = $(this).data('mode');
    console.log('🔄 Switching price mode to:', newMode);
    
    if (!PRICE_CONFIG[newMode]) {
      console.log('⚙️ Initializing mode:', newMode);
      PRICE_CONFIG[newMode] = JSON.parse(JSON.stringify(DEFAULT_PRICE_CONFIG[newMode] || {}));
    }
    
    PRICE_MODE = newMode;
    
    $('.fmb-price-mode-btn').removeAttr('style');
    $(this).css('background', '#23b1bf').css('color', '#fff');
    
    $('.fmb-price-content').hide();
    $(`.fmb-price-content[data-mode="${newMode}"]`).show();
    
    if (newMode === 'area') {
      updateAreaPreview();
    } else if (newMode === 'fixed') {
      updateFixedPreview();
    } else if (newMode === 'formula') {
      renderFormulaMini();
    }
    
    persistPricing();
  });

  /* =========================
   *  Price Inputs
   * ========================= */
  $(document).on('input', '.fmb-price-input', function() {
    const type = $(this).data('type');
    const key = $(this).data('key');
    const value = $(this).val();

    console.log(`📝 Price input changed: ${PRICE_MODE}/${type}/${key} = ${value}`);

    if (!PRICE_CONFIG[PRICE_MODE]) {
      PRICE_CONFIG[PRICE_MODE] = {};
    }

    if (PRICE_MODE === 'fixed') {
      PRICE_CONFIG[PRICE_MODE][type] = parseInt(value) || 0;
      updateFixedPreview();
    } 
    else if (PRICE_MODE === 'area') {
      if (!PRICE_CONFIG[PRICE_MODE][type] || typeof PRICE_CONFIG[PRICE_MODE][type] !== 'object') {
        PRICE_CONFIG[PRICE_MODE][type] = JSON.parse(JSON.stringify(DEFAULT_PRICE_CONFIG['area'][type]));
      }
      
      if (['base', 'per_cm2', 'min_cm2', 'max_cm2'].includes(key)) {
        const numValue = ['per_cm2', 'min_cm2', 'max_cm2'].includes(key) 
          ? parseFloat(value) || 0 
          : parseInt(value) || 0;
        
        PRICE_CONFIG[PRICE_MODE][type][key] = numValue;
      }
      updateAreaPreview();
    }

    persistPricing();
  });

  function updateFixedPreview() {
    const printPrice = PRICE_CONFIG[PRICE_MODE]?.print ?? DEFAULT_PRICE_CONFIG['fixed']['print'];
    const embPrice = PRICE_CONFIG[PRICE_MODE]?.emb ?? DEFAULT_PRICE_CONFIG['fixed']['emb'];
    
    console.log('🎯 Fixed preview:', { printPrice, embPrice });
    
    $('.fmb-price-preview-fixed-print').text(printPrice);
    $('.fmb-price-preview-fixed-emb').text(embPrice);
  }

  function updateAreaPreview() {
    const printBase = PRICE_CONFIG[PRICE_MODE]?.print?.base ?? DEFAULT_PRICE_CONFIG['area']['print']['base'];
    const printPer = PRICE_CONFIG[PRICE_MODE]?.print?.per_cm2 ?? DEFAULT_PRICE_CONFIG['area']['print']['per_cm2'];
    const embBase = PRICE_CONFIG[PRICE_MODE]?.emb?.base ?? DEFAULT_PRICE_CONFIG['area']['emb']['base'];
    const embPer = PRICE_CONFIG[PRICE_MODE]?.emb?.per_cm2 ?? DEFAULT_PRICE_CONFIG['area']['emb']['per_cm2'];
    
    const exampleArea = 425;
    const printPrice = Math.round(printBase + (exampleArea * printPer));
    const embPrice = Math.round(embBase + (exampleArea * embPer));
    
    console.log('🎯 Area preview:', { printPrice, embPrice, printBase, printPer, embBase, embPer });
    
    $('.fmb-price-preview-area-print').html(`<strong>${printPrice} Kč</strong>`);
    $('.fmb-price-preview-area-emb').html(`<strong>${embPrice} Kč</strong>`);
  }

  /* =========================
   *  Mini Formula Builder
   * ========================= */
  function renderFormulaMini() {
    console.log('🧮 Rendering formula mini builders...');
    
    $('.fmb-formula-mini').each(function() {
      const $container = $(this);
      const type = $container.data('type');
      
      if (!PRICE_CONFIG[PRICE_MODE]) {
        PRICE_CONFIG[PRICE_MODE] = {};
      }
      if (!PRICE_CONFIG[PRICE_MODE][type]) {
        PRICE_CONFIG[PRICE_MODE][type] = { tokens: [] };
      }
      
      const tokens = PRICE_CONFIG[PRICE_MODE][type].tokens || [];
      
      const $tokensContainer = $container.find('.fmb-formula-mini-tokens');
      if ($tokensContainer.hasClass('ui-sortable')) {
        $tokensContainer.sortable('destroy');
      }
      
      $container.html(`
        <div class="fmb-formula-mini-tokens" data-type="${type}"></div>
        <div class="fmb-formula-mini-toolbar">
          <button type="button" class="button button-small fmb-formula-add-var" data-type="${type}">+ Proměnná</button>
          <button type="button" class="button button-small fmb-formula-add-op" data-type="${type}">+ Operátor</button>
          <button type="button" class="button button-small fmb-formula-add-num" data-type="${type}">+ Číslo</button>
        </div>
        <div class="fmb-formula-preview-mini">
          <strong>Výraz:</strong> <code class="formula-expr-mini">—</code><br>
          <strong>Náhled:</strong> <span class="formula-result-mini">—</span>
        </div>
      `);
      
      const $newTokensContainer = $container.find('.fmb-formula-mini-tokens');
      
      tokens.forEach((token) => {
        appendTokenMini($newTokensContainer, token, type);
      });
      
      $newTokensContainer.sortable({
        items: '> .fmb-formula-token-mini',
        update: () => {
          const newOrder = [];
          $newTokensContainer.children().each(function() {
            const data = $(this).data('token');
            if (data) newOrder.push(data);
          });
          PRICE_CONFIG[PRICE_MODE][type].tokens = newOrder;
          updateFormulaPreviewMini($container, type);
          persistPricing();
        }
      });
      
      updateFormulaPreviewMini($container, type);
    });
  }

  function appendTokenMini($container, token, type) {
    const $token = $('<div class="fmb-formula-token-mini"></div>');
    $token.data('token', token);
    
    if (token.type === 'var') {
      $token.addClass('fmb-token-var-mini');
      
      const $select = $('<select class="fmb-token-select-mini"></select>');
      $select.append('<option value="base">Base (Cena)</option>');
      $select.append('<option value="qty">Qty (Počet)</option>');
      $select.append('<option value="area">Area (Plocha %)</option>');
      $select.append('<option value="area_cm2">Area cm² (Plocha cm²)</option>');
      $select.append('<option value="option_mult">Mult (Násobič)</option>');
      $select.val(token.val);
      
      $select.on('change', function() {
        token.val = $(this).val();
        const $formulaContainer = $(this).closest('.fmb-formula-mini');
        updateFormulaPreviewMini($formulaContainer, type);
        persistPricing();
      });
      
      $token.append($select);
    } 
    else if (token.type === 'op') {
      $token.addClass('fmb-token-op-mini');
      
      const $select = $('<select class="fmb-token-select-mini"></select>');
      $select.append('<option value="+">+ (Plus)</option>');
      $select.append('<option value="-">− (Minus)</option>');
      $select.append('<option value="*">× (Násobení)</option>');
      $select.append('<option value="/">÷ (Dělení)</option>');
      $select.append('<option value="(">(</option>');
      $select.append('<option value=")">)</option>');
      $select.val(token.val);
      
      $select.on('change', function() {
        token.val = $(this).val();
        const $formulaContainer = $(this).closest('.fmb-formula-mini');
        updateFormulaPreviewMini($formulaContainer, type);
        persistPricing();
      });
      
      $token.append($select);
    } 
    else if (token.type === 'num') {
      $token.addClass('fmb-token-num-mini');
      
      const $input = $('<input type="number" step="0.1" class="fmb-token-num-input-mini">');
      $input.val(token.val);
      
      $input.on('input', function() {
        token.val = parseFloat($(this).val()) || 0;
        const $formulaContainer = $(this).closest('.fmb-formula-mini');
        updateFormulaPreviewMini($formulaContainer, type);
        persistPricing();
      });
      
      $token.append($input);
    }
    
    const $del = $('<span class="fmb-token-del-mini">✕</span>');
    $del.on('click', function() {
      const idx = PRICE_CONFIG[PRICE_MODE][type].tokens.indexOf(token);
      if (idx >= 0) {
        PRICE_CONFIG[PRICE_MODE][type].tokens.splice(idx, 1);
      }
      renderFormulaMini();
      persistPricing();
    });
    
    $token.append($del);
    $container.append($token);
  }

  $(document).on('click', '.fmb-formula-add-var', function() {
    const type = $(this).data('type');
    if (!PRICE_CONFIG[PRICE_MODE][type].tokens) {
      PRICE_CONFIG[PRICE_MODE][type].tokens = [];
    }
    PRICE_CONFIG[PRICE_MODE][type].tokens.push({ type: 'var', val: 'base' });
    renderFormulaMini();
    persistPricing();
  });

  $(document).on('click', '.fmb-formula-add-op', function() {
    const type = $(this).data('type');
    if (!PRICE_CONFIG[PRICE_MODE][type].tokens) {
      PRICE_CONFIG[PRICE_MODE][type].tokens = [];
    }
    PRICE_CONFIG[PRICE_MODE][type].tokens.push({ type: 'op', val: '*' });
    renderFormulaMini();
    persistPricing();
  });

  $(document).on('click', '.fmb-formula-add-num', function() {
    const type = $(this).data('type');
    if (!PRICE_CONFIG[PRICE_MODE][type].tokens) {
      PRICE_CONFIG[PRICE_MODE][type].tokens = [];
    }
    PRICE_CONFIG[PRICE_MODE][type].tokens.push({ type: 'num', val: '1' });
    renderFormulaMini();
    persistPricing();
  });

  function updateFormulaPreviewMini($container, type) {
    const tokens = PRICE_CONFIG[PRICE_MODE][type]?.tokens || [];
    
    let expr = '';
    tokens.forEach(t => {
      if (t.type === 'var') {
        if (t.val === 'base') expr += '100';
        else if (t.val === 'qty') expr += '10';
        else if (t.val === 'area') expr += '0.5';
        else if (t.val === 'area_cm2') expr += '425';
        else if (t.val === 'option_mult') expr += '1.2';
      } else if (t.type === 'num') {
        expr += String(t.val);
      } else if (t.type === 'op') {
        expr += t.val;
      }
    });

    $container.find('.formula-expr-mini').text(expr || '—');

    let result = '—';
    try {
      if (expr && /^[0-9+*\-\/().\s]+$/.test(expr)) {
        result = Math.round(Function('"use strict"; return (' + expr + ');')()) + ' Kč';
      }
    } catch(e) {
      result = 'Chyba';
    }
    $container.find('.formula-result-mini').text(result);
  }

  /* =========================
   *  Zone Editor Modal
   * ========================= */
  let $modal = null;
  let currentModalCallback = null;

  function createModal() {
    if ($modal) return;
    
    $modal = $(`
      <div class="fmb-zone-modal">
        <div class="fmb-zone-modal-content">
          <div class="fmb-zone-modal-header">
            <h3>🎯 Nastavení tiskové zóny</h3>
            <button type="button" class="fmb-zone-modal-close">×</button>
          </div>
          <div class="fmb-zone-modal-body">
            <div class="fmb-zone-modal-canvas">
              <img src="" alt="Preview">
              <div class="fmb-zone-rect"></div>
            </div>
          </div>
          <div class="fmb-zone-modal-footer">
            <div class="fmb-zone-modal-info">
              <span>X: <strong class="zone-x-val">0</strong>%</span>
              <span>Y: <strong class="zone-y-val">0</strong>%</span>
              <span>W: <strong class="zone-w-val">0</strong>%</span>
              <span>H: <strong class="zone-h-val">0</strong>%</span>
            </div>
            <div class="fmb-zone-modal-actions">
              <button type="button" class="button button-primary fmb-zone-modal-save">✓ Uložit zónu</button>
            </div>
          </div>
        </div>
      </div>
    `);
    
    $('body').append($modal);
    
    $modal.find('.fmb-zone-modal-close').on('click', closeModal);
    $modal.on('click', function(e) {
      if ($(e.target).is('.fmb-zone-modal')) {
        closeModal();
      }
    });
    
    $modal.find('.fmb-zone-modal-save').on('click', function() {
      if (currentModalCallback) {
        const $canvas = $modal.find('.fmb-zone-modal-canvas');
        const $rect = $modal.find('.fmb-zone-rect');
        const W = $canvas.width();
        const H = $canvas.height();
        const pos = $rect.position();
        
        const zone = {
          x: round1((pos.left / W) * 100),
          y: round1((pos.top / H) * 100),
          w: round1(($rect.outerWidth() / W) * 100),
          h: round1(($rect.outerHeight() / H) * 100)
        };
        
        currentModalCallback(zone);
      }
      closeModal();
    });
    
    $(document).on('keydown.fmbmodal', function(e) {
      if (e.key === 'Escape' && $modal.hasClass('active')) {
        closeModal();
      }
    });
  }

  function openZoneModal(imageUrl, zone, callback) {
    createModal();
    currentModalCallback = callback;
    
    const $img = $modal.find('.fmb-zone-modal-canvas img');
    const $rect = $modal.find('.fmb-zone-rect');
    const $canvas = $modal.find('.fmb-zone-modal-canvas');
    
    if ($rect.hasClass('ui-resizable')) {
      $rect.resizable('destroy');
    }
    if ($rect.hasClass('ui-draggable')) {
      $rect.draggable('destroy');
    }
    
    $img.attr('src', imageUrl);
    
    $img.off('load').on('load', function() {
      setTimeout(() => {
        const W = $canvas.width();
        const H = $canvas.height();
        
        if (W === 0 || H === 0) {
          console.error('Canvas has no dimensions!');
          return;
        }
        
        const x = (zone.x / 100) * W;
        const y = (zone.y / 100) * H;
        const w = (zone.w / 100) * W;
        const h = (zone.h / 100) * H;
        
        $rect.css({
          left: Math.round(x),
          top: Math.round(y),
          width: Math.max(30, Math.round(w)),
          height: Math.max(30, Math.round(h))
        });
        
        updateModalInfo();
        
        $rect.draggable({
          containment: 'parent',
          drag: updateModalInfo,
          stop: updateModalInfo
        }).resizable({
          containment: 'parent',
          handles: 'ne, se, sw, nw',
          resize: updateModalInfo,
          stop: updateModalInfo
        });
      }, 100);
    });
    
    $modal.addClass('active');
    
    function updateModalInfo() {
      const W = $canvas.width();
      const H = $canvas.height();
      const pos = $rect.position();
      
      const x = round1((pos.left / W) * 100);
      const y = round1((pos.top / H) * 100);
      const w = round1(($rect.outerWidth() / W) * 100);
      const h = round1(($rect.outerHeight() / H) * 100);
      
      $modal.find('.zone-x-val').text(x);
      $modal.find('.zone-y-val').text(y);
      $modal.find('.zone-w-val').text(w);
      $modal.find('.zone-h-val').text(h);
    }
  }

  function closeModal() {
    if ($modal) {
      $modal.removeClass('active');
      currentModalCallback = null;
      
      const $rect = $modal.find('.fmb-zone-rect');
      if ($rect.hasClass('ui-resizable')) {
        $rect.resizable('destroy');
      }
      if ($rect.hasClass('ui-draggable')) {
        $rect.draggable('destroy');
      }
    }
  }

  /* =========================
   *  Render Colors
   * ========================= */
  function renderColors() {
    const $colorsRoot = $('#fmb-colors2-root');
    if (!$colorsRoot.length) return;
    
    $colorsRoot.empty();

    if (!Array.isArray(COLORS) || !COLORS.length) {
      $colorsRoot.append('<div class="fmb-admin-empty">Zatím žádné barvy. Přidej první barvu.</div>');
      persistColors();
      return;
    }

    COLORS.forEach((c, cIdx) => {
      if (!Array.isArray(c.per_view)) c.per_view = [];
      if (!Array.isArray(c.zones))    c.zones    = [];

      const $card = $('<div class="fmb-color-box"></div>');

      const $head = $('<div class="fmb-color-head"></div>');
      const $hex  = $(`<input type="text" class="fmb-color-hex" value="${c.hex||'#ffffff'}">`);
      const $lab  = $(`<input type="text" class="fmb-color-label" placeholder="Název barvy" value="${c.label||''}">`);
      const $del  = $('<button type="button" class="button-link-delete">✕ Odstranit</button>');
      $head.append($hex, $lab, $del);
      $card.append($head);

      const $views = $('<div class="fmb-views-wrap"></div>');

      c.per_view.forEach((v, vIdx) => {
        const zone = c.zones[vIdx] || { x:20, y:20, w:60, h:60, label: `View ${vIdx+1}` };

        const $v = $('<div class="fmb-view-card"></div>');

        const $title = $('<div class="fmb-view-title"></div>');
        const $vLabel = $(`<input type="text" class="fmb-view-label" placeholder="Pohled" value="${zone.label||''}">`);
        const $vRemove = $('<button type="button" class="button-link-delete">✕</button>');
        $title.append($vLabel, $vRemove);
        $v.append($title);

        const $thumb = $('<div class="fmb-view-thumb"></div>');
        const $img = $('<img alt="">');
        if (v.url) $img.attr('src', v.url);
        $thumb.append($img);
        $v.append($thumb);

        const $acts = $('<div class="fmb-view-actions"></div>');
        const $pick = $('<button type="button" class="button button-small">📷 Obrázek</button>');
        const $editZone = $('<button type="button" class="button button-small button-primary">🎯 Zóna</button>');
        const $rm   = $('<button type="button" class="button button-small">🗑</button>');
        $acts.append($pick, $editZone, $rm);
        $v.append($acts);

        const $zWrap = $('<div class="fmb-zone-wrap"></div>');
        $zWrap.append('<div class="fmb-zone-title">📐 Zóna (%)</div>');
        const $zx = $(`<input type="number" step="0.1" class="fmb-zone-x" value="${round1(zone.x)}" readonly>`);
        const $zy = $(`<input type="number" step="0.1" class="fmb-zone-y" value="${round1(zone.y)}" readonly>`);
        const $zw = $(`<input type="number" step="0.1" class="fmb-zone-w" value="${round1(zone.w)}" readonly>`);
        const $zh = $(`<input type="number" step="0.1" class="fmb-zone-h" value="${round1(zone.h)}" readonly>`);
        const $grid = $('<div class="fmb-zone-grid"></div>');
        $grid.append($('<label>X</label>').append($zx));
        $grid.append($('<label>Y</label>').append($zy));
        $grid.append($('<label>W</label>').append($zw));
        $grid.append($('<label>H</label>').append($zh));
        $zWrap.append($grid, '<div class="fmb-zone-help">💡 Klikni na zónu pro úpravu</div>');
        $v.append($zWrap);

        $vLabel.on('input', () => {
          const newLabel = $vLabel.val().trim();
          COLORS[cIdx].per_view[vIdx].label = newLabel;
          COLORS[cIdx].zones[vIdx] = COLORS[cIdx].zones[vIdx] || {};
          COLORS[cIdx].zones[vIdx].label = newLabel;
          persistColors();
        });

        $vRemove.on('click', () => {
          if(confirm('Odstranit pohled?')){
            COLORS[cIdx].per_view.splice(vIdx, 1);
            COLORS[cIdx].zones.splice(vIdx, 1);
            persistColors(); 
            renderColors();
          }
        });

        $pick.on('click', async () => {
          const img = await openMedia();
          COLORS[cIdx].per_view[vIdx].id = img.id;
          COLORS[cIdx].per_view[vIdx].url = img.url;
          $img.attr('src', img.url);
          persistColors();
        });
        
        $rm.on('click', () => {
          if(confirm('Odebrat?')){
            COLORS[cIdx].per_view.splice(vIdx, 1);
            COLORS[cIdx].zones.splice(vIdx, 1);
            persistColors();
            renderColors();
          }
        });

        $editZone.on('click', () => {
          const imageUrl = v.url || '';
          if (!imageUrl) {
            alert('Nahraj nejprve obrázek.');
            return;
          }
          
          openZoneModal(imageUrl, zone, (newZone) => {
            COLORS[cIdx].zones[vIdx] = {
              ...COLORS[cIdx].zones[vIdx],
              x: newZone.x,
              y: newZone.y,
              w: newZone.w,
              h: newZone.h
            };
            
            $zx.val(newZone.x);
            $zy.val(newZone.y);
            $zw.val(newZone.w);
            $zh.val(newZone.h);
            
            persistColors();
          });
        });

        $views.append($v);
      });

      const $addView = $('<button type="button" class="button button-secondary" style="margin-top:6px">➕ Pohled</button>');
      $addView.on('click', () => {
        const newIdx = c.per_view.length;
        COLORS[cIdx].per_view.push({ label:`View ${newIdx + 1}`, id:0, url:''});
        COLORS[cIdx].zones.push({ x:20, y:20, w:60, h:60, label:`View ${newIdx + 1}` });
        persistColors(); 
        renderColors();
      });

      $card.append($views, $addView);
      $colorsRoot.append($card);

      $hex.wpColorPicker({
        defaultColor: c.hex || '#ffffff',
        change: (e, ui) => { COLORS[cIdx].hex = ui.color.toString(); persistColors(); },
        clear: () => { COLORS[cIdx].hex = '#ffffff'; persistColors(); }
      });
      
      $lab.on('input', () => { COLORS[cIdx].label = $lab.val().trim(); persistColors(); });
      
      $del.on('click', () => { 
        if (confirm('Odstranit barvu?')) {
          COLORS.splice(cIdx,1); 
          persistColors(); 
          renderColors(); 
        }
      });
    });

    persistColors();
  }

  $('#fmb-color-add').on('click', () => {
    COLORS.push({ hex:'#000000', label:'', per_view:[], zones:[] });
    renderColors();
  });

  /* =========================
   *  Init
   * ========================= */
  $(document).ready(function(){
    console.log('✅ Initializing FMB Admin...');
    renderColors();
    
    if (PRICE_MODE === 'area') {
      updateAreaPreview();
    } else if (PRICE_MODE === 'fixed') {
      updateFixedPreview();
    } else if (PRICE_MODE === 'formula') {
      renderFormulaMini();
    }
    
    // OPRAVENO: Inicializuj skrytá pole hned na začátku
    persistColors();
    persistPricing();
    
    $('form#post').on('submit', function(){
      persistColors();
      persistPricing();
      console.log('📤 Submitting...');
    });
    
    console.log('✅ FMB Admin initialized');
  });

})(jQuery);