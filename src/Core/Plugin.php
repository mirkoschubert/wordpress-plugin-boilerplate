<?php

namespace WPPluginBoilerplate\Core;

use WPPluginBoilerplate\API\RestController;
use WPPluginBoilerplate\Core\Config;
use WPPluginBoilerplate\Core\Migration;
use WPPluginBoilerplate\Admin\Admin;

final class Plugin
{
  protected $admin;
  protected $migration;
  protected $config;
  protected $rest_controller;
  protected $options = [];

  /**
   * Initialize the Plugin
   * @return void
   * @since 1.0.0
   */
  public function init()
  {
    $this->config = Config::get_instance();
    $this->options = $this->config->get_options();

    // Load global helper functions
    require_once \WP_PLUGIN_BOILERPLATE_DIR . 'src/Core/helpers.php';

    $this->migration = new Migration();
    $this->migration->run();

    add_action('rest_api_init', [$this, 'init_rest_api'], 10);

    $this->load_modules();

    if (is_admin()) {
      $this->admin = new Admin();
    }

    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    add_action('init', [$this, 'setup_languages']);

    $this->load_builders();
    $this->add_plugin_body_class();
  }

  public function init_rest_api()
  {
    $this->rest_controller = new RestController();
    $this->rest_controller->register_routes();
  }

  public function activate()
  {
    $this->config = Config::get_instance();
    $this->migration = new Migration();
    $this->migration->run();

    update_option('wp_plugin_boilerplate_version', $this->config->plugin_version);
    flush_rewrite_rules();
  }

  public function deactivate()
  {
    flush_rewrite_rules();
  }

  /**
   * Enqueue scripts and styles
   * @return void
   * @since 1.0.0
   */
  public function enqueue_scripts()
  {
    wp_enqueue_script('wp-i18n');
    wp_enqueue_script('wp-plugin-boilerplate-script', $this->config->plugin_url . 'src/assets/js/main.js', ['jquery', 'wp-i18n'], null, true);
  }

  /**
   * Load plugin text domain
   * @return void
   * @since 1.0.0
   */
  public function setup_languages()
  {
    load_plugin_textdomain('wp-plugin-boilerplate', false, dirname(plugin_basename(WP_PLUGIN_BOILERPLATE_FILE)) . '/languages');
  }

  /**
   * Add a plugin class to the body tag
   * @return void
   * @since 1.0.0
   */
  public function add_plugin_body_class()
  {
    if (is_admin()) {
      add_filter('admin_body_class', function ($classes) {
        $classes .= ' wp-plugin-boilerplate';
        return $classes;
      });
    } else {
      add_filter('body_class', function ($classes) {
        $classes[] = 'wp-plugin-boilerplate';
        return $classes;
      });
    }
  }

  /**
   * Load builder integrations (Divi, Breakdance) if available
   * @return void
   * @since 1.0.0
   */
  private function load_builders()
  {
    // Divi Builder Integration
    add_action('divi_extensions_init', function () {
      $divi_dir = $this->config->plugin_dir . 'src/Builders/Divi';
      if (is_dir($divi_dir)) {
        $module_files = glob("{$divi_dir}/*.php");
        foreach ($module_files as $file) {
          require_once $file;
        }
      }
    });

    // Breakdance Builder Integration
    add_action('breakdance_loaded', function () {
      $breakdance_dir = $this->config->plugin_dir . 'src/Builders/Breakdance';
      if (is_dir($breakdance_dir)) {
        $element_files = glob("{$breakdance_dir}/*.php");
        foreach ($element_files as $file) {
          require_once $file;
        }
      }
    });
  }

  /**
   * Load all modules from the modules directory
   * @return void
   */
  private function load_modules()
  {
    $modules_dir = $this->config->plugin_dir . 'modules';

    if (!is_dir($modules_dir)) {
      error_log("WPPluginBoilerplate: Modules directory not found: {$modules_dir}");
      return;
    }

    // Get all subdirectories in the modules folder
    $module_folders = glob("{$modules_dir}/*", GLOB_ONLYDIR);

    foreach ($module_folders as $module_folder) {
      $module_name = \basename($module_folder);

      // Skip directories starting with underscore or dot
      if (\substr($module_name, 0, 1) === '_' || \substr($module_name, 0, 1) === '.') {
        continue;
      }

      // Get main module class file (should be named same as directory)
      $module_file = "{$module_folder}/{$module_name}.php";

      if (\file_exists($module_file)) {
        $class_name = "\\WPPluginBoilerplate\\Modules\\{$module_name}\\{$module_name}";

        // Initialize module if class exists
        if (\class_exists($class_name)) {
          try {
            $instance = new $class_name();
          } catch (\Exception $e) {
            error_log("WPPluginBoilerplate: Failed to load module {$module_name}: " . $e->getMessage());
          }
        } else {
          error_log("WPPluginBoilerplate: Module class not found: {$class_name}");
        }
      } else {
        error_log("WPPluginBoilerplate: Module file not found: {$module_file}");
      }
    }
  }
}
