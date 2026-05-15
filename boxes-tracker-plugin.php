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

        // Settings page
        add_action('admin_menu', [self::class, 'add_admin_menu']);
        add_action('admin_init', [self::class, 'register_settings']);
    }

    public static function add_admin_menu() {
        add_options_page(
            'Boxes Tracker Settings',
            'Boxes Tracker',
            'manage_options',
            'boxes-tracker',
            [self::class, 'settings_page_html']
        );
    }

    public static function register_settings() {
        register_setting('boxes_tracker_settings_group', 'boxes_tracker_api_url');

        add_settings_section(
            'boxes_tracker_main_section',
            'Configuración de la API',
            null,
            'boxes-tracker'
        );

        add_settings_field(
            'boxes_tracker_api_url_field',
            'URL Base de la API',
            [self::class, 'api_url_field_html'],
            'boxes-tracker',
            'boxes_tracker_main_section'
        );
    }

    public static function api_url_field_html() {
        $api_url = get_option('boxes_tracker_api_url', '');
        echo '<input type="url" name="boxes_tracker_api_url" value="' . esc_attr($api_url) . '" style="width: 100%; max-width: 600px;" placeholder="https://api.ejemplo.com/endpoint">';
        echo '<p class="description">Introduce la URL base de la API sin parámetros.</p>';
    }

    public static function settings_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Ajustes de Boxes Tracker</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('boxes_tracker_settings_group');
                do_settings_sections('boxes-tracker');
                submit_button();
                ?>
            </form>
        </div>
        <?php
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

    public static function detect_courier($tracking_number) {
        $tracking_number = strtoupper(trim($tracking_number));
        
        if (preg_match('/\b(1Z[0-9A-Z]{16})\b/', $tracking_number)) {
            return ['UPS'];
        }
        
        $couriers = [];
        
        if (preg_match('/^\d{10}$/', $tracking_number)) {
            $couriers[] = 'DHL';
        }
        
        if (preg_match('/^\d{11}$/', $tracking_number)) {
            $couriers[] = 'COORDINADORA';
        }
        
        if (preg_match('/^\d{12}$/', $tracking_number) || preg_match('/^\d{15}$/', $tracking_number) || preg_match('/^\d{20}$/', $tracking_number)) {
            $couriers[] = 'FEDEX';
        }

        if (empty($couriers)) {
            return ['UPS', 'DHL', 'FEDEX', 'COORDINADORA'];
        }
        
        return $couriers;
    }

    public static function consultar_tracking_number($tracking_number) {
        $api_url = get_option('boxes_tracker_api_url');
        if (empty($api_url)) {
            return ['error' => true, 'message' => 'La URL de la API no está configurada.'];
        }

        $couriers = self::detect_courier($tracking_number);
        $last_error = '';

        foreach ($couriers as $courier) {
            $request_url = add_query_arg([
                'courier' => $courier,
                'tracking' => $tracking_number
            ], $api_url);

            $response = wp_remote_get($request_url, [
                'timeout' => 15,
            ]);

            if (is_wp_error($response)) {
                $last_error = $response->get_error_message();
                continue;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['ok']) && $data['ok'] === true) {
                return $data;
            } else {
                $last_error = isset($data['description']) && !empty($data['description']) ? $data['description'] : 'Guía no encontrada.';
            }
        }

        return ['error' => true, 'message' => $last_error ?: 'No se encontró información para este número de guía.'];
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
