<?php
/**
 * inc/wc-admin-filters.php
 *
 * Streamerský svět view with two multi-select dropdowns.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1) Views (tabs)
add_filter( 'views_edit-product', function( $views ) {
    global $wpdb;
    $base = remove_query_arg( ['trash','paged'], admin_url('edit.php?post_type=product') );

    $inf_count   = (int)$wpdb->get_var("
      SELECT COUNT(DISTINCT p.ID)
      FROM {$wpdb->posts} p
      JOIN {$wpdb->term_relationships} tr ON p.ID=tr.object_id
      JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id=tt.term_taxonomy_id
      WHERE p.post_type='product' AND tt.taxonomy='pa_influencer'
    ");
    $eshop_count = (int)$wpdb->get_var("
      SELECT COUNT(DISTINCT p.ID)
      FROM {$wpdb->posts} p
      JOIN {$wpdb->term_relationships} tr ON p.ID=tr.object_id
      JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id=tt.term_taxonomy_id
      WHERE p.post_type='product' AND tt.taxonomy='pa_e-shop'
    ");

    $views['streamer_svet'] = sprintf(
      '<a href="%1$s&streamer_svet=1"%2$s>Streamerský svět <span class="count">(%3$d)</span></a>',
      esc_url($base),
      isset($_GET['streamer_svet']) ? ' class="current"' : '',
      $inf_count
    );
    $views['darky_pro_kazdeho'] = sprintf(
      '<a href="%1$s&darky_pro_kazdeho=1"%2$s>Dárky pro každého <span class="count">(%3$d)</span></a>',
      esc_url($base),
      isset($_GET['darky_pro_kazdeho']) ? ' class="current"' : '',
      $eshop_count
    );

    return $views;
} );

// 2) Query adjustments
add_action( 'pre_get_posts', function( $q ){
    if ( ! is_admin() || ! $q->is_main_query() || $q->get('post_type')!=='product' ) return;

    $tax_query = [];
    if ( isset($_GET['streamer_svet']) ) {
        $tax_query[] = ['taxonomy'=>'pa_influencer','operator'=>'EXISTS'];
    } elseif ( isset($_GET['darky_pro_kazdeho']) ) {
        $tax_query[] = ['taxonomy'=>'pa_e-shop','operator'=>'EXISTS'];
    }

    if ( ! empty($_GET['filter_influencer']) ) {
        $tax_query[] = [
          'taxonomy'=>'pa_influencer',
          'field'=>'slug',
          'terms'=>array_map('sanitize_text_field',(array)$_GET['filter_influencer']),
        ];
    }

    if ( ! empty($_GET['filter_kategorie']) ) {
        $cats = array_map('sanitize_text_field',(array)$_GET['filter_kategorie']);
        $infs = get_posts([
          'post_type'=>'influencer',
          'fields'=>'ids',
          'numberposts'=>-1,
          'tax_query'=>[[
            'taxonomy'=>'influencer_category',
            'field'=>'slug',
            'terms'=>$cats,
          ]]
        ]);
        if ( $infs ) {
          $slugs = array_map(function($id){
            return sanitize_title(get_the_title($id));
          }, $infs);
          $tax_query[] = [
            'taxonomy'=>'pa_influencer',
            'field'=>'slug',
            'terms'=>$slugs,
          ];
        }
    }

    if ( $tax_query ) {
        $q->set('tax_query', $tax_query);
    }
},15 );

// 3) Add our two multi-select dropdowns + hide default filters
add_action( 'restrict_manage_posts', function(){
    global $typenow;
    if ( $typenow!=='product' || ! isset($_GET['streamer_svet']) ) {
      return;
    }

    // Keep the Apply button only
    echo '<input type="hidden" name="streamer_svet" value="1">';

    // CSS: hide all the default WP filters and style our dropdowns & checkboxes
    ?>
    <style>
      /* hide default filters */
      label[for="filter-by-product_cat"],
      select[name="product_cat"],
      label[for="filter-by-product_type"],
      select[name="product_type"],
      label[for="filter-by-stock_status"],
      select[name="stock_status"],
      label[for="filter-by-brand"],
      select[name="brand"],
      label[for="filter-by-status"] { display:none !important; }

      /* dropdown container */
      .dd-wrap { display:inline-block; position:relative; margin-right:8px; }
      .dd-toggle { cursor:pointer; }
      .dd-panel {
        display:none; position:absolute; background:#fff; border:1px solid #ccc;
        padding:8px; max-height:240px; overflow:auto; min-width:280px; z-index:999;
      }
      /* tiny square checkboxes */
      .dd-panel input[type=checkbox] {
        width:12px; height:12px; margin:4px 6px 4px 0; vertical-align:middle;
      }
    </style>
    <?php

    // Influencer dropdown
    echo '<div class="dd-wrap">';
      echo '<button class="dd-toggle button">Influencer ▾</button>';
      echo '<div class="dd-panel">';
        foreach ( get_terms(['taxonomy'=>'pa_influencer','hide_empty'=>false]) as $t ){
          $slug = esc_attr($t->slug);
          $sel  = in_array($slug,(array)($_GET['filter_influencer']??[])) ? ' checked' : '';
          printf(
            '<label><input type="checkbox" name="filter_influencer[]" value="%1$s"%2$s><span>%3$s</span></label><br>',
            $slug, $sel, esc_html($t->name)
          );
        }
      echo '</div>';
    echo '</div>';

    // Kategorie dropdown
    echo '<div class="dd-wrap">';
      echo '<button class="dd-toggle button">Kategorie ▾</button>';
      echo '<div class="dd-panel">';
        foreach ( get_terms(['taxonomy'=>'influencer_category','hide_empty'=>false]) as $t ){
          $slug = esc_attr($t->slug);
          $sel  = in_array($slug,(array)($_GET['filter_kategorie']??[])) ? ' checked' : '';
          printf(
            '<label><input type="checkbox" name="filter_kategorie[]" value="%1$s"%2$s><span>%3$s</span></label><br>',
            $slug, $sel, esc_html($t->name)
          );
        }
      echo '</div>';
    echo '</div>';

    // JS: toggle panels & keep open until outside click
    ?>
    <script>
      (()=>{
        document.querySelectorAll('.dd-toggle').forEach(btn=>{
          btn.addEventListener('click', e=>{
            e.preventDefault();
            let pnl = btn.nextElementSibling;
            pnl.style.display = pnl.style.display==='block' ? 'none' : 'block';
          });
        });
        document.addEventListener('click', e=>{
          if (!e.target.closest('.dd-wrap')) {
            document.querySelectorAll('.dd-panel').forEach(d=>d.style.display='none');
          }
        });
      })();
    </script>
    <?php

},30 );

