/* themes/bricks/inc/mockups-product.js */
(function($){
  'use strict';

  const $mockupSelect = $('#mockup_selected');
  const $graphicId    = $('#mockup_graphic_id');
  const $pickGraphic  = $('#mockup_pick_graphic');
  const $removeGraphic= $('#mockup_remove_graphic');
  const $thumbWrap    = $('#mockup_graphic_thumb');

  const $sizeRadios   = $('input[name="mockup_size_mode"]'); // global default
  const $sizeCustom   = $('#mockup_size_custom');

  const $sizesWrap    = $('#mockup_multi_sizes');
  const $sizesHidden  = $('#mockup_sizes_json');

  const $btnRender    = $('#mockup_render_btn');

  // ---------- helpers ----------
  const clamp = (n,min,max)=>Math.max(min,Math.min(max,n));
  function getGlobalPct(){
    const mode = $sizeRadios.filter(':checked').val() || 'medium';
    if (mode==='small') return 20;
    if (mode==='large') return 50;
    if (mode==='custom') return clamp(parseFloat(String($sizeCustom.val()||'35').replace(',','.'))||35,1,100);
    return 35;
  }

  // content helpers (dlouhý popis produktu)
  function getCurrentContentHTML(){
    try {
      // Block editor
      if (window.wp && wp.data && wp.data.select){
        const sel = wp.data.select('core/editor');
        if (sel && sel.getEditedPostContent) {
          return sel.getEditedPostContent() || '';
        }
      }
    }catch(e){}
    // Classic editor (TinyMCE / textarea)
    const tmce = window.tinymce && tinymce.get && tinymce.get('content');
    if (tmce && !tmce.isHidden()) return tmce.getContent() || '';
    const $ta = $('#content');
    return $ta.length ? ($ta.val() || '') : '';
  }
  function setContentHTML(html){
    try {
      if (window.wp && wp.data && wp.data.dispatch){
        const disp = wp.data.dispatch('core/editor');
        if (disp && disp.editPost){
          disp.editPost({ content: html });
          return;
        }
      }
    }catch(e){}
    const tmce = window.tinymce && tinymce.get && tinymce.get('content');
    if (tmce && !tmce.isHidden()) { tmce.setContent(html || ''); tmce.save(); }
    const $ta = $('#content'); if ($ta.length) $ta.val(html || '');
  }
  function isEmptyHTML(html){
    if (!html) return true;
    const txt = $('<div>').html(html).text().trim();
    // Odstraň i NBSP apod.
    return txt.replace(/\u00a0/g,'').trim().length === 0;
  }

  const imageCache = {};
  function loadImage(url){
    if (imageCache[url]) return imageCache[url];
    imageCache[url] = new Promise((resolve, reject)=>{
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = ()=>resolve(img);
      img.onerror = ()=>reject(new Error('Image load error '+url));
      img.src = url;
    });
    return imageCache[url];
  }

  // ---------- state ----------
  let mockupImages = []; // [{id,url,x,y,index}]
  let graphicURL   = null;

  // ---------- UI (per-image grid s canvasy) ----------
  function serializeSizesFromUI(){
    const rows = [];
    $sizesWrap.find('.ms-card').each(function(){
      const $c = $(this);
      const id = $c.data('id'); const index = Number($c.data('index'));
      const sel = $c.find(`input[name="ms-${index}"]:checked`).val();
      let pct = null;
      if (sel==='20'||sel==='35'||sel==='50') pct = Number(sel);
      else if (sel==='custom'){
        const v = Number($c.find('.ms-custom').val()||0);
        if (v>0) pct = Math.max(1, Math.min(100, v));
      }
      if (pct!==null) rows.push({id, index, pct});
    });
    $sizesHidden.val(JSON.stringify(rows));
  }

  function readSaved(){
    try { return JSON.parse($sizesHidden.val() || $sizesWrap.attr('data-saved') || '[]'); }
    catch(e){ return []; }
  }

  function getPctForCard($card){
    const index = Number($card.data('index'));
    const sel = $card.find(`input[name="ms-${index}"]:checked`).val();
    if (sel==='20'||sel==='35'||sel==='50') return Number(sel);
    if (sel==='custom'){
      const v = Number($card.find('.ms-custom').val()||0);
      if (v>0) return Math.max(1, Math.min(100, v));
    }
    const saved = readSaved();
    const id = $card.data('id');
    const rec = saved.find(r => String(r.id)===String(id)) || saved.find(r=> Number(r.index)===index);
    return rec && rec.pct ? Number(rec.pct) : null;
  }

  async function renderCardPreview($card, img){
    const canvas = $card.find('.ms-canvas')[0];
    if (!canvas) return;

    try{
      const base  = await loadImage(img.url);
      const print = graphicURL ? await loadImage(graphicURL) : null;

      const maxW = $card.width() || 260;
      const scale = Math.min(1, maxW / base.width);
      canvas.width  = Math.round(base.width * scale);
      canvas.height = Math.round(base.height * scale);

      const ctx = canvas.getContext('2d');
      ctx.clearRect(0,0,canvas.width,canvas.height);
      ctx.drawImage(base, 0, 0, canvas.width, canvas.height);

      if (print){
        const pct = (function(){
          const m = getPctForCard($card);
          return (m!==null) ? m : getGlobalPct();
        })();

        const targetW = (pct/100)*canvas.width;
        const r = targetW / print.width;
        const targetH = print.height * r;

        const cx = (img.x/100)*canvas.width;
        const cy = (img.y/100)*canvas.height;
        const dx = Math.round(cx - targetW/2);
        const dy = Math.round(cy - targetH/2);

        ctx.drawImage(print, dx, dy, Math.round(targetW), Math.round(targetH));
      }

    }catch(e){
      console.error('Preview error', e);
    }
  }

  function renderAllPreviews(){
    $sizesWrap.find('.ms-card').each(function(){
      const idx = Number($(this).data('index'));
      const img = mockupImages[idx];
      if (img) renderCardPreview($(this), img);
    });
  }

  function buildSizesUI(){
    $sizesWrap.empty();
    const saved = readSaved();

    const $grid = $('<div class="ms-grid" />').css({
      display:'grid', gridTemplateColumns:'repeat(auto-fill, minmax(260px, 1fr))', gap:'12px'
    });

    mockupImages.forEach(img=>{
      const savedRec = saved.find(r => String(r.id)===String(img.id)) || saved.find(r=> Number(r.index)===Number(img.index));
      const preset = savedRec ? savedRec.pct : '';

      const $card = $(`
        <div class="ms-card" data-id="${img.id}" data-index="${img.index}">
          <canvas class="ms-canvas"></canvas>
          <div class="ms-controls" style="padding:8px 0;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <strong>Velikost:</strong>
            <label><input type="radio" name="ms-${img.index}" value="20"> 20%</label>
            <label><input type="radio" name="ms-${img.index}" value="35"> 35%</label>
            <label><input type="radio" name="ms-${img.index}" value="50"> 50%</label>
            <label><input type="radio" name="ms-${img.index}" value="custom"> Vlastní</label>
            <input type="number" class="ms-custom" min="1" max="100" step="1" value="${preset!==''?preset:''}" placeholder="%" />
          </div>
        </div>
      `);

      if (preset===20 || preset===35 || preset===50) {
        $card.find(`input[name="ms-${img.index}"][value="${preset}"]`).prop('checked', true);
        $card.find('.ms-custom').val('');
      } else if (preset!=='') {
        $card.find(`input[name="ms-${img.index}"][value="custom"]`).prop('checked', true);
      }

      $card.on('change input', 'input', function(){
        serializeSizesFromUI();
        renderCardPreview($card, img);
      });

      $grid.append($card);
    });

    $sizesWrap.append($grid);
    renderAllPreviews();
  }

  // ---------- AJAX dat mockupu ----------
  async function loadMockupData(){
    const mid = parseInt($mockupSelect.val() || '0', 10);
    if (!mid) { mockupImages=[]; $sizesWrap.empty(); return; }
    try{
      const resp = await $.post(MOCKUP_PROD.ajax_url, { action:'get_mockup_data', mockup_id: mid });
      if (resp && resp.success) {
        mockupImages = resp.data.images || [];

        // doplň long description do produktu, pokud je prázdný
        const current = getCurrentContentHTML();
        if (isEmptyHTML(current) && resp.data.long_desc) {
          setContentHTML(resp.data.long_desc);
        }

      } else {
        mockupImages = [];
      }
      buildSizesUI();
    }catch(e){ mockupImages=[]; $sizesWrap.empty(); }
  }

  // ---------- výběr grafiky ----------
  let frame=null;
  $pickGraphic.on('click', function(e){
    e.preventDefault();
    if (frame) frame.close();
    frame = wp.media({ title: MOCKUP_PROD.i18n?.add || 'Přidat obrázek', button:{text:'Vybrat'}, multiple:false, library:{type:['image']} });
    frame.on('select', function(){
      const att = frame.state().get('selection').first().toJSON();
      $graphicId.val(att.id);
      const url = (att.sizes && (att.sizes.large?.url || att.sizes.medium?.url || att.sizes.full?.url)) || att.url || '';
      graphicURL = url;
      $thumbWrap.html('<img src="'+url+'" alt="" style="max-width:120px;border:1px solid #ddd;border-radius:4px;">');
      renderAllPreviews();
    });
    frame.open();
  });
  $removeGraphic.on('click', function(e){
    e.preventDefault();
    $graphicId.val('');
    graphicURL = null;
    $thumbWrap.html('<span class="thumb-empty">(žádná)</span>');
    renderAllPreviews();
  });

  $mockupSelect.on('change', loadMockupData);
  $sizeRadios.on('change', renderAllPreviews);
  $sizeCustom.on('input',  renderAllPreviews);

  // Boot: pokud je uložená grafika, načti URL kvůli náhledu
  (function bootGraphicFromId(){
    const id = parseInt($graphicId.val() || '0', 10);
    if (!id) return;
    $.post(MOCKUP_PROD.ajax_url, { action:'get_attachment_src', security: MOCKUP_PROD.nonce, attachment_id:id })
      .done(function(resp){
        if (resp && resp.success && resp.data?.url) {
          graphicURL = resp.data.url;
          $thumbWrap.html('<img src="'+graphicURL+'" alt="" style="max-width:120px;border:1px solid #ddd;border-radius:4px;">');
          renderAllPreviews();
        }
      });
  })();

  // inicializace
  loadMockupData();

  // ---------- RENDER VŠECH ----------
  $btnRender.on('click', function () {
    const mockupId  = parseInt($mockupSelect.val() || '0', 10);
    const graphicId = parseInt($graphicId.val() || '0', 10);
    if (!mockupId)  { alert(MOCKUP_PROD.i18n?.no_mockup  || 'Není vybrán mockup.');  return; }
    if (!graphicId) { alert(MOCKUP_PROD.i18n?.no_graphic || 'Nebyla vybrána grafika.'); return; }

    // aktualizuj hidden JSON z UI
    serializeSizesFromUI();

    $btnRender.prop('disabled', true).text(MOCKUP_PROD.i18n?.rendering || 'Renderuji…');

    $.ajax({
      url: MOCKUP_PROD.ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'render_mockup_image',
        security: MOCKUP_PROD.nonce,
        product_id: $btnRender.data('post'),
        mockup_id: mockupId,
        graphic_id: graphicId,
        size_mode: ($('input[name="mockup_size_mode"]:checked').val() || 'medium'),
        size_custom: getGlobalPct(),
        sizes_json: $sizesHidden.val() || ''
      }
    }).done(function (resp) {
      if (!resp || !resp.success) {
        alert((resp && resp.data && resp.data.message) ? resp.data.message : 'Render selhal.');
        return;
      }
      if (resp.data.errors && resp.data.errors.length) {
        alert((resp.data.message || 'Hotovo, ale některé položky selhaly:') + "\n" + resp.data.errors.join("\n"));
      } else {
        alert(resp.data.message || 'Hotovo.');
      }
      window.location.reload();
    }).fail(function (xhr) {
      const body = (xhr && xhr.responseText) ? String(xhr.responseText).slice(0, 400) : '(bez těla odpovědi)';
      alert('Render: HTTP '+ (xhr ? xhr.status : '??') + ' ' + (xhr ? xhr.statusText : '') + '\n\n' + body);
      console.error('render_mockup_image failed', xhr);
    }).always(function () {
      $btnRender.prop('disabled', false).text(MOCKUP_PROD.i18n?.render || 'Vyrenderovat všechny');
    });
  });

})(jQuery);
