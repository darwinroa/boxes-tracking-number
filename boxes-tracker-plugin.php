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
        $atts = shortcode_atts([
            'tracking' => '1Z9999999999999999', // valor por defecto
        ], $atts);

        $data = self::consultar_tracking_estatico_valor($atts['tracking']);

        // Mostrar resultado como bloque <pre> para depuración
        return '<pre>' . esc_html(print_r($data, true)) . '</pre>';
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
