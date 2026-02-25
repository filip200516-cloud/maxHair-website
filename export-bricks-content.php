<?php
/**
 * Export Bricks Content to JSON
 * Upload this file to your WordPress root and access via browser
 */

// Load WordPress
require_once('wp-load.php');

// Check if user is logged in and has permission
if (!is_user_logged_in() || !current_user_can('edit_posts')) {
    die('Unauthorized');
}

header('Content-Type: application/json');

global $wpdb;
$table_prefix = $wpdb->prefix;

// Get all pages with Bricks content
$query = $wpdb->prepare("
    SELECT pm.post_id, p.post_title, p.post_name, pm.meta_value 
    FROM {$table_prefix}postmeta pm 
    INNER JOIN {$table_prefix}posts p ON pm.post_id = p.ID 
    WHERE pm.meta_key = %s 
    AND p.post_type = 'page' 
    AND p.post_status != 'trash'
    ORDER BY p.post_title
", '_bricks_page_content');

$results = $wpdb->get_results($query);

$pages = [];
foreach ($results as $row) {
    $content = maybe_unserialize($row->meta_value);
    if (is_string($content)) {
        $content = json_decode($content, true);
    }
    
    $pages[] = [
        'id' => $row->post_id,
        'title' => $row->post_title,
        'slug' => $row->post_name,
        'content' => $content
    ];
}

// Get templates
$templateQuery = $wpdb->prepare("
    SELECT pm.post_id, p.post_title, pm.meta_value, pm2.meta_value as template_type
    FROM {$table_prefix}postmeta pm 
    INNER JOIN {$table_prefix}posts p ON pm.post_id = p.ID 
    LEFT JOIN {$table_prefix}postmeta pm2 ON pm.post_id = pm2.post_id AND pm2.meta_key = '_bricks_template_type'
    WHERE pm.meta_key = %s 
    AND p.post_type = 'bricks_template'
    AND p.post_status != 'trash'
", '_bricks_page_content');

$templateResults = $wpdb->get_results($templateQuery);

$templates = [];
foreach ($templateResults as $row) {
    $content = maybe_unserialize($row->meta_value);
    if (is_string($content)) {
        $content = json_decode($content, true);
    }
    
    $type = maybe_unserialize($row->template_type);
    if (is_string($type)) {
        $type = json_decode($type, true);
    }
    
    $templates[] = [
        'id' => $row->post_id,
        'title' => $row->post_title,
        'type' => $type,
        'content' => $content
    ];
}

echo json_encode([
    'pages' => $pages,
    'templates' => $templates,
    'count' => [
        'pages' => count($pages),
        'templates' => count($templates)
    ]
], JSON_PRETTY_PRINT);
