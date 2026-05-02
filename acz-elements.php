<?php
/**
 * Plugin Name: ACZ Elements for Elementor
 * Description: ACZ Elements for Elementor.
 * Version: 1.2.6
 * Author: Allan Cabusora
 * Text Domain: acz-elements
 * Requires Plugins: elementor
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Elementor tested up to: 3.28.0
 * Elementor Pro tested up to: 3.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ACZ_ELEMENTS_VERSION', '1.2.6' );
define( 'ACZ_RC_FILE', __FILE__ );
define( 'ACZ_RC_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACZ_RC_URL', plugin_dir_url( __FILE__ ) );
define( 'ACZ_RC_VERSION', ACZ_ELEMENTS_VERSION ); // Backward compatibility.

require_once __DIR__ . '/acz-plugin.php';


new ACZ_Plugin();
