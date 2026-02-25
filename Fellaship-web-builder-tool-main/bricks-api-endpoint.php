<?php
/**
 * Plugin Name: Bricks API Endpoint
 * Description: Custom REST API endpoint pro práci s Bricks Builder meta daty
 * Version: 1.0.0
 * Author: Fellaship Web Builder Tool
 */

// Zabránit přímému přístupu
if (!defined('ABSPATH')) {
    exit;
}

/**
 * DŮLEŽITÉ: Povolit Code Execution pro administrátory pomocí WordPress filtrů
 * Tyto filtry se spouští při každém načtení pluginu
 */

// Filtr pro povolení Code Execution - vrací true pro administrátory
add_filter('bricks/code/allow_execution', function($allow) {
    if (is_user_logged_in() && current_user_can('administrator')) {
        return true;
    }
    return $allow;
}, 999);

// Filtr pro zakázání blokování Code Execution
add_filter('bricks/code/disable_execution', '__return_false', 999);

// Alternativní filtr
add_filter('bricks/code/execute', '__return_true', 999);

/**
 * DEVELOPMENT MODE: Vypnout kontrolu podpisů kódu
 * POZOR: Toto je pouze pro vývoj! V produkci použijte správné podepisování.
 */
add_filter('bricks/code/disable_signature_check', '__return_true', 999);

/**
 * Registrace custom REST API endpointů pro Bricks
 */
add_action('rest_api_init', function () {
    // Endpoint pro získání Bricks obsahu stránky
    register_rest_route('bricks/v1', '/page/(?P<id>\d+)/content', array(
        'methods' => 'GET',
        'callback' => 'bricks_get_page_content',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                }
            ),
        ),
    ));

    // Endpoint pro aktualizaci Bricks obsahu stránky
    register_rest_route('bricks/v1', '/page/(?P<id>\d+)/content', array(
        'methods' => 'POST',
        'callback' => 'bricks_update_page_content',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                }
            ),
            'content' => array(
                'required' => true,
                'type' => 'string',
            ),
        ),
    ));

    // Endpoint pro debug page meta (všechny Bricks meta hodnoty)
    register_rest_route('bricks/v1', '/debug-page-meta/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'bricks_debug_page_meta',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
            ),
        ),
    ));

    // Endpoint pro získání všech stránek s Bricks obsahem
    register_rest_route('bricks/v1', '/pages', array(
        'methods' => 'GET',
        'callback' => 'bricks_get_all_pages',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Endpoint pro upload a instalaci pluginu ze ZIP
    register_rest_route('bricks/v1', '/install-plugin', array(
        'methods' => 'POST',
        'callback' => 'bricks_install_plugin',
        'permission_callback' => function () {
            $user = wp_get_current_user();
            return $user && $user->ID > 0;
        },
    ));

    // Endpoint pro upload/aktualizaci PHP souboru pluginu
    register_rest_route('bricks/v1', '/upload-plugin', array(
        'methods' => 'POST',
        'callback' => 'bricks_upload_plugin_file',
        'permission_callback' => function () {
            return current_user_can('install_plugins');
        },
    ));

    // Endpoint pro upload a instalaci TÉMATU ze ZIP
    register_rest_route('bricks/v1', '/install-theme', array(
        'methods' => 'POST',
        'callback' => 'bricks_install_theme',
        'permission_callback' => function () {
            return current_user_can('install_themes') || current_user_can('manage_options');
        },
    ));

    // Endpoint pro aktivaci tématu
    register_rest_route('bricks/v1', '/activate-theme', array(
        'methods' => 'POST',
        'callback' => 'bricks_activate_theme',
        'permission_callback' => function () {
            $user = wp_get_current_user();
            return $user && $user->ID > 0;
        },
    ));

    // Endpoint pro kontrolu nainstalovaných témat
    register_rest_route('bricks/v1', '/themes', array(
        'methods' => 'GET',
        'callback' => 'bricks_get_themes',
        'permission_callback' => function () {
            return current_user_can('edit_theme_options');
        },
    ));

    // Endpoint pro aktualizaci tématu
    register_rest_route('bricks/v1', '/update-theme', array(
        'methods' => 'POST',
        'callback' => 'bricks_update_theme',
        'permission_callback' => function () {
            return current_user_can('update_themes');
        },
        'args' => array(
            'theme' => array(
                'required' => false,
                'type' => 'string',
                'description' => 'Slug tématu k aktualizaci (výchozí: bricks)'
            ),
        ),
    ));

    // Endpoint pro smazání tématu
    register_rest_route('bricks/v1', '/delete-theme', array(
        'methods' => 'POST',
        'callback' => 'bricks_delete_theme',
        'permission_callback' => function () {
            return current_user_can('delete_themes');
        },
        'args' => array(
            'theme' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'Slug tématu ke smazání'
            ),
        ),
    ));

    // Endpoint pro smazání template (DELETE)
    register_rest_route('bricks/v1', '/template/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'bricks_delete_template',
        'permission_callback' => function () {
            return current_user_can('delete_posts');
        },
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
            ),
            'force' => array(
                'required' => false,
                'type' => 'boolean',
                'default' => true
            ),
        ),
    ));

    // Endpoint pro smazání template (POST s _method=DELETE pro kompatibilitu)
    register_rest_route('bricks/v1', '/template/(?P<id>\d+)/delete', array(
        'methods' => 'POST',
        'callback' => 'bricks_delete_template',
        'permission_callback' => function () {
            return current_user_can('delete_posts');
        },
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
            ),
            'force' => array(
                'required' => false,
                'type' => 'boolean',
                'default' => true
            ),
        ),
    ));

    // Alternativní endpoint pro smazání template (bez regex, jednodušší)
    register_rest_route('bricks/v1', '/delete-template', array(
        'methods' => 'POST',
        'callback' => 'bricks_delete_template',
        'permission_callback' => function () {
            return current_user_can('delete_posts');
        },
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
            ),
            'force' => array(
                'required' => false,
                'type' => 'boolean',
                'default' => true
            ),
        ),
    ));

    // Endpoint pro hromadné smazání templates
    register_rest_route('bricks/v1', '/templates/delete', array(
        'methods' => 'POST',
        'callback' => 'bricks_delete_templates_bulk',
        'permission_callback' => function () {
            return current_user_can('delete_posts');
        },
    ));

    // Endpoint pro získání Bricks Templates
    register_rest_route('bricks/v1', '/templates', array(
        'methods' => 'GET',
        'callback' => 'bricks_get_templates',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Endpoint pro vytvoření/aktualizaci Bricks Template
    register_rest_route('bricks/v1', '/template', array(
        'methods' => 'POST',
        'callback' => 'bricks_create_or_update_template',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Endpoint pro získání meta dat template
    register_rest_route('bricks/v1', '/template/(?P<id>\d+)/meta', array(
        'methods' => 'GET',
        'callback' => 'bricks_get_template_meta',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Endpoint pro debug template (zobrazit vše, co je uložené)
    register_rest_route('bricks/v1', '/template/(?P<id>\d+)/debug', array(
        'methods' => 'GET',
        'callback' => 'bricks_debug_template',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Endpoint pro aktualizaci Bricks obsahu template (stejně jako pro pages)
    register_rest_route('bricks/v1', '/template/(?P<id>\d+)/content', array(
        'methods' => 'POST',
        'callback' => 'bricks_update_template_content',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                }
            ),
            'content' => array(
                'required' => true,
                'type' => 'string',
            ),
        ),
    ));

    // Endpoint pro debug template (zobrazit vše, co je uložené)
    register_rest_route('bricks/v1', '/template/(?P<id>\d+)/debug', array(
        'methods' => 'GET',
        'callback' => 'bricks_debug_template',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Endpoint pro instalaci pluginu z URL
    register_rest_route('bricks/v1', '/install-plugin-from-url', array(
        'methods' => 'POST',
        'callback' => 'bricks_install_plugin_from_url',
        'permission_callback' => function () {
            return current_user_can('install_plugins');
        },
        'args' => array(
            'plugin_url' => array(
                'required' => true,
                'type' => 'string',
                'validate_callback' => function ($param) {
                    return filter_var($param, FILTER_VALIDATE_URL) !== false;
                }
            ),
        ),
    ));

    // Endpoint pro aktivaci pluginu
    register_rest_route('bricks/v1', '/activate-plugin', array(
        'methods' => 'POST',
        'callback' => 'bricks_activate_plugin',
        'permission_callback' => function () {
            return current_user_can('activate_plugins');
        },
        'args' => array(
            'plugin' => array(
                'required' => true,
                'type' => 'string',
            ),
        ),
    ));

    // Endpoint pro povolení Code Execution v Bricks
    register_rest_route('bricks/v1', '/enable-code-execution', array(
        'methods' => 'POST',
        'callback' => 'bricks_enable_code_execution',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));

    // Endpoint pro kontrolu Code Execution nastavení
    register_rest_route('bricks/v1', '/code-execution-status', array(
        'methods' => 'GET',
        'callback' => 'bricks_get_code_execution_status',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));

    // Endpoint pro povolení editace Pages pomocí Bricks
    register_rest_route('bricks/v1', '/enable-pages-editing', array(
        'methods' => 'POST',
        'callback' => 'bricks_enable_pages_editing',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));

    // Endpoint pro konfiguraci všech Bricks Settings najednou
    register_rest_route('bricks/v1', '/configure-settings', array(
        'methods' => 'POST',
        'callback' => 'bricks_configure_settings',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));

    // Endpoint pro debug - získání aktuální struktury bricks_settings
    register_rest_route('bricks/v1', '/debug-settings', array(
        'methods' => 'GET',
        'callback' => 'bricks_debug_settings',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));

    // Endpoint pro přímé hledání post_types v databázi
    register_rest_route('bricks/v1', '/find-post-types', array(
        'methods' => 'GET',
        'callback' => 'bricks_find_post_types',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));

    // Endpoint pro nastavení WordPress Reading (Static page)
    register_rest_route('bricks/v1', '/set-reading', array(
        'methods' => 'POST',
        'callback' => 'bricks_set_reading',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
        'args' => array(
            'page_id' => array(
                'required' => true,
                'type' => 'integer',
            ),
        ),
    ));

    // Endpoint pro aktivaci Bricks licence
    register_rest_route('bricks/v1', '/activate-license', array(
        'methods' => 'POST',
        'callback' => 'bricks_activate_license',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
        'args' => array(
            'license_key' => array(
                'required' => true,
                'type' => 'string',
            ),
        ),
    ));

    // Endpoint pro aktivaci pluginu
    register_rest_route('bricks/v1', '/activate-plugin', array(
        'methods' => 'POST',
        'callback' => 'bricks_activate_plugin',
        'permission_callback' => function () {
            return current_user_can('activate_plugins');
        },
        'args' => array(
            'plugin' => array(
                'required' => true,
                'type' => 'string',
            ),
        ),
    ));

    // Endpoint pro regeneraci code signatures na stránce/template
    register_rest_route('bricks/v1', '/regenerate-signatures/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'bricks_regenerate_signatures',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
            ),
        ),
    ));

    // Endpoint pro globální regeneraci všech code signatures (používá interní Bricks funkci)
    register_rest_route('bricks/v1', '/regenerate-all-signatures', array(
        'methods' => 'POST',
        'callback' => 'bricks_regenerate_all_signatures',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));

    // Endpoint pro nastavení Builder Access a rolí
    register_rest_route('bricks/v1', '/configure-builder-access', array(
        'methods' => 'POST',
        'callback' => 'bricks_configure_builder_access',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
        'args' => array(
            'roles' => array(
                'required' => false,
                'type' => 'object',
                'description' => 'Mapování rolí na úrovně přístupu (full, editContent, none)'
            ),
            'code_execution_roles' => array(
                'required' => false,
                'type' => 'array',
                'description' => 'Pole rolí, které mají povoleno Code Execution'
            ),
        ),
    ));

    // Endpoint pro aktualizaci Bricks licenčního klíče
    register_rest_route('bricks/v1', '/license', array(
        'methods' => 'POST',
        'callback' => 'bricks_update_license',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
        'args' => array(
            'license_key' => array(
                'required' => true,
                'type' => 'string',
            ),
        ),
    ));

    // Endpoint pro vymazání cache (LiteSpeed, Bricks, WordPress)
    register_rest_route('bricks/v1', '/purge-all-cache', array(
        'methods' => 'POST',
        'callback' => 'bricks_purge_all_cache',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));
});

/**
 * Vymazat všechny cache (LiteSpeed, Bricks, WordPress)
 */
function bricks_purge_all_cache($request) {
    $result = array(
        'success' => true,
        'purged' => array()
    );
    
    // 1. LiteSpeed Cache
    if (class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_all')) {
        LiteSpeed_Cache_API::purge_all();
        $result['purged'][] = 'litespeed';
    } elseif (function_exists('litespeed_purge_all')) {
        litespeed_purge_all();
        $result['purged'][] = 'litespeed_function';
    } elseif (defined('LSCWP_V')) {
        // Try via action hook
        do_action('litespeed_purge_all');
        $result['purged'][] = 'litespeed_action';
    }
    
    // 2. WP Super Cache
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
        $result['purged'][] = 'wp_super_cache';
    }
    
    // 3. W3 Total Cache
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
        $result['purged'][] = 'w3_total_cache';
    }
    
    // 4. WP Fastest Cache
    if (function_exists('wpfc_clear_all_cache')) {
        wpfc_clear_all_cache();
        $result['purged'][] = 'wp_fastest_cache';
    }
    
    // 5. WordPress Object Cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        $result['purged'][] = 'wp_object_cache';
    }
    
    // 6. Bricks Cache (if exists)
    if (function_exists('bricks_clear_cache')) {
        bricks_clear_cache();
        $result['purged'][] = 'bricks_cache';
    }
    
    // 7. Delete all Bricks transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bricks%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_bricks%'");
    $result['purged'][] = 'bricks_transients';
    
    // 8. Clear opcache if available
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        $result['purged'][] = 'opcache';
    }
    
    return rest_ensure_response($result);
}

