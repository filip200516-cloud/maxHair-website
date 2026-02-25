<?php
// themes/bricks/inc/mockups-product.php
if ( ! defined('ABSPATH') ) exit;

/* ----------------------- Mockup helpers ----------------------- */
function brx_mockup_get_images($mockup_id){
  $json = get_post_meta($mockup_id, '_mockup_images_json', true);
  if (!is_string($json) || $json==='') return [];
  $arr = json_decode($json, true); if (!is_array($arr)) return [];
  $out = [];
  foreach ($arr as $i=>$o){
    $id  = isset($o['id'])  ? (int)$o['id']  : 0;
    $url = isset($o['url']) ? (string)$o['url'] : '';
    if (!$url && $id) $url = wp_get_attachment_image_url($id,'large') ?: wp_get_attachment_url($id);
    if (!$url) continue;
    $out[] = [
      'id'=>$id,'url'=>$url,
      'x'=>isset($o['x'])?(float)$o['x']:50.0,
      'y'=>isset($o['y'])?(float)$o['y']:50.0,
      'index'=>(int)$i,
      'colors'=>(isset($o['colors'])&&is_array($o['colors']))?array_values(array_map('strval',$o['colors'])):[],
    ];
  }
  return $out;
}
function brx_mockup_local_path_from_url($url){
  $u = wp_upload_dir();
  if (!empty($u['baseurl']) && strpos($url,$u['baseurl'])===0) return $u['basedir'].substr($url, strlen($u['baseurl']));
  return '';
}
function brx_mockup_collect_color_slugs($mockup_id){
  $json = get_post_meta($mockup_id, '_mockup_images_json', true);
  if (!is_string($json) || $json==='') return [];
  $arr = json_decode($json, true); if (!is_array($arr)) return [];
  $uniq=[];
  foreach($arr as $o){
    if (!empty($o['colors']) && is_array($o['colors'])){
      foreach($o['colors'] as $slug){ $slug = sanitize_title((string)$slug); if ($slug!=='') $uniq[$slug]=1; }
    }
  }
  return array_keys($uniq);
}

/* ------------------- Woo attribute / terms -------------------- */
function brx_wc_ensure_color_attribute_registered(){
  if (!function_exists('wc_attribute_taxonomy_id_by_name')) return 0;
  $attr_id = wc_attribute_taxonomy_id_by_name('color');
  if ($attr_id){
    if (!taxonomy_exists('pa_color') && function_exists('wc_register_product_attributes')) wc_register_product_attributes();
    return (int)$attr_id;
  }
  if (!function_exists('wc_create_attribute')) return 0;
  $attr_id = wc_create_attribute([
    'slug' => 'color', 'name'=>'Color', 'type'=>'select', 'order_by'=>'menu_order', 'has_archives'=>false
  ]);
  if (is_wp_error($attr_id)) { error_log('[mockup] wc_create_attribute: '.$attr_id->get_error_message()); return 0; }
  if (function_exists('wc_register_product_attributes')) wc_register_product_attributes();
  return (int)$attr_id;
}

function brx_wc_get_or_create_color_term($slug, $name, $hex=''){
  $slug = sanitize_title($slug); if ($slug==='') return 0;
  if (!taxonomy_exists('pa_color')) brx_wc_ensure_color_attribute_registered();
  $term = get_term_by('slug',$slug,'pa_color');
  if (!$term){
    $res = wp_insert_term($name ?: ucfirst(str_replace(['-','_'],' ',$slug)), 'pa_color', ['slug'=>$slug]);
    if (is_wp_error($res)) { error_log('[mockup] insert term pa_color: '.$res->get_error_message()); return 0; }
    $term_id = (int)$res['term_id'];
  } else {
    $term_id = (int)$term->term_id;
  }
  if ($hex) update_term_meta($term_id, 'pa_color_hex', $hex);
  return $term_id;
}

/* -------------------- Product type switching ------------------ */
function brx_wc_force_variable($product_id){
  if (!function_exists('wc_get_product')) return false;

  // 1) taxonomy product_type
  if (taxonomy_exists('product_type')){
    wp_set_object_terms($product_id, 'variable', 'product_type', false);
    if (function_exists('wp_remove_object_terms')) {
      @wp_remove_object_terms($product_id, ['simple','grouped','external','subscription','bundle'], 'product_type');
    }
  }

  // 2) CRUD object
  $p = wc_get_product($product_id);
  if ($p && method_exists($p,'set_type')) { $p->set_type('variable'); $p->save(); }

  // 3) variable wrapper save (někdy nutné)
  if (class_exists('WC_Product_Variable')) { $v = new WC_Product_Variable($product_id); $v->save(); }

  // 4) cache
  if (function_exists('wc_delete_product_transients')) wc_delete_product_transients($product_id);
  clean_post_cache($product_id);

  // 5) verify
  $p2 = wc_get_product($product_id);
  return ($p2 && $p2->get_type()==='variable');
}

