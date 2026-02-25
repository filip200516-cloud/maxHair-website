<?php
// themes/bricks/inc/mockups-metabox.php
if ( ! defined('ABSPATH') ) exit;

/**
 * Metabox – CPT mockup (galerie + center + long description)
 */
function brx_render_mockup_gallery_metabox_inner($post){
  if (!$post || $post->post_type !== 'mockup') return;

  $json = get_post_meta($post->ID, '_mockup_images_json', true);
  if (!is_string($json) || $json === '') $json = '[]';

  $long_desc = get_post_meta($post->ID, '_mockup_long_desc', true);
  if (!is_string($long_desc)) $long_desc = '';
  ?>
  <div class="mockup-box">
    <p>Nahraj 1+ obrázků, přetahuj pořadí a klikem do náhledu nastav <em>center point</em> (X/Y v&nbsp;%). První v&nbsp;pořadí je výchozí.</p>
    <p><button type="button" class="button" id="mockup-add-images">Přidat obrázky</button></p>

    <ul id="mockup-images" data-initial="<?php echo esc_attr($json); ?>"></ul>
    <input type="hidden" id="mockup_images_json" name="mockup_images_json" value="<?php echo esc_attr($json); ?>">

    <hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

    <h3 style="margin:8px 0 6px;">Long description produktu</h3>
    <p class="description" style="margin-top:0">Doplní se do produktu při použití mockupu, pokud je popis prázdný.</p>
    <div style="margin-top:6px">
      <?php
      wp_editor(
        $long_desc,
        'mockup_long_desc',
        [
          'textarea_name' => 'mockup_long_desc',
          'textarea_rows' => 8,
          'media_buttons' => true,
          'teeny'         => false,
          'quicktags'     => true,
        ]
      );
      ?>
    </div>
  </div>
  <?php
}

/**
 * Metabox – produkt (výběr mockupu, grafika, velikosti, render)
 */
function brx_render_mockups_metabox($post){
  if (!$post || $post->post_type !== 'product') return;

  $post_id = (int)$post->ID;

  $selected_mockup = (int) get_post_meta($post_id, '_mockup_selected', true);
  $graphic_id      = (int) get_post_meta($post_id, '_mockup_graphic_id', true);
  $size_mode       =        get_post_meta($post_id, '_mockup_size_mode', true) ?: 'medium';
  $size_custom     = (int)  get_post_meta($post_id, '_mockup_size_custom', true) ?: 35;
  $sizes_json      =        get_post_meta($post_id, '_mockup_sizes_json', true);
  if (!is_string($sizes_json)) $sizes_json = '';

  $mockups = get_posts([
    'post_type'      => 'mockup',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
  ]);

  $graphic_url = $graphic_id ? ( wp_get_attachment_image_url($graphic_id, 'thumbnail') ?: wp_get_attachment_url($graphic_id) ) : '';
  ?>
  <div class="mockup-editor">
    <h2>Vyber mockup</h2>
    <div class="mockup-selector">
      <select id="mockup_selected" name="mockup_selected">
        <option value="">— Vyber mockup —</option>
        <?php foreach($mockups as $m): ?>
          <option value="<?php echo (int)$m->ID; ?>" <?php selected($selected_mockup, (int)$m->ID); ?>>
            <?php echo esc_html($m->post_title) . ' (#'.(int)$m->ID.')'; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="print-upload" style="margin-top:10px">
      <label><strong>Mockup Image upload (potisk)</strong></label>
      <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap">
        <input type="hidden" id="mockup_graphic_id" name="mockup_graphic_id" value="<?php echo (int)$graphic_id; ?>">
        <button type="button" class="button" id="mockup_pick_graphic">Vybrat / nahrát grafiku</button>
        <button type="button" class="button button-link-delete" id="mockup_remove_graphic">Odebrat</button>
        <div id="mockup_graphic_thumb">
          <?php if ($graphic_url): ?>
            <img src="<?php echo esc_url($graphic_url); ?>" alt="" style="max-width:120px;border:1px solid #ddd;border-radius:4px;">
          <?php else: ?>
            <span class="thumb-empty">(žádná)</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div style="margin-top:10px">
      <strong>Globální velikost potisku (default)</strong><br>
      <label><input type="radio" name="mockup_size_mode" value="small"  <?php checked($size_mode,'small');  ?>> Malý (~20%)</label>
      &nbsp; <label><input type="radio" name="mockup_size_mode" value="medium" <?php checked($size_mode,'medium'); ?>> Střední (~35%)</label>
      &nbsp; <label><input type="radio" name="mockup_size_mode" value="large"  <?php checked($size_mode,'large');  ?>> Velký (~50%)</label>
      &nbsp; <label><input type="radio" name="mockup_size_mode" value="custom" <?php checked($size_mode,'custom'); ?>> Vlastní (%)</label>
      &nbsp; <input type="number" id="mockup_size_custom" name="mockup_size_custom" value="<?php echo (int)$size_custom; ?>" min="1" max="100" step="1" style="width:90px">
      <p class="description">Per-image nastavení níže může tento default přepsat.</p>
    </div>

    <div id="mockup_multi_sizes_wrap" style="margin:12px 0">
      <h3 style="margin:8px 0">Velikost pro jednotlivé obrázky</h3>
      <div id="mockup_multi_sizes" data-saved="<?php echo esc_attr($sizes_json); ?>"></div>
      <input type="hidden" id="mockup_sizes_json" name="mockup_sizes_json" value="<?php echo esc_attr($sizes_json); ?>">
      <p class="description">Pro každý obrázek uvidíš náhled s potiskem v jeho zvolené velikosti.</p>
    </div>

    <p>
      <button type="button" class="button button-primary render-btn" id="mockup_render_btn" data-post="<?php echo (int)$post_id; ?>">
        Vyrenderovat všechny
      </button>
    </p>
  </div>
  <?php
}
