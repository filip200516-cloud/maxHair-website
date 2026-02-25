<?php
/**
 * Plugin Name: FMB Standalone Builder
 * Description: Frontend konfigurátor mockupů s jednotným systémem cen a vlastním košíkem
 * Version: 4.0.1
 */

if (!defined('ABSPATH')) exit;

define('FMB_SB_VER', '4.0.1');
define('FMB_SB_DIR', plugin_dir_path(__FILE__));
define('FMB_SB_URL', plugin_dir_url(__FILE__));

require_once FMB_SB_DIR . 'admin-metabox-zone.php';

final class FMB_SB_Plugin {
    
    const CART_PAGE_SLUG = 'vlastni-objednavka';
    
    public function __construct() {
        add_action('init', [$this, 'register_cpt_alias']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        
        // Shortcodes
        add_shortcode('fmb_builder', [$this, 'shortcode_builder']);
        add_shortcode('fmb_cart', [$this, 'shortcode_cart']);

        // AJAX handlers
        add_action('wp_ajax_fmb_list_mockups', [$this, 'ajax_list_mockups']);
        add_action('wp_ajax_nopriv_fmb_list_mockups', [$this, 'ajax_list_mockups']);

        add_action('wp_ajax_fmb_send_quote', [$this, 'ajax_send_quote']);
        add_action('wp_ajax_nopriv_fmb_send_quote', [$this, 'ajax_send_quote']);
    }

    public function register_cpt_alias() {
        if (!post_type_exists(FMB_Admin_Mockups_Zone::CPT)) {
            register_post_type(FMB_Admin_Mockups_Zone::CPT, [
                'labels'=>['name'=>__('Mockupy','fmb'),'singular_name'=>__('Mockup','fmb')],
                'public'=>false,'show_ui'=>true,'menu_icon'=>'dashicons-format-image',
                'supports'=>['title','thumbnail'],
            ]);
        }
    }

    public function register_assets() {
        wp_register_script(
            'fabric',
            'https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js',
            [],
            '5.3.0',
            true
        );

        wp_register_style('fmb-standalone', FMB_SB_URL.'fmb-standalone.css', [], FMB_SB_VER);
        wp_register_script('fmb-standalone', FMB_SB_URL.'fmb-standalone.js', ['fabric', 'jquery', 'jquery-ui-sortable'], FMB_SB_VER, true);
        
        wp_register_style('fmb-cart', FMB_SB_URL.'fmb-cart.css', [], FMB_SB_VER);
        wp_register_script('fmb-cart', FMB_SB_URL.'fmb-cart.js', ['jquery'], FMB_SB_VER, true);
    }

    private function get_api_data() {
        $user = wp_get_current_user();
        $cart_url = home_url('/' . self::CART_PAGE_SLUG . '/');
        $builder_url = get_site_url() . '/vytvor-si-vlastni-vec/';
        
        return [
            'ajaxurl'     => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('fmb_ajax'),
            'builderUrl'  => $builder_url,
            'cartUrl'     => $cart_url,
            'prefill' => [
                'isLogged' => is_user_logged_in(),
                'name'     => $user && $user->ID ? $user->display_name : '',
                'email'    => $user && $user->ID ? $user->user_email : '',
                'phone'    => $user && $user->ID ? get_user_meta($user->ID, 'billing_phone', true) : '',
                'company'  => $user && $user->ID ? get_user_meta($user->ID, 'billing_company', true) : '',
                'ico'      => $user && $user->ID ? get_user_meta($user->ID, 'billing_ico', true) : '',
                'dic'      => $user && $user->ID ? get_user_meta($user->ID, 'billing_dic', true) : '',
                'address'  => $user && $user->ID ? get_user_meta($user->ID, 'billing_address_1', true) : '',
                'city'     => $user && $user->ID ? get_user_meta($user->ID, 'billing_city', true) : '',
                'postcode' => $user && $user->ID ? get_user_meta($user->ID, 'billing_postcode', true) : '',
            ],
        ];
    }

    public function shortcode_builder($atts=[]) {
        wp_enqueue_style('fmb-standalone');
        wp_enqueue_script('fmb-standalone');
        wp_localize_script('fmb-standalone', 'FMB_API', $this->get_api_data());

        ob_start(); ?>
        <div class="fmb-sb">
          <div class="fmb-sb-layout">
            
            <!-- ===== LEVÝ PANEL - Views + Tools ===== -->
            <div class="fmb-sb-left">
              <div id="fmb-views" class="fmb-sb-views"></div>
              
              <div class="fmb-sb-tools">
                <div class="fmb-sb-tools-title">Nástroje</div>
                <div id="fmb-tools-host"></div>
              </div>
            </div>
            
            <!-- ===== STŘEDNÍ PANEL - Preview + Mockupy ===== -->
            <div class="fmb-sb-center">
              <div class="fmb-sb-preview" id="fmb-preview"></div>
              <h3 class="fmb-sb-h3">Vyber mockup</h3>
              <div class="fmb-sb-mockup-list" id="fmb-mockup-list"></div>
            </div>
            
            <!-- ===== PRAVÝ PANEL - Nastavení produktu ===== -->
            <aside class="fmb-sb-right">
              <div class="fmb-pd">
                <div class="fmb-pd-title" id="fmb-p-title">Vyber produkt</div>
                <p class="fmb-pd-desc" id="fmb-p-desc">Krásné tričko, originální dárek. Kvalitní materiál. Vlastní design.</p>

                <div class="fmb-pd-group">
                  <div class="fmb-pd-label">TYP PROVEDENÍ</div>
                  <div class="fmb-pills" id="fmb-design-type">
                    <button type="button" class="fmb-pill active" data-type="print">Potisk</button>
                    <button type="button" class="fmb-pill" data-type="emb">Výšivka</button>
                  </div>
                </div>

                <div class="fmb-pd-group">
                  <div class="fmb-pd-label">VYBER VARIANTU</div>
                  <div class="fmb-pills" id="fmb-variants"></div>
                </div>

                <div class="fmb-pd-group">
                  <div class="fmb-pd-label">VYBER VELIKOST</div>
                  <div class="fmb-sizes" id="fmb-sizes"></div>
                </div>

                <div class="fmb-pd-group">
                  <div class="fmb-pd-label">VYBER BARVU</div>
                  <div class="fmb-swatches" id="fmb-colors"></div>
                </div>

                <div class="fmb-pd-qtyprice">
                  <div class="fmb-qty">
                    <div class="fmb-pd-label">POČET KUSŮ</div>
                    <div class="fmb-stepper">
                      <button type="button" class="fmb-stepper-btn" data-op="-">−</button>
                      <input type="number" id="fmb-qty" min="1" value="1"/>
                      <button type="button" class="fmb-stepper-btn" data-op="+">+</button>
                    </div>
                  </div>
                  <div class="fmb-price">
                    <div class="fmb-pd-label muted">CENA</div>
                    <div class="fmb-price-val"><span id="fmb-price">0 Kč</span></div>
                  </div>
                </div>

                <button class="fmb-btn-buy" id="fmb-add-to-cart">🛒 PŘIDAT K OBJEDNÁVCE</button>
                
                <a href="<?php echo esc_url(home_url('/' . self::CART_PAGE_SLUG . '/')); ?>" class="fmb-btn-cart-link" id="fmb-go-to-cart">
                  <span class="fmb-cart-icon">📋</span>
                  <span class="fmb-cart-text">Zobrazit objednávku</span>
                  <span class="fmb-cart-badge" id="fmb-cart-badge">0</span>
                </a>
              </div>
            </aside>
          </div>

          <input type="file" id="fmb-file-input" accept="image/*" hidden>
        </div>
        
        <!-- Toast notifikace -->
        <div class="fmb-toast" id="fmb-toast" hidden>
          <span class="fmb-toast-icon">✓</span>
          <span class="fmb-toast-text"></span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * MULTIPAGE CART SHORTCODE
     */
    public function shortcode_cart($atts=[]) {
        wp_enqueue_style('fmb-cart');
        wp_enqueue_script('fmb-cart');
        wp_localize_script('fmb-cart', 'FMB_CART_API', $this->get_api_data());

        ob_start(); ?>
        <div class="fmb-cart-page">
          <div class="fmb-cart-container">
            
            <!-- ===== HEADER ===== -->
            <div class="fmb-cart-header">
              <h1 class="fmb-cart-title">📋 Vaše objednávka</h1>
              <a href="<?php echo esc_url(get_site_url() . '/vytvor-si-vlastni-vec/'); ?>" class="fmb-cart-back">
                ← Pokračovat v konfiguraci
              </a>
            </div>

            <!-- ===== PROGRESS STEPS ===== -->
            <div class="fmb-cart-steps">
              <div class="fmb-cart-step active" id="fmb-step-1">
                <span class="fmb-cart-step-number">1</span>
                <span>Položky</span>
              </div>
              <div class="fmb-cart-step-divider" id="fmb-step-divider-1"></div>
              <div class="fmb-cart-step" id="fmb-step-2">
                <span class="fmb-cart-step-number">2</span>
                <span>Kontakt & odeslání</span>
              </div>
            </div>

            <!-- ===== PRÁZDNÝ KOŠÍK ===== -->
            <div class="fmb-cart-empty" id="fmb-cart-empty" hidden>
              <div class="fmb-cart-empty-icon">🛒</div>
              <h2>Vaše objednávka je prázdná</h2>
              <p>Přidejte si produkty z konfigurátoru.</p>
              <a href="<?php echo esc_url(get_site_url() . '/vytvor-si-vlastni-vec/'); ?>" class="fmb-btn-primary">
                Přejít do konfigurátoru
              </a>
            </div>

            <!-- ===== PAGE 1: POLOŽKY + SOUHRN ===== -->
            <div class="fmb-cart-page-section" id="fmb-cart-page1">
              <div class="fmb-cart-page1-layout">
                
                <!-- Seznam položek -->
                <div class="fmb-cart-items" id="fmb-cart-items">
                  <!-- Položky se vygenerují JS -->
                </div>
                
                <!-- Sidebar - Souhrn -->
                <div class="fmb-cart-sidebar">
                  <div class="fmb-cart-summary">
                    <h3>📊 Souhrn objednávky</h3>
                    <div class="fmb-cart-summary-row">
                      <span>Počet položek:</span>
                      <strong id="fmb-cart-count">0</strong>
                    </div>
                    <div class="fmb-cart-summary-row">
                      <span>Celkem kusů:</span>
                      <strong id="fmb-cart-total-qty">0</strong>
                    </div>
                    <div class="fmb-cart-summary-row fmb-cart-summary-total">
                      <span>Orientační cena:</span>
                      <strong id="fmb-cart-total-price">0 Kč</strong>
                    </div>
                    
                    <button type="button" class="fmb-btn-continue" id="fmb-continue-btn">
                      Pokračovat k odeslání →
                    </button>
                  </div>
                </div>
                
              </div>
            </div>

            <!-- ===== PAGE 2: KONTAKTNÍ FORMULÁŘ ===== -->
            <div class="fmb-cart-page-section" id="fmb-cart-page2">
              <div class="fmb-cart-page2-layout">
                
                <!-- Formulář -->
                <div class="fmb-cart-form">
                  <h3>👤 Kontaktní údaje</h3>
                  
                  <?php if (!is_user_logged_in()): ?>
                  <div class="fmb-cart-login-hint">
                    Máte účet? <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">Přihlaste se</a> pro rychlejší vyplnění.
                  </div>
                  <?php endif; ?>
                  
                  <div class="fmb-form-row">
                    <label class="fmb-form-label">
                      Jméno a příjmení <span class="required">*</span>
                      <input type="text" id="fmb-form-name" class="fmb-form-input" placeholder="Jan Novák" required>
                    </label>
                  </div>
                  
                  <div class="fmb-form-row">
                    <label class="fmb-form-label">
                      E-mail <span class="required">*</span>
                      <input type="email" id="fmb-form-email" class="fmb-form-input" placeholder="jan@example.cz" required>
                    </label>
                  </div>
                  
                  <div class="fmb-form-row">
                    <label class="fmb-form-label">
                      Telefon <span class="required">*</span>
                      <input type="tel" id="fmb-form-phone" class="fmb-form-input" placeholder="+420 123 456 789" required>
                    </label>
                  </div>
                  
                  <!-- Fakturace na firmu -->
                  <div class="fmb-form-row">
                    <label class="fmb-form-checkbox fmb-form-checkbox-company">
                      <input type="checkbox" id="fmb-form-is-company">
                      <span>Nakupuji na firmu (chci fakturu na IČO)</span>
                    </label>
                  </div>
                  
                  <!-- Firemní údaje - skryté dokud není zaškrtnuto -->
                  <div class="fmb-form-company-fields" id="fmb-company-fields" style="display: none;">
                    <div class="fmb-form-row">
                      <label class="fmb-form-label">
                        Název firmy <span class="required">*</span>
                        <input type="text" id="fmb-form-company" class="fmb-form-input" placeholder="Název společnosti s.r.o.">
                      </label>
                    </div>
                    
                    <div class="fmb-form-row fmb-form-row-half">
                      <label class="fmb-form-label">
                        IČO <span class="required">*</span>
                        <input type="text" id="fmb-form-ico" class="fmb-form-input" placeholder="12345678">
                      </label>
                      <label class="fmb-form-label">
                        DIČ
                        <input type="text" id="fmb-form-dic" class="fmb-form-input" placeholder="CZ12345678">
                      </label>
                    </div>
                  </div>
                  
                  <div class="fmb-form-row">
                    <label class="fmb-form-label">
                      Ulice a číslo popisné
                      <input type="text" id="fmb-form-address" class="fmb-form-input" placeholder="Hlavní 123">
                    </label>
                  </div>
                  
                  <div class="fmb-form-row fmb-form-row-half">
                    <label class="fmb-form-label">
                      Město
                      <input type="text" id="fmb-form-city" class="fmb-form-input" placeholder="Praha">
                    </label>
                    <label class="fmb-form-label">
                      PSČ
                      <input type="text" id="fmb-form-postcode" class="fmb-form-input" placeholder="110 00">
                    </label>
                  </div>
                  
                  <div class="fmb-form-row">
                    <label class="fmb-form-label">
                      Poznámka k objednávce
                      <textarea id="fmb-form-note" class="fmb-form-textarea" rows="3" placeholder="Máte nějaké speciální požadavky? Napište nám..."></textarea>
                    </label>
                  </div>
                  
                  <div class="fmb-form-row">
                    <label class="fmb-form-checkbox">
                      <input type="checkbox" id="fmb-form-gdpr" required>
                      <span>Souhlasím se <a href="/ochrana-osobnich-udaju/" target="_blank">zpracováním osobních údajů</a> <span class="required">*</span></span>
                    </label>
                  </div>
                </div>
                
                <!-- Sidebar - Finální souhrn -->
                <div class="fmb-cart-final-summary">
                  <h3>📦 Vaše objednávka</h3>
                  
                  <div class="fmb-cart-final-items" id="fmb-final-items">
                    <!-- Items se vygenerují JS -->
                  </div>
                  
                  <div class="fmb-cart-final-total">
                    <span class="fmb-cart-final-total-label">Celková cena:</span>
                    <span class="fmb-cart-final-total-value" id="fmb-final-total">0 Kč</span>
                  </div>
                  
                  <button type="button" class="fmb-btn-back" id="fmb-back-btn">
                    ← Zpět k položkám
                  </button>
                  
                  <button type="button" class="fmb-btn-submit" id="fmb-submit-order">
                    ✉️ ODESLAT POPTÁVKU
                  </button>
                  
                  <p class="fmb-form-hint">
                    Po odeslání vás budeme kontaktovat s cenovou nabídkou a dalšími detaily.
                  </p>
                </div>
                
              </div>
            </div>

          </div>
        </div>
        
        <!-- Toast notifikace -->
        <div class="fmb-toast" id="fmb-toast" hidden>
          <span class="fmb-toast-icon">✓</span>
          <span class="fmb-toast-text"></span>
        </div>
        <?php
        return ob_get_clean();
    }

    public function ajax_list_mockups() {
        if (!check_ajax_referer('fmb_ajax', 'nonce', false)) {
            wp_send_json_error([
                'message' => 'Neplatný nebo vypršelý bezpečnostní token. Zkuste stránku obnovit (F5).'
            ]);
        }

        $q = new WP_Query([
            'post_type'=>FMB_Admin_Mockups_Zone::CPT,
            'posts_per_page'=>-1,
            'post_status'=>'publish',
        ]);

        $out = [];
        while ($q->have_posts()) {
            $q->the_post();
            $id = get_the_ID();

            $colors2_meta = get_post_meta($id, FMB_Admin_Mockups_Zone::META_COLORS2, true);
            if (!is_array($colors2_meta)) $colors2_meta = [];

            $views = [];
            $colors2 = [];
            
            if (!empty($colors2_meta) && isset($colors2_meta[0]['zones']) && is_array($colors2_meta[0]['zones'])) {
                foreach ($colors2_meta[0]['zones'] as $vIdx => $zone) {
                    $views[] = [
                        'label'    => isset($zone['label']) ? $zone['label'] : "View " . ($vIdx + 1),
                        'base'     => 0,
                        'base_url' => '',
                        'x' => isset($zone['x']) ? floatval($zone['x']) : 20,
                        'y' => isset($zone['y']) ? floatval($zone['y']) : 20,
                        'w' => isset($zone['w']) ? floatval($zone['w']) : 60,
                        'h' => isset($zone['h']) ? floatval($zone['h']) : 60,
                    ];
                }
            }

            foreach ($colors2_meta as $cIdx => $c) {
                $colorItem = [
                    'hex' => isset($c['hex']) ? sanitize_text_field($c['hex']) : '#ffffff',
                    'label' => isset($c['label']) ? sanitize_text_field($c['label']) : '',
                    'per_view' => [],
                ];
                
                if (isset($c['per_view']) && is_array($c['per_view'])) {
                    foreach ($c['per_view'] as $vi => $viewData) {
                        $url = '';
                        
                        if (is_array($viewData)) {
                            if (isset($viewData['id']) && intval($viewData['id']) > 0) {
                                $url = wp_get_attachment_image_url(intval($viewData['id']), 'large');
                            }
                            if (empty($url) && isset($viewData['url']) && !empty($viewData['url'])) {
                                $url = $viewData['url'];
                            }
                        } elseif (is_numeric($viewData) && intval($viewData) > 0) {
                            $url = wp_get_attachment_image_url(intval($viewData), 'large');
                        } elseif (is_string($viewData)) {
                            $url = $viewData;
                        }
                        
                        $colorItem['per_view'][$vi] = $url ?: '';
                    }
                }
                
                $colors2[] = $colorItem;
            }

            $thumb = get_the_post_thumbnail_url($id, 'large');
            if (!$thumb && !empty($colors2) && !empty($colors2[0]['per_view'])) {
                $thumb = reset($colors2[0]['per_view']) ?: '';
            }

            $variants = get_post_meta($id, FMB_Admin_Mockups_Zone::META_VARIANTS, true) ?: [];
            $sizes    = get_post_meta($id, FMB_Admin_Mockups_Zone::META_SIZES, true) ?: [];
            $desc     = get_post_meta($id, FMB_Admin_Mockups_Zone::META_DESC, true) ?: '';

            $price_mode = get_post_meta($id, FMB_Admin_Mockups_Zone::META_PRICE_MODE, true) ?: 'fixed';
            $price_config = get_post_meta($id, FMB_Admin_Mockups_Zone::META_PRICE_CONFIG, true) ?: [];

            $product_width = get_post_meta($id, FMB_Admin_Mockups_Zone::META_PRODUCT_WIDTH, true);
            $product_height = get_post_meta($id, FMB_Admin_Mockups_Zone::META_PRODUCT_HEIGHT, true);

            if (!is_array($variants)) {
                $variants = array_filter(array_map('trim', explode(',', $variants)));
            }
            if (!is_array($sizes)) {
                $sizes = array_filter(array_map('trim', explode(',', $sizes)));
            }

            $mockupData = [
                'id'      => $id,
                'title'   => get_the_title(),
                'image'   => $thumb ?: '',
                'views'   => $views,
                'variants'=> $variants,
                'sizes'   => $sizes,
                'colors'  => [],
                'colors2' => $colors2,
                'desc'    => $desc,
                'price_mode' => $price_mode,
                'price_config' => $price_config,
                'productWidth'  => floatval($product_width),
                'productHeight' => floatval($product_height),
            ];
            
            $out[] = $mockupData;
        }
        wp_reset_postdata();
        
        wp_send_json_success($out);
    }

    public function ajax_send_quote() {
        if (!check_ajax_referer('fmb_ajax', 'nonce', false)) {
            wp_send_json_error([
                'message' => 'Neplatný nebo vypršelý bezpečnostní token. Zkuste stránku obnovit (F5).'
            ]);
        }

        $name       = sanitize_text_field($_POST['name'] ?? '');
        $email      = sanitize_email($_POST['email'] ?? '');
        $phone      = sanitize_text_field($_POST['phone'] ?? '');
        $is_company = isset($_POST['is_company']) && $_POST['is_company'] === '1';
        $company    = sanitize_text_field($_POST['company'] ?? '');
        $ico        = sanitize_text_field($_POST['ico'] ?? '');
        $dic        = sanitize_text_field($_POST['dic'] ?? '');
        $address    = sanitize_text_field($_POST['address'] ?? '');
        $city       = sanitize_text_field($_POST['city'] ?? '');
        $postcode   = sanitize_text_field($_POST['postcode'] ?? '');
        $note       = sanitize_textarea_field($_POST['note'] ?? '');
        $items_raw  = wp_unslash($_POST['items'] ?? '');

        if (!$name || !is_email($email) || !$phone) {
            wp_send_json_error(['message' => 'Vyplňte prosím jméno, platný e-mail a telefon.']);
        }

        // Validace firemních údajů pokud je zaškrtnuto
        if ($is_company) {
            if (empty($company)) {
                wp_send_json_error(['message' => 'Vyplňte prosím název firmy.']);
            }
            if (empty($ico)) {
                wp_send_json_error(['message' => 'Vyplňte prosím IČO.']);
            }
        }

        $items = json_decode($items_raw, true);
        if (!is_array($items) || empty($items)) {
            wp_send_json_error(['message' => 'Objednávka neobsahuje žádné položky.']);
        }

        $upload_dir = wp_upload_dir();
        $attachments = [];
        $embedded_images = [];
        $original_files = [];

        // Zpracování obrázků z položek
        foreach ($items as $itemIdx => $item) {
            // Preview obrázky (náhledy mockupu)
            if (!empty($item['images']) && is_array($item['images'])) {
                foreach ($item['images'] as $imgIdx => $img) {
                    if (empty($img['data']) || !is_string($img['data'])) continue;
                    
                    $img_data = $img['data'];
                    if (preg_match('/^data:image\/(\w+);base64,/', $img_data, $type)) {
                        $img_data = substr($img_data, strpos($img_data, ',') + 1);
                        $type = strtolower($type[1]);
                        
                        $img_data = base64_decode($img_data);
                        
                        if ($img_data === false) continue;
                        
                        $filename = 'fmb-preview-item-' . $itemIdx . '-view-' . $imgIdx . '-' . time() . '.' . $type;
                        $filepath = $upload_dir['path'] . '/' . $filename;
                        
                        if (file_put_contents($filepath, $img_data)) {
                            $attachments[] = $filepath;
                            
                            $cid = 'preview_' . $itemIdx . '_view_' . $imgIdx . '@fmb';
                            $embedded_images[] = [
                                'cid'      => $cid,
                                'file'     => $filepath,
                                'name'     => ($img['name'] ?? "Náhled $imgIdx"),
                                'itemIdx'  => $itemIdx,
                                'product'  => $item['mockupTitle'] ?? 'Produkt',
                                'type'     => 'preview'
                            ];
                        }
                    }
                }
            }
            
            // Originální obrázky v plné kvalitě pro tisk
            if (!empty($item['originalImages']) && is_array($item['originalImages'])) {
                foreach ($item['originalImages'] as $origIdx => $origImg) {
                    if (empty($origImg['data']) || !is_string($origImg['data'])) continue;
                    
                    $orig_data = $origImg['data'];
                    if (preg_match('/^data:image\/(\w+);base64,/', $orig_data, $type)) {
                        $orig_data = substr($orig_data, strpos($orig_data, ',') + 1);
                        $type = strtolower($type[1]);
                        
                        $orig_data = base64_decode($orig_data);
                        
                        if ($orig_data === false) continue;
                        
                        $orig_name = isset($origImg['name']) ? sanitize_file_name($origImg['name']) : 'original';
                        $filename = 'fmb-ORIGINAL-item-' . $itemIdx . '-' . $orig_name . '-' . time() . '.' . $type;
                        $filepath = $upload_dir['path'] . '/' . $filename;
                        
                        if (file_put_contents($filepath, $orig_data)) {
                            $attachments[] = $filepath;
                            
                            $original_files[] = [
                                'file'     => $filepath,
                                'name'     => ($origImg['name'] ?? "Originál $origIdx"),
                                'itemIdx'  => $itemIdx,
                                'product'  => $item['mockupTitle'] ?? 'Produkt',
                                'viewLabel'=> $origImg['viewLabel'] ?? ''
                            ];
                        }
                    }
                }
            }
        }

        $to = get_option('admin_email');
        $subject = '🎨 Nová objednávka z konfigurátoru (' . count($items) . ' položek) - ' . esc_html($name);
        
        if ($is_company && $company) {
            $subject .= ' [' . esc_html($company) . ']';
        }
        
        $headers = [
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
            'Reply-To: ' . $name . ' <' . $email . '>',
            'Content-Type: text/html; charset=UTF-8'
        ];

        $customer_data = [
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'is_company' => $is_company,
            'company'    => $company,
            'ico'        => $ico,
            'dic'        => $dic,
            'address'    => $address,
            'city'       => $city,
            'postcode'   => $postcode,
            'note'       => $note,
        ];

        $body = $this->build_order_email_html($customer_data, $items, $embedded_images, $original_files);

        $sent = wp_mail($to, $subject, $body, $headers, $attachments);

        // Vyčistit dočasné soubory
        foreach ($attachments as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        if ($sent) {
            $this->send_customer_order_confirmation($customer_data, $items);
            wp_send_json_success(['message' => 'Objednávka byla úspěšně odeslána.']);
        } else {
            wp_send_json_error(['message' => 'Nepodařilo se odeslat e-mail.']);
        }
    }

    private function build_order_email_html($customer, $items, $embedded_images, $original_files) {
        $total_qty = 0;
        $total_price = 0;
        
        foreach ($items as $item) {
            $total_qty += intval($item['qty'] ?? 1);
            $price_num = intval(preg_replace('/[^0-9]/', '', $item['price'] ?? '0'));
            $total_price += $price_num;
        }

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #23b1bf, #1a8a96); color: white; padding: 30px; border-radius: 12px 12px 0 0; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .header p { margin: 10px 0 0; opacity: 0.9; }
                .content { background: #f8f9fa; padding: 30px; }
                .section { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
                .section h3 { margin: 0 0 15px; color: #23b1bf; font-size: 16px; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
                .info-row { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
                .info-row:last-child { border-bottom: none; }
                .label { font-weight: 600; color: #555; font-size: 12px; text-transform: uppercase; }
                .value { color: #000; margin-top: 2px; }
                .company-badge { display: inline-block; background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-bottom: 10px; }
                .item-card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 15px; border-left: 4px solid #23b1bf; }
                .item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
                .item-title { font-weight: 700; font-size: 18px; color: #1e293b; }
                .item-price { font-weight: 700; font-size: 18px; color: #23b1bf; }
                .item-details { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 14px; }
                .item-detail { background: #f8f9fa; padding: 8px 12px; border-radius: 4px; }
                .item-detail strong { display: block; font-size: 11px; color: #666; text-transform: uppercase; }
                .item-images { margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap; }
                .item-images img { max-width: 150px; border-radius: 6px; border: 1px solid #e9ecef; }
                .originals-section { margin-top: 15px; padding-top: 15px; border-top: 2px dashed #e9ecef; }
                .originals-title { font-size: 12px; font-weight: 700; color: #059669; text-transform: uppercase; margin-bottom: 8px; }
                .originals-list { font-size: 13px; color: #666; }
                .originals-list li { margin-bottom: 4px; }
                .summary-box { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); padding: 20px; border-radius: 8px; text-align: center; }
                .summary-total { font-size: 28px; font-weight: 800; color: #2e7d32; }
                .footer { background: #e9ecef; padding: 20px; border-radius: 0 0 12px 12px; text-align: center; font-size: 12px; color: #666; }
                .attachment-note { background: #dbeafe; border: 1px solid #93c5fd; border-radius: 8px; padding: 15px; margin-top: 20px; }
                .attachment-note h4 { margin: 0 0 8px; color: #1e40af; font-size: 14px; }
                .attachment-note p { margin: 0; font-size: 13px; color: #1e3a8a; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎨 Nová objednávka z konfigurátoru</h1>
                    <p><?php echo count($items); ?> položek • <?php echo $total_qty; ?> kusů celkem</p>
                </div>
                
                <div class="content">
                    <!-- Zákazník -->
                    <div class="section">
                        <h3>👤 Kontaktní údaje zákazníka</h3>
                        <?php if ($customer['is_company']): ?>
                        <div class="company-badge">🏢 Firemní objednávka</div>
                        <?php endif; ?>
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="label">Jméno</div>
                                <div class="value"><?php echo esc_html($customer['name']); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="label">E-mail</div>
                                <div class="value"><a href="mailto:<?php echo esc_attr($customer['email']); ?>"><?php echo esc_html($customer['email']); ?></a></div>
                            </div>
                            <div class="info-row">
                                <div class="label">Telefon</div>
                                <div class="value"><?php echo esc_html($customer['phone']); ?></div>
                            </div>
                            <?php if ($customer['is_company'] && $customer['company']): ?>
                            <div class="info-row">
                                <div class="label">Firma</div>
                                <div class="value"><strong><?php echo esc_html($customer['company']); ?></strong></div>
                            </div>
                            <div class="info-row">
                                <div class="label">IČO</div>
                                <div class="value"><?php echo esc_html($customer['ico']); ?></div>
                            </div>
                            <?php if ($customer['dic']): ?>
                            <div class="info-row">
                                <div class="label">DIČ</div>
                                <div class="value"><?php echo esc_html($customer['dic']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($customer['address'] || $customer['city']): ?>
                        <div class="info-row" style="margin-top: 10px;">
                            <div class="label">Adresa</div>
                            <div class="value">
                                <?php echo esc_html($customer['address']); ?><br>
                                <?php echo esc_html($customer['postcode']); ?> <?php echo esc_html($customer['city']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($customer['note']): ?>
                        <div class="info-row" style="margin-top: 10px;">
                            <div class="label">Poznámka</div>
                            <div class="value"><?php echo nl2br(esc_html($customer['note'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Položky -->
                    <div class="section">
                        <h3>🛒 Položky objednávky</h3>
                        
                        <?php foreach ($items as $idx => $item): ?>
                        <div class="item-card">
                            <div class="item-header">
                                <div class="item-title"><?php echo esc_html($item['mockupTitle'] ?? 'Produkt'); ?></div>
                                <div class="item-price"><?php echo esc_html($item['price'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="item-details">
                                <div class="item-detail">
                                    <strong>Varianta</strong>
                                    <?php echo esc_html($item['variant'] ?? 'N/A'); ?>
                                </div>
                                <div class="item-detail">
                                    <strong>Velikost</strong>
                                    <?php echo esc_html($item['size'] ?? 'N/A'); ?>
                                </div>
                                <div class="item-detail">
                                    <strong>Barva</strong>
                                    <?php echo esc_html($item['colorName'] ?? 'N/A'); ?>
                                    <?php if (!empty($item['colorHex'])): ?>
                                    <span style="display:inline-block;width:14px;height:14px;background:<?php echo esc_attr($item['colorHex']); ?>;border:1px solid #ddd;border-radius:3px;vertical-align:middle;margin-left:4px;"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="item-detail">
                                    <strong>Provedení</strong>
                                    <?php echo esc_html($item['designType'] ?? 'Potisk'); ?>
                                </div>
                                <div class="item-detail">
                                    <strong>Počet kusů</strong>
                                    <?php echo esc_html($item['qty'] ?? 1); ?> ks
                                </div>
                            </div>
                            
                            <?php 
                            $item_images = array_filter($embedded_images, function($img) use ($idx) {
                                return $img['itemIdx'] === $idx && $img['type'] === 'preview';
                            });
                            if (!empty($item_images)): 
                            ?>
                            <div class="item-images">
                                <?php foreach ($item_images as $img): ?>
                                <div>
                                    <img src="cid:<?php echo esc_attr($img['cid']); ?>" alt="<?php echo esc_attr($img['name']); ?>">
                                    <div style="font-size:11px;color:#666;text-align:center;margin-top:4px;"><?php echo esc_html($img['name']); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php 
                            $item_originals = array_filter($original_files, function($orig) use ($idx) {
                                return $orig['itemIdx'] === $idx;
                            });
                            if (!empty($item_originals)): 
                            ?>
                            <div class="originals-section">
                                <div class="originals-title">📎 Originální soubory pro tisk (v příloze):</div>
                                <ul class="originals-list">
                                    <?php foreach ($item_originals as $orig): ?>
                                    <li>
                                        <strong><?php echo esc_html($orig['name']); ?></strong>
                                        <?php if ($orig['viewLabel']): ?>
                                        (<?php echo esc_html($orig['viewLabel']); ?>)
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($original_files)): ?>
                    <div class="attachment-note">
                        <h4>📎 Přílohy obsahují originální soubory v plné kvalitě</h4>
                        <p>V příloze tohoto e-mailu najdete <?php echo count($original_files); ?> originálních souborů připravených pro tisk. Soubory začínající "fmb-ORIGINAL-" jsou v plném rozlišení.</p>
                    </div>
                    <?php endif; ?>

                    <!-- Souhrn -->
                    <div class="summary-box">
                        <div style="font-size:14px;color:#666;margin-bottom:5px;">CELKOVÁ ORIENTAČNÍ CENA</div>
                        <div class="summary-total"><?php echo number_format($total_price, 0, ',', ' '); ?> Kč</div>
                        <div style="font-size:12px;color:#666;margin-top:5px;">(<?php echo $total_qty; ?> kusů)</div>
                    </div>
                </div>
                
                <div class="footer">
                    <p style="margin:0;">Tato objednávka byla odeslána z konfigurátoru na webu <?php echo esc_html(get_bloginfo('name')); ?></p>
                    <p style="margin:5px 0 0;">⏰ <?php echo date_i18n('j. n. Y v H:i'); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function send_customer_order_confirmation($customer, $items) {
        $subject = '✅ Potvrzení objednávky - ' . get_bloginfo('name');
        
        $headers = [
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        $total_qty = 0;
        $total_price = 0;
        foreach ($items as $item) {
            $total_qty += intval($item['qty'] ?? 1);
            $price_num = intval(preg_replace('/[^0-9]/', '', $item['price'] ?? '0'));
            $total_price += $price_num;
        }
        
        $items_html = '';
        foreach ($items as $item) {
            $items_html .= '<tr>
                <td style="padding:12px;border-bottom:1px solid #e9ecef;">
                    <strong>' . esc_html($item['mockupTitle'] ?? 'Produkt') . '</strong><br>
                    <span style="font-size:12px;color:#666;">' . esc_html($item['variant'] ?? '') . ' • ' . esc_html($item['size'] ?? '') . ' • ' . esc_html($item['colorName'] ?? '') . '</span>
                </td>
                <td style="padding:12px;border-bottom:1px solid #e9ecef;text-align:center;">' . esc_html($item['qty'] ?? 1) . ' ks</td>
                <td style="padding:12px;border-bottom:1px solid #e9ecef;text-align:right;font-weight:600;">' . esc_html($item['price'] ?? 'N/A') . '</td>
            </tr>';
        }
        
        $company_info = '';
        if ($customer['is_company'] && $customer['company']) {
            $company_info = '
            <div style="background:#fef3c7;border-radius:8px;padding:15px;margin-bottom:20px;">
                <strong>🏢 Fakturační údaje firmy:</strong><br>
                ' . esc_html($customer['company']) . '<br>
                IČO: ' . esc_html($customer['ico']) . ($customer['dic'] ? '<br>DIČ: ' . esc_html($customer['dic']) : '') . '
            </div>';
        }
        
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
        </head>
        <body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;margin:0;padding:0;background:#f5f5f5;">
            <div style="max-width:600px;margin:0 auto;padding:20px;">
                <div style="background:linear-gradient(135deg,#23b1bf,#1a8a96);color:white;padding:30px;border-radius:12px 12px 0 0;text-align:center;">
                    <h1 style="margin:0;font-size:24px;">✅ Děkujeme za Vaši objednávku!</h1>
                </div>
                
                <div style="background:#fff;padding:30px;border-radius:0 0 12px 12px;">
                    <p>Dobrý den, <strong>' . esc_html($customer['name']) . '</strong>,</p>
                    <p>děkujeme za Vaši objednávku z našeho konfigurátoru. Brzy se Vám ozveme s cenovou nabídkou a dalšími detaily.</p>
                    
                    ' . $company_info . '
                    
                    <h3 style="color:#23b1bf;border-bottom:2px solid #e9ecef;padding-bottom:10px;">📦 Shrnutí objednávky</h3>
                    
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th style="padding:12px;text-align:left;font-size:12px;text-transform:uppercase;color:#666;">Položka</th>
                                <th style="padding:12px;text-align:center;font-size:12px;text-transform:uppercase;color:#666;">Počet</th>
                                <th style="padding:12px;text-align:right;font-size:12px;text-transform:uppercase;color:#666;">Cena</th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . $items_html . '
                        </tbody>
                        <tfoot>
                            <tr style="background:#e8f5e9;">
                                <td colspan="2" style="padding:15px;font-weight:700;">Celkem (' . $total_qty . ' kusů)</td>
                                <td style="padding:15px;text-align:right;font-weight:800;font-size:18px;color:#2e7d32;">' . number_format($total_price, 0, ',', ' ') . ' Kč</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div style="background:#f8f9fa;padding:15px;border-radius:8px;margin-top:20px;font-size:13px;color:#666;">
                        <strong>Co bude následovat?</strong><br>
                        Náš tým zkontroluje Vaši objednávku a do 24 hodin Vás budeme kontaktovat s finální cenovou nabídkou a dalšími informacemi o realizaci.
                    </div>
                    
                    <p style="margin-top:25px;">Pokud máte jakékoliv dotazy, neváhejte nás kontaktovat.</p>
                    
                    <p style="color:#666;font-size:13px;margin-top:30px;padding-top:20px;border-top:1px solid #e9ecef;">
                        S pozdravem,<br>
                        <strong>' . esc_html(get_bloginfo('name')) . '</strong>
                    </p>
                </div>
            </div>
        </body>
        </html>';
        
        wp_mail($customer['email'], $subject, $body, $headers);
    }
}

new FMB_SB_Plugin();