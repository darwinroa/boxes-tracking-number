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
     * Shortcode: [boxes_tracker tracking="1Z9999999999999999"]
     */
    public static function shortcode_boxes_tracker($atts) {
        $tracking_number = isset($_GET['boxes_tracking_number']) ? sanitize_text_field($_GET['boxes_tracking_number']) : '';

        $form_html = '
            <form method="get">
                <label for="boxes_tracking_number">Número de seguimiento:</label>
                <input type="text" id="boxes_tracking_number" name="boxes_tracking_number" value="' . esc_attr($tracking_number) . '" required />
                <button type="submit">Consultar</button>
            </form>
        ';

        if (empty($tracking_number)) {
            return $form_html;
        }

        $data = self::consultar_tracking_estatico_valor($tracking_number);

        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/tracking-result.php';
        $result_html = ob_get_clean();


        wp_enqueue_style('boxes-tracker-css');

        return $form_html . $result_html;
    }

    /**
     * Consulta la API con tracking dinámico
     */
    public static function consultar_tracking_estatico_valor($tracking_number) {
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

// Registra el documento de estilos
add_action('wp_enqueue_scripts', function () {
    wp_register_style('boxes-tracker-css', plugin_dir_url(__FILE__) . 'assets/css/boxes-tracker.css');
});

