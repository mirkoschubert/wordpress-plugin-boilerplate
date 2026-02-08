<?php

namespace WPPluginBoilerplate\Core\Traits;

trait DependencyChecker
{
  /**
   * Checks if all dependencies for a setting are met
   * @param array $dependencies
   * @return array
   */
  public function check_dependencies(array $dependencies): array
  {
    $result = ['supported' => true];

    // WordPress Version Check
    if (isset($dependencies['wordpress'])) {
      $wp_version = self::get_wp_version();
      $wp_check = $this->check_version_constraint($wp_version, $dependencies['wordpress']);
      if (!$wp_check['valid']) {
        $result['supported'] = false;
      }
    }

    // Divi Version Check (only if Divi is active)
    if (isset($dependencies['divi'])) {
      $divi_version = self::get_divi_version();
      $divi_check = $this->check_version_constraint($divi_version, $dependencies['divi']);
      if (!$divi_check['valid']) {
        $result['supported'] = false;
      }
    }

    // Breakdance Version Check (only if Breakdance is active)
    if (isset($dependencies['breakdance'])) {
      $breakdance_version = self::get_breakdance_version();
      $breakdance_check = $this->check_version_constraint($breakdance_version, $dependencies['breakdance']);
      if (!$breakdance_check['valid']) {
        $result['supported'] = false;
      }
    }

    // Plugin Dependencies
    if (isset($dependencies['plugins'])) {
      foreach ($dependencies['plugins'] as $plugin => $constraint) {
        if (!$this->check_plugin_active($plugin, $constraint)) {
          $result['supported'] = false;
        }
      }
    }

    return $result;
  }

  // =====================================================================
  // Builder / Theme Detection
  // =====================================================================

  /**
   * Checks if Divi Theme or Divi Builder is active
   * @return bool
   */
  public static function is_divi_active(): bool
  {
    return \function_exists('et_get_theme_version') || \class_exists('ET_Builder_Module');
  }

  /**
   * Returns the Divi version or '0.0.0' if not active
   * @return string
   */
  public static function get_divi_version(): string
  {
    return \function_exists('et_get_theme_version') ? et_get_theme_version() : '0.0.0';
  }

  /**
   * Checks if Breakdance Builder is active
   * @return bool
   */
  public static function is_breakdance_active(): bool
  {
    return \defined('__BREAKDANCE_VERSION') || \class_exists('\\Breakdance\\Plugin');
  }

  /**
   * Returns the Breakdance version or '0.0.0' if not active
   * @return string
   */
  public static function get_breakdance_version(): string
  {
    return \defined('__BREAKDANCE_VERSION') ? __BREAKDANCE_VERSION : '0.0.0';
  }

  // =====================================================================
  // WordPress Version
  // =====================================================================

  /**
   * Returns the current WordPress version
   * @return string
   */
  public static function get_wp_version(): string
  {
    return get_bloginfo('version');
  }

  // =====================================================================
  // Version Constraint Checking
  // =====================================================================

  /**
   * Checks version constraint with flexible operators
   * @param string $current Current version
   * @param string $constraint Version constraint (e.g., ">= 4.7", "< 5.8", "= 4.9.1", "4.0-4.5")
   * @return array
   */
  private function check_version_constraint(string $current, string $constraint): array
  {
    // Check for range constraint (e.g., "4.0-4.5")
    if (\preg_match('/^(.+?)\s*-\s*(.+)$/', \trim($constraint), $matches)) {
      $min_version = $matches[1];
      $max_version = $matches[2];

      $valid = version_compare($current, $min_version, '>=') && version_compare($current, $max_version, '<=');

      return ['valid' => $valid];
    }

    // Parse single constraint
    if (\preg_match('/^(>=|<=|>|<|=)\s*(.+)$/', \trim($constraint), $matches)) {
      $operator = $matches[1];
      $version = $matches[2];

      $valid = false;

      switch ($operator) {
        case '>=':
          $valid = version_compare($current, $version, '>=');
          break;
        case '<=':
          $valid = version_compare($current, $version, '<=');
          break;
        case '>':
          $valid = version_compare($current, $version, '>');
          break;
        case '<':
          $valid = version_compare($current, $version, '<');
          break;
        case '=':
          $valid = version_compare($current, $version, '=');
          break;
      }

      return ['valid' => $valid];
    }

    return ['valid' => true];
  }

  /**
   * Checks if a plugin is active and meets version constraints
   * @param string $plugin Plugin slug or file
   * @param string $constraint Version constraint
   * @return bool
   */
  private function check_plugin_active(string $plugin, string $constraint = ''): bool
  {
    if (!\function_exists('is_plugin_active')) {
      include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    if (empty($constraint)) {
      return is_plugin_active($plugin);
    }

    return is_plugin_active($plugin);
  }
}
