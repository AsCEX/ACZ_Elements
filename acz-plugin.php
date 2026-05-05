<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-acz-theme-options.php';
require_once __DIR__ . '/includes/class-acz-custom-css.php';

final class ACZ_Plugin {
    const SLUG = 'acz-elements';

    private $rendering_global_single_post = false;

    private function get_version(): string {
        return defined( 'ACZ_ELEMENTS_VERSION' ) ? ACZ_ELEMENTS_VERSION : '1.2.1';
    }

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        add_filter( 'plugin_action_links_' . plugin_basename( ACZ_RC_FILE ), [ $this, 'add_plugin_action_links' ] );

        if ( ! $this->required_plugins_are_active() ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_required_plugins' ] );
            return;
        }

        new ACZ_Theme_Options();
        new ACZ_Custom_CSS();

        add_action( 'wp_head', [ $this, 'render_global_template_preloader_styles' ], 1 );
        add_action( 'wp_head', [ $this, 'render_option_visibility_styles' ] );

        add_action(
            'admin_head-edit-tags.php',
            function () {
                $screen     = get_current_screen();
                $taxonomies = [ 'solution', 'application', 'fixture' ];

                if ( ! $screen || ! in_array( $screen->taxonomy, $taxonomies, true ) ) {
                    return;
                }

                echo wp_kses(
                    '<style>
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
                </style>',
                    [
                        'style' => [],
                    ]
                );
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

                echo wp_kses(
                    '<style>
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
                    
                </style>',
                    [
                        'style' => [],
                    ]
                );
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

        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'wp_body_open', [ $this, 'render_global_header' ], 1 );
        add_filter( 'the_content', [ $this, 'render_global_single_post_content' ], 999 );
        add_filter( 'template_include', [ $this, 'maybe_use_global_archive_template' ], 99 );
        add_filter( 'template_include', [ $this, 'maybe_use_global_search_template' ], 99 );
        add_filter( 'template_include', [ $this, 'maybe_use_global_404_template' ], 99 );
        add_action( 'wp_footer', [ $this, 'render_global_template_preloader_script' ], 999 );
        add_action( 'wp_footer', [ $this, 'render_global_footer' ], 100 );
        add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'elementor/controls/register', [ $this, 'register_controls' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ], 1 );
        add_action( 'acf/include_field_types', [ $this, 'register_acf_field_types' ] );
        add_action( 'acf/register_fields', [ $this, 'register_acf_field_types' ] );
        add_action( 'wp_ajax_acz_post_filter_gallery', [ $this, 'ajax_post_filter_gallery' ] );
        add_action( 'wp_ajax_nopriv_acz_post_filter_gallery', [ $this, 'ajax_post_filter_gallery' ] );
        add_action( 'wp_ajax_acz_get_posts_by_query', [ $this, 'ajax_get_posts_by_query' ] );
        add_action( 'wp_ajax_acz_get_posts_value_titles', [ $this, 'ajax_get_posts_value_titles' ] );
    }

    public function render_global_header() {
        if ( is_admin() || wp_doing_ajax() ) {
            return;
        }

        if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
            return;
        }

        $template_id = ACZ_Theme_Options::get_global_header_template_id();

        if ( ! $template_id ) {
            return;
        }

        if ( get_the_ID() === $template_id ) {
            return;
        }

        $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
        if ( '' === trim( $content ) ) {
            return;
        }

        echo '<div class="acz-global-header" data-template-id="' . esc_attr( (string) $template_id ) . '">';
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
    }

    public function render_global_single_post_content( $content ) {
        if ( $this->rendering_global_single_post || is_admin() || wp_doing_ajax() ) {
            return $content;
        }

        if ( ! is_singular() || is_page() || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
            return $content;
        }

        $template_id = ACZ_Theme_Options::get_global_single_post_template_id();

        if ( ! $template_id || get_the_ID() === $template_id ) {
            return $content;
        }

        $this->rendering_global_single_post = true;

        try {
            $template_content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
        } finally {
            $this->rendering_global_single_post = false;
        }

        if ( '' === trim( $template_content ) ) {
            return $content;
        }

        return '<div class="acz-global-single-post acz-global-template-shell is-loading" data-template-id="' . esc_attr( (string) $template_id ) . '"><div class="acz-global-template-spinner" aria-hidden="true"></div><div class="acz-global-template-content">' . $template_content . '</div></div>';
    }

    public function render_option_visibility_styles() {
        $css_rules = [];

        if ( ! get_field( 'acz_show_testimonials', 'option' ) ) {
            $css_rules[] = '#acz-testimonial{display:none!important;}';
        }

        if ( ! get_field( 'acz_show_media_insights', 'option' ) ) {
            $css_rules[] = '#acz-media-insights{display:none!important;}';
        }

        if ( empty( $css_rules ) ) {
            return;
        }

        echo wp_kses(
            '<style>' . implode( '', $css_rules ) . '</style>',
            [
                'style' => [],
            ]
        );
    }

    public function render_global_template_preloader_styles() {
        if ( ! $this->has_global_template_preloader() ) {
            return;
        }

        ?>
        <style>
            .acz-global-template-shell {
                position: relative;
                min-height: 180px;
            }
            .acz-global-template-shell.is-loading {
                min-height: 60vh;
            }
            .acz-global-template-shell.is-loading .acz-global-template-content {
                opacity: 0;
                visibility: hidden;
            }
            .acz-global-template-content {
                opacity: 1;
                visibility: visible;
                transition: opacity 180ms ease;
            }
            .acz-global-template-spinner {
                display: none;
            }
            .acz-global-template-shell.is-loading .acz-global-template-spinner {
                position: absolute;
                inset: 0;
                z-index: 50;
                display: flex;
                align-items: center;
                justify-content: center;
                background: inherit;
                min-height: 180px;
            }
            .acz-global-template-shell.is-loading .acz-global-template-spinner::before {
                content: "";
                width: 42px;
                height: 42px;
                border: 3px solid rgba(23, 142, 121, 0.18);
                border-top-color: #178e79;
                border-radius: 50%;
                animation: aczGlobalTemplateSpin 800ms linear infinite;
            }
            @keyframes aczGlobalTemplateSpin {
                to {
                    transform: rotate(360deg);
                }
            }
        </style>
        <?php
    }

    public function render_global_template_preloader_script() {
        if ( ! $this->has_global_template_preloader() ) {
            return;
        }

        ?>
        <script>
            (function () {
                var done = false;

                function revealGlobalTemplates() {
                    if (done) {
                        return;
                    }

                    done = true;

                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () {
                            document.querySelectorAll('.acz-global-template-shell.is-loading').forEach(function (shell) {
                                shell.classList.remove('is-loading');
                                shell.classList.add('is-ready');
                            });
                        });
                    });
                }

                function waitForFontsThenReveal() {
                    if (document.fonts && typeof document.fonts.ready !== 'undefined') {
                        document.fonts.ready.then(revealGlobalTemplates).catch(revealGlobalTemplates);
                        return;
                    }

                    revealGlobalTemplates();
                }

                if (document.readyState === 'complete') {
                    waitForFontsThenReveal();
                } else {
                    window.addEventListener('load', waitForFontsThenReveal, { once: true });
                }

                window.setTimeout(revealGlobalTemplates, 4000);
            })();
        </script>
        <?php
    }

    private function has_global_template_preloader(): bool {
        if ( is_admin() || wp_doing_ajax() ) {
            return false;
        }

        if ( is_singular() && ! is_page() ) {
            return (bool) ACZ_Theme_Options::get_global_single_post_template_id();
        }

        if ( is_archive() ) {
            return (bool) ACZ_Theme_Options::get_global_archive_template_id();
        }

        if ( is_search() ) {
            return (bool) ACZ_Theme_Options::get_global_search_template_id();
        }

        if ( is_404() ) {
            return (bool) ACZ_Theme_Options::get_global_404_template_id();
        }

        return false;
    }

    public function maybe_use_global_archive_template( $template ) {
        if ( is_admin() || wp_doing_ajax() || ! is_archive() ) {
            return $template;
        }

        if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
            return $template;
        }

        $template_id = ACZ_Theme_Options::get_global_archive_template_id();

        if ( ! $template_id || get_the_ID() === $template_id ) {
            return $template;
        }

        $archive_template = __DIR__ . '/includes/templates/global-archive.php';

        return file_exists( $archive_template ) ? $archive_template : $template;
    }

    public function maybe_use_global_search_template( $template ) {
        if ( is_admin() || wp_doing_ajax() || ! is_search() ) {
            return $template;
        }

        if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
            return $template;
        }

        $template_id = ACZ_Theme_Options::get_global_search_template_id();

        if ( ! $template_id || get_the_ID() === $template_id ) {
            return $template;
        }

        $search_template = __DIR__ . '/includes/templates/global-search.php';

        return file_exists( $search_template ) ? $search_template : $template;
    }

    public function maybe_use_global_404_template( $template ) {
        if ( is_admin() || wp_doing_ajax() || ! is_404() ) {
            return $template;
        }

        if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
            return $template;
        }

        $template_id = ACZ_Theme_Options::get_global_404_template_id();

        if ( ! $template_id || get_the_ID() === $template_id ) {
            return $template;
        }

        $template_404 = __DIR__ . '/includes/templates/global-404.php';

        return file_exists( $template_404 ) ? $template_404 : $template;
    }

    public function render_global_footer() {
        if ( is_admin() || wp_doing_ajax() ) {
            return;
        }

        if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
            return;
        }

        $template_id = ACZ_Theme_Options::get_global_footer_template_id();
        if ( ! $template_id ) {
            return;
        }

        if ( get_the_ID() === $template_id ) {
            return;
        }

        $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
        if ( '' === trim( $content ) ) {
            return;
        }

        echo '<div class="acz-global-footer" data-template-id="' . esc_attr( (string) $template_id ) . '">';
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
    }

    public function ajax_get_posts_by_query() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
        }

        if ( ! check_ajax_referer( 'acz_get_posts_by_query', 'nonce', false ) ) {
            wp_send_json_error( [
                'message' => 'Invalid nonce',
            ], 403 );
        }

        $search     = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
        $post_types = isset( $_GET['post_type'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_GET['post_type'] ) ) : [ 'post' ];

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
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
        }

        if ( ! check_ajax_referer( 'acz_get_posts_by_query', 'nonce', false ) ) {
            wp_send_json_error( [
                'message' => 'Invalid nonce',
            ], 403 );
        }

        $ids = isset( $_POST['id'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['id'] ) ) : [];
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

        require_once __DIR__ . '/includes/acf/class-acz-acf-field-repeater.php';
        if ( class_exists( '\ACZ_ACF_Field_Repeater' ) ) {
            acf_register_field_type( new \ACZ_ACF_Field_Repeater() );
        }
    }

    public function register_assets() {
        $base_url = plugin_dir_url( __FILE__ );
        $version  = $this->get_version();

        wp_register_style(
            'cec-swiper',
            $base_url . 'assets/vendor/swiper/swiper-bundle.min.css',
            [],
            '12.1.3'
        );

        wp_register_script(
            'cec-swiper',
            $base_url . 'assets/vendor/swiper/swiper-bundle.min.js',
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
            $base_url . 'assets/vendor/coloured-icons/ci.min.css',
            [],
            '1.0.0'
        );

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
        require_once __DIR__ . '/includes/widgets/class-acz-nav-menu-widget.php';

        $widgets_manager->register( new \ACZ_Breadcrumbs_Widget() );
        $widgets_manager->register( new \ACZ_Carousel_Widget() );
        $widgets_manager->register( new \ACZ_Colored_Icon_List_Widget() );
        $widgets_manager->register( new \ACZ_Custom_Meta_Widget() );
        $widgets_manager->register( new \ACZ_Faq_Widget() );
        $widgets_manager->register( new \ACZ_Featured_Post_Widget() );
        $widgets_manager->register( new \ACZ_Logo_Carousel_Widget() );
        $widgets_manager->register( new \ACZ_Media_Gallery_Widget() );
        $widgets_manager->register( new \ACZ_Nav_Menu_Widget() );
        $widgets_manager->register( new \ACZ_Post_Carousel_Widget() );
        $widgets_manager->register( new \ACZ_Post_Content_Widget() );
        $widgets_manager->register( new \ACZ_Post_Filter_Widget() );
        $widgets_manager->register( new \ACZ_Post_Gallery_Widget() );
        $widgets_manager->register( new \ACZ_Post_List_Widget() );
        $widgets_manager->register( new \ACZ_Post_Tabs_Widget() );
        $widgets_manager->register( new \ACZ_Site_Logo_Widget() );
        $widgets_manager->register( new \ACZ_Taxonomy_List_Widget() );
        $widgets_manager->register( new \ACZ_Taxonomy_Widget() );
        $widgets_manager->register( new \ACZ_Timeline_Widget() );
    }

    public function register_controls( $controls_manager ) {
        // No custom controls yet.
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

    public function admin_notice_missing_required_plugins() {
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $missing = function_exists( 'acz_elements_get_missing_required_plugins' )
            ? acz_elements_get_missing_required_plugins()
            : [];

        if ( empty( $missing ) ) {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo esc_html__( 'ACZ Elements requires the following plugins to be installed and active:', 'acz-elements' );
        echo '</p>';
        echo wp_kses_post( acz_elements_format_missing_dependencies_notice( $missing ) );
        echo '</div>';
    }

    private function required_plugins_are_active(): bool {
        return function_exists( 'acz_elements_get_missing_required_plugins' )
            && empty( acz_elements_get_missing_required_plugins() )
            && function_exists( 'acf' );
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
        if ( ! check_ajax_referer( 'acz_post_filter_gallery', 'nonce', false ) ) {
            wp_send_json_error( [
                'message' => 'Invalid nonce',
            ], 403 );
        }

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
        if ( '' === trim( $body ) ) {
            wp_send_json_error(
                [
                    'message' => 'Empty response body.',
                ],
                500
            );
        }

        if ( ! function_exists( 'mb_convert_encoding' ) ) {
            wp_send_json_error(
                [
                    'message' => 'PHP mbstring extension is required.',
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
