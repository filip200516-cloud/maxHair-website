<?php
/**
 * Filtry: taxonomie + term meta + admin stránka + CSV import + metabox + AJAX
 */
defined('ABSPATH') || exit;

/* =========================================================
 * 1) TAXONOMIES
 * =======================================================*/
add_action('init', function () {
  $common = [
    'public'            => true,
    'show_ui'           => true,
    'show_in_menu'      => true,
    'show_in_rest'      => true,     // blokový editor / REST
    'hierarchical'      => true,
    'show_admin_column' => true,
    'rewrite'           => ['slug' => 'gift'],
    'meta_box_cb'       => false,    // default WP metabox nevytvářej
  ];

  register_taxonomy('gift_audience', ['product'], array_merge($common, [
    'labels' => [
      'name'          => 'Dárky pro (Audience)',
      'singular_name' => 'Audience',
      'menu_name'     => 'Dárky pro',
    ],
  ]));

  register_taxonomy('gift_type', ['product'], array_merge($common, [
    'labels' => [
      'name'          => 'Typ dárku',
      'singular_name' => 'Typ dárku',
      'menu_name'     => 'Typ dárku',
    ],
  ]));

  register_taxonomy('gift_theme', ['product'], array_merge($common, [
    'labels' => [
      'name'          => 'Témata dárků',
      'singular_name' => 'Téma',
      'menu_name'     => 'Témata',
    ],
  ]));
});

/* Pro jistotu skryj default boxy i kdyby nějaké zůstaly z cache/plug-inů */
add_action('add_meta_boxes_product', function () {
  remove_meta_box('gift_audiencediv', 'product', 'side');
  remove_meta_box('gift_typediv',     'product', 'side');
  remove_meta_box('gift_themediv',    'product', 'side');
}, 99);

/* =========================================================
 * 2) TERM META (ikona, pořadí, skrytí)
 * =======================================================*/
function es_gift_term_fields($term, $taxonomy){
  $icon   = get_term_meta($term->term_id, 'icon', true);
  $hidden = (bool) get_term_meta($term->term_id, 'hidden', true);
  $order  = (int)  get_term_meta($term->term_id, 'order', true); ?>
  <tr class="form-field">
    <th scope="row"><label for="es_icon">Ikona (emoji / třída / URL)</label></th>
    <td><input name="es_icon" id="es_icon" type="text" value="<?php echo esc_attr($icon); ?>" class="regular-text"></td>
  </tr>
  <tr class="form-field">
    <th scope="row"><label for="es_order">Pořadí</label></th>
    <td><input name="es_order" id="es_order" type="number" value="<?php echo esc_attr($order); ?>" class="small-text"> <em>(nižší = dřív)</em></td>
  </tr>
  <tr class="form-field">
    <th scope="row"><label for="es_hidden">Skrýt ve filtru</label></th>
    <td><label><input name="es_hidden" id="es_hidden" type="checkbox" <?php checked($hidden); ?>> Nezobrazovat</label></td>
  </tr>
<?php }
function es_gift_term_fields_add($taxonomy){ ?>
  <div class="form-field">
    <label for="es_icon">Ikona (emoji / třída / URL)</label>
    <input name="es_icon" id="es_icon" type="text" value="" class="regular-text">
  </div>
  <div class="form-field">
    <label for="es_order">Pořadí</label>
    <input name="es_order" id="es_order" type="number" value="0" class="small-text">
  </div>
  <div class="form-field">
    <label><input name="es_hidden" id="es_hidden" type="checkbox"> Nezobrazovat</label>
  </div>
<?php }
foreach (['gift_audience','gift_type','gift_theme'] as $tax){
  add_action("{$tax}_add_form_fields", function() use ($tax){ es_gift_term_fields_add($tax); });
  add_action("{$tax}_edit_form_fields", function($term) use ($tax){ es_gift_term_fields($term, $tax); }, 10, 2);
}
add_action('created_term', 'es_gift_save_term_meta', 10, 3);
add_action('edited_term',  'es_gift_save_term_meta', 10, 3);
function es_gift_save_term_meta($term_id, $tt_id, $taxonomy){
  if (! in_array($taxonomy, ['gift_audience','gift_type','gift_theme'], true)) return;
  if (isset($_POST['es_icon']))   update_term_meta($term_id, 'icon', sanitize_text_field($_POST['es_icon']));
  update_term_meta($term_id, 'hidden', !empty($_POST['es_hidden']) ? 1 : 0);
  if (isset($_POST['es_order']))  update_term_meta($term_id, 'order', (int) $_POST['es_order']);
}

