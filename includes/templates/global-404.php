<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$template_id = class_exists( 'ACZ_Theme_Options' ) ? ACZ_Theme_Options::get_global_404_template_id() : 0;

if ( $template_id && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
    $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );

    if ( '' !== trim( $content ) ) {
        echo '<main class="acz-global-404 acz-global-template-shell is-loading" data-template-id="' . esc_attr( (string) $template_id ) . '">';
        echo '<div class="acz-global-template-spinner" aria-hidden="true"></div>';
        echo '<div class="acz-global-template-content">';
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
        echo '</main>';
    }
}

get_footer();
