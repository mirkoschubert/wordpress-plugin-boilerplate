<?php

namespace WPPluginBoilerplate\Modules\CustomPostTypes;

use WPPluginBoilerplate\Core\Abstracts\ModuleService;
use WPPluginBoilerplate\Modules\CustomPostTypes\CPT\Jobs as JobsCPT;

class Service extends ModuleService
{
  /**
   * Jobs CPT instance
   * @var JobsCPT|null
   */
  private $jobs_cpt = null;

  /**
   * Initializes all module services
   * @return void
   * @since 1.1.0
   */
  public function init_service()
  {
    // Register Jobs CPT if enabled
    if ($this->is_option_enabled('enable_jobs')) {
      // Initialize Jobs CPT (PSR-4 autoloading handles class loading)
      $this->jobs_cpt = new JobsCPT('job', 'jobs', $this->module);

      // Load and set parent ID
      $parent_id = $this->get_module_option('jobs_parent_page');
      if ($parent_id > 0) {
        $this->jobs_cpt->set_parent_id($parent_id);
      }

      // Register ACF fields if ACF is available
      if (function_exists('acf_add_local_field_group')) {
        add_action('acf/init', [$this, 'register_jobs_acf_fields']);
      }

      // Hook for options update (flush rewrite rules)
      add_action('wp_plugin_boilerplate_module_options_saved_customposttypes', [$this, 'on_options_update'], 10, 2);
    }
  }

  /**
   * Handle module options update
   * Called when settings are saved
   *
   * @param array $new_options New options
   * @param array $old_options Old options
   * @return void
   * @since 1.0.0
   */
  public function on_options_update($new_options, $old_options)
  {
    // Reload parent ID
    $parent_id = $new_options['jobs_parent_page'] ?? 0;
    if (isset($this->jobs_cpt)) {
      $this->jobs_cpt->set_parent_id($parent_id);
      $this->jobs_cpt->update_existing_post_parents();
    }

    // Flush rewrite rules
    flush_rewrite_rules(true);
  }


  /**
   * Register ACF fields for Jobs CPT
   * Programmatic registration ensures fields are translatable
   * @return void
   * @since 1.1.0
   */
  public function register_jobs_acf_fields()
  {
    if (!function_exists('acf_add_local_field_group')) {
      return;
    }

    acf_add_local_field_group([
      'key' => 'group_wpbp_jobs',
      'title' => __('Job Details', 'wp-plugin-boilerplate'),
      'fields' => [
        [
          'key' => 'field_job_start_date',
          'label' => __('Start Date', 'wp-plugin-boilerplate'),
          'name' => 'start_date',
          'type' => 'date_picker',
          'instructions' => __('When should this position start?', 'wp-plugin-boilerplate'),
          'required' => 0,
          'display_format' => 'd/m/Y',
          'return_format' => 'Y-m-d',
          'first_day' => 1,
        ],
        [
          'key' => 'field_job_application_deadline',
          'label' => __('Application Deadline', 'wp-plugin-boilerplate'),
          'name' => 'application_deadline',
          'type' => 'date_picker',
          'instructions' => __('Last day to apply for this position', 'wp-plugin-boilerplate'),
          'required' => 0,
          'display_format' => 'd/m/Y',
          'return_format' => 'Y-m-d',
          'first_day' => 1,
        ],
        [
          'key' => 'field_job_type',
          'label' => __('Job Type', 'wp-plugin-boilerplate'),
          'name' => 'job_type',
          'type' => 'select',
          'instructions' => __('Type of employment', 'wp-plugin-boilerplate'),
          'required' => 1,
          'choices' => [
            'full-time' => __('Full-time', 'wp-plugin-boilerplate'),
            'part-time' => __('Part-time', 'wp-plugin-boilerplate'),
            'contract' => __('Contract', 'wp-plugin-boilerplate'),
            'freelance' => __('Freelance', 'wp-plugin-boilerplate'),
            'internship' => __('Internship', 'wp-plugin-boilerplate'),
          ],
          'default_value' => 'full-time',
          'allow_null' => 0,
          'multiple' => 0,
          'ui' => 1,
          'return_format' => 'value',
        ],
        [
          'key' => 'field_job_experience_level',
          'label' => __('Experience Level', 'wp-plugin-boilerplate'),
          'name' => 'experience_level',
          'type' => 'select',
          'instructions' => __('Required experience level', 'wp-plugin-boilerplate'),
          'required' => 0,
          'choices' => [
            'entry' => __('Entry Level', 'wp-plugin-boilerplate'),
            'junior' => __('Junior', 'wp-plugin-boilerplate'),
            'mid' => __('Mid-Level', 'wp-plugin-boilerplate'),
            'senior' => __('Senior', 'wp-plugin-boilerplate'),
            'lead' => __('Lead/Principal', 'wp-plugin-boilerplate'),
            'executive' => __('Executive', 'wp-plugin-boilerplate'),
          ],
          'default_value' => 'mid',
          'allow_null' => 1,
          'multiple' => 0,
          'ui' => 1,
          'return_format' => 'value',
        ],
        [
          'key' => 'field_job_salary_min',
          'label' => __('Salary Range (Min)', 'wp-plugin-boilerplate'),
          'name' => 'salary_min',
          'type' => 'number',
          'instructions' => __('Minimum salary (optional)', 'wp-plugin-boilerplate'),
          'required' => 0,
          'min' => 0,
          'step' => 1000,
        ],
        [
          'key' => 'field_job_salary_max',
          'label' => __('Salary Range (Max)', 'wp-plugin-boilerplate'),
          'name' => 'salary_max',
          'type' => 'number',
          'instructions' => __('Maximum salary (optional)', 'wp-plugin-boilerplate'),
          'required' => 0,
          'min' => 0,
          'step' => 1000,
        ],
        [
          'key' => 'field_job_remote_work',
          'label' => __('Remote Work', 'wp-plugin-boilerplate'),
          'name' => 'remote_work',
          'type' => 'select',
          'instructions' => __('Remote work options', 'wp-plugin-boilerplate'),
          'required' => 0,
          'choices' => [
            'onsite' => __('On-site only', 'wp-plugin-boilerplate'),
            'hybrid' => __('Hybrid', 'wp-plugin-boilerplate'),
            'remote' => __('Fully remote', 'wp-plugin-boilerplate'),
          ],
          'default_value' => 'onsite',
          'allow_null' => 0,
          'multiple' => 0,
          'ui' => 1,
          'return_format' => 'value',
        ],
        [
          'key' => 'field_job_contact_email',
          'label' => __('Contact Email', 'wp-plugin-boilerplate'),
          'name' => 'contact_email',
          'type' => 'email',
          'instructions' => __('Email address for applications (optional, defaults to admin email)', 'wp-plugin-boilerplate'),
          'required' => 0,
        ],
        [
          'key' => 'field_job_application_url',
          'label' => __('Application URL', 'wp-plugin-boilerplate'),
          'name' => 'application_url',
          'type' => 'url',
          'instructions' => __('External application URL (if applications are handled elsewhere)', 'wp-plugin-boilerplate'),
          'required' => 0,
        ],
      ],
      'location' => [
        [
          [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'job',
          ],
        ],
      ],
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => true,
      'description' => __('Job posting details and metadata', 'wp-plugin-boilerplate'),
    ]);
  }
}
