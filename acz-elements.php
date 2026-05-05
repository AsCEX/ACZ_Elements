<?php
/**
 * Plugin Name: ACZ Elements for Elementor
 * Description: ACZ Elements for Elementor.
 * Version: 1.2.8
 * Author: aczolutions.com
 * Text Domain: acz-elements
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: elementor, advanced-custom-fields
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Elementor tested up to: 3.28.0
 * Elementor Pro tested up to: 3.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ACZ_ELEMENTS_VERSION', '1.2.8' );
define( 'ACZ_RC_FILE', __FILE__ );
define( 'ACZ_RC_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACZ_RC_URL', plugin_dir_url( __FILE__ ) );
define( 'ACZ_RC_VERSION', ACZ_ELEMENTS_VERSION ); // Backward compatibility.
define( 'ACZ_ELEMENTS_FILE', __FILE__ );

function acz_elements_get_required_plugins(): array {
    return [
        'elementor' => [
            'name'  => 'Elementor',
            'slug'  => 'elementor',
            'files' => [
                'elementor/elementor.php',
            ],
        ],
        'acf' => [
            'name'  => 'Advanced Custom Fields (ACF)',
            'slug'  => 'advanced-custom-fields',
            'files' => [
                'advanced-custom-fields/acf.php',
                'advanced-custom-fields-pro/acf.php',
            ],
        ],
    ];
}

function acz_elements_get_missing_required_plugins(): array {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $missing = [];

    foreach ( acz_elements_get_required_plugins() as $dependency ) {
        $is_active = false;

        foreach ( $dependency['files'] as $plugin_file ) {
            if ( is_plugin_active( $plugin_file ) ) {
                $is_active = true;
                break;
            }
        }

        if ( ! $is_active ) {
            $missing[] = $dependency;
        }
    }

    return $missing;
}

function acz_elements_get_dependency_action_url( array $dependency ): string {
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $installed_plugins = get_plugins();

    foreach ( $dependency['files'] as $plugin_file ) {
        if ( isset( $installed_plugins[ $plugin_file ] ) ) {
            return wp_nonce_url(
                admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin_file ) ),
                'activate-plugin_' . $plugin_file
            );
        }
    }

    return wp_nonce_url(
        self_admin_url( 'update.php?action=install-plugin&plugin=' . rawurlencode( $dependency['slug'] ) ),
        'install-plugin_' . $dependency['slug']
    );
}

function acz_elements_format_missing_dependencies_notice( array $missing ): string {
    $items = [];

    foreach ( $missing as $dependency ) {
        $items[] = sprintf(
            '<li><strong>%1$s</strong> - <a href="%2$s">%3$s</a></li>',
            esc_html( $dependency['name'] ),
            esc_url( acz_elements_get_dependency_action_url( $dependency ) ),
            esc_html__( 'Install or activate', 'acz-elements' )
        );
    }

    return sprintf(
        '<p>%1$s</p><ul>%2$s</ul><p><a href="%3$s">%4$s</a></p>',
        esc_html__( 'ACZ Elements requires these plugins before it can be activated:', 'acz-elements' ),
        implode( '', $items ),
        esc_url( admin_url( 'plugins.php' ) ),
        esc_html__( 'Return to Plugins', 'acz-elements' )
    );
}

function acz_elements_activate(): void {
    $missing = acz_elements_get_missing_required_plugins();

    if ( empty( $missing ) ) {
        return;
    }

    deactivate_plugins( plugin_basename( __FILE__ ) );

    wp_die(
        wp_kses_post( acz_elements_format_missing_dependencies_notice( $missing ) ),
        esc_html__( 'Missing Required Plugins', 'acz-elements' ),
        [
            'back_link' => true,
        ]
    );
}

register_activation_hook( __FILE__, 'acz_elements_activate' );

require_once __DIR__ . '/acz-plugin.php';


new ACZ_Plugin();