/* ------------- Build pa_color attribute + variations ----------- */
function brx_wc_setup_color_variations($product_id, $color_slugs){
  if (empty($color_slugs) || !function_exists('wc_get_product')) return false;

  $attr_id = brx_wc_ensure_color_attribute_registered();
  if (!$attr_id) return false;

  $product = wc_get_product($product_id);
  if (!$product) return false;

  // force variable
  if ($product->get_type()!=='variable') {
    brx_wc_force_variable($product_id);
    $product = wc_get_product($product_id); // reload
  }

  // create terms
  $term_ids=[]; $slugs=[]; 
  foreach ($color_slugs as $slug){
    $slug = sanitize_title($slug);
    $term = get_term_by('slug',$slug,'pa_color');
    $name = $term ? $term->name : ucfirst(str_replace(['-','_'],' ',$slug));
    $hex  = $term ? get_term_meta($term->term_id,'pa_color_hex',true) : '';
    $tid  = brx_wc_get_or_create_color_term($slug,$name,$hex);
    if ($tid){ $term_ids[]=$tid; $slugs[]=$slug; }
  }
  if (!$term_ids) return false;

  // attach taxonomy attribute to product (remove any custom color attribute leftovers)
  if (class_exists('WC_Product_Attribute')){
    $attrs = $product->get_attributes();
    foreach (array_keys($attrs) as $key){
      $lk = strtolower($key);
      if ($lk==='color' || $lk==='Color') unset($attrs[$key]);
    }
    $attr = new WC_Product_Attribute();
    $attr->set_id($attr_id);
    $attr->set_name('pa_color');
    $attr->set_options($term_ids);   // for taxonomy: IDs
    $attr->set_visible(true);
    $attr->set_variation(true);
    $attrs['pa_color'] = $attr;
    $product->set_attributes($attrs);
    $product->save();
  } else {
    $attrs = (array)get_post_meta($product_id,'_product_attributes',true);
    $attrs['pa_color'] = [
      'name'=>'pa_color',
      'value'=>implode(' | ', array_map(fn($tid)=>($t=get_term($tid)) ? $t->slug : '', $term_ids)),
      'position'=>0, 'is_visible'=>1, 'is_variation'=>1, 'is_taxonomy'=>1,
    ];
    update_post_meta($product_id,'_product_attributes',$attrs);
  }

  // ensure product has term relations (for swatche/filtrování)
  wp_set_object_terms($product_id, $slugs, 'pa_color', false);

  // create missing variations (slug-based)
  $existing = [];
  foreach ($product->get_children() as $child){
    $v = wc_get_product($child); if (!$v) continue;
    $slug = $v->get_meta('attribute_pa_color', true);
    if (!$slug) $slug = $v->get_attribute('pa_color');
    if ($slug) $existing[strtolower($slug)] = (int)$child;
  }
  $parent_price = $product->get_regular_price(); if ($parent_price==='') $parent_price = $product->get_price();

  foreach ($slugs as $slug){
    $key = strtolower($slug);
    if (isset($existing[$key])) continue;

    // create via wp_insert_post to be extra-safe
    $var_id = wp_insert_post([
      'post_type'=>'product_variation', 'post_status'=>'publish', 'post_parent'=>$product_id, 'menu_order'=>0,
    ], true);
    if (is_wp_error($var_id)) { error_log('[mockup] create variation: '.$var_id->get_error_message()); continue; }

    // set attribute both ways (meta + CRUD)
    update_post_meta($var_id, 'attribute_pa_color', $slug);
    if ($parent_price!=='') { update_post_meta($var_id, '_regular_price', $parent_price); update_post_meta($var_id, '_price', $parent_price); }

    if (class_exists('WC_Product_Variation')){
      $v = new WC_Product_Variation($var_id);
      $v->set_parent_id($product_id);
      $v->set_attributes(['pa_color'=>$slug]);
      if ($parent_price!=='') $v->set_regular_price($parent_price);
      $v->set_status('publish');
      $v->save();
    }
  }

  if (function_exists('wc_delete_product_transients')) wc_delete_product_transients($product_id);
  clean_post_cache($product_id);
  $product = wc_get_product($product_id); if ($product) $product->save();
  return true;
}

