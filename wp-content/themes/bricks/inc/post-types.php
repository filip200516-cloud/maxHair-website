<?php
/**
 * inc/post-types.php
 *
 * Register the Influencer custom post type
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action( 'init', 'register_influencer_cpt' );
function register_influencer_cpt() {
    $labels = [
        'name'               => __( 'Influenceri', 'bricks' ),
        'singular_name'      => __( 'Influencer', 'bricks' ),
        'menu_name'          => __( 'Influenceri', 'bricks' ),
        'add_new_item'       => __( 'Přidat influencera', 'bricks' ),
        'edit_item'          => __( 'Upravit influencera', 'bricks' ),
        'new_item'           => __( 'Nový influencer', 'bricks' ),
        'view_item'          => __( 'Zobrazit influencera', 'bricks' ),
        'search_items'       => __( 'Hledat influencera', 'bricks' ),
        'not_found'          => __( 'Žádní influenceri nenalezeni', 'bricks' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'show_in_rest'       => true,
        'has_archive'        => true,
        'rewrite'            => [
            'slug'       => 'influencer',
            'with_front' => false,
        ],
        'menu_icon'          => 'dashicons-businessperson',
        // Podpora jen Title a Thumbnail (editor jsme odstranili)
        'supports'           => [ 'title', 'thumbnail' ],
        'taxonomies'         => [ 'influencer_category' ],
        'show_in_menu'       => true,
        'publicly_queryable' => true,
        'hierarchical'       => false,
    ];

    register_post_type( 'influencer', $args );
}
