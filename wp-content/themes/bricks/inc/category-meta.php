<?php
/**
 * inc/category-meta.php
 *
 * Term meta fields and Media Library uploader for influencer_category taxonomy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1) Enqueue Media Library on taxonomy screens
add_action( 'admin_enqueue_scripts', 'influencer_taxonomy_admin_assets' );
function influencer_taxonomy_admin_assets( $hook ) {
    $screen = get_current_screen();
    if ( isset( $screen->taxonomy ) && $screen->taxonomy === 'influencer_category' ) {
        wp_enqueue_media();
    }
}

// 2) Hide default Slug, Parent, Description fields
add_action( 'admin_head', 'influencer_category_hide_default_fields' );
function influencer_category_hide_default_fields() {
    $screen = get_current_screen();
    if ( isset( $screen->taxonomy ) && $screen->taxonomy === 'influencer_category' ) {
        echo '<style>
            .form-field.term-slug-wrap,
            .form-field.term-parent-wrap,
            .form-field.term-description-wrap {
                display: none;
            }
        </style>';
    }
}

// 3) Add background + icon fields to Add Term form
add_action( 'influencer_category_add_form_fields', 'influencer_category_add_meta_fields', 10, 2 );
function influencer_category_add_meta_fields( $taxonomy ) {
    ?>
    <div class="form-field term-group">
        <label><?php _e( 'Pozadí karty', 'bricks' ); ?></label>
        <div>
            <img id="influencer_category_background_preview" style="display:none;max-width:150px;margin-bottom:8px;">
            <input type="hidden" id="influencer_category_background" name="influencer_category_background" value="">
            <button class="upload_image_button button"><?php _e( 'Nahrát obrázek', 'bricks' ); ?></button>
            <button class="remove_image_button button" style="display:none;"><?php _e( 'Odstranit obrázek', 'bricks' ); ?></button>
        </div>
    </div>
    <div class="form-field term-group">
        <label><?php _e( 'Ikonka', 'bricks' ); ?></label>
        <div>
            <img id="influencer_category_icon_preview" style="display:none;max-width:50px;margin-bottom:8px;">
            <input type="hidden" id="influencer_category_icon" name="influencer_category_icon" value="">
            <button class="upload_icon_button button"><?php _e( 'Nahrát ikonku', 'bricks' ); ?></button>
            <button class="remove_icon_button button" style="display:none;"><?php _e( 'Odstranit ikonku', 'bricks' ); ?></button>
        </div>
    </div>
    <?php
}

// 4) Add background + icon fields to Edit Term form
add_action( 'influencer_category_edit_form_fields', 'influencer_category_edit_meta_fields', 10, 2 );
function influencer_category_edit_meta_fields( $term ) {
    $bg   = get_term_meta( $term->term_id, 'influencer_category_background', true );
    $icon = get_term_meta( $term->term_id, 'influencer_category_icon', true );
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label><?php _e( 'Pozadí karty', 'bricks' ); ?></label></th>
        <td>
            <img id="influencer_category_background_preview" src="<?php echo esc_url( $bg ); ?>" style="max-width:150px;margin-bottom:8px;<?php echo $bg ? '' : 'display:none;'; ?>">
            <input type="hidden" id="influencer_category_background" name="influencer_category_background" value="<?php echo esc_attr( $bg ); ?>">
            <button class="upload_image_button button"><?php _e( 'Nahrát obrázek', 'bricks' ); ?></button>
            <button class="remove_image_button button" style="<?php echo $bg ? '' : 'display:none;'; ?>"><?php _e( 'Odstranit obrázek', 'bricks' ); ?></button>
        </td>
    </tr>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label><?php _e( 'Ikonka', 'bricks' ); ?></label></th>
        <td>
            <img id="influencer_category_icon_preview" src="<?php echo esc_url( $icon ); ?>" style="max-width:50px;margin-bottom:8px;<?php echo $icon ? '' : 'display:none;'; ?>">
            <input type="hidden" id="influencer_category_icon" name="influencer_category_icon" value="<?php echo esc_attr( $icon ); ?>">
            <button class="upload_icon_button button"><?php _e( 'Nahrát ikonku', 'bricks' ); ?></button>
            <button class="remove_icon_button button" style="<?php echo $icon ? '' : 'display:none;'; ?>"><?php _e( 'Odstranit ikonku', 'bricks' ); ?></button>
        </td>
    </tr>
    <?php
}

// 5) Media uploader JS in admin footer
add_action( 'admin_footer-edit-tags.php', 'influencer_category_media_script' );
add_action( 'admin_footer-term.php',      'influencer_category_media_script' );
function influencer_category_media_script() {
    $screen = get_current_screen();
    if ( ! isset( $screen->taxonomy ) || $screen->taxonomy !== 'influencer_category' ) {
        return;
    }
    ?>
    <script>
    jQuery(function($){
        function initUploader(btn, preview, input, removeBtn, title){
            var frame;
            $(btn).on('click', function(e){
                e.preventDefault();
                if(frame){ frame.open(); return; }
                frame = wp.media({ title: title, button:{ text: title }, multiple: false });
                frame.on('select', function(){
                    var att = frame.state().get('selection').first().toJSON();
                    $(preview).attr('src', att.url).show();
                    $(input).val(att.url);
                    $(removeBtn).show();
                });
                frame.open();
            });
            $(removeBtn).on('click', function(e){
                e.preventDefault();
                $(preview).hide().attr('src','');
                $(input).val('');
                $(removeBtn).hide();
            });
        }
        initUploader(
            '.upload_image_button',
            '#influencer_category_background_preview',
            '#influencer_category_background',
            '.remove_image_button',
            '<?php _e( 'Vyber obrázek', 'bricks' ); ?>'
        );
        initUploader(
            '.upload_icon_button',
            '#influencer_category_icon_preview',
            '#influencer_category_icon',
            '.remove_icon_button',
            '<?php _e( 'Vyber ikonku', 'bricks' ); ?>'
        );
    });
    </script>
    <?php
}

// 6) Save term meta
add_action( 'created_influencer_category', 'save_influencer_category_meta', 10, 2 );
add_action( 'edited_influencer_category',  'save_influencer_category_meta', 10, 2 );
function save_influencer_category_meta( $term_id, $tt_id ) {
    if ( isset( $_POST['influencer_category_background'] ) ) {
        update_term_meta( $term_id, 'influencer_category_background', esc_url_raw( $_POST['influencer_category_background'] ) );
    }
    if ( isset( $_POST['influencer_category_icon'] ) ) {
        update_term_meta( $term_id, 'influencer_category_icon', esc_url_raw( $_POST['influencer_category_icon'] ) );
    }
}
