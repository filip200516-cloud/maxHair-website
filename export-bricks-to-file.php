<?php
// Načíst WordPress
require_once('wp-load.php');

global $wpdb;
$table_prefix = $wpdb->prefix;

// Získat všechny stránky s kompletními daty
$pages = $wpdb->get_results("
    SELECT 
        p.ID,
        p.post_title,
        p.post_name as slug,
        p.post_content,
        p.post_excerpt,
        p.post_date,
        p.post_modified,
        p.post_status,
        p.post_type,
        p.post_parent,
        p.menu_order
    FROM {$table_prefix}posts p
    WHERE p.post_type = 'page' 
    AND p.post_status != 'trash'
    ORDER BY p.post_title
");

$result = [
    'pages' => [],
    'export_date' => date('Y-m-d H:i:s')
];

foreach ($pages as $page) {
    // Získat všechny meta fields pro tuto stránku
    $allMeta = $wpdb->get_results($wpdb->prepare("
        SELECT meta_key, meta_value 
        FROM {$table_prefix}postmeta 
        WHERE post_id = %d
    ", $page->ID));
    
    $meta = [];
    foreach ($allMeta as $metaRow) {
        $value = maybe_unserialize($metaRow->meta_value);
        // Zkusit dekódovat JSON
        if (is_string($value)) {
            $decoded = @json_decode($value, true);
            $meta[$metaRow->meta_key] = ($decoded !== null && json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
        } else {
            $meta[$metaRow->meta_key] = $value;
        }
    }
    
    // Získat Bricks obsah
    $bricksContent = isset($meta['_bricks_page_content']) ? $meta['_bricks_page_content'] : null;
    
    $result['pages'][] = [
        'id' => (int)$page->ID,
        'title' => $page->post_title,
        'slug' => $page->slug,
        'content' => $page->post_content,
        'excerpt' => $page->post_excerpt,
        'date' => $page->post_date,
        'modified' => $page->post_modified,
        'status' => $page->post_status,
        'parent' => (int)$page->post_parent,
        'menu_order' => (int)$page->menu_order,
        'bricks_content' => $bricksContent,
        'meta' => $meta
    ];
}

// Získat templates
$templates = $wpdb->get_results("
    SELECT 
        p.ID,
        p.post_title,
        p.post_name as slug,
        pm.meta_value as bricks_content,
        pm2.meta_value as template_type
    FROM {$table_prefix}posts p
    LEFT JOIN {$table_prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_bricks_page_content'
    LEFT JOIN {$table_prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_bricks_template_type'
    WHERE p.post_type = 'bricks_template'
    AND p.post_status != 'trash'
");

foreach ($templates as $template) {
    $bricksContent = maybe_unserialize($template->bricks_content);
    if (is_string($bricksContent)) {
        $decoded = @json_decode($bricksContent, true);
        $bricksContent = ($decoded !== null && json_last_error() === JSON_ERROR_NONE) ? $decoded : $bricksContent;
    }
    
    $templateType = maybe_unserialize($template->template_type);
    if (is_string($templateType)) {
        $decoded = @json_decode($templateType, true);
        $templateType = ($decoded !== null && json_last_error() === JSON_ERROR_NONE) ? $decoded : $templateType;
    }
    
    $result['templates'][] = [
        'id' => (int)$template->ID,
        'title' => $template->post_title,
        'slug' => $template->slug,
        'type' => $templateType,
        'bricks_content' => $bricksContent
    ];
}

// Získat theme options
$result['theme_options'] = get_option('theme_mods_bricks');
$result['bricks_settings'] = get_option('bricks');

// Uložit do souboru
$exportFile = __DIR__ . '/bricks-export.json';
file_put_contents($exportFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "Export uložen do: " . $exportFile . "\n";
echo "Stránek: " . count($result['pages']) . "\n";
echo "Templates: " . count($result['templates']) . "\n";
?>