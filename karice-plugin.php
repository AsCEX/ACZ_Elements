<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-karice-theme-options.php';

final class KC_Plugin {
    const SLUG = 'karice-carousel';

    private function get_version(): string {
        return defined( 'KARICE_ELEMENTS_VERSION' ) ? KARICE_ELEMENTS_VERSION : '1.2.1';
    }

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        new KC_Theme_Options();
        add_filter( 'plugin_action_links_' . plugin_basename( KARICE_RC_FILE ), [ $this, 'add_plugin_action_links' ] );

        add_action(
            'admin_head-edit-tags.php',
            function () {
                $screen     = get_current_screen();
                $taxonomies = [ 'capability', 'space', 'fixture' ];

                if ( ! $screen || ! in_array( $screen->taxonomy, $taxonomies, true ) ) {
                    return;
                }

                echo '<style>
                    body.krc-tax-form-hidden #col-left { display: none !important; }
                    body.krc-tax-form-hidden #col-right { width: 100% !important; float: none !important; margin-left: 0 !important; }
                    .krc-tax-form-toggle { margin: 8px 0 14px; }
                </style>';
            }
        );

        add_action(
            'admin_footer-edit-tags.php',
            function () {
                $screen     = get_current_screen();
                $taxonomies = [ 'capability', 'space', 'fixture' ];

                if ( ! $screen || ! in_array( $screen->taxonomy, $taxonomies, true ) ) {
                    return;
                }

                $default_collapsed = KC_Theme_Options::get( 'taxonomy_add_form_collapsed', true ) ? '1' : '0';
                $taxonomy = sanitize_key( (string) $screen->taxonomy );
                ?>
                <script>
                    (function () {
                        var colLeft = document.getElementById('col-left');
                        var colRight = document.getElementById('col-right');
                        if (!colLeft || !colRight) {
                            return;
                        }

                        var storageKey = 'krcTaxFormHidden_<?php echo esc_js( $taxonomy ); ?>';
                        var hidden = window.localStorage ? localStorage.getItem(storageKey) : null;
                        if (hidden === null) {
                            hidden = '<?php echo esc_js( $default_collapsed ); ?>';
                        }

                        var target = document.querySelector('.wrap .search-form') || document.querySelector('.wrap');
                        if (!target) {
                            return;
                        }

                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'button button-secondary krc-tax-form-toggle';

                        function applyState() {
                            var isHidden = hidden === '1';
                            document.body.classList.toggle('krc-tax-form-hidden', isHidden);
                            button.textContent = isHidden ? 'Show Add Form' : 'Hide Add Form';
                        }

                        button.addEventListener('click', function () {
                            hidden = (hidden === '1') ? '0' : '1';
                            if (window.localStorage) {
                                localStorage.setItem(storageKey, hidden);
                            }
                            applyState();
                        });

                        target.insertAdjacentElement('afterend', button);
                        applyState();
                    })();
                </script>
                <?php
            }
        );

        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
            return;
        }

        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ], 1 );
        add_action( 'wp_ajax_krc_post_filter_gallery', [ $this, 'ajax_post_filter_gallery' ] );
        add_action( 'wp_ajax_nopriv_krc_post_filter_gallery', [ $this, 'ajax_post_filter_gallery' ] );
    }

    public function register_assets() {
        $base_url = plugin_dir_url( __FILE__ );
        $version  = $this->get_version();

        wp_register_style(
            'cec-swiper',
            'https://cdn.jsdelivr.net/npm/swiper@12.1.3/swiper-bundle.min.css',
            [],
            '12.1.3'
        );

        wp_register_script(
            'cec-swiper',
            'https://cdn.jsdelivr.net/npm/swiper@12.1.3/swiper-bundle.min.js',
            [],
            '12.1.3',
            true
        );

        wp_register_style(
            'cec-widget',
            $base_url . 'assets/css/widget.css',
            [ 'cec-swiper' ],
            $version
        );

        wp_register_style(
            'karice-post-gallery',
            $base_url . 'assets/css/widget.css',
            [],
            $version
        );

        wp_register_script(
            'cec-widget',
            $base_url . 'assets/js/widget.js',
            [ 'cec-swiper' ],
            $version,
            true
        );

        wp_register_script(
            'krc-post-filter-ajax',
            $base_url . 'assets/js/post-filter-ajax.js',
            [],
            $version,
            true
        );

        wp_localize_script(
            'krc-post-filter-ajax',
            'KrcPostFilterAjax',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'krc_post_filter_gallery' ),
            ]
        );
    }

    public function register_categories( $elements_manager ) {
        $reordered_categories = [
            'karice' => [
                'title' => esc_html__( 'Karice', 'karice-elements' ),
                'icon'  => 'fa fa-plug',
            ],
        ];

        if ( ! method_exists( $elements_manager, 'get_categories' ) ) {
            return;
        }

        $old_categories = $elements_manager->get_categories();
        $reordered_categories = array_merge( $reordered_categories, $old_categories );

        try {
            $set_categories = \Closure::bind( function( $categories ) {
                if ( property_exists( $this, 'categories' ) ) {
                    $this->categories = $categories;
                }
            }, $elements_manager, $elements_manager );

            if ( $set_categories ) {
                $set_categories( $reordered_categories );
            }
        } catch ( \Exception $e ) {
            // Fallback to doing nothing if reordering fails.
        }
    }

    public function register_widgets( $widgets_manager ) {
        if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
            return;
        }

        require_once __DIR__ . '/includes/widgets/class-karice-carousel-widget.php';
        require_once __DIR__ . '/includes/widgets/class-karice-post-gallery-widget.php';
        require_once __DIR__ . '/includes/widgets/class-karice-post-filter-widget.php';
        require_once __DIR__ . '/includes/widgets/class-karice-breadcrumbs-widget.php';
        require_once __DIR__ . '/includes/widgets/class-karice-taxonomy-list-widget.php';
        require_once __DIR__ . '/includes/widgets/class-karice-featured-post-widget.php';
        require_once __DIR__ . '/includes/widgets/class-karice-post-carousel-widget.php';

        if ( KC_Theme_Options::is_widget_enabled( 'carousel' ) ) {
            $widgets_manager->register( new \KC_Karice_Carousel_Widget() );
        }

        if ( KC_Theme_Options::is_widget_enabled( 'post_gallery' ) ) {
            $widgets_manager->register( new \KC_Karice_Post_Gallery_Widget() );
        }

        if ( KC_Theme_Options::is_widget_enabled( 'post_filter' ) ) {
            $widgets_manager->register( new \KC_Karice_Post_Filter_Widget() );
        }

        if ( KC_Theme_Options::is_widget_enabled( 'breadcrumbs' ) ) {
            $widgets_manager->register( new \KC_Karice_Breadcrumbs_Widget() );
        }

        if ( KC_Theme_Options::is_widget_enabled( 'taxonomy_list' ) ) {
            $widgets_manager->register( new \KC_Karice_Taxonomy_List_Widget() );
        }

        if ( KC_Theme_Options::is_widget_enabled( 'featured_post' ) ) {
            $widgets_manager->register( new \KC_Karice_Featured_Post_Widget() );
        }

        if ( KC_Theme_Options::is_widget_enabled( 'post_carousel' ) ) {
            $widgets_manager->register( new \KC_Karice_Post_Carousel_Widget() );
        }
    }

    public function add_plugin_action_links( array $links ): array {
        $theme_options_link = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( admin_url( 'themes.php?page=karice-theme-options' ) ),
            esc_html__( 'Theme Options', 'karice-elements' )
        );

        array_unshift( $links, $theme_options_link );

        return $links;
    }

    public function admin_notice_missing_elementor() {
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo esc_html__( 'Karice Carousel for Elementor requires Elementor to be installed and activated.', 'karice-carousel' );
        echo '</p></div>';
    }

    private function is_same_host_url( string $url ): bool {
        $target = wp_parse_url( $url );
        $home   = wp_parse_url( home_url( '/' ) );

        if ( ! is_array( $target ) || ! is_array( $home ) ) {
            return false;
        }

        $target_host = strtolower( (string) ( $target['host'] ?? '' ) );
        $home_host   = strtolower( (string) ( $home['host'] ?? '' ) );

        return '' !== $target_host && $target_host === $home_host;
    }

    private function get_node_inner_html( \DOMNode $node ): string {
        $html = '';
        foreach ( $node->childNodes as $child ) {
            $html .= $node->ownerDocument->saveHTML( $child );
        }
        return $html;
    }

    private function extract_gallery_payload( string $html, array $ids ): array {
        $result = [
            'slots' => [],
        ];

        if ( '' === trim( $html ) || empty( $ids ) ) {
            return $result;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
        libxml_clear_errors();

        $xpath = new \DOMXPath( $dom );

        foreach ( $ids as $id ) {
            $id = sanitize_html_class( (string) $id );
            if ( '' === $id ) {
                continue;
            }

            $widget_nodes = $xpath->query( "//*[@id='{$id}']" );
            if ( ! $widget_nodes || 0 === $widget_nodes->length ) {
                continue;
            }

            $widget_node = $widget_nodes->item( 0 );
            if ( ! $widget_node instanceof \DOMElement ) {
                continue;
            }

            $slot_nodes = $xpath->query( ".//*[contains(concat(' ', normalize-space(@class), ' '), ' krc-post-gallery-ajax-slot ')]", $widget_node );
            if ( ! $slot_nodes || 0 === $slot_nodes->length ) {
                continue;
            }

            $slot_node = $slot_nodes->item( 0 );
            if ( ! $slot_node instanceof \DOMElement ) {
                continue;
            }

            $result['slots'][ $id ] = $this->get_node_inner_html( $slot_node );
        }

        return $result;
    }

    public function ajax_post_filter_gallery() {
        check_ajax_referer( 'krc_post_filter_gallery', 'nonce' );

        $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['url'] ) ) : '';
        $ids = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'sanitize_html_class', wp_unslash( $_POST['ids'] ) ) : [];

        $ids = array_values( array_filter( $ids ) );

        if ( '' === $url || empty( $ids ) || ! $this->is_same_host_url( $url ) ) {
            wp_send_json_error(
                [
                    'message' => 'Invalid URL or widget IDs.',
                ],
                400
            );
        }

        $response = wp_remote_get(
            $url,
            [
                'timeout' => 12,
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error(
                [
                    'message' => $response->get_error_message(),
                ],
                500
            );
        }

        $body = wp_remote_retrieve_body( $response );
        if ( '' === $body ) {
            wp_send_json_error(
                [
                    'message' => 'Empty response body.',
                ],
                500
            );
        }

        $payload = $this->extract_gallery_payload( $body, $ids );
        if ( empty( $payload['slots'] ) ) {
            wp_send_json_error(
                [
                    'message' => 'No gallery slots found.',
                ],
                404
            );
        }

        $response_payload = [
            'slots' => $payload['slots'],
        ];

        wp_send_json_success(
            $response_payload
        );
    }
}
