<?php
/**
 * Admin – Mockupy: barvy, pohledy a jednotný systém cen
 */

if (!defined('ABSPATH')) exit;

class FMB_Admin_Mockups_Zone {
    const CPT = 'fmb_mockup';

    const META_COLORS2 = '_fmb_colors2';
    const META_DESC    = '_fmb_desc';
    const META_VARIANTS= '_fmb_variants';
    const META_SIZES   = '_fmb_sizes';

    const META_PRODUCT_WIDTH  = '_fmb_product_width_cm';
    const META_PRODUCT_HEIGHT = '_fmb_product_height_cm';
    
    const META_PRICE_MODE     = '_fmb_price_mode';
    const META_PRICE_FIXED    = '_fmb_price_fixed';
    const META_PRICE_CONFIG   = '_fmb_price_config';

    public function __construct() {
        add_action('init', [$this, 'register_cpt']);
        add_action('add_meta_boxes', [$this, 'add_metaboxes']);
        add_action('save_post', [$this, 'save_meta'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
    }

    public function register_cpt() {
        if (post_type_exists(self::CPT)) return;
        register_post_type(self::CPT, [
            'labels' => [
                'name'          => __('Mockupy', 'fmb'),
                'singular_name' => __('Mockup', 'fmb'),
                'add_new_item'  => __('Přidat mockup', 'fmb'),
                'edit_item'     => __('Upravit mockup', 'fmb'),
            ],
            'public'      => false,
            'show_ui'     => true,
            'menu_icon'   => 'dashicons-format-image',
            'supports'    => ['title','thumbnail'],
            'show_in_menu'=> true,
        ]);
    }

    public function add_metaboxes() {
        add_meta_box(
            'fmb_dimensions',
            __('📐 Fyzické rozměry tiskové zóny', 'fmb'),
            [$this, 'render_dimensions'],
            self::CPT,
            'normal',
            'high'
        );

        add_meta_box(
            'fmb_pricing_unified',
            __('💰 JEDNOTNÝ SYSTÉM CEN', 'fmb'),
            [$this, 'render_pricing_unified'],
            self::CPT,
            'normal',
            'high'
        );

        add_meta_box(
            'fmb_colors_views',
            __('Barvy a per-view obrázky', 'fmb'),
            [$this, 'render_colors_views'],
            self::CPT,
            'normal',
            'high'
        );

        add_meta_box(
            'fmb_product_opts',
            __('Produkt – volby a popis', 'fmb'),
            [$this, 'render_side_opts'],
            self::CPT,
            'side',
            'default'
        );
    }

    public function assets($hook) {
        if (!in_array($hook, ['post.php','post-new.php'], true)) return;
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== self::CPT) return;

        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-resizable');

        wp_enqueue_style(
            'fmb-admin-zone',
            plugin_dir_url(__FILE__) . 'fmb-admin-zone.css',
            [],
            '4.0.0'
        );
        wp_enqueue_script(
            'fmb-admin-zone',
            plugin_dir_url(__FILE__) . 'fmb-admin-zone.js',
            ['jquery','wp-color-picker','jquery-ui-sortable','jquery-ui-draggable','jquery-ui-resizable'],
            '4.0.0',
            true
        );

        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        
        $colors2 = $post_id ? get_post_meta($post_id, self::META_COLORS2, true) : [];
        if (!is_array($colors2)) $colors2 = [];
        foreach ($colors2 as &$c) {
            $c['hex']      = isset($c['hex']) ? (string)$c['hex'] : '#ffffff';
            $c['label']    = isset($c['label']) ? (string)$c['label'] : '';
            $c['per_view'] = isset($c['per_view']) && is_array($c['per_view']) ? $c['per_view'] : [];
            $c['zones']    = isset($c['zones']) && is_array($c['zones']) ? $c['zones'] : [];
        }

        $price_mode = $post_id ? get_post_meta($post_id, self::META_PRICE_MODE, true) : 'fixed';
        if (!in_array($price_mode, ['fixed', 'area', 'formula'], true)) {
            $price_mode = 'fixed';
        }

        $price_fixed = $post_id ? get_post_meta($post_id, self::META_PRICE_FIXED, true) : 0;
        $price_config = $post_id ? get_post_meta($post_id, self::META_PRICE_CONFIG, true) : [];
        if (!is_array($price_config)) $price_config = [];

        wp_localize_script('fmb-admin-zone', 'FMB_ADMIN', [
            'nonce'      => wp_create_nonce('fmb_admin'),
            'colors2'    => $colors2,
            'priceMode'  => $price_mode,
            'priceFixed' => intval($price_fixed),
            'priceConfig'=> $price_config,
            'i18n'       => [
                'pickColor'   => __('Vybrat barvu', 'fmb'),
                'addColor'    => __('Přidat barvu', 'fmb'),
                'remove'      => __('Odebrat', 'fmb'),
                'chooseImage' => __('Vybrat obrázek', 'fmb'),
                'view'        => __('View', 'fmb'),
                'front'       => __('Zepředu', 'fmb'),
                'label'       => __('Název', 'fmb'),
                'zone'        => __('Zóna (%, levá / horní / šířka / výška)', 'fmb'),
            ],
        ]);
    }

    public function render_dimensions($post) {
        $width = get_post_meta($post->ID, self::META_PRODUCT_WIDTH, true);
        $height = get_post_meta($post->ID, self::META_PRODUCT_HEIGHT, true);
        ?>
        <div class="fmb-admin-block">
            <p class="desc">
                <?php esc_html_e('Nastavte fyzické rozměry tiskové zóny (plochy kde se tiskuje/vysívá návrh).', 'fmb'); ?>
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div style="padding: 16px; background: #f0f7ff; border-radius: 8px; border: 1px solid #bee3f8;">
                    <h4 style="margin: 0 0 12px;">📏 Rozměry tiskové plochy</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 20px 1fr; gap: 10px; margin-bottom: 12px;">
                        <label>
                            <strong><?php esc_html_e('Šířka (cm)', 'fmb'); ?></strong><br>
                            <input type="number" 
                                   name="<?php echo esc_attr(self::META_PRODUCT_WIDTH); ?>" 
                                   value="<?php echo esc_attr($width); ?>"
                                   placeholder="25" min="1" step="0.1" 
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 4px;">
                        </label>
                        <span style="display: flex; align-items: flex-end; justify-content: center; font-weight: bold; margin-bottom: 6px;">×</span>
                        <label>
                            <strong><?php esc_html_e('Výška (cm)', 'fmb'); ?></strong><br>
                            <input type="number" 
                                   name="<?php echo esc_attr(self::META_PRODUCT_HEIGHT); ?>" 
                                   value="<?php echo esc_attr($height); ?>"
                                   placeholder="17" min="1" step="0.1"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 4px;">
                        </label>
                    </div>

                    <p style="font-size: 12px; color: #666; margin: 0; line-height: 1.6;">
                        💡 <strong>Příklad:</strong> Pokud je tisková zóna na triku 25 × 17 cm, zadej právě tyhle rozměry.
                    </p>
                </div>

                <div style="padding: 16px; background: #fff8f0; border-radius: 8px; border: 1px solid #fed7aa;">
                    <h4 style="margin: 0 0 12px;">✨ Jak se používá?</h4>
                    <p style="font-size: 12px; line-height: 1.6; margin: 0;">
                        1. Uživatel nahraje design<br>
                        2. Jeho plocha se vypočítá z fyzických rozměrů<br>
                        3. Cena se spočítá podle zvoleného módu<br>
                        4. Uvidí náhled s přesnou velikostí
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_pricing_unified($post) {
        $mode = get_post_meta($post->ID, self::META_PRICE_MODE, true) ?: 'fixed';
        $fixed = get_post_meta($post->ID, self::META_PRICE_FIXED, true) ?: 0;
        $config = get_post_meta($post->ID, self::META_PRICE_CONFIG, true) ?: [];
        if (!is_array($config)) $config = [];

        $default_config = [
            'fixed' => [
                'print' => 100,
                'emb' => 200,
            ],
            'area' => [
                'print' => ['base' => 200, 'per_cm2' => 5, 'min_cm2' => 50, 'max_cm2' => 5000],
                'emb' => ['base' => 500, 'per_cm2' => 10, 'min_cm2' => 50, 'max_cm2' => 5000],
            ],
            'formula' => [
                'print' => ['tokens' => [], 'info' => 'base * qty * 1.0'],
                'emb' => ['tokens' => [], 'info' => 'base * qty * 1.5'],
            ]
        ];

        // OPRAVENO: Hluboké sloučení s výchozími hodnotami
        foreach ($default_config as $m => $defaults) {
            if (!isset($config[$m])) {
                $config[$m] = $defaults;
            } else {
                foreach ($defaults as $type => $subdefaults) {
                    if (!isset($config[$m][$type])) {
                        $config[$m][$type] = $subdefaults;
                    } else if (is_array($subdefaults) && is_array($config[$m][$type])) {
                        // Sloučit i jednotlivé klíče uvnitř
                        foreach ($subdefaults as $key => $value) {
                            if (!isset($config[$m][$type][$key])) {
                                $config[$m][$type][$key] = $value;
                            }
                        }
                    }
                }
            }
        }
        ?>
        <div class="fmb-admin-block">
            <div class="fmb-price-tabs">
                <div class="fmb-price-mode-selector">
                    <button type="button" class="fmb-price-mode-btn" data-mode="fixed" 
                        <?php echo $mode === 'fixed' ? 'style="background:#23b1bf;color:#fff"' : ''; ?>>
                        💰 Fixní cena
                    </button>
                    <button type="button" class="fmb-price-mode-btn" data-mode="area" 
                        <?php echo $mode === 'area' ? 'style="background:#23b1bf;color:#fff"' : ''; ?>>
                        📐 Cena podle plochy
                    </button>
                    <button type="button" class="fmb-price-mode-btn" data-mode="formula" 
                        <?php echo $mode === 'formula' ? 'style="background:#23b1bf;color:#fff"' : ''; ?>>
                        🧮 Vlastní vzorec
                    </button>
                </div>

                <input type="hidden" name="<?php echo esc_attr(self::META_PRICE_MODE); ?>" 
                       value="<?php echo esc_attr($mode); ?>" id="fmb_price_mode_field">
                <input type="hidden" name="<?php echo esc_attr(self::META_PRICE_CONFIG); ?>" 
                       value="" id="fmb_price_config_field">

                <!-- MÓD: FIXNÍ CENA -->
                <div class="fmb-price-content" data-mode="fixed" 
                    <?php echo $mode !== 'fixed' ? 'style="display:none"' : ''; ?>>
                    <p class="desc">Jednoduché nastavení: stejná cena pro potisk i výšivku (nebo jiná).</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:20px;">
                        <div style="padding:16px; border:1px solid #e6eaf2; border-radius:8px; background:#f8fafc;">
                            <label>
                                <strong>💻 Potisk – cena (Kč)</strong><br>
                                <input type="number" min="0" step="1" class="fmb-price-input" 
                                    data-type="print" data-key="price"
                                    value="<?php echo esc_attr($config['fixed']['print'] ?? 100); ?>"
                                    style="width:100%; padding:8px; margin-top:8px; border:1px solid #ddd; border-radius:4px;">
                            </label>
                        </div>
                        <div style="padding:16px; border:1px solid #e6eaf2; border-radius:8px; background:#f8fafc;">
                            <label>
                                <strong>✨ Výšivka – cena (Kč)</strong><br>
                                <input type="number" min="0" step="1" class="fmb-price-input" 
                                    data-type="emb" data-key="price"
                                    value="<?php echo esc_attr($config['fixed']['emb'] ?? 200); ?>"
                                    style="width:100%; padding:8px; margin-top:8px; border:1px solid #ddd; border-radius:4px;">
                            </label>
                        </div>
                    </div>
                    <div style="margin-top:16px; padding:12px; background:#f0fff0; border-radius:6px; border-left:4px solid #00aa00;">
                        <strong>💡 Náhled:</strong> Potisk = <span class="fmb-price-preview-fixed-print">100</span> Kč, Výšivka = <span class="fmb-price-preview-fixed-emb">200</span> Kč
                    </div>
                </div>

                <!-- MÓD: CENA PODLE PLOCHY -->
                <div class="fmb-price-content" data-mode="area" 
                    <?php echo $mode !== 'area' ? 'style="display:none"' : ''; ?>>
                    <p class="desc">Cena se vypočítá podle plochy obrázku v cm². Ideální pro pravidelné designs.</p>
                    <div style="display:grid; gap:20px; margin-top:20px;">
                        <!-- POTISK -->
                        <div style="border:1px solid #bee3f8; border-radius:8px; padding:16px; background:#f0f7ff;">
                            <h4 style="margin:0 0 12px; color:#0c5aa0;">💻 Potisk</h4>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <label>
                                    <strong>Základní cena (Kč)</strong><br>
                                    <input type="number" min="0" step="1" class="fmb-price-input" 
                                        data-type="print" data-key="base"
                                        value="<?php echo esc_attr($config['area']['print']['base'] ?? 200); ?>"
                                        style="width:100%; padding:8px; margin-top:4px; border:1px solid #ddd; border-radius:4px;">
                                    <small style="color:#666;">Minimální cena za každý potisk</small>
                                </label>
                                <label>
                                    <strong>Cena za cm² (Kč)</strong><br>
                                    <input type="number" min="0" step="0.1" class="fmb-price-input" 
                                        data-type="print" data-key="per_cm2"
                                        value="<?php echo esc_attr($config['area']['print']['per_cm2'] ?? 5); ?>"
                                        style="width:100%; padding:8px; margin-top:4px; border:1px solid #ddd; border-radius:4px;">
                                    <small style="color:#666;">Přičítá se k základní ceně</small>
                                </label>
                                <label>
                                    <strong>Minimální plocha (cm²)</strong><br>
                                    <input type="number" min="0" step="1" class="fmb-price-input" 
                                        data-type="print" data-key="min_cm2"
                                        value="<?php echo esc_attr($config['area']['print']['min_cm2'] ?? 50); ?>"
                                        style="width:100%; padding:8px; margin-top:4px; border:1px solid #ddd; border-radius:4px;">
                                    <small style="color:#666;">Varování pokud je menší</small>
                                </label>
                                <label>
                                    <strong>Maximální plocha (cm²)</strong><br>
                                    <input type="number" min="0" step="1" class="fmb-price-input" 
                                        data-type="print" data-key="max_cm2"
                                        value="<?php echo esc_attr($config['area']['print']['max_cm2'] ?? 5000); ?>"
                                        style="width:100%; padding:8px; margin-top:4px; border:1px solid #ddd; border-radius:4px;">
                                    <small style="color:#666;">Varování pokud je větší</small>
                                </label>
                            </div>
                            <div style="margin-top:12px; padding:10px; background:#fff; border-radius:4px; border-left:3px solid #0c5aa0;">
                                <strong style="color:#0c5aa0;">Příklad:</strong> 25×17cm obrázek = 425 cm²<br>
                                Cena: 200 Kč + (425 × 5 Kč) = <span class="fmb-price-preview-area-print"><strong>2325 Kč</strong></span>
                            </div>
                        </div>

                        <!-- VÝŠIVKA -->
                        <div style="border:1px solid #fed7aa; border-radius:8px; padding:16px; background:#fff8f0;">
                            <h4 style="margin:0 0 12px; color:#d97706;">✨ Výšivka</h4>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <label>
                                    <strong>Základní cena (Kč)</strong><br>
                                    <input type="number" min="0" step="1" class="fmb-price-input" 
                                        data-type="emb" data-key="base"
                                        value="<?php echo esc_attr($config['area']['emb']['base'] ?? 500); ?>"
                                        style="width:100%; padding:8px; margin-top:4px; border:1px solid #ddd; border-radius:4px;">
                                    <small style="color:#666;">Minimální cena za každou výšivku</small>
                                </label>
                                <label>
                                    <strong>Cena za cm² (Kč)</strong><br>
                                    <input type="number" min="0" step="0.1" class="fmb-price-input" 
                                        data-type="emb" data-key="per_cm2"
                                        value="<?php echo esc_attr($config['area']['emb']['per_cm2'] ?? 10); ?>"
                                        style="width:100%; padding:8px; margin-top:4px; border:1px solid #ddd; border-radius:4px;">
                                    <small style="color:#666;">Přičítá se k základní ceně</small>
                                </label>
                                <label>
                                    <strong>Minimální plocha (cm²)</strong><br>
                                    <input type="number" min="0" step="1" class="fmb-price-input" 
                                        data-type="emb" data-key="min_cm2"
                                        value="<?php echo esc_attr($config['area']['emb']['min_cm2'] ?? 50); ?>"
                                        style="width:100%; padding:8px; margin-top:4px; border:1px solid #ddd; border-radius:4px;">
                                    <small style="color:#666;">Varování pokud je menší</small>
                                </label>
                                <label>
                                    <strong>Maximální plocha (cm²)</strong><br>
                                    <input type="number" min="0" step="1" class="fmb-price-input" 
                                        data-type="emb" data-key="max_cm2"
                                        value="<?php echo esc_attr($config['area']['emb']['max_cm2'] ?? 5000); ?>"
                                        style="width:100%; padding:8px; margin-top:4px; border:1px solid #ddd; border-radius:4px;">
                                    <small style="color:#666;">Varování pokud je větší</small>
                                </label>
                            </div>
                            <div style="margin-top:12px; padding:10px; background:#fff; border-radius:4px; border-left:3px solid #d97706;">
                                <strong style="color:#d97706;">Příklad:</strong> 25×17cm obrázek = 425 cm²<br>
                                Cena: 500 Kč + (425 × 10 Kč) = <span class="fmb-price-preview-area-emb"><strong>4750 Kč</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MÓD: VLASTNÍ VZOREC -->
                <div class="fmb-price-content" data-mode="formula" 
                    <?php echo $mode !== 'formula' ? 'style="display:none"' : ''; ?>>
                    <p class="desc">Pokročilý mód: vytvoř si vlastní vzorec pro výpočet ceny.</p>
                    <div style="margin-top:20px;">
                        <div style="display:grid; gap:20px;">
                            <div style="border:1px solid #bee3f8; border-radius:8px; padding:16px; background:#f0f7ff;">
                                <h4 style="margin:0 0 12px; color:#0c5aa0;">💻 Potisk – Vzorec</h4>
                                <div class="fmb-formula-mini" data-type="print"></div>
                            </div>

                            <div style="border:1px solid #fed7aa; border-radius:8px; padding:16px; background:#fff8f0;">
                                <h4 style="margin:0 0 12px; color:#d97706;">✨ Výšivka – Vzorec</h4>
                                <div class="fmb-formula-mini" data-type="emb"></div>
                            </div>
                        </div>
                        <p style="margin-top:16px; font-size:12px; color:#666;">
                            <strong>Dostupné proměnné:</strong> base, qty, area, area_cm2, option_mult<br>
                            <strong>Operátory:</strong> +, −, ×, ÷, (, )<br>
                            <strong>Příklady:</strong> base * qty | (base + area * 2) * qty | base * qty * 1.2
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_colors_views($post) {
        echo '<input type="hidden" id="fmb_colors2_field" name="fmb_colors2_field" value="" />';
        echo '<div class="fmb-admin-block">';
        echo '<p class="desc">'.esc_html__('Každá barva může mít vlastní obrázky pro jednotlivé pohledy.', 'fmb').'</p>';
        echo '<div id="fmb-colors2-root"></div>';
        echo '<div class="fmb-admin-actions">';
        echo '  <button type="button" class="button button-primary" id="fmb-color-add">'.esc_html__('Přidat barvu', 'fmb').'</button>';
        echo '</div>';
        echo '</div>';
        wp_nonce_field('fmb_admin_save', 'fmb_admin_nonce');
    }

    public function render_side_opts($post) {
        $variants   = get_post_meta($post->ID, self::META_VARIANTS, true);
        $sizes      = get_post_meta($post->ID, self::META_SIZES, true);
        $desc       = get_post_meta($post->ID, self::META_DESC, true);
        ?>
        <p><label><strong><?php esc_html_e('Varianty (CSV):', 'fmb');?></strong><br>
            <input type="text" name="<?php echo esc_attr(self::META_VARIANTS);?>" value="<?php echo esc_attr(is_array($variants)?implode(', ',$variants):$variants);?>" placeholder="Classic, Premium, Organic">
        </label></p>

        <p><label><strong><?php esc_html_e('Velikosti (CSV):', 'fmb');?></strong><br>
            <input type="text" name="<?php echo esc_attr(self::META_SIZES);?>" value="<?php echo esc_attr(is_array($sizes)?implode(', ',$sizes):$sizes);?>" placeholder="S, M, L, XL, XXL">
        </label></p>

        <p><label><strong><?php esc_html_e('Krátký popis:', 'fmb');?></strong><br>
            <textarea name="<?php echo esc_attr(self::META_DESC);?>" rows="5"><?php echo esc_textarea($desc);?></textarea>
        </label></p>
        <?php
    }

    public function save_meta($post_id, $post) {
        if ($post->post_type !== self::CPT) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['fmb_admin_nonce']) || !wp_verify_nonce($_POST['fmb_admin_nonce'], 'fmb_admin_save')) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $json = isset($_POST['fmb_colors2_field']) ? wp_unslash($_POST['fmb_colors2_field']) : '';
        $data = json_decode($json, true);
        if (!is_array($data)) $data = [];
        foreach ($data as &$c) {
            $c['hex']   = isset($c['hex']) ? sanitize_text_field($c['hex']) : '#ffffff';
            $c['label'] = isset($c['label']) ? sanitize_text_field($c['label']) : '';
            if (!isset($c['per_view']) || !is_array($c['per_view'])) $c['per_view'] = [];
            if (isset($c['zones']) && is_array($c['zones'])) {
                foreach ($c['zones'] as $vi => &$z) {
                    $z['x'] = isset($z['x']) ? round(floatval($z['x']), 1) : 0;
                    $z['y'] = isset($z['y']) ? round(floatval($z['y']), 1) : 0;
                    $z['w'] = isset($z['w']) ? round(floatval($z['w']), 1) : 100;
                    $z['h'] = isset($z['h']) ? round(floatval($z['h']), 1) : 100;
                    $z['label'] = isset($z['label']) ? sanitize_text_field($z['label']) : '';
                }
            } else {
                $c['zones'] = [];
            }
        }
        update_post_meta($post_id, self::META_COLORS2, $data);

        $variants = isset($_POST[self::META_VARIANTS]) ? sanitize_text_field($_POST[self::META_VARIANTS]) : '';
        $sizes    = isset($_POST[self::META_SIZES]) ? sanitize_text_field($_POST[self::META_SIZES]) : '';
        update_post_meta($post_id, self::META_VARIANTS, $variants ? array_map('trim', explode(',', $variants)) : []);
        update_post_meta($post_id, self::META_SIZES,    $sizes ? array_map('trim', explode(',', $sizes)) : []);
        
        $desc  = isset($_POST[self::META_DESC]) ? wp_kses_post($_POST[self::META_DESC]) : '';
        update_post_meta($post_id, self::META_DESC, $desc);

        $product_width = isset($_POST[self::META_PRODUCT_WIDTH]) ? floatval($_POST[self::META_PRODUCT_WIDTH]) : 0;
        $product_height = isset($_POST[self::META_PRODUCT_HEIGHT]) ? floatval($_POST[self::META_PRODUCT_HEIGHT]) : 0;
        update_post_meta($post_id, self::META_PRODUCT_WIDTH, $product_width);
        update_post_meta($post_id, self::META_PRODUCT_HEIGHT, $product_height);

        $price_mode = isset($_POST[self::META_PRICE_MODE]) ? sanitize_text_field($_POST[self::META_PRICE_MODE]) : 'fixed';
        if (!in_array($price_mode, ['fixed', 'area', 'formula'], true)) {
            $price_mode = 'fixed';
        }
        update_post_meta($post_id, self::META_PRICE_MODE, $price_mode);

        $price_config_raw = isset($_POST[self::META_PRICE_CONFIG]) ? wp_unslash($_POST[self::META_PRICE_CONFIG]) : '';
        $price_config = json_decode($price_config_raw, true);
        if (!is_array($price_config)) $price_config = [];
        update_post_meta($post_id, self::META_PRICE_CONFIG, $price_config);
    }
}

new FMB_Admin_Mockups_Zone();