/* =========================================================
 * 3) ADMIN – Jedna stránka „Filtry“ pod Produkty
 *    - Seed
 *    - Import CSV
 *    - Přehled termů
 * =======================================================*/
add_action('admin_menu', function () {
  add_submenu_page(
    'edit.php?post_type=product',
    'Filtry',
    'Filtry',
    'manage_woocommerce',
    'es-filters',
    'es_render_filters_admin_page',
    30
  );

  // schovej taxonomy submenus, ponecháme jen „Filtry“
  remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=gift_audience&post_type=product');
  remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=gift_type&post_type=product');
  remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=gift_theme&post_type=product');
});

/** vykreslení admin stránky */
function es_render_filters_admin_page(){
  if (!current_user_can('manage_woocommerce')) wp_die('Insufficient permissions.');
  $seeded   = isset($_GET['seeded']);
  $imported = isset($_GET['imported']); ?>
  <div class="wrap">
    <h1>Filtry</h1>

    <?php if ($seeded): ?>
      <div class="notice notice-success"><p>Seed hotový. Termíny byly vloženy.</p></div>
    <?php endif; ?>
    <?php if ($imported): ?>
      <div class="notice notice-success"><p>Import CSV dokončen.</p></div>
    <?php endif; ?>

    <h2>Hromadné akce</h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <?php wp_nonce_field('es_filters_actions','es_nonce'); ?>
      <input type="hidden" name="action" value="es_seed_filters">
      <p><button class="button button-primary" type="submit">Seedovat předdefinované termíny</button></p>
    </form>

    <hr>

    <h2>Import z CSV</h2>
    <p>CSV hlavička: <code>taxonomy,name,slug,parent,icon,order,hidden</code></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
      <?php wp_nonce_field('es_filters_import','es_import_nonce'); ?>
      <input type="hidden" name="action" value="es_import_filters">
      <input type="file" name="csv" accept=".csv" required>
      <button type="submit" class="button">Importovat CSV</button>
    </form>

    <hr>

    <h2>Přehled</h2>
    <div style="display:flex;gap:32px;flex-wrap:wrap">
      <?php foreach (['gift_audience'=>'Dárky pro','gift_type'=>'Typ dárku','gift_theme'=>'Témata'] as $tax=>$label): ?>
        <div>
          <h3><?php echo esc_html($label); ?></h3>
          <ul>
          <?php
            $terms = get_terms(['taxonomy'=>$tax,'hide_empty'=>false,'orderby'=>'meta_value_num','meta_key'=>'order','order'=>'ASC']);
            if (!is_wp_error($terms) && $terms){
              foreach($terms as $t){
                $hidden = get_term_meta($t->term_id,'hidden',true) ? ' (skryto)' : '';
                printf('<li>%s%s</li>', esc_html($t->name), esc_html($hidden));
              }
            } else {
              echo '<li><em>Žádné termíny</em></li>';
            }
          ?>
          </ul>
          <p><a class="button" href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy='.$tax.'&post_type=product')); ?>">Spravovat</a></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php }

/** admin_post: Seed (vloží default termy) */
add_action('admin_post_es_seed_filters', function () {
  if (!current_user_can('manage_options')) wp_die('Insufficient permissions.');
  check_admin_referer('es_filters_actions', 'es_nonce');

  // audiences
  $audiences = ['Dámské','Pánské','Dětské','LGBT','Páry','Mazlíčci','Bez potisku','OUTLET %'];
  foreach ($audiences as $i=>$name){
    if (!term_exists($name,'gift_audience')) {
      $t = wp_insert_term($name,'gift_audience');
      if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
    }
  }
  // types
  $types = ['Oblečení','Produkty','Služby'];
  foreach ($types as $i=>$name){
    if (!term_exists($name,'gift_type')) {
      $t = wp_insert_term($name,'gift_type');
      if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
    }
  }
  // themes
  $themes = [
    'Dárky pro maminku','Dárky pro ségru','Dárky pro babičku','Dárky pro tátu','Dárky pro bráchu','Dárky pro dědu',
    'Dárky pro partnera','Dárky pro partnerku','Dárky pro přátele','Dárky pro učitele','Láska','Sport','Auta','Motorky',
    'Dětské','Hlášky','Humor','Hudba & Film','Grilování','Vodáci','Formule 1','Yoga a Fitness',
    'Rozlučka se svobodou','Rybáři','Cyklistická','Svatba','Politika','Koně','Pejskové','Kočičky',
    'Alkohol','Drogy','Hokej','Fotbal','Golf'
  ];
  foreach ($themes as $i=>$name){
    if (!term_exists($name,'gift_theme')) {
      $t = wp_insert_term($name,'gift_theme');
      if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
    }
  }

  wp_safe_redirect(add_query_arg(['page'=>'es-filters','seeded'=>'1'], admin_url('edit.php?post_type=product')));
  exit;
});

/** admin_post: Import CSV */
add_action('admin_post_es_import_filters', function () {
  if (!current_user_can('manage_woocommerce')) wp_die('Insufficient permissions.');
  check_admin_referer('es_filters_import', 'es_import_nonce');

  if (empty($_FILES['csv']['name'])) wp_die('Soubor nebyl nahrán.');

  require_once ABSPATH . 'wp-admin/includes/file.php';
  $overrides = ['test_form' => false, 'mimes' => ['csv'=>'text/csv','txt'=>'text/plain']];
  $file = wp_handle_upload($_FILES['csv'], $overrides);
  if (isset($file['error'])) wp_die('Upload selhal: ' . esc_html($file['error']));
  $path = $file['file'];

  if (($fh = fopen($path, 'r')) === false) wp_die('Nelze číst CSV.');
  $header = fgetcsv($fh, 0, ','); // taxonomy,name,slug,parent,icon,order,hidden

  while (($row = fgetcsv($fh, 0, ',')) !== false) {
    $data = array_combine($header, $row);
    if (!$data) continue;

    $taxonomy = trim($data['taxonomy'] ?? '');
    $name     = trim($data['name'] ?? '');
    if (!$taxonomy || !$name) continue;
    if (!in_array($taxonomy, ['gift_audience','gift_type','gift_theme'], true)) continue;

    $args = [];
    if (!empty($data['slug']))  $args['slug'] = sanitize_title($data['slug']);
    if (!empty($data['parent'])){
      $parent_term = get_term_by('name', $data['parent'], $taxonomy);
      if ($parent_term && !is_wp_error($parent_term)) $args['parent'] = (int)$parent_term->term_id;
    }

    $exists = term_exists($name, $taxonomy);
    if ($exists && is_array($exists)) {
      $term_id = (int)$exists['term_id'];
      if (!empty($args['slug'])) wp_update_term($term_id, $taxonomy, ['name'=>$name,'slug'=>$args['slug']]);
    } else {
      $insert = wp_insert_term($name, $taxonomy, $args);
      if (is_wp_error($insert)) continue;
      $term_id = (int)$insert['term_id'];
    }

    if (isset($data['icon']))  update_term_meta($term_id,'icon', sanitize_text_field($data['icon']));
    if (isset($data['order'])) update_term_meta($term_id,'order', (int)$data['order']);
    if (isset($data['hidden']))update_term_meta($term_id,'hidden', (int)!!$data['hidden']);
  }
  fclose($fh);

  wp_safe_redirect(add_query_arg(['page'=>'es-filters','imported'=>'1'], admin_url('edit.php?post_type=product')));
  exit;
});


// 4) === METABOX "Filtry" – opravené vykreslení bez duplikací ===
add_action('add_meta_boxes', function(){
  add_meta_box('es_filters_box', 'Filtry', 'es_render_filters_metabox', 'product', 'side', 'default');
});

function es_render_filters_metabox($post){
  wp_nonce_field('es_filters_metabox','es_filters_metabox_nonce');

  $taxes = [
    'gift_audience' => 'Dárky pro',
    'gift_type'     => 'Typ dárku',
    'gift_theme'    => 'Témata',
  ];

  echo '<div class="es-filters-meta">';
  foreach ($taxes as $tax => $label) {

    // skrytý input pro možnost "vyčistit všechny termy"
    echo '<input type="hidden" name="tax_input['.$tax.'][]" value="0" />';

    // vygeneruj HTML checklistu bez přímého výstupu
    $checklist = wp_terms_checklist(
      $post->ID,
      [
        'taxonomy'       => $tax,
        'checked_ontop'  => false,
        'echo'           => false,  // << KLÍČOVÉ
      ]
    );

    echo '<div class="es-filters-meta__group">';
      echo '<strong style="display:block;margin:6px 0 4px;">'.esc_html($label).'</strong>';
      echo '<div class="categorydiv">'.$checklist.'</div>'; // nepřidávej vlastní <ul>
    echo '</div>';
  }
  echo '</div>';
}

add_action('save_post_product', function($post_id){
  if (!isset($_POST['es_filters_metabox_nonce']) || !wp_verify_nonce($_POST['es_filters_metabox_nonce'],'es_filters_metabox')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_product', $post_id)) return;
  // ukládání provede WP přes tax_input[]
}, 10, 1);


/* =========================================================
 * 5) FRONTEND AJAX – načtení produktů pro embedded UI
 *    (VRACÍ POUZE KARTY S TŘÍDAMI giftp-*, BEZ WRAPPERU GRIDU)
 * =======================================================*/
add_action('wp_ajax_es_filter_products', 'es_filter_products');
add_action('wp_ajax_nopriv_es_filter_products', 'es_filter_products');

function es_filter_products(){
  check_ajax_referer('es_gift_filter','nonce');

  // nasbíráme všechny taxonomy z POSTu: gift_audience, gift_type, gift_theme + případně pa_*
  $tax_query = ['relation' => 'AND'];

  foreach ($_POST as $key => $val){
    if (!is_array($val)) continue;
    if (!taxonomy_exists($key)) continue;

    $ids = array_map('intval', $val);
    if ($ids){
      $tax_query[] = [
        'taxonomy' => $key,
        'field'    => 'term_id',
        'terms'    => $ids,
        'operator' => 'IN'
      ];
    }
  }

  // Omez na produkty, které mají nějakou hodnotu atributu pa_e-shop
  $eshop_slugs = get_terms(['taxonomy'=>'pa_e-shop','fields'=>'slugs']);
  if (!is_wp_error($eshop_slugs) && !empty($eshop_slugs)){
    $tax_query[] = [
      'taxonomy' => 'pa_e-shop',
      'field'    => 'slug',
      'terms'    => $eshop_slugs,
      'operator' => 'IN',
    ];
  }

  // Vytáhneme produkty
  $products = wc_get_products([
    'status'    => 'publish',
    'limit'     => 24,
    'paginate'  => false,
    'tax_query' => $tax_query
  ]);

  // === HTML výstup – POUZE KARTY S giftp-* TŘÍDAMI (BEZ OBALOVEHO GRIDU) ===
  ob_start();

  if (!empty($products)){
    foreach ($products as $product) {
      $permalink = $product->get_permalink();

      // hlavní obrázek
      $img_id       = $product->get_image_id();
      $original_src = $img_id ? wp_get_attachment_image_url($img_id,'medium') : wc_placeholder_img_src();

      // map varianta -> obrázek podle barvy
      $variation_map = [];
      if ( $product->is_type('variable') ) {
        foreach( $product->get_available_variations() as $var ){
          $slug = $var['attributes']['attribute_pa_color'] ?? '';
          if ( $slug && ! isset( $variation_map[ $slug ] ) && $var['image_id'] ) {
            $variation_map[ $slug ] = wp_get_attachment_image_url( $var['image_id'], 'medium' );
          }
        }
      }

      // všechny termy pa_color na produktu
      $color_terms = wp_get_post_terms( $product->get_id(), 'pa_color' );
      ?>
      <div class="giftp-card">
        <a class="giftp-link" href="<?php echo esc_url( $permalink ); ?>">
          <img class="giftp-img"
               src="<?php echo esc_url( $original_src ); ?>"
               data-original="<?php echo esc_attr( $original_src ); ?>"
               alt="<?php echo esc_attr( $product->get_name() ); ?>">
          <div class="giftp-title"><?php echo esc_html( $product->get_name() ); ?></div>
        </a>

        <?php if ( ! empty( $color_terms ) ) : ?>
          <div class="giftp-swatches">
            <?php foreach( $color_terms as $ct ) :
              $hex = get_term_meta( $ct->term_id, 'pa_color_hex', true );
              if ( ! $hex ) $hex = '#ccc';

              $img = $variation_map[ $ct->slug ] ?? $original_src;
              $vurl = add_query_arg( 'attribute_pa_color', $ct->slug, $permalink );
            ?>
              <div class="giftp-swatch"
                   style="background-color:<?php echo esc_attr( $hex ); ?>;"
                   data-img="<?php echo esc_attr( $img ); ?>"
                   data-url="<?php echo esc_attr( $vurl ); ?>"
                   title="<?php echo esc_attr( $ct->name ); ?>">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php
    }
  } else {
    echo '<p>Žádné produkty.</p>';
  }

  $html = ob_get_clean();
  wp_send_json_success(['html' => $html]);
}

/** ====== Sloučení sloupců v přehledu produktů do jednoho "Filtry" ====== */
add_filter('manage_edit-product_columns', function($columns){
    // odeber auto-sloupce taxonomií (WordPress je přidá, když má taxonomie show_admin_column=1)
    unset($columns['taxonomy-gift_audience']);
    unset($columns['taxonomy-gift_type']);
    unset($columns['taxonomy-gift_theme']);

    // vlož nový sloupec "Filtry" (za "Kategorie" nebo hned za název)
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        // vlož po "product_cat" (Kategorie) pokud existuje, jinak po "name"
        if ($key === 'product_cat' || ($key === 'name' && !isset($columns['product_cat']))) {
            $new['gift_filters'] = __('Filtry', 'bricks');
        }
    }
    return $new;
}, 50);

add_action('manage_product_posts_custom_column', function($column, $post_id){
    if ($column !== 'gift_filters') return;

    $taxes = [
        'gift_audience' => 'Dárky pro',
        'gift_type'     => 'Typ',
        'gift_theme'    => 'Téma',
    ];

    $out = [];
    foreach ($taxes as $tax => $label){
        $terms = get_the_terms($post_id, $tax);
        if (is_wp_error($terms) || empty($terms)) continue;

        // udělej z nich malé „badge“
        $badges = array_map(function($t){
            return '<span class="gf-badge" title="'.esc_attr($t->name).'">'.esc_html($t->name).'</span>';
        }, $terms);

        $out[] = '<div class="gf-row"><strong>'.esc_html($label).':</strong> '.implode(' ', $badges).'</div>';
    }

    if ($out){
        echo '<div class="gf-wrap">'.implode('', $out).'</div>';
    } else {
        echo '<span class="gf-empty">—</span>';
    }
}, 10, 2);

// Admin CSS jen na seznamu produktů
add_action('admin_head-edit.php', function () {
    if (empty($_GET['post_type']) || $_GET['post_type'] !== 'product') return; ?>
    <style>
      /* Sloupec Filtry */
      th.column-gift_filters { width: 320px; }
      .column-gift_filters .gf-wrap{ display:block; line-height:1.45; }
      .column-gift_filters .gf-row{ margin:2px 0 6px; }
      .column-gift_filters .gf-badge{
        display:inline-block;
        background:#f6f7f7;
        border:1px solid #dcdcdc;
        border-radius:12px;
        padding:2px 8px;
        margin:2px 3px 0 0;
        font-size:11px;
        white-space:nowrap;
      }
      .column-gift_filters .gf-empty{ color:#aaa; }
      /* Zajisti zalamování v buňce, ať se nedeformuje tabulka */
      .wp-list-table .column-gift_filters{ white-space: normal; }
    </style>
<?php });

/* Volitelné: pokud chceš, aby se ty 3 auto-sloupce netvořily vůbec, přepni registraci taxonomií:
   'show_admin_column' => false
   (Ale výše uvedené unset() je odstraní i bez toho.) */
