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
}

// Inicializar el plugin
BoxesTracker::init();
