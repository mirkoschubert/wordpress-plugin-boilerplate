<?php

namespace WPPluginBoilerplate\Modules\CustomPostTypes;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class CustomPostTypes extends Module
{
  protected $enabled = false;
  protected $author = 'Mirko Schubert';
  protected $version = '1.0.0';
  protected $slug = 'customposttypes';

  public function get_name(): string
  {
    return __('Custom Post Types', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Register custom post types with optional ACF field groups.', 'wp-plugin-boilerplate');
  }

  protected $dependencies = [];

  protected $default_options = [
    'enabled' => false,
    'enable_jobs' => false,
    'jobs_parent_page' => 0,
    'jobs_supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
    'enable_job_categories' => false,
    'enable_job_locations' => false,
  ];

  /**
   * Admin settings for the module
   */
  public function admin_settings(): array
  {
    return [
      'jobs_group' => [
        'type' => 'group',
        'title' => __('Jobs', 'wp-plugin-boilerplate'),
        'description' => __('Configure the Jobs custom post type with parent page, taxonomies, and ACF fields', 'wp-plugin-boilerplate'),
        'fields' => [
          'enable_jobs' => [
            'type' => 'toggle',
            'label' => __('Enable Jobs', 'wp-plugin-boilerplate'),
            'description' => __('Register the Jobs custom post type with taxonomies and ACF fields.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['enable_jobs'],
          ],
          'jobs_parent_page' => [
            'type' => 'page_select',
            'label' => __('Parent Page', 'wp-plugin-boilerplate'),
            'description' => __('Select the page under which jobs will be organized. Job URLs will be: /parent-slug/job-title/', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['jobs_parent_page'],
            'depends_on' => ['enable_jobs' => true],
          ],
          'jobs_supports' => [
            'type' => 'multi_select',
            'label' => __('Jobs Supports', 'wp-plugin-boilerplate'),
            'description' => __('Post editor features to enable', 'wp-plugin-boilerplate'),
            'options' => [
              'title' => __('Title', 'wp-plugin-boilerplate'),
              'editor' => __('Editor', 'wp-plugin-boilerplate'),
              'thumbnail' => __('Featured Image', 'wp-plugin-boilerplate'),
              'excerpt' => __('Excerpt', 'wp-plugin-boilerplate'),
              'revisions' => __('Revisions', 'wp-plugin-boilerplate'),
              'author' => __('Author', 'wp-plugin-boilerplate'),
              'comments' => __('Comments', 'wp-plugin-boilerplate'),
              'custom-fields' => __('Custom Fields', 'wp-plugin-boilerplate'),
            ],
            'default' => $this->default_options['jobs_supports'],
            'depends_on' => ['enable_jobs' => true],
          ],
          'enable_job_categories' => [
            'type' => 'toggle',
            'label' => __('Enable Job Categories', 'wp-plugin-boilerplate'),
            'description' => __('Register Job Category taxonomy. Categories will be included in URLs: /parent/category/job-title/', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['enable_job_categories'],
            'depends_on' => ['enable_jobs' => true],
          ],
          'enable_job_locations' => [
            'type' => 'toggle',
            'label' => __('Enable Job Locations', 'wp-plugin-boilerplate'),
            'description' => __('Register Job Location taxonomy for job locations', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['enable_job_locations'],
            'depends_on' => ['enable_jobs' => true],
          ],
        ],
      ],
    ];
  }
}
