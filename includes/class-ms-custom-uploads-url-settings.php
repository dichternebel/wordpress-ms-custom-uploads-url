<?php
//
// The settings page to be displayed in the WP admin area
//
class MS_Custom_Uploads_URL_Settings {

  protected $settings_slug = 'ms-custom-uploads-url-settings';

  public function __construct() {
    if (is_multisite() && is_plugin_active_for_network('ms-custom-uploads-url/ms-custom-uploads-url.php')) {
      add_action( 'network_admin_menu', array( $this, 'add_submenu_page' ) );
    }
    else if (is_multisite() && is_main_site()) {
      add_action( 'admin_menu', array( $this, 'add_options_page' ) );
    }
    else if (! is_multisite() && is_plugin_active('ms-custom-uploads-url/ms-custom-uploads-url.php')) {
      add_action( 'admin_menu', array( $this, 'add_options_page' ) );
    }
  }

  public function add_submenu_page() {
    add_submenu_page(
      'settings.php',
      __( 'Custom Uploads URL', 'ms-custom-uploads-url' ),
      __( 'Custom Uploads URL', 'ms-custom-uploads-url' ),
      'manage_network_options',
      $this->settings_slug . '-page',
      array( $this, 'display_options_page' )
    );
  }

  public function add_options_page() {
    add_options_page(
      __( 'Custom Uploads URL', 'ms-custom-uploads-url' ),
      __( 'Custom Uploads URL', 'ms-custom-uploads-url' ),
      'manage_options',
      $this->settings_slug . '-page',
      array( $this, 'display_options_page' )
    );
  }

