<?php

/**
 * AJAX Handler Trait
 *
 * Provides standardized AJAX request handling with security checks
 * and JSON response utilities.
 *
 * @package     WPPluginBoilerplate\Core\Traits
 * @since       1.1.0
 * @author      Mirko Schubert
 */

namespace WPPluginBoilerplate\Core\Traits;

trait AjaxHandler
{
    /**
     * Verify AJAX request with nonce and capability check
     *
     * @param string $action The action name for nonce verification
     * @param string $capability The capability to check (default: 'manage_options')
     * @return void Exits with JSON error if verification fails
     * @since 1.1.0
     */
    protected function verify_ajax_request(string $action, string $capability = 'manage_options'): void
    {
        // Verify nonce
        check_ajax_referer($action . '_nonce', 'nonce');

        // Check user capability
        if (!current_user_can($capability)) {
            $this->send_json_error(__('Insufficient permissions.', 'wp-plugin-boilerplate'));
        }
    }

    /**
     * Send JSON success response
     *
     * @param mixed $data Optional data to send with the response
     * @return void Exits after sending JSON
     * @since 1.1.0
     */
    protected function send_json_success($data = null): void
    {
        wp_send_json_success($data);
    }

    /**
     * Send JSON error response
     *
     * @param string $message Error message to send
     * @param mixed $data Optional additional data
     * @return void Exits after sending JSON
     * @since 1.1.0
     */
    protected function send_json_error(string $message, $data = null): void
    {
        $response = ['message' => $message];

        if ($data !== null) {
            $response['data'] = $data;
        }

        wp_send_json_error($response);
    }

    /**
     * Sanitize and validate AJAX input
     *
     * @param string $key The $_POST key to retrieve
     * @param string $type The type of sanitization (text, email, url, int, bool, array)
     * @param bool $required Whether the field is required
     * @return mixed Sanitized value or null if not set
     * @since 1.1.0
     */
    protected function sanitize_ajax_input(string $key, string $type = 'text', bool $required = false)
    {
        if (!isset($_POST[$key])) {
            if ($required) {
                $this->send_json_error(
                    sprintf(__('Required field "%s" is missing.', 'wp-plugin-boilerplate'), $key)
                );
            }
            return null;
        }

        $value = $_POST[$key];

        switch ($type) {
            case 'email':
                return sanitize_email($value);

            case 'url':
                return esc_url_raw($value);

            case 'int':
                return intval($value);

            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case 'array':
                if (!is_array($value)) {
                    return [];
                }
                return array_map('sanitize_text_field', $value);

            case 'text':
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Log AJAX errors for debugging
     *
     * @param string $message Error message to log
     * @param array $context Optional context data
     * @return void
     * @since 1.1.0
     */
    protected function log_ajax_error(string $message, array $context = []): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = sprintf(
                '[WP Plugin Boilerplate AJAX] %s | Context: %s',
                $message,
                !empty($context) ? json_encode($context) : 'none'
            );
            error_log($log_message);
        }
    }
}
