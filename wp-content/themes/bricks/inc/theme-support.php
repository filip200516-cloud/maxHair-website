<?php
/**
 * inc/theme-support.php
 *
 * Theme support for CPT Influencer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Add support for featured images (thumbnails) on the Influencer CPT
add_action( 'after_setup_theme', 'influencer_theme_support' );
function influencer_theme_support() {
    add_theme_support( 'post-thumbnails', [ 'influencer' ] );
}
