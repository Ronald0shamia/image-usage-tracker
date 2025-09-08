<?php
/**
 * Plugin Name: Image Usage Tracker
 * Description: Zeigt an, in welchen Beiträgen oder Seiten ein Bild verwendet wird.
 * Version: 1.1
 * Author: Dein Name
 */

if (!defined('ABSPATH')) {
    exit; // Kein direkter Zugriff erlaubt
}

// Dateien einbinden
require_once plugin_dir_path(__FILE__) . 'includes/class-admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-image-usage.php';

// Plugin starten
function iut_init_plugin() {
    new IUT_Admin_Menu();
    new IUT_Image_Usage();
}
add_action('plugins_loaded', 'iut_init_plugin');


// Skripte einbinden
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_image-usage-tracker' && $hook !== 'image-usage-tracker_page_image-usage-tracker-overview') {
        return;
    }

    wp_enqueue_script(
        'image-usage-admin',
        plugin_dir_url(__FILE__) . 'assets/js/image-usage-admin.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('image-usage-admin', 'imageUsage', [
        'nonce' => wp_create_nonce('image_usage_nonce')
    ]);
});

