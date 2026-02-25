<?php
// pokud to ještě není na začátku
if ( ! defined( 'ABSPATH' ) ) {
    exit; // zabezpečení
}

// ---- Načtení všech inc–modulů ----
foreach ( [
    'post-types',
    'theme-support',
    'wc-integration',
    'taxonomy-category',
    'category-meta',
    'metaboxes',
    'admin-order',
    'frontend-display',
	'wc-admin-filters',
	'color-admin',
] as $module ) {
    $path = __DIR__ . "/inc/{$module}.php";
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

/**
 * Define constants
 *
 * @since 1.0
 */
define( 'BRICKS_VERSION', '2.0' );
define( 'BRICKS_NAME', 'Bricks' );
define( 'BRICKS_TEMP_DIR', 'bricks-temp' ); // Template import/export (JSON & ZIP)
define( 'BRICKS_TEMPLATE_SCREENSHOTS_DIR', 'bricks/template-screenshots' ); // Template screenshots (@since 1.10)
define( 'BRICKS_PATH', trailingslashit( get_template_directory() ) );    // require_once files
define( 'BRICKS_PATH_ASSETS', trailingslashit( BRICKS_PATH . 'assets' ) );
define( 'BRICKS_URL', trailingslashit( get_template_directory_uri() ) ); // WP enqueue files
define( 'BRICKS_URL_ASSETS', trailingslashit( BRICKS_URL . 'assets' ) );
define( 'BRICKS_REMOTE_URL', 'https://bricksbuilder.io/' );
define( 'BRICKS_REMOTE_ACCOUNT', BRICKS_REMOTE_URL . 'account/' );

define( 'BRICKS_BUILDER_PARAM', 'bricks' );
define( 'BRICKS_BUILDER_IFRAME_PARAM', 'brickspreview' );
define( 'BRICKS_DEFAULT_IMAGE_SIZE', 'large' );

define( 'BRICKS_DB_PANEL_WIDTH', 'bricks_panel_width' );
define( 'BRICKS_DB_STRUCTURE_WIDTH', 'bricks_structure_width' ); // @since 1.10.2
define( 'BRICKS_DB_BUILDER_SCALE_OFF', 'bricks_builder_scale_off' );
define( 'BRICKS_DB_BUILDER_WIDTH_LOCKED', 'bricks_builder_width_locked' );

define( 'BRICKS_DB_COMPONENTS', 'bricks_components' );
define( 'BRICKS_DB_COLOR_PALETTE', 'bricks_color_palette' );
define( 'BRICKS_DB_BREAKPOINTS', 'bricks_breakpoints' );
define( 'BRICKS_DB_GLOBAL_SETTINGS', 'bricks_global_settings' );
define( 'BRICKS_DB_GLOBAL_ELEMENTS', 'bricks_global_elements' );
define( 'BRICKS_DB_GLOBAL_CLASSES', 'bricks_global_classes' );
define( 'BRICKS_DB_GLOBAL_CLASSES_CATEGORIES', 'bricks_global_classes_categories' );
define( 'BRICKS_DB_GLOBAL_CLASSES_LOCKED', 'bricks_global_classes_locked' );
define( 'BRICKS_DB_GLOBAL_CLASSES_TIMESTAMP', 'bricks_global_classes_timestamp' );
define( 'BRICKS_GLOBAL_CLASSES_DEFAULT_TRASH_RETENTION_DAYS', 30 );
define( 'BRICKS_DB_GLOBAL_CLASSES_TRASH', 'bricks_global_classes_trash' );
define( 'BRICKS_DB_GLOBAL_CLASSES_USER', 'bricks_global_classes_user' );
define( 'BRICKS_DB_PSEUDO_CLASSES', 'bricks_global_pseudo_classes' );
define( 'BRICKS_DB_GLOBAL_VARIABLES', 'bricks_global_variables' );
define( 'BRICKS_DB_GLOBAL_VARIABLES_CATEGORIES', 'bricks_global_variables_categories' );
define( 'BRICKS_DB_PINNED_ELEMENTS', 'bricks_pinned_elements' );
define( 'BRICKS_DB_SIDEBARS', 'bricks_sidebars' );
define( 'BRICKS_DB_THEME_STYLES', 'bricks_theme_styles' );
define( 'BRICKS_DB_ADOBE_FONTS', 'bricks_adobe_fonts' );
define( 'BRICKS_DB_ELEMENT_MANAGER', 'bricks_element_manager' );
define( 'BRICKS_DB_FONT_FAVORITES', 'bricks_font_favorites' ); // @since 2.0

define( 'BRICKS_DB_ICON_SETS', 'bricks_icon_sets' ); // @since 2.0
define( 'BRICKS_DB_CUSTOM_ICONS', 'bricks_custom_icons' ); // @since 2.0
define( 'BRICKS_DB_DISABLED_ICON_SETS', 'bricks_disabled_icon_sets' ); // @since 2.0

define( 'BRICKS_DB_EDITOR_MODE', '_bricks_editor_mode' );
define( 'BRICKS_BREAKPOINTS_LAST_GENERATED', 'bricks_breakpoints_last_generated' );

define( 'BRICKS_CSS_FILES_LAST_GENERATED', 'bricks_css_files_last_generated' );
define( 'BRICKS_CSS_FILES_LAST_GENERATED_TIMESTAMP', 'bricks_css_files_last_generated_timestamp' );
define( 'BRICKS_CSS_FILES_ADMIN_NOTICE', 'bricks_css_files_admin_notice' );

define( 'BRICKS_CODE_SIGNATURES_LAST_GENERATED', 'bricks_code_signatures_last_generated' );
define( 'BRICKS_CODE_SIGNATURES_LAST_GENERATED_TIMESTAMP', 'bricks_code_signatures_last_generated_timestamp' );
define( 'BRICKS_CODE_SIGNATURES_ADMIN_NOTICE', 'bricks_code_signatures_admin_notice' );

define( 'BRICKS_DB_CAPABILITIES_PERMISSIONS', 'bricks_capabilities_permissions' );

/**
 * Lock code signatures (default: false)
 *
 * @since 1.11.1
 */
if ( ! defined( 'BRICKS_LOCK_CODE_SIGNATURES' ) ) {
	define( 'BRICKS_LOCK_CODE_SIGNATURES', false );
}

/**
 * Syntax since 1.2 (container element)
 *
 * Pre 1.2: '_bricks_page_{$content_type}'
 */
define( 'BRICKS_DB_PAGE_HEADER', '_bricks_page_header_2' );
define( 'BRICKS_DB_PAGE_CONTENT', '_bricks_page_content_2' );
define( 'BRICKS_DB_PAGE_FOOTER', '_bricks_page_footer_2' );
define( 'BRICKS_DB_PAGE_SETTINGS', '_bricks_page_settings' );

define( 'BRICKS_DB_REMOTE_TEMPLATES', 'bricks_remote_templates' );
define( 'BRICKS_DB_TEMPLATE_SLUG', 'bricks_template' );
define( 'BRICKS_DB_TEMPLATE_TAX_BUNDLE', 'template_bundle' );
define( 'BRICKS_DB_TEMPLATE_TAX_TAG', 'template_tag' );
define( 'BRICKS_DB_TEMPLATE_TYPE', '_bricks_template_type' );
define( 'BRICKS_DB_TEMPLATE_SETTINGS', '_bricks_template_settings' );

define( 'BRICKS_DB_CUSTOM_FONTS', 'bricks_fonts' );
define( 'BRICKS_DB_CUSTOM_FONT_FACES', 'bricks_font_faces' );
define( 'BRICKS_DB_CUSTOM_FONT_FACE_RULES', 'bricks_font_face_rules' ); // @since 1.7.2

define( 'BRICKS_EXPORT_TEMPLATES', 'brick_export_templates' );

define( 'BRICKS_ADMIN_PAGE_URL_LICENSE', admin_url( 'admin.php?page=bricks-license' ) );

define( 'BRICKS_AUTH_CHECK_INTERVAL', 30 );

if ( ! defined( 'BRICKS_DEBUG' ) ) {
	define( 'BRICKS_DEBUG', false );
}

if ( ! defined( 'BRICKS_MAX_REVISIONS_TO_KEEP' ) ) {
	define( 'BRICKS_MAX_REVISIONS_TO_KEEP', 100 );
}

/**
 * Multisite constants
 *
 * @since 1.0
 */

// Global data: Components (@since 1.12)
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_COMPONENTS' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_COMPONENTS', false );
}

