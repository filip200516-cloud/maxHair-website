<?php
// Načíst WordPress
require_once('wp-load.php');

if (!is_user_logged_in() || !current_user_can('edit_posts')) {
    die('Unauthorized');
}

header('Content-Type: application/json');

global $wpdb;
$table_prefix = $wpdb->prefix;

// Získat všechny stránky s Bricks obsahem
$pages = $wpdb->get_results($wpdb->prepare("
    SELECT 
        p.ID as post_id,
        p.post_title,
        p.post_name as slug,
        p.post_content,
        p.post_excerpt,
        p.post_date,
        p.post_modified,
        p.post_status,
        p.post_type,
        pm.meta_value as bricks_content
    FROM {$table_prefix}posts p
    LEFT JOIN {$table_prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_bricks_page_content'
    WHERE p.post_type = 'page' 
    AND p.post_status != 'trash'
    ORDER BY p.post_title
"));

// Získat všechny templates s Bricks obsahem
$templates = $wpdb->get_results($wpdb->prepare("
    SELECT 
        p.ID as post_id,
        p.post_title,
        p.post_name as slug,
        pm.meta_value as bricks_content,
        pm2.meta_value as template_type
    FROM {$table_prefix}posts p
    LEFT JOIN {$table_prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_bricks_page_content'
    LEFT JOIN {$table_prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_bricks_template_type'
    WHERE p.post_type = 'bricks_template'
    AND p.post_status != 'trash'
"));

// Získat všechny media soubory používané v Bricks
$mediaQuery = $wpdb->prepare("
    SELECT DISTINCT
        p.ID,
        p.post_title,
        p.guid as url,
        pm.meta_value as file_path
    FROM {$table_prefix}posts p
    INNER JOIN {$table_prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
    WHERE p.post_type = 'attachment'
    AND (p.post_mime_type LIKE 'image/%' OR p.post_mime_type LIKE 'video/%')
    ORDER BY p.post_date DESC
    LIMIT 1000
");
$media = $wpdb->get_results($mediaQuery);

// Získat theme options a Bricks settings
$themeOptions = get_option('bricks');
$bricksSettings = get_option('bricks_settings');

$result = [
    'pages' => [],
    'templates' => [],
    'media' => [],
    'theme_options' => $themeOptions,
    'bricks_settings' => $bricksSettings,
    'export_date' => date('Y-m-d H:i:s')
];

foreach ($pages as $page) {
    $bricksContent = maybe_unserialize($page->bricks_content);
    if (is_string($bricksContent)) {
        $bricksContent = json_decode($bricksContent, true);
    }
    
    // Získat všechny meta fields
    $allMeta = $wpdb->get_results($wpdb->prepare("
        SELECT meta_key, meta_value 
        FROM {$table_prefix}postmeta 
        WHERE post_id = %d
    ", $page->post_id));
    
    $meta = [];
    foreach ($allMeta as $metaRow) {
        $value = maybe_unserialize($metaRow->meta_value);
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $meta[$metaRow->meta_key] = ($decoded !== null) ? $decoded : $value;
        } else {
            $meta[$metaRow->meta_key] = $value;
        }
    }
    
    $result['pages'][] = [
        'id' => $page->post_id,
        'title' => $page->post_title,
        'slug' => $page->slug,
        'content' => $page->post_content,
        'excerpt' => $page->post_excerpt,
        'date' => $page->post_date,
        'modified' => $page->post_modified,
        'status' => $page->post_status,
        'bricks_content' => $bricksContent,
        'meta' => $meta
    ];
}

foreach ($templates as $template) {
    $bricksContent = maybe_unserialize($template->bricks_content);
    if (is_string($bricksContent)) {
        $bricksContent = json_decode($bricksContent, true);
    }
    
    $templateType = maybe_unserialize($template->template_type);
    if (is_string($templateType)) {
        $templateType = json_decode($templateType, true);
    }
    
    $result['templates'][] = [
        'id' => $template->post_id,
        'title' => $template->post_title,
        'slug' => $template->slug,
        'type' => $templateType,
        'bricks_content' => $bricksContent
    ];
}

foreach ($media as $item) {
    $filePath = maybe_unserialize($item->file_path);
    $result['media'][] = [
        'id' => $item->ID,
        'title' => $item->post_title,
        'url' => $item->url,
        'file_path' => $filePath
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>