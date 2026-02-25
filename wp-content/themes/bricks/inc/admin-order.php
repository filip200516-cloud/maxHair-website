<?php
// inc/admin-order.php
if ( ! defined( 'ABSPATH' ) ) exit;

// 1) Register the submenu page
add_action( 'admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=influencer',
        __( 'Pořadí influencerů', 'bricks' ),
        __( 'Pořadí', 'bricks' ),
        'manage_options',
        'influencer-order',
        'render_influencer_order_page'
    );
});

// 2) Render the page
function render_influencer_order_page() {
    $current = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';
    $terms   = get_terms( [
        'taxonomy'   => 'influencer_category',
        'hide_empty' => true,
    ] );

    echo '<div class="wrap"><h1>' . esc_html__( 'Pořadí influencerů', 'bricks' ) . '</h1>';

    // Category tabs
    echo '<div id="ioc_categories" style="margin-bottom:1em;">';
    printf(
        '<button class="button%s" data-term="">%s</button>',
        $current === '' ? ' button-primary' : '',
        esc_html__( 'Všichni', 'bricks' )
    );
    foreach ( $terms as $t ) {
        printf(
            '<button class="button%s" data-term="%s">%s</button>',
            $current === $t->slug ? ' button-primary' : '',
            esc_attr( $t->slug ),
            esc_html( $t->name )
        );
    }
    echo '</div>';

    // The form that will POST to admin-post.php
    echo '<form id="ioc_form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    wp_nonce_field( 'ioc_save', 'ioc_nonce' );
    echo '<input type="hidden" name="action" value="ioc_save_order">';

    // The sortable list
    echo '<ul id="ioc_list" style="list-style:none;padding:0;">';

    // Query influencers (filtered by term)
    $args = [
        'post_type'      => 'influencer',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ];
    if ( $current ) {
        $args['tax_query'] = [[
            'taxonomy' => 'influencer_category',
            'field'    => 'slug',
            'terms'    => $current,
        ]];
    }
    $infs = get_posts( $args );

    foreach ( $infs as $inf ) {
        // get first category, then its icon
        $cats      = wp_get_post_terms( $inf->ID, 'influencer_category' );
        $icon_html = '';
        if ( ! empty( $cats ) ) {
            $term      = $cats[0];
            $icon_src  = get_term_meta( $term->term_id, 'influencer_category_icon', true );
            if ( $icon_src ) {
                $icon_html = sprintf(
                    '<img src="%1$s" style="width:1.5em; height:1.5em; vertical-align:middle; margin-right:.5em; object-fit:contain;" />',
                    esc_url( $icon_src )
                );
            }
        }

        printf(
            '<li data-id="%1$d" style="padding:8px 12px; margin:4px 0; border:1px solid #ddd; background:#fff; cursor:move;">
                %3$s%2$s
                <input type="hidden" name="order[]" value="%1$d">
            </li>',
            intval( $inf->ID ),
            esc_html( get_the_title( $inf ) ),
            $icon_html
        );
    }

    echo '</ul>';

    // Save button
    submit_button( __( 'Uložit pořadí', 'bricks' ) );
    echo '</form>';

    // Inline JS + CSS to activate jQuery UI Sortable
    ?>
    <style>
    .placeholder {
      background: #f0f0f0;
      border: 2px dashed #ccc;
      height: 2.5em;
      margin: 4px 0;
    }
    </style>
    <script>
    jQuery(function($){
      // 1) Make the list sortable
      $('#ioc_list').sortable({
        placeholder: 'placeholder'
      });

      // 2) Category tabs click
      $('#ioc_categories button').on('click', function(){
        var term = $(this).data('term'),
            url  = new URL(window.location);
        if(!term) url.searchParams.delete('term');
        else      url.searchParams.set('term', term);
        window.location = url.toString();
      });

      // 3) On submit, rewrite hidden inputs in new order
      $('#ioc_form').on('submit', function(){
        $('#ioc_list li').each(function(i){
          $(this).find('input[name="order[]"]').val( $(this).data('id') );
        });
      });
    });
    </script>
    <?php

    echo '</div>';
}

// 3) Handle the POST when you click “Uložit pořadí”
add_action( 'admin_post_ioc_save_order', function(){
    if ( ! isset( $_POST['ioc_nonce'] ) || ! wp_verify_nonce( $_POST['ioc_nonce'], 'ioc_save' ) ) {
        wp_die( __( 'Nonce ověření selhalo.', 'bricks' ) );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Nemáte oprávnění.', 'bricks' ) );
    }
    if ( ! empty( $_POST['order'] ) && is_array( $_POST['order'] ) ) {
        foreach ( $_POST['order'] as $index => $id ) {
            wp_update_post([
                'ID'         => intval( $id ),
                'menu_order' => intval( $index ),
            ]);
        }
    }
    // Redirect back to same tab
    $redirect = admin_url( 'edit.php?post_type=influencer&page=influencer-order' );
    if ( isset( $_GET['term'] ) ) {
        $redirect .= '&term=' . urlencode( sanitize_text_field( $_GET['term'] ) );
    }
    wp_safe_redirect( $redirect );
    exit;
});

// 4) Enqueue jQuery UI Sortable only on our page
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'influencer_page_influencer-order' ) return;
    wp_enqueue_script( 'jquery-ui-sortable' );
} );
