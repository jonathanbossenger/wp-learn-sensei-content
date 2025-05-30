<?php
/**
 * Plugin name: WP Learn Sensei Content
 * Description: A custom Sensei extension plugin for WP Learn.
 * Version: 1.0.0
 * Author: WP Learn
 * Text Domain: wp-learn-sensei-content
 * Domain Path: /languages
 * Requires PHP: 7.4
 *
 * @package WP_Learn
 */
declare(strict_types=1);

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP_LEARN_SENSEI_CONTENT_VERSION', '1.0.0' );
define( 'WP_LEARN_SENSEI_CONTENT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_LEARN_SENSEI_CONTENT_URL', plugin_dir_url( __FILE__ ) );

// Simple autoloader for plugin classes
spl_autoload_register( function ( $class ) {
    // Check if the class is in our namespace
    if ( strpos( $class, 'WP_Learn\\Sensei_Content\\' ) !== 0 ) {
        return;
    }

    // Convert namespace to file path
    $file_path = str_replace( 'WP_Learn\\Sensei_Content\\', '', $class );
    $file_path = str_replace( '\\', DIRECTORY_SEPARATOR, $file_path );
    $file_path = WP_LEARN_SENSEI_CONTENT_PATH . 'includes' . DIRECTORY_SEPARATOR . $file_path . '.php';

    // Include the file if it exists
    if ( file_exists( $file_path ) ) {
        require_once $file_path;
    }
} );

// Initialize the plugin on plugins_loaded to ensure all dependencies are available.
add_action( 'plugins_loaded', 'wp_learn_sensei_content_init' );
/**
 * Initialize the plugin.
 */
function wp_learn_sensei_content_init() {
    // Check if Sensei LMS is active
    if ( ! class_exists( 'Sensei_Main' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>' . esc_html__( 'WP Learn Sensei Content requires Sensei LMS plugin to be installed and activated.', 'wp-learn-sensei-content' ) . '</p></div>';
        } );
        return;
    }
}

// Register WP CLI commands if WP CLI is available
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    add_action( 'init', function() {
        WP_CLI::add_command( 'sensei-content', 'WP_Learn\\Sensei_Content\\CLI\\CourseStructure' );
    } );
}
