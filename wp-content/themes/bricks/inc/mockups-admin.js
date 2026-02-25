/* themes/bricks/inc/mockups-admin.js */
(function($){
  'use strict';

  const $list   = $('#mockup-images');
  const $hidden = $('#mockup_images_json');
  const $btnAdd = $('#mockup-add-images');

  if (!$list.length) return;

  // helpers
  const clamp  = (n, min, max) => Math.max(min, Math.min(max, n));
  const round1 = (n) => Math.round(n * 10) / 10; // 1 desetinné místo

  let items  = []; // [{id,url,x,y,colors:[]}]
  let COLORS = []; // [{slug,name,hex,source,id}]

  /** Načti barvy z adminu (CPT "color" + případně pa_color) */
  function fetchColors(){
    const url = (window.MOCKUP_ADMIN && MOCKUP_ADMIN.ajax_url) || (window.ajaxurl);
    if (!url) { COLORS = []; render(); return; }
    $.post(url, { action: 'brx_get_colors' })
      .done(function(resp){
        if (resp && resp.success && resp.data && $.isArray(resp.data.colors)) {
          COLORS = resp.data.colors;
        } else {
          COLORS = [];
        }
      })
      .fail(function(){ COLORS = []; })
      .always(function(){ render(); });
  }

  function makeColorSelect(selected){
    selected = Array.isArray(selected) ? selected.map(String) : [];
    const sizeAttr = Math.min(6, Math.max(3, COLORS.length || 3));
    const $sel = $('<select multiple class="mockup-colors-select" />').attr('size', sizeAttr);
    COLORS.forEach(function(c){
      const label = c.name + (c.hex ? (' ('+c.hex+')') : '');
      const $opt = $('<option>').val(c.slug).text(label);
      if (selected.indexOf(String(c.slug)) !== -1) $opt.attr('selected', true);
      $sel.append($opt);
    });
    return $sel;
  }

  function persist(){
    try{ $hidden.val(JSON.stringify(items)); }catch(e){}
  }

  function render(){
    $list.empty();

    items.forEach(function(it, idx){
      const $li = $('<li class="mockup-item" />').attr('data-index', idx);

      // THUMB
      const $thumb = $('<div class="mockup-thumb" />')
        .attr('data-id', it.id||0)
        .attr('data-url', it.url||'');
      const $img = $('<img>').attr('src', it.url||'').attr('alt','');
      const $marker = $('<div class="center-marker" />')
        .css({ left:(it.x||50)+'%', top:(it.y||50)+'%' });
      $thumb.append($img, $marker);

      // FIELDS: X, Y, Remove
      const $fields = $('<div class="mockup-fields" />');
      const $xIn = $('<input type="number" step="0.1" min="0" max="100" />').val(it.x!=null?it.x:50);
      const $yIn = $('<input type="number" step="0.1" min="0" max="100" />').val(it.y!=null?it.y:50);
      const $remove = $('<a href="#" class="mockup-remove">Odebrat</a>');
      $fields.append(
        $('<label>').text('X %').prepend($xIn),
        $('<label>').text('Y %').prepend($yIn),
        $remove
      );

      // COLORS (multiselect) – vložíme mezi thumb a spodní fields
      const $colorsWrap = $('<div class="mockup-colors" style="padding:8px 12px;border-top:1px solid #eef2f7;background:#fafafa;"></div>');
      $colorsWrap.append('<div style="font-size:12.5px;color:#374151;margin-bottom:6px;">Barvy (volitelné)</div>');
      const currentColors = Array.isArray(it.colors) ? it.colors : [];
      const $colorSel = makeColorSelect(currentColors);
      $colorsWrap.append($colorSel);

      // assemble
      $li.append($thumb, $colorsWrap, $fields);
      $list.append($li);

      // interactions

      // klik do obrázku = změna středu
      $thumb.on('click', function(e){
        const rect = $img.get(0).getBoundingClientRect();
        var x = ((e.clientX - rect.left) / rect.width) * 100;
        var y = ((e.clientY - rect.top)  / rect.height) * 100;
        x = clamp(round1(x), 0, 100);
        y = clamp(round1(y), 0, 100);
        items[idx].x = x; items[idx].y = y;
        $marker.css({ left:x+'%', top:y+'%' });
        $xIn.val(x); $yIn.val(y);
        persist();
      });

      // ruční změna vstupů
      $xIn.add($yIn).on('input', function(){
        var x = clamp(round1(parseFloat($xIn.val() || '50')), 0, 100);
        var y = clamp(round1(parseFloat($yIn.val() || '50')), 0, 100);
        items[idx].x = x; items[idx].y = y;
        $marker.css({ left:x+'%', top:y+'%' });
        persist();
      });

      // změna barev
      $colorSel.on('change', function(){
        var chosen = $(this).val() || [];
        items[idx].colors = chosen.map(String);
        persist();
      });

      // odebrat
      $remove.on('click', function(ev){
        ev.preventDefault();
        items.splice(idx,1);
        render();
        persist();
      });
    });

    // sortable
    try{ $list.sortable('destroy'); }catch(e){}
    $list.sortable({
      placeholder:'mockup-item-placeholder',
      update:function(){
        var reordered=[];
        $list.find('li.mockup-item').each(function(){
          var old = Number($(this).attr('data-index'));
          reordered.push(items[old]);
        });
        items = reordered;
        render();
        persist();
      }
    });

    persist();
  }

  function bootFromInitial(){
    var initial = [];
    try{ initial = JSON.parse(String($list.attr('data-initial')||'[]')); }catch(e){ initial = []; }
    items = (initial||[]).map(function(o){
      return {
        id: Number(o.id)||0,
        url: String(o.url||''),
        x: (typeof o.x==='number') ? clamp(round1(o.x),0,100) : 50.0,
        y: (typeof o.y==='number') ? clamp(round1(o.y),0,100) : 50.0,
        colors: Array.isArray(o.colors) ? o.colors.map(String) : []
      };
    });
  }

  // media add
  var frame=null;
  $btnAdd.on('click', function(e){
    e.preventDefault();
    if (frame) frame.close();
    frame = wp.media({
      title: (window.MOCKUP_ADMIN && MOCKUP_ADMIN.i18n && MOCKUP_ADMIN.i18n.add) || 'Přidat obrázky',
      button:{ text:'Vybrat' },
      multiple:true,
      library:{ type:['image'] }
    });
    frame.on('select', function(){
      var selection = frame.state().get('selection').toJSON();
      selection.forEach(function(att){
        var sizes = att.sizes||{};
        var url = (sizes.large && sizes.large.url) ||
                  (sizes.medium && sizes.medium.url) ||
                  (sizes.full && sizes.full.url) ||
                  att.url || '';
        items.push({ id:Number(att.id)||0, url:url, x:50.0, y:50.0, colors:[] });
      });
      render();
      persist();
    });
    frame.open();
  });

  // init
  bootFromInitial();
  fetchColors(); // po načtení volá render()
})(jQuery);
