<?php
/**
 * inc/color-admin.php
 *
 * Správa term-meta “pa_color_hex”
 * + vlastní sloupec s barevným čtverečkem v seznamu termů
 * + skrytí polí Slug a Description v Add/Edit formu
 * + submenu “Barvy” pod Products
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 0) Přidej submenu “Barvy” pod Products
 */
add_action( 'admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=product',
        __( 'Barvy', 'your-text-domain' ),
        __( 'Barvy', 'your-text-domain' ),
        'manage_woocommerce',
        'edit-tags.php?taxonomy=pa_color&post_type=product'
    );
}, 20 );

/**
 * 1) Pole “Hex barvy” v Add term form
 */
add_action( 'pa_color_add_form_fields', function( $taxonomy ) {
    ?>
    <div class="form-field term-group">
        <label for="pa_color_hex"><?php esc_html_e( 'Hex barvy', 'your-text-domain' ); ?></label>
        <input name="pa_color_hex" id="pa_color_hex" type="text" value="" />
        <p class="description">
            <?php esc_html_e( 'Zadejte kód barvy (např. #ff0000).', 'your-text-domain' ); ?>
        </p>
    </div>
    <?php
}, 10, 1 );

/**
 * 2) Pole “Hex barvy” v Edit term form
 */
add_action( 'pa_color_edit_form_fields', function( $term ) {
    $hex = get_term_meta( $term->term_id, 'pa_color_hex', true );
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row">
            <label for="pa_color_hex"><?php esc_html_e( 'Hex barvy', 'your-text-domain' ); ?></label>
        </th>
        <td>
            <input name="pa_color_hex" id="pa_color_hex" type="text"
                   value="<?php echo esc_attr( $hex ); ?>" />
            <p class="description">
                <?php esc_html_e( 'Zadejte kód barvy (např. #00ff00).', 'your-text-domain' ); ?>
            </p>
        </td>
    </tr>
    <?php
}, 10, 1 );

/**
 * 3) Uložení metadat při vytváření termu
 */
add_action( 'created_pa_color', function( $term_id ) {
    if ( isset( $_POST['pa_color_hex'] ) ) {
        $hex = sanitize_text_field( wp_unslash( $_POST['pa_color_hex'] ) );
        update_term_meta( $term_id, 'pa_color_hex', $hex );
    }
}, 10, 1 );

/**
 * 4) Uložení metadat při úpravě termu
 */
add_action( 'edited_pa_color', function( $term_id ) {
    if ( isset( $_POST['pa_color_hex'] ) ) {
        $hex = sanitize_text_field( wp_unslash( $_POST['pa_color_hex'] ) );
        update_term_meta( $term_id, 'pa_color_hex', $hex );
    }
}, 10, 1 );

/**
 * 5) Přidání vlastního sloupce “Ikonka / barva” do seznamu termů
 */
add_filter( 'manage_edit-pa_color_columns', function( $columns ) {
    $new = [];
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'name' === $key ) {
            $new['color_square'] = __( 'Ikonka / barva', 'your-text-domain' );
        }
    }
    return $new;
} );

/**
 * 6) Naplnění sloupce “Ikonka / barva”
 */
add_action( 'manage_pa_color_custom_column', function( $content, $column_name, $term_id ) {
    if ( 'color_square' === $column_name ) {
        $hex = get_term_meta( $term_id, 'pa_color_hex', true );
        if ( $hex ) {
            $content = sprintf(
                '<div style="
                    width:24px;
                    height:24px;
                    background-color:%1$s;
                    border:1px solid #ccc;
                    border-radius:4px;
                "></div>',
                esc_attr( $hex )
            );
        } else {
            $content = '–';
        }
    }
    return $content;
}, 10, 3 );

/**
 * 7) Skrytí polí Slug a Description pouze na stránce Barvy
 */
add_action( 'admin_head-edit-tags.php', function() {
    $screen = get_current_screen();
    if ( $screen && $screen->taxonomy === 'pa_color' ) {
        echo '<style>
            /* skryj slug + description v Add form */
            .term-slug-wrap, .term-description-wrap { display: none !important; }
            /* skryj slug + description v Edit form */
            tr.form-field.term-slug-wrap, tr.form-field.term-description-wrap { display: none !important; }
        </style>';
    }
} );