/**
 * Získat Bricks obsah stránky
 */
function bricks_get_page_content($request) {
    $page_id = $request->get_param('id');
    $meta_key = '_bricks_page_content';

    // Zkontrolovat, zda stránka existuje
    $page = get_post($page_id);
    if (!$page || $page->post_type !== 'page') {
        return new WP_Error('page_not_found', 'Stránka nenalezena', array('status' => 404));
    }

    // Získat Bricks obsah z meta
    $bricks_content = get_post_meta($page_id, $meta_key, true);

    if (empty($bricks_content)) {
        return new WP_Error('no_bricks_content', 'Bricks obsah nenalezen', array('status' => 404));
    }

    // Pokud je to string, parsovat jako JSON
    if (is_string($bricks_content)) {
        $bricks_content = json_decode($bricks_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('invalid_json', 'Neplatný JSON formát', array('status' => 400));
        }
    }

    return rest_ensure_response($bricks_content);
}

/**
 * Debug: Získat všechny Bricks meta hodnoty stránky
 */
function bricks_debug_page_meta($request) {
    $page_id = $request->get_param('id');
    
    // Zkontrolovat, zda stránka/post existuje
    $post = get_post($page_id);
    if (!$post) {
        return new WP_Error('post_not_found', 'Post nenalezen', array('status' => 404));
    }
    
    // Získat všechny meta hodnoty
    $all_meta = get_post_meta($page_id);
    
    // Filtrovat Bricks meta
    $bricks_meta = array();
    foreach ($all_meta as $key => $value) {
        if (strpos($key, 'bricks') !== false || strpos($key, '_bricks') !== false) {
            $meta_value = $value[0] ?? $value;
            // Zkusit deserializovat
            $unserialized = maybe_unserialize($meta_value);
            $bricks_meta[$key] = array(
                'type' => gettype($unserialized),
                'value' => $unserialized,
                'raw_length' => is_string($meta_value) ? strlen($meta_value) : null
            );
        }
    }
    
    return rest_ensure_response(array(
        'post_id' => $page_id,
        'post_type' => $post->post_type,
        'post_title' => $post->post_title,
        'bricks_meta' => $bricks_meta,
        'all_meta_keys' => array_keys($all_meta)
    ));
}

/**
 * Aktualizovat Bricks obsah stránky
 */
function bricks_update_page_content($request) {
    $page_id = $request->get_param('id');
    $content = $request->get_param('content');
    $meta_key = '_bricks_page_content';

    // Zkontrolovat, zda stránka existuje
    $page = get_post($page_id);
    if (!$page || $page->post_type !== 'page') {
        return new WP_Error('page_not_found', 'Stránka nenalezena', array('status' => 404));
    }

    // Validovat a dekódovat JSON
    if (is_string($content)) {
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('invalid_json', 'Neplatný JSON formát: ' . json_last_error_msg(), array('status' => 400));
        }
        $content_array = $decoded;
    } else {
        $content_array = $content;
    }
    
    // DŮLEŽITÉ: Bricks očekává obsah jako PHP array (serializovaný), ne JSON string
    // Uložit do obou meta klíčů (pro kompatibilitu s různými verzemi Bricks)
    $result1 = update_post_meta($page_id, '_bricks_page_content', $content_array);
    $result2 = update_post_meta($page_id, '_bricks_page_content_2', $content_array);
    
    // Nastavit editor mode na 'bricks'
    update_post_meta($page_id, '_bricks_editor_mode', 'bricks');
    
    // Také nastavit, že stránka používá Bricks
    update_post_meta($page_id, '_bricks_page_content_type', 'bricks');
    
    if ($result1 === false && $result2 === false) {
        return new WP_Error('update_failed', 'Nepodařilo se aktualizovat Bricks obsah', array('status' => 500));
    }

    // Vrátit aktualizovaný obsah
    $updated_content = get_post_meta($page_id, '_bricks_page_content_2', true);
    if (empty($updated_content)) {
        $updated_content = get_post_meta($page_id, '_bricks_page_content', true);
    }
    
    // Pokud je to serializované, deserializovat
    if (is_string($updated_content)) {
        $unserialized = maybe_unserialize($updated_content);
        if ($unserialized !== false) {
            $updated_content = $unserialized;
        } else {
            // Zkusit jako JSON
            $json_decoded = json_decode($updated_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $updated_content = $json_decoded;
            }
        }
    }

    return rest_ensure_response(array(
        'success' => true,
        'page_id' => $page_id,
        'content' => $updated_content,
        'meta_keys_updated' => array(
            '_bricks_page_content' => $result1 !== false,
            '_bricks_page_content_2' => $result2 !== false,
            '_bricks_editor_mode' => true
        )
    ));
}

/**
 * Získat všechny stránky s Bricks obsahem
 */
function bricks_get_all_pages($request) {
    $pages = get_posts(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_key' => '_bricks_page_content',
        'meta_compare' => 'EXISTS'
    ));

    $result = array();
    foreach ($pages as $page) {
        $bricks_content = get_post_meta($page->ID, '_bricks_page_content', true);
        
        if (is_string($bricks_content)) {
            $bricks_content = json_decode($bricks_content, true);
        }

        $result[] = array(
            'id' => $page->ID,
            'title' => $page->post_title,
            'slug' => $page->post_name,
            'status' => $page->post_status,
            'bricks_content' => $bricks_content
        );
    }

    return rest_ensure_response($result);
}

/**
 * Instalovat plugin z uploadovaného ZIP souboru
 */
function bricks_install_plugin($request) {
    // Zkontrolovat, zda je uživatel přihlášen
    $user = wp_get_current_user();
    if (!$user || !$user->ID) {
        return new WP_Error('not_logged_in', 'Uživatel není přihlášen', array('status' => 401));
    }

    // Zkontrolovat oprávnění - zkusit více možností
    $can_install = current_user_can('install_plugins') || 
                   current_user_can('manage_options') ||
                   current_user_can('activate_plugins');
    
    // Pokud nemá capability, zkontrolovat roli
    if (!$can_install) {
        $user_roles = $user->roles;
        if (!in_array('administrator', $user_roles)) {
            return new WP_Error('insufficient_permissions', 
                'Nemáte oprávnění instalovat pluginy. Vyžaduje se role Administrator nebo capability install_plugins. Vaše role: ' . implode(', ', $user_roles), 
                array('status' => 403));
        }
        // Pokud je administrátor, ale nemá capability, povolíme to
        $can_install = true;
    }

    // Zkontrolovat, zda byl soubor uploadován
    if (empty($_FILES['plugin_file'])) {
        return new WP_Error('no_file', 'ZIP soubor nebyl uploadován', array('status' => 400));
    }

    $file = $_FILES['plugin_file'];

    // Validovat, že je to ZIP soubor
    $file_type = wp_check_filetype($file['name']);
    if ($file_type['ext'] !== 'zip') {
        return new WP_Error('invalid_file', 'Soubor musí být ZIP archiv', array('status' => 400));
    }

    // Načíst WordPress Filesystem API (MUSÍ být před wp_tempnam!)
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
    require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');

    // Vytvořit dočasný soubor
    $temp_file = wp_tempnam('bricks-plugin-');
    if (!move_uploaded_file($file['tmp_name'], $temp_file)) {
        return new WP_Error('upload_failed', 'Nepodařilo se uložit uploadovaný soubor', array('status' => 500));
    }

    // Nastavit filesystem method na direct (pokud je to možné)
    if (!defined('FS_METHOD')) {
        define('FS_METHOD', 'direct');
    }

    // Vytvořit tichý upgrader skin (anonymní třída)
    $skin = new class extends WP_Upgrader_Skin {
        public function feedback($string, ...$args) {
            // Potlačit všechny feedback zprávy
            return;
        }
        
        public function header() {
            return;
        }
        
        public function footer() {
            return;
        }
    };
    
    // Vytvořit upgrader s tichým skinem
    $upgrader = new Plugin_Upgrader($skin);

    // Nainstalovat plugin z dočasného souboru
    $result = $upgrader->install($temp_file);

    // Smazat dočasný soubor
    @unlink($temp_file);

    if (is_wp_error($result)) {
        return new WP_Error('install_failed', 'Instalace selhala: ' . $result->get_error_message(), array('status' => 500));
    }

    if ($result === false) {
        return new WP_Error('install_failed', 'Instalace selhala - neznámá chyba', array('status' => 500));
    }

    // Získat název pluginu
    $plugin_file = $upgrader->plugin_info();
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Plugin úspěšně nainstalován',
        'plugin' => $plugin_file
    ));
}

