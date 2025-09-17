<?php
/**
 * Plugin Name:           My Awesome Plugin
 * Plugin URI:            https://github.com/mandytechnologies/my-awesome-plugin
 * Description:           This is plugin to demonstrate how to create a WordPress plugin that can update itself from a GitHub repository.
 * Version:               1.0.0
 * Requires PHP:          8.0
 * Requires at least:     6.1.0
 * Tested up to:          6.8.2
 * Author:                Mandy Technologies
 * Author URI:            https://www.mandytechnologies.com/
 * License:               GPLv2 or later
 * License URI:           https://www.gnu.org/licenses/
 * Text Domain:           my-awesome-plugin
*/


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// ---------------------------------------------------------------------------------
// Define the GitHub repository URL and other plugin details.
// Make sure to replace 'your-github-username' and 'your-repo-name' with your own.
// ---------------------------------------------------------------------------------
define('MY_AWESOME_PLUGIN_SLUG', 'my-awesome-plugin');
define('MY_AWESOME_PLUGIN_VERSION', '1.0.0'); // This version needs to match the one in the plugin header.

// The URL to the JSON file that will contain the update information.
// You need to host this file on a publicly accessible server.
// It's a good practice to use a CDN or a service like GitHub Pages for this.
define('MY_AWESOME_PLUGIN_UPDATE_URL', 'https://github.com/mandytechnologies/my-awesome-plugin/main/package.json');

// ---------------------------------------------------------------------------------
// Check for updates by hooking into the WordPress update process.
// ---------------------------------------------------------------------------------
add_filter('pre_set_site_transient_update_plugins', 'my_awesome_plugin_check_update');

function my_awesome_plugin_check_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    // Get the remote update information from the JSON file.
    $remote_version_info = my_awesome_plugin_get_remote_version_info();

    if ($remote_version_info && version_compare(MY_AWESOME_PLUGIN_VERSION, $remote_version_info->new_version, '<')) {
        // We have a new version available!
        $plugin_data = new stdClass();
        $plugin_data->id = 0;
        $plugin_data->slug = MY_AWESOME_PLUGIN_SLUG;
        $plugin_data->new_version = $remote_version_info->new_version;
        $plugin_data->url = $remote_version_info->url;
        $plugin_data->package = $remote_version_info->package;

        $transient->response[MY_AWESOME_PLUGIN_SLUG . '/' . MY_AWESOME_PLUGIN_SLUG . '.php'] = $plugin_data;

        // Add plugin details to the `plugins_api_args` filter for the "View details" link.
        add_filter('plugins_api_args', 'my_awesome_plugin_api_args', 10, 2);
    }

    return $transient;
}

// ---------------------------------------------------------------------------------
// Get remote version information from the JSON file.
// This function caches the request to avoid hitting the server too often.
// ---------------------------------------------------------------------------------
function my_awesome_plugin_get_remote_version_info() {
    $cache_key = 'my-awesome-plugin-update-info';
    $cached_data = get_transient($cache_key);

    if (false !== $cached_data) {
        return $cached_data;
    }

    $response = wp_remote_get(MY_AWESOME_PLUGIN_UPDATE_URL);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (!is_object($data) || !isset($data->new_version) || !isset($data->package)) {
        return false;
    }

    set_transient($cache_key, $data, HOUR_IN_SECONDS * 12); // Cache for 12 hours.
    return $data;
}

// ---------------------------------------------------------------------------------
// Add plugin details to the "View details" modal.
// ---------------------------------------------------------------------------------
function my_awesome_plugin_api_args($args, $action) {
    if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== MY_AWESOME_PLUGIN_SLUG) {
        return $args;
    }

    $remote_info = my_awesome_plugin_get_remote_version_info();

    if ($remote_info) {
        $args->slug = MY_AWESOME_PLUGIN_SLUG;
        $args->info = $remote_info;
    }

    return $args;
}
