jQuery(function($){
  // Selektory (metabox je v classic metabox oblasti i v Gutenbergu)
  const $box   = $('.mockup-single-box');
  if (!$box.length) return;

  const $thumb = $('#mockup-thumb');
  const $json  = $('#mockup_image_json');
  const $xIn   = $('#mockup-x');
  const $yIn   = $('#mockup-y');

  // 1) Helper: ulož stav do DOM + hidden JSON
  function setData(obj){
    const id  = obj.id || 0;
    const url = obj.url || '';
    // zaokrouhlení na 1 desetinu
    let x = typeof obj.x === 'number' ? obj.x : parseFloat($thumb.attr('data-x')||'50');
    let y = typeof obj.y === 'number' ? obj.y : parseFloat($thumb.attr('data-y')||'50');
    x = Math.round(Math.max(0, Math.min(100, x)) * 10) / 10;
    y = Math.round(Math.max(0, Math.min(100, y)) * 10) / 10;

    $thumb.attr({'data-id':id,'data-url':url,'data-x':x,'data-y':y});
    $xIn.val(x.toFixed(1)); $yIn.val(y.toFixed(1));

    if(url){
      $thumb.removeClass('is-empty').html(
        '<img src="'+url+'" alt=""><span class="mockup-marker" style="left:'+x+'%; top:'+y+'%;"></span>'
      );
    }else{
      $thumb.addClass('is-empty').html('<span class="mockup-empty">Žádný obrázek</span>');
    }
    $json.val(JSON.stringify({id, url, x, y}));
  }

  // 2) Klik do náhledu = nastavení středu
  $thumb.on('click', function(e){
    const $img = $(this).find('img');
    if(!$img.length) return;
    const rect = $img.get(0).getBoundingClientRect();
    let x = ((e.clientX - rect.left) / rect.width) * 100;
    let y = ((e.clientY - rect.top)  / rect.height) * 100;
    setData({ id: parseInt($thumb.attr('data-id')||'0',10), url: String($thumb.attr('data-url')||''), x, y });
  });

  // 3) Ruční změna vstupů
  $xIn.add($yIn).on('input', function(){
    let x = parseFloat($xIn.val() || '50');
    let y = parseFloat($yIn.val() || '50');
    setData({ id: parseInt($thumb.attr('data-id')||'0',10), url: String($thumb.attr('data-url')||''), x, y });
  });

  // 4) Media frame (JEN JEDEN obrázek)
  let frame = null;
  $(document).on('click', '#mockup-pick', function(e){
    e.preventDefault();
    if(!frame){
      frame = wp.media({
        title: 'Vybrat obrázek mockupu',
        button: { text: 'Použít' },
        multiple: false,                   // jen jeden
        library: { type: ['image'] },     // jen obrázky
        frame: 'select',                   // standardní select frame
        state: 'insert'
      });
      // správně: .on('select', ...) — viz ofiko/handbook příklady. :contentReference[oaicite:2]{index=2}
      frame.on('select', function(){
        const att = frame.state().get('selection').first();
        if (!att) return;
        const obj = att.toJSON();
        // preferuj 'large' / 'medium' / jinak originál
        const sizes = obj.sizes || {};
        const url = (sizes.large && sizes.large.url) || (sizes.medium && sizes.medium.url) || obj.url;
        setData({ id: obj.id, url: url || obj.url, x: 50, y: 50 });
      });
    }
    frame.open();
  });

  // 5) Odebrat
  $(document).on('click', '#mockup-remove', function(e){
    e.preventDefault();
    setData({ id: 0, url: '', x: 50, y: 50 });
  });

  // Init: z metaboxu (data-* už obsahují stav)
  setData({
    id:  parseInt($thumb.attr('data-id')||'0',10),
    url: String($thumb.attr('data-url')||''),
    x:   parseFloat($thumb.attr('data-x')||'50'),
    y:   parseFloat($thumb.attr('data-y')||'50'),
  });
});
