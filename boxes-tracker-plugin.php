<?php
/**
 * Plugin Name: Boxes Tracker
 * Plugin URI: https://github.com/darwinroa/boxes-tracking-number
 * Description: Consulta el estado de tracking de paquetes mediante una API externa.
 * Version: 1.0.1
 * Author: Darwin Roa
 * Author URI: https://github.com/darwinroa
 */

if (!defined('ABSPATH')) {
    exit;
}

class BoxesTracker {
    public static function init() {
        // Shortcode
        add_shortcode('boxes_tracker', [self::class, 'shortcode_boxes_tracker']);

        // Scripts y estilos
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_assets']);

        // AJAX
        add_action('wp_ajax_boxes_tracker_lookup', [self::class, 'handle_ajax']);
        add_action('wp_ajax_nopriv_boxes_tracker_lookup', [self::class, 'handle_ajax']);
    }

    public static function enqueue_assets() {
        if (is_singular() && has_shortcode(get_post()->post_content, 'boxes_tracker')) {
            wp_enqueue_style(
                'boxes-tracker-css',
                plugin_dir_url(__FILE__) . 'assets/css/boxes-tracker.css'
            );
            wp_enqueue_script(
                'boxes-tracker-js',
                plugin_dir_url(__FILE__) . 'assets/js/boxes-tracker.js',
                [],
                false,
                true
            );
            wp_localize_script('boxes-tracker-js', 'bt_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('boxes-tracker-nonce'),
            ]);
        }
    }

    public static function shortcode_boxes_tracker() {
        $angleRight = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>';
        return "
            <div class='bt__boxes_tracker'>
                <div class='bt__header'>
                    <h1 class='bt__main_title bt__text_underground'>Tracking</h1>
                    <form id='bt__form' class='bt__form'>
                        <label for='bt_tracking_number'>Número de seguimiento:</label>
                        <div class='bt__input_container'>
                            <input type='text' id='bt_tracking_number' name='tracking_number' class='bt_tracking_number' placeholder='Número de Tracking' required>
                            <button type='submit' class='bt__button'>$angleRight</button>
                        </div>
                    </form>
                </div>
                <div id='bt__result'></div>
            </div>
        ";
    }

    public static function consultar_tracking_number($tracking_number) {
        $url = "https://boxes-tracker-api.diegoesolorzano.workers.dev/track";

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode(['tracking_number' => $tracking_number]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return ['error' => true, 'message' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public static function handle_ajax() {
        check_ajax_referer('boxes-tracker-nonce');

        $tracking_number = sanitize_text_field($_POST['tracking_number'] ?? '');

        if (empty($tracking_number)) {
            wp_send_json_error('Número de seguimiento requerido.');
        }

        $data = self::consultar_tracking_number($tracking_number);

        if (isset($data['error'])) {
            echo '<p>Error: ' . esc_html($data['message']) . '</p>';
            wp_die();
        }

        include plugin_dir_path(__FILE__) . 'templates/tracking-result.php';
        wp_die();
    }
}

BoxesTracker::init();
