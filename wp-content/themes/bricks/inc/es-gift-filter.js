(function(){
  // vnitřní stav vybraných termů
  const state = { aud:[], types:[], themes:[] };

  function toggle(arr, id){
    const i = arr.indexOf(id);
    if (i >= 0) arr.splice(i,1);
    else arr.push(id);
  }

  // Sbalování sekcí (– / +)
  document.addEventListener('click', (e)=>{
    const tgl = e.target.closest('.fl-toggle');
    if (tgl){
      const sec = tgl.closest('.fl-sec');
      const body = sec.querySelector('.fl-body');
      const open = tgl.getAttribute('aria-expanded') !== 'false';
      tgl.setAttribute('aria-expanded', open ? 'false' : 'true');
      tgl.textContent = open ? '+' : '–';
      body.style.display = open ? 'none' : '';
      return;
    }

    const btn = e.target.closest('.fl-item');
    if (!btn) return;

    const termId = parseInt(btn.dataset.term,10);
    if (!termId) return;

    const sec = btn.closest('.fl-sec');
    if (!sec) return;

    btn.setAttribute('aria-pressed', btn.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');

    switch(sec.dataset.tax){
      case 'gift_audience': toggle(state.aud, termId); break;
      case 'gift_type':     toggle(state.types, termId); break;
      case 'gift_theme':    toggle(state.themes, termId); break;
    }
    collectAndSend();
  });

  // AJAX – načti grid podle state
  function collectAndSend(){
    const fd = new FormData();
    fd.append('action','es_filter_products');
    fd.append('nonce', ES_GIFT_FILTER.nonce);
    ['aud','types','themes'].forEach(key=>{
      state[key].forEach(v => fd.append(key+'[]', v));
    });

    const gridWrap = document.querySelector('#esf-results .es_products_grid');
    if (gridWrap){ gridWrap.setAttribute('data-loading','1'); gridWrap.innerHTML = ''; }

    fetch(ES_GIFT_FILTER.ajaxurl, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (data.success){
          document.querySelector('#esf-results').innerHTML = data.data.html;
        }
      })
      .catch(console.error);
  }

  // první render (bez filtrů)
  document.addEventListener('DOMContentLoaded', collectAndSend);
})();
