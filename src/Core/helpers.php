<?php

/**
 * Global Helper Functions
 *
 * This file contains global utility functions that can be used throughout
 * the plugin. These functions are optional convenience helpers and modules
 * should remain independent from them.
 *
 * @package     WPPluginBoilerplate\Core
 * @since       1.1.0
 * @author      Mirko Schubert
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('wpbp_format_bytes')) {
    /**
     * Convert bytes to human-readable file size format
     *
     * @param int $bytes The number of bytes
     * @param int $precision Decimal precision (default: 2)
     * @return string Formatted file size string (e.g., "2.5 MB")
     * @since 1.1.0
     *
     * @example wpbp_format_bytes(1024) // Returns "1 kB"
     * @example wpbp_format_bytes(1572864) // Returns "1.5 MB"
     */
    function wpbp_format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        // For small files (B or kB), don't show decimals
        if ($pow <= 1) {
            return round($bytes) . ' ' . $units[$pow];
        }

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('wpbp_mime_to_extension')) {
    /**
     * Convert MIME type to file extension
     *
     * @param string $mime The MIME type to convert
     * @return string|null File extension or null if not found
     * @since 1.1.0
     *
     * @example wpbp_mime_to_extension('application/pdf') // Returns "pdf"
     * @example wpbp_mime_to_extension('image/jpeg') // Returns "jpg"
     */
    function wpbp_mime_to_extension(string $mime): ?string
    {
        $mime_map = [
            // Documents
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/rtf' => 'rtf',

            // Images
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/bmp' => 'bmp',
            'image/x-icon' => 'ico',

            // Archives
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/x-7z-compressed' => '7z',
            'application/x-tar' => 'tar',
            'application/gzip' => 'gz',

            // Media
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
            'video/webm' => 'webm',

            // Code
            'text/html' => 'html',
            'text/css' => 'css',
            'text/javascript' => 'js',
            'application/json' => 'json',
            'application/xml' => 'xml',
            'application/x-php' => 'php',
        ];

        return $mime_map[$mime] ?? null;
    }
}

if (!function_exists('wpbp_get_plugin_option')) {
    /**
     * Get a plugin option value
     *
     * Helper function to retrieve plugin options with optional default value
     *
     * @param string $key The option key to retrieve
     * @param mixed $default Default value if option doesn't exist
     * @return mixed The option value or default
     * @since 1.1.0
     *
     * @example wpbp_get_plugin_option('my_setting', 'default_value')
     */
    function wpbp_get_plugin_option(string $key, $default = null)
    {
        $options = get_option('wp_plugin_boilerplate_options', []);

        if (isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }
}

if (!function_exists('wpbp_update_plugin_option')) {
    /**
     * Update a plugin option value
     *
     * Helper function to update plugin options
     *
     * @param string $key The option key to update
     * @param mixed $value The value to set
     * @return bool True on success, false on failure
     * @since 1.1.0
     *
     * @example wpbp_update_plugin_option('my_setting', 'new_value')
     */
    function wpbp_update_plugin_option(string $key, $value): bool
    {
        $options = get_option('wp_plugin_boilerplate_options', []);
        $options[$key] = $value;

        return update_option('wp_plugin_boilerplate_options', $options);
    }
}

if (!function_exists('wpbp_is_module_enabled')) {
    /**
     * Check if a module is enabled
     *
     * @param string $module_slug The module slug to check
     * @return bool True if module is enabled, false otherwise
     * @since 1.1.0
     *
     * @example wpbp_is_module_enabled('administration')
     */
    function wpbp_is_module_enabled(string $module_slug): bool
    {
        $modules = wpbp_get_plugin_option('modules', []);

        if (isset($modules[$module_slug]['enabled'])) {
            return (bool) $modules[$module_slug]['enabled'];
        }

        return false;
    }
}

if (!function_exists('wpbp_sanitize_slug')) {
    /**
     * Sanitize a string to be used as a slug
     *
     * Similar to sanitize_title but with additional checks
     *
     * @param string $string The string to sanitize
     * @return string Sanitized slug
     * @since 1.1.0
     *
     * @example wpbp_sanitize_slug('My Custom Post Type') // Returns "my-custom-post-type"
     */
    function wpbp_sanitize_slug(string $string): string
    {
        $string = sanitize_title($string);

        // Remove leading/trailing hyphens
        $string = trim($string, '-');

        // Ensure it's not empty
        if (empty($string)) {
            $string = 'item-' . uniqid();
        }

        return $string;
    }
}

if (!function_exists('wpbp_get_file_icon_class')) {
    /**
     * Get Dashicons class for file type
     *
     * Returns appropriate Dashicons class based on file extension or MIME type
     *
     * @param string $file_type File extension or MIME type
     * @return string Dashicons class name
     * @since 1.1.0
     *
     * @example wpbp_get_file_icon_class('pdf') // Returns "dashicons-pdf"
     */
    function wpbp_get_file_icon_class(string $file_type): string
    {
        // Convert MIME to extension if needed
        if (strpos($file_type, '/') !== false) {
            $file_type = wpbp_mime_to_extension($file_type) ?? 'file';
        }

        $icon_map = [
            'pdf' => 'dashicons-pdf',
            'doc' => 'dashicons-media-document',
            'docx' => 'dashicons-media-document',
            'xls' => 'dashicons-media-spreadsheet',
            'xlsx' => 'dashicons-media-spreadsheet',
            'ppt' => 'dashicons-media-document',
            'pptx' => 'dashicons-media-document',
            'zip' => 'dashicons-media-archive',
            'rar' => 'dashicons-media-archive',
            '7z' => 'dashicons-media-archive',
            'jpg' => 'dashicons-format-image',
            'jpeg' => 'dashicons-format-image',
            'png' => 'dashicons-format-image',
            'gif' => 'dashicons-format-image',
            'webp' => 'dashicons-format-image',
            'svg' => 'dashicons-format-image',
            'mp3' => 'dashicons-format-audio',
            'wav' => 'dashicons-format-audio',
            'mp4' => 'dashicons-format-video',
            'avi' => 'dashicons-format-video',
            'mov' => 'dashicons-format-video',
            'txt' => 'dashicons-media-text',
            'csv' => 'dashicons-media-spreadsheet',
        ];

        return $icon_map[strtolower($file_type)] ?? 'dashicons-media-default';
    }
}

if (!function_exists('wpbp_array_get')) {
    /**
     * Get an item from an array using "dot" notation
     *
     * @param array $array The array to search
     * @param string $key The key in dot notation (e.g., 'user.profile.name')
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The value or default
     * @since 1.1.0
     *
     * @example wpbp_array_get(['user' => ['name' => 'John']], 'user.name') // Returns "John"
     */
    function wpbp_array_get(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
