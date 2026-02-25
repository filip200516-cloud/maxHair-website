<?php
/**
 * Live Search Handler for Products & Influencers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_ajax_fmb_live_search', 'fmb_live_search_handler');
add_action('wp_ajax_nopriv_fmb_live_search', 'fmb_live_search_handler');

function fmb_live_search_handler() {
    check_ajax_referer('fmb_search_nonce', 'nonce');
    
    $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
    
    if (strlen($keyword) < 2) {
        wp_send_json_success(['html' => '<div class="fmb-search-empty">Zadejte alespoň 2 znaky</div>']);
    }
    
    $results = [];
    
    // Hledání influencerů
    $influencers = get_posts([
        'post_type' => 'influencer',
        'posts_per_page' => 3,
        's' => $keyword,
        'post_status' => 'publish'
    ]);
    
    foreach ($influencers as $inf) {
        $thumbnail = get_the_post_thumbnail_url($inf->ID, 'large');
        
        // Získání kategorie influencera (zkusíme různé možnosti)
        $category = 'Influencer';
        
        // Možnost 1: Custom taxonomy 'influencer_category'
        $terms = get_the_terms($inf->ID, 'influencer_category');
        if ($terms && !is_wp_error($terms)) {
            $category = $terms[0]->name;
        } else {
            // Možnost 2: Meta field 'influencer_type'
            $meta_type = get_post_meta($inf->ID, 'influencer_type', true);
            if ($meta_type) {
                $category = $meta_type;
            } else {
                // Možnost 3: Category taxonomy
                $cats = get_the_terms($inf->ID, 'category');
                if ($cats && !is_wp_error($cats)) {
                    $category = $cats[0]->name;
                }
            }
        }
        
        $results[] = [
            'type' => 'influencer',
            'title' => $inf->post_title,
            'url' => get_permalink($inf->ID),
            'image' => $thumbnail ?: '',
            'category' => $category,
        ];
    }
    
    // Hledání produktů
    $products = get_posts([
        'post_type' => 'product',
        'posts_per_page' => 6,
        's' => $keyword,
        'post_status' => 'publish'
    ]);
    
    foreach ($products as $prod) {
        $product = wc_get_product($prod->ID);
        $results[] = [
            'type' => 'product',
            'title' => $prod->post_title,
            'url' => get_permalink($prod->ID),
            'image' => get_the_post_thumbnail_url($prod->ID, 'thumbnail'),
            'price' => $product ? $product->get_price_html() : ''
        ];
    }
    
    // Generování HTML
    $html = '';
    if (empty($results)) {
        $html = '<div class="fmb-search-empty">Nenalezeny žádné výsledky pro "' . esc_html($keyword) . '"</div>';
    } else {
        foreach ($results as $item) {
            if ($item['type'] === 'influencer') {
                // Normalizuj kategorii pro CSS selector
                $cat_slug = strtolower(trim($item['category']));
                $cat_slug = sanitize_title($cat_slug);
                
                $html .= '<a href="'.esc_url($item['url']).'" class="fmb-search-result fmb-search-influencer">';
                if ($item['image']) {
                    $html .= '<div class="fmb-search-banner"><img src="'.esc_url($item['image']).'" alt="'.esc_attr($item['title']).'"></div>';
                }
                $html .= '<div class="fmb-search-category-strip" data-category="'.esc_attr($cat_slug).'">'.esc_html(strtoupper($item['category'])).'</div>';
                $html .= '</a>';
            } else {
                $html .= '<a href="'.esc_url($item['url']).'" class="fmb-search-result fmb-search-product">';
                if ($item['image']) {
                    $html .= '<img src="'.esc_url($item['image']).'" alt="">';
                }
                $html .= '<div class="fmb-search-info">';
                $html .= '<h4>'.esc_html($item['title']).'</h4>';
                $html .= '<div class="fmb-search-price">'.$item['price'].'</div>';
                $html .= '</div></a>';
            }
        }
    }
    
    wp_send_json_success(['html' => $html]);
}