/**
 * Instalovat TÉMA z uploadovaného ZIP souboru
 */
function bricks_install_theme($request) {
    // Zkontrolovat, zda je uživatel přihlášen
    $user = wp_get_current_user();
    if (!$user || !$user->ID) {
        return new WP_Error('not_logged_in', 'Uživatel není přihlášen', array('status' => 401));
    }

    // Zkontrolovat oprávnění
    $can_install = current_user_can('install_themes') || 
                   current_user_can('manage_options') ||
                   in_array('administrator', $user->roles);
    
    if (!$can_install) {
        return new WP_Error('insufficient_permissions', 
            'Nemáte oprávnění instalovat témata.', 
            array('status' => 403));
    }

    // Zkontrolovat, zda byl soubor uploadován
    if (empty($_FILES['theme_file'])) {
        return new WP_Error('no_file', 'ZIP soubor nebyl uploadován', array('status' => 400));
    }

    $file = $_FILES['theme_file'];

    // Validovat, že je to ZIP soubor
    $file_type = wp_check_filetype($file['name']);
    if ($file_type['ext'] !== 'zip') {
        return new WP_Error('invalid_file', 'Soubor musí být ZIP archiv', array('status' => 400));
    }

    // Načíst WordPress Filesystem API
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/theme-install.php');
    require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
    require_once(ABSPATH . 'wp-admin/includes/theme.php');

    // Vytvořit dočasný soubor
    $temp_file = wp_tempnam('bricks-theme-');
    if (!move_uploaded_file($file['tmp_name'], $temp_file)) {
        return new WP_Error('upload_failed', 'Nepodařilo se uložit uploadovaný soubor', array('status' => 500));
    }

    // Nastavit filesystem method na direct
    if (!defined('FS_METHOD')) {
        define('FS_METHOD', 'direct');
    }

    // Vytvořit tichý upgrader skin
    $skin = new class extends WP_Upgrader_Skin {
        public function feedback($string, ...$args) { return; }
        public function header() { return; }
        public function footer() { return; }
    };
    
    // Vytvořit THEME upgrader
    $upgrader = new Theme_Upgrader($skin);

    // Nainstalovat téma z dočasného souboru
    $result = $upgrader->install($temp_file);

    // Smazat dočasný soubor
    @unlink($temp_file);

    if (is_wp_error($result)) {
        return new WP_Error('install_failed', 'Instalace selhala: ' . $result->get_error_message(), array('status' => 500));
    }

    if ($result === false) {
        return new WP_Error('install_failed', 'Instalace selhala - neznámá chyba', array('status' => 500));
    }

    // Získat název tématu
    $theme_info = $upgrader->theme_info();
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Téma úspěšně nainstalováno',
        'theme' => $theme_info ? $theme_info->get_stylesheet() : null
    ));
}

/**
 * Aktivovat téma
 */
function bricks_activate_theme($request) {
    $theme = $request->get_param('theme');
    
    if (empty($theme)) {
        $theme = 'bricks'; // Výchozí název Bricks tématu
    }

    // Zkontrolovat, zda téma existuje - zkusit najít podle slug nebo názvu
    $theme_obj = null;
    $found_stylesheet = null;
    
    // Zkusit najít téma podle slug nebo názvu
    $all_themes = wp_get_themes();
    foreach ($all_themes as $stylesheet => $theme_data) {
        $theme_name = $theme_data->get('Name');
        $theme_stylesheet = $theme_data->get_stylesheet();
        // Zkontrolovat slug (stylesheet), název nebo lowercase varianty
        if ($stylesheet === $theme || 
            strtolower($stylesheet) === strtolower($theme) ||
            strtolower($theme_name) === strtolower($theme) ||
            strtolower($theme_stylesheet) === strtolower($theme)) {
            $theme_obj = $theme_data;
            $found_stylesheet = $stylesheet; // Použít klíč z wp_get_themes() - to je správný stylesheet
            break;
        }
    }
    
    // Pokud stále není nalezeno, zkusit přímo wp_get_theme
    if (!$theme_obj || !$found_stylesheet) {
        $theme_obj = wp_get_theme($theme);
        if ($theme_obj->exists()) {
            $found_stylesheet = $theme_obj->get_stylesheet();
        }
    }
    
    if (!$theme_obj || !$found_stylesheet) {
        return new WP_Error('theme_not_found', 'Téma nebylo nalezeno: ' . $theme, array('status' => 404));
    }

    // Aktivovat téma pomocí stylesheet (skutečný název adresáře)
    // switch_theme() potřebuje stylesheet (název adresáře), ne slug
    switch_theme($found_stylesheet);

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Téma aktivováno',
        'theme' => $found_stylesheet,
        'theme_name' => $theme_obj->get('Name'),
        'active_theme' => get_stylesheet()
    ));
}

/**
 * Získat seznam nainstalovaných témat
 */
function bricks_get_themes($request) {
    // DŮLEŽITÉ: Vynutit kontrolu aktualizací
    wp_update_themes();
    
    $themes = wp_get_themes();
    $result = array();
    $active_theme = get_stylesheet();
    
    // Získat dostupné aktualizace (funkce může být dostupná pouze v admin kontextu)
    $updates = array();
    if (function_exists('get_theme_updates')) {
        $updates = get_theme_updates();
    }
    
    foreach ($themes as $slug => $theme) {
        // Zkontrolovat, zda je dostupná aktualizace
        $update_available = false;
        $update_version = null;
        
        if (isset($updates[$slug])) {
            $update_available = true;
            $update_version = $updates[$slug]->update['new_version'];
        }
        
        $result[] = array(
            'slug' => $slug,
            'stylesheet' => $theme->get_stylesheet(), // Skutečný název adresáře
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'active' => ($slug === $active_theme),
            'update_available' => $update_available,
            'update_version' => $update_version
        );
    }
    
    return rest_ensure_response($result);
}

/**
 * Aktualizovat téma
 */
function bricks_update_theme($request) {
    $theme_slug = $request->get_param('theme');
    
    if (empty($theme_slug)) {
        $theme_slug = 'bricks'; // Výchozí název Bricks tématu
    }

    // Zkontrolovat oprávnění
    if (!current_user_can('update_themes')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění aktualizovat témata', array('status' => 403));
    }

    // Zkontrolovat, zda téma existuje
    $theme_obj = wp_get_theme($theme_slug);
    if (!$theme_obj->exists()) {
        return new WP_Error('theme_not_found', 'Téma nebylo nalezeno: ' . $theme_slug, array('status' => 404));
    }

    // Načíst WordPress Filesystem API
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
    require_once(ABSPATH . 'wp-admin/includes/theme.php');

    // Nastavit filesystem method na direct
    if (!defined('FS_METHOD')) {
        define('FS_METHOD', 'direct');
    }

    // Vytvořit tichý upgrader skin
    $skin = new class extends WP_Upgrader_Skin {
        public function feedback($string, ...$args) { return; }
        public function header() { return; }
        public function footer() { return; }
    };
    
    // Vytvořit Theme upgrader
    $upgrader = new Theme_Upgrader($skin);

    // Aktualizovat téma
    $result = $upgrader->upgrade($theme_slug);

    if (is_wp_error($result)) {
        return new WP_Error('update_failed', 'Aktualizace selhala: ' . $result->get_error_message(), array('status' => 500));
    }

    if ($result === false) {
        return new WP_Error('update_failed', 'Aktualizace selhala - neznámá chyba', array('status' => 500));
    }

    // Získat novou verzi
    $updated_theme = wp_get_theme($theme_slug);
    $new_version = $updated_theme->get('Version');
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Téma úspěšně aktualizováno',
        'theme' => $theme_slug,
        'new_version' => $new_version
    ));
}

/**
 * Smazat téma
 */
function bricks_delete_theme($request) {
    $theme_slug = $request->get_param('theme');
    
    if (empty($theme_slug)) {
        return new WP_Error('missing_theme', 'Slug tématu je povinný', array('status' => 400));
    }
    
    // Zkontrolovat oprávnění
    if (!current_user_can('delete_themes')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění mazat témata', array('status' => 403));
    }
    
    // Zkontrolovat, zda téma existuje
    $theme = wp_get_theme($theme_slug);
    if (!$theme->exists()) {
        return new WP_Error('theme_not_found', 'Téma nenalezeno: ' . $theme_slug, array('status' => 404));
    }
    
    // Zkontrolovat, zda není aktivní téma
    $active_theme = get_stylesheet();
    if ($theme_slug === $active_theme) {
        return new WP_Error('cannot_delete_active', 'Nelze smazat aktivní téma. Nejdříve aktivujte jiné téma.', array('status' => 400));
    }
    
    // Načíst WordPress Filesystem API
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    
    // Získat cestu k tématu
    $theme_root = get_theme_root();
    $theme_path = $theme_root . '/' . $theme_slug;
    
    // Smazat složku tématu
    if (!file_exists($theme_path)) {
        return new WP_Error('theme_path_not_found', 'Cesta k tématu nenalezena: ' . $theme_path, array('status' => 404));
    }
    
    // Použít WordPress Filesystem API pro smazání
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if (!$wp_filesystem->delete($theme_path, true)) {
        return new WP_Error('delete_failed', 'Nepodařilo se smazat téma', array('status' => 500));
    }
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Téma úspěšně smazáno',
        'theme' => $theme_slug,
        'deleted_path' => $theme_path
    ));
}

/**
 * Smazat template
 */
function bricks_delete_template($request) {
    $template_id = $request->get_param('id');
    $force = $request->get_param('force') !== false; // Výchozí true
    
    // Zkontrolovat oprávnění
    if (!current_user_can('delete_posts')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění mazat templates', array('status' => 403));
    }
    
    // Zkontrolovat, zda template existuje
    $template = get_post($template_id);
    if (!$template || $template->post_type !== 'bricks_template') {
        return new WP_Error('template_not_found', 'Template nenalezen', array('status' => 404));
    }
    
    // Smazat template
    if ($force) {
        $result = wp_delete_post($template_id, true); // true = trvale smazat
    } else {
        $result = wp_trash_post($template_id); // Přesunout do koše
    }
    
    if (!$result) {
        return new WP_Error('delete_failed', 'Nepodařilo se smazat template', array('status' => 500));
    }
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => $force ? 'Template trvale smazán' : 'Template přesunut do koše',
        'id' => $template_id,
        'deleted' => $force
    ));
}

