<?php
/*
  Script Name: Handle file requests
  Script URI: https://www.github.com/dichternebel/wordpress-ms-custom-uploads-url/
  Description: Used to serve requests from the plugin "Custom Uploads URL"
  Version: 0.6.0
  Author: dichternebel
  Author URI: https://profiles.wordpress.org/dichternebel/
*/

// Load the Wordpress "lite" environment
define( 'SHORTINIT', true );

// Try to find wp-load.php ... and skate or die!
if (file_exists($_SERVER['DOCUMENT_ROOT'] . ('./wp-load.php'))) {
	require_once $_SERVER['DOCUMENT_ROOT'] . ('./wp-load.php');
} else if (file_exists('../../../wp-load.php')) {
	require_once ('../../../wp-load.php');
} else {
	die('Oops! Could not find wp-load.php.');
}

// Multisite guard
if (is_multisite() && ('1' == $current_blog->archived || '1' == $current_blog->spam)) {
	wp_die('File not found', 'File Not Found', 404);
}

// Ensure the plugin is activated for the current site
if ( ! function_exists( 'is_plugin_active' )  ||  ! function_exists( 'is_plugin_active_for_network' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}
if ( ! is_plugin_active('ms-custom-uploads-url/ms-custom-uploads-url.php')
	|| ! is_plugin_active_for_network('ms-custom-uploads-url/ms-custom-uploads-url.php')) {
	wp_die('<strong>Error:</strong> "MS Custom Uploads URL" plugin not active for this site.', 'Service Unavailable', 503);
}

// Ensure PHP fileinfo extension is enabled
$extension_name = 'fileinfo';
if ( ! extension_loaded($extension_name)) {
	wp_die('<strong>Error:</strong> PHP extension ' . $extension_name . ' not enabled.', 'Service Unavailable', 503);
}

// Get the request
$file_request = $_GET['file'];

// Check if parameter is given
if (!$file_request) {
	wp_die('File not found. ', 'File Not Found', 404);
}

// Get relative uploads path
$upload_path = trim( get_option( 'upload_path' ) );
// Trying to mimic WP's actual behavior
if ( empty( $upload_path )) {
	if (! is_multisite() || is_main_site()) {
		$upload_path  = 'wp-content/uploads';
	} else {
		$site_id = $current_blog->blog_id;
    	$upload_path  = 'wp-content/uploads/sites/' . $site_id;
	}
}
else if (is_multisite() && ! is_main_site()) {
	$site_id = $current_blog->blog_id;
    $upload_path .= '/sites/' . $site_id;
}

// Sanitize and verify request to avoid path traversal
$absolute_upload_path = realpath(ABSPATH . $upload_path);
$file_path = realpath($absolute_upload_path . '/' . $file_request);

if ($file_path === false || strpos($file_path, $absolute_upload_path) !== 0) {
    wp_die('File not found: ' . basename($file_request), 'File Not Found', 404);
}

// Check file for the allowed MIME types
$allowed_mime_types = get_allowed_mime_types();
$check_file = wp_check_filetype($file_path, $allowed_mime_types);
if (!$check_file['ext']) {
    wp_die('File type is not allowed: ' . basename($file_request), 'Unsupported Media Type', 415);
}

// Get file info
$file_name = basename($file_path);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);

// Prepare the headers
// (partially grapped from ./wp-includes/ms-files.php)
$wp_last_modified = gmdate( 'D, d M Y H:i:s', filemtime( $file_path ) );
$wp_etag          = '"' . md5( $wp_last_modified ) . '"';

header( "Content-Disposition: inline; filename=$file_name;" );
header( "Content-Type: $mime_type" );
header( 'Content-Length: ' . filesize($file_path) );
header( "Last-Modified: $wp_last_modified GMT" );
header( 'ETag: ' . $wp_etag );
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );

// Support for conditional GET - use stripslashes() to avoid formatting.php dependency.
if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) {
	$client_etag = stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] );
} else {
	$client_etag = '';
}

if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
	$client_last_modified = trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
} else {
	$client_last_modified = '';
}

// If string is empty, return 0. If not, attempt to parse into a timestamp.
$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;

// Make a timestamp for our most recent modification.
$wp_modified_timestamp = strtotime( $wp_last_modified );

if ( ( $client_last_modified && $client_etag )
	? ( ( $client_modified_timestamp >= $wp_modified_timestamp ) && ( $client_etag === $wp_etag ) )
	: ( ( $client_modified_timestamp >= $wp_modified_timestamp ) || ( $client_etag === $wp_etag ) )
) {
	status_header( 304 );
	exit;
}

// Stream the file
$fp = fopen($file_path, 'rb');
fpassthru($fp);
fclose($fp);
