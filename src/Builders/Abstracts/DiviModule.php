<?php

namespace WPPluginBoilerplate\Builders\Abstracts;

/**
 * Abstract base class for Divi Builder custom modules.
 *
 * Extend this class to create custom Divi modules within the plugin.
 * Place your module files in src/Builders/Divi/ — they will be auto-loaded
 * when Divi is active.
 *
 * Usage:
 *   class MyCustomModule extends DiviModule {
 *       public function init() {
 *           $this->name = 'My Custom Module';
 *           $this->slug = 'wpbp_my_custom_module';
 *           // ...
 *       }
 *       public function get_fields() { return []; }
 *       public function render($attrs, $content, $render_slug) { return '<div>...</div>'; }
 *   }
 *
 * @since 1.0.0
 */
abstract class DiviModule
{
  /**
   * Check if Divi Builder is available
   * @return bool
   */
  public static function is_divi_active(): bool
  {
    return class_exists('ET_Builder_Module');
  }

  /**
   * Register a Divi module class.
   * Call this in a `divi_extensions_init` or `et_builder_ready` hook.
   *
   * @param string $class_name Fully qualified class name extending ET_Builder_Module
   * @return void
   */
  public static function register(string $class_name): void
  {
    if (!self::is_divi_active()) {
      return;
    }

    if (class_exists($class_name)) {
      new $class_name();
    }
  }

  /**
   * Returns the plugin's asset URL for Divi module assets
   * @param string $path Relative path within the Builders/Divi/assets directory
   * @return string
   */
  protected static function get_asset_url(string $path): string
  {
    return WP_PLUGIN_BOILERPLATE_URL . 'src/Builders/Divi/assets/' . $path;
  }

  /**
   * Returns the plugin's asset directory for Divi module assets
   * @param string $path Relative path within the Builders/Divi/assets directory
   * @return string
   */
  protected static function get_asset_dir(string $path): string
  {
    return WP_PLUGIN_BOILERPLATE_DIR . 'src/Builders/Divi/assets/' . $path;
  }
}