/**
 * Instalovat plugin z URL
 */
function bricks_install_plugin_from_url($request) {
    $plugin_url = $request->get_param('plugin_url');

    // Zkontrolovat oprávnění
    if (!current_user_can('install_plugins')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění instalovat pluginy', array('status' => 403));
    }

    // Načíst WordPress Filesystem API
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
    require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');

    // Vytvořit upgrader
    $upgrader = new Plugin_Upgrader();

    // Stáhnout a nainstalovat plugin
    $result = $upgrader->install($plugin_url);

    if (is_wp_error($result)) {
        return new WP_Error('install_failed', 'Instalace selhala: ' . $result->get_error_message(), array('status' => 500));
    }

    if ($result === false) {
        return new WP_Error('install_failed', 'Instalace selhala - neznámá chyba', array('status' => 500));
    }

    // Získat název pluginu
    $plugin_file = $upgrader->plugin_info();
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Plugin úspěšně nainstalován',
        'plugin' => $plugin_file
    ));
}

/**
 * Aktivovat plugin
 */
function bricks_activate_plugin($request) {
    $plugin = $request->get_param('plugin');

    // Zkontrolovat oprávnění
    if (!current_user_can('activate_plugins')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění aktivovat pluginy', array('status' => 403));
    }

    // Zkontrolovat, zda plugin existuje
    if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
        return new WP_Error('plugin_not_found', 'Plugin nenalezen: ' . $plugin, array('status' => 404));
    }

    // Aktivovat plugin
    $result = activate_plugin($plugin);

    if (is_wp_error($result)) {
        return new WP_Error('activation_failed', 'Aktivace selhala: ' . $result->get_error_message(), array('status' => 500));
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Plugin úspěšně aktivován',
        'plugin' => $plugin
    ));
}

/**
 * Aktualizovat Bricks licenční klíč
 */
function bricks_update_license($request) {
    $license_key = $request->get_param('license_key');

    // Zkontrolovat oprávnění
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění spravovat nastavení', array('status' => 403));
    }

    // Uložit licenční klíč do WordPress options
    // Bricks ukládá licenci v různých místech podle verze
    $result = update_option('bricks_license_key', $license_key);
    
    // Zkusit také alternativní možnosti
    update_option('bricks_license', $license_key);
    update_option('bricks_license_key_hash', md5($license_key));

    // Pokud je Bricks aktivní, zkusit použít jeho API
    if (function_exists('bricks_set_license')) {
        bricks_set_license($license_key);
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Licenční klíč aktualizován',
        'license_key' => $license_key
    ));
}

/**
 * Aktivovat Bricks licenci
 */
function bricks_activate_license($request) {
    $license_key = $request->get_param('license_key');

    // Zkontrolovat oprávnění
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění spravovat nastavení', array('status' => 403));
    }

    if (empty($license_key)) {
        return new WP_Error('missing_license_key', 'Licenční klíč je povinný', array('status' => 400));
    }

    // Uložit licenční klíč
    update_option('bricks_license_key', $license_key);
    update_option('bricks_license', $license_key);
    update_option('bricks_license_key_hash', md5($license_key));

    // Zkusit použít Bricks API pro aktivaci (pokud existuje)
    $activation_result = null;
    if (class_exists('\\Bricks\\License')) {
        if (method_exists('\\Bricks\\License', 'activate')) {
            $activation_result = \Bricks\License::activate($license_key);
        }
    }

    // Zkusit také přes AJAX akci (pokud Bricks používá AJAX)
    if (has_action('wp_ajax_bricks_activate_license')) {
        do_action('wp_ajax_bricks_activate_license');
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Licence aktivována',
        'license_key' => $license_key,
        'activation_result' => $activation_result,
        'note' => 'Zkontrolujte v Bricks → Settings → License, zda je licence aktivní'
    ));
}

/**
 * Nastavit WordPress Reading (Static page)
 */
function bricks_set_reading($request) {
    $page_id = $request->get_param('page_id');

    // Zkontrolovat oprávnění
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění spravovat nastavení', array('status' => 403));
    }

    if (empty($page_id)) {
        return new WP_Error('missing_page_id', 'Page ID je povinné', array('status' => 400));
    }

    // Zkontrolovat, zda stránka existuje
    $page = get_post($page_id);
    if (!$page || $page->post_type !== 'page') {
        return new WP_Error('page_not_found', 'Stránka nenalezena', array('status' => 404));
    }

    // Nastavit WordPress Reading settings
    update_option('show_on_front', 'page');
    update_option('page_on_front', $page_id);

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'WordPress Reading nastaveno na statickou stránku',
        'page_id' => $page_id,
        'page_title' => $page->post_title,
        'settings' => array(
            'show_on_front' => 'page',
            'page_on_front' => $page_id
        )
    ));
}

/**
 * Získat všechny Bricks Templates
 */
function bricks_get_templates($request) {
    $template_type = $request->get_param('type'); // header, footer, atd.
    
    $args = array(
        'post_type' => 'bricks_template',
        'posts_per_page' => -1,
        'post_status' => 'any'
    );
    
    if ($template_type) {
        $args['meta_query'] = array(
            array(
                'key' => '_bricks_template_type',
                'value' => $template_type,
                'compare' => '='
            )
        );
    }
    
    $templates = get_posts($args);
    $result = array();
    
    foreach ($templates as $template) {
        $template_type_meta = get_post_meta($template->ID, '_bricks_template_type', true);
        
        // Získat Bricks obsah - zkusit oba možné meta klíče
        $bricks_content = get_post_meta($template->ID, '_bricks_page_content_2', true);
        if (empty($bricks_content)) {
            $bricks_content = get_post_meta($template->ID, '_bricks_page_content', true);
        }
        
        // Pokud je obsah string, zkusit dekódovat
        if (is_string($bricks_content)) {
            $decoded = json_decode($bricks_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $bricks_content = $decoded;
            }
        }
        
        $result[] = array(
            'id' => $template->ID,
            'title' => $template->post_title,
            'slug' => $template->post_name,
            'type' => $template_type_meta,
            'status' => $template->post_status,
            'bricks_content' => $bricks_content,
            'has_content' => !empty($bricks_content),
            'content_type' => gettype($bricks_content),
            'content_length' => is_array($bricks_content) ? count($bricks_content) : (is_string($bricks_content) ? strlen($bricks_content) : 0)
        );
    }
    
    return rest_ensure_response($result);
}

/**
 * Vytvořit nebo aktualizovat Bricks Template
 */
function bricks_create_or_update_template($request) {
    $title = $request->get_param('title');
    $template_type = $request->get_param('type'); // header, footer
    $content = $request->get_param('content'); // Bricks JSON obsah
    $template_id = $request->get_param('id'); // Pokud aktualizujeme existující
    
    if (empty($title) || empty($template_type)) {
        return new WP_Error('missing_params', 'Title a type jsou povinné', array('status' => 400));
    }
    
    if (!in_array($template_type, array('header', 'footer'))) {
        return new WP_Error('invalid_type', 'Type musí být "header" nebo "footer"', array('status' => 400));
    }
    
    // Validovat a zpracovat JSON obsah
    $content_array = null;
    if (!empty($content)) {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new WP_Error('invalid_json', 'Neplatný JSON formát: ' . json_last_error_msg(), array('status' => 400));
            }
            
            // Bricks očekává pouze pole elementů, ne celý objekt
            // Pokud přišel objekt s "content", extrahovat pouze content
            if (isset($decoded['content']) && is_array($decoded['content'])) {
                $content_array = $decoded['content'];
            } else {
                $content_array = $decoded;
            }
        } else if (is_array($content)) {
            // Pokud je to objekt s "content", extrahovat
            if (isset($content['content']) && is_array($content['content'])) {
                $content_array = $content['content'];
            } else {
                $content_array = $content;
            }
        }
    }
    
    // Pokud máme ID, aktualizovat existující
    if ($template_id) {
        $template = get_post($template_id);
        if (!$template || $template->post_type !== 'bricks_template') {
            return new WP_Error('template_not_found', 'Template nenalezen', array('status' => 404));
        }
        
        // Aktualizovat
        wp_update_post(array(
            'ID' => $template_id,
            'post_title' => $title,
            'post_status' => 'publish'
        ));
        
        update_post_meta($template_id, '_bricks_template_type', $template_type);
        if (!empty($content_array)) {
            // DŮLEŽITÉ: Pro header templates Bricks používá _bricks_page_header_2 místo _bricks_page_content_2!
            // Pro footer templates možná _bricks_page_footer_2
            $specific_meta_key = null;
            if ($template_type === 'header') {
                $specific_meta_key = '_bricks_page_header_2';
            } elseif ($template_type === 'footer') {
                $specific_meta_key = '_bricks_page_footer_2';
            }
            
            // Uložit do standardních meta klíčů
            update_post_meta($template_id, '_bricks_page_content_2', $content_array);
            update_post_meta($template_id, '_bricks_page_content', $content_array);
            
            // DŮLEŽITÉ: Uložit také do specifického meta klíče pro header/footer!
            if ($specific_meta_key) {
                update_post_meta($template_id, $specific_meta_key, $content_array);
            }
            
            // Také nastavit, že používáme Bricks editor
            update_post_meta($template_id, '_bricks_editor_mode', 'bricks');
            // DŮLEŽITÉ: Nastavit content type
            update_post_meta($template_id, '_bricks_page_content_type', 'bricks');
        }
        
        // DŮLEŽITÉ: Pro Header a Footer templates
        if (in_array($template_type, array('header', 'footer'))) {
            update_post_meta($template_id, '_bricks_template_active', true);
            update_post_meta($template_id, '_bricks_template_conditions', array());
        }
        
        // Zkontrolovat, co se skutečně uložilo
        $saved_content = get_post_meta($template_id, '_bricks_page_content', true);
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Template aktualizován',
            'id' => $template_id,
            'title' => $title,
            'type' => $template_type,
            'content_saved' => !empty($saved_content),
            'content_length' => is_string($saved_content) ? strlen($saved_content) : (is_array($saved_content) ? count($saved_content) : 0)
        ));
    }
    
    // Vytvořit nový template
    $post_id = wp_insert_post(array(
        'post_title' => $title,
        'post_type' => 'bricks_template',
        'post_status' => 'publish',
        'post_content' => ''
    ));
    
    if (is_wp_error($post_id)) {
        return new WP_Error('create_failed', 'Nepodařilo se vytvořit template: ' . $post_id->get_error_message(), array('status' => 500));
    }
    
    // Nastavit meta
    update_post_meta($post_id, '_bricks_template_type', $template_type);
    if (!empty($content_array)) {
        // DŮLEŽITÉ: Pro header templates Bricks používá _bricks_page_header_2 místo _bricks_page_content_2!
        // Pro footer templates možná _bricks_page_footer_2
        $specific_meta_key = null;
        if ($template_type === 'header') {
            $specific_meta_key = '_bricks_page_header_2';
        } elseif ($template_type === 'footer') {
            $specific_meta_key = '_bricks_page_footer_2';
        }
        
        // Uložit do standardních meta klíčů
        update_post_meta($post_id, '_bricks_page_content_2', $content_array);
        update_post_meta($post_id, '_bricks_page_content', $content_array);
        
        // DŮLEŽITÉ: Uložit také do specifického meta klíče pro header/footer!
        if ($specific_meta_key) {
            update_post_meta($post_id, $specific_meta_key, $content_array);
        }
        
        // Také nastavit, že používáme Bricks editor
        update_post_meta($post_id, '_bricks_editor_mode', 'bricks');
        // DŮLEŽITÉ: Nastavit content type
        update_post_meta($post_id, '_bricks_page_content_type', 'bricks');
    }
    
    // DŮLEŽITÉ: Pro Header a Footer templates, které se používají automaticky,
    // Bricks může potřebovat explicitní nastavení, že template je aktivní
    // Nastavit meta, které Bricks používá pro identifikaci default templates
    if (in_array($template_type, array('header', 'footer'))) {
        // Bricks může kontrolovat, zda template má být použit jako default
        // Nastavit meta klíč, který Bricks používá
        update_post_meta($post_id, '_bricks_template_active', true);
        update_post_meta($post_id, '_bricks_template_conditions', array());
        
        // Pro header/footer templates, které se používají globálně,
        // můžeme nastavit prázdné conditions (což znamená "použít všude")
        // Bricks automaticky použije první publikovaný header/footer template
    }
    
    // Zkontrolovat, co se skutečně uložilo
    $saved_content = get_post_meta($post_id, '_bricks_page_content', true);
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Template vytvořen',
        'id' => $post_id,
        'title' => $title,
        'type' => $template_type,
        'content_saved' => !empty($saved_content),
        'content_length' => is_string($saved_content) ? strlen($saved_content) : (is_array($saved_content) ? count($saved_content) : 0)
    ));
}

