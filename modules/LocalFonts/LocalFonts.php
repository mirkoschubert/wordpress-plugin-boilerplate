<?php

namespace WPPluginBoilerplate\Modules\LocalFonts;

use WPPluginBoilerplate\Core\Abstracts\Module;
use WPPluginBoilerplate\Modules\LocalFonts\Downloads;

final class LocalFonts extends Module
{
  protected $enabled = true;
  protected $author = 'Mirko Schubert';
  protected $version = '1.0.0';
  protected $slug = 'localfonts';

  public function get_name(): string
  {
    return __('Local Fonts', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Download and manage Google Fonts locally for GDPR compliance and better performance.', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [];
  protected $default_options = [
    'enabled' => true,
    'disable_google_fonts' => true,
    'enable_gutenberg_fonts' => false,
    'selected_fonts' => [],
    'font_display' => 'swap'
  ];


  /**
   * Constructor for the LocalFonts module.
   * @package LocalFonts
   * @since 1.0.0
   */
  public function __construct()
  {
    parent::__construct();

    // Hook for admin settings save
    add_action('wp_plugin_boilerplate_module_options_saved_' . $this->slug, [$this, 'handle_font_downloads'], 10, 2);
  }


  /**
   * Admin Settings
   * @package LocalFonts
   * @since 1.0.0
   */
  public function admin_settings(): array
  {
    return [
      'disable_google_fonts' => [
        'type' => 'toggle',
        'label' => __('Disable Google Fonts Globally', 'wp-plugin-boilerplate'),
        'description' => __('Automatically disable all Google Fonts loading. Works with Divi, Oxygen, and other themes/plugins that use Google Fonts.', 'wp-plugin-boilerplate'),
        'default' => $this->default_options['disable_google_fonts'],
      ],
      'enable_gutenberg_fonts' => [
        'type' => 'toggle',
        'label' => __('Enable Fonts in Gutenberg Editor', 'wp-plugin-boilerplate'),
        'description' => __('Register local fonts in the Gutenberg (Block) editor font picker.', 'wp-plugin-boilerplate'),
        'default' => false,
      ],
      'selected_fonts' => [
        'type' => 'multi_select',
        'label' => __('Select Google Fonts', 'wp-plugin-boilerplate'),
        'description' => __('Choose Google Fonts to download locally. All weights and styles will be downloaded automatically.', 'wp-plugin-boilerplate'),
        'options' => $this->get_google_fonts_options(),
        'default' => $this->default_options['selected_fonts'],
      ],
      'font_display' => [
        'type' => 'select',
        'label' => __('Font Display', 'wp-plugin-boilerplate'),
        'description' => __('CSS font-display property for optimal loading performance.', 'wp-plugin-boilerplate'),
        'options' => [
          'auto' => __('Auto', 'wp-plugin-boilerplate'),
          'block' => __('Block', 'wp-plugin-boilerplate'),
          'swap' => __('Swap (Recommended)', 'wp-plugin-boilerplate'),
          'fallback' => __('Fallback', 'wp-plugin-boilerplate'),
          'optional' => __('Optional', 'wp-plugin-boilerplate')
        ],
        'default' => $this->default_options['font_display'],
      ],
    ];
  }


  /**
   * Fetches the list of Google Fonts from the Webfonts Helper API.
   * @package LocalFonts
   * @since 1.0.0
   */
  private function get_google_fonts_options()
  {
    $cache_key = 'wp_plugin_boilerplate_gwfh_fonts_list';
    $cached_fonts = get_transient($cache_key);

    if ($cached_fonts !== false) {
      return $cached_fonts;
    }

    $response = wp_remote_get("https://gwfh.mranftl.com/api/fonts", [
      'timeout' => 15,
      'user-agent' => 'WPPluginBoilerplate/1.0 LocalFonts'
    ]);

    if (is_wp_error($response)) {
      error_log('LocalFonts: GWFH API error: ' . $response->get_error_message());
      return $this->get_websafe_fonts_options();
    }

    $fonts_data = \json_decode(wp_remote_retrieve_body($response), true);
    if (!\is_array($fonts_data)) {
      return $this->get_websafe_fonts_options();
    }

    $fonts_metadata = [];
    $options = [];

    foreach ($fonts_data as $font) {
      $family = $font['family'];
      $options[$family] = $family;
      $fonts_metadata[$family] = $font;
    }

    // Cache metadata for download service
    set_transient('wp_plugin_boilerplate_gwfh_fonts_metadata', $fonts_metadata, DAY_IN_SECONDS);

    \asort($options);
    set_transient($cache_key, $options, DAY_IN_SECONDS);

    return $options;
  }

  /**
   * Returns a list of web-safe fonts as a fallback.
   * @package LocalFonts
   * @since 1.0.0
   */
  private function get_websafe_fonts_options()
  {
    return [
      'Arial' => 'Arial',
      'Helvetica' => 'Helvetica',
      'Times New Roman' => 'Times New Roman',
      'Georgia' => 'Georgia',
      'Verdana' => 'Verdana',
    ];
  }


  /**
   * Handles font downloads and removals when admin settings are saved.
   * @package LocalFonts
   * @since 1.0.0
   */
  public function handle_font_downloads($new_options, $old_options = [])
  {
    // Sync Divi Theme Options (only if Divi is active)
    $disable = $new_options['disable_google_fonts'] ?? true;
    if (self::is_divi_active()) {
      $google_api_settings = get_option('et_google_api_settings', []);
      $google_api_settings['use_google_fonts'] = $disable ? 'off' : 'on';
      update_option('et_google_api_settings', $google_api_settings);
    }

    $old_selected = $old_options['selected_fonts'] ?? [];
    $new_selected = $new_options['selected_fonts'] ?? [];

    // Instantiate download service
    $downloads = new Downloads($this);

    $fonts_to_download = \array_diff($new_selected, $old_selected);
    if (!empty($fonts_to_download)) {
      $downloads->download_fonts($fonts_to_download);
    }

    $fonts_to_remove = \array_diff($old_selected, $new_selected);
    if (!empty($fonts_to_remove)) {
      $downloads->remove_fonts($fonts_to_remove);
    }
  }
}
