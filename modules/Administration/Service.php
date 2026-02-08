<?php

namespace WPPluginBoilerplate\Modules\Administration;

use WPPluginBoilerplate\Core\Abstracts\ModuleService;
use enshrined\svgSanitize\Sanitizer;

class Service extends ModuleService
{
  /**
   * Initializes all module services
   * @return void
   * @since 1.3.0
   */
  public function init_service()
  {
    // === Common (Admin + Frontend) ===

    // Stop Update Mails
    if ($this->is_option_enabled('stop_mail_updates')) {
      add_filter('auto_core_update_send_email', [$this, 'stop_update_mails'], 10, 4);
      add_filter('auto_plugin_update_send_email', '__return_false');
      add_filter('auto_theme_update_send_email', '__return_false');
    }

    // Enable infinite scroll for media library
    if ($this->is_option_enabled('media_infinite_scroll')) {
      add_filter('media_library_infinite_scrolling', '__return_true');
      add_filter('upload_per_page', function () {
        return 9999;
      });
    }

    // Register custom image sizes
    $this->register_custom_image_sizes();

    // Add SVG, WebP and AVIF support
    if ($this->is_option_enabled('svg_support') || $this->is_option_enabled('webp_support') || $this->is_option_enabled('avif_support')) {
      if (version_compare(get_bloginfo('version'), '5.8', '<')) {
        add_filter('mime_types', [$this, 'supported_mimes']);
      } else {
        add_filter('upload_mimes', [$this, 'supported_mimes']);
      }
      add_filter('wp_check_filetype_and_ext', [$this, 'handle_modern_image_upload'], 10, 5);

      // Sanitize SVG uploads
      if ($this->is_option_enabled('svg_support')) {
        add_filter('wp_handle_upload_prefilter', [$this, 'sanitize_svg_upload']);
      }
    }

    // === Admin Only ===

    if (is_admin()) {
      // Duplicate Posts
      if ($this->is_option_enabled('duplicate_posts')) {
        add_action('admin_action_duplicate_post_as_draft', [$this, 'duplicate_post_as_draft']);
        add_filter('post_row_actions', [$this, 'duplicate_post_link'], 10, 2);
        add_filter('page_row_actions', [$this, 'duplicate_post_link'], 10, 2);
      }

    }

    // === Frontend Only ===

    if (!is_admin()) {
      // Frontend CSS
      add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

      // External Links (via JS to cover all page areas)
      if ($this->is_option_enabled('external_links_new_tab')) {
        add_action('wp_footer', [$this, 'render_external_links_script'], 99);
      }
    }
  }


  // =====================================================================
  // Common
  // =====================================================================

  /**
   * Stops email notifications for automatic updates
   * @param bool $send
   * @param string $type
   * @param object $core_update
   * @param mixed $result
   * @return bool
   * @since 1.0.0
   */
  public function stop_update_mails($send, $type, $core_update, $result): bool
  {
    return empty($type) || $type !== 'success';
  }

  /**
   * Adds SVG, WebP and AVIF support for file uploads
   * @param array $mimes
   * @return array
   * @since 1.0.0
   */
  public function supported_mimes(array $mimes = []): array
  {
    if ($this->is_option_enabled('svg_support')) {
      $mimes['svg'] = 'image/svg+xml';
    }
    if ($this->is_option_enabled('webp_support') && version_compare(get_bloginfo('version'), '5.8', '<')) {
      $mimes['webp'] = 'image/webp';
    }
    if ($this->is_option_enabled('avif_support') && version_compare(get_bloginfo('version'), '6.5', '<')) {
      $mimes['avif'] = 'image/avif';
    }
    return $mimes;
  }

  /**
   * Handles modern image format upload checks
   * @param array $data
   * @param string $file
   * @param string $filename
   * @param array $mimes
   * @param string|false $real_mime
   * @return array
   * @since 1.0.0
   */
  public function handle_modern_image_upload($data, $file, $filename, $mimes, $real_mime)
  {
    if (!empty($data['type'])) {
      return $data;
    }

    $ext = \strtolower(\pathinfo($filename, PATHINFO_EXTENSION));

    if ($ext === 'svg' && $this->is_option_enabled('svg_support')) {
      $data['type'] = 'image/svg+xml';
      $data['ext'] = 'svg';
    } elseif ($ext === 'webp' && $this->is_option_enabled('webp_support') && version_compare(get_bloginfo('version'), '5.8', '<')) {
      $data['type'] = 'image/webp';
      $data['ext'] = 'webp';
    } elseif ($ext === 'avif' && $this->is_option_enabled('avif_support') && version_compare(get_bloginfo('version'), '6.5', '<')) {
      $data['type'] = 'image/avif';
      $data['ext'] = 'avif';
    }

    return $data;
  }

  /**
   * Sanitizes SVG files on upload to prevent XSS attacks
   * @param array $file
   * @return array
   * @since 3.0.0
   */
  public function sanitize_svg_upload($file)
  {
    if ($file['type'] !== 'image/svg+xml') {
      return $file;
    }

    $sanitizer = new Sanitizer();
    $dirty = \file_get_contents($file['tmp_name']);
    $clean = $sanitizer->sanitize($dirty);

    if ($clean === false) {
      $file['error'] = __('This SVG file could not be sanitized and was rejected for security reasons.', 'wp-plugin-boilerplate');
      return $file;
    }

    \file_put_contents($file['tmp_name'], $clean);
    return $file;
  }

