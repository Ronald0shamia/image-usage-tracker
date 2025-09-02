<?php
if (!defined('ABSPATH')) exit;

class IUT_Image_Usage {

    public function __construct() {
        add_action('admin_init', [$this, 'show_image_usage']);
    }

    /**
     * Zeigt die Bildverwendung in einer eigenen Ansicht
     */
    public function show_image_usage() {
        if (isset($_GET['page']) && $_GET['page'] === 'image-usage-tracker' && isset($_GET['image_id'])) {
            $image_id = intval($_GET['image_id']);
            $image_url = wp_get_attachment_url($image_id);

            echo '<div class="wrap">';
            echo '<h1>Bildverwendung</h1>';
            echo '<p><strong>Datei:</strong> ' . esc_html(basename($image_url)) . '</p>';
            echo '<p><strong>Bild-URL:</strong> ' . esc_url($image_url) . '</p>';
            echo '<hr>';

            // Suche in allen Beiträgen und Seiten
            $args = [
                'post_type'      => ['post', 'page'],
                'post_status'    => 'any',
                'posts_per_page' => -1,
                's'              => basename($image_url)
            ];

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                echo '<h2>Gefunden in:</h2><ul>';
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<li><a href="' . get_edit_post_link() . '">' . get_the_title() . '</a></li>';
                }
                echo '</ul>';
            } else {
                echo '<p><em>Dieses Bild wird aktuell nirgends verwendet.</em></p>';
            }

            wp_reset_postdata();
            echo '</div>';
            exit;
        }
    }
}
