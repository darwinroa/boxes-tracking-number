<?php
/**
 * Plugin Name: Boxes Tracker
 * Description: Consulta el estado de tracking de paquetes mediante una API externa.
 * Version: 1.0
 * Author: Darwin Roa
 */

if (!defined('ABSPATH')) {
    exit; // Evitar acceso directo
}

class BoxesTracker {
    
    public static function init() {
        // Puedes enganchar acciones aquí si lo necesitas luego
    }

    /**
     * Realiza una consulta a la API con un número de tracking fijo
     */
    public static function consultar_tracking_estatico() {
        $tracking_number = "1Z9999999999999999";
        $url = "https://boxes-tracker-api.diegoesolorzano.workers.dev/track";

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'tracking_number' => $tracking_number,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return [
                'error' => true,
                'message' => $response->get_error_message(),
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
    }

    /**
     * Shortcode: [boxes_tracker]
     */
    public static function shortcode_boxes_tracker($atts) {
        wp_enqueue_style('boxes-tracker-css');
        wp_enqueue_script('boxes-tracker-js');
        wp_enqueue_script('boxes-tracker-js', plugin_dir_url(__FILE__) . 'assets/js/boxes-tracker.js', [], false, true);
        wp_localize_script('boxes-tracker-js', 'bt_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('boxes-tracker-nonce'),
        ]);

        $form_html = '
            <form id="bt__form">
                <label for="bt_tracking_number">Número de seguimiento:</label>
                <input type="text" id="bt_tracking_number" name="tracking_number" required>
                <button type="submit">Consultar</button>
            </form>
            <div id="bt__result"></div>
        ';

        return $form_html;
    }

    /**
     * Consulta la API con tracking dinámico
     */
    public static function consultar_tracking_number($tracking_number) {
        $url = "https://boxes-tracker-api.diegoesolorzano.workers.dev/track";

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'tracking_number' => $tracking_number,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return [
                'error' => true,
                'message' => $response->get_error_message(),
            ];
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}

// Inicializar el plugin
BoxesTracker::init();

// Registrar el shortcode boxes_tracker
add_shortcode('boxes_tracker', ['BoxesTracker', 'shortcode_boxes_tracker']);

// Registrar scripts y estilos
add_action('wp_enqueue_scripts', function () {
    wp_register_style('boxes-tracker-css', plugin_dir_url(__FILE__) . 'assets/css/boxes-tracker.css');
});

/**
 * Handle AJAX 
 */
add_action('wp_ajax_boxes_tracker_lookup', 'boxes_tracker_ajax_lookup');
add_action('wp_ajax_nopriv_boxes_tracker_lookup', 'boxes_tracker_ajax_lookup');

function boxes_tracker_ajax_lookup() {
    check_ajax_referer('boxes-tracker-nonce');

    $tracking_number = sanitize_text_field($_POST['tracking_number'] ?? '');

    if (empty($tracking_number)) {
        wp_send_json_error('Número de seguimiento requerido.');
    }

    $data = BoxesTracker::consultar_tracking_number($tracking_number);

    if (isset($data['error'])) {
        echo '<p>Error: ' . esc_html($data['message']) . '</p>';
        wp_die();
    }

    // Cargar plantilla
    $tracking_number = $tracking_number; // disponible para la plantilla
    include plugin_dir_path(__FILE__) . 'templates/tracking-result.php';
    wp_die();
}
