<?php
/**
 * inc/metaboxes.php
 *
 * Portrait Photo metabox + admin tweaks for CPT Influencer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1) Disable Gutenberg for Influencer CPT
 */
add_filter( 'use_block_editor_for_post_type', function( $use, $post_type ) {
    return $post_type === 'influencer' ? false : $use;
}, 10, 2 );

/**
 * 2) Remove LiteSpeed & External Featured Image; rename Featured image → Thumbnail
 */
add_action( 'admin_head', 'influencer_cleanup_meta_boxes' );
function influencer_cleanup_meta_boxes() {
    $screen = get_current_screen();
    if ( $screen->post_type !== 'influencer' ) {
        return;
    }
    ?>
    <script>
    jQuery(function($){
        // Remove LiteSpeed & External Featured Image boxes
        $('.postbox').has('.hndle:contains("LiteSpeed"), .hndle:contains("External Featured Image")').remove();

        // Rename Featured image → Thumbnail
        $('#postimagediv .hndle').each(function(){
            if ( $(this).text().trim() === 'Featured image' ) {
                $(this).text('<?php echo esc_js( __( "Thumbnail", "bricks" ) ); ?>');
            }
        });
    });
    </script>
    <?php
}

/**
 * 3) Add Portrait Photo metabox
 */
add_action( 'add_meta_boxes', 'influencer_add_portrait_meta_box' );
function influencer_add_portrait_meta_box() {
    add_meta_box(
        'influencer_portrait',
        __( 'Portrétní fotka', 'bricks' ),
        'influencer_portrait_meta_box_callback',
        'influencer',
        'side',
        'default'
    );
}

/**
 * 4) Render Portrait Photo field + preview + uploader
 */
function influencer_portrait_meta_box_callback( $post ) {
    wp_nonce_field( 'save_influencer_portrait', 'influencer_portrait_nonce' );
    $att_id = (int) get_post_meta( $post->ID, 'influencer_portrait_id', true );

    echo '<div style="margin-bottom:8px;">';
    if ( $att_id ) {
        // uses medium size, max-width:150px
        echo wp_get_attachment_image( $att_id, 'medium', false, [
            'id'    => 'influencer_portrait_preview',
            'style' => 'max-width:150px;width:100%;height:auto;'
        ] );
    } else {
        echo '<img id="influencer_portrait_preview" style="max-width:150px;width:100%;height:auto;display:none;">';
    }
    echo '</div>';

    // hidden field for attachment ID
    printf(
        '<input type="hidden" id="influencer_portrait_id" name="influencer_portrait_id" value="%d">',
        $att_id
    );

    echo '<button type="button" class="button" id="influencer_portrait_upload">'
         . esc_html__( 'Nahrát', 'bricks' ) .
         '</button> ';
    echo '<button type="button" class="button" id="influencer_portrait_remove" style="'
         . ( $att_id ? '' : 'display:none;' ) .
         '">'
         . esc_html__( 'Odstranit', 'bricks' ) .
         '</button>';
    ?>

    <script>
    jQuery(function($){
        var frame;
        $('#influencer_portrait_upload').on('click', function(e){
            e.preventDefault();
            if ( frame ) { frame.open(); return; }
            frame = wp.media({
                title: '<?php echo esc_js( __( 'Vyber portrét', 'bricks' ) ); ?>',
                button: { text: '<?php echo esc_js( __( 'Použít obrázek', 'bricks' ) ); ?>' },
                multiple: false
            });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                $('#influencer_portrait_id').val(att.id);
                var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
                $('#influencer_portrait_preview')
                    .attr('src', url )
                    .show();
                $('#influencer_portrait_remove').show();
            });
            frame.open();
        });
        $('#influencer_portrait_remove').on('click', function(e){
            e.preventDefault();
            $('#influencer_portrait_id').val('');
            $('#influencer_portrait_preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}

/**
 * 5) Enqueue Media Library on Influencer edit screens
 */
add_action( 'admin_enqueue_scripts', 'influencer_admin_enqueue_media' );
function influencer_admin_enqueue_media( $hook ) {
    if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        global $post;
        if ( isset( $post->post_type ) && $post->post_type === 'influencer' ) {
            wp_enqueue_media();
        }
    }
}

/**
 * 6) Save Portrait attachment ID
 */
add_action( 'save_post_influencer', 'save_influencer_portrait_meta', 10, 2 );
function save_influencer_portrait_meta( $post_id, $post ) {
    if (
        empty( $_POST['influencer_portrait_nonce'] ) ||
        ! wp_verify_nonce( $_POST['influencer_portrait_nonce'], 'save_influencer_portrait' ) ||
        wp_is_post_autosave( $post_id ) ||
        wp_is_post_revision( $post_id )
    ) {
        return;
    }
    if ( isset( $_POST['influencer_portrait_id'] ) ) {
        $id = (int) $_POST['influencer_portrait_id'];
        if ( $id ) {
            update_post_meta( $post_id, 'influencer_portrait_id', $id );
        } else {
            delete_post_meta( $post_id, 'influencer_portrait_id' );
        }
    }
}
