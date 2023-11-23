<?php
/*
  Plugin Name: Custom Uploads URL
  Plugin URI: https://www.github.com/dichternebel/wordpress-ms-custom-uploads-url/
  Description: Plugin for using custom uploads URL in a WordPress multisite network
  Version: 0.6.0
  Author: dichternebel
  Author URI: https://profiles.wordpress.org/dichternebel/
  Text Domain: ms-custom-uploads-url
  Domain Path: /languages
  Network: true
*/

//  Guard
if ( ! defined( 'WPINC' ) ) {
  die;
}

// This changes the upload URL to use "/files" instead of e.g. "/wp-content/uploads/sites/42"
function custom_upload_url($dirs) {
    $site_domain = get_site_url();
    $dirs['baseurl'] = $site_domain . ('/files');
    return $dirs;
}

// Make sure the plugin is activated for the current site
if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
  require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}
if ( is_plugin_active('ms-custom-uploads-url/ms-custom-uploads-url.php') 
  || is_plugin_active_for_network('ms-custom-uploads-url/ms-custom-uploads-url.php')) {
	add_filter('upload_dir', 'custom_upload_url');

  require_once( 'includes/class-ms-custom-uploads-url-settings.php' );
  new MS_Custom_Uploads_URL_Settings();
}