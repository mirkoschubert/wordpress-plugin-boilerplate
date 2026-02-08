<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

delete_option('wp_plugin_boilerplate_options');
delete_option('wp_plugin_boilerplate_version');
