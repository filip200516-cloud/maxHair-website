<?php
// themes/bricks/inc/mockups-register.php
if ( ! defined('ABSPATH') ) exit;

/**
 * 1) CPT: mockup + mockup_render
 */
add_action('init', function () {

  register_post_type('mockup', [
    'labels' => [
      'name'          => 'Mockupy',
      'singular_name' => 'Mockup',
      'menu_name'     => 'Mockupy',
      'add_new'       => 'Přidat mockup',
      'add_new_item'  => 'Přidat nový mockup',
      'edit_item'     => 'Upravit mockup',
      'all_items'     => 'Galerie mockupů',
      'search_items'  => 'Hledat mockupy',
    ],
    'public'       => false,
    'show_ui'      => true,
    'show_in_menu' => 'edit.php?post_type=product',
    'supports'     => ['title'],
  ]);

  register_post_type('mockup_render', [
    'labels' => [
      'name'          => 'Mockup rendery',
      'singular_name' => 'Mockup render',
      'add_new'       => 'Nový render',
      'all_items'     => 'Mockup rendery',
    ],
    'public'       => false,
    'show_ui'      => true,
    'show_in_menu' => 'edit.php?post_type=product',
    'supports'     => ['title','thumbnail'],
  ]);
});

/**
 * 2) Enqueue – admin
 */
add_action('admin_enqueue_scripts', function($hook){
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen) return;

  $dir_uri  = get_stylesheet_directory_uri() . '/inc';
  $dir_path = get_stylesheet_directory()     . '/inc';

  // Editor mockupu
  if ( ($hook === 'post.php' || $hook === 'post-new.php') && $screen->post_type === 'mockup' ) {
    wp_enqueue_media();
    if ( file_exists($dir_path.'/mockups-admin.css') ) {
      wp_enqueue_style('mockups-admin-css', $dir_uri.'/mockups-admin.css', [], @filemtime($dir_path.'/mockups-admin.css') ?: null);
    }
    wp_enqueue_script('jquery-ui-sortable');
    if ( file_exists($dir_path.'/mockups-admin.js') ) {
      wp_enqueue_script('mockups-admin-js', $dir_uri.'/mockups-admin.js', ['jquery','jquery-ui-sortable'], @filemtime($dir_path.'/mockups-admin.js') ?: null, true);
      wp_localize_script('mockups-admin-js', 'MOCKUP_ADMIN', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'i18n'     => ['add' => 'Přidat obrázky'],
      ]);
    }
  }

  // Editor produktu
  if ( ($hook === 'post.php' || $hook === 'post-new.php') && $screen->post_type === 'product' ) {
    wp_enqueue_media();
    if ( file_exists($dir_path.'/mockups-product.css') ) {
      wp_enqueue_style('mockups-product-css', $dir_uri.'/mockups-product.css', [], @filemtime($dir_path.'/mockups-product.css') ?: null);
    }
    if ( file_exists($dir_path.'/mockups-product.js') ) {
      wp_enqueue_script('mockups-product-js', $dir_uri.'/mockups-product.js', ['jquery'], @filemtime($dir_path.'/mockups-product.js') ?: null, true);
      wp_localize_script('mockups-product-js', 'MOCKUP_PROD', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('mockup_prod'),
        'i18n'     => [
          'add'             => 'Přidat obrázky',
          'rendering'       => 'Renderuji…',
          'render'          => 'Vyrenderovat',
          'preview_loading' => 'Načítám náhled…',
          'no_mockup'       => 'Není vybrán mockup.',
          'no_graphic'      => 'Nebyla vybrána grafika.',
        ],
      ]);
    }
  }

  // List „Mockupy“ – fallback grid
  if ($hook === 'edit.php' && $screen->post_type === 'mockup') {
    $list_css = $dir_path . '/mockups-admin-list.css';
    if ( file_exists($list_css) ) {
      wp_enqueue_style('mockups-admin-list', $dir_uri.'/mockups-admin-list.css', [], @filemtime($list_css) ?: null);
    } else {
      $css = '
        body.edit-php.post-type-mockup .wp-list-table thead,
        body.edit-php.post-type-mockup .wp-list-table tfoot{display:none;}
        body.edit-php.post-type-mockup .wp-list-table.widefat.fixed.striped tbody#the-list{
          display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin:12px;
        }
        body.edit-php.post-type-mockup .wp-list-table tbody#the-list tr{
          display:block;border:1px solid #e5e7eb;border-radius:8px;background:#fff;overflow:hidden;
        }
        body.edit-php.post-type-mockup .wp-list-table tbody#the-list tr td{display:block;padding:0;border:0;}
        body.edit-php.post-type-mockup .brx-mockup-card{text-align:center;padding:12px;}
        body.edit-php.post-type-mockup .brx-mockup-card .thumb{
          aspect-ratio:1/1;background:#f6f7f7;display:flex;align-items:center;justify-content:center;overflow:hidden;border-radius:6px;margin-bottom:8px;
        }
        body.edit-php.post-type-mockup .brx-mockup-card .thumb img{max-width:100%;max-height:100%;display:block;}
        body.edit-php.post-type-mockup .brx-mockup-card .title{font-weight:600;line-height:1.3;}
      ';
      wp_add_inline_style('common', $css);
    }
  }
});

/**
 * 3) Sloupce listu
 */
if (!function_exists('brx_mockup_first_image_url')) {
  function brx_mockup_first_image_url($post_id, $size = 'medium'){
    $json = get_post_meta($post_id, '_mockup_images_json', true);
    if (!is_string($json) || $json === '') return '';
    $arr = json_decode($json, true);
    if (!is_array($arr) || empty($arr)) return '';
    $first = $arr[0];
    $att_id = isset($first['id']) ? (int)$first['id'] : 0;
    $url    = isset($first['url']) ? (string)$first['url'] : '';
    if ($att_id) {
      $u = wp_get_attachment_image_url($att_id, $size);
      if ($u) return $u;
    }
    return $url;
  }
}
add_filter('manage_mockup_posts_columns', function($c){
  return ['cb' => '<input type="checkbox" />', 'card' => 'Mockup'];
}, 999);
add_action('manage_mockup_posts_custom_column', function($col, $post_id){
  if ($col !== 'card') return;
  $thumb = brx_mockup_first_image_url($post_id, 'medium') ?: '';
  $title = get_the_title($post_id);
  echo '<div class="brx-mockup-card"><div class="thumb">';
  if ($thumb) echo '<a href="'.esc_url(get_edit_post_link($post_id)).'"><img src="'.esc_url($thumb).'" alt=""></a>';
  else        echo '<a href="'.esc_url(get_edit_post_link($post_id)).'"><span style="color:#999">Bez obrázku</span></a>';
  echo '</div><div class="title"><a href="'.esc_url(get_edit_post_link($post_id)).'">'.esc_html($title).'</a></div></div>';
}, 10, 2);

/**
 * 4) Metaboxy (UI je v mockups-metabox.php)
 */
add_action('add_meta_boxes', function(){
  add_meta_box('mockup_gallery_box','Galerie Mockupů','brx_render_mockup_gallery_metabox','mockup','normal','high');
});
function brx_render_mockup_gallery_metabox($post){
  if ( ! function_exists('brx_render_mockup_gallery_metabox_inner') ) require_once __DIR__ . '/mockups-metabox.php';
  brx_render_mockup_gallery_metabox_inner($post);
}
add_action('add_meta_boxes', function(){
  add_meta_box('mockup_product_box','Mockup render',function($post){
    if ( ! function_exists('brx_render_mockups_metabox') ) require_once __DIR__ . '/mockups-metabox.php';
    brx_render_mockups_metabox($post);
  },'product','normal','high');
});

/**
 * 5) Ukládání meta
 */
add_action('save_post_mockup', function($post_id){
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  if (isset($_POST['mockup_images_json'])) {
    $raw  = wp_unslash($_POST['mockup_images_json']);
    $test = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) update_post_meta($post_id, '_mockup_images_json', wp_slash($raw));
  }
  if (isset($_POST['mockup_long_desc'])) {
    $html = wp_kses_post( wp_unslash($_POST['mockup_long_desc']) );
    update_post_meta($post_id, '_mockup_long_desc', $html);
  }
}, 10, 1);

add_action('save_post_product', function($pid){
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $pid)) return;

  if (isset($_POST['mockup_selected']))     update_post_meta($pid, '_mockup_selected', (int) $_POST['mockup_selected']);
  if (isset($_POST['mockup_graphic_id']))   update_post_meta($pid, '_mockup_graphic_id', (int) $_POST['mockup_graphic_id']);
  if (isset($_POST['mockup_size_mode'])) {
    $mode = in_array($_POST['mockup_size_mode'], ['small','medium','large','custom'], true) ? $_POST['mockup_size_mode'] : 'medium';
    update_post_meta($pid, '_mockup_size_mode', $mode);
  }
  if (isset($_POST['mockup_size_custom'])) {
    $pct = max(1, min(100, (int) $_POST['mockup_size_custom']));
    update_post_meta($pid, '_mockup_size_custom', $pct);
  }
  if (isset($_POST['mockup_sizes_json'])) {
    $raw  = wp_unslash($_POST['mockup_sizes_json']);
    $test = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) update_post_meta($pid, '_mockup_sizes_json', wp_slash($raw));
  }
}, 10, 1);

/**
 * 6) Schovej generované přílohy v Mediatéce
 */
add_filter('ajax_query_attachments_args', function($args){
  $args = is_array($args) ? $args : [];
  $args['meta_query'] = isset($args['meta_query']) && is_array($args['meta_query']) ? $args['meta_query'] : [];
  $args['meta_query'][] = ['key'=>'_mockup_generated','compare'=>'NOT EXISTS'];
  return $args;
});

/**
 * 7) Sloupce u mockup_render
 */
add_filter('manage_mockup_render_posts_columns', function($cols){ $cols['product']='Produkt'; $cols['mockup']='Mockup'; $cols['thumb']='Náhled'; return $cols;});
add_action('manage_mockup_render_posts_custom_column', function($col, $post_id){
  if ($col === 'product') { $pid=(int)get_post_meta($post_id,'_product_id',true); if($pid) echo '<a href="'.esc_url(get_edit_post_link($pid)).'">#'.$pid.' '.esc_html(get_the_title($pid)).'</a>'; }
  if ($col === 'mockup')  { $mid=(int)get_post_meta($post_id,'_mockup_id',true);  if($mid) echo '<a href="'.esc_url(get_edit_post_link($mid)).'">#'.$mid.' '.esc_html(get_the_title($mid)).'</a>'; }
  if ($col === 'thumb')   { $att=(int)get_post_meta($post_id,'_attachment_id',true); if($att) echo wp_get_attachment_image($att,[80,80]); }
},10,2);

/**
 * 8) Barvy – AJAX pro editor mockupu (vrací seznam dostupných barev)
 */
function brx_colors_fetch_all(){
  $out = [];

  // CPT "color"
  if (post_type_exists('color')) {
    $posts = get_posts([
      'post_type'      => 'color',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC',
      'fields'         => 'ids',
    ]);
    foreach ($posts as $pid){
      $name = get_the_title($pid);
      $hex  = get_post_meta($pid, '_color_hex', true);
      if (!$hex) $hex = get_post_meta($pid, 'hex', true);
      if (!$hex) $hex = get_post_meta($pid, 'color_hex', true);
      $slug = sanitize_title($name);
      $out[$slug] = ['source'=>'cpt','id'=>(int)$pid,'slug'=>$slug,'name'=>(string)$name,'hex'=>is_string($hex)?$hex:''];
    }
  }

  // Woo atribut pa_color (pokud existuje)
  if (taxonomy_exists('pa_color')) {
    $terms = get_terms(['taxonomy'=>'pa_color','hide_empty'=>false]);
    if (!is_wp_error($terms)) {
      foreach ($terms as $t) {
        if (!isset($out[$t->slug])) {
          $hex = get_term_meta($t->term_id, 'hex', true);
          if (!$hex) $hex = get_term_meta($t->term_id, 'color', true);
          $out[$t->slug] = ['source'=>'taxonomy','id'=>(int)$t->term_id,'slug'=>$t->slug,'name'=>$t->name,'hex'=>is_string($hex)?$hex:''];
        }
      }
    }
  }

  return array_values($out);
}
add_action('wp_ajax_brx_get_colors', function(){
  if (!current_user_can('edit_posts')) wp_send_json_error(['message'=>'Nedostatečná oprávnění.']);
  wp_send_json_success(['colors'=>brx_colors_fetch_all()]);
});
