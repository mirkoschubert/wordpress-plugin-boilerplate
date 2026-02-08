<?php

namespace WPPluginBoilerplate\Modules\A11y;

use WPPluginBoilerplate\Core\Abstracts\ModuleService;

class Service extends ModuleService
{
  /**
   * Initializes all module services
   * @return void
   * @since 3.0.0
   */
  public function init_service()
  {
    // === Frontend Only ===
    if (!is_admin()) {
      // 1. Skip Link
      if ($this->is_option_enabled('skip_link')) {
        add_action('wp_body_open', [$this, 'add_skip_link']);
      }

      // 3. Scroll to Top
      if ($this->is_option_enabled('scroll_top')) {
        add_action('wp_footer', [$this, 'add_scroll_top'], 10);
      }

      // 4. Dynamic CSS for animations and text selection
      add_action('wp_head', [$this, 'add_dynamic_css']);

      // Assets
      add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }
  }


  /**
   * Enqueues frontend assets
   * @return void
   * @since 1.0.0
   */
  public function enqueue_assets()
  {
    // 2. Skip Link
    if ($this->is_option_enabled('skip_link')) {
      wp_enqueue_style('wp-plugin-boilerplate-skip-link', $this->module->get_asset_url("css/a11y-skip-link.min.css"));
    }

    // 3. Scroll to Top
    if ($this->is_option_enabled('scroll_top')) {
      wp_enqueue_style('wp-plugin-boilerplate-scroll-top', $this->module->get_asset_url("css/a11y-scroll-top.min.css"));
    }

    // 4. Focus Elements
    if ($this->is_option_enabled('focus_elements')) {
      wp_enqueue_style('wp-plugin-boilerplate-focus-elements', $this->module->get_asset_url("css/a11y-focus-elements.min.css"));
    }

    // 5. Keyboard Navigation
    if ($this->is_option_enabled('nav_keyboard')) {
      wp_enqueue_style('wp-plugin-boilerplate-nav-keyboard', $this->module->get_asset_url("css/a11y-nav-keyboard.min.css"));
    }

    // 6. Fix Screenreader
    if ($this->is_option_enabled('fix_screenreader')) {
      wp_enqueue_style('wp-plugin-boilerplate-fix-screenreader', $this->module->get_asset_url("css/a11y-fix-screenreader.min.css"));
    }

    // 7. Underline Links
    if ($this->is_option_enabled('underline_links')) {
      wp_enqueue_style('wp-plugin-boilerplate-underline-links', $this->module->get_asset_url("css/a11y-underline-links.min.css"));
    }

    // 8. Stop Animations
    if ($this->is_option_enabled('stop_animations')) {
      wp_enqueue_style('wp-plugin-boilerplate-stop-animations', $this->module->get_asset_url("css/a11y-stop-animations.min.css"));
    }

    // 9. Slider Navigation Spacing
    if ($this->is_option_enabled('slider_nav_spacing')) {
      wp_enqueue_style('wp-plugin-boilerplate-slider-nav-spacing', $this->module->get_asset_url("css/a11y-slider-nav-spacing.min.css"));
    }

    wp_enqueue_script(
      'wp-plugin-boilerplate-a11y-script',
      $this->module->get_asset_url('js/a11y.js'),
      ['jquery', 'wp-i18n'],
      null,
      true
    );

    // Set script translations for i18n support
    wp_set_script_translations('wp-plugin-boilerplate-a11y-script', 'wp-plugin-boilerplate');

    // Pass options to JS
    wp_localize_script('wp-plugin-boilerplate-a11y-script', 'a11yOptions', $this->get_module_options());
  }


  /**
   * Adds a skip link to the page
   * @return void
   * @since 1.0.0
   */
  public function add_skip_link()
  {
    echo '<a href="#main-content" target="_self" class="skip-link" role="link">' . esc_html__('Skip to content', 'wp-plugin-boilerplate') . '</a>';
  }


  /**
   * Adds a scroll to top button
   * @return void
   * @since 1.0.0
   */
  public function add_scroll_top()
  {
    echo '<button class="top-link hide" id="js-top"><svg role="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 6"><path d="M12 6H0l6-6z"/></svg><span class="screen-reader-text">Back to top</span></button>';
  }

  /**
   * Adds dynamic CSS for animations and text selection
   * @return void
   * @since 1.0.0
   */
  public function add_dynamic_css()
  {
    $css = '';

    // Custom text selection colors
    $bg_color = $this->get_module_option('text_highlight_bg');
    $text_color = $this->get_module_option('text_highlight_color');

    if ($bg_color && $text_color) {
      $css .= '::selection {';
      $css .= 'color: ' . esc_attr($text_color) . ' !important;';
      $css .= 'background-color: ' . esc_attr($bg_color) . ' !important;';
      $css .= '}';
      $css .= '::-moz-selection {';
      $css .= 'color: ' . esc_attr($text_color) . ' !important;';
      $css .= 'background-color: ' . esc_attr($bg_color) . ' !important;';
      $css .= '}';
    }

    if (!empty($css)) {
      echo '<style id="wp-plugin-boilerplate-a11y-dynamic-css">' . $css . '</style>';
    }
  }
}
