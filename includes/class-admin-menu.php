<?php
if (!defined('ABSPATH')) exit;

class IUT_Admin_Menu {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_filter('attachment_fields_to_edit', [$this, 'add_usage_button'], 10, 2);
    }

    /**
     * Admin-Menü registrieren
     */
    public function register_menu() {
        add_menu_page(
            'Bild-Verwendung',
            'Bild-Verwendung',
            'manage_options',
            'image-usage-tracker',
            [$this, 'usage_overview_page'],
            'dashicons-format-image'
        );
    }

    /**
     * Übersicht-Seite
     */
    public function usage_overview_page() {
        echo '<div class="wrap"><h1>Bild-Verwendung</h1>';
        echo '<p>Wähle ein Bild in der Mediathek, um seine Verwendung anzuzeigen.</p></div>';
    }

    /**
     * Button "Verwendung anzeigen" in der Mediathek
     */
    public function add_usage_button($form_fields, $post) {
        $url = admin_url('admin.php?page=image-usage-tracker&image_id=' . $post->ID);
        $form_fields['image_usage_tracker'] = [
            'label' => __('Verwendung', 'image-usage-tracker'),
            'input' => 'html',
            'html'  => '<a href="' . esc_url($url) . '" class="button button-primary">Verwendung anzeigen</a>',
        ];
        return $form_fields;
    }
}