// 4) Custom columns (only in Streamerský svět)
add_filter( 'manage_edit-product_columns', function($cols){
    if ( empty($_GET['streamer_svet']) ) {
      return $cols;
    }
    return [
      'cb'         => $cols['cb'],
      'name'       => __( 'Name',''),       // will include thumbnail
      'influencer' => __( 'Influencer','' ),
      'kategorie'  => __( 'Kategorie',''  ),
      'price'      => $cols['price'],
      'date'       => $cols['date'],
    ];
},20 );

// 5) Fill new columns + show thumbnail in Name
add_action( 'manage_product_posts_custom_column', function($col, $id){
    if ( $col==='name' ) {
      // prepend product thumbnail
      $thumb = get_the_post_thumbnail($id,'thumbnail',[
        'style'=>'margin-right:8px;vertical-align:middle;width:48px;height:auto;object-fit:cover;'
      ]);
      echo $thumb;
    }
    if ($col==='influencer') {
        $names = wp_get_post_terms($id,'pa_influencer',['fields'=>'names']);
        echo $names ? esc_html(join(', ',$names)) : '—';
    }
    if ($col==='kategorie') {
        $names = wp_get_post_terms($id,'pa_influencer',['fields'=>'names']);
        if ($names) {
            $cpt = get_page_by_title($names[0],OBJECT,'influencer');
            if ($cpt) {
                $cats = wp_get_post_terms($cpt->ID,'influencer_category',['fields'=>'ids']);
                if ($cats) {
                    $icon = get_term_meta($cats[0],'influencer_category_icon',true);
                    if ($icon) {
                        printf('<img src="%s" width="24" style="object-fit:contain">',$icon);
                        return;
                    }
                }
            }
        }
        echo '—';
    }
},10,2 );

// 6) Make “Influencer” sortable A→Z
add_filter( 'manage_edit-product_sortable_columns', function($cols){
    if ( isset($_GET['streamer_svet']) ) {
        $cols['influencer'] = 'influencer_name';
    }
    return $cols;
});
add_filter( 'posts_clauses', function($clauses, $q){
    global $wpdb;
    if (
      is_admin() && $q->is_main_query()
      && isset($_GET['streamer_svet'])
      && $q->get('orderby')==='influencer_name'
    ) {
      $clauses['join'] .= "
        LEFT JOIN {$wpdb->term_relationships} tr ON {$wpdb->posts}.ID=tr.object_id
        LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id=tt.term_taxonomy_id
          AND tt.taxonomy='pa_influencer'
        LEFT JOIN {$wpdb->terms} t ON tt.term_id=t.term_id
      ";
      $clauses['groupby'] = "{$wpdb->posts}.ID";
      $ord = strtoupper($q->get('order'))==='DESC' ? 'DESC' : 'ASC';
      $clauses['orderby'] = "LOWER(t.name) {$ord}, {$wpdb->posts}.post_title ASC";
    }
    return $clauses;
},10,2 );