/* ----------- Map slug -> variation id (after creation) -------- */
function brx_wc_get_color_variation_map($product_id){
  $map=[]; if (!function_exists('wc_get_product')) return $map;
  $product = wc_get_product($product_id); if (!$product) return $map;
  foreach ($product->get_children() as $child){
    $v = wc_get_product($child); if (!$v) continue;
    $slug = $v->get_meta('attribute_pa_color', true);
    if (!$slug) $slug = $v->get_attribute('pa_color');
    if ($slug) $map[strtolower($slug)] = (int)$child;
  }
  return $map;
}

/* -------------------- AJAX: mockup data ----------------------- */
add_action('wp_ajax_get_mockup_data', function(){
  $mockup_id = isset($_POST['mockup_id']) ? (int)$_POST['mockup_id'] : 0;
  if (!$mockup_id) wp_send_json_error(['message'=>'Chybí mockup_id.']);
  $images = brx_mockup_get_images($mockup_id);
  if (!$images) wp_send_json_error(['message'=>'Mockup nemá žádný obrázek.']);
  $first = $images[0];
  $long_desc = wp_kses_post( (string) get_post_meta($mockup_id, '_mockup_long_desc', true) );
  wp_send_json_success([
    'images'=>$images,'base_url'=>$first['url'],'x'=>(float)$first['x'],'y'=>(float)$first['y'],'long_desc'=>$long_desc,
  ]);
});

/* ------------- AJAX: attachment src (grafika) ----------------- */
add_action('wp_ajax_get_attachment_src', function(){
  check_ajax_referer('mockup_prod','security');
  if (!current_user_can('upload_files')) wp_send_json_error(['message'=>'Nedostatečná oprávnění.']);
  $id = isset($_POST['attachment_id'])?(int)$_POST['attachment_id']:0;
  if (!$id) wp_send_json_error(['message'=>'Chybí attachment_id.']);
  $url = wp_get_attachment_image_url($id,'large') ?: wp_get_attachment_url($id);
  if (!$url) wp_send_json_error(['message'=>'Nepodařilo se získat URL.']);
  wp_send_json_success(['url'=>esc_url_raw($url)]);
});

/* ----------------- Attachments & thumbnails ------------------- */
function brx_mockup_insert_hidden_attachment($filepath,$fileurl,$parent=0){
  $filetype = wp_check_filetype(basename($filepath), null);
  $attachment = [
    'guid'=>$fileurl,'post_mime_type'=>$filetype['type']?:'image/png',
    'post_title'=>sanitize_file_name(basename($filepath)),'post_content'=>'','post_status'=>'inherit',
    'post_parent'=>(int)$parent,'post_type'=>'attachment',
  ];
  $attach_id = wp_insert_attachment($attachment,$filepath,$parent);
  if (is_wp_error($attach_id)) { error_log('[mockup] insert_attachment: '.$attach_id->get_error_message()); return 0; }
  require_once ABSPATH.'wp-admin/includes/image.php';
  $meta = wp_generate_attachment_metadata($attach_id,$filepath);
  if (!is_wp_error($meta) && $meta) wp_update_attachment_metadata($attach_id,$meta);
  update_post_meta($attach_id,'_mockup_generated',1);
  return (int)$attach_id;
}
function brx_mockup_set_featured_image($product_id,$attach_id){
  $product_id=(int)$product_id; $attach_id=(int)$attach_id; if(!$product_id||!$attach_id) return false;
  wp_update_post(['ID'=>$attach_id,'post_parent'=>$product_id,'post_status'=>'inherit','post_type'=>'attachment']);
  $file = get_attached_file($attach_id);
  if ($file && file_exists($file)){
    require_once ABSPATH.'wp-admin/includes/image.php';
    $meta = wp_generate_attachment_metadata($attach_id,$file);
    if (!is_wp_error($meta) && $meta) wp_update_attachment_metadata($attach_id,$meta);
  }
  update_post_meta($product_id,'_thumbnail_id',$attach_id);
  if (function_exists('wc_get_product')){
    $p = wc_get_product($product_id);
    if ($p){ $p->set_image_id($attach_id); $p->save(); }
  }
  clean_post_cache($product_id);
  return ((int)get_post_thumbnail_id($product_id)===$attach_id);
}