  // =====================================================================
  // Admin
  // =====================================================================

  /**
   * Handles duplication of posts as drafts
   * @return void
   * @since 1.1.0
   */
  public function duplicate_post_as_draft()
  {
    if (!current_user_can('edit_posts')) {
      return;
    }
    if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce(sanitize_text_field($_GET['duplicate_nonce']), 'duplicate_nonce')) {
      return;
    }
    global $wpdb;
    if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && $_REQUEST['action'] === 'duplicate_post_as_draft'))) {
      wp_die('No post to duplicate has been supplied!');
    }

    $post_id = isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']);
    $post = get_post($post_id);

    if (!$post) {
      wp_die('Post creation failed, could not find original post: ' . absint($post_id));
    }

    $new_post_id = wp_insert_post([
      'comment_status' => $post->comment_status,
      'ping_status'    => $post->ping_status,
      'post_author'    => get_current_user_id(),
      'post_content'   => $post->post_content,
      'post_excerpt'   => $post->post_excerpt,
      'post_name'      => $post->post_name,
      'post_parent'    => $post->post_parent,
      'post_password'  => $post->post_password,
      'post_status'    => 'draft',
      'post_title'     => $post->post_title . ' -- Copy',
      'post_type'      => $post->post_type,
      'to_ping'        => $post->to_ping,
      'menu_order'     => $post->menu_order,
    ]);

    $taxonomies = get_object_taxonomies($post->post_type);
    foreach ($taxonomies as $taxonomy) {
      $terms = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'slugs']);
      wp_set_object_terms($new_post_id, $terms, $taxonomy, false);
    }

    $post_meta_infos = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", $post_id)); // phpcs:ignore
    if (\count($post_meta_infos) > 0) {
      $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ";
      $sql_query_params = [];
      foreach ($post_meta_infos as $meta_info) {
        if ($meta_info->meta_key === '_wp_old_slug') {
          continue;
        }
        $sql_query .= '(%d, %s, %s),';
        $sql_query_params[] = $new_post_id;
        $sql_query_params[] = $meta_info->meta_key;
        $sql_query_params[] = $meta_info->meta_value;
      }
      $sql_query = \rtrim($sql_query, ',');
      if (!empty($sql_query_params)) {
        $wpdb->query($wpdb->prepare($sql_query, $sql_query_params)); // phpcs:ignore
      }
    }

    wp_safe_redirect(admin_url("post.php?action=edit&post={$new_post_id}"));
    exit();
  }

  /**
   * Adds a "Duplicate" link to post/page row actions
   * @param array $actions
   * @param \WP_Post $post
   * @return array
   * @since 1.0.0
   */
  public function duplicate_post_link($actions, $post)
  {
    if (current_user_can('edit_posts')) {
      $actions['duplicate'] = '<a href="' . esc_url(admin_url('admin.php?action=duplicate_post_as_draft&post=' . $post->ID . '&duplicate_nonce=' . wp_create_nonce('duplicate_nonce'))) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
    }
    return $actions;
  }

  // =====================================================================
  // Frontend
  // =====================================================================

  /**
   * Enqueues frontend CSS assets
   * @return void
   * @since 1.0.0
   */
  public function enqueue_frontend_assets()
  {
    if ($this->is_option_enabled('hyphens')) {
      wp_enqueue_style('wp-plugin-boilerplate-hyphens', $this->module->get_asset_url('css/misc-hyphens.min.css'));
    }
  }

  /**
   * Renders inline JS to add target/rel attributes to all external links on the page
   * Uses JS instead of PHP content filter to cover Theme Builder, widgets, footer etc.
   * @return void
   * @since 1.3.0
   */
  public function render_external_links_script()
  {
    $rel_value = $this->get_module_option('external_links_rel') ?: 'noopener noreferrer nofollow';
    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
    ?>
    <script>
    (function() {
      var siteHost = <?php echo wp_json_encode($site_host); ?>;
      var relValue = <?php echo wp_json_encode($rel_value); ?>;
      document.querySelectorAll('a[href]').forEach(function(link) {
        try {
          var url = new URL(link.href, window.location.origin);
          if (url.protocol !== 'http:' && url.protocol !== 'https:') return;
          if (url.hostname === siteHost || url.hostname.endsWith('.' + siteHost)) return;
          if (!link.hasAttribute('target')) link.setAttribute('target', '_blank');
          link.setAttribute('rel', relValue);
        } catch(e) {}
      });
    })();
    </script>
    <?php
  }

  /**
   * Register custom image sizes from admin settings
   * @return void
   * @since 1.1.0
   */
  private function register_custom_image_sizes(): void
  {
    $custom_sizes = $this->get_module_option('custom_image_sizes', []);

    if (empty($custom_sizes) || !is_array($custom_sizes)) {
      return;
    }

    foreach ($custom_sizes as $size) {
      // Validate required fields
      if (empty($size['name']) || !isset($size['width']) || !isset($size['height'])) {
        continue;
      }

      // Sanitize values
      $name = sanitize_title($size['name']);
      $width = absint($size['width']);
      $height = absint($size['height']);
      $crop = !empty($size['crop']);

      // Register the image size
      add_image_size($name, $width, $height, $crop);
    }
  }
}
