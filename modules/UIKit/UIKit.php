<?php

namespace WPPluginBoilerplate\Modules\UIKit;

use WPPluginBoilerplate\Core\Abstracts\Module;

final class UIKit extends Module
{

  protected $enabled = true;
  protected $dev_only = true;
  protected $author = 'Mirko Schubert';
  protected $version = '1.0.0';
  protected $slug = 'uikit';

  public function get_name(): string
  {
    return __('UI Kit', 'wp-plugin-boilerplate');
  }

  public function get_description(): string
  {
    return __('Reference module for all form field types, repeaters and dependencies', 'wp-plugin-boilerplate');
  }
  protected $dependencies = [];
  protected $default_options = [
    'enabled' => true,
  ];

  /**
   * Admin settings for the UI Kit module
   * @return array
   * @package UIKit
   * @since 1.0.0
   */
  public function admin_settings(): array {
    return [

      // 1. BASIC FORM FIELDS
      'basic_fields' => [
        'type' => 'group',
        'title' => __('Basic Form Fields', 'wp-plugin-boilerplate'),
        'description' => __('All available field types', 'wp-plugin-boilerplate'),
        'fields' => [
          'test_text' => [
            'type' => 'text',
            'label' => __('Text Field', 'wp-plugin-boilerplate'),
            'description' => __('A simple text input field', 'wp-plugin-boilerplate'),
            'default' => 'Default text value',
            'validate' => [
              'required' => false,
              'min_length' => 3,
              'max_length' => 100
            ]
          ],
          'test_textarea' => [
            'type' => 'textarea',
            'label' => __('Textarea Field', 'wp-plugin-boilerplate'),
            'description' => __('Multi-line text input', 'wp-plugin-boilerplate'),
            'default' => "Default textarea\nwith multiple lines",
          ],
          'test_number' => [
            'type' => 'number',
            'label' => __('Number Field', 'wp-plugin-boilerplate'),
            'description' => __('Numeric input with min/max validation', 'wp-plugin-boilerplate'),
            'default' => 42,
            'validate' => [
              'min' => 0,
              'max' => 1000
            ]
          ],
          'test_toggle' => [
            'type' => 'toggle',
            'label' => __('Toggle Field', 'wp-plugin-boilerplate'),
            'description' => __('Boolean on/off switch', 'wp-plugin-boilerplate'),
            'default' => true,
          ],
          'test_select' => [
            'type' => 'select',
            'label' => __('Select Field', 'wp-plugin-boilerplate'),
            'description' => __('Single selection dropdown', 'wp-plugin-boilerplate'),
            'default' => 'option2',
            'options' => [
              'option1' => __('First Option', 'wp-plugin-boilerplate'),
              'option2' => __('Second Option', 'wp-plugin-boilerplate'),
              'option3' => __('Third Option', 'wp-plugin-boilerplate'),
              'option4' => __('Fourth Option', 'wp-plugin-boilerplate'),
            ]
          ],
          'test_multi_select' => [
            'type' => 'multi_select',
            'label' => __('Multi-Select Field', 'wp-plugin-boilerplate'),
            'description' => __('Multiple selection dropdown', 'wp-plugin-boilerplate'),
            'default' => ['option1', 'option3'],
            'options' => [
              'option1' => __('Option 1', 'wp-plugin-boilerplate'),
              'option2' => __('Option 2', 'wp-plugin-boilerplate'),
              'option3' => __('Option 3', 'wp-plugin-boilerplate'),
              'option4' => __('Option 4', 'wp-plugin-boilerplate'),
              'option5' => __('Option 5', 'wp-plugin-boilerplate'),
            ]
          ],
          'test_color' => [
            'type' => 'color',
            'label' => __('Color Field', 'wp-plugin-boilerplate'),
            'description' => __('Color picker with hex value', 'wp-plugin-boilerplate'),
            'default' => '#007cba',
          ],
          'test_image' => [
            'type' => 'image',
            'label' => __('Image Field', 'wp-plugin-boilerplate'),
            'description' => __('Image upload via media library', 'wp-plugin-boilerplate'),
            'default' => '',
          ],
        ]
      ],

      // 2. REPEATERS
      'repeater_fields' => [
        'type' => 'group',
        'title' => __('Repeater Fields', 'wp-plugin-boilerplate'),
        'description' => __('Simple and complex repeater examples', 'wp-plugin-boilerplate'),
        'fields' => [
          'simple_repeater' => [
            'type' => 'repeater',
            'label' => __('Simple Repeater', 'wp-plugin-boilerplate'),
            'description' => __('Basic repeater with text fields', 'wp-plugin-boilerplate'),
            'fields' => [
              'title' => [
                'type' => 'text',
                'label' => __('Item Title', 'wp-plugin-boilerplate'),
                'default' => 'New Item'
              ],
              'description' => [
                'type' => 'textarea',
                'label' => __('Item Description', 'wp-plugin-boilerplate'),
                'default' => 'Item description here...'
              ]
            ]
          ],
          'complex_repeater' => [
            'type' => 'repeater',
            'label' => __('Complex Repeater', 'wp-plugin-boilerplate'),
            'description' => __('Repeater with multiple field types and dependencies', 'wp-plugin-boilerplate'),
            'fields' => [
              'name' => [
                'type' => 'text',
                'label' => __('Name', 'wp-plugin-boilerplate'),
                'default' => 'Complex Item'
              ],
              'type' => [
                'type' => 'select',
                'label' => __('Type', 'wp-plugin-boilerplate'),
                'default' => 'type_a',
                'options' => [
                  'type_a' => __('Type A', 'wp-plugin-boilerplate'),
                  'type_b' => __('Type B', 'wp-plugin-boilerplate'),
                  'type_c' => __('Type C', 'wp-plugin-boilerplate'),
                ]
              ],
              'enabled' => [
                'type' => 'toggle',
                'label' => __('Enable Item', 'wp-plugin-boilerplate'),
                'default' => true
              ],
              'value' => [
                'type' => 'number',
                'label' => __('Value (Type A only)', 'wp-plugin-boilerplate'),
                'default' => 25,
                'depends_on' => [
                  'type' => 'type_a'
                ],
                'validate' => [
                  'min' => 1,
                  'max' => 100
                ]
              ],
              'color' => [
                'type' => 'color',
                'label' => __('Color (when enabled)', 'wp-plugin-boilerplate'),
                'default' => '#ff0000',
                'depends_on' => [
                  'enabled' => true
                ]
              ],
              'advanced_text' => [
                'type' => 'text',
                'label' => __('Advanced Text (Type B & C)', 'wp-plugin-boilerplate'),
                'default' => 'Advanced setting',
                'depends_on' => [
                  'type' => ['type_b', 'type_c']
                ]
              ]
            ]
          ],
        ]
      ],

      // 3. DEPENDENCIES
      'dependency_fields' => [
        'type' => 'group',
        'title' => __('Dependencies', 'wp-plugin-boilerplate'),
        'description' => __('All dependency variants: simple, conditional, double and array', 'wp-plugin-boilerplate'),
        'fields' => [
          'dep_toggle' => [
            'type' => 'toggle',
            'label' => __('Master Toggle', 'wp-plugin-boilerplate'),
            'description' => __('Controls dependent fields below', 'wp-plugin-boilerplate'),
            'default' => true,
          ],
          'dep_text' => [
            'type' => 'text',
            'label' => __('Simple Dependency', 'wp-plugin-boilerplate'),
            'description' => __('Visible when master toggle is on', 'wp-plugin-boilerplate'),
            'default' => 'This depends on toggle',
            'depends_on' => [
              'dep_toggle' => true
            ]
          ],
          'dep_mode' => [
            'type' => 'select',
            'label' => __('Mode Select', 'wp-plugin-boilerplate'),
            'description' => __('Controls conditional and array dependencies', 'wp-plugin-boilerplate'),
            'default' => 'mode_a',
            'options' => [
              'mode_a' => __('Mode A', 'wp-plugin-boilerplate'),
              'mode_b' => __('Mode B', 'wp-plugin-boilerplate'),
              'mode_c' => __('Mode C', 'wp-plugin-boilerplate'),
            ]
          ],
          'dep_conditional' => [
            'type' => 'text',
            'label' => __('Conditional Dependency', 'wp-plugin-boilerplate'),
            'description' => __('Visible only in Mode A', 'wp-plugin-boilerplate'),
            'default' => 'Conditional value',
            'depends_on' => [
              'dep_mode' => 'mode_a'
            ]
          ],
          'dep_double' => [
            'type' => 'color',
            'label' => __('Double Dependency', 'wp-plugin-boilerplate'),
            'description' => __('Visible when toggle is on AND mode is A', 'wp-plugin-boilerplate'),
            'default' => '#ff6b35',
            'depends_on' => [
              'dep_toggle' => true,
              'dep_mode' => 'mode_a'
            ]
          ],
          'dep_array' => [
            'type' => 'text',
            'label' => __('Array Dependency', 'wp-plugin-boilerplate'),
            'description' => __('Visible in Mode B or Mode C', 'wp-plugin-boilerplate'),
            'default' => 'Array dependent value',
            'depends_on' => [
              'dep_mode' => ['mode_b', 'mode_c']
            ]
          ],
        ]
      ],
    ];
  }
}
