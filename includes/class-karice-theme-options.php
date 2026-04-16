<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class KC_Theme_Options {
    const OPTION_KEY = 'karice_theme_options';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public static function get_defaults(): array {
        return [
            'widget_carousel_enabled'      => true,
            'widget_post_gallery_enabled'  => true,
            'widget_post_filter_enabled'   => true,
            'widget_breadcrumbs_enabled'   => true,
            'widget_taxonomy_list_enabled' => true,
            'widget_featured_post_enabled' => true,
            'widget_post_carousel_enabled' => true,
            'taxonomy_add_form_collapsed'  => true,
        ];
    }

    public static function get_all(): array {
        $defaults = self::get_defaults();
        $saved    = get_option( self::OPTION_KEY, [] );
        $options  = is_array( $saved ) ? $saved : [];

        foreach ( $defaults as $key => $default ) {
            if ( isset( $options[ $key ] ) ) {
                $options[ $key ] = (bool) $options[ $key ];
            } else {
                $options[ $key ] = $default;
            }
        }

        return $options;
    }

    public static function get( string $key, $default = null ) {
        $options = self::get_all();

        if ( array_key_exists( $key, $options ) ) {
            return $options[ $key ];
        }

        return $default;
    }

    public static function is_widget_enabled( string $widget_key ): bool {
        return (bool) self::get( 'widget_' . sanitize_key( $widget_key ) . '_enabled', true );
    }

    public function register_menu() {
        add_theme_page(
            esc_html__( 'Karice Theme Options', 'karice-elements' ),
            esc_html__( 'Karice Theme Options', 'karice-elements' ),
            'edit_theme_options',
            'karice-theme-options',
            [ $this, 'render_page' ]
        );
    }

    public function register_settings() {
        register_setting(
            'karice_theme_options_group',
            self::OPTION_KEY,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_options' ],
                'default'           => self::get_defaults(),
            ]
        );

        add_settings_section(
            'krc_widgets_section',
            esc_html__( 'Elementor Widgets', 'karice-elements' ),
            function () {
                echo '<p>' . esc_html__( 'Enable or disable Karice Elementor widgets globally.', 'karice-elements' ) . '</p>';
            },
            'karice-theme-options'
        );

        $widget_fields = [
            'widget_carousel_enabled'      => esc_html__( 'Karice Carousel Widget', 'karice-elements' ),
            'widget_post_gallery_enabled'  => esc_html__( 'Karice Post Gallery Widget', 'karice-elements' ),
            'widget_post_filter_enabled'   => esc_html__( 'Karice Post Filter Widget', 'karice-elements' ),
            'widget_breadcrumbs_enabled'   => esc_html__( 'Karice Breadcrumbs Widget', 'karice-elements' ),
            'widget_taxonomy_list_enabled' => esc_html__( 'Karice Taxonomy List Widget', 'karice-elements' ),
            'widget_featured_post_enabled' => esc_html__( 'Karice Featured Post Widget', 'karice-elements' ),
            'widget_post_carousel_enabled' => esc_html__( 'Karice Post Carousel Widget', 'karice-elements' ),
        ];

        foreach ( $widget_fields as $key => $label ) {
            add_settings_field(
                $key,
                $label,
                [ $this, 'render_checkbox_field' ],
                'karice-theme-options',
                'krc_widgets_section',
                [
                    'key' => $key,
                ]
            );
        }

        add_settings_section(
            'krc_general_section',
            esc_html__( 'General', 'karice-elements' ),
            '__return_false',
            'karice-theme-options'
        );

        add_settings_field(
            'taxonomy_add_form_collapsed',
            esc_html__( 'Collapse Taxonomy Add Form by Default', 'karice-elements' ),
            [ $this, 'render_checkbox_field' ],
            'karice-theme-options',
            'krc_general_section',
            [
                'key'         => 'taxonomy_add_form_collapsed',
                'description' => esc_html__( 'Applies to capability, space, and fixture taxonomy screens.', 'karice-elements' ),
            ]
        );
    }

    public function sanitize_options( $input ): array {
        $defaults  = self::get_defaults();
        $sanitized = [];
        $input     = is_array( $input ) ? $input : [];

        foreach ( $defaults as $key => $default ) {
            $sanitized[ $key ] = isset( $input[ $key ] );
        }

        return $sanitized;
    }

    public function render_checkbox_field( array $args ) {
        $key         = isset( $args['key'] ) ? (string) $args['key'] : '';
        $description = isset( $args['description'] ) ? (string) $args['description'] : '';
        $options     = self::get_all();
        $checked     = ! empty( $options[ $key ] );

        printf(
            '<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s /> %4$s</label>',
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            checked( $checked, true, false ),
            esc_html__( 'Enabled', 'karice-elements' )
        );

        if ( '' !== $description ) {
            echo '<p class="description">' . esc_html( $description ) . '</p>';
        }
    }

    public function render_page() {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Karice Theme Options', 'karice-elements' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'karice_theme_options_group' );
                do_settings_sections( 'karice-theme-options' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
