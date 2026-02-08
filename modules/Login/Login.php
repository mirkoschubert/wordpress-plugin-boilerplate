<?php

namespace WPPluginBoilerplate\Modules\Login;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class Login extends Module
{

  protected $enabled = false;
  protected $author = 'Mirko Schubert';
  protected $version = '1.0.0';
  protected $slug = 'login';

  public function get_name(): string
  {
    return __('Login', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Customize the WordPress login page with site identity and background image.', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [];
  protected $default_options = [
    'enabled' => false,
    'login_site_identity' => false,
    'login_logo_width' => 120,
    'login_background_image' => 0,
  ];

  /**
   * Admin settings for the module
   * @return array
   * @package Login
   * @since 1.0.0
   */
  public function admin_settings(): array
  {
    return [
      'login_site_identity' => [
        'type' => 'toggle',
        'label' => __('Use site icon as login logo', 'wp-plugin-boilerplate'),
        'description' => __('Replace the WordPress logo on the login page with the site icon and link to the homepage.', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['login_site_identity'],
      ],
      'login_logo_width' => [
        'type' => 'number',
        'label' => __('Logo width (px)', 'wp-plugin-boilerplate'),
        'description' => __('Width of the logo on the login page in pixels.', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['login_logo_width'],
        'depends_on' => ['login_site_identity' => true],
      ],
      'login_background_image' => [
        'type' => 'image',
        'label' => __('Background Image', 'wp-plugin-boilerplate'),
        'description' => __('Background image for the login page (displayed full-size).', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['login_background_image'],
      ],
    ];
  }
}
