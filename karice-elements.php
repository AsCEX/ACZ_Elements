<?php
/**
 * Plugin Name: Karice Elements for Elementor
 * Description: Karice Elements for Elementor.
 * Version: 1.2.1
 * Author: Allan Cabusora
 * Text Domain: karice-elements
 * Requires Plugins: elementor
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Elementor tested up to: 3.28.0
 * Elementor Pro tested up to: 3.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'KARICE_ELEMENTS_VERSION', '1.2.5' );
define( 'KARICE_RC_FILE', __FILE__ );
define( 'KARICE_RC_PATH', plugin_dir_path( __FILE__ ) );
define( 'KARICE_RC_URL', plugin_dir_url( __FILE__ ) );
define( 'KARICE_RC_VERSION', KARICE_ELEMENTS_VERSION ); // Backward compatibility.

require_once __DIR__ . '/karice-plugin.php';


new KC_Plugin();
