<?php
// inc/frontend-display.php

if ( ! defined( 'ABSPATH' ) ) exit;

// 1) Shortcode
add_shortcode( 'influencer_grid', 'render_influencer_grid' );
function render_influencer_grid( $atts ) {
    // stačí all, category=slug
    $atts = shortcode_atts(['category'=>'all'], $atts, 'influencer_grid');
    // query
    $q = [
      'post_type'=>'influencer',
      'posts_per_page'=>-1
    ];
    if( $atts['category']!=='all' ) {
      $q['tax_query']=[[
        'taxonomy'=>'influencer_category',
        'field'=>'slug',
        'terms'=>sanitize_text_field($atts['category']),
      ]];
    }
    $posts = get_posts($q);
    if( empty($posts) ) {
      return '<p>Žádní influenceři</p>';
    }

    // 2) začátek wrapperu
    ob_start(); ?>
    <div class="youtubers-container">
      <div class="filters">
        <div class="filter-item active" data-filter="all">
          <div class="filter-icon">🌐</div><div class="filter-label">Vše</div>
        </div>
        <?php
        // dynamické kategorie
        $terms = get_terms(['taxonomy'=>'influencer_category','hide_empty'=>true]);
        foreach($terms as $term):
          $icon = get_term_meta($term->term_id,'influencer_category_icon',true) ?: '❔';
          ?>
          <div class="filter-item" data-filter="<?php echo esc_attr($term->slug) ?>">
            <div class="filter-icon">
              <img src="<?php echo esc_url($icon) ?>" style="width:100%;height:100%;object-fit:contain">
            </div>
            <div class="filter-label"><?php echo esc_html($term->name) ?></div>
          </div>
        <?php endforeach ?>
        <div class="filter-item reset-filter" data-filter="all" title="Zrušit filtr">
          <div class="filter-icon">×</div>
        </div>
      </div>

      <h2 class="dynamic-title">Best trending <span class="cats">Vše</span></h2>

      <div class="youtuber-list grid">
        <?php foreach($posts as $p):
          $cats = wp_get_post_terms($p->ID,'influencer_category');
          $slug = $cats? $cats[0]->slug : 'all';
          $bg   = $cats? get_term_meta($cats[0]->term_id,'influencer_category_background',true) : '';
          $att  = get_post_meta($p->ID,'influencer_portrait_id',true);
          $img  = $att? wp_get_attachment_image_url($att,'medium') : '';
        ?>
        <div class="youtuber-item <?php echo esc_attr($slug) ?>">
          <a href="<?php echo get_permalink($p) ?>">
            <div class="streamer-card"<?php if($bg): ?> style="background-image:url(<?php echo esc_url($bg) ?>)"<?php endif ?>>
              <?php if($img): ?>
                <img src="<?php echo esc_url($img) ?>" alt="<?php echo esc_attr($p->post_title) ?>">
              <?php endif ?>
              <div class="streamer-label">
                <?php echo esc_html($p->post_title) ?>
              </div>
            </div>
          </a>
        </div>
        <?php endforeach ?>
      </div>

      <button id="load-more" class="load-more-btn">
        <span class="lt-grey">zobrazit </span><span class="lt-red">další</span><span class="arrow-down"></span>
      </button>
      <button id="load-less" class="load-less-btn">
        <span class="lt-grey">zobrazit </span><span class="lt-red">méně</span><span class="arrow-down"></span>
      </button>
    </div>
    <?php
    return ob_get_clean();
}
