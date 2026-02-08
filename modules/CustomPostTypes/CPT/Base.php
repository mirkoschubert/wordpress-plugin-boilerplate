<?php

namespace WPPluginBoilerplate\Modules\CustomPostTypes\CPT;

use WPPluginBoilerplate\Core\Abstracts\Module;

/**
 * Abstract Base Class for Custom Post Types
 * Handles parent page selection, permalink structure, and post parent assignment
 *
 * @package WPPluginBoilerplate\Modules\CustomPostTypes\CPT
 * @since 1.0.0
 */
abstract class Base
{
  /**
   * Post type slug (e.g., 'job')
   * @var string
   */
  protected $post_type;

  /**
   * Plural key for options (e.g., 'jobs')
   * @var string
   */
  protected $plural_key;

  /**
   * Parent page ID
   * @var int
   */
  protected $parent_id = 0;

  /**
   * Module reference
   * @var Module
   */
  protected $module;

  /**
   * Constructor
   *
   * @param string $post_type Post type slug
   * @param string $plural_key Plural key for options
   * @param Module $module Module reference
   */
  public function __construct(string $post_type, string $plural_key, Module $module)
  {
    $this->post_type = $post_type;
    $this->plural_key = $plural_key;
    $this->module = $module;

    // Load parent ID from module options
    $this->load_parent_id();

    // Register all hooks
    $this->register_hooks();
  }

  /**
   * Load parent ID from module options
   *
   * @return void
   */
  protected function load_parent_id(): void
  {
    $option_key = $this->plural_key . '_parent_page';
    $options = $this->module->get_options();
    $parent_id = $options[$option_key] ?? 0;

    if ($parent_id > 0) {
      $this->set_parent_id($parent_id);
    }
  }

  /**
   * Get option value from module
   *
   * @param string $key Option key
   * @param mixed $default Default value
   * @return mixed Option value
   */
  protected function get_module_option(string $key, $default = null)
  {
    $options = $this->module->get_options();
    return $options[$key] ?? $default;
  }

  /**
   * Set parent ID
   *
   * @param int $id Parent page ID
   * @return void
   */
  public function set_parent_id(int $id): void
  {
    $this->parent_id = $id;
  }

  /**
   * Register WordPress hooks
   *
   * @return void
   */
  protected function register_hooks(): void
  {
    // Register post type
    add_action('init', [$this, 'register_post_type'], 10);

    // Register taxonomies if method exists
    if (method_exists($this, 'register_taxonomies')) {
      add_action('init', [$this, 'register_taxonomies'], 5);
    }

    // Permalink and rewrite adjustments
    add_filter('post_type_link', [$this, 'change_link'], 10, 2);
    add_filter('generate_rewrite_rules', [$this, 'rewrite_rules']);

    // Set post parent
    add_filter('wp_insert_post_data', [$this, 'set_post_parent'], 99, 2);
  }

  /**
   * Register the custom post type
   * Must be implemented by child classes
   *
   * @return void
   */
  abstract public function register_post_type(): void;

  /**
   * Register taxonomies for this post type
   * Optional - can be implemented by child classes
   *
   * @return void
   */
  abstract public function register_taxonomies(): void;

  /**
   * Define rewrite rules for the post type
   * Must be implemented by child classes
   *
   * @param \WP_Rewrite $wp_rewrite WordPress rewrite object
   * @return \WP_Rewrite Modified rewrite object
   */
  abstract public function rewrite_rules(\WP_Rewrite $wp_rewrite): \WP_Rewrite;

  /**
   * Modify permalink structure for posts
   * Must be implemented by child classes
   *
   * @param string $permalink Original permalink
   * @param \WP_Post $post Post object
   * @return string Modified permalink
   */
  abstract public function change_link(string $permalink, \WP_Post $post): string;

  /**
   * Set post parent for all posts of this type
   *
   * @param array $data Post data
   * @param array $postarr Raw post data
   * @return array Modified post data
   */
  public function set_post_parent(array $data, array $postarr): array
  {
    if (isset($postarr['post_type']) && $postarr['post_type'] === $this->post_type && $this->parent_id > 0) {
      $data['post_parent'] = $this->parent_id;
    }

    return $data;
  }

  /**
   * Update parent IDs for all existing posts
   * Called when parent page selection changes
   *
   * @return void
   */
  public function update_existing_post_parents(): void
  {
    if ($this->parent_id <= 0) {
      return;
    }

    $args = [
      'post_type' => $this->post_type,
      'posts_per_page' => -1,
      'post_status' => 'any',
    ];

    $posts = get_posts($args);

    foreach ($posts as $post) {
      if ($post->post_parent != $this->parent_id) {
        wp_update_post([
          'ID' => $post->ID,
          'post_parent' => $this->parent_id,
        ]);
      }
    }
  }

  /**
   * Get the base path for this CPT including parent page
   *
   * @param string|null $custom_slug Custom slug to use instead of plural_key
   * @return string Base path
   */
  protected function get_base_path(?string $custom_slug = null): string
  {
    $parent_path = $this->get_parent_path();
    $slug = $custom_slug ?: $this->plural_key;

    return !empty($parent_path) ? $parent_path . '/' . $slug : $slug;
  }

  /**
   * Get the full path of the parent page
   *
   * @return string Parent page URI or empty string
   */
  protected function get_parent_path(): string
  {
    if ($this->parent_id > 0) {
      return trim(get_page_uri($this->parent_id), '/');
    }

    return '';
  }

  /**
   * Get the slug of the parent page
   *
   * @return string Parent page slug or empty string
   */
  protected function get_parent_slug(): string
  {
    if ($this->parent_id > 0) {
      $parent = get_post($this->parent_id);
      if ($parent) {
        return $parent->post_name;
      }
    }

    return '';
  }

  /**
   * Handle options update
   * Called when module options are saved
   *
   * @return void
   */
  public function on_options_update(): void
  {
    // Reload parent ID
    $this->load_parent_id();

    // Update existing posts
    $this->update_existing_post_parents();

    // Flush rewrite rules
    flush_rewrite_rules(true);
  }
}
