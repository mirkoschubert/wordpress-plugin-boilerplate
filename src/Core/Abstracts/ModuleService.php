<?php

namespace WPPluginBoilerplate\Core\Abstracts;

use WPPluginBoilerplate\Core\Interfaces\ServiceInterface;
use WPPluginBoilerplate\Core\Config;
use WPPluginBoilerplate\Core\Traits\DependencyChecker;

abstract class ModuleService implements ServiceInterface
{
  use DependencyChecker;
  /**
   * Reference to the module
   * @var Module
   */
  protected $module;

  /**
   * Configuration object
   * @var Config
   */
  protected $config;

  /**
   * Module options
   * @var array
   */
  protected $options;

  /**
   * Constructor
   * @param Module $module
   * @since 1.0.0
   */
  public function __construct(Module $module)
  {
    $this->module = $module;
    $this->config = Config::get_instance();
    $this->options = $module->get_options();
  }

  /**
   * Initializes all module services (to be overridden by child classes)
   * @return void
   * @since 1.0.0
   */
  public function init_service()
  {
    // Override in child classes
  }

  /**
   * Enqueues assets (to be overridden by child classes)
   * @return void
   * @since 1.0.0
   */
  public function enqueue_assets()
  {
    // Override in child classes
  }

  /**
   * Returns the module slug
   * @return string
   * @since 1.0.0
   */
  public function get_module_slug()
  {
    return $this->module->get_slug();
  }

  /**
   * Returns the module options
   * @return array
   * @since 1.0.0
   */
  public function get_module_options()
  {
    return $this->options;
  }

  /**
   * Returns a specific module option
   * @param string $key
   * @return mixed
   * @since 1.0.0
   */
  public function get_module_option($key)
  {
    if (isset($this->options[$key])) {
      return $this->options[$key];
    }

    $defaults = $this->module->get_default_options();
    return $defaults[$key] ?? null;
  }

  /**
   * Checks if a specific option is enabled
   * @param string $key
   * @return bool
   * @since 1.0.0
   */
  protected function is_option_enabled($key)
  {
    if (isset($this->options[$key])) {
      return (bool) $this->options[$key];
    }

    $defaults = $this->module->get_default_options();
    return isset($defaults[$key]) ? (bool) $defaults[$key] : false;
  }

  /**
   * Checks if a specific option is empty
   * @param mixed $key
   * @return bool
   * @since 1.0.0
   */
  protected function is_option_empty($key)
  {
    return !isset($this->options[$key]) || empty($this->options[$key]) || $this->options[$key] === '';
  }
}
