<?php

namespace WPPluginBoilerplate\Builders\Abstracts;

/**
 * Abstract base class for Breakdance custom elements.
 *
 * Extend this class to create custom Breakdance elements within the plugin.
 * Place your element files in src/Builders/Breakdance/ — they will be auto-loaded
 * when Breakdance is active.
 *
 * Usage:
 *   class MyCustomElement extends BreakdanceElement {
 *       public static function slug(): string { return 'wpbp_my_element'; }
 *       public static function label(): string { return 'My Element'; }
 *       public static function category(): string { return 'WP Plugin Boilerplate'; }
 *       public function controls(): array { return []; }
 *       public function render($properties, $children): string { return '<div>...</div>'; }
 *   }
 *
 * @since 1.0.0
 */
abstract class BreakdanceElement
{
  /**
   * Check if Breakdance is available
   * @return bool
   */
  public static function is_breakdance_active(): bool
  {
    return defined('__BREAKDANCE_VERSION') || class_exists('\\Breakdance\\Plugin');
  }

  /**
   * Register a Breakdance element class.
   * Call this in a `breakdance_loaded` hook.
   *
   * @param string $class_name Fully qualified class name
   * @return void
   */
  public static function register(string $class_name): void
  {
    if (!self::is_breakdance_active()) {
      return;
    }

    if (function_exists('\\Breakdance\\Elements\\registerElement')) {
      \Breakdance\Elements\registerElement($class_name);
    }
  }

  /**
   * Returns the plugin's asset URL for Breakdance element assets
   * @param string $path Relative path within the Builders/Breakdance/assets directory
   * @return string
   */
  protected static function get_asset_url(string $path): string
  {
    return WP_PLUGIN_BOILERPLATE_URL . 'src/Builders/Breakdance/assets/' . $path;
  }

  /**
   * Returns the plugin's asset directory for Breakdance element assets
   * @param string $path Relative path within the Builders/Breakdance/assets directory
   * @return string
   */
  protected static function get_asset_dir(string $path): string
  {
    return WP_PLUGIN_BOILERPLATE_DIR . 'src/Builders/Breakdance/assets/' . $path;
  }
}
