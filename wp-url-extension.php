<?php
/*
	Plugin Name: WP URL Extension
	Description: Adds .html, .php, whatever extension to url of all pages, post, tags, custom post
	Version: 0.2
	Author: Alex Egorov
	Author URI: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url
	Plugin URI: https://ru.wordpress.org/plugins/wp-url-extension/
	GitHub Plugin URI:
	License: GPLv2 or later (license.txt)
	Text Domain: wpue
	Domain Path: /wpue
*/

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once( plugin_dir_path( __FILE__ ) . 'class-url-extension.php' );
$instance = new WPUE_Extension;
require_once( plugin_dir_path( __FILE__ ) . 'wp-url-extension-settings.php' );
$instance = new WPUE_Settings;

add_filter('plugin_action_links', 'wpue_plugin_action_links', 10, 2);
function wpue_plugin_action_links($links, $file) {
    static $this_plugin;
    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) { // check to make sure we are on the correct plugin
			$settings_link = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url" target="_blank">❤ ' . __('Donate', 'cchl') . '</a> | <a href="'.get_site_url().'/wp-admin/options-permalink.php">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link); // add the link to the list
    }
    return $links;
}
