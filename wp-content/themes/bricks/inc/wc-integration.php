<?php
/**
 * inc/wc-integration.php
 *
 * WooCommerce integration for the Influencer CPT:
 * Keeps the pa_influencer attribute terms in sync
 * with the list of published Influencer posts.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Synchronize the pa_influencer terms to exactly match
 * the titles of all published Influencer CPTs.
 */
function sync_all_influencer_terms() {
    // 1) Get all published Influencer IDs
    $post_ids = get_posts( [
        'post_type'      => 'influencer',
        'post_status'    => 'publish',
        'numberposts'    => -1,
        'fields'         => 'ids',
    ] );

    // 2) Map to titles
    $titles = array_map( 'get_the_title', $post_ids );

    // 3) Ensure the "Influencer" product attribute exists
    $exists = false;
    foreach ( wc_get_attribute_taxonomies() as $attr ) {
        if ( $attr->attribute_name === 'influencer' ) {
            $exists = true;
            break;
        }
    }
    if ( ! $exists ) {
        $attribute_id = wc_create_attribute( [
            'name'         => 'Influencer',
            'slug'         => 'influencer',
            'type'         => 'select',
            'order_by'     => 'menu_order',
            'has_archives' => false,
        ] );
        if ( ! is_wp_error( $attribute_id ) ) {
            flush_rewrite_rules( false );
        }
    }

    // 4) Add missing terms
    foreach ( $titles as $title ) {
        if ( ! term_exists( $title, 'pa_influencer' ) ) {
            wp_insert_term( $title, 'pa_influencer' );
        }
    }

    // 5) Remove obsolete terms
    $all_terms = get_terms( [
        'taxonomy'   => 'pa_influencer',
        'hide_empty' => false,
        'fields'     => 'all',
    ] );
    foreach ( $all_terms as $term ) {
        if ( ! in_array( $term->name, $titles, true ) ) {
            wp_delete_term( $term->term_id, 'pa_influencer' );
        }
    }
}

// 6) Hook into init so that any manual deletion/add bude napraveno hned
add_action( 'init', 'sync_all_influencer_terms', 50 );

// 7) Also on save (publish/update) of an influencer
add_action( 'save_post_influencer', 'sync_all_influencer_terms', 20, 2 );

// 8) And before an influencer post is deleted
add_action( 'before_delete_post', function( $post_id ) {
    if ( get_post_type( $post_id ) === 'influencer' ) {
        sync_all_influencer_terms();
    }
}, 20 );

/**
 * Hide the pa_influencer dropdown on variable products.
 */
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', function( $args ) {
    if ( isset( $args['attribute'] ) && strpos( $args['attribute'], 'influencer' ) !== false ) {
        $args['options']          = [];
        $args['show_option_none'] = false;
    }
    return $args;
}, 20 );
