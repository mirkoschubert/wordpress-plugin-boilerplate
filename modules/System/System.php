<?php

namespace WPPluginBoilerplate\Modules\System;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class System extends Module
{

  protected $enabled = false;
  protected $author = 'Mirko Schubert';
  protected $version = '1.0.0';
  protected $slug = 'system';

  public function get_name(): string
  {
    return __('System', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Environment badge, search engine visibility warning and status dashboard widget.', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [];
  protected $default_options = [
    'enabled' => false,
    'environment_badge' => false,
    'search_visibility_warning' => true,
    'status_panel' => false,
  ];

  /**
   * Admin settings for the module
   * @return array
   * @package System
   * @since 1.0.0
   */
  public function admin_settings(): array
  {
    return [
      'environment_badge' => [
        'type' => 'toggle',
        'label' => __('Show environment badge', 'wp-plugin-boilerplate'),
        'description' => __('Display a colored badge in the admin bar showing the current environment (Local, Dev, Staging, Live).', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['environment_badge'],
      ],
      'search_visibility_warning' => [
        'type' => 'toggle',
        'label' => __('Show search engine visibility warning', 'wp-plugin-boilerplate'),
        'description' => __('Display a warning icon in the environment badge when "Discourage search engines" is enabled.', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['search_visibility_warning'],
        'depends_on' => ['environment_badge' => true],
      ],
      'status_panel' => [
        'type' => 'toggle',
        'label' => __('Show status in At a Glance', 'wp-plugin-boilerplate'),
        'description' => __('Display system versions and image format support in the At a Glance dashboard widget.', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['status_panel'],
      ],
    ];
  }
}
