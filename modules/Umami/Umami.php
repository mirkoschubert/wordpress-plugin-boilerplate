<?php

namespace WPPluginBoilerplate\Modules\Umami;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class Umami extends Module
{

  protected $enabled = true;
  protected $author = 'Mirko Schubert';
  protected $version = '1.0.0';
  protected $slug = 'umami';

  public function get_name(): string
  {
    return __('Umami', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Umami Analytics integration for WordPress.', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [
    'jquery',
  ];
  protected $default_options = [
    'enabled' => true,
    'umami_domain' => '',
    'website_id' => '',
    'ignore_logged_in' => true,
    'enable_events' => false,
    'events' => [],
  ];

  /**
   * Admin settings for the module
   * @return array
   * @package Misc
   * @since 1.0.0
   */
  public function admin_settings(): array {
    return [
      'umami_domain' => [
        'type' => 'text',
        'label' => __('Enter the Domain of your umami instance', 'wp-plugin-boilerplate'),
        'description' => __('without https://', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['umami_domain'],
      ],
      'website_id' => [
        'type' => 'text',
        'label' => __('Enter your Umami website ID', 'wp-plugin-boilerplate'),
        'description' => __('', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['website_id'],
      ],
      'ignore_logged_in' => [
        'type' => 'toggle',
        'label' => __('Ignore users that are logged in', 'wp-plugin-boilerplate'),
        'description' => __('', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['ignore_logged_in'],
      ],
      'enable_events' => [
        'type' => 'toggle',
        'label' => __('Enable Events for Umami', 'wp-plugin-boilerplate'),
        'description' => __('', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['enable_events'],
      ],
      'events' => [
        'type' => 'repeater',
        'label' => __('Umami Events', 'wp-plugin-boilerplate'),
        'description' => __('Configure events for Umami Analytics. Set the Event ID as the CSS ID of the element you want to track.', 'wp-plugin-boilerplate'),
        'fields' => [
          'id' => [
            'type' => 'text',
            'label' => 'Event ID',
            'default' => ''
          ],
          'name' => [
            'type' => 'text',
            'label' => 'Event Name',
            'default' => ''
          ]
        ],
        'default' => [],
        'depends_on' => [
          'enable_events' => true
        ]
      ],
    ];
  }
}