  public function display_options_page() {
    ?>
    <div class="wrap">
      <div style="float:right;">
          <script type='text/javascript' src='https://storage.ko-fi.com/cdn/widget/Widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Support me on Ko-fi', '#29abe0', 'V7V6JM5JS');kofiwidget2.draw();</script>
      </div>
      <h1><?php _e( 'Settings' ); ?> > <?php echo esc_html( get_admin_page_title() ); ?></h1>
      <div>
        <h3>Prerequisites</h3>
        <p>If your current setup does not meet these prerequisites the plugin will not work:</p>
        <?php $this->display_prerequisites(); ?>
      </div>
      <div>
        <h3>Recommended</h3>
        <p>Although the plugin might work with lower versions, please check if you meet the recommended PHP version:</p>
        <p>PHP version ≥ 5.3 : <?php echo phpversion(); ?></p>
      </div>
      <div>
        <h3>Setting Up Rewrite Rules</h3>
        <p>The plugin needs rewrite rules being set up correctly in order to work. Here you will find some information how to do that.</p>
        <?php $this->display_rewrite_rule(); ?>
      </div>
    </div>
    <?php
  }

  public function display_prerequisites() {
    $wp_version = get_bloginfo('version');
    $fileinfo_ext = extension_loaded('fileinfo') ? '<span style="color: green">loaded</span>' : '<span style="color: red">missing</span>';

    if (version_compare($wp_version, '5.1.17', '<')) {
      ?>
      <div class="notice notice-warning is-dismissible">
          <p><?php _e( 'Your WordPress version is not supported by this plugin! Please upgrade WP.', 'ms-custom-uploads-url' ); ?></p>
      </div>
      <?php
      $wp_version = '<span style="color: red">' . get_bloginfo('version') . '</span>';
    }
    if ( ! extension_loaded('fileinfo')) {
      ?>
      <div class="notice notice-warning is-dismissible">
          <p><?php _e( 'PHP extension <code>fileinfo</code> is missing! Please add it to your <code>php.ini</code> and restart the web server.', 'ms-custom-uploads-url' ); ?></p>
      </div>
      <?php
    }
    echo '<p>WordPress ≥ 5.1.17 : ' . $wp_version . '</p>';
    echo '<p>PHP extension <code>fileinfo</code>: ' . $fileinfo_ext . '</p>';
  }

  public function display_rewrite_rule() {
    $server_name = $_SERVER['SERVER_SOFTWARE'] ? $_SERVER['SERVER_SOFTWARE'] : 'an unknown webserver';
    echo '<p>It seems like you are using ' . $server_name . '.</p>';

    if (preg_match( '/^nginx\//', $server_name )) {
      echo '1. Please add following two rules to your e.g. <code>site.conf</code>, <code>nginx.conf</code> or similar inside the <i>server</i> node before any other location rule:';
      echo '<pre>';
      echo esc_html(file_get_contents( realpath(dirname(__FILE__) . '/rewrite_rule_nginx.txt')));
      echo '</pre>';
      echo '2. Check the path to <code>ms-custom-uploads-url/handle-file-requests.php</code> and change the rules accordingly.';
      echo '<p>3. Save the configuration file.</p>';
      echo '<p>4. Restart your web server to apply the changes like <code>nginx -s reload</code>.</p>';
      return;
    }

    if (preg_match( '/^Apache\//', $server_name )) {
      echo '<p><strong>Heads-up:</strong> The <code>rewrite_module</code> has to be loaded into Apache to make this work!</p>';
      echo '1. Please add following two rules to your <code>.htaccess</code> file or the <i>virtual host configuration</i> in <code>httpd.conf</code> before any other RewriteRule:';
      echo '<pre>';
      echo esc_html(file_get_contents( realpath(dirname(__FILE__) . '/rewrite_rule_apache.txt')));
      echo '</pre>';
      echo '2. Check the path to <code>ms-custom-uploads-url/handle-file-requests.php</code> and change the rules accordingly.';
      echo '<p>3. Save the configuration file.</p>';
      echo '<p>4. Restart your web server to apply the changes like <code>systemctl restart apache2</code>.</p>';
      return;
    }

    if (preg_match( '/^Microsoft-IIS\//', $server_name )) {
      echo '<p><strong>Heads-up:</strong> The <a href="https://www.iis.net/downloads/microsoft/url-rewrite" target="_blank">IIS-URL Rewrite Module</a> has to be installed to make this work!</p>';
      echo '1. Please add following two rules to your <b>web.config</b> file before any other rewrite rule:';
      echo '<pre>';
      echo esc_html(file_get_contents( realpath(dirname(__FILE__) . '/rewrite_rule_iis.txt')));
      echo '</pre>';
      echo '2. Check the path to <code>ms-custom-uploads-url/handle-file-requests.php</code> and change the rules accordingly.';
      echo '<p>3. Save the configuration file.</p>';
      return;
    }

    echo <<< CHATGPT
        <p>Since I am not able to guess your web server, here's a textual guide on how to set up two rewrite rules on your server, assuming you have the necessary access to the server configuration:</p>
    
        <p><strong>1. Access Server Configuration:</strong></p>
        <p>- Log in to your web server where your WordPress site is hosted.</p>
        <p>- Locate the server configuration files. The location of these files may vary depending on your web server software.</p>
    
        <p><strong>2. Edit Configuration File:</strong></p>
        <p>- Open the configuration file for your web server. Common file names include <code>nginx.conf</code> for Nginx or <code>httpd.conf</code> for Apache. The location of these files can vary, so check your server's documentation.</p>
    
        <p><strong>3. Identify the Location Section:</strong></p>
        <p>- Find the section of the configuration file that deals with server or location directives. This is where you will define the rules for URL rewriting.</p>
    
        <p><strong>4. Rule for <code>/files/(.*)</code>:</strong></p>
        <p>- Add a rule to handle requests to URLs starting with <code>/files/</code>. This rule should internally redirect these requests to <code>/wp-content/plugins/ms-custom-uploads-url/handle-file-requests.php?file=&lt;filename&gt;</code>.</p>
    
        <p><strong>5. Rule for <code>/(.*)/files/(.*)</code>:</strong></p>
        <p>- If you are using WordPress multisite with subdirectories, add a rule to handle requests where <code>/files/</code> appears after any path segment. This rule should internally redirect these requests to the same PHP script with the file parameter.</p>
    
        <p><strong>6. Save and Restart:</strong></p>
        <p>- Save the configuration file.</p>
        <p>- Restart your web server to apply the changes. The command for this varies based on your server software (<code>nginx -s reload</code> for Nginx, <code>systemctl restart apache2</code> for Apache, etc.).</p>
    
        <p><strong>7. Testing:</strong></p>
        <p>- Test the setup by making requests to URLs that match the patterns specified in the rules. Ensure that the URLs are being internally rewritten as expected.</p>
    
        <p><strong>8. Troubleshooting:</strong></p>
        <p>- If there are issues, check the error logs of your web server for any error messages. Adjust the rules accordingly.</p>
    CHATGPT;
  }
}