// Global data: Color palette
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_COLOR_PALETTE' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_COLOR_PALETTE', false );
}

// Global data: Global classes
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_CLASSES' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_CLASSES', false );
}

// Global data: Global classes categories
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_CLASSES_CATEGORIES' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_CLASSES_CATEGORIES', false );
}

// Global data: Global variables
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_VARIABLES' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_VARIABLES', false );
}

// Global data: Global variables categories
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_VARIABLES_CATEGORIES' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_VARIABLES_CATEGORIES', false );
}

// Global data: Global elements
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_GLOBAL_ELEMENTS' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_GLOBAL_ELEMENTS', false );
}

// Global data: Font favorites
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_FONT_FAVORITES' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_FONT_FAVORITES', false );
}

// Global data: Icon sets
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_ICON_SETS' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_ICON_SETS', false );
}

// Global data: Custom icons
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_CUSTOM_ICONS' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_CUSTOM_ICONS', false );
}

// Global data: Disabled icon sets
if ( ! defined( 'BRICKS_MULTISITE_USE_MAIN_SITE_DISABLED_ICON_SETS' ) ) {
	define( 'BRICKS_MULTISITE_USE_MAIN_SITE_DISABLED_ICON_SETS', false );
}

/**
 * Use minified assets when SCRIPT_DEBUG is off
 *
 * @since 1.0
 */
