<?php

namespace WPPluginBoilerplate\Modules\CustomPostTypes\CPT;

/**
 * Jobs Custom Post Type
 *
 * Handles registration of the Jobs CPT with:
 * - Dynamic parent page-based permalinks
 * - Optional category-based URL structure
 * - Conditional taxonomy registration
 *
 * @package WPPluginBoilerplate\Modules\CustomPostTypes\CPT
 * @since 1.0.0
 */
class Jobs extends Base
{
  /**
   * Register the Jobs post type
   *
   * @return void
   */
  public function register_post_type(): void
  {
    $labels = [
      'name' => _x('Jobs', 'Post type general name', 'wp-plugin-boilerplate'),
      'singular_name' => _x('Job', 'Post type singular name', 'wp-plugin-boilerplate'),
      'menu_name' => _x('Jobs', 'Admin Menu text', 'wp-plugin-boilerplate'),
      'name_admin_bar' => _x('Job', 'Add New on Toolbar', 'wp-plugin-boilerplate'),
      'add_new' => __('Add New', 'wp-plugin-boilerplate'),
      'add_new_item' => __('Add New Job', 'wp-plugin-boilerplate'),
      'new_item' => __('New Job', 'wp-plugin-boilerplate'),
      'edit_item' => __('Edit Job', 'wp-plugin-boilerplate'),
      'view_item' => __('View Job', 'wp-plugin-boilerplate'),
      'all_items' => __('All Jobs', 'wp-plugin-boilerplate'),
      'search_items' => __('Search Jobs', 'wp-plugin-boilerplate'),
      'parent_item_colon' => __('Parent Jobs:', 'wp-plugin-boilerplate'),
      'not_found' => __('No jobs found.', 'wp-plugin-boilerplate'),
      'not_found_in_trash' => __('No jobs found in Trash.', 'wp-plugin-boilerplate'),
      'featured_image' => _x('Job Featured Image', 'Overrides the "Featured Image" phrase', 'wp-plugin-boilerplate'),
      'set_featured_image' => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'wp-plugin-boilerplate'),
      'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'wp-plugin-boilerplate'),
      'use_featured_image' => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'wp-plugin-boilerplate'),
      'archives' => _x('Job archives', 'The post type archive label used in nav menus', 'wp-plugin-boilerplate'),
      'insert_into_item' => _x('Insert into job', 'Overrides the "Insert into post"/"Insert into page" phrase', 'wp-plugin-boilerplate'),
      'uploaded_to_this_item' => _x('Uploaded to this job', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'wp-plugin-boilerplate'),
      'filter_items_list' => _x('Filter jobs list', 'Screen reader text for the filter links', 'wp-plugin-boilerplate'),
      'items_list_navigation' => _x('Jobs list navigation', 'Screen reader text for the pagination', 'wp-plugin-boilerplate'),
      'items_list' => _x('Jobs list', 'Screen reader text for the items list', 'wp-plugin-boilerplate'),
    ];

    // Get supports from options
    $supports = $this->get_module_option('jobs_supports', ['title', 'editor', 'thumbnail', 'excerpt', 'revisions']);
    if (empty($supports)) {
      $supports = ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'];
    }

    $args = [
      'labels' => $labels,
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'rewrite' => [
        'slug' => $this->get_parent_path() ?: 'jobs',
        'with_front' => false,
        'pages' => false,
      ],
      'capability_type' => 'post',
      'has_archive' => false, // Handled via Breakdance pages
      'hierarchical' => false,
      'menu_position' => 20,
      'menu_icon' => 'dashicons-businessman',
      'supports' => $supports,
      'show_in_rest' => true, // Enable Gutenberg editor
    ];

    // Add taxonomies if enabled
    $taxonomies = [];
    if ($this->get_module_option('enable_job_categories', true)) {
      $taxonomies[] = 'job_category';
    }
    if ($this->get_module_option('enable_job_locations', false)) {
      $taxonomies[] = 'job_location';
    }
    if (!empty($taxonomies)) {
      $args['taxonomies'] = $taxonomies;
    }

    register_post_type($this->post_type, $args);
  }

  /**
   * Register taxonomies for Jobs CPT
   * Conditionally registers based on module settings
   *
   * @return void
   */
  public function register_taxonomies(): void
  {
    // Register Job Category taxonomy if enabled
    if ($this->get_module_option('enable_job_categories', true)) {
      $category_labels = [
        'name' => _x('Job Categories', 'taxonomy general name', 'wp-plugin-boilerplate'),
        'singular_name' => _x('Job Category', 'taxonomy singular name', 'wp-plugin-boilerplate'),
        'search_items' => __('Search Job Categories', 'wp-plugin-boilerplate'),
        'all_items' => __('All Job Categories', 'wp-plugin-boilerplate'),
        'parent_item' => __('Parent Job Category', 'wp-plugin-boilerplate'),
        'parent_item_colon' => __('Parent Job Category:', 'wp-plugin-boilerplate'),
        'edit_item' => __('Edit Job Category', 'wp-plugin-boilerplate'),
        'update_item' => __('Update Job Category', 'wp-plugin-boilerplate'),
        'add_new_item' => __('Add New Job Category', 'wp-plugin-boilerplate'),
        'new_item_name' => __('New Job Category Name', 'wp-plugin-boilerplate'),
        'menu_name' => __('Categories', 'wp-plugin-boilerplate'),
      ];

      register_taxonomy('job_category', [$this->post_type], [
        'hierarchical' => false,
        'labels' => $category_labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var' => true,
        'rewrite' => false, // CPT handles rewriting
        'publicly_queryable' => false,
        'show_in_quick_edit' => false,
      ]);
    }

    // Register Job Location taxonomy if enabled
    if ($this->get_module_option('enable_job_locations', false)) {
      $location_labels = [
        'name' => _x('Job Locations', 'taxonomy general name', 'wp-plugin-boilerplate'),
        'singular_name' => _x('Job Location', 'taxonomy singular name', 'wp-plugin-boilerplate'),
        'search_items' => __('Search Job Locations', 'wp-plugin-boilerplate'),
        'popular_items' => __('Popular Job Locations', 'wp-plugin-boilerplate'),
        'all_items' => __('All Job Locations', 'wp-plugin-boilerplate'),
        'edit_item' => __('Edit Job Location', 'wp-plugin-boilerplate'),
        'update_item' => __('Update Job Location', 'wp-plugin-boilerplate'),
        'add_new_item' => __('Add New Job Location', 'wp-plugin-boilerplate'),
        'new_item_name' => __('New Job Location Name', 'wp-plugin-boilerplate'),
        'menu_name' => __('Locations', 'wp-plugin-boilerplate'),
      ];

      register_taxonomy('job_location', [$this->post_type], [
        'hierarchical' => false,
        'labels' => $location_labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'job-location'],
        'show_in_rest' => true,
      ]);
    }
  }

  /**
   * Define rewrite rules for Jobs CPT
   * Includes category-based URLs if categories are enabled
   *
   * @param \WP_Rewrite $wp_rewrite WordPress rewrite object
   * @return \WP_Rewrite Modified rewrite object
   */
  public function rewrite_rules(\WP_Rewrite $wp_rewrite): \WP_Rewrite
  {
    $rules = [];
    $base_path = $this->get_parent_path() ?: 'jobs';

    // Add category-based rules if categories are enabled
    $categories_enabled = $this->get_module_option('enable_job_categories', true);

    if ($categories_enabled) {
      $terms = get_terms([
        'taxonomy' => 'job_category',
        'hide_empty' => false,
      ]);

      if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
          $rules[$base_path . '/' . $term->slug . '/([^/]*)$'] =
            'index.php?post_type=' . $this->post_type . '&job_category=' . $term->slug . '&name=$matches[1]';
        }
      }
    }

    // Fallback rule without category (always needed)
    $rules[$base_path . '/([^/]*)$'] = 'index.php?post_type=' . $this->post_type . '&name=$matches[1]';

    $wp_rewrite->rules = $rules + $wp_rewrite->rules;
    return $wp_rewrite;
  }

  /**
   * Modify permalink structure for job posts
   * Includes parent page path and optional category
   *
   * @param string $permalink Original permalink
   * @param \WP_Post $post Post object
   * @return string Modified permalink
   */
  public function change_link(string $permalink, \WP_Post $post): string
  {
    if ($post->post_type !== $this->post_type) {
      return $permalink;
    }

    $base_path = $this->get_parent_path() ?: 'jobs';
    $categories_enabled = $this->get_module_option('enable_job_categories', true);

    // If categories are disabled, use simple permalink
    if (!$categories_enabled) {
      return get_home_url() . '/' . $base_path . '/' . $post->post_name;
    }

    // Try to get job category
    $terms = get_the_terms($post, 'job_category');
    $term_slug = '';

    if (!empty($terms) && !is_wp_error($terms)) {
      // Use first category
      $term_slug = $terms[0]->slug;
    }

    // Build permalink with or without category
    if (!empty($term_slug)) {
      $permalink = get_home_url() . '/' . $base_path . '/' . $term_slug . '/' . $post->post_name;
    } else {
      $permalink = get_home_url() . '/' . $base_path . '/' . $post->post_name;
    }

    return $permalink;
  }
}
