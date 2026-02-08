<?php

namespace WPPluginBoilerplate\Modules\Privacy;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class Privacy extends Module
{

  protected $enabled = true;
  protected $author = 'Mirko Schubert';
  protected $version = '1.1.0';
  protected $slug = 'privacy';

  public function get_name(): string
  {
    return __('Privacy & Security', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Privacy and security enhancements for WordPress including GDPR compliance.', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [];
  protected $default_options = [
    'enabled' => true,
    // Privacy (ehem. GDPR)
    'comments_external' => true,
    'comments_ip' => true,
    'disable_emojis' => true,
    'disable_oembeds' => true,
    'dns_prefetching' => true,
    'rest_api' => true,
    // Security
    'track_last_login' => false,
    'disable_author_archives' => false,
    'obfuscate_author_slugs' => false,
  ];

  /**
   * Admin settings for the module
   * @return array
   * @since 1.0.0
   */
  public function admin_settings(): array
  {
    return [
      'privacy' => [
        'type' => 'group',
        'label' => __('Privacy', 'wp-plugin-boilerplate'),
        'fields' => [
          'comments_external' => [
            'type' => 'toggle',
            'label' => __('Comments External', 'wp-plugin-boilerplate'),
            'description' => __('Make external links in comments truely external.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['comments_external'],
          ],
          'comments_ip' => [
            'type' => 'toggle',
            'label' => __('Comments IP', 'wp-plugin-boilerplate'),
            'description' => __('Enable comments to be loaded with IP address.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['comments_ip'],
          ],
          'disable_emojis' => [
            'type' => 'toggle',
            'label' => __('Disable Emojis', 'wp-plugin-boilerplate'),
            'description' => __('Disable emojis for GDPR compliance.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['disable_emojis'],
          ],
          'disable_oembeds' => [
            'type' => 'toggle',
            'label' => __('Disable oEmbeds', 'wp-plugin-boilerplate'),
            'description' => __('Disable oEmbeds for GDPR compliance.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['disable_oembeds'],
          ],
          'dns_prefetching' => [
            'type' => 'toggle',
            'label' => __('Disable DNS Prefetching', 'wp-plugin-boilerplate'),
            'description' => __('Disable DNS prefetching for GDPR compliance.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['dns_prefetching'],
          ],
          'rest_api' => [
            'type' => 'toggle',
            'label' => __('Disable REST API', 'wp-plugin-boilerplate'),
            'description' => __('Disable REST API for GDPR compliance.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['rest_api'],
          ],
        ],
      ],
      'security' => [
        'type' => 'group',
        'label' => __('Security', 'wp-plugin-boilerplate'),
        'fields' => [
          'track_last_login' => [
            'type' => 'toggle',
            'label' => __('Track last login time', 'wp-plugin-boilerplate'),
            'description' => __('Show a "Last Login" column in the users table.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['track_last_login'],
          ],
          'disable_author_archives' => [
            'type' => 'toggle',
            'label' => __('Disable author archives', 'wp-plugin-boilerplate'),
            'description' => __('Redirect author archive pages to 404 and remove author links.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['disable_author_archives'],
          ],
          'obfuscate_author_slugs' => [
            'type' => 'toggle',
            'label' => __('Obfuscate author slugs', 'wp-plugin-boilerplate'),
            'description' => __('Replace author usernames in URLs with encrypted IDs to prevent user enumeration.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['obfuscate_author_slugs'],
          ],
        ],
      ],
    ];
  }
}
