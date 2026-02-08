<?php

namespace WPPluginBoilerplate\Modules\A11y;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class A11y extends Module
{

  protected $enabled = true;
  protected $author = 'Mirko Schubert';
  protected $version = '1.1.0';
  protected $slug = 'a11y';

  public function get_name(): string
  {
    return __('Accessibility', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Accessibility module for WordPress.', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [
    'jquery',
  ];
  protected $default_options = [
    'enabled' => true,
    'aria_support' => true,
    'nav_keyboard' => true,
    'focus_elements' => true,
    'external_links' => true,
    'skip_link' => true,
    'scroll_top' => true,
    'fix_screenreader' => true,
    'underline_links' => true,
    'optimize_forms' => true,
    'stop_animations' => true,
    'text_highlight_bg' => '#3399ff',
    'text_highlight_color' => '#ffffff',
    'slider_nav_spacing' => false
  ];


  /**
   * Admin settings for the module
   * @return array
   * @package A11y
   * @since 1.0.0
   */
  public function admin_settings(): array
  {
    return [
      'basic_group' => [
        'type' => 'group',
        'title' => __('Basic Accessibility', 'wp-plugin-boilerplate'),
        'description' => __('Essential accessibility features for your website', 'wp-plugin-boilerplate'),
        'fields' => [
          'skip_link' => [
            'type' => 'toggle',
            'label' => __('Add a skip link to the page', 'wp-plugin-boilerplate'),
            'description' => '',
            'default' => $this->default_options['skip_link'],
          ],
          'scroll_top' => [
            'type' => 'toggle',
            'label' => __('Accessible scroll to top button', 'wp-plugin-boilerplate'),
            'description' => __('Adds an accessible scroll-to-top button.', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['scroll_top'],
          ],
        ]
      ],
      'navigation_group' => [
        'type' => 'group',
        'title' => __('Navigation & Focus', 'wp-plugin-boilerplate'),
        'description' => __('Keyboard navigation and focus management', 'wp-plugin-boilerplate'),
        'fields' => [
          'nav_keyboard' => [
            'type' => 'toggle',
            'label' => __('Make main navigation fully keyboard accessible', 'wp-plugin-boilerplate'),
            'description' => '',
            'default' => $this->default_options['nav_keyboard'],
          ],
          'focus_elements' => [
            'type' => 'toggle',
            'label' => __('Focus all clickable elements correctly', 'wp-plugin-boilerplate'),
            'description' => '',
            'default' => $this->default_options['focus_elements'],
          ],
          'external_links' => [
            'type' => 'toggle',
            'label' => __('Tag external links for assistive technology', 'wp-plugin-boilerplate'),
            'description' => '',
            'default' => $this->default_options['external_links'],
          ],
        ]
      ],
      'content_group' => [
        'type' => 'group',
        'title' => __('Content & ARIA', 'wp-plugin-boilerplate'),
        'description' => __('Content accessibility and ARIA enhancements', 'wp-plugin-boilerplate'),
        'fields' => [
          'aria_support' => [
            'type' => 'toggle',
            'label' => __('Add ARIA support to all relevant elements', 'wp-plugin-boilerplate'),
            'description' => '',
            'default' => $this->default_options['aria_support'],
          ],
          'optimize_forms' => [
            'type' => 'toggle',
            'label' => __('Optimize forms for accessibility', 'wp-plugin-boilerplate'),
            'description' => __('Supports comment form, Minimal Contact Form and Forminator', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['optimize_forms'],
          ],
          'fix_screenreader' => [
            'type' => 'toggle',
            'label' => __('Fix screenreader text', 'wp-plugin-boilerplate'),
            'description' => '',
            'default' => $this->default_options['fix_screenreader'],
          ],
        ]
      ],
      'visual_group' => [
        'type' => 'group',
        'title' => __('Visual & Animation', 'wp-plugin-boilerplate'),
        'description' => __('Visual accessibility and animation controls', 'wp-plugin-boilerplate'),
        'fields' => [
          'stop_animations' => [
            'type' => 'toggle',
            'label' => __('Stop animations if users have preference set for no animations', 'wp-plugin-boilerplate'),
            'description' => __('Respects the prefers-reduced-motion setting on user devices', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['stop_animations'],
          ],
          'underline_links' => [
            'type' => 'toggle',
            'label' => __('Underline all links except headlines and social icons', 'wp-plugin-boilerplate'),
            'description' => '',
            'default' => $this->default_options['underline_links'],
          ],
          'text_highlight_bg' => [
            'type' => 'color',
            'label' => __('Text selection background color', 'wp-plugin-boilerplate'),
            'description' => __('Background color for selected text', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['text_highlight_bg'],
          ],
          'text_highlight_color' => [
            'type' => 'color',
            'label' => __('Text selection text color', 'wp-plugin-boilerplate'),
            'description' => __('Text color for selected text', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['text_highlight_color'],
          ],
          'slider_nav_spacing' => [
            'type' => 'toggle',
            'label' => __('Add spacing to slider navigation elements', 'wp-plugin-boilerplate'),
            'description' => __('Improves accessibility by adding space between slider controls', 'wp-plugin-boilerplate'),
            'default' => $this->default_options['slider_nav_spacing'],
          ],
        ]
      ]
    ];
  }


}