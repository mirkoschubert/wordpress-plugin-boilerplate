<?php

namespace WPPluginBoilerplate\Modules\Administration;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class Administration extends Module
{

  protected $enabled = true;
  protected $author = 'Mirko Schubert';
  protected $version = '1.0.0';
  protected $slug = 'administration';

  public function get_name(): string
  {
    return __('Administration', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Admin, content management and frontend enhancements for WordPress.', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [
    'jquery',
  ];
  protected $default_options = [
    'enabled' => true,
    'duplicate_posts' => false,
    'stop_mail_updates' => false,
    'media_infinite_scroll' => false,
    'svg_support' => false,
    'webp_support' => false,
    'avif_support' => false,
    'hyphens' => false,
    'external_links_new_tab' => false,
    'external_links_rel' => 'noopener noreferrer nofollow',
  ];

  /**
   * Admin settings for the module
   * @return array
   * @package Administration
   * @since 1.0.0
   */
  public function admin_settings(): array {
    return [
      'admin_group' => [
        'type' => 'group',
        'title' => __('Admin & Backend', 'wp-plugin-boilerplate'),
        'description' => __('WordPress backend improvements and content management features', 'wp-plugin-boilerplate'),
        'fields' => [
          'duplicate_posts' => [
            'type' => 'toggle',
            'label' => __('Enable duplicate posts', 'wp-plugin-boilerplate'),
            'description' => __('Allows you to duplicate posts and pages easily.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['duplicate_posts'],
          ],
          'stop_mail_updates' => [
            'type' => 'toggle',
            'label' => __('Disable auto-update emails', 'wp-plugin-boilerplate'),
            'description' => __('Stop email notifications when plugins or themes are automatically updated', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['stop_mail_updates'],
          ],
        ]
      ],
      'media_group' => [
        'type' => 'group',
        'title' => __('Media & Files', 'wp-plugin-boilerplate'),
        'description' => __('Media library enhancements and file format support', 'wp-plugin-boilerplate'),
        'fields' => [
          'media_infinite_scroll' => [
            'type' => 'toggle',
            'label' => __('Enable infinite scroll for media library', 'wp-plugin-boilerplate'),
            'description' => __('Load media files continuously without pagination', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['media_infinite_scroll'],
          ],
          'svg_support' => [
            'type' => 'toggle',
            'label' => __('Enable SVG file uploads', 'wp-plugin-boilerplate'),
            'description' => __('Allow SVG files in media library. Warning: SVG files can contain malicious code', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['svg_support'],
            'dependencies' => [
              'wordpress' => '>= 4.7',
            ]
          ],
          'webp_support' => [
            'type' => 'toggle',
            'label' => __('Enable WebP file uploads', 'wp-plugin-boilerplate'),
            'description' => __('Allow WebP files in media library. Native support available in WordPress 5.8+', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['webp_support'],
            'dependencies' => [
              'wordpress' => '< 5.8',
            ]
          ],
          'avif_support' => [
            'type' => 'toggle',
            'label' => __('Enable AVIF file uploads', 'wp-plugin-boilerplate'),
            'description' => __('Allow AVIF files in media library. Native support available in WordPress 6.5+', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['avif_support'],
            'dependencies' => [
              'wordpress' => '< 6.5',
            ]
          ],
        ]
      ],
      'frontend_group' => [
        'type' => 'group',
        'title' => __('Frontend & Design', 'wp-plugin-boilerplate'),
        'description' => __('Visual improvements and frontend enhancements', 'wp-plugin-boilerplate'),
        'fields' => [
          'hyphens' => [
            'type' => 'toggle',
            'label' => __('Enable hyphenation', 'wp-plugin-boilerplate'),
            'description' => __('Activate automatic word hyphenation for better text layout', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['hyphens'],
          ],
          'external_links_new_tab' => [
            'type' => 'toggle',
            'label' => __('Open external links in new tab', 'wp-plugin-boilerplate'),
            'description' => __('Automatically add target="_blank" and rel attributes to all external links on the page.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['external_links_new_tab'],
          ],
          'external_links_rel' => [
            'type' => 'text',
            'label' => __('Rel attributes for external links', 'wp-plugin-boilerplate'),
            'description' => __('Space-separated rel attribute values for external links.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['external_links_rel'],
            'depends_on' => ['external_links_new_tab' => true],
          ],
        ]
      ],
    ];
  }
}