/* ----------------- (volitelné) log renderu -------------------- */
function brx_mockup_create_render_post($args){
  $post_id = wp_insert_post([
    'post_type'=>'mockup_render','post_status'=>'publish',
    'post_title'=>'Render '.(int)$args['product_id'].' • '.current_time('Y-m-d H:i:s'),
  ], true);
  if (is_wp_error($post_id)) { error_log('[mockup] insert render post: '.$post_id->get_error_message()); return 0; }
  update_post_meta($post_id,'_product_id',(int)$args['product_id']);
  update_post_meta($post_id,'_mockup_id',(int)$args['mockup_id']);
  update_post_meta($post_id,'_attachment_id',(int)$args['attachment_id']);
  update_post_meta($post_id,'_url',esc_url_raw($args['url']));
  if (!empty($args['attachment_id'])) set_post_thumbnail($post_id,(int)$args['attachment_id']);
  return (int)$post_id;
}

/* ----------------- Main AJAX: render & wire-up ---------------- */
add_action('wp_ajax_render_mockup_image', function(){
  check_ajax_referer('mockup_prod','security');

  $product_id  = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $mockup_id   = isset($_POST['mockup_id'])  ? (int)$_POST['mockup_id']  : 0;
  $graphic_id  = isset($_POST['graphic_id']) ? (int)$_POST['graphic_id'] : 0;
  $size_mode   = isset($_POST['size_mode'])  ? (string)$_POST['size_mode'] : 'medium';
  $size_custom = isset($_POST['size_custom'])? (float) $_POST['size_custom'] : 35.0;
  $sizes_json  = isset($_POST['sizes_json']) ? wp_unslash($_POST['sizes_json']) : '';

  if (!$product_id || !$mockup_id || !$graphic_id) wp_send_json_error(['message'=>'Chybí povinné parametry.']);
  if (!current_user_can('edit_post',$product_id)) wp_send_json_error(['message'=>'Nedostatečná oprávnění.']);

  // long desc fill (if empty)
  $desc_filled=false;
  $post = get_post($product_id);
  if ($post && $post->post_type==='product' && trim(wp_strip_all_tags($post->post_content))===''){
    $long = (string) get_post_meta($mockup_id,'_mockup_long_desc',true);
    if ($long!==''){ wp_update_post(['ID'=>$product_id,'post_content'=>$long]); $desc_filled=true; }
  }

  // variations (pa_color)
  $variations_done=false;
  try{
    $color_slugs = brx_mockup_collect_color_slugs($mockup_id);
    if ($color_slugs) $variations_done = brx_wc_setup_color_variations($product_id,$color_slugs);
  }catch(\Throwable $e){ error_log('[mockup] variations: '.$e->getMessage()); }

  $images = brx_mockup_get_images($mockup_id);
  if (!$images) wp_send_json_error(['message'=>'Mockup nemá žádné obrázky.']);

  $has_imagick = class_exists('Imagick');
  $has_gd = function_exists('imagecreatetruecolor') && function_exists('imagecreatefromstring');
  if (!$has_imagick && !$has_gd) wp_send_json_error(['message'=>'Na serveru není Imagick ani GD.']);

  $graphic_path = get_attached_file($graphic_id);
  if (!$graphic_path || !file_exists($graphic_path)) wp_send_json_error(['message'=>'Soubor grafiky nenalezen.']);

  // per-image sizes map
  $sizes_map=[];
  if (is_string($sizes_json) && $sizes_json!==''){
    $arr = json_decode($sizes_json,true);
    if (is_array($arr)){
      foreach($arr as $r){
        $pct = isset($r['pct'])?(float)$r['pct']:null; if ($pct===null) continue;
        if (!empty($r['id']))   $sizes_map['id:'.(int)$r['id']] = max(1,min(100,$pct));
        if (isset($r['index'])) $sizes_map['idx:'.(int)$r['index']] = max(1,min(100,$pct));
      }
    }
  }

  $upload = wp_upload_dir();
  if (!empty($upload['error'])) wp_send_json_error(['message'=>'Upload dir error: '.$upload['error']]);
  $subdir = 'mockups/'.gmdate('Y/m');
  $folder = trailingslashit($upload['basedir']).$subdir;
  if (!file_exists($folder) && !wp_mkdir_p($folder)) wp_send_json_error(['message'=>'Nelze vytvořit složku: '.$folder]);

  @ini_set('memory_limit','512M'); @set_time_limit(60);

  $results=[]; $attach_ids=[]; $index_to_attach=[]; $errors=[];

  foreach($images as $i=>$img){
    // pick percent
    if (isset($sizes_map['id:'.$img['id']])) $pct=$sizes_map['id:'.$img['id']];
    elseif (isset($sizes_map['idx:'.$i]))    $pct=$sizes_map['idx:'.$i];
    else {
      $pct = ($size_mode==='small'?20:($size_mode==='large'?50:($size_mode==='custom'?max(1,min(100,(float)$size_custom)):35)));
    }

    $base_path = $img['id'] ? get_attached_file($img['id']) : brx_mockup_local_path_from_url($img['url']);
    if (!$base_path || !file_exists($base_path)) { $errors[]="Base obrazek #$i nenalezen."; continue; }

    try{
      if ($has_imagick){
        $base=new Imagick($base_path); $graphic=new Imagick($graphic_path);
        try{$base->setImageAlphaChannel(Imagick::ALPHACHANNEL_OPAQUE);}catch(\Throwable $e){}
        try{$graphic->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);}catch(\Throwable $e){}
        $bw=$base->getImageWidth(); $bh=$base->getImageHeight();
        $gw=$graphic->getImageWidth(); $gh=$graphic->getImageHeight();
        $tw=max(1,(int)round(($pct/100)*$bw)); $scale=$tw/max(1,$gw); $th=max(1,(int)round($gh*$scale));
        $graphic->resizeImage($tw,$th,Imagick::FILTER_LANCZOS,1); $graphic->setImageFormat('png');
        $cx=($img['x']/100)*$bw; $cy=($img['y']/100)*$bh; $dx=(int)round($cx-$tw/2); $dy=(int)round($cy-$th/2);
        $base->setImageFormat('png'); $base->compositeImage($graphic,Imagick::COMPOSITE_OVER,$dx,$dy);
        $filename='mockup-'.$product_id.'-'.$i.'-'.time().'.png';
        $filepath=trailingslashit($folder).$filename; $base->writeImage($filepath); $base->clear(); $graphic->clear();
      }else{
        $base=@imagecreatefromstring(file_get_contents($base_path));
        $graphic=@imagecreatefromstring(file_get_contents($graphic_path));
        if(!$base||!$graphic){ $errors[]="GD load selhal (#$i)."; continue; }
        imagesavealpha($base,true); imagealphablending($base,true); imagesavealpha($graphic,true);
        $bw=imagesx($base); $bh=imagesy($base); $gw=imagesx($graphic); $gh=imagesy($graphic);
        $tw=max(1,(int)round(($pct/100)*$bw)); $scale=$tw/max(1,$gw); $th=max(1,(int)round($gh*$scale));
        $res=imagecreatetruecolor($tw,$th); imagesavealpha($res,true); imagealphablending($res,false);
        $tr=imagecolorallocatealpha($res,0,0,0,127); imagefilledrectangle($res,0,0,$tw,$th,$tr);
        imagecopyresampled($res,$graphic,0,0,0,0,$tw,$th,$gw,$gh); imagedestroy($graphic); $graphic=$res;
        imagealphablending($graphic,true); imagesavealpha($graphic,true);
        $cx=($img['x']/100)*$bw; $cy=($img['y']/100)*$bh; $dx=(int)round($cx-$tw/2); $dy=(int)round($cy-$th/2);
        imagecopy($base,$graphic,$dx,$dy,0,0,$tw,$th);
        $filename='mockup-'.$product_id.'-'.$i.'-'.time().'.png';
        $filepath=trailingslashit($folder).$filename; imagepng($base,$filepath); imagedestroy($base); imagedestroy($graphic);
      }

      $fileurl=trailingslashit($upload['baseurl']).$subdir.'/'.$filename;
      $aid=brx_mockup_insert_hidden_attachment($filepath,$fileurl,$product_id);
      if ($aid){
        $attach_ids[]=$aid; $index_to_attach[$i]=(int)$aid;
        $results[]=['index'=>$i,'url'=>esc_url_raw($fileurl),'attachment'=>(int)$aid,'percent'=>(float)$pct];
        if (post_type_exists('mockup_render')){
          brx_mockup_create_render_post(['product_id'=>$product_id,'mockup_id'=>$mockup_id,'attachment_id'=>$aid,'url'=>$fileurl]);
        }
      }
    }catch(\Throwable $e){ $errors[]="Render selhal (#$i): ".$e->getMessage(); }
  }

  // featured + gallery
  $featured_ok=false;
  if ($attach_ids){
    $featured_ok = brx_mockup_set_featured_image($product_id,(int)$attach_ids[0]);
    if (function_exists('wc_get_product')){
      $p = wc_get_product($product_id);
      if ($p){
        $existing = array_map('intval',(array)$p->get_gallery_image_ids());
        $keep=[]; foreach($existing as $eid){ if(!(bool)get_post_meta($eid,'_mockup_generated',true)) $keep[]=$eid; }
        $p->set_gallery_image_ids(array_values(array_unique(array_merge($keep,array_map('intval',array_slice($attach_ids,1))))));
        $p->save();
      }
    }
  }

  // assign variation images (slug → var id) — RELOAD map after creation
  $variation_images_set=[];
  try{
    if (function_exists('wc_delete_product_transients')) wc_delete_product_transients($product_id);
    clean_post_cache($product_id);
    $var_map = brx_wc_get_color_variation_map($product_id); // slug => variation_id
    foreach($images as $i=>$img){
      if (empty($img['colors']) || !isset($index_to_attach[$i])) continue;
      foreach($img['colors'] as $slug){
        $slug = sanitize_title((string)$slug);
        if (isset($var_map[$slug])){
          $vid=(int)$var_map[$slug]; $aid=(int)$index_to_attach[$i];
          if (function_exists('wc_get_product')){
            $v = wc_get_product($vid);
            if ($v && method_exists($v,'set_image_id')){ $v->set_image_id($aid); $v->save(); }
            else { update_post_meta($vid,'_thumbnail_id',$aid); }
          } else { update_post_meta($vid,'_thumbnail_id',$aid); }
          $variation_images_set[]=$vid;
        }
      }
    }
  }catch(\Throwable $e){ error_log('[mockup] set var images: '.$e->getMessage()); }

  if (function_exists('wc_delete_product_transients')) wc_delete_product_transients($product_id);

  wp_send_json_success([
    'message'=>'Mockupy vygenerovány. Produkt přepnut na Variable, vytvořeny varianty dle pa_color a přiřazeny obrázky.',
    'renders'=>$results,'errors'=>$errors,
    'featured_set'=>(bool)$featured_ok,
    'thumbnail_id'=>(int)get_post_thumbnail_id($product_id),
    'desc_filled'=>(bool)$desc_filled,
    'variations_done'=>(bool)$variations_done,
    'variation_images_set'=>array_values(array_unique(array_map('intval',$variation_images_set))),
  ]);
});

