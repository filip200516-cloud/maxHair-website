<?php
/**
 * inc/taxonomy-category.php
 *
 * Register the influencer_category taxonomy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// 1) Register taxonomy "influencer_category"
add_action( 'init', 'register_influencer_category_taxonomy' );
function register_influencer_category_taxonomy() {
    $labels = [
        'name'                       => __( 'Kategorie', 'bricks' ),
        'singular_name'              => __( 'Kategorie influencera', 'bricks' ),
        'menu_name'                  => __( 'Kategorie', 'bricks' ),
        'all_items'                  => __( 'Všechny kategorie', 'bricks' ),
        'edit_item'                  => __( 'Upravit kategorii', 'bricks' ),
        'view_item'                  => __( 'Zobrazit kategorii', 'bricks' ),
        'update_item'                => __( 'Aktualizovat kategorii', 'bricks' ),
        'add_new_item'               => __( 'Přidat novou kategorii', 'bricks' ),
        'new_item_name'              => __( 'Nová kategorie', 'bricks' ),
        'search_items'               => __( 'Hledat kategorie', 'bricks' ),
        'popular_items'              => __( 'Oblíbené kategorie', 'bricks' ),
        'separate_items_with_commas' => __( 'Odděl kategorie čárkou', 'bricks' ),
        'add_or_remove_items'        => __( 'Přidat nebo odebrat kategorie', 'bricks' ),
        'choose_from_most_used'      => __( 'Vybrat z nejpoužívanějších', 'bricks' ),
        'not_found'                  => __( 'Žádné kategorie nenalezeny', 'bricks' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => [
            'slug'       => 'kategorie',
            'with_front' => false,
        ],
    ];

    register_taxonomy( 'influencer_category', [ 'influencer' ], $args );
}
