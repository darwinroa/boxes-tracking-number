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

        $result_html = '<div class="bt__tracking-wrapper">';

        // Botón de nueva búsqueda
        $result_html .= '<div class="bt__header">
            <h2>Tracking</h2>
            <a href="' . esc_url(remove_query_arg('boxes_tracking_number')) . '" class="bt__button">NUEVA BÚSQUEDA</a>
        </div>';

        // Información general
        $result_html .= '<div class="bt__summary">';
        $result_html .= '<p><strong>Código:</strong> (' . esc_html(strtoupper($data['carrier'])) . ') ' . esc_html($data['tracking_number']) . '</p>';

        if (!empty($data['estimated_delivery'])) {
            $result_html .= '<p><strong>La entrega se ha realizado el:</strong> ' . esc_html(date('d-m-Y H:i', strtotime($data['estimated_delivery']))) . '</p>';
        }

        if (!empty($data['signed_by'])) {
            $result_html .= '<p><strong>Firmado por:</strong> ' . esc_html($data['signed_by']) . '</p>';
        }

        $result_html .= '</div>';

        // Tabla de eventos
        if (!empty($data['events']) && is_array($data['events'])) {
            $result_html .= '<h3>Estado del pedido</h3>';
            $result_html .= '<table class="bt__events-table">
                <thead><tr><th>FECHA</th><th>DESCRIPCIÓN</th></tr></thead><tbody>';

            foreach ($data['events'] as $event) {
                $result_html .= '<tr>
                    <td>' . esc_html($event['timestamp']) . '</td>
                    <td>' . esc_html($event['status']) . (!empty($event['location']) ? ' - ' . esc_html($event['location']) : '') . '</td>
                </tr>';
            }

            $result_html .= '</tbody></table>';
        }

        $result_html .= '</div>';


        $styles = '
          <style>
              .bt__tracking-wrapper { font-family: Arial, sans-serif; max-width: 700px; margin: 20px auto; }
              .bt__header h2 { font-size: 2.5em; margin-bottom: 0; border-bottom: 3px solid red; display: inline-block; padding-bottom: 0.2em; }
              .bt__button { display: inline-block; background: red; color: white; padding: 0.5em 1em; margin-top: 10px; text-decoration: none; font-weight: bold; }
              .bt__summary { margin-top: 20px; }
              .bt__summary p { font-size: 1.1em; color: #333; }
              .bt__events-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
              .bt__events-table th, .bt__events-table td { border-bottom: 1px solid #ddd; padding: 10px; text-align: left; }
              .bt__events-table th { text-transform: uppercase; color: #666; font-size: 0.9em; }
              .bt__events-table td { color: #222; font-size: 1em; }
          </style>
          ';

        return $styles . $form_html . $result_html;
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