if ( BRICKS_DEBUG || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
	define( 'BRICKS_ASSETS_SUFFIX', '' );
} else {
	define( 'BRICKS_ASSETS_SUFFIX', '.min' );
}

/**
 * Admin notice if PHP version is older than 5.4
 *
 * Required due to: array shorthand, array dereferencing etc.
 *
 * @since 1.0
 */
if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	require_once BRICKS_PATH . 'includes/init.php';
} else {
	add_action(
		'admin_notices',
		function() {
			// translators: %1$s: Bricks (theme name), %2$s: PHP version
			$message = sprintf( esc_html__( '%1$s requires PHP version %2$s+.', 'bricks' ), 'Bricks', '5.4' );
			$html    = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
			echo wp_kses_post( $html );
		}
	);
}

/**
 * Builder check
 *
 * @since 1.0
 */
function bricks_is_builder() {
	return ( ! is_admin() && isset( $_GET[ BRICKS_BUILDER_PARAM ] ) );
}

function bricks_is_builder_iframe() {
	return ( bricks_is_builder() && isset( $_GET[ BRICKS_BUILDER_IFRAME_PARAM ] ) );
}

function bricks_is_builder_main() {
	return ( bricks_is_builder() && ! isset( $_GET[ BRICKS_BUILDER_IFRAME_PARAM ] ) );
}

function bricks_is_frontend() {
	return ! bricks_is_builder();
}

/**
 * Is AJAX call check
 *
 * @since 1.0
 */
function bricks_is_ajax_call() {
	return defined( 'DOING_AJAX' ) && DOING_AJAX;
}

/**
 * Is WP REST API call check
 *
 * @since 1.5
 */
function bricks_is_rest_call() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * Is builder call (AJAX OR REST API)
 *
 * @since 1.5
 */
function bricks_is_builder_call() {
	// Use PHP constant BRICKS_IS_BUILDER @since 1.5.5 to perform builder check logic only once
	if ( ! defined( 'BRICKS_IS_BUILDER' ) ) {
		define( 'BRICKS_IS_BUILDER', \Bricks\Builder::is_builder_call() );
	}

	return BRICKS_IS_BUILDER;
}


