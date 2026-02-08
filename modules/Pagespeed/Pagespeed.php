<?php

namespace WPPluginBoilerplate\Modules\Pagespeed;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class Pagespeed extends Module
{

  protected $enabled = true;
  protected $author = 'Mirko Schubert';
  protected $version = '1.1.0';
  protected $slug = 'pagespeed';

  public function get_name(): string
  {
    return __('Pagespeed', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Google Pagespeed optimization module for WordPress.', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [
    'jquery',
  ];
  protected $default_options = [
    'enabled' => true,
    'remove_pingback' => true,
    'remove_dashicons' => true,
    'remove_version_strings' => true,
    'remove_shortlink' => true,
    'preload_fonts' => false,
    'preload_fonts_list' => [
      ['path' => '/wp-content/themes/Divi/core/admin/fonts/modules/all/modules.woff']
    ]
  ];

  /**
   * Summary of admin_settings
   * @return array
   * @package Pagespeed
   * @since 1.0.0
   */
  public function admin_settings(): array
  {
    return [
      'remove_pingback' => [
        'type' => 'toggle',
        'label' => __('Remove Pingback', 'wp-plugin-boilerplate'),
        'description' => __('Removes the pingback header from the site.', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['remove_pingback'],
      ],
      'remove_dashicons' => [
        'type' => 'toggle',
        'label' => __('Remove Dashicons', 'wp-plugin-boilerplate'),
        'description' => __('Removes dashicons from the frontend', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['remove_dashicons'],
      ],
      'remove_version_strings' => [
        'type' => 'toggle',
        'label' => __('Remove Version Strings', 'wp-plugin-boilerplate'),
        'description' => __('Removes CSS and JS version query strings', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['remove_version_strings'],
      ],
      'remove_shortlink' => [
        'type' => 'toggle',
        'label' => __('Remove Shortlink', 'wp-plugin-boilerplate'),
        'description' => __('Removes shortlink from head', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['remove_shortlink'],
      ],
      'preload_fonts' => [
        'type' => 'toggle',
        'label' => __('Preload Fonts', 'wp-plugin-boilerplate'),
        'description' => __('Preload some fonts for speed', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['preload_fonts'],
      ],
      'preload_fonts_list' => [
        'type' => 'repeater',
        'label' => __('Fonts List', 'wp-plugin-boilerplate'),
        'description' => __('Enter font paths to preload for better performance.', 'wp-plugin-boilerplate'),
        'fields' => [
          'path' => [
            'type' => 'text',
            'label' => __('Font Path', 'wp-plugin-boilerplate'),
            'description' => __('Path starting with "/wp-content/" and ending with font extension', 'wp-plugin-boilerplate'),
            'default' => '',
            'validate' => [
              'pattern' => '/^\/wp-content\/.*\.(woff|woff2|ttf|otf|eot)$/',
              'error_message' => __('Please enter a valid font path. It should start with "/wp-content/" and end with a font extension (woff, woff2, ttf, otf, eot).', 'wp-plugin-boilerplate')
            ]
          ]
        ],
        'default' => $this->default_options['preload_fonts_list'],
        'depends_on' => [
          'preload_fonts' => true
        ]
      ],
    ];
  }
}