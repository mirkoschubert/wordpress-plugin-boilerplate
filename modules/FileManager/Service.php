<?php

/**
 * FileManager Service
 *
 * Handles file management functionality including CPT registration,
 * AJAX handlers, and shortcode rendering.
 *
 * @package     WPPluginBoilerplate\Modules\FileManager
 * @since       1.1.0
 * @author      Mirko Schubert
 */

namespace WPPluginBoilerplate\Modules\FileManager;

use WPPluginBoilerplate\Core\Abstracts\ModuleService;
use WPPluginBoilerplate\Core\Traits\AjaxHandler;

class Service extends ModuleService
{
    use AjaxHandler;

    /**
     * Get module option with default value
     */
    private function get_option(string $key, $default = null)
    {
        $value = $this->get_module_option($key);
        return $value !== null ? $value : $default;
    }

    /**
     * Initialize service hooks
     */
    public function init_service(): void
    {
        // Register CPT
        if ($this->is_option_enabled('cpt_enabled')) {
            add_action('init', [$this, 'register_file_cpt']);
        }

        // Admin columns and filters
        if (is_admin()) {
            add_filter('manage_wpbp_file_posts_columns', [$this, 'add_admin_columns']);
            add_action('manage_wpbp_file_posts_custom_column', [$this, 'render_admin_column_content'], 10, 2);
            add_filter('manage_edit-wpbp_file_sortable_columns', [$this, 'add_sortable_columns']);
            add_action('restrict_manage_posts', [$this, 'add_admin_filters']);
            add_action('pre_get_posts', [$this, 'apply_admin_filters']);
        }

        // Frontend
        add_shortcode('wpbp_files', [$this, 'files_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

        // AJAX handlers
        add_action('wp_ajax_wpbp_list_files', [$this, 'ajax_list_files']);
        add_action('wp_ajax_wpbp_upload_file', [$this, 'ajax_upload_file']);
        add_action('wp_ajax_wpbp_get_file_data', [$this, 'ajax_get_file_data']);
        add_action('wp_ajax_wpbp_edit_file', [$this, 'ajax_edit_file']);
        add_action('wp_ajax_wpbp_delete_file', [$this, 'ajax_delete_file']);
        add_action('wp_ajax_wpbp_download_file', [$this, 'ajax_download_file']);

        // Cleanup on file deletion
        add_action('before_delete_post', [$this, 'delete_associated_attachment'], 10, 1);
    }

    /**
     * Register File Custom Post Type
     */
    public function register_file_cpt(): void
    {
        $labels = [
            'name' => _x('Files', 'Post Type General Name', 'wp-plugin-boilerplate'),
            'singular_name' => _x('File', 'Post Type Singular Name', 'wp-plugin-boilerplate'),
            'menu_name' => __('Files', 'wp-plugin-boilerplate'),
            'all_items' => __('All Files', 'wp-plugin-boilerplate'),
            'add_new' => __('Add New', 'wp-plugin-boilerplate'),
            'add_new_item' => __('Add New File', 'wp-plugin-boilerplate'),
            'edit_item' => __('Edit File', 'wp-plugin-boilerplate'),
            'update_item' => __('Update File', 'wp-plugin-boilerplate'),
            'view_item' => __('View File', 'wp-plugin-boilerplate'),
            'search_items' => __('Search Files', 'wp-plugin-boilerplate'),
            'not_found' => __('No files found', 'wp-plugin-boilerplate'),
            'not_found_in_trash' => __('No files found in Trash', 'wp-plugin-boilerplate'),
        ];

        $menu_position = $this->get_option('menu_position', 26);
        $manage_cap = $this->get_option('manage_capability', 'manage_downloads');

        $args = [
            'label' => __('Files', 'wp-plugin-boilerplate'),
            'description' => __('Downloadable files with permission control', 'wp-plugin-boilerplate'),
            'labels' => $labels,
            'supports' => ['title', 'author', 'revisions', 'custom-fields'],
            'taxonomies' => [],
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'menu_position' => $menu_position,
            'menu_icon' => 'dashicons-download',
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'capabilities' => [
                'edit_post' => $manage_cap,
                'read_post' => $manage_cap,
                'delete_post' => $this->get_option('delete_capability', 'delete_downloads'),
                'edit_posts' => $manage_cap,
                'edit_others_posts' => $manage_cap,
                'publish_posts' => $this->get_option('upload_capability', 'upload_downloads'),
                'read_private_posts' => $manage_cap,
            ],
            'show_in_rest' => false,
            'rewrite' => false,
        ];

        register_post_type('wpbp_file', $args);
    }

    /**
     * Add custom admin columns
     */
    public function add_admin_columns(array $columns): array
    {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['area'] = __('Area', 'wp-plugin-boilerplate');
        $new_columns['file_type'] = __('Type', 'wp-plugin-boilerplate');
        $new_columns['file_size'] = __('Size', 'wp-plugin-boilerplate');
        $new_columns['author'] = $columns['author'];
        $new_columns['date'] = $columns['date'];

        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public function render_admin_column_content(string $column, int $post_id): void
    {
        switch ($column) {
            case 'area':
                $area = get_post_meta($post_id, '_wpbp_file_area', true);
                echo esc_html($area ?: '-');
                break;

            case 'file_type':
                $attachment_id = get_post_meta($post_id, '_wpbp_file_attachment', true);
                if ($attachment_id) {
                    $file_path = get_attached_file($attachment_id);
                    $file_type = wp_check_filetype($file_path);
                    echo '<span class="dashicons ' . esc_attr(wpbp_get_file_icon_class($file_type['ext'])) . '"></span> ';
                    echo esc_html(strtoupper($file_type['ext']));
                } else {
                    echo '-';
                }
                break;

            case 'file_size':
                $attachment_id = get_post_meta($post_id, '_wpbp_file_attachment', true);
                if ($attachment_id) {
                    $file_path = get_attached_file($attachment_id);
                    if (file_exists($file_path)) {
                        echo esc_html(wpbp_format_bytes(filesize($file_path)));
                    }
                } else {
                    echo '-';
                }
                break;
        }
    }

    /**
     * Add sortable columns
     */
    public function add_sortable_columns(array $columns): array
    {
        $columns['area'] = 'area';
        $columns['file_type'] = 'file_type';
        return $columns;
    }

    /**
     * Add admin filter dropdowns
     */
    public function add_admin_filters(): void
    {
        global $typenow;

        if ($typenow !== 'wpbp_file') {
            return;
        }

        // Area filter
        $areas = $this->get_option('areas_list', []);
        $current_area = $_GET['wpbp_area'] ?? '';

        echo '<select name="wpbp_area">';
        echo '<option value="">' . esc_html__('All Areas', 'wp-plugin-boilerplate') . '</option>';
        foreach ($areas as $area) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($area['slug']),
                selected($current_area, $area['slug'], false),
                esc_html($area['name'])
            );
        }
        echo '</select>';

        // File type filter
        $current_type = $_GET['wpbp_file_type'] ?? '';
        $file_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'jpg', 'png'];

        echo '<select name="wpbp_file_type">';
        echo '<option value="">' . esc_html__('All File Types', 'wp-plugin-boilerplate') . '</option>';
        foreach ($file_types as $type) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($type),
                selected($current_type, $type, false),
                esc_html(strtoupper($type))
            );
        }
        echo '</select>';
    }

    /**
     * Apply admin filters to query
     */
    public function apply_admin_filters(\WP_Query $query): void
    {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'wpbp_file') {
            return;
        }

        // Area filter
        if (!empty($_GET['wpbp_area'])) {
            $query->set('meta_query', [
                [
                    'key' => '_wpbp_file_area',
                    'value' => sanitize_text_field($_GET['wpbp_area']),
                ],
            ]);
        }

        // File type filter
        if (!empty($_GET['wpbp_file_type'])) {
            $file_type = sanitize_text_field($_GET['wpbp_file_type']);

            // This requires joining with attachments - simplified for now
            // In production, you might want a more sophisticated solution
        }
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets(): void
    {
        if (!is_user_logged_in()) {
            return;
        }

        $module_url = \WP_PLUGIN_BOILERPLATE_URL . 'modules/FileManager/';

        wp_enqueue_style(
            'wpbp-filemanager',
            $module_url . 'assets/css/filemanager.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'wpbp-filemanager',
            $module_url . 'assets/js/filemanager.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('wpbp-filemanager', 'wpbpFileManager', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpbp_file_nonce'),
            'areas' => $this->get_option('areas_list', []),
            'canUpload' => current_user_can($this->get_option('upload_capability', 'upload_downloads')),
            'canEdit' => current_user_can($this->get_option('edit_capability', 'edit_downloads')),
            'canDelete' => current_user_can($this->get_option('delete_capability', 'delete_downloads')),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this file?', 'wp-plugin-boilerplate'),
                'uploadSuccess' => __('File uploaded successfully', 'wp-plugin-boilerplate'),
                'uploadError' => __('Error uploading file', 'wp-plugin-boilerplate'),
                'deleteSuccess' => __('File deleted successfully', 'wp-plugin-boilerplate'),
                'deleteError' => __('Error deleting file', 'wp-plugin-boilerplate'),
            ],
        ]);
    }

    /**
     * Files shortcode
     */
    public function files_shortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'area' => 'general',
            'show_upload' => 'true',
        ], $atts);

        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to view files.', 'wp-plugin-boilerplate') . '</p>';
        }

        // Check area access
        $area_config = $this->get_area_config($atts['area']);
        if (!$area_config || !$this->user_can_access_area($atts['area'])) {
            return '<p>' . esc_html__('You do not have permission to access this area.', 'wp-plugin-boilerplate') . '</p>';
        }

        ob_start();
        include __DIR__ . '/templates/files-list.php';
        return ob_get_clean();
    }

    /**
     * Check if user can access area
     */
    public function user_can_access_area(string $area_slug): bool
    {
        $area_config = $this->get_area_config($area_slug);

        if (!$area_config) {
            return false;
        }

        $required_cap = $area_config['required_capability'] ?? 'read';
        return current_user_can($required_cap);
    }

    /**
     * Get area configuration
     */
    public function get_area_config(string $area_slug): ?array
    {
        $areas = $this->get_option('areas_list', []);

        foreach ($areas as $area) {
            if ($area['slug'] === $area_slug) {
                return $area;
            }
        }

        return null;
    }

    /**
     * AJAX: List files
     */
    public function ajax_list_files(): void
    {
        $this->verify_ajax_request('wpbp_file', 'read');

        $area = $this->sanitize_ajax_input('area', 'text', true);

        if (!$this->user_can_access_area($area)) {
            $this->send_json_error(__('Access denied', 'wp-plugin-boilerplate'));
        }

        $args = [
            'post_type' => 'wpbp_file',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => [
                [
                    'key' => '_wpbp_file_area',
                    'value' => $area,
                ],
            ],
        ];

        $files = get_posts($args);
        $formatted_files = [];

        foreach ($files as $file) {
            $attachment_id = get_post_meta($file->ID, '_wpbp_file_attachment', true);
            $attachment_url = wp_get_attachment_url($attachment_id);
            $file_path = get_attached_file($attachment_id);
            $file_type = wp_check_filetype($file_path);

            $formatted_files[] = [
                'id' => $file->ID,
                'title' => $file->post_title,
                'url' => $attachment_url,
                'type' => $file_type['ext'],
                'mime_type' => get_post_mime_type($attachment_id),
                'size' => file_exists($file_path) ? wpbp_format_bytes(filesize($file_path)) : '',
                'date' => get_the_date('', $file),
                'author' => get_the_author_meta('display_name', $file->post_author),
                'can_edit' => current_user_can($this->get_option('edit_capability', 'edit_downloads')),
                'can_delete' => current_user_can($this->get_option('delete_capability', 'delete_downloads')),
            ];
        }

        $this->send_json_success(['files' => $formatted_files]);
    }

    /**
     * AJAX: Upload file
     */
    public function ajax_upload_file(): void
    {
        $upload_cap = $this->get_option('upload_capability', 'upload_downloads');
        $this->verify_ajax_request('wpbp_file', $upload_cap);

        // Honeypot check
        if (!empty($_POST['website'])) {
            $this->log_ajax_error('Honeypot triggered');
            $this->send_json_error(__('Invalid request', 'wp-plugin-boilerplate'));
        }

        $area = $this->sanitize_ajax_input('area', 'text', true);
        $title = $this->sanitize_ajax_input('title', 'text', true);

        if (!$this->user_can_access_area($area)) {
            $this->send_json_error(__('Access denied', 'wp-plugin-boilerplate'));
        }

        if (!isset($_FILES['file'])) {
            $this->send_json_error(__('No file provided', 'wp-plugin-boilerplate'));
        }

        // Handle upload
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $file = $_FILES['file'];
        $upload = wp_handle_upload($file, ['test_form' => false]);

        if (isset($upload['error'])) {
            $this->send_json_error($upload['error']);
        }

        // Create attachment
        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name(basename($upload['file'])),
            'post_content' => '',
            'post_status' => 'inherit',
        ], $upload['file']);

        if (is_wp_error($attachment_id)) {
            $this->send_json_error($attachment_id->get_error_message());
        }

        wp_generate_attachment_metadata($attachment_id, $upload['file']);

        // Create file post
        $post_id = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'wpbp_file',
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            wp_delete_attachment($attachment_id, true);
            $this->send_json_error($post_id->get_error_message());
        }

        // Save metadata
        update_post_meta($post_id, '_wpbp_file_area', $area);
        update_post_meta($post_id, '_wpbp_file_attachment', $attachment_id);

        $this->send_json_success([
            'message' => __('File uploaded successfully', 'wp-plugin-boilerplate'),
            'file_id' => $post_id,
        ]);
    }

    /**
     * AJAX: Get file data
     */
    public function ajax_get_file_data(): void
    {
        $this->verify_ajax_request('wpbp_file', 'read');

        $file_id = $this->sanitize_ajax_input('file_id', 'int', true);

        $file = get_post($file_id);
        if (!$file || $file->post_type !== 'wpbp_file') {
            $this->send_json_error(__('File not found', 'wp-plugin-boilerplate'));
        }

        $area = get_post_meta($file_id, '_wpbp_file_area', true);
        if (!$this->user_can_access_area($area)) {
            $this->send_json_error(__('Access denied', 'wp-plugin-boilerplate'));
        }

        $attachment_id = get_post_meta($file_id, '_wpbp_file_attachment', true);
        $file_path = get_attached_file($attachment_id);
        $file_type = wp_check_filetype($file_path);

        $this->send_json_success([
            'id' => $file_id,
            'title' => $file->post_title,
            'area' => $area,
            'file' => [
                'name' => basename($file_path),
                'size' => wpbp_format_bytes(filesize($file_path)),
                'type' => $file_type['ext'],
            ],
        ]);
    }

    /**
     * AJAX: Edit file
     */
    public function ajax_edit_file(): void
    {
        $edit_cap = $this->get_option('edit_capability', 'edit_downloads');
        $this->verify_ajax_request('wpbp_file', $edit_cap);

        $file_id = $this->sanitize_ajax_input('file_id', 'int', true);
        $title = $this->sanitize_ajax_input('title', 'text', true);

        $file = get_post($file_id);
        if (!$file || $file->post_type !== 'wpbp_file') {
            $this->send_json_error(__('File not found', 'wp-plugin-boilerplate'));
        }

        $result = wp_update_post([
            'ID' => $file_id,
            'post_title' => $title,
        ]);

        if (is_wp_error($result)) {
            $this->send_json_error($result->get_error_message());
        }

        $this->send_json_success(__('File updated successfully', 'wp-plugin-boilerplate'));
    }

    /**
     * AJAX: Delete file
     */
    public function ajax_delete_file(): void
    {
        $delete_cap = $this->get_option('delete_capability', 'delete_downloads');
        $this->verify_ajax_request('wpbp_file', $delete_cap);

        $file_id = $this->sanitize_ajax_input('file_id', 'int', true);

        $file = get_post($file_id);
        if (!$file || $file->post_type !== 'wpbp_file') {
            $this->send_json_error(__('File not found', 'wp-plugin-boilerplate'));
        }

        $result = wp_trash_post($file_id);

        if (!$result) {
            $this->send_json_error(__('Error deleting file', 'wp-plugin-boilerplate'));
        }

        $this->send_json_success(__('File deleted successfully', 'wp-plugin-boilerplate'));
    }

    /**
     * AJAX: Download file
     */
    public function ajax_download_file(): void
    {
        $this->verify_ajax_request('wpbp_file', 'read');

        $file_id = $this->sanitize_ajax_input('file_id', 'int', true);

        $file = get_post($file_id);
        if (!$file || $file->post_type !== 'wpbp_file') {
            wp_die(__('File not found', 'wp-plugin-boilerplate'), 404);
        }

        $area = get_post_meta($file_id, '_wpbp_file_area', true);
        if (!$this->user_can_access_area($area)) {
            wp_die(__('Access denied', 'wp-plugin-boilerplate'), 403);
        }

        $attachment_id = get_post_meta($file_id, '_wpbp_file_attachment', true);
        $file_path = get_attached_file($attachment_id);

        if (!file_exists($file_path)) {
            wp_die(__('File not found', 'wp-plugin-boilerplate'), 404);
        }

        // Force download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($file_path);
        exit;
    }

    /**
     * Delete associated attachment when file post is deleted
     */
    public function delete_associated_attachment(int $post_id): void
    {
        $post = get_post($post_id);

        if ($post && $post->post_type === 'wpbp_file') {
            $attachment_id = get_post_meta($post_id, '_wpbp_file_attachment', true);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true);
            }
        }
    }
}
