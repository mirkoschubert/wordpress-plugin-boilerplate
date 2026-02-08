<?php

namespace WPPluginBoilerplate\Core;

class Migration
{
    protected $config;

    public function __construct()
    {
        $this->config = Config::get_instance();
    }

    /**
     * Run migration if needed
     */
    public function run()
    {
        $current_version = $this->config->plugin_version;
        $stored_version = get_option('wp_plugin_boilerplate_version', null);

        if ($stored_version === null) {
            $stored_version = $this->detect_version_from_options();
        }

        if (version_compare($stored_version, $current_version, '<')) {
            $this->migrate($stored_version);
            update_option('wp_plugin_boilerplate_version', $current_version);
        }
    }

    /**
     * Detects the version from the options structure
     */
    protected function detect_version_from_options()
    {
        $options = get_option('wp_plugin_boilerplate_options', []);

        if (empty($options)) {
            return '0';
        }

        return '1.0.0';
    }

    /**
     * Runs the actual migration
     */
    protected function migrate($from_version)
    {
        // Add migration methods here as the plugin evolves
        // Example:
        // if (version_compare($from_version, '2.0.0', '<')) {
        //     $this->migrate_to_200();
        // }
    }

    /**
     * Normalizes a boolean value
     */
    private function normalize_bool($value)
    {
        if ($value === 'on' || $value === '1' || $value === true) {
            return true;
        }
        return false;
    }
}
