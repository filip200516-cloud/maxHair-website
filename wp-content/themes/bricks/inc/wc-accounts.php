<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * ===== 1) ENDPOINTY =====
 * Přidej si libovolné vlastní: support, invoices, favorites
 */
add_action('init', function () {
  foreach (['support','invoices','favorites'] as $ep) {
    add_rewrite_endpoint($ep, EP_ROOT | EP_PAGES);
  }
}, 9);

/** Doporučení: po nasazení uložit Permalinky (Settings → Permalinks → Save). */

/**
 * ===== 2) MENU / POŘADÍ =====
 */
add_filter('woocommerce_account_menu_items', function($items){
  // Příklad: nechci Downloads
  // unset($items['downloads']);

  $ordered = [
    'dashboard'        => __('Přehled','woocommerce'),
    'orders'           => __('Objednávky','woocommerce'),
    'edit-address'     => __('Adresa','woocommerce'),
    'payment-methods'  => __('Platební metody','woocommerce'),
    'edit-account'     => __('Nastavení účtu','woocommerce'),

    // naše vlastní
    'support'          => __('Podpora','woocommerce'),
    'invoices'         => __('Faktury','woocommerce'),
    'favorites'        => __('Oblíbené','woocommerce'),

    'customer-logout'  => __('Odhlásit se','woocommerce'),
  ];

  $new = [];
  foreach($ordered as $key => $label){
    if (isset($items[$key])) {
      $new[$key] = $items[$key];          // použij label Woo, pokud existuje
    } elseif (in_array($key, ['support','invoices','favorites'])) {
      $new[$key] = $label;                // náš custom
    }
  }
  // co zbylo z jiných pluginů → na konec
  foreach($items as $k => $v){ if(!isset($new[$k])) $new[$k] = $v; }
  return $new;
}, 20);

/**
 * ===== 3) OBSAH ENDPOINTŮ =====
 * Každý endpoint = vlastní callback. Zde jednoduché příklady.
 */
add_action('woocommerce_account_support_endpoint', function(){
  echo '<h2>Podpora</h2><p>Potřebujete pomoct? Napište na <a href="mailto:podpora@tvujweb.cz">podpora@tvujweb.cz</a>.</p>';
  echo '<p><a class="button" href="'.esc_url( wc_get_endpoint_url('orders','','') ).'">Moje objednávky</a></p>';
});

add_action('woocommerce_account_invoices_endpoint', function(){
  echo '<h2>Faktury</h2>';
  // Sem můžeš připojit plugin na faktury (PDF Invoices & Packing Slips apod.)
  echo '<p>Zde budou ke stažení PDF faktury k objednávkám.</p>';
});

add_action('woocommerce_account_favorites_endpoint', function(){
  echo '<h2>Oblíbené produkty</h2>';
  // Když máš wishlist plugin, můžeš sem vypsat jeho shortcode:
  // echo do_shortcode('[yith_wcwl_wishlist]');
  echo '<p>Zatím žádné oblíbené položky.</p>';
});

/**
 * ===== 4) SHORTCODE PRO BRICKS =====
 * [fmb_my_account] – spolehlivý render menu + obsahu přes Woo hooky.
 */
add_shortcode('fmb_my_account', function($atts){
  ob_start();
  echo '<div class="fmb-myaccount-wrap">';
  if (is_user_logged_in()){
    echo '<div class="fmb-myaccount-grid">';
      echo '<aside class="fmb-myaccount-nav">'; do_action('woocommerce_account_navigation'); echo '</aside>';
      echo '<main class="fmb-myaccount-content">'; do_action('woocommerce_account_content'); echo '</main>';
    echo '</div>';
  } else {
    echo '<div class="fmb-myaccount-auth">'; wc_get_template('myaccount/form-login.php'); echo '</div>';
  }
  echo '</div>';
  return ob_get_clean();
});

/**
 * ===== 5) LEHKÉ CSS jen pro My Account =====
 */
add_action('wp_head', function(){
  if (!is_account_page()) return; ?>
  <style>
    .fmb-myaccount-grid{display:grid;grid-template-columns:260px 1fr;gap:24px;align-items:start}
    @media (max-width:980px){.fmb-myaccount-grid{grid-template-columns:1fr}}
    .fmb-myaccount-nav .woocommerce-MyAccount-navigation ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px}
    .fmb-myaccount-nav .woocommerce-MyAccount-navigation li a{display:block;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;text-decoration:none}
    .fmb-myaccount-nav .woocommerce-MyAccount-navigation li.is-active a{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
    .fmb-myaccount-content .woocommerce-MyAccount-content{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px}
    .fmb-myaccount-auth .u-columns{display:grid;grid-template-columns:1fr 1fr;gap:24px}
    @media (max-width:980px){.fmb-myaccount-auth .u-columns{grid-template-columns:1fr}}
  </style>
<?php });

/**
 * ===== 6) Přesměrování po odhlášení (volitelné) =====
 */
add_filter('woocommerce_logout_redirect', fn($r)=>home_url('/'));

// Sidebar header (avatar + jméno + mail) a přesun logoutu na konec seznamu
add_action('woocommerce_before_account_navigation', function(){
  if (!is_user_logged_in()) return;
  $u = wp_get_current_user();
  $ava = get_avatar_url($u->ID, ['size'=>96]);
  echo '<div class="fmb-nav-head">';
  echo '<div class="ava"><img src="'.esc_url($ava).'" alt=""></div>';
  echo '<div class="meta"><div class="name">'.esc_html($u->display_name ?: $u->user_login).'</div>';
  echo '<div class="mail">'.esc_html($u->user_email).'</div></div></div>';
});

// Přidej class na logout <li>, ať se styluje jako spodní tlačítko
add_filter('woocommerce_account_menu_items', function($items){
  // nic neměníme na pořadí – jen přidáme marker pro CSS
  add_filter('woocommerce_account_menu_item_classes', function($classes, $endpoint){
    if ($endpoint === 'customer-logout') $classes[] = 'fmb-logout';
    return $classes;
  }, 10, 2);
  return $items;
}, 9);

// Dashboard – hero + karty (mění jen dashboard, nic ostatního)
// CZ karty na nástěnce (bez hero textu)
add_action('woocommerce_account_dashboard', function(){
  $base = wc_get_page_permalink('myaccount');
  ?>
  <div class="fmb-dash-cards">
    <a class="fmb-card track" href="<?php echo esc_url( wc_get_endpoint_url('orders','',$base) ); ?>">
      <div class="ic"></div>
      <div>
        <h3>Sledovat objednávku</h3>
        <p>Sledujte stav své objednávky a novinky o doručení.</p>
      </div>
    </a>
    <a class="fmb-card orders" href="<?php echo esc_url( wc_get_endpoint_url('orders','',$base) ); ?>">
      <div class="ic"></div>
      <div>
        <h3>Objednávky</h3>
        <p>Zobrazte detaily, spravujte vrácení a znovu objednejte oblíbené.</p>
      </div>
    </a>
    <a class="fmb-card downloads" href="<?php echo esc_url( wc_get_endpoint_url('downloads','',$base) ); ?>">
      <div class="ic"></div>
      <div>
        <h3>Stahování</h3>
        <p>Prohlédni si svoje stažené návrhy.</p>
      </div>
    </a>
    <a class="fmb-card wishlist" href="<?php echo esc_url( home_url('/wishlist/') ); ?>">
      <div class="ic"></div>
      <div>
        <h3>Oblíbené</h3>
        <p>Uložte si produkty na později a sdílejte je s přáteli.</p>
      </div>
    </a>
  </div>
  <?php
}, 20);
