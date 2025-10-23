<?php
/**
 * Plugin Name:           My Awesome Plugin
 * Plugin URI:            https://github.com/mandytechnologies/my-awesome-plugin
 * Description:           This is plugin to demonstrate how to create a WordPress plugin that can update itself from a GitHub repository.
 * Version:               1.0.5
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

require 'plugin-update-checker/plugin-update-checker.php';

$update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/mandytechnologies/my-awesome-plugin/',
	__FILE__,
	'my-awesome-plugin'
);

require_once( 'includes/class-plugin.php' );