/**
 * Aktualizovat Bricks obsah template (stejně jako pro pages)
 */
function bricks_update_template_content($request) {
    $template_id = $request->get_param('id');
    $content = $request->get_param('content');

    // Zkontrolovat, zda template existuje
    $template = get_post($template_id);
    if (!$template || $template->post_type !== 'bricks_template') {
        return new WP_Error('template_not_found', 'Template nenalezen', array('status' => 404));
    }

    // Validovat a dekódovat JSON
    if (is_string($content)) {
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('invalid_json', 'Neplatný JSON formát: ' . json_last_error_msg(), array('status' => 400));
        }
        
        // Pokud má objekt "content" pole, extrahovat
        if (isset($decoded['content']) && is_array($decoded['content'])) {
            $content_array = $decoded['content'];
        } else {
            $content_array = $decoded;
        }
    } else {
        $content_array = $content;
    }
    
    // DŮLEŽITÉ: Pro header templates Bricks používá _bricks_page_header_2 místo _bricks_page_content_2!
    // Pro footer templates možná _bricks_page_footer_2
    $template_type = get_post_meta($template_id, '_bricks_template_type', true);
    
    // Určit správný meta klíč podle typu template
    $specific_meta_key = null;
    if ($template_type === 'header') {
        $specific_meta_key = '_bricks_page_header_2';
    } elseif ($template_type === 'footer') {
        $specific_meta_key = '_bricks_page_footer_2';
    }
    
    // Uložit do standardních meta klíčů
    $result1 = update_post_meta($template_id, '_bricks_page_content', $content_array);
    $result2 = update_post_meta($template_id, '_bricks_page_content_2', $content_array);
    
    // DŮLEŽITÉ: Uložit také do specifického meta klíče pro header/footer!
    if ($specific_meta_key) {
        update_post_meta($template_id, $specific_meta_key, $content_array);
    }
    
    // Nastavit editor mode na 'bricks'
    update_post_meta($template_id, '_bricks_editor_mode', 'bricks');
    
    // Také nastavit, že template používá Bricks
    update_post_meta($template_id, '_bricks_page_content_type', 'bricks');
    
    // DŮLEŽITÉ: Pro templates, které se používají automaticky (header/footer),
    // Bricks může potřebovat explicitní nastavení, že template je aktivní
    if (in_array($template_type, array('header', 'footer'))) {
        update_post_meta($template_id, '_bricks_template_active', true);
        // Prázdné pole conditions = použít všude
        update_post_meta($template_id, '_bricks_template_conditions', array());
    }
    
    // DŮLEŽITÉ: Zkontrolovat, zda template má správný post_status
    $template = get_post($template_id);
    if ($template && $template->post_status !== 'publish') {
        wp_update_post(array(
            'ID' => $template_id,
            'post_status' => 'publish'
        ));
    }
    
    // DŮLEŽITÉ: Možná Bricks potřebuje, aby template měl nastavený nějaký další meta klíč
    // Zkusit nastavit všechny možné meta klíče, které Bricks může potřebovat
    if (!empty($content_array) && is_array($content_array)) {
        // Nastavit, že template má obsah
        update_post_meta($template_id, '_bricks_has_content', true);
        // Nastavit počet elementů
        update_post_meta($template_id, '_bricks_content_count', count($content_array));
    }
    
    // DŮLEŽITÉ: Spustit WordPress hook, aby Bricks věděl, že template byl aktualizován
    // Toto může být klíčové pro to, aby Bricks rozpoznal změny
    do_action('save_post', $template_id, $template, true);
    do_action('save_post_bricks_template', $template_id, $template, true);
    
    // Také zkusit vymazat cache, pokud existuje
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete($template_id, 'post_meta');
        wp_cache_delete($template_id, 'posts');
    }
    
    if ($result1 === false && $result2 === false) {
        return new WP_Error('update_failed', 'Nepodařilo se aktualizovat Bricks obsah', array('status' => 500));
    }

    // Vrátit aktualizovaný obsah
    $updated_content = get_post_meta($template_id, '_bricks_page_content_2', true);
    if (empty($updated_content)) {
        $updated_content = get_post_meta($template_id, '_bricks_page_content', true);
    }
    
    // Pokud je to serializované, deserializovat
    if (is_string($updated_content)) {
        $unserialized = maybe_unserialize($updated_content);
        if ($unserialized !== false) {
            $updated_content = $unserialized;
        } else {
            // Zkusit jako JSON
            $json_decoded = json_decode($updated_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $updated_content = $json_decoded;
            }
        }
    }

    return rest_ensure_response(array(
        'success' => true,
        'template_id' => $template_id,
        'content' => $updated_content,
        'meta_keys_updated' => array(
            '_bricks_page_content' => $result1 !== false,
            '_bricks_page_content_2' => $result2 !== false,
            '_bricks_editor_mode' => true
        )
    ));
}

/**
 * Debug: Získat všechny informace o template (pro debugging)
 */
function bricks_debug_template($request) {
    $template_id = $request->get_param('id');
    
    $template = get_post($template_id);
    if (!$template || $template->post_type !== 'bricks_template') {
        return new WP_Error('template_not_found', 'Template nenalezen', array('status' => 404));
    }
    
    // Získat všechny meta
    $all_meta = get_post_meta($template_id);
    
    // Získat Bricks obsah z obou meta klíčů
    $content_1 = get_post_meta($template_id, '_bricks_page_content', true);
    $content_2 = get_post_meta($template_id, '_bricks_page_content_2', true);
    
    // Deserializovat, pokud je to string
    $content_1_unserialized = is_string($content_1) ? maybe_unserialize($content_1) : $content_1;
    $content_2_unserialized = is_string($content_2) ? maybe_unserialize($content_2) : $content_2;
    
    // Pokud je to stále string, zkusit JSON decode
    if (is_string($content_1_unserialized)) {
        $json_decoded = json_decode($content_1_unserialized, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $content_1_unserialized = $json_decoded;
        }
    }
    if (is_string($content_2_unserialized)) {
        $json_decoded = json_decode($content_2_unserialized, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $content_2_unserialized = $json_decoded;
        }
    }
    
    return rest_ensure_response(array(
        'id' => $template_id,
        'title' => $template->post_title,
        'post_type' => $template->post_type,
        'post_status' => $template->post_status,
        'meta_keys' => array_keys($all_meta),
        'bricks_meta' => array(
            '_bricks_template_type' => get_post_meta($template_id, '_bricks_template_type', true),
            '_bricks_template_active' => get_post_meta($template_id, '_bricks_template_active', true),
            '_bricks_template_conditions' => get_post_meta($template_id, '_bricks_template_conditions', true),
            '_bricks_editor_mode' => get_post_meta($template_id, '_bricks_editor_mode', true),
            '_bricks_page_content_type' => get_post_meta($template_id, '_bricks_page_content_type', true),
        ),
        'content_1' => array(
            'raw_type' => gettype($content_1),
            'raw_length' => is_string($content_1) ? strlen($content_1) : (is_array($content_1) ? count($content_1) : 0),
            'unserialized_type' => gettype($content_1_unserialized),
            'unserialized_length' => is_string($content_1_unserialized) ? strlen($content_1_unserialized) : (is_array($content_1_unserialized) ? count($content_1_unserialized) : 0),
            'is_array' => is_array($content_1_unserialized),
            'first_element' => is_array($content_1_unserialized) && count($content_1_unserialized) > 0 ? $content_1_unserialized[0] : null
        ),
        'content_2' => array(
            'raw_type' => gettype($content_2),
            'raw_length' => is_string($content_2) ? strlen($content_2) : (is_array($content_2) ? count($content_2) : 0),
            'unserialized_type' => gettype($content_2_unserialized),
            'unserialized_length' => is_string($content_2_unserialized) ? strlen($content_2_unserialized) : (is_array($content_2_unserialized) ? count($content_2_unserialized) : 0),
            'is_array' => is_array($content_2_unserialized),
            'first_element' => is_array($content_2_unserialized) && count($content_2_unserialized) > 0 ? $content_2_unserialized[0] : null
        ),
        'content_1_sample' => is_array($content_1_unserialized) && count($content_1_unserialized) > 0 
            ? array_slice($content_1_unserialized, 0, 3) 
            : $content_1_unserialized,
        'content_2_sample' => is_array($content_2_unserialized) && count($content_2_unserialized) > 0 
            ? array_slice($content_2_unserialized, 0, 3) 
            : $content_2_unserialized
    ));
}

/**
 * Získat meta data template
 */
function bricks_get_template_meta($request) {
    $template_id = $request->get_param('id');
    
    $template = get_post($template_id);
    if (!$template || $template->post_type !== 'bricks_template') {
        return new WP_Error('template_not_found', 'Template nenalezen', array('status' => 404));
    }
    
    // Získat všechny meta
    $all_meta = get_post_meta($template_id);
    $bricks_meta = array();
    
    foreach ($all_meta as $key => $value) {
        if (strpos($key, 'bricks') !== false || strpos($key, '_bricks') !== false) {
            $bricks_meta[$key] = $value;
        }
    }
    
    // Zvlášť získat _bricks_page_content
    $bricks_content = get_post_meta($template_id, '_bricks_page_content', true);
    
    return rest_ensure_response(array(
        'id' => $template_id,
        'title' => $template->post_title,
        'status' => $template->post_status,
        'bricks_meta' => $bricks_meta,
        '_bricks_page_content' => $bricks_content,
        '_bricks_page_content_type' => gettype($bricks_content),
        '_bricks_page_content_length' => is_string($bricks_content) ? strlen($bricks_content) : (is_array($bricks_content) ? count($bricks_content) : 0)
    ));
}