/* ------------- Hide internal color options on frontend -------- */
if (!function_exists('brx_hidden_color_slugs')){
  function brx_hidden_color_slugs(){ return apply_filters('brx_hidden_color_slugs',['e-shop','eshop','e_shop']); }
}
add_filter('woocommerce_dropdown_variation_attribute_options_args', function($args){
  if (is_admin() && !wp_doing_ajax()) return $args;
  if (empty($args['options']) || !is_array($args['options'])) return $args;
  $attr = isset($args['attribute'])?(string)$args['attribute']:'';
  if (stripos($attr,'pa_color')===false && stripos($attr,'color')===false) return $args;

  $hidden = brx_hidden_color_slugs(); $filtered=[];
  foreach($args['options'] as $opt){
    // taxonomy může posílat ID
    if (is_numeric($opt)){ $t=get_term((int)$opt,'pa_color'); $slug=$t?$t->slug:''; }
    else { $slug=sanitize_title((string)$opt); }
    if (in_array($slug,$hidden,true)) continue;
    $filtered[]=$opt;
  }
  $args['options']=$filtered; return $args;
},10,1);

add_filter('woocommerce_variation_is_visible', function($visible,$variation_id){
  if (is_admin()) return $visible;
  if (!function_exists('wc_get_product')) return $visible;
  $v = wc_get_product($variation_id); if (!$v) return $visible;
  $slug = $v->get_meta('attribute_pa_color',true); if (!$slug) $slug=$v->get_attribute('pa_color');
  $slug = sanitize_title((string)$slug);
  if (in_array($slug, brx_hidden_color_slugs(), true)) return false;
  return $visible;
},10,2);
