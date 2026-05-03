<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-acz-theme-options.php';
require_once __DIR__ . '/includes/class-acz-custom-css.php';

final class ACZ_Plugin {
    const SLUG = 'acz-elements';

    private function get_version(): string {
        return defined( 'ACZ_ELEMENTS_VERSION' ) ? ACZ_ELEMENTS_VERSION : '1.2.1';
    }

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        new ACZ_Theme_Options();
        new ACZ_Custom_CSS();
        add_filter( 'plugin_action_links_' . plugin_basename( ACZ_RC_FILE ), [ $this, 'add_plugin_action_links' ] );

        add_action( 'wp_head', function () {
            $show_testimonials = get_field('acz_show_testimonials', 'option');
            $show_media_insights = get_field('acz_show_media_insights', 'option');

            $testimonials_style = !$show_testimonials ? '#acz-testimonial{ display: none!important; }' : '';
            $media_insights_style = !$show_media_insights ? '#acz-media-insights{ display: none!important; }' : '';

            echo "<style>
                {$testimonials_style}
                {$media_insights_style}
            </style>";
        });

        add_action(
            'admin_head-edit-tags.php',
            function () {
                $screen     = get_current_screen();
                $taxonomies = [ 'solution', 'application', 'fixture' ];

                if ( ! $screen || ! in_array( $screen->taxonomy, $taxonomies, true ) ) {
                    return;
                }

                echo '<style>
                    body #col-left { display: none;width: 100% !important; }
                    body #col-right { width: 100% !important; }
                 
                    body.acz-tax-show-form #col-left { display: block !important;}
                    body.acz-tax-show-form #col-right { display:none!important; }
                    
                    .acz-tax-form-toggle { margin: 8px 0 14px; }
                    
                    #addtag{
                        border: 1px solid #c3c4c7;
                        border-radius: 12px;
                        background: #fff;
                        padding: 24px;
                        max-width: 100%!important;
                    }
                    
                    .acf-field-repeater .acf-repeater .acf-table .acf-row:nth-child(even) .acf-row-handle {
                        background: #235c74 !important;
                    }
                    .acf-field-repeater .acf-repeater .acf-table .acf-row:nth-child(even) td {
                        border-top: 1px solid #235c74 !important;
                        border-bottom: 1px solid #235c74 !important;
                    }
                </style>';
            }
        );

        add_action(
            'admin_head-term.php',
            function () {
                $screen     = get_current_screen();
                $taxonomies = [ 'solution', 'application', 'fixture' ];

                if ( ! $screen || ! in_array( $screen->taxonomy, $taxonomies, true ) ) {
                    return;
                }

                echo '<style>
                    #edittag{
                        border: 1px solid #c3c4c7;
                        border-radius: 12px;
                        background: #fff;
                        padding: 24px;
                        max-width: 100%!important;
                    }
                    
                    .acf-field-repeater .acf-repeater .acf-table .acf-row:nth-child(even) .acf-row-handle {
                        background: #235c74 !important;
                    }
                    .acf-field-repeater .acf-repeater .acf-table .acf-row:nth-child(even) td {
                        border-top: 1px solid #235c74 !important;
                        border-bottom: 1px solid #235c74 !important;
                    }
                    
                </style>';
            }
        );

        add_action(
            'admin_footer-edit-tags.php',
            function () {
                $screen     = get_current_screen();
                $taxonomies = [ 'solution', 'application', 'fixture' ];

                if ( ! $screen || ! in_array( $screen->taxonomy, $taxonomies, true ) ) {
                    return;
                }

                $default_collapsed = ACZ_Theme_Options::get( 'taxonomy_add_form_collapsed', true ) ? '1' : '0';
                $taxonomy = sanitize_key( (string) $screen->taxonomy );
                ?>
                <script>
                    (function () {
                        var colLeft = document.getElementById('col-left');
                        var colRight = document.getElementById('col-right');
                        if (!colLeft || !colRight) {
                            return;
                        }

                        var storageKey = 'aczTaxFormHidden_<?php echo esc_js( $taxonomy ); ?>';
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
                        button.className = 'button button-secondary acz-tax-form-toggle';

                        function applyState() {
                            var isHidden = hidden === '1';
                            document.body.classList.toggle('acz-tax-show-form', isHidden);
                            button.textContent = isHidden ? 'Hide Add Form' : 'Show Add Form';
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
        add_action( 'acf/include_field_types', [ $this, 'register_acf_field_types' ] );
        add_action( 'acf/register_fields', [ $this, 'register_acf_field_types' ] );
        add_action( 'wp_ajax_acz_post_filter_gallery', [ $this, 'ajax_post_filter_gallery' ] );
        add_action( 'wp_ajax_nopriv_acz_post_filter_gallery', [ $this, 'ajax_post_filter_gallery' ] );
        add_action( 'wp_ajax_acz_get_posts_by_query', [ $this, 'ajax_get_posts_by_query' ] );
        add_action( 'wp_ajax_nopriv_acz_get_posts_by_query', [ $this, 'ajax_get_posts_by_query' ] );
        add_action( 'wp_ajax_acz_get_posts_value_titles', [ $this, 'ajax_get_posts_value_titles' ] );
        add_action( 'wp_ajax_nopriv_acz_get_posts_value_titles', [ $this, 'ajax_get_posts_value_titles' ] );
    }

    public function ajax_get_posts_by_query() {
        check_ajax_referer( 'acz_post_carousel_query', 'nonce' );

        $search = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';
        $post_types = isset( $_GET['post_type'] ) ? (array) $_GET['post_type'] : [ 'post' ];

        $query_args = [
            'post_type'      => $post_types,
            'posts_per_page' => 20,
            's'              => $search,
            'post_status'    => 'publish',
        ];

        $query = new WP_Query( $query_args );
        $results = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $results[] = [
                    'id'   => get_the_ID(),
                    'text' => get_the_title() . ' (' . get_the_ID() . ')',
                ];
            }
            wp_reset_postdata();
        }

        wp_send_json_success( [ 'results' => $results ] );
    }

    public function ajax_get_posts_value_titles() {
        check_ajax_referer( 'acz_post_carousel_query', 'nonce' );

        $ids = isset( $_POST['id'] ) ? (array) $_POST['id'] : [];
        if ( empty( $ids ) ) {
            wp_send_json_success( [] );
        }

        $results = [];
        foreach ( $ids as $id ) {
            $post = get_post( $id );
            if ( $post ) {
                $results[ $id ] = $post->post_title . ' (' . $id . ')';
            }
        }

        wp_send_json_success( $results );
    }

    public function register_acf_field_types() {
        if ( ! function_exists( 'acf_register_field_type' ) ) {
            return;
        }

        if ( class_exists( '\ACZ_ACF_Field_Elementor_Icon' ) ) {
            return;
        }

        require_once __DIR__ . '/includes/acf/class-acz-acf-field-elementor-icon.php';

        if ( class_exists( '\ACZ_ACF_Field_Elementor_Icon' ) ) {
            acf_register_field_type( new \ACZ_ACF_Field_Elementor_Icon() );
        }
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
            'acz-common',
            $base_url . 'assets/css/widget.css',
            [],
            $version
        );

        wp_register_style(
            'acz-elements-widget',
            $base_url . 'assets/css/widget.css',
            [ 'acz-common', 'cec-swiper' ],
            $version
        );

        wp_register_style(
            'acz-post-gallery',
            $base_url . 'assets/css/widget.css',
            [ 'acz-common' ],
            $version
        );

        wp_register_style(
            'coloured-icons',
            'https://cdn.jsdelivr.net/gh/dheereshag/coloured-icons@master/app/ci.min.css',
            [],
            '1.9.7'
        );

        wp_enqueue_style( 'coloured-icons' );

        wp_register_script(
            'acz-elements-widget',
            $base_url . 'assets/js/widget.js',
            [ 'cec-swiper' ],
            $version,
            true
        );

        wp_register_script(
            'acz-post-filter-ajax',
            $base_url . 'assets/js/post-filter-ajax.js',
            [],
            $version,
            true
        );

        wp_localize_script(
            'acz-post-filter-ajax',
            'AczPostFilterAjax',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'acz_post_filter_gallery' ),
            ]
        );

        wp_register_script( 'acz-post-carousel-editor', $base_url . 'assets/js/editor-select2.js', [ 'jquery' ], $version, true );
        wp_localize_script(
            'acz-post-carousel-editor',
            'AczPostCarouselEditor',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'acz_get_posts_by_query' ),
            ]
        );
    }

    public function register_categories( $elements_manager ) {
        $reordered_categories = [
            'acz' => [
                'title' => esc_html__( 'ACZ', 'acz-elements' ),
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

        require_once __DIR__ . '/includes/widgets/class-acz-carousel-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-post-gallery-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-post-filter-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-breadcrumbs-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-taxonomy-list-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-featured-post-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-post-carousel-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-post-tabs-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-faq-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-post-list-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-taxonomy-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-custom-meta-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-media-gallery-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-post-content-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-logo-carousel-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-colored-icon-list-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-timeline-widget.php';
        require_once __DIR__ . '/includes/widgets/class-acz-site-logo-widget.php';

        if ( ACZ_Theme_Options::is_widget_enabled( 'carousel' ) ) {
            $widgets_manager->register( new \ACZ_Carousel_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'post_gallery' ) ) {
            $widgets_manager->register( new \ACZ_Post_Gallery_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'post_filter' ) ) {
            $widgets_manager->register( new \ACZ_Post_Filter_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'breadcrumbs' ) ) {
            $widgets_manager->register( new \ACZ_Breadcrumbs_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'taxonomy_list' ) ) {
            $widgets_manager->register( new \ACZ_Taxonomy_List_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'featured_post' ) ) {
            $widgets_manager->register( new \ACZ_Featured_Post_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'post_carousel' ) ) {
            $widgets_manager->register( new \ACZ_Post_Carousel_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'post_tabs' ) ) {
            $widgets_manager->register( new \ACZ_Post_Tabs_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'faq' ) ) {
            $widgets_manager->register( new \ACZ_FAQ_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'post_list' ) ) {
            $widgets_manager->register( new \ACZ_Post_List_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'taxonomy' ) ) {
            $widgets_manager->register( new \ACZ_Taxonomy_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'custom_meta' ) ) {
            $widgets_manager->register( new \ACZ_Custom_Meta_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'media_gallery' ) ) {
            $widgets_manager->register( new \ACZ_Media_Gallery_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'post_content' ) ) {
            $widgets_manager->register( new \ACZ_Post_Content_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'logo_carousel' ) ) {
            $widgets_manager->register( new \ACZ_Logo_Carousel_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'colored_icon_list' ) ) {
            $widgets_manager->register( new \ACZ_Colored_Icon_List_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'timeline' ) ) {
            $widgets_manager->register( new \ACZ_Timeline_Widget() );
        }

        if ( ACZ_Theme_Options::is_widget_enabled( 'site_logo' ) ) {
            $widgets_manager->register( new \ACZ_Site_Logo_Widget() );
        }
    }

    public function add_plugin_action_links( array $links ): array {
        $theme_options_link = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( admin_url( 'admin.php?page=acz-theme-options' ) ),
            esc_html__( 'ACZ Options', 'acz-elements' )
        );

        array_unshift( $links, $theme_options_link );

        return $links;
    }

    public function admin_notice_missing_elementor() {
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo esc_html__( 'ACZ Carousel for Elementor requires Elementor to be installed and activated.', 'acz-elements' );
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

            $slot_nodes = $xpath->query( ".//*[contains(concat(' ', normalize-space(@class), ' '), ' acz-post-gallery-ajax-slot ')]", $widget_node );
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
        check_ajax_referer( 'acz_post_filter_gallery', 'nonce' );

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