/**
 * Render dynamic data tags inside of a content string
 *
 * Example: Inside an executing Code element, custom plugin, etc.
 *
 * Academy: https://academy.bricksbuilder.io/article/function-bricks_render_dynamic_data/
 *
 * @since 1.5.5
 *
 * @param string $content The content (including dynamic data tags).
 * @param int    $post_id The post ID.
 * @param string $context text, image, link, etc.
 *
 * @return string
 */
function bricks_render_dynamic_data( $content, $post_id = 0, $context = 'text' ) {
	return \Bricks\Integrations\Dynamic_Data\Providers::render_content( $content, $post_id, $context );
}

// 1. Vytvoření shortcodu pro size chart modal
add_shortcode('size_chart_modal', function() {
    ob_start(); ?>
    <a href="#" class="size-chart-link" id="sizeChartTrigger">See Size Chart</a>
    <div id="sizeChartModal" class="size-chart-modal">
      <div class="modal-content">
        <button class="close-btn" id="closeSizeChart">&times;</button>
        <h2>Hoodie Size Chart</h2>
        <table>
          <thead><tr><th>Size</th><th>Width (cm)</th><th>Length (cm)</th></tr></thead>
          <tbody>
            <tr><td>S</td><td>50</td><td>68</td></tr>
            <tr><td>M</td><td>54</td><td>70</td></tr>
            <tr><td>L</td><td>58</td><td>72</td></tr>
            <tr><td>XL</td><td>62</td><td>74</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php
    return ob_get_clean();
});

