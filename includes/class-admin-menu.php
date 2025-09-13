<?php
if (!defined('ABSPATH')) exit;

class IUT_Admin_Menu {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_filter('attachment_fields_to_edit', [$this, 'add_usage_button'], 10, 2);
        add_action('wp_ajax_image_usage_load_images', [$this, 'ajax_load_images']);

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

        // Untermenü für die Bilder-Übersichten
         add_submenu_page(
        'image-usage-tracker',
        'Bilder-Übersicht',
        'Bilder-Übersicht',
        'manage_options',
        'image-usage-tracker-overview',
        [$this, 'render_images_overview']
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

    /**
 * Übersicht aller Bilder und deren Verwendungen
 */
    public function render_images_overview() {
        
        $search   = isset($_GET['image_search']) ? sanitize_text_field($_GET['image_search']) : '';
        $filter   = isset($_GET['image_filter']) ? sanitize_text_field($_GET['image_filter']) : 'all';

        echo '<div class="wrap"><h1>Bilder-Übersicht</h1>';

        // 🔹 Such- und Filterformular
        echo '<form method="get" style="margin-bottom:20px;">';
        echo '<input type="hidden" name="page" value="image-usage-tracker-overview" />';
        echo '<input type="text" name="image_search" placeholder="Dateiname suchen..." value="' . esc_attr($search) . '" style="padding:5px; width:250px;">';
        echo '<select name="image_filter" style="padding:5px; margin-left:10px;">';
        echo '<option value="all" ' . selected($filter, 'all', false) . '>Alle Bilder</option>';
        echo '<option value="used" ' . selected($filter, 'used', false) . '>Nur verwendete</option>';
        echo '<option value="unused" ' . selected($filter, 'unused', false) . '>Nur ungenutzte</option>';
        echo '</select>';
        echo '<input type="submit" class="button button-primary" value="Filtern">';
        echo '</form>';


        echo '<div class="wrap"><h1>Bilder-Übersicht</h1>';
        echo '<table class="widefat fixed striped">';
        echo '<thead>
                <tr>
                    <th>Bild</th>
                    <th>Dateiname</th>
                    <th>Verwendungen</th>
                    <th>Aktion</th>
                </tr>
            </thead><tbody>';

        // 🔹 Alle Bilder holen
        $args = [
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
        ];

        // Suche nach Dateinamen
        if (!empty($search)) {
            $args['s'] = $search;
        }

        $images = get_posts($args);

        if ($images) {
            foreach ($images as $image) {
                $image_url = wp_get_attachment_url($image->ID);
                $filename  = basename($image_url);

                // Zählen, wie oft das Bild verwendet wird
                $query = new WP_Query([
                    'post_type'      => ['post', 'page'],
                    'post_status'    => 'any',
                    'posts_per_page' => -1,
                    's'              => $filename,
                ]);

                $count = $query->found_posts;

                // 🔹 Filterlogik: Nur verwenden, wenn passend
                if ($filter === 'used' && $count === 0) {
                    continue;
                }
                if ($filter === 'unused' && $count > 0) {
                    continue;
                }

                echo '<tr>';
                echo '<td><img src="' . esc_url($image_url) . '" width="60" style="border-radius:6px"></td>';
                echo '<td>' . esc_html($filename) . '</td>';
                echo '<td>' . intval($count) . '</td>';
                echo '<td>
                        <a href="' . admin_url('admin.php?page=image-usage-tracker&image_id=' . $image->ID) . '" class="button button-primary">Verwendung anzeigen</a>
                    </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4">Keine Bilder gefunden.</td></tr>';
        }

        echo '</tbody></table></div>';
    }

    // AJAX-Handler für das Laden von Bildern
    public function ajax_load_images() {
    // Sicherheitscheck
    check_ajax_referer('image_usage_nonce', 'security');

    $paged   = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $search  = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $filter  = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : 'all';
    $per_page = 20;

    $args = [
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
    ];

    // Suche nach Dateinamen
    if (!empty($search)) {
        $args['s'] = $search;
    }

    $images = get_posts($args);
    $data = [];

    foreach ($images as $image) {
        $image_url = wp_get_attachment_url($image->ID);
        $filename  = basename($image_url);

        // Verwendungszähler
       global $wpdb;
        $table_name = $wpdb->prefix . 'image_usage_cache';
        $count = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT usage_count FROM $table_name WHERE image_id = %d", $image->ID)
        );

        // Filterlogik
        if ($filter === 'used' && $count === 0) {
            continue;
        }
        if ($filter === 'unused' && $count > 0) {
            continue;
        }

        $data[] = [
            'id'       => $image->ID,
            'url'      => $image_url,
            'filename' => $filename,
            'count'    => $count,
            'link'     => admin_url('admin.php?page=image-usage-tracker&image_id=' . $image->ID),
        ];
    }

    wp_send_json([
        'success' => true,
        'images'  => $data,
        'paged'   => $paged,
        'has_more' => count($images) === $per_page,
    ]);
}

}
