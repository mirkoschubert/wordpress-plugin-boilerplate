<?php

namespace WPPluginBoilerplate\Core\Abstracts;

use WPPluginBoilerplate\Core\Interfaces\ModuleInterface;
use WPPluginBoilerplate\Core\Config;
use WPPluginBoilerplate\Core\Traits\DependencyChecker;

abstract class Module implements ModuleInterface
{
  use DependencyChecker;

  protected $enabled = true;
  protected $dev_only = false;
  protected $name = '';
  protected $description = '';
  protected $author = '';
  protected $version = '';
  protected $slug = '';
  protected $dependencies = [
    'jquery',
  ];
  protected $config;
  protected $options;
  protected $default_options = [
    'enabled' => true,
  ];
  private static $modules = [];

  protected $service;
  protected $rest_controller;
  protected $module_dir;

  public function __construct()
  {
    if ($this->dev_only && !\in_array(wp_get_environment_type(), ['development', 'local'], true)) {
      return;
    }

    $this->init();

    if (!empty($this->slug)) {
      self::$modules[$this->slug] = $this;
    }
  }

  /**
   * Initializes the module
   * @return void
   * @since 1.0.0
   */
  public function init()
  {
    if (empty($this->name)) {
      $this->name = get_class($this);
      $this->name = \str_replace('WPPluginBoilerplate\\Modules\\', '', $this->name);
    }
    $this->author = empty($this->author) ? 'Mirko Schubert' : $this->author;
    $this->version = empty($this->version) ? '1.0.0' : $this->version;
    $this->slug = empty($this->slug) ? \strtolower($this->name) : $this->slug;

    // Cache module directory name via reflection (once)
    $reflection = new \ReflectionClass($this);
    $this->module_dir = \basename(\dirname($reflection->getFileName()));

    $this->config = Config::get_instance();
    $this->options = $this->config->get_module_options($this->slug);

    if (!\is_array($this->options)) {
      $this->options = [];
    }

    // Fill in missing options from defaults
    if (!empty($this->default_options) && \is_array($this->default_options)) {
      foreach ($this->default_options as $key => $default_value) {
        if (!\array_key_exists($key, $this->options)) {
          $this->options[$key] = $default_value;
        }
      }
    }

    if (!$this->is_enabled()) {
      return;
    }

    // Auto-initialize services if they exist
    $this->init_services();

    // Initialize REST Controller
    add_action('rest_api_init', [$this, 'init_rest_controller'], 5);

    // Enqueue assets
    if (is_admin()) {
      add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    } else {
      add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
  }

  /**
   * Initializes the module services
   * @return void
   * @since 1.0.0
   */
  public function init_services()
  {
    $service_class = "WPPluginBoilerplate\\Modules\\{$this->module_dir}\\Service";
    if (\class_exists($service_class)) {
      $this->service = new $service_class($this);
      if (\method_exists($this->service, 'init_service')) {
        $this->service->init_service();
      }
    }
  }

  /**
   * Initializes the REST Controller if available
   * @return void
   * @since 1.0.0
   */
  public function init_rest_controller()
  {
    if ($this->rest_controller !== null) {
      if (\defined('WP_DEBUG') && WP_DEBUG) {
        error_log("WPPluginBoilerplate: REST Controller for {$this->slug} already initialized");
      }
      return;
    }
    $possible_namespaces = [
      "WPPluginBoilerplate\\Modules\\{$this->module_dir}\\API\\RestController",
      "WPPluginBoilerplate\\Modules\\{$this->module_dir}\\RestController"
    ];

    foreach ($possible_namespaces as $class_name) {
      if (\class_exists($class_name)) {
        $this->rest_controller = new $class_name($this);
        if (\method_exists($this->rest_controller, 'register_routes')) {
          $this->rest_controller->register_routes();
        }
        break;
      }
    }
  }

  /**
   * Returns the REST Controller
   * @return mixed|null
   */
  public function get_rest_controller()
  {
    return $this->rest_controller ?? null;
  }

  /**
   * Module activation hook
   * @return void
   * @since 1.0.0
   */
  public function activate()
  {
    $this->config->set_option($this->slug, 'enabled', true);
  }

  /**
   * Module deactivation hook
   * @return void
   * @since 1.0.0
   */
  public function deactivate()
  {
    $this->config->set_option($this->slug, 'enabled', false);
  }

  public function uninstall()
  {
    $this->config->delete_module_options($this->slug);
  }

  /**
   * Enqueues module scripts and styles
   * @return void
   * @since 1.0.0
   */
  public function enqueue_scripts()
  {
    $css_file = "{$this->config->plugin_dir}modules/{$this->module_dir}/assets/css/{$this->slug}.css";
    $js_file = "{$this->config->plugin_dir}modules/{$this->module_dir}/assets/js/{$this->slug}.js";

    if (\file_exists($css_file)) {
      wp_enqueue_style("wp-plugin-boilerplate-{$this->slug}-style", "{$this->config->plugin_url}modules/{$this->module_dir}/assets/css/{$this->slug}.css");
    }

    if (\file_exists($js_file)) {
      wp_enqueue_script("wp-plugin-boilerplate-{$this->slug}-script", "{$this->config->plugin_url}modules/{$this->module_dir}/assets/js/{$this->slug}.js", ['jquery'], null, true);
      wp_localize_script("wp-plugin-boilerplate-{$this->slug}-script", 'wpbp_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
    }
  }

  /**
   * Enqueues module admin scripts
   * @return void
   * @since 1.0.0
   */
  public function enqueue_admin_scripts()
  {
    $admin_css_file = "{$this->config->plugin_dir}modules/{$this->module_dir}/assets/css/{$this->slug}-admin.css";
    $admin_js_file = "{$this->config->plugin_dir}modules/{$this->module_dir}/assets/js/{$this->slug}-admin.js";

    if (\file_exists($admin_css_file)) {
      wp_enqueue_style("wp-plugin-boilerplate-{$this->slug}-admin-style", "{$this->config->plugin_url}modules/{$this->module_dir}/assets/css/{$this->slug}-admin.css");
    }

    if (\file_exists($admin_js_file)) {
      wp_enqueue_script("wp-plugin-boilerplate-{$this->slug}-admin-script", "{$this->config->plugin_url}modules/{$this->module_dir}/assets/js/{$this->slug}-admin.js", ['jquery'], null, true);
      wp_localize_script("wp-plugin-boilerplate-{$this->slug}-admin-script", 'wpbp_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
    }
  }

  /**
   * Sanitizes options based on default_options
   * @param array $options
   * @return array
   * @since 1.0.0
   */
  public function sanitize_options($options)
  {
    $sanitized = [];
    $admin_settings = $this->admin_settings();

    // Fix PHP form arrays
    foreach ($options as $key => $value) {
      if (\preg_match('/^(\w+)\[(\d+)$/', $key, $matches)) {
        $real_key = $matches[1];
        $index = \intval($matches[2]);

        if (!isset($sanitized[$real_key])) {
          $sanitized[$real_key] = [];
        }

        $sanitized[$real_key][$index] = $value;
        continue;
      }

      $sanitized[$key] = $value;
    }

    // Regular sanitization
    foreach ($sanitized as $key => $value) {
      // List fields
      if (isset($admin_settings[$key]) && $admin_settings[$key]['type'] === 'list') {
        $sanitized[$key] = $this->sanitize_list_field($key, $value, $admin_settings[$key]);
        continue;
      }

      // Repeater fields
      if (isset($admin_settings[$key]) && $admin_settings[$key]['type'] === 'repeater') {
        $sanitized[$key] = $this->sanitize_repeater_field($key, $value, $admin_settings[$key]);
        continue;
      }

      // Boolean values
      if (isset($this->default_options[$key]) && \is_bool($this->default_options[$key])) {
        if (\is_string($value)) {
          $sanitized[$key] = $value === 'true' || $value === '1' || $value === 'on';
        } else {
          $sanitized[$key] = (bool) $value;
        }
      }
      // Text fields
      elseif (\is_string($value)) {
        $sanitized[$key] = sanitize_text_field($value);
      }
      // Numeric fields
      elseif (\is_numeric($value)) {
        $sanitized[$key] = \is_float($value + 0) ? (float) $value : (int) $value;
      }
      // Fallback
      else {
        $sanitized[$key] = $value;
      }
    }

    return $sanitized;
  }

  /**
   * Sanitizes a list field
   * @param string $key
   * @param mixed $value
   * @param array $field_config
   * @return array
   * @since 1.0.0
   */
  private function sanitize_list_field($key, $value, $field_config)
  {
    if (!\is_array($value)) {
      if (\is_string($value) && !empty($value)) {
        $value = \explode("\n", $value);
        $value = \array_map('trim', $value);
      } else {
        $value = [];
      }
    }

    $sanitized = [];
    $has_validation = isset($field_config['validate']) && isset($field_config['validate']['pattern']);
    $validation_pattern = $has_validation ? $field_config['validate']['pattern'] : '';

    foreach ($value as $item) {
      $item = \trim(sanitize_text_field($item));

      if (empty($item)) {
        continue;
      }

      if ($has_validation && !empty($validation_pattern)) {
        if (!\preg_match($validation_pattern, $item)) {
          if (\defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WPPluginBoilerplate: sanitize_list_field: Invalid entry {$item}");
          }
          continue;
        }
      }

      $sanitized[] = $item;
    }

    return $sanitized;
  }

  /**
   * Sanitizes repeater field data
   */
  private function sanitize_repeater_field($field_name, $value, $field_config)
  {
    if (!\is_array($value)) {
      return [];
    }

    $sanitized = [];
    foreach ($value as $item) {
      if (\is_array($item)) {
        $sanitized_item = [];
        foreach ($field_config['fields'] as $sub_field_id => $sub_field_config) {
          if (isset($item[$sub_field_id])) {
            $sanitized_item[$sub_field_id] = sanitize_text_field($item[$sub_field_id]);
          }
        }
        if (!empty($sanitized_item)) {
          $sanitized[] = $sanitized_item;
        }
      }
    }

    return $sanitized;
  }

  /**
   * Returns all module instances
   * @return array
   * @since 1.0.0
   */
  public static function get_all_modules(): array
  {
    return self::$modules;
  }

  /**
   * Returns a module instance by slug
   * @param string $slug
   * @return Module|null
   * @since 1.0.0
   */
  public static function get_instance($slug)
  {
    return self::$modules[$slug] ?? null;
  }

  /**
   * Returns the default options of the module
   * @return array
   * @since 1.0.0
   */
  public function get_default_options()
  {
    return $this->default_options;
  }

  /**
   * Returns all default options of all modules
   * @return array
   * @since 1.0.0
   */
  public static function get_all_default_options()
  {
    $defaults = [];
    foreach (self::$modules as $slug => $instance) {
      $module_defaults = $instance->get_default_options();
      if (!empty($module_defaults)) {
        $defaults[$slug] = $module_defaults;
      }
    }
    return $defaults;
  }

  public function get_name()
  {
    return $this->name;
  }

  public function get_version()
  {
    return $this->version;
  }

  public function get_slug()
  {
    return $this->slug;
  }

  public function get_description()
  {
    return $this->description;
  }

  public function get_author()
  {
    return $this->author;
  }

  /**
   * Checks if the module is enabled
   * @return bool
   * @since 1.0.0
   */
  public function is_enabled()
  {
    if (!isset($this->options['enabled'])) {
      return isset($this->default_options['enabled']) ?
        (bool) $this->default_options['enabled'] :
        true;
    }

    return (bool) $this->options['enabled'];
  }

  /**
   * Returns current module options
   * @return array
   * @since 1.0.0
   */
  public function get_options()
  {
    return $this->options ?: [];
  }

  /**
   * Checks if an option is enabled
   * @param string $key
   * @return bool
   */
  protected function is_option_enabled($key)
  {
    if (isset($this->options[$key])) {
      if (\is_string($this->options[$key])) {
        return $this->options[$key] === 'on' || $this->options[$key] === '1' || $this->options[$key] === 'true';
      }
      return (bool) $this->options[$key];
    }

    $defaults = $this->default_options;
    if (isset($defaults[$key])) {
      if (\is_string($defaults[$key])) {
        return $defaults[$key] === 'on' || $defaults[$key] === '1' || $defaults[$key] === 'true';
      }
      return (bool) $defaults[$key];
    }

    return false;
  }

  /**
   * Returns the URL to an asset in the module directory
   * @param string $path Relative path within the assets directory
   * @return string Full URL to the asset
   */
  public function get_asset_url($path)
  {
    return "{$this->config->plugin_url}modules/{$this->module_dir}/assets/{$path}";
  }

  /**
   * Returns the admin settings for this module
   * @return array
   */
  abstract public function admin_settings(): array;

  /**
   * Returns admin settings with dependency checks
   * @return array
   */
  public function get_admin_settings_with_dependencies(): array
  {
    $settings = $this->admin_settings();

    foreach ($settings as $key => &$setting) {
      if (isset($setting['dependencies'])) {
        $dependency_check = $this->check_dependencies($setting['dependencies']);
        $setting['dependency_status'] = $dependency_check;
      }

      // Process grouped fields recursively
      if ($setting['type'] === 'group' && isset($setting['fields'])) {
        foreach ($setting['fields'] as $field_key => &$field_setting) {
          if (isset($field_setting['dependencies'])) {
            $dependency_check = $this->check_dependencies($field_setting['dependencies']);
            $field_setting['dependency_status'] = $dependency_check;
          }
        }
      }
    }

    return $settings;
  }
}
