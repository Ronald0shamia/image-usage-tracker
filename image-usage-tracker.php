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