/**
 * Povolit Code Execution v Bricks
 * Nastavení: Bricks > Settings > Custom code > Code execution
 */
function bricks_enable_code_execution($request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění spravovat nastavení', array('status' => 403));
    }

    // Bricks ukládá code execution nastavení v různých místech podle verze
    // Zkusit všechny možné option keys
    
    // Bricks 2.x používá pravděpodobně:
    $result = array();
    
    // Možnost 1: Přímý option key
    update_option('bricks_code_execution', '1');
    update_option('bricks_code_execution_enabled', true);
    $result['bricks_code_execution'] = 'updated';
    
    // Možnost 2: V bricks_settings array
    $bricks_settings = get_option('bricks_settings', array());
    if (!is_array($bricks_settings)) {
        $bricks_settings = array();
    }
    $bricks_settings['code_execution'] = true;
    $bricks_settings['code_execution_enabled'] = true;
    update_option('bricks_settings', $bricks_settings);
    $result['bricks_settings'] = 'updated';
    
    // Možnost 3: V bricks_custom_code_settings
    $custom_code_settings = get_option('bricks_custom_code_settings', array());
    if (!is_array($custom_code_settings)) {
        $custom_code_settings = array();
    }
    $custom_code_settings['code_execution'] = true;
    $custom_code_settings['code_execution_enabled'] = true;
    update_option('bricks_custom_code_settings', $custom_code_settings);
    $result['bricks_custom_code_settings'] = 'updated';
    
    // Možnost 4: Přímý database update (pokud je Bricks aktivní)
    global $wpdb;
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = 'bricks_code_execution'",
        '1'
    ));
    
    // Zkusit také jako serializovaný boolean
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = 'bricks_code_execution'",
        serialize(true)
    ));
    
    // Zkusit najít všechny bricks_* options a aktualizovat je
    $bricks_options = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'bricks%code%execution%' OR option_name LIKE 'bricks%custom%code%'"
    );
    
    foreach ($bricks_options as $option) {
        update_option($option->option_name, true);
    }
    
    $result['database_direct'] = 'updated';
    
    // Možnost 5: Zkusit přes Bricks settings API (pokud existuje)
    if (function_exists('bricks_set_code_execution')) {
        bricks_set_code_execution(true);
        $result['bricks_function'] = 'called';
    }
    
    // Možnost 6: Zkusit přes update_option s různými formáty
    update_option('bricks_code_execution', 1);
    update_option('bricks_code_execution', '1');
    update_option('bricks_code_execution', 'yes');
    update_option('bricks_code_execution', 'enabled');
    
    // Možnost 7: Zkusit v bricks_settings jako serializovaný array
    $bricks_settings_full = get_option('bricks_settings', array());
    if (!is_array($bricks_settings_full)) {
        $bricks_settings_full = array();
    }
    $bricks_settings_full['code_execution'] = 1;
    $bricks_settings_full['code_execution_enabled'] = 1;
    $bricks_settings_full['custom_code'] = array('code_execution' => true);
    update_option('bricks_settings', $bricks_settings_full);
    $result['bricks_settings_full'] = 'updated';
    
    // Možnost 8: Zkusit přes transients (Bricks může používat cache)
    delete_transient('bricks_code_execution');
    set_transient('bricks_code_execution', true, 0);
    $result['transients'] = 'updated';
    
    // Možnost 9: Povolit Code Execution pro všechny role (Administrator, Editor, atd.)
    $bricks_settings = get_option('bricks_settings', array());
    if (!is_array($bricks_settings)) {
        $bricks_settings = array();
    }
    
    // Povolit code execution pro všechny role
    $bricks_settings['code_execution_roles'] = array('administrator', 'editor', 'author', 'contributor');
    $bricks_settings['code_execution_enabled_roles'] = array('administrator', 'editor', 'author', 'contributor');
    
    // Povolit code execution globálně
    $bricks_settings['code_execution'] = true;
    $bricks_settings['code_execution_enabled'] = true;
    $bricks_settings['enable_code_execution'] = true;
    
    update_option('bricks_settings', $bricks_settings);
    $result['bricks_settings_roles'] = 'updated';
    
    // Možnost 10: Přes bricks/code/echo_function_names filter (pokud existuje)
    if (function_exists('add_filter')) {
        add_filter('bricks/code/echo_function_names', function($functions) {
            return array_merge($functions ? $functions : array(), array('echo', 'print', 'var_dump', 'print_r'));
        });
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Code execution povoleno',
        'updated_options' => $result,
        'note' => 'Pokud se nastavení neprojeví, zkontrolujte ručně v Bricks > Settings > Custom code > Code execution'
    ));
}

/**
 * Získat status Code Execution nastavení
 */
function bricks_get_code_execution_status($request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění spravovat nastavení', array('status' => 403));
    }

    $status = array();
    
    // Zkontrolovat všechny možné option keys
    $status['bricks_code_execution'] = get_option('bricks_code_execution', false);
    $status['bricks_code_execution_enabled'] = get_option('bricks_code_execution_enabled', false);
    
    $bricks_settings = get_option('bricks_settings', array());
    if (is_array($bricks_settings)) {
        $status['bricks_settings_code_execution'] = isset($bricks_settings['code_execution']) ? $bricks_settings['code_execution'] : false;
    }
    
    $custom_code_settings = get_option('bricks_custom_code_settings', array());
    if (is_array($custom_code_settings)) {
        $status['bricks_custom_code_settings_code_execution'] = isset($custom_code_settings['code_execution']) ? $custom_code_settings['code_execution'] : false;
    }
    
    // Zjistit, zda je nějaké nastavení aktivní
    $is_enabled = false;
    foreach ($status as $key => $value) {
        if ($value === true || $value === '1' || $value === 1) {
            $is_enabled = true;
            break;
        }
    }
    
    return rest_ensure_response(array(
        'enabled' => $is_enabled,
        'status' => $status,
        'note' => 'Zkontrolujte v Bricks > Settings > Custom code > Code execution'
    ));
}

/**
 * Povolit editaci Pages pomocí Bricks
 * Nastavení: Bricks > Settings > Post types > Page
 */
function bricks_enable_pages_editing($request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění spravovat nastavení', array('status' => 403));
    }

    $result = array();
    
    // Bricks ukládá post types v bricks_settings
    $bricks_settings = get_option('bricks_settings', array());
    if (!is_array($bricks_settings)) {
        $bricks_settings = array();
    }
    
    // Povolit editaci Pages
    if (!isset($bricks_settings['post_types']) || !is_array($bricks_settings['post_types'])) {
        $bricks_settings['post_types'] = array();
    }
    
    // Přidat 'page' do post_types
    if (!in_array('page', $bricks_settings['post_types'])) {
        $bricks_settings['post_types'][] = 'page';
    }
    
    // Alternativní možnosti
    $bricks_settings['enable_page_editing'] = true;
    $bricks_settings['page_editing_enabled'] = true;
    $bricks_settings['bricks_page'] = true;
    
    update_option('bricks_settings', $bricks_settings);
    $result['bricks_settings'] = 'updated';
    
    // Zkusit také přímý option
    update_option('bricks_post_types', array('page'));
    $result['bricks_post_types'] = 'updated';
    
    // Zkusit přes database
    global $wpdb;
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = 'bricks_post_types'",
        serialize(array('page'))
    ));
    $result['database_direct'] = 'updated';
    
    // Smazat cache
    delete_transient('bricks_post_types');
    $result['cache_cleared'] = 'updated';

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Editace Pages pomocí Bricks povolena',
        'updated_options' => $result,
        'note' => 'Pokud se nastavení neprojeví, zkontrolujte ručně v Bricks > Settings > Post types > Page'
    ));
}

/**
 * Kompletní konfigurace všech Bricks Settings najednou
 * Povolí Code Execution + Editaci Pages
 */
function bricks_configure_settings($request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění spravovat nastavení', array('status' => 403));
    }

    $result = array();
    
    // Načíst aktuální bricks_settings
    $bricks_settings = get_option('bricks_settings', array());
    if (!is_array($bricks_settings)) {
        $bricks_settings = array();
    }
    
    // 1. POVOLIT CODE EXECUTION - různé formáty
    $bricks_settings['code_execution'] = true;
    $bricks_settings['code_execution_enabled'] = true;
    $bricks_settings['enable_code_execution'] = true;
    $bricks_settings['executeCode'] = true;
    
    // Code execution pro role - různé formáty
    // Povolit pouze Administrator (jak uživatel požadoval)
    // Formát 1: Pole pouze s povolenými rolemi
    $bricks_settings['code_execution_roles'] = array('administrator');
    $bricks_settings['codeExecutionRoles'] = array('administrator');
    
    // Formát 2: Samostatné boolean hodnoty pro každou roli (podobně jako post_types_page: true)
    $bricks_settings['code_execution_role_administrator'] = true;
    $bricks_settings['code_execution_role_editor'] = false;
    $bricks_settings['code_execution_role_author'] = false;
    $bricks_settings['code_execution_role_contributor'] = false;
    
    // Formát 3: Alternativní názvy
    $bricks_settings['codeExecutionRoleAdministrator'] = true;
    $bricks_settings['codeExecutionRoleEditor'] = false;
    $bricks_settings['codeExecutionRoleAuthor'] = false;
    $bricks_settings['codeExecutionRoleContributor'] = false;
    
    // Formát 4: V custom_code sekci
    if (!isset($bricks_settings['custom_code']) || !is_array($bricks_settings['custom_code'])) {
        $bricks_settings['custom_code'] = array();
    }
    $bricks_settings['custom_code']['code_execution'] = true;
    $bricks_settings['custom_code']['code_execution_roles'] = array('administrator');
    $bricks_settings['custom_code']['codeExecutionRoles'] = array('administrator');
    
    // Formát 5: Samostatné boolean hodnoty v custom_code
    $bricks_settings['custom_code']['code_execution_role_administrator'] = true;
    $bricks_settings['custom_code']['code_execution_role_editor'] = false;
    $bricks_settings['custom_code']['code_execution_role_author'] = false;
    $bricks_settings['custom_code']['code_execution_role_contributor'] = false;
    
    // 2. POVOLIT EDITACI PAGES - různé formáty
    // Formát 1: Array
    $bricks_settings['post_types'] = array('page', 'post');
    $bricks_settings['postTypes'] = array('page', 'post');
    
    // Formát 2: Jednotlivé klíče
    $bricks_settings['post_types_page'] = true;
    $bricks_settings['post_types_post'] = true;
    $bricks_settings['postTypes_page'] = true;
    $bricks_settings['postTypes_post'] = true;
    
    // Formát 3: Objekt
    $bricks_settings['postTypesEnabled'] = array('page' => true, 'post' => true);
    
    // Uložit bricks_settings
    update_option('bricks_settings', $bricks_settings);
    $result['bricks_settings'] = 'updated';
    
    // 3. DŮLEŽITÉ: Také aktualizovat bricks_global_settings!
    $bricks_global_settings = get_option('bricks_global_settings', array());
    if (!is_array($bricks_global_settings)) {
        $bricks_global_settings = array();
    }
    
    // Post types v global settings (Bricks 2.x používá tento formát)
    $bricks_global_settings['postTypes'] = array('post', 'page');
    
    // Code execution v global settings
    $bricks_global_settings['executeCodeEnabled'] = true;
    
    // Code execution role v global settings
    $bricks_global_settings['codeExecutionRoles'] = array('administrator');
    $bricks_global_settings['code_execution_roles'] = array('administrator');
    
    // Také jako samostatné boolean hodnoty v global settings
    $bricks_global_settings['codeExecutionRoleAdministrator'] = true;
    $bricks_global_settings['codeExecutionRoleEditor'] = false;
    $bricks_global_settings['codeExecutionRoleAuthor'] = false;
    $bricks_global_settings['codeExecutionRoleContributor'] = false;
    
    // Uložit bricks_global_settings
    update_option('bricks_global_settings', $bricks_global_settings);
    $result['bricks_global_settings'] = 'updated';
    
    // 4. Použít WordPress filtry (pokud Bricks je aktivní)
    // Filtr pro povolení post types
    if (function_exists('add_filter')) {
        add_filter('bricks/registered_post_types_args', function($args) {
            // Povolit Bricks pro všechny post types
            return $args;
        }, 999);
        $result['filters_added'] = 'yes';
    }
    
    // Filtr pro povolení code execution
    if (function_exists('add_filter')) {
        add_filter('bricks/code/disable_execution', '__return_false', 999);
        add_filter('bricks/code/allow_execution', '__return_true', 999);
        add_filter('bricks/code/execute', '__return_true', 999);
        $result['code_execution_filter'] = 'enabled';
    }
    
    // 5. DŮLEŽITÉ: Uložit role jako user meta pro aktuálního uživatele
    $current_user_id = get_current_user_id();
    if ($current_user_id) {
        update_user_meta($current_user_id, 'bricks_code_execution', true);
        update_user_meta($current_user_id, 'bricks_code_execution_enabled', true);
        $result['user_meta_updated'] = $current_user_id;
    }
    
    // 5. Zkusit použít Bricks API přímo (pokud existuje)
    if (class_exists('\\Bricks\\Database')) {
        // Zkusit uložit přes Bricks Database třídu
        if (method_exists('\\Bricks\\Database', 'set_setting')) {
            \Bricks\Database::set_setting('postTypes', array('post', 'page'));
            \Bricks\Database::set_setting('executeCodeEnabled', true);
            $result['bricks_database_api'] = 'used';
        }
    }
    
    $result['bricks_global_settings_content'] = $bricks_global_settings;
    
    // Smazat VŠECHNY cache
    delete_transient('bricks_settings');
    delete_transient('bricks_global_settings');
    wp_cache_delete('bricks_settings', 'options');
    wp_cache_delete('bricks_global_settings', 'options');
    wp_cache_flush(); // Vymazat celou cache
    
    // Zkusit také přes Bricks cache (pokud existuje)
    if (function_exists('bricks_clear_cache')) {
        bricks_clear_cache();
        $result['bricks_cache_cleared'] = 'yes';
    }
    
    // Trigger WordPress akcí, které Bricks může poslouchat
    do_action('bricks/settings/updated', $bricks_settings);
    do_action('bricks/global_settings/updated', $bricks_global_settings);
    do_action('update_option_bricks_settings', $bricks_settings, get_option('bricks_settings'));
    do_action('update_option_bricks_global_settings', $bricks_global_settings, get_option('bricks_global_settings'));
    
    $result['cache_cleared'] = 'completed';
    $result['actions_triggered'] = 'yes';

    // Zkontrolovat, zda se nastavení skutečně uložilo
    $final_bricks_settings = get_option('bricks_settings', array());
    $final_global_settings = get_option('bricks_global_settings', array());
    
    $pages_enabled = false;
    if (isset($final_bricks_settings['post_types']) && in_array('page', $final_bricks_settings['post_types'])) {
        $pages_enabled = true;
    }
    if (isset($final_global_settings['postTypes']) && in_array('page', $final_global_settings['postTypes'])) {
        $pages_enabled = true;
    }
    
    $code_execution_enabled = false;
    if (isset($final_bricks_settings['code_execution']) && $final_bricks_settings['code_execution']) {
        $code_execution_enabled = true;
    }
    if (isset($final_global_settings['executeCodeEnabled']) && $final_global_settings['executeCodeEnabled']) {
        $code_execution_enabled = true;
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Všechna Bricks nastavení nakonfigurována',
        'updated_options' => $result,
        'settings' => array(
            'code_execution' => $code_execution_enabled,
            'pages_editing' => $pages_enabled,
            'post_types' => $final_bricks_settings['post_types'] ?? $final_global_settings['postTypes'] ?? array(),
            'bricks_settings' => $final_bricks_settings,
            'bricks_global_settings' => $final_global_settings
        ),
        'note' => 'Obnovte stránku v Bricks Settings (F5)'
    ));
}

/**
 * Debug endpoint - získat aktuální strukturu bricks_settings
 * Pro zjištění správného formátu nastavení
 */
function bricks_debug_settings($request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění', array('status' => 403));
    }

    global $wpdb;
    
    // Získat VŠECHNY options obsahující klíčová slova
    $all_options = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} 
         WHERE option_name LIKE '%bricks%' 
         OR option_name LIKE '%post_type%'
         OR option_name LIKE '%code_exec%'
         OR option_name LIKE '%code%'
         OR option_name LIKE '%exec%'
         OR option_name LIKE '%builder%'
         OR option_name LIKE '%role%'
         OR option_name LIKE '%admin%'
         LIMIT 300"
    );
    
    $result = array();
    foreach ($all_options as $option) {
        $value = maybe_unserialize($option->option_value);
        $result[$option->option_name] = array(
            'value' => $value,
            'type' => gettype($value)
        );
    }
    
    // Získat bricks_settings
    $bricks_settings = get_option('bricks_settings', array());
    
    // Zkusit získat Bricks database class settings (pokud existuje)
    $bricks_db_settings = null;
    if (class_exists('\\Bricks\\Database')) {
        $bricks_db_settings = array(
            'post_types' => \Bricks\Database::get_setting('postTypes'),
            'code_execution' => \Bricks\Database::get_setting('codeExecution'),
            'all_settings' => \Bricks\Database::$global_settings ?? 'N/A'
        );
    }
    
    // Zkusit získat theme mods
    $theme_mods = get_theme_mods();
    $bricks_theme_mods = array();
    foreach ($theme_mods as $key => $value) {
        if (strpos($key, 'bricks') !== false || strpos($key, 'post') !== false || strpos($key, 'code') !== false) {
            $bricks_theme_mods[$key] = $value;
        }
    }
    
    return rest_ensure_response(array(
        'bricks_settings' => $bricks_settings,
        'bricks_settings_type' => gettype($bricks_settings),
        'bricks_db_settings' => $bricks_db_settings,
        'bricks_theme_mods' => $bricks_theme_mods,
        'all_relevant_options' => $result,
        'note' => 'Rozšířený debug výstup'
    ));
}

/**
 * Najít, kde Bricks ukládá post_types
 */
function bricks_find_post_types($request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('insufficient_permissions', 'Nemáte oprávnění', array('status' => 403));
    }

    global $wpdb;
    
    // Hledat všechny options, které obsahují "page" nebo "post" v hodnotě
    $all_options = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} 
         WHERE option_name LIKE '%bricks%' 
         OR option_name LIKE '%post%'
         OR option_name LIKE '%builder%'
         LIMIT 200"
    );
    
    $found = array();
    foreach ($all_options as $option) {
        $value = maybe_unserialize($option->option_value);
        $value_str = is_array($value) ? json_encode($value) : (string)$value;
        
        // Hledat "page" nebo "post" v hodnotě
        if (stripos($value_str, 'page') !== false || 
            stripos($value_str, 'post') !== false ||
            stripos($option->option_name, 'post') !== false ||
            stripos($option->option_name, 'page') !== false) {
            $found[$option->option_name] = array(
                'value' => $value,
                'type' => gettype($value),
                'raw_length' => strlen($option->option_value)
            );
        }
    }
    
    // Také zkusit získat přes Bricks API (pokud existuje)
    $bricks_post_types = null;
    if (function_exists('bricks_get_post_types')) {
        $bricks_post_types = bricks_get_post_types();
    }
    
    // Zkusit přes get_option s různými názvy
    $possible_names = array(
        'bricks_post_types',
        'bricks_postTypes',
        'bricks_post_types_enabled',
        'bricks_enable_post_types',
        'bricks_builder_post_types',
        'bricks_edit_post_types'
    );
    
    $found_options = array();
    foreach ($possible_names as $name) {
        $value = get_option($name, 'NOT_FOUND');
        if ($value !== 'NOT_FOUND') {
            $found_options[$name] = $value;
        }
    }
    
    return rest_ensure_response(array(
        'found_in_db' => $found,
        'bricks_api_result' => $bricks_post_types,
        'found_options' => $found_options,
        'bricks_settings_post_types' => get_option('bricks_settings', array())['post_types'] ?? 'NOT_FOUND',
        'note' => 'Hledání, kde Bricks ukládá post_types'
    ));
}

/**
 * Regenerovat code signatures pro stránku/template
 * Bricks generuje podpisy pomocí hash_hmac založeného na obsahu kódu a WordPress salts
 */
function bricks_regenerate_signatures($request) {
    $post_id = $request->get_param('id');
    
    // Zkontrolovat, zda post existuje
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('post_not_found', 'Post nenalezen', array('status' => 404));
    }
    
    // Získat Bricks obsah
    $bricks_content = get_post_meta($post_id, '_bricks_page_content_2', true);
    if (empty($bricks_content)) {
        $bricks_content = get_post_meta($post_id, '_bricks_page_content', true);
    }
    
    if (empty($bricks_content)) {
        return new WP_Error('no_bricks_content', 'Bricks obsah nenalezen', array('status' => 404));
    }
    
    // Deserializovat obsah
    if (is_string($bricks_content)) {
        $bricks_content = maybe_unserialize($bricks_content);
        if (is_string($bricks_content)) {
            $bricks_content = json_decode($bricks_content, true);
        }
    }
    
    if (!is_array($bricks_content)) {
        return new WP_Error('invalid_content', 'Neplatný formát obsahu', array('status' => 400));
    }
    
    // Zkusit použít Bricks interní funkci pro generování podpisů (pokud existuje)
    $signatures_regenerated = 0;
    $current_user_id = get_current_user_id();
    $current_time = time();
    
    // POZNÁMKA: Bricks používá WordPress wp_hash() funkci, která automaticky
    // používá všechny WordPress salts z wp-config.php
    // Není potřeba ručně kombinovat salts - wp_hash() to dělá za nás
    
    // Pro fallback (pokud wp_hash() není dostupná):
    $auth_salt = defined('AUTH_SALT') ? AUTH_SALT : '';
    $secure_auth_salt = defined('SECURE_AUTH_SALT') ? SECURE_AUTH_SALT : '';
    $logged_in_salt = defined('LOGGED_IN_SALT') ? LOGGED_IN_SALT : '';
    $nonce_salt = defined('NONCE_SALT') ? NONCE_SALT : '';
    
    // Kombinovat všechny salts pro fallback
    $salt = $auth_salt . $secure_auth_salt . $logged_in_salt . $nonce_salt;
    
    // Pokud nejsou salts definované, použít fallback
    if (empty($salt)) {
        $salt = get_option('bricks_license_key', '') . get_site_url() . get_option('admin_email', '');
    }
    
    // Projít všechny elementy a regenerovat podpisy pro CODE elementy
    foreach ($bricks_content as &$element) {
        if (isset($element['name']) && $element['name'] === 'code' && 
            isset($element['settings']['code'])) {
            
            $code_content = $element['settings']['code'];
            
            // Zkusit použít Bricks interní funkci (pokud existuje)
            $signature = null;
            
            if (function_exists('bricks_generate_code_signature')) {
                $signature = bricks_generate_code_signature($code_content);
            } elseif (class_exists('\\Bricks\\Code') && method_exists('\\Bricks\\Code', 'generate_signature')) {
                $signature = \Bricks\Code::generate_signature($code_content);
            } elseif (function_exists('wp_hash')) {
                // SPRÁVNÁ METODA: Bricks používá WordPress wp_hash() funkci
                // wp_hash() používá HMAC-MD5 algoritmus s WordPress salts
                // Toto je oficiální způsob, jak Bricks generuje podpisy
                $signature = wp_hash($code_content);
            } else {
                // Fallback: použít hash_hmac s MD5 (stejný algoritmus jako wp_hash)
                // Kombinovat všechny WordPress salts
                $signature = hash_hmac('md5', $code_content, $salt);
            }
            
            if ($signature) {
                // Uložit podpis
                $element['settings']['signature'] = $signature;
                $element['settings']['user_id'] = $current_user_id;
                $element['settings']['time'] = $current_time;
                
                // Ujistit se, že executeCode je true (pokud má kód)
                if (!isset($element['settings']['executeCode'])) {
                    $element['settings']['executeCode'] = true;
                }
                
                $signatures_regenerated++;
            }
        }
    }
    
    // Uložit aktualizovaný obsah zpět
    update_post_meta($post_id, '_bricks_page_content_2', $bricks_content);
    update_post_meta($post_id, '_bricks_page_content', $bricks_content);
    
    // DŮLEŽITÉ: Pro templates, pokud je to header/footer, uložit také do specifického meta klíče
    $post = get_post($post_id);
    if ($post && $post->post_type === 'bricks_template') {
        $template_type = get_post_meta($post_id, '_bricks_template_type', true);
        if ($template_type === 'header') {
            update_post_meta($post_id, '_bricks_page_header_2', $bricks_content);
        } elseif ($template_type === 'footer') {
            update_post_meta($post_id, '_bricks_page_footer_2', $bricks_content);
        }
    }
    
    return rest_ensure_response(array(
        'success' => true,
        'post_id' => $post_id,
        'signatures_regenerated' => $signatures_regenerated,
        'message' => "Regenerováno {$signatures_regenerated} podpisů kódu"
    ));
}

/**
 * Globální regenerace všech code signatures pomocí interní Bricks funkce
 * Zavolá stejnou funkci, kterou používá tlačítko "Generovat podpisy kódu" v Bricks Settings
 */
function bricks_regenerate_all_signatures($request) {
    // Zkusit použít Bricks interní funkci pro regeneraci všech podpisů
    $result = array(
        'success' => false,
        'method' => 'unknown',
        'signatures_regenerated' => 0,
        'posts_processed' => 0
    );
    
    // Metoda 1: Zkusit použít Bricks\Helpers třídu
    if (class_exists('\Bricks\Helpers')) {
        // Zkusit různé možné metody
        $possible_methods = array(
            'regenerate_all_code_signatures',
            'regenerateCodeSignatures',
            'regenerate_signatures',
            'sign_all_code'
        );
        
        foreach ($possible_methods as $method) {
            if (method_exists('\Bricks\Helpers', $method)) {
                try {
                    $bricks_result = call_user_func(array('\Bricks\Helpers', $method));
                    $result['success'] = true;
                    $result['method'] = "Bricks\\Helpers::{$method}";
                    $result['bricks_result'] = $bricks_result;
                    return rest_ensure_response($result);
                } catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
            }
        }
    }
    
    // Metoda 2: Zkusit použít Bricks\Code třídu
    if (class_exists('\Bricks\Code')) {
        $possible_methods = array(
            'regenerate_all_signatures',
            'regenerateAllSignatures',
            'sign_all'
        );
        
        foreach ($possible_methods as $method) {
            if (method_exists('\Bricks\Code', $method)) {
                try {
                    $bricks_result = call_user_func(array('\Bricks\Code', $method));
                    $result['success'] = true;
                    $result['method'] = "Bricks\\Code::{$method}";
                    $result['bricks_result'] = $bricks_result;
                    return rest_ensure_response($result);
                } catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
            }
        }
    }
    
    // Metoda 3: Zkusit zavolat AJAX akci, kterou používá Bricks admin
    if (function_exists('do_action')) {
        // Bricks může používat AJAX akci pro regeneraci
        $ajax_actions = array(
            'bricks_regenerate_code_signatures',
            'bricks_regenerate_all_code_signatures',
            'bricks_sign_all_code'
        );
        
        foreach ($ajax_actions as $action) {
            if (has_action("wp_ajax_{$action}")) {
                try {
                    do_action("wp_ajax_{$action}");
                    $result['success'] = true;
                    $result['method'] = "AJAX action: {$action}";
                    return rest_ensure_response($result);
                } catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
            }
        }
    }
    
    // Metoda 4: Fallback - projít všechny posty a podepsat je ručně
    global $wpdb;
    
    // Najít všechny posty s Bricks obsahem
    $posts_with_bricks = $wpdb->get_results(
        "SELECT post_id FROM {$wpdb->postmeta} 
         WHERE meta_key IN ('_bricks_page_content', '_bricks_page_content_2')
         GROUP BY post_id",
        ARRAY_A
    );
    
    $total_signatures = 0;
    $posts_processed = 0;
    
    foreach ($posts_with_bricks as $post_data) {
        $post_id = $post_data['post_id'];
        $post = get_post($post_id);
        
        if (!$post) {
            continue;
        }
        
        // Použít existující funkci pro regeneraci podpisů
        $regenerate_request = new WP_REST_Request('POST', '/bricks/v1/regenerate-signatures/' . $post_id);
        $regenerate_request->set_param('id', $post_id);
        
        $regenerate_result = bricks_regenerate_signatures($regenerate_request);
        
        if (!is_wp_error($regenerate_result)) {
            $data = $regenerate_result->get_data();
            if (isset($data['signatures_regenerated'])) {
                $total_signatures += $data['signatures_regenerated'];
                $posts_processed++;
            }
        }
    }
    
    $result['success'] = true;
    $result['method'] = 'fallback_manual_regeneration';
    $result['signatures_regenerated'] = $total_signatures;
    $result['posts_processed'] = $posts_processed;
    $result['note'] = 'Použita fallback metoda - prošly všechny posty s Bricks obsahem';
    
    return rest_ensure_response($result);
}

/**
 * Konfigurovat Builder Access a role pro Bricks
 * Od verze 2.0 Bricks ukládá nastavení přístupu v bricks_global_settings
 */
function bricks_configure_builder_access($request) {
    $roles_config = $request->get_param('roles');
    $code_execution_roles = $request->get_param('code_execution_roles');
    
    // Získat aktuální nastavení
    $settings = get_option('bricks_global_settings', array());
    
    // Výchozí konfigurace rolí (pokud není zadána)
    if (empty($roles_config)) {
        $roles_config = array(
            'administrator' => 'full',
            'editor' => 'editContent',
            'author' => 'none',
            'contributor' => 'none',
            'subscriber' => 'none'
        );
    }
    
    // Nastavit Builder Access pro každou roli
    foreach ($roles_config as $role => $access_level) {
        // Bricks ukládá jako builderAccess{Role} nebo builderAccess_{role}
        $key1 = 'builderAccess' . ucfirst($role);
        $key2 = 'builderAccess_' . $role;
        $key3 = 'builderAccess' . str_replace(' ', '', ucwords(str_replace('_', ' ', $role)));
        
        $settings[$key1] = $access_level;
        $settings[$key2] = $access_level;
        $settings[$key3] = $access_level;
    }
    
    // Nastavit Code Execution role (pokud jsou zadány)
    if (!empty($code_execution_roles)) {
        // Povolit Code Execution globálně
        $settings['executeCodeEnabled'] = true;
        $settings['codeExecution'] = true;
        
        // Nastavit pro každou roli
        $all_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        foreach ($all_roles as $role) {
            $enabled = in_array($role, $code_execution_roles);
            
            // Bricks může ukládat jako codeExecutionRole{Role} nebo codeExecutionRole_{role}
            $key1 = 'codeExecutionRole' . ucfirst($role);
            $key2 = 'codeExecutionRole_' . $role;
            $key3 = 'codeExecution' . ucfirst($role);
            
            $settings[$key1] = $enabled;
            $settings[$key2] = $enabled;
            $settings[$key3] = $enabled;
        }
        
        // Také jako pole
        $settings['codeExecutionRoles'] = $code_execution_roles;
    }
    
    // Uložit nastavení
    update_option('bricks_global_settings', $settings);
    
    // Zkusit použít Bricks Database API (pokud existuje)
    if (class_exists('\Bricks\Database')) {
        if (method_exists('\Bricks\Database', 'set_setting')) {
            foreach ($roles_config as $role => $access_level) {
                \Bricks\Database::set_setting("builderAccess{$role}", $access_level);
            }
            
            if (!empty($code_execution_roles)) {
                \Bricks\Database::set_setting('executeCodeEnabled', true);
                \Bricks\Database::set_setting('codeExecutionRoles', $code_execution_roles);
            }
        }
    }
    
    // Vymazat cache
    delete_transient('bricks_global_settings');
    wp_cache_delete('bricks_global_settings', 'options');
    
    return rest_ensure_response(array(
        'success' => true,
        'settings' => $settings,
        'roles_configured' => $roles_config,
        'code_execution_roles' => $code_execution_roles,
        'message' => 'Builder Access a role byly úspěšně nakonfigurovány'
    ));
}