// 2. Načtení inline CSS a JS pro modal
add_action('wp_enqueue_scripts', function() {
    // Styl
    wp_add_inline_style('woocommerce-inline', "
      .size-chart-link { color:#0073e6; font-weight:bold; cursor:pointer; }
      .size-chart-modal { display:none; position:fixed;top:0;left:0;width:100%;height:100%;
                         background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:9999; }
      .size-chart-modal .modal-content { background:#fff;padding:20px;border-radius:8px;
                                         max-width:360px;width:90%;position:relative;box-shadow:0 4px 20px rgba(0,0,0,0.2); }
      .size-chart-modal .close-btn { position:absolute;top:10px;right:10px;font-size:24px;
                                     background:none;border:none;color:#888;cursor:pointer; }
      .size-chart-modal .close-btn:hover { color:#444; }
      .size-chart-modal table { width:100%;border-collapse:collapse;margin-top:10px;font-size:14px; }
      .size-chart-modal th, .size-chart-modal td { padding:8px;border:1px solid #ddd; }
      .size-chart-modal th { background:#f5f5f5; }
      .size-chart-modal tr:nth-child(even) { background:#fafafa; }
    ");

    // Skrypt
    wp_add_inline_script('jquery-core', "
      jQuery(function($){
        const modal = $('#sizeChartModal'),
              trigger = $('#sizeChartTrigger'),
              closeBtn = $('#closeSizeChart');
        trigger.on('click', e => { e.preventDefault(); modal.css('display','flex'); });
        closeBtn.on('click', () => modal.hide());
        $(window).on('click', e => { if (e.target.id === 'sizeChartModal') modal.hide(); });
      });
    ");
});

// === Jednorázové spuštění seed skriptu pomocí transientu ===
if ( is_admin() && current_user_can('manage_options') && ! get_transient('es_seed_done') ) {
    set_transient('es_seed_done', true, MINUTE_IN_SECONDS);

    // === ZAČÁTEK seed skriptu – import termínů ===
    add_action('init', function(){
      if (!isset($_GET['seed_gift_terms'])) return;

      $audiences = ['Dámské','Pánské','Dětské','LGBT','Páry','Mazlíčci','Bez potisku','OUTLET %'];
      foreach($audiences as $i=>$name){
        if (!term_exists($name,'gift_audience')) {
          $t = wp_insert_term($name,'gift_audience');
          if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
        }
      }

      $types = ['Oblečení','Produkty','Služby'];
      foreach($types as $i=>$name){
        if (!term_exists($name,'gift_type')) {
          $t = wp_insert_term($name,'gift_type');
          if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
        }
      }

      $themes = [
        'Dárky pro maminku','Dárky pro ségru','Dárky pro babičku','Dárky pro tátu','Dárky pro bráchu','Dárky pro dědu',
        'Dárky pro partnera','Dárky pro partnerku','Dárky pro přátele','Dárky pro učitele','Láska','Sport','Auta','Motorky',
        'Dětské','Hlášky','Humor','Hudba & Film','Grilování','Vodáci','Formule 1','Yoga a Fitness',
        'Rozlučka se svobodou','Rybáři','Cyklistická','Svatba','Politika','Koně','Pejskové','Kočičky',
        'Alkohol','Drogy','Hokej','Fotbal','Golf'
      ];
      foreach($themes as $i=>$name){
        if (!term_exists($name,'gift_theme')) {
          $t = wp_insert_term($name,'gift_theme');
          if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
        }
      }

      wp_die('Hotovo (odstraň seed skript).');
    });
    // === KONEC seed skriptu ===
}

// === Mockupy – core ===
$mockups_register = get_stylesheet_directory() . '/inc/mockups-register.php';
$mockups_product  = get_stylesheet_directory() . '/inc/mockups-product.php';
if ( file_exists( $mockups_register ) ) require_once $mockups_register;
if ( file_exists( $mockups_product ) )  require_once $mockups_product;

defined('ABSPATH') || exit;

// Najdi /inc v child theme, případně fallback do parent theme.
function es_inc_dir(): string {
  $child = trailingslashit(get_stylesheet_directory()) . 'inc/';
  if (is_dir($child)) return $child;
  $parent = trailingslashit(get_template_directory()) . 'inc/';
  return is_dir($parent) ? $parent : '';
}

// Načti inc soubory v bezpečném pořadí
add_action('after_setup_theme', function () {
  $inc = es_inc_dir();
  if (!$inc) return;

  foreach (['filters.php', 'seed-gift-terms.php'] as $f) {
    $path = $inc . $f;
    if (file_exists($path)) require_once $path;
  }
});

add_action('admin_head', function () {
    echo '<style>
        #adminmenuback, #adminmenuwrap, #adminmenu { 
            display:block !important; 
            visibility:visible !important; 
            opacity:1 !important; 
        }
        #adminmenu, #adminmenu * { 
            pointer-events:auto !important; 
        }
    </style>';
});

// [influencer_banner full="1" link="auto" class="" src="" width="" height=""]
add_shortcode('influencer_banner', function($atts = []) {
    if ( ! function_exists('is_product') || ! is_product() ) return '';

    global $product;
    if ( ! $product instanceof WC_Product ) $product = wc_get_product( get_the_ID() );
    if ( ! $product ) return '';

    $terms = get_the_terms( $product->get_id(), 'pa_influencer' );
    if ( is_wp_error($terms) || empty($terms) ) return '';

    $names = wp_list_pluck( $terms, 'name' );
    $alt   = 'Produkt influencera: ' . implode(', ', array_map('wp_strip_all_tags', $names));

    $defaults = [
        'src'    => 'https://silver-alligator-496114.hostingersite.com/wp-content/uploads/2025/08/test_2-m7VbzyM549hqVDR4-1-scaled.png',
        'class'  => '',
        'link'   => '',
        'width'  => '',
        'height' => '',
        'full'   => '0', // "1" = pokus o originál (bez -scaled)
    ];
    $a = shortcode_atts($defaults, $atts, 'influencer_banner');

    $src = $a['src'];

    // FULL-RES: zkusit získat originál z attachmentu nebo odříznout "-scaled"
    if ($a['full'] === '1') {
        $id = function_exists('attachment_url_to_postid') ? attachment_url_to_postid($src) : 0;

        if ($id && function_exists('wp_get_original_image_url')) {
            $orig = wp_get_original_image_url($id);
            if ($orig && ! is_wp_error($orig)) $src = $orig;
        } else {
            // fallback: odeber "-scaled" na konci názvu
            $try = preg_replace('/-scaled(\.[a-z0-9]+)$/i', '$1', $src);

            // ověř, že soubor fyzicky existuje v /uploads (bez HTTP requestu)
            $up = wp_get_upload_dir();
            if (strpos($try, $up['baseurl']) === 0) {
                $rel = ltrim(substr($try, strlen($up['baseurl'])), '/');
                $abs = trailingslashit($up['basedir']) . $rel;
                if (file_exists($abs)) $src = $try;
            } else {
                // Poslední možnost – použij bez kontroly (pokud je mimo uploads)
                $src = $try;
            }
        }
    }

    $class = trim(preg_replace('/[^A-Za-z0-9_\-\s]/', '', $a['class']));
    $w = $a['width']  !== '' ? ' width="'.intval($a['width']).'"'   : '';
    $h = $a['height'] !== '' ? ' height="'.intval($a['height']).'"' : '';

    // Link: "" | konkrétní URL | "auto" = první term
    $href = '';
    if ($a['link'] === 'auto') {
        $first = is_array($terms) ? reset($terms) : null;
        if ($first && ! is_wp_error($first)) {
            $tlink = get_term_link($first);
            if (! is_wp_error($tlink)) $href = $tlink;
        }
    } elseif (! empty($a['link'])) {
        $href = esc_url($a['link']);
    }

    ob_start(); ?>
    <div class="influencer-banner<?php echo $class ? ' '.esc_attr($class) : ''; ?>" aria-label="Banner pro produkt influencera">
      <?php if ($href) echo '<a href="'.esc_url($href).'">'; ?>
      <img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async"<?php echo $w.$h; ?> />
      <?php if ($href) echo '</a>'; ?>
    </div>
    <?php
    return ob_get_clean();
});

$wc_accounts = get_stylesheet_directory() . '/inc/wc-accounts.php';
if ( file_exists( $wc_accounts ) ) require_once $wc_accounts;
$search_handler = get_stylesheet_directory() . '/inc/search-handler.php';
if ( file_exists( $search_handler ) ) require_once $search_handler;

/**
 * MaxHair SMTP - odesílání bez pluginu (PHPMailer přímo)
 * Přidejte do wp-config.php před "That's all, stop editing!":
 *
 * define('MAXHAIR_SMTP_HOST', 'smtp.hostinger.com');
 * define('MAXHAIR_SMTP_PORT', 465);
 * define('MAXHAIR_SMTP_SECURE', 'ssl');
 * define('MAXHAIR_SMTP_USER', 'forms@fellaship.cz');
 * define('MAXHAIR_SMTP_PASS', 'vase-heslo-k-emailu');
 */
function maxhair_smtp_send( $to, $subject, $body, $args = array() ) {
    if ( ! defined('MAXHAIR_SMTP_HOST') || ! defined('MAXHAIR_SMTP_USER') || ! defined('MAXHAIR_SMTP_PASS') ) {
        return array( 'success' => false, 'error' => 'SMTP není nakonfigurován v wp-config.php' );
    }
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
    $mail = new \PHPMailer\PHPMailer\PHPMailer( true );
    try {
        $mail->isSMTP();
        $mail->Host       = MAXHAIR_SMTP_HOST;
        $mail->Port       = defined('MAXHAIR_SMTP_PORT') ? (int) MAXHAIR_SMTP_PORT : 465;
        $mail->SMTPSecure = defined('MAXHAIR_SMTP_SECURE') ? MAXHAIR_SMTP_SECURE : 'ssl';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAXHAIR_SMTP_USER;
        $mail->Password   = MAXHAIR_SMTP_PASS;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom( MAXHAIR_SMTP_USER, isset($args['from_name']) ? $args['from_name'] : 'MaxHair.cz' );
        $mail->addAddress( $to );
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->isHTML( ! empty($args['is_html']) );
        if ( ! empty($args['reply_to']) ) {
            $mail->addReplyTo( $args['reply_to']['email'], $args['reply_to']['name'] ?? '' );
        }
        $mail->send();
        return array( 'success' => true );
    } catch ( \PHPMailer\PHPMailer\Exception $e ) {
        return array( 'success' => false, 'error' => $mail->ErrorInfo );
    }
}

/**
 * MaxHair - šablona emailu (HTML)
 */
function maxhair_email_template( $args ) {
    $title  = $args['title'] ?? 'Nová zpráva';
    $fields = $args['fields'] ?? array();
    $footer = $args['footer'] ?? '';
    $primary = '#E5C158';
    $dark   = '#1A1A1A';
    $gray   = '#6B7280';
    $rows = '';
    foreach ( $fields as $label => $value ) {
        $rows .= sprintf(
            '<tr><td style="padding:12px 16px;color:%s;font-size:13px;font-weight:600;width:120px;">%s</td><td style="padding:12px 16px;color:%s;font-size:15px;">%s</td></tr>',
            esc_attr( $gray ),
            esc_html( $label ),
            esc_attr( $dark ),
            $value
        );
    }
    return sprintf(
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;font-family:\'Segoe UI\',Arial,sans-serif;background:#f5f5f5;padding:24px;">
        <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
        <div style="background:#1a1a1a;padding:24px 32px;"><span style="color:%s;font-size:12px;font-weight:600;letter-spacing:1px;">MAXHAIR.CZ</span><h1 style="margin:8px 0 0;color:%s;font-size:22px;font-weight:700;">%s</h1></div>
        <table style="width:100%%;border-collapse:collapse;"><tbody>%s</tbody></table>
        <div style="padding:16px 32px;background:#fafafa;border-top:1px solid #eee;color:%s;font-size:12px;">%s</div>
        </div></body></html>',
        esc_attr( $primary ),
        esc_attr( $primary ),
        esc_html( $title ),
        $rows,
        esc_attr( $gray ),
        esc_html( $footer )
    );
}

/**
 * MaxHair Contact Form AJAX Handler
 */
add_action('wp_enqueue_scripts', function() {
    wp_localize_script('jquery', 'maxhairAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));
});

function maxhair_contact_submit() {
    $name    = sanitize_text_field( $_POST['name'] ?? '' );
    $email   = sanitize_email( $_POST['email'] ?? '' );
    $phone   = sanitize_text_field( $_POST['phone'] ?? '' );
    $message = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( empty($name) || empty($email) || empty($phone) ) {
        wp_send_json_error('Vyplňte prosím všechna povinná pole.');
        return;
    }

    if ( ! is_email($email) ) {
        wp_send_json_error('Zadejte prosím platný email.');
        return;
    }

    $body = maxhair_email_template( array(
        'title'   => 'Nová poptávka z maxhair.cz',
        'fields'  => array(
            'Jméno'   => esc_html( $name ),
            'Email'   => '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>',
            'Telefon' => '<a href="tel:' . esc_attr( preg_replace( '/\s+/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>',
            'Zpráva'  => nl2br( esc_html( $message ) ),
        ),
        'footer'  => 'Odesláno ' . date_i18n( 'd.m.Y H:i' ),
    ) );

    $result = maxhair_smtp_send(
        'info@maxhair.cz',
        'Nová poptávka z maxhair.cz - ' . $name,
        $body,
        array(
            'is_html'  => true,
            'reply_to' => array( 'email' => $email, 'name' => $name ),
        )
    );

    if ( $result['success'] ) {
        wp_send_json_success('Děkujeme za vaši zprávu!');
    } else {
        $msg = 'Nepodařilo se odeslat zprávu. Zkuste to prosím znovu.';
        if ( ! empty($result['error']) ) {
            $msg .= ' (Chyba: ' . esc_html( $result['error'] ) . ')';
        }
        wp_send_json_error( $msg );
    }
}
add_action('wp_ajax_maxhair_contact_submit', 'maxhair_contact_submit');
add_action('wp_ajax_nopriv_maxhair_contact_submit', 'maxhair_contact_submit');

/**
 * MaxHair Contact Form - AJAX submit script (odesílá emaily přes forms@fellaship.cz na info@maxhair.cz)
 */
add_action('wp_footer', function() {
    ?>
    <script>
    (function() {
        if (typeof maxhairAjax === 'undefined') return;
        function initMaxhairForms() {
            var forms = document.querySelectorAll('form.contact-form, .kontakt-form form.form');
            forms.forEach(function(form) {
                if (form.dataset.maxhairAjax) return;
                form.dataset.maxhairAjax = '1';
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var btn = form.querySelector('button[type="submit"], .btn-submit');
                    var originalText = btn ? btn.textContent : '';
                    if (btn) { btn.disabled = true; btn.textContent = 'Odesílám...'; }
                    var fd = new FormData(form);
                    fd.append('action', 'maxhair_contact_submit');
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', maxhairAjax.ajaxurl);
                    xhr.onload = function() {
                        if (btn) { btn.disabled = false; btn.textContent = originalText; }
                        try {
                            var r = JSON.parse(xhr.responseText);
                            if (r.success) {
                                window.location.href = '/dekujeme';
                            } else {
                                alert(r.data || 'Nepodařilo se odeslat. Zkuste to prosím znovu.');
                            }
                        } catch (err) {
                            alert('Nepodařilo se odeslat. Zkuste to prosím znovu.');
                        }
                    };
                    xhr.onerror = function() {
                        if (btn) { btn.disabled = false; btn.textContent = originalText; }
                        alert('Chyba připojení. Zkuste to prosím znovu.');
                    };
                    xhr.send(fd);
                });
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMaxhairForms);
        } else {
            initMaxhairForms();
        }
    })();
    </script>
    <?php
}, 9998);

/**
 * REVISION 2 - Design overrides (high priority CSS in footer)
 */
add_action('wp_footer', function() {
    ?>
    <style id="revision2-overrides">
        /* 1. Hide WhatsApp floating bubble - Only hide OLD Bricks-generated WhatsApp elements, not custom MaxHair ones */
        .brxe-div[class*="whatsapp"]:not(.mh-whatsapp-float):not(.mh-ki-whatsapp):not([class*="mh-"]),
        #brx-content [id*="whatsapp"]:not(.mh-whatsapp-float) {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }
        
        /* 2. Remove green color from buttons */
        .brxe-button[style*="#25d366"],
        .brxe-button[style*="#25D366"],
        .brxe-button[style*="rgb(37, 211, 102)"],
        .brxe-button[style*="green"],
        a.brxe-button[style*="background-color: rgb(37"],
        a.brxe-button[style*="background: rgb(37"] {
            background-color: #1a1a1a !important;
            background: #1a1a1a !important;
            border-color: #1a1a1a !important;
        }
        
        /* 3. Align offer/pricing buttons on same level */
        .brxe-section .brxe-container > .brxe-div {
            display: flex;
            flex-direction: column;
        }
        .brxe-section .brxe-container > .brxe-div > .brxe-button:last-child {
            margin-top: auto !important;
        }
        
        /* 4. Change brown backgrounds to elegant dark blue-gray */
        .brxe-section[style*="rgb(139, 90"],
        .brxe-section[style*="rgb(160, 82"],
        .brxe-section[style*="#8B5A2B"],
        .brxe-section[style*="#A0522D"],
        .brxe-section[style*="sienna"],
        .brxe-section[style*="brown"],
        .brxe-div[style*="rgb(139, 90"],
        .brxe-div[style*="rgb(160, 82"] {
            background-color: #2C3E50 !important;
            background: #2C3E50 !important;
        }
    </style>
    <?php
}, 9999);