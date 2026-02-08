<?php

/**
 * FileManager Module
 *
 * Provides file upload, download, and management functionality with
 * permission-based access control and shortcode support.
 *
 * @package     WPPluginBoilerplate\Modules\FileManager
 * @since       1.1.0
 * @author      Mirko Schubert
 */

namespace WPPluginBoilerplate\Modules\FileManager;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class FileManager extends Module
{
    protected $enabled = false;
    protected $author = 'Mirko Schubert';
    protected $version = '1.0.0';
    protected $slug = 'filemanager';

    public function get_name(): string
    {
        return __('File Manager', 'wp-plugin-boilerplate');
    }

    public function get_description(): string
    {
        return __('File upload, download, and management with permission-based access control. Provides shortcode for frontend file listings and AJAX-based file operations.', 'wp-plugin-boilerplate');
    }

    protected $dependencies = [
        'jquery',
    ];

    protected $default_options = [
        'enabled' => false,
        'cpt_enabled' => true,
        'menu_position' => 26,
        'allowed_file_types' => [],
        'max_file_size' => 0,
        'areas_list' => [
            [
                'slug' => 'general',
                'name' => 'General Files',
                'description' => 'General file downloads for all users',
                'required_capability' => 'read',
            ],
        ],
        'manage_capability' => 'manage_downloads',
        'upload_capability' => 'upload_downloads',
        'edit_capability' => 'edit_downloads',
        'delete_capability' => 'delete_downloads',
        'show_file_size' => true,
        'show_file_type' => true,
        'show_upload_date' => true,
        'show_author' => false,
        'enable_frontend_upload' => true,
    ];

    /**
     * Admin settings for the module
     */
    public function admin_settings(): array
    {
        return [
            'general_group' => [
                'type' => 'group',
                'title' => __('General Settings', 'wp-plugin-boilerplate'),
                'description' => __('Basic file manager configuration', 'wp-plugin-boilerplate'),
                'fields' => [
                    'cpt_enabled' => [
                        'type' => 'toggle',
                        'label' => __('Enable File Custom Post Type', 'wp-plugin-boilerplate'),
                        'description' => __('Register a custom post type for files in the WordPress admin.', 'wp-plugin-boilerplate'),
                        'default' => $this->default_options['cpt_enabled'],
                    ],
                    'menu_position' => [
                        'type' => 'number',
                        'label' => __('Menu Position', 'wp-plugin-boilerplate'),
                        'description' => __('Position in admin menu (5=below Posts, 10=below Media, 20=below Pages).', 'wp-plugin-boilerplate'),
                        'default' => $this->default_options['menu_position'],
                        'min' => 5,
                        'max' => 100,
                    ],
                ],
            ],
            'permissions_group' => [
                'type' => 'group',
                'title' => __('Permissions', 'wp-plugin-boilerplate'),
                'description' => __('Configure capabilities for file management', 'wp-plugin-boilerplate'),
                'fields' => [
                    'manage_capability' => [
                        'type' => 'text',
                        'label' => __('Manage Files Capability', 'wp-plugin-boilerplate'),
                        'description' => __('Capability required to view and manage files in admin.', 'wp-plugin-boilerplate'),
                        'default' => $this->default_options['manage_capability'],
                    ],
                    'upload_capability' => [
                        'type' => 'text',
                        'label' => __('Upload Files Capability', 'wp-plugin-boilerplate'),
                        'description' => __('Capability required to upload new files.', 'wp-plugin-boilerplate'),
                        'default' => $this->default_options['upload_capability'],
                    ],
                ],
            ],
            'frontend_group' => [
                'type' => 'group',
                'title' => __('Frontend Display', 'wp-plugin-boilerplate'),
                'description' => __('Configure what information is shown in the frontend', 'wp-plugin-boilerplate'),
                'fields' => [
                    'show_file_size' => [
                        'type' => 'toggle',
                        'label' => __('Show File Size', 'wp-plugin-boilerplate'),
                        'default' => $this->default_options['show_file_size'],
                    ],
                    'show_file_type' => [
                        'type' => 'toggle',
                        'label' => __('Show File Type', 'wp-plugin-boilerplate'),
                        'default' => $this->default_options['show_file_type'],
                    ],
                    'show_upload_date' => [
                        'type' => 'toggle',
                        'label' => __('Show Upload Date', 'wp-plugin-boilerplate'),
                        'default' => $this->default_options['show_upload_date'],
                    ],
                ],
            ],
        ];
    }
}
