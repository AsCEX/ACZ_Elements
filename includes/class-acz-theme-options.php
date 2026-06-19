<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class ACZ_Theme_Options {
    const OPTION_KEY = 'acz_theme_options';
    const PAGE_SLUG  = 'acz-theme-options';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'maybe_acf_form_head' ], 0 );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'acf/init', [ $this, 'register_acf_fields' ], 20 );
        add_action( 'acf/render_field/key=field_acz_submit_button', [ $this, 'render_acf_submit_button' ] );
        add_filter( 'acf/load_value', [ $this, 'load_legacy_option_value' ], 10, 3 );
        add_filter( 'acf/load_field/key=field_acz_header_location', [ $this, 'load_location_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_footer_location', [ $this, 'load_location_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_header_include_fields', [ $this, 'load_include_field_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_footer_include_fields', [ $this, 'load_include_field_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_single_post_post_type', [ $this, 'load_single_post_type_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_single_post_include_posts', [ $this, 'load_single_post_include_post_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_single_post_sample_post', [ $this, 'load_single_post_sample_post_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_archive_taxonomy', [ $this, 'load_archive_taxonomy_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_archive_include_terms', [ $this, 'load_archive_include_term_choices' ] );
        add_filter( 'acf/load_field/key=field_acz_archive_sample_term', [ $this, 'load_archive_sample_term_choices' ] );

        add_action( 'wp_ajax_acf/fields/post_object/query', [ $this, 'ajax_template_post_object_query' ], 0 );
    }

    public function ajax_template_post_object_query() {

        $field_key = isset( $_REQUEST['field_key'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['field_key'] ) ) : '';

        if ( ! in_array( $field_key, [ 'field_acz_header_template_id', 'field_acz_footer_template_id', 'field_acz_single_post_template_id', 'field_acz_archive_template_id' ], true ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_theme_options' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Forbidden', 'acz-elements' ) ], 403 );
        }

        $search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['s'] ) ) : '';
        $paged  = isset( $_REQUEST['paged'] ) ? max( 1, absint( $_REQUEST['paged'] ) ) : 1;

        $query_args = [
            'post_type'      => 'elementor_library',
            'post_status'    => [ 'publish', 'private', 'inherit' ],
            'posts_per_page' => 20,
            'paged'          => $paged,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        if ( '' !== $search ) {
            $query_args['s'] = $search;
        }

        /*if ( taxonomy_exists( 'elementor_library_type' ) ) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'elementor_library_type',
                    'field'    => 'slug',
                    'terms'    => 'field_acz_header_template_id' === $field_key ? 'header' : 'footer',
                ],
            ];
        }*/
//        print_r($query_args);die;
        $query   = new WP_Query( $query_args );
        $results = [];

        foreach ( $query->posts as $template ) {
            $results[] = [
                'id'   => $template->ID,
                'text' => sprintf(
                    '%s (#%d)',
                    html_entity_decode( get_the_title( $template ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
                    $template->ID
                ),
            ];
        }

        wp_send_json(
            [
                'results' => $results,
                'more'    => $query->max_num_pages > $paged,
            ]
        );
    }

    public function load_legacy_option_value( $value, $post_id, $field ) {
        if ( ! in_array( $post_id, [ 'option', 'options' ], true ) || ! empty( $value ) || empty( $field['name'] ) ) {
            return $value;
        }

        if ( '__acz_missing__' !== get_option( 'options_' . $field['name'], '__acz_missing__' ) ) {
            return $value;
        }

        $legacy_map = [
            'acz_global_headers'                 => 'global_headers',
            'acz_global_footers'                 => 'global_footers',
            'acz_global_single_posts'            => 'global_single_posts',
            'acz_global_archives'                => 'global_archives',
            'acz_global_single_page_template_id' => 'global_single_page_template_id',
            'acz_global_single_post_template_id' => 'global_single_post_template_id',
            'acz_global_archive_template_id'     => 'global_archive_template_id',
            'acz_global_search_template_id'      => 'global_search_template_id',
            'acz_global_404_template_id'         => 'global_404_template_id',
        ];

        if ( ! isset( $legacy_map[ $field['name'] ] ) ) {
            return $value;
        }

        $legacy_options = get_option( self::OPTION_KEY, [] );
        if ( ! is_array( $legacy_options ) || ! array_key_exists( $legacy_map[ $field['name'] ], $legacy_options ) ) {
            return $value;
        }

        return $legacy_options[ $legacy_map[ $field['name'] ] ];
    }

    protected static function get_location_rule_choices(): array {
        return [
            'entire_site' => esc_html__( 'Entire Site', 'acz-elements' ),
            'page'        => esc_html__( 'Page', 'acz-elements' ),
            'single'      => esc_html__( 'Single', 'acz-elements' ),
            'archive'     => esc_html__( 'Archive', 'acz-elements' ),
            'search'      => esc_html__( 'Search', 'acz-elements' ),
            '404'         => esc_html__( '404', 'acz-elements' ),
        ];
    }

    public function load_location_choices( $field ) {
        if ( is_admin() ) {
            $screen = function_exists('get_current_screen') ? get_current_screen() : null;
            $is_options_page = $screen && 'toplevel_page_' . self::PAGE_SLUG === $screen->id;
            
            if ( ! $is_options_page ) {
                return $field;
            }
        }

        $field['choices'] = self::get_location_rule_choices();
        $field['ajax']    = 0;

        return $field;
    }

    public function load_include_field_choices( $field ) {
        if ( is_admin() ) {
            $screen = function_exists('get_current_screen') ? get_current_screen() : null;
            $is_options_page = $screen && 'toplevel_page_' . self::PAGE_SLUG === $screen->id;

            if ( ! $is_options_page ) {
                return $field;
            }
        }

        $field['choices'] = self::get_include_field_choices();
        $field['ajax']    = 0;

        return $field;
    }

    public function load_single_post_type_choices( $field ) {
        $field['choices'] = self::get_single_post_type_choices();
        $field['ajax']    = 0;

        return $field;
    }

    public function load_single_post_include_post_choices( $field ) {
        $field['choices']       = self::get_single_post_include_post_choices();
        $field['ajax']          = 0;
        $field['default_value'] = [ 'all_post' ];

        return $field;
    }

    public function load_single_post_sample_post_choices( $field ) {
        $field['choices'] = self::get_single_post_sample_post_choices();
        $field['ajax']    = 0;

        return $field;
    }

    public function load_archive_taxonomy_choices( $field ) {
        $field['choices'] = self::get_archive_taxonomy_choices();
        $field['ajax']    = 0;

        return $field;
    }

    public function load_archive_include_term_choices( $field ) {
        $field['choices']       = self::get_archive_include_term_choices();
        $field['ajax']          = 0;
        $field['default_value'] = [ 'all_terms' ];

        return $field;
    }

    public function load_archive_sample_term_choices( $field ) {
        $field['choices'] = self::get_archive_sample_term_choices();
        $field['ajax']    = 0;

        return $field;
    }

    protected static function get_include_field_choices_by_location(): array {
        return [
            'page'    => self::get_page_include_choices(),
            'single'  => self::get_single_include_choices(),
            'archive' => self::get_archive_include_choices(),
        ];
    }

    protected static function get_include_field_choices(): array {
        $choices = [];

        foreach ( self::get_include_field_choices_by_location() as $location_choices ) {
            foreach ( $location_choices as $value => $label ) {
                $choices[ $value ] = $label;
            }
        }

        return $choices;
    }

    protected static function get_page_include_choices(): array {
        return self::get_post_include_choices(
            [
                'page',
            ]
        );
    }

    protected static function get_single_include_choices(): array {
        $post_types = get_post_types(
            [
                'public' => true,
            ],
            'names'
        );

        $post_types = array_values(
            array_diff(
                $post_types,
                [
                    'attachment',
                    'elementor_library',
                    'page',
                ]
            )
        );

        return self::get_post_include_choices( $post_types );
    }

    protected static function get_single_post_type_choices(): array {
        $post_types = get_post_types(
            [
                'public' => true,
            ],
            'objects'
        );

        $choices = [];

        foreach ( $post_types as $post_type ) {
            if ( in_array( $post_type->name, [ 'attachment', 'elementor_library', 'page' ], true ) ) {
                continue;
            }

            $choices[ $post_type->name ] = $post_type->labels->singular_name ?: $post_type->label ?: $post_type->name;
        }

        asort( $choices, SORT_NATURAL | SORT_FLAG_CASE );

        return $choices;
    }

    protected static function get_single_post_include_post_choices(): array {
        $choices = [
            'all_post' => esc_html__( 'All Post', 'acz-elements' ),
        ];

        foreach ( self::get_post_include_choices( array_keys( self::get_single_post_type_choices() ) ) as $post_id => $label ) {
            $post_type = get_post_type( (int) $post_id );

            if ( ! $post_type ) {
                continue;
            }

            $choices[ $post_type . ':' . $post_id ] = $label;
        }

        return $choices;
    }

    protected static function get_single_post_sample_post_choices(): array {
        return self::get_post_include_choices( array_keys( self::get_single_post_type_choices() ) );
    }

    protected static function get_single_post_include_post_choices_by_type(): array {
        $choices = [];

        foreach ( self::get_single_post_type_choices() as $post_type => $label ) {
            $choices[ $post_type ] = [
                'all_post' => esc_html__( 'All Post', 'acz-elements' ),
            ];
        }

        foreach ( self::get_post_include_choices( array_keys( self::get_single_post_type_choices() ) ) as $post_id => $label ) {
            $post_type = get_post_type( (int) $post_id );

            if ( ! $post_type || ! isset( $choices[ $post_type ] ) ) {
                continue;
            }

            $choices[ $post_type ][ $post_type . ':' . $post_id ] = $label;
        }

        return $choices;
    }

    protected static function get_single_post_sample_post_choices_by_type(): array {
        $choices = [];

        foreach ( self::get_single_post_type_choices() as $post_type => $label ) {
            $choices[ $post_type ] = [];
        }

        foreach ( self::get_post_include_choices( array_keys( self::get_single_post_type_choices() ) ) as $post_id => $label ) {
            $post_type = get_post_type( (int) $post_id );

            if ( ! $post_type || ! isset( $choices[ $post_type ] ) ) {
                continue;
            }

            $choices[ $post_type ][ (string) $post_id ] = $label;
        }

        return $choices;
    }

    protected static function get_post_include_choices( array $post_types ): array {
        if ( empty( $post_types ) ) {
            return [];
        }

        $posts = get_posts(
            [
                'post_type'      => $post_types,
                'post_status'    => [ 'publish', 'private' ],
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );

        $choices = [];

        foreach ( $posts as $post ) {
            $post_type = get_post_type_object( $post->post_type );
            $type_name = $post_type && ! empty( $post_type->labels->singular_name )
                ? $post_type->labels->singular_name
                : $post->post_type;

            $choices[ (string) $post->ID ] = sprintf(
                '%s - %s (#%d)',
                html_entity_decode( get_the_title( $post ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
                $type_name,
                $post->ID
            );
        }

        return $choices;
    }

    protected static function get_archive_include_choices(): array {
        $choices = [
            'author_archive' => esc_html__( 'Author Archive', 'acz-elements' ),
            'date_archive'   => esc_html__( 'Date Archive', 'acz-elements' ),
        ];

        $post_types = get_post_types(
            [
                'public'      => true,
                'has_archive' => true,
            ],
            'objects'
        );

        foreach ( $post_types as $post_type ) {
            if ( in_array( $post_type->name, [ 'attachment', 'elementor_library' ], true ) ) {
                continue;
            }

            $choices[ 'post_type_archive:' . $post_type->name ] = sprintf(
                '%s Archive',
                $post_type->labels->singular_name ?: $post_type->name
            );
        }

        $taxonomies = get_taxonomies(
            [
                'public' => true,
            ],
            'objects'
        );

        foreach ( $taxonomies as $taxonomy ) {
            $choices[ 'taxonomy:' . $taxonomy->name ] = sprintf(
                '%s Archive',
                $taxonomy->labels->singular_name ?: $taxonomy->name
            );
        }

        asort( $choices, SORT_NATURAL | SORT_FLAG_CASE );

        return $choices;
    }

    protected static function get_archive_taxonomy_choices(): array {
        $taxonomies = get_taxonomies(
            [
                'public' => true,
            ],
            'objects'
        );

        $choices = [];

        foreach ( $taxonomies as $taxonomy ) {
            $choices[ $taxonomy->name ] = $taxonomy->labels->singular_name ?: $taxonomy->label ?: $taxonomy->name;
        }

        asort( $choices, SORT_NATURAL | SORT_FLAG_CASE );

        return $choices;
    }

    protected static function get_archive_include_term_choices(): array {
        $choices = [
            'all_terms' => esc_html__( 'All Terms', 'acz-elements' ),
        ];

        foreach ( self::get_term_include_choices( array_keys( self::get_archive_taxonomy_choices() ) ) as $term_id => $label ) {
            $term = get_term( (int) $term_id );

            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }

            $choices[ $term->taxonomy . ':' . $term_id ] = $label;
        }

        return $choices;
    }

    protected static function get_archive_sample_term_choices(): array {
        return self::get_term_include_choices( array_keys( self::get_archive_taxonomy_choices() ) );
    }

    protected static function get_archive_include_term_choices_by_taxonomy(): array {
        $choices = [];

        foreach ( self::get_archive_taxonomy_choices() as $taxonomy => $label ) {
            $choices[ $taxonomy ] = [
                'all_terms' => esc_html__( 'All Terms', 'acz-elements' ),
            ];
        }

        foreach ( self::get_term_include_choices( array_keys( self::get_archive_taxonomy_choices() ) ) as $term_id => $label ) {
            $term = get_term( (int) $term_id );

            if ( ! $term || is_wp_error( $term ) || ! isset( $choices[ $term->taxonomy ] ) ) {
                continue;
            }

            $choices[ $term->taxonomy ][ $term->taxonomy . ':' . $term_id ] = $label;
        }

        return $choices;
    }

    protected static function get_archive_sample_term_choices_by_taxonomy(): array {
        $choices = [];

        foreach ( self::get_archive_taxonomy_choices() as $taxonomy => $label ) {
            $choices[ $taxonomy ] = [];
        }

        foreach ( self::get_term_include_choices( array_keys( self::get_archive_taxonomy_choices() ) ) as $term_id => $label ) {
            $term = get_term( (int) $term_id );

            if ( ! $term || is_wp_error( $term ) || ! isset( $choices[ $term->taxonomy ] ) ) {
                continue;
            }

            $choices[ $term->taxonomy ][ (string) $term_id ] = $label;
        }

        return $choices;
    }

    protected static function get_term_include_choices( array $taxonomies ): array {
        if ( empty( $taxonomies ) ) {
            return [];
        }

        $terms = get_terms(
            [
                'taxonomy'   => $taxonomies,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]
        );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return [];
        }

        $choices = [];

        foreach ( $terms as $term ) {
            $taxonomy = get_taxonomy( $term->taxonomy );
            $type_name = $taxonomy && ! empty( $taxonomy->labels->singular_name )
                ? $taxonomy->labels->singular_name
                : $term->taxonomy;

            $choices[ (string) $term->term_id ] = sprintf(
                '%s - %s (#%d)',
                html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
                $type_name,
                $term->term_id
            );
        }

        return $choices;
    }

    public static function get_defaults(): array {
        return [
            'global_headers'                 => [],
            'global_footers'                 => [],
            'global_single_posts'            => [],
            'global_archives'                => [],
            'global_single_page_template_id' => 0,
            'global_single_post_template_id' => 0,
            'global_archive_template_id'     => 0,
            'global_search_template_id'      => 0,
            'global_404_template_id'         => 0,
        ];
    }

    public static function get_all(): array {
        $defaults = self::get_defaults();
        $saved    = get_option( self::OPTION_KEY, [] );
        $saved    = is_array( $saved ) ? $saved : [];
        $options  = $defaults;

        foreach ( $defaults as $key => $default ) {
            if ( ! array_key_exists( $key, $saved ) ) {
                continue;
            }

            if ( is_bool( $default ) ) {
                $options[ $key ] = (bool) $saved[ $key ];
                continue;
            }

            if ( is_array( $default ) ) {
                $options[ $key ] = is_array( $saved[ $key ] ) ? $saved[ $key ] : [];
                continue;
            }

            $options[ $key ] = is_scalar( $saved[ $key ] ) ? (string) $saved[ $key ] : $default;
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

    public static function is_widget_enabled( string $widget_id ): bool {
        return (bool) self::get( 'widget_enabled_' . $widget_id, true );
    }

    private static function get_matching_global_template_id( array $rows ): int {

        foreach ( $rows as $row ) {
            $template_id = self::get_matching_global_row_template_id( $row, false );

            if ( $template_id ) {
                return $template_id;
            }
        }

        foreach ( $rows as $row ) {
            $template_id = self::get_matching_global_row_template_id( $row, true );

            if ( $template_id ) {
                return $template_id;
            }
        }


        return 0;
    }

    private static function get_matching_global_row_template_id( $row, bool $entire_site_pass ): int {
        if ( ! is_array( $row ) ) {
            return 0;
        }

        $template_id = self::normalize_template_id(
            self::get_global_row_value(
                $row,
                [
                    'template_id',
                    'field_acz_header_template_id',
                    'field_acz_footer_template_id',
                ],
                0
            )
        );

        if ( ! $template_id ) {
            return 0;
        }

        $location = self::normalize_location_rule(
            self::get_global_row_value(
                $row,
                [
                    'location',
                    'field_acz_header_location',
                    'field_acz_footer_location',
                ],
                'entire_site'
            )
        );
        $location = '' !== $location ? $location : 'entire_site';

        if ( $entire_site_pass !== ( 'entire_site' === $location ) ) {
            return 0;
        }

        if ( ! self::location_rule_matches( $location ) ) {
            return 0;
        }

        $include_fields = self::get_global_row_value(
            $row,
            [
                'include_fields',
                'field_acz_header_include_fields',
                'field_acz_footer_include_fields',
            ],
            []
        );
        $include_fields = array_filter( array_map( 'sanitize_text_field', (array) $include_fields ) );

        if ( ! empty( $include_fields ) && ! self::include_fields_match_location( $location, $include_fields ) ) {
            return 0;
        }

        return 'elementor_library' === get_post_type( $template_id ) ? $template_id : 0;
    }

    private static function location_rule_matches( $location ): bool {
        $location = self::normalize_location_rule( $location );

        if ( '' === $location ) {
            $location = 'entire_site';
        }


        if ( array_key_exists( $location, self::get_location_rule_choices() ) ) {
            switch ( $location ) {
                case 'entire_site':
                    return true;
                case 'page':
                    return is_page();
                case 'single':
                    return is_singular() && ! is_page();
                case 'archive':
                    return is_archive();
                case 'search':
                    return is_search();
                case '404':
                    return is_404();
            }
        }

        $current_id = get_queried_object_id();

        if ( ! $current_id ) {
            $current_id = get_the_ID();
        }

        return is_numeric( $location ) && $current_id && (int) $location === (int) $current_id;
    }

    private static function normalize_location_rule( $location ): string {
        $location = sanitize_text_field( (string) $location );
        $normalized_location = strtolower( str_replace( [ '-', ' ' ], '_', $location ) );

        if ( array_key_exists( $normalized_location, self::get_location_rule_choices() ) ) {
            return $normalized_location;
        }

        return $location;
    }

    private static function get_global_row_value( array $row, array $keys, $default ) {
        foreach ( $keys as $key ) {
            if ( array_key_exists( $key, $row ) ) {
                return $row[ $key ];
            }
        }

        return $default;
    }

    private static function include_fields_match_location( string $location, array $include_fields ): bool {
        if ( 'entire_site' === $location ) {
            return true;
        }

        switch ( $location ) {
            case 'page':
            case 'single':
                $current_id = get_queried_object_id();

                if ( ! $current_id ) {
                    $current_id = get_the_ID();
                }

                return $current_id && in_array( (string) $current_id, $include_fields, true );

            case 'archive':
                return self::archive_include_fields_match( $include_fields );
        }

        return true;
    }

    private static function archive_include_fields_match( array $include_fields ): bool {
        if ( in_array( 'author_archive', $include_fields, true ) && is_author() ) {
            return true;
        }

        if ( in_array( 'date_archive', $include_fields, true ) && is_date() ) {
            return true;
        }

        foreach ( $include_fields as $include_field ) {
            if ( 0 === strpos( $include_field, 'post_type_archive:' ) ) {
                $post_type = substr( $include_field, strlen( 'post_type_archive:' ) );

                if ( $post_type && is_post_type_archive( $post_type ) ) {
                    return true;
                }
            }

            if ( 0 === strpos( $include_field, 'taxonomy:' ) ) {
                $taxonomy = substr( $include_field, strlen( 'taxonomy:' ) );

                if ( ! $taxonomy ) {
                    continue;
                }

                if ( 'category' === $taxonomy && is_category() ) {
                    return true;
                }

                if ( 'post_tag' === $taxonomy && is_tag() ) {
                    return true;
                }

                if ( is_tax( $taxonomy ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function normalize_template_id( $template ): int {
        if ( $template instanceof WP_Post ) {
            return absint( $template->ID );
        }

        if ( is_array( $template ) && isset( $template['ID'] ) ) {
            return absint( $template['ID'] );
        }

        return absint( $template );
    }

    private static function normalize_include_post_id( $include_post, string $post_type = '' ): int {
        $include_post = sanitize_text_field( (string) $include_post );

        if ( false !== strpos( $include_post, ':' ) ) {
            [ $include_post_type, $post_id ] = array_pad( explode( ':', $include_post, 2 ), 2, '' );

            if ( '' !== $post_type && $include_post_type !== $post_type ) {
                return 0;
            }

            return absint( $post_id );
        }

        return absint( $include_post );
    }

    private static function normalize_include_term_id( $include_term, string $taxonomy = '' ): int {
        $include_term = sanitize_text_field( (string) $include_term );

        if ( false !== strpos( $include_term, ':' ) ) {
            [ $include_taxonomy, $term_id ] = array_pad( explode( ':', $include_term, 2 ), 2, '' );

            if ( '' !== $taxonomy && $include_taxonomy !== $taxonomy ) {
                return 0;
            }

            return absint( $term_id );
        }

        return absint( $include_term );
    }

    private static function get_matching_single_post_template_id( array $rows ): int {
        if ( ! is_singular() || is_page() ) {
            return 0;
        }

        $current_id = get_queried_object_id();

        if ( ! $current_id ) {
            $current_id = get_the_ID();
        }

        if ( ! $current_id ) {
            return 0;
        }

        $current_post_type = get_post_type( $current_id );

        if ( ! $current_post_type ) {
            return 0;
        }

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $post_type = sanitize_key(
                (string) self::get_global_row_value(
                    $row,
                    [
                        'post_type',
                        'field_acz_single_post_post_type',
                    ],
                    ''
                )
            );

            if ( $post_type !== $current_post_type ) {
                continue;
            }

            $include_posts = (array) self::get_global_row_value(
                $row,
                [
                    'include_posts',
                    'field_acz_single_post_include_posts',
                ],
                [ 'all_post' ]
            );

            $matches_current_post = empty( $include_posts ) || in_array( 'all_post', $include_posts, true );

            if ( ! $matches_current_post ) {
                foreach ( $include_posts as $include_post ) {
                    if ( (int) $current_id === self::normalize_include_post_id( $include_post, $post_type ) ) {
                        $matches_current_post = true;
                        break;
                    }
                }
            }

            if ( ! $matches_current_post ) {
                continue;
            }

            $template_id = self::normalize_template_id(
                self::get_global_row_value(
                    $row,
                    [
                        'template_id',
                        'field_acz_single_post_template_id',
                    ],
                    0
                )
            );

            if ( $template_id && 'elementor_library' === get_post_type( $template_id ) ) {
                return $template_id;
            }
        }

        return 0;
    }

    private static function get_matching_archive_template_id( array $rows ): int {
        if ( ! is_archive() || ! is_tax() && ! is_category() && ! is_tag() ) {
            return 0;
        }

        $queried_object = get_queried_object();

        if ( ! $queried_object instanceof WP_Term ) {
            return 0;
        }

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $taxonomy = sanitize_key(
                (string) self::get_global_row_value(
                    $row,
                    [
                        'taxonomy',
                        'field_acz_archive_taxonomy',
                    ],
                    ''
                )
            );

            if ( $taxonomy !== $queried_object->taxonomy ) {
                continue;
            }

            $include_terms = (array) self::get_global_row_value(
                $row,
                [
                    'include_terms',
                    'field_acz_archive_include_terms',
                ],
                [ 'all_terms' ]
            );

            $matches_current_term = empty( $include_terms ) || in_array( 'all_terms', $include_terms, true );

            if ( ! $matches_current_term ) {
                foreach ( $include_terms as $include_term ) {
                    if ( (int) $queried_object->term_id === self::normalize_include_term_id( $include_term, $taxonomy ) ) {
                        $matches_current_term = true;
                        break;
                    }
                }
            }

            if ( ! $matches_current_term ) {
                continue;
            }

            $template_id = self::normalize_template_id(
                self::get_global_row_value(
                    $row,
                    [
                        'template_id',
                        'field_acz_archive_template_id',
                    ],
                    0
                )
            );

            if ( $template_id && 'elementor_library' === get_post_type( $template_id ) ) {
                return $template_id;
            }
        }

        return 0;
    }

    private static function get_single_post_rows(): array {
        $single_posts = [];

        if ( function_exists( 'get_field' ) ) {
            $single_posts = get_field( 'acz_global_single_posts', 'option' );

            if ( empty( $single_posts ) ) {
                $single_posts = get_option( 'options_acz_global_single_posts' );
            }
        }

        if ( empty( $single_posts ) ) {
            $single_posts = self::get( 'global_single_posts', [] );
        }

        return is_array( $single_posts ) ? $single_posts : [];
    }

    private static function get_archive_rows(): array {
        $archives = [];

        if ( function_exists( 'get_field' ) ) {
            $archives = get_field( 'acz_global_archives', 'option' );

            if ( empty( $archives ) ) {
                $archives = get_option( 'options_acz_global_archives' );
            }
        }

        if ( empty( $archives ) ) {
            $archives = self::get( 'global_archives', [] );
        }

        return is_array( $archives ) ? $archives : [];
    }

    public static function get_current_elementor_template_id(): int {
        $candidates = [];

        foreach ( [ 'post', 'post_id', 'editor_post_id' ] as $key ) {
            if ( isset( $_REQUEST[ $key ] ) && is_scalar( $_REQUEST[ $key ] ) ) {
                $candidates[] = absint( wp_unslash( $_REQUEST[ $key ] ) );
            }
        }

        $queried_id = get_queried_object_id();
        if ( $queried_id ) {
            $candidates[] = absint( $queried_id );
        }

        $current_id = get_the_ID();
        if ( $current_id ) {
            $candidates[] = absint( $current_id );
        }

        foreach ( array_filter( array_unique( $candidates ) ) as $candidate ) {
            if ( 'elementor_library' === get_post_type( $candidate ) ) {
                return (int) $candidate;
            }
        }

        return 0;
    }

    public static function is_elementor_preview_context(): bool {
        if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance ) ) {
            return false;
        }

        $plugin = \Elementor\Plugin::$instance;

        if ( isset( $plugin->editor ) && method_exists( $plugin->editor, 'is_edit_mode' ) && $plugin->editor->is_edit_mode() ) {
            return true;
        }

        if ( isset( $plugin->preview ) && method_exists( $plugin->preview, 'is_preview_mode' ) && $plugin->preview->is_preview_mode() ) {
            return true;
        }

        return false;
    }

    public static function get_global_header_template_id(): int {
        $headers = [];

        if ( function_exists( 'get_field' ) ) {
            $headers = get_field( 'acz_global_headers', 'option' );
            // If ACF Free or get_field failed for 'option', try manual retrieval
            if ( empty( $headers ) ) {
                $headers = get_option( 'options_acz_global_headers' );
            }
        }

        if ( empty( $headers ) ) {
            $headers = self::get( 'global_headers', [] );
        }

        if ( ! is_array( $headers ) || empty( $headers ) ) {
            return 0;
        }

        return self::get_matching_global_template_id( $headers );
    }

    public static function get_global_footer_template_id(): int {
        $footers = [];

        if ( function_exists( 'get_field' ) ) {
            $footers = get_field( 'acz_global_footers', 'option' );
            
            // If ACF Free or get_field failed for 'option', try manual retrieval
            if ( empty( $footers ) ) {
                $footers = get_option( 'options_acz_global_footers' );
            }
        }

        if ( empty( $footers ) ) {
            $footers = self::get( 'global_footers', [] );
        }

        if ( ! is_array( $footers ) || empty( $footers ) ) {
            return 0;
        }

        return self::get_matching_global_template_id( $footers );
    }

    public static function get_global_single_page_template_id(): int {
        $template_id = 0;
        if ( function_exists( 'get_field' ) ) {
            $template_id = absint( get_field( 'acz_global_single_page_template_id', 'option' ) );
            if ( ! $template_id ) {
                $template_id = absint( get_option( 'options_acz_global_single_page_template_id' ) );
            }
        }
        if ( ! $template_id ) {
            $template_id = absint( self::get( 'global_single_page_template_id', 0 ) );
        }
        return ( $template_id && 'elementor_library' === get_post_type( $template_id ) ) ? $template_id : 0;
    }

    public static function get_global_single_post_template_id(): int {
        $single_posts = [];

        if ( function_exists( 'get_field' ) ) {
            $single_posts = get_field( 'acz_global_single_posts', 'option' );

            if ( empty( $single_posts ) ) {
                $single_posts = get_option( 'options_acz_global_single_posts' );
            }
        }

        if ( empty( $single_posts ) ) {
            $single_posts = self::get( 'global_single_posts', [] );
        }

        if ( is_array( $single_posts ) && ! empty( $single_posts ) ) {
            $matching_template_id = self::get_matching_single_post_template_id( $single_posts );

            if ( $matching_template_id ) {
                return $matching_template_id;
            }
        }

        $template_id = 0;
        if ( function_exists( 'get_field' ) ) {
            $template_id = absint( get_field( 'acz_global_single_post_template_id', 'option' ) );
            if ( ! $template_id ) {
                $template_id = absint( get_option( 'options_acz_global_single_post_template_id' ) );
            }
        }
        if ( ! $template_id ) {
            $template_id = absint( self::get( 'global_single_post_template_id', 0 ) );
        }
        return ( $template_id && 'elementor_library' === get_post_type( $template_id ) ) ? $template_id : 0;
    }

    public static function get_elementor_preview_sample_post_id( int $template_id = 0 ): int {
        $template_id = $template_id ?: self::get_current_elementor_template_id();

        if ( ! $template_id ) {
            return 0;
        }

        foreach ( self::get_single_post_rows() as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $row_template_id = self::normalize_template_id(
                self::get_global_row_value(
                    $row,
                    [
                        'template_id',
                        'field_acz_single_post_template_id',
                    ],
                    0
                )
            );

            if ( $row_template_id !== $template_id ) {
                continue;
            }

            $post_type = sanitize_key(
                (string) self::get_global_row_value(
                    $row,
                    [
                        'post_type',
                        'field_acz_single_post_post_type',
                    ],
                    ''
                )
            );
            $sample_post_id = absint(
                self::get_global_row_value(
                    $row,
                    [
                        'sample_post',
                        'field_acz_single_post_sample_post',
                    ],
                    0
                )
            );

            if ( $sample_post_id && ( '' === $post_type || $post_type === get_post_type( $sample_post_id ) ) ) {
                return $sample_post_id;
            }
        }

        return 0;
    }

    public static function get_global_archive_template_id(): int {
        $archives = [];

        if ( function_exists( 'get_field' ) ) {
            $archives = get_field( 'acz_global_archives', 'option' );

            if ( empty( $archives ) ) {
                $archives = get_option( 'options_acz_global_archives' );
            }
        }

        if ( empty( $archives ) ) {
            $archives = self::get( 'global_archives', [] );
        }

        if ( is_array( $archives ) && ! empty( $archives ) ) {
            $matching_template_id = self::get_matching_archive_template_id( $archives );

            if ( $matching_template_id ) {
                return $matching_template_id;
            }
        }

        $template_id = 0;
        if ( function_exists( 'get_field' ) ) {
            $template_id = absint( get_field( 'acz_global_archive_template_id', 'option' ) );
            if ( ! $template_id ) {
                $template_id = absint( get_option( 'options_acz_global_archive_template_id' ) );
            }
        }
        if ( ! $template_id ) {
            $template_id = absint( self::get( 'global_archive_template_id', 0 ) );
        }
        return ( $template_id && 'elementor_library' === get_post_type( $template_id ) ) ? $template_id : 0;
    }

    public static function get_elementor_preview_sample_term( int $template_id = 0 ) {
        $template_id = $template_id ?: self::get_current_elementor_template_id();

        if ( ! $template_id ) {
            return null;
        }

        foreach ( self::get_archive_rows() as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $row_template_id = self::normalize_template_id(
                self::get_global_row_value(
                    $row,
                    [
                        'template_id',
                        'field_acz_archive_template_id',
                    ],
                    0
                )
            );

            if ( $row_template_id !== $template_id ) {
                continue;
            }

            $taxonomy = sanitize_key(
                (string) self::get_global_row_value(
                    $row,
                    [
                        'taxonomy',
                        'field_acz_archive_taxonomy',
                    ],
                    ''
                )
            );
            $sample_term_id = absint(
                self::get_global_row_value(
                    $row,
                    [
                        'sample_term',
                        'field_acz_archive_sample_term',
                    ],
                    0
                )
            );

            if ( ! $sample_term_id ) {
                continue;
            }

            $term = $taxonomy ? get_term( $sample_term_id, $taxonomy ) : get_term( $sample_term_id );

            if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
                return $term;
            }
        }

        return null;
    }

    public static function get_elementor_preview_sample_term_id( int $template_id = 0 ): int {
        $term = self::get_elementor_preview_sample_term( $template_id );

        return $term instanceof WP_Term ? (int) $term->term_id : 0;
    }

    public static function get_global_search_template_id(): int {
        $template_id = 0;
        if ( function_exists( 'get_field' ) ) {
            $template_id = absint( get_field( 'acz_global_search_template_id', 'option' ) );
            if ( ! $template_id ) {
                $template_id = absint( get_option( 'options_acz_global_search_template_id' ) );
            }
        }
        if ( ! $template_id ) {
            $template_id = absint( self::get( 'global_search_template_id', 0 ) );
        }
        return ( $template_id && 'elementor_library' === get_post_type( $template_id ) ) ? $template_id : 0;
    }

    public static function get_global_404_template_id(): int {
        $template_id = 0;
        if ( function_exists( 'get_field' ) ) {
            $template_id = absint( get_field( 'acz_global_404_template_id', 'option' ) );
            if ( ! $template_id ) {
                $template_id = absint( get_option( 'options_acz_global_404_template_id' ) );
            }
        }
        if ( ! $template_id ) {
            $template_id = absint( self::get( 'global_404_template_id', 0 ) );
        }
        return ( $template_id && 'elementor_library' === get_post_type( $template_id ) ) ? $template_id : 0;
    }

    public function register_menu() {
        $callback = [ $this, 'render_page' ];
        $icon_url = ACZ_RC_URL . 'assets/img/acz-icon.png';

        add_action( 'admin_head', function () {
            ?>
            <style>

                #adminmenu .toplevel_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> .wp-menu-image img {
                    width: 20px;
                    height: 20px;
                    margin-top: -1px;
                    object-fit: contain;
                    padding-top: 7px;
                }
                #adminmenu .toplevel_page_acz-theme-options.current .wp-menu-image img{
                    filter: brightness(100);
                }
            </style>
            <?php
        });

        add_menu_page(
            esc_html__( 'ACZ Theme Options', 'acz-elements' ),
            esc_html__( 'ACZ Options', 'acz-elements' ),
            'edit_theme_options',
            self::PAGE_SLUG,
            $callback,
            $icon_url,
            4
        );
    }

    public function maybe_acf_form_head() {
        if ( ! is_admin() || ! function_exists( 'acf_form_head' ) ) {
            return;
        }

        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
        if ( self::PAGE_SLUG !== $page ) {
            return;
        }

        acf_form_head();
    }

    public function register_acf_options_page() {
        // The ACZ admin page is the canonical layout for both ACF Free and ACF Pro.
        // Do not register ACF Pro's native options-page renderer for this screen.
    }

    public function register_acf_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group(
            [
                'key'      => 'group_acz_theme_options',
                'title'    => esc_html__( 'ACZ Theme Options', 'acz-elements' ),
                'fields'   => [
                    [
                        'key'   => 'field_acz_tab_header',
                        'label' => esc_html__( 'Header', 'acz-elements' ),
                        'type'  => 'tab',
                            'placement' => 'left',
                    ],
                    [
                        'key'          => 'field_acz_global_headers',
                        'label'        => esc_html__( 'Global Headers', 'acz-elements' ),
                        'name'         => 'acz_global_headers',
                        'type'         => 'acz_repeater',
                        'instructions' => esc_html__( 'Add one or more headers and specify their locations.', 'acz-elements' ),
                        'required'     => 0,
                        'layout'       => 'table',
                        'button_label' => esc_html__( 'Add Header', 'acz-elements' ),
                        'sub_fields'   => [
                            [
                                'key'           => 'field_acz_header_template_id',
                                'label'         => esc_html__( 'Header Template', 'acz-elements' ),
                                'name'          => 'template_id',
                                'type'          => 'post_object',
                                'post_type'     => [ 'elementor_library' ],

                                'taxonomy'      => [],
                                'allow_null'    => 1,
                                'multiple'      => 0,
                                'return_format' => 'object',
                                'ui'            => 1,
                            ],
                            [
                                'key'     => 'field_acz_header_location',
                                'label'   => esc_html__( 'Location', 'acz-elements' ),
                                'name'    => 'location',
                                'type'    => 'select',
                                'choices' => self::get_location_rule_choices(),
                                'ui'             => 1,
                                'ajax'           => 0,
                                'placeholder'    => esc_html__( 'Select a location rule...', 'acz-elements' ),
                                'allow_null'     => 0,
                                'default_value'  => 'entire_site',
                            ],
                            [
                                'key'            => 'field_acz_header_include_fields',
                                'label'          => esc_html__( 'Include Fields', 'acz-elements' ),
                                'name'           => 'include_fields',
                                'type'           => 'select',
                                'choices'        => self::get_include_field_choices(),
                                'ui'             => 1,
                                'ajax'           => 0,
                                'multiple'       => 1,
                                'placeholder'    => esc_html__( 'Select specific locations...', 'acz-elements' ),
                                'allow_null'     => 1,
                            ],
                        ],
                    ],
                    [
                        'key'   => 'field_acz_tab_footer',
                        'label' => esc_html__( 'Footer', 'acz-elements' ),
                        'type'  => 'tab',
                    ],
                    [
                        'key'          => 'field_acz_global_footers',
                        'label'        => esc_html__( 'Global Footers', 'acz-elements' ),
                        'name'         => 'acz_global_footers',
                        'type'         => 'acz_repeater',
                        'instructions' => esc_html__( 'Add one or more footers and specify their locations.', 'acz-elements' ),
                        'required'     => 0,
                        'layout'       => 'table',
                        'button_label' => esc_html__( 'Add Footer', 'acz-elements' ),
                        'sub_fields'   => [
                            [
                                'key'           => 'field_acz_footer_template_id',
                                'label'         => esc_html__( 'Footer Template', 'acz-elements' ),
                                'name'          => 'template_id',
                                'type'          => 'post_object',
                                'post_type'     => [ 'elementor_library' ],
                                'post_status'   => [ 'publish', 'private', 'inherit' ],
                                'taxonomy'      => [ 'elementor_library_type:footer' ],
                                'return_format' => 'id',
                                'multiple'      => 0,
                                'ui'            => 1,
                                'allow_null'    => 1,
                            ],
                            [
                                'key'     => 'field_acz_footer_location',
                                'label'   => esc_html__( 'Location', 'acz-elements' ),
                                'name'    => 'location',
                                'type'    => 'select',
                                'choices' => self::get_location_rule_choices(),
                                'ui'             => 1,
                                'ajax'           => 0,
                                'placeholder'    => esc_html__( 'Select a location rule...', 'acz-elements' ),
                                'allow_null'     => 0,
                                'default_value'  => 'entire_site',
                            ],
                            [
                                'key'            => 'field_acz_footer_include_fields',
                                'label'          => esc_html__( 'Include Fields', 'acz-elements' ),
                                'name'           => 'include_fields',
                                'type'           => 'select',
                                'choices'        => self::get_include_field_choices(),
                                'ui'             => 1,
                                'ajax'           => 0,
                                'multiple'       => 1,
                                'placeholder'    => esc_html__( 'Select specific locations...', 'acz-elements' ),
                                'allow_null'     => 1,
                            ],
                        ],
                    ],
                    [
                        'key'   => 'field_acz_tab_single_post',
                        'label' => esc_html__( 'Single Post', 'acz-elements' ),
                        'type'  => 'tab',
                    ],
                    [
                        'key'          => 'field_acz_global_single_posts',
                        'label'        => esc_html__( 'Single Post', 'acz-elements' ),
                        'name'         => 'acz_global_single_posts',
                        'type'         => 'acz_repeater',
                        'instructions' => esc_html__( 'Add one or more single post templates and choose where each one applies.', 'acz-elements' ),
                        'required'     => 0,
                        'layout'       => 'table',
                        'button_label' => esc_html__( 'Add Single Post', 'acz-elements' ),
                        'sub_fields'   => [
                            [
                                'key'           => 'field_acz_single_post_post_type',
                                'label'         => esc_html__( 'List of Post Type', 'acz-elements' ),
                                'name'          => 'post_type',
                                'type'          => 'select',
                                'choices'       => self::get_single_post_type_choices(),
                                'ui'            => 1,
                                'ajax'          => 0,
                                'allow_null'    => 0,
                                'multiple'      => 0,
                                'placeholder'   => esc_html__( 'Select a post type...', 'acz-elements' ),
                                'default_value' => 'post',
                            ],
                            [
                                'key'           => 'field_acz_single_post_template_id',
                                'label'         => esc_html__( 'Elementor Template', 'acz-elements' ),
                                'name'          => 'template_id',
                                'type'          => 'post_object',
                                'post_type'     => [ 'elementor_library' ],
                                'post_status'   => [ 'publish', 'private', 'inherit' ],
                                'return_format' => 'id',
                                'multiple'      => 0,
                                'ui'            => 1,
                                'allow_null'    => 1,
                            ],
                            [
                                'key'            => 'field_acz_single_post_include_posts',
                                'label'          => esc_html__( 'Include Post', 'acz-elements' ),
                                'name'           => 'include_posts',
                                'type'           => 'select',
                                'choices'        => self::get_single_post_include_post_choices(),
                                'ui'             => 1,
                                'ajax'           => 0,
                                'multiple'       => 1,
                                'allow_null'     => 1,
                                'default_value'  => [ 'all_post' ],
                                'placeholder'    => esc_html__( 'All Post', 'acz-elements' ),
                            ],
                            [
                                'key'           => 'field_acz_single_post_sample_post',
                                'label'         => esc_html__( 'Sample Post', 'acz-elements' ),
                                'name'          => 'sample_post',
                                'type'          => 'select',
                                'choices'       => self::get_single_post_sample_post_choices(),
                                'ui'            => 1,
                                'ajax'          => 0,
                                'multiple'      => 0,
                                'allow_null'    => 1,
                                'placeholder'   => esc_html__( 'Select a preview post...', 'acz-elements' ),
                            ],
                        ],
                    ],
                    [
                        'key'   => 'field_acz_tab_archive',
                        'label' => esc_html__( 'Archive', 'acz-elements' ),
                        'type'  => 'tab',
                    ],
                    [
                        'key'          => 'field_acz_global_archives',
                        'label'        => esc_html__( 'Archive', 'acz-elements' ),
                        'name'         => 'acz_global_archives',
                        'type'         => 'acz_repeater',
                        'instructions' => esc_html__( 'Add one or more archive templates and choose which taxonomy terms each one applies to.', 'acz-elements' ),
                        'required'     => 0,
                        'layout'       => 'table',
                        'button_label' => esc_html__( 'Add Archive', 'acz-elements' ),
                        'sub_fields'   => [
                            [
                                'key'           => 'field_acz_archive_taxonomy',
                                'label'         => esc_html__( 'List Taxonomy', 'acz-elements' ),
                                'name'          => 'taxonomy',
                                'type'          => 'select',
                                'choices'       => self::get_archive_taxonomy_choices(),
                                'ui'            => 1,
                                'ajax'          => 0,
                                'allow_null'    => 0,
                                'multiple'      => 0,
                                'placeholder'   => esc_html__( 'Select a taxonomy...', 'acz-elements' ),
                                'default_value' => 'category',
                            ],
                            [
                                'key'           => 'field_acz_archive_template_id',
                                'label'         => esc_html__( 'Elementor Template', 'acz-elements' ),
                                'name'          => 'template_id',
                                'type'          => 'post_object',
                                'post_type'     => [ 'elementor_library' ],
                                'post_status'   => [ 'publish', 'private', 'inherit' ],
                                'return_format' => 'id',
                                'multiple'      => 0,
                                'ui'            => 1,
                                'allow_null'    => 1,
                            ],
                            [
                                'key'            => 'field_acz_archive_include_terms',
                                'label'          => esc_html__( 'Include Terms', 'acz-elements' ),
                                'name'           => 'include_terms',
                                'type'           => 'select',
                                'choices'        => self::get_archive_include_term_choices(),
                                'ui'             => 1,
                                'ajax'           => 0,
                                'multiple'       => 1,
                                'allow_null'     => 1,
                                'default_value'  => [ 'all_terms' ],
                                'placeholder'    => esc_html__( 'All Terms', 'acz-elements' ),
                            ],
                            [
                                'key'           => 'field_acz_archive_sample_term',
                                'label'         => esc_html__( 'Sample Term', 'acz-elements' ),
                                'name'          => 'sample_term',
                                'type'          => 'select',
                                'choices'       => self::get_archive_sample_term_choices(),
                                'ui'            => 1,
                                'ajax'          => 0,
                                'multiple'      => 0,
                                'allow_null'    => 1,
                                'placeholder'   => esc_html__( 'Select a preview term...', 'acz-elements' ),
                            ],
                        ],
                    ],
                    [
                        'key'   => 'field_acz_tab_search',
                        'label' => esc_html__( 'Search Page', 'acz-elements' ),
                        'type'  => 'tab',
                    ],
                    [
                        'key'           => 'field_acz_global_search_template_id',
                        'label'         => esc_html__( 'Search Page Template', 'acz-elements' ),
                        'name'          => 'acz_global_search_template_id',
                        'type'          => 'post_object',
                        'post_type'     => [ 'elementor_library' ],
                        'post_status'   => [ 'publish' ],
                        'return_format' => 'id',
                        'allow_null'    => 1,
                        'multiple'      => 0,
                        'ui'            => 1,
                        'instructions'  => esc_html__( 'Select a saved Elementor template for the search results page.', 'acz-elements' ),
                    ],
                    [
                        'key'   => 'field_acz_tab_404',
                        'label' => esc_html__( '404 Page', 'acz-elements' ),
                        'type'  => 'tab',
                    ],
                    [
                        'key'           => 'field_acz_global_404_template_id',
                        'label'         => esc_html__( '404 Page Template', 'acz-elements' ),
                        'name'          => 'acz_global_404_template_id',
                        'type'          => 'post_object',
                        'post_type'     => [ 'elementor_library' ],
                        'post_status'   => [ 'publish' ],
                        'return_format' => 'id',
                        'allow_null'    => 1,
                        'multiple'      => 0,
                        'ui'            => 1,
                        'instructions'  => esc_html__( 'Select a saved Elementor template for the 404 error page.', 'acz-elements' ),
                    ]
                ],
                'location' => [
                    [
                        [
                            'param'    => 'options_page',
                            'operator' => '==',
                            'value'    => self::PAGE_SLUG,
                        ],
                    ],
                ],
                'menu_order' => 10,
                'label_placement' => 'left',
                'position'        => 'acf_after_title',
                'style'           => 'default',
            ]
        );
    }

    public function render_acf_submit_button( $field ) {
//        echo '<div class="acf-actions">';
        echo '<input type="submit" class="button button-primary acf-submit-btn button-large" value="' . esc_attr__( 'Save Changes', 'acz-elements' ) . '">';
//        echo '</div>';
    }

    public function register_settings() {
        register_setting(
            'acz_theme_options_group',
            self::OPTION_KEY,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_options' ],
                'default'           => self::get_defaults(),
            ]
        );
    }

    public function sanitize_options( $input ): array {
        $defaults  = self::get_defaults();
        $sanitized = $defaults;
        $input     = is_array( $input ) ? $input : [];

        foreach ( $defaults as $key => $default ) {
            if ( ! isset( $input[ $key ] ) ) {
                continue;
            }

            if ( $key === 'global_headers' ) {
                $sanitized[ $key ] = $this->sanitize_headers_repeater( $input[ $key ] );
                continue;
            }

            if ( $key === 'global_footers' ) {
                $sanitized[ $key ] = $this->sanitize_footers_repeater( $input[ $key ] );
                continue;
            }

            if ( $key === 'global_single_posts' ) {
                $sanitized[ $key ] = $this->sanitize_single_posts_repeater( $input[ $key ] );
                continue;
            }

            if ( $key === 'global_archives' ) {
                $sanitized[ $key ] = $this->sanitize_archives_repeater( $input[ $key ] );
                continue;
            }

            $sanitized[ $key ] = $this->sanitize_template_id( $input[ $key ] );
        }

        return $sanitized;
    }

    private function sanitize_headers_repeater( $input ): array {
        return $this->sanitize_repeater_field( $input );
    }

    private function sanitize_footers_repeater( $input ): array {
        return $this->sanitize_repeater_field( $input );
    }

    private function sanitize_single_posts_repeater( $input ): array {
        if ( ! is_array( $input ) ) {
            return [];
        }

        $post_type_choices = self::get_single_post_type_choices();
        $sanitized         = [];

        foreach ( $input as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $template_id = $this->sanitize_template_id(
                self::get_global_row_value(
                    $row,
                    [
                        'template_id',
                        'field_acz_single_post_template_id',
                    ],
                    0
                )
            );
            $post_type   = sanitize_key(
                (string) self::get_global_row_value(
                    $row,
                    [
                        'post_type',
                        'field_acz_single_post_post_type',
                    ],
                    ''
                )
            );

            if ( ! $template_id || ! array_key_exists( $post_type, $post_type_choices ) ) {
                continue;
            }

            $include_posts = [];
            foreach (
                (array) self::get_global_row_value(
                    $row,
                    [
                        'include_posts',
                        'field_acz_single_post_include_posts',
                    ],
                    [ 'all_post' ]
                ) as $include_post
            ) {
                $include_post = sanitize_text_field( (string) $include_post );

                if ( 'all_post' === $include_post ) {
                    $include_posts = [ 'all_post' ];
                    break;
                }

                $post_id = self::normalize_include_post_id( $include_post, $post_type );

                if ( $post_id && $post_type === get_post_type( $post_id ) ) {
                    $include_posts[] = (string) $post_id;
                }
            }

            if ( empty( $include_posts ) ) {
                $include_posts = [ 'all_post' ];
            }

            $sample_post = absint(
                self::get_global_row_value(
                    $row,
                    [
                        'sample_post',
                        'field_acz_single_post_sample_post',
                    ],
                    0
                )
            );

            if ( $sample_post && $post_type !== get_post_type( $sample_post ) ) {
                $sample_post = 0;
            }

            $sanitized[] = [
                'post_type'     => $post_type,
                'template_id'   => $template_id,
                'include_posts' => array_values( array_unique( $include_posts ) ),
                'sample_post'   => $sample_post,
            ];
        }

        return $sanitized;
    }

    private function sanitize_archives_repeater( $input ): array {
        if ( ! is_array( $input ) ) {
            return [];
        }

        $taxonomy_choices = self::get_archive_taxonomy_choices();
        $sanitized        = [];

        foreach ( $input as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $template_id = $this->sanitize_template_id(
                self::get_global_row_value(
                    $row,
                    [
                        'template_id',
                        'field_acz_archive_template_id',
                    ],
                    0
                )
            );
            $taxonomy    = sanitize_key(
                (string) self::get_global_row_value(
                    $row,
                    [
                        'taxonomy',
                        'field_acz_archive_taxonomy',
                    ],
                    ''
                )
            );

            if ( ! $template_id || ! array_key_exists( $taxonomy, $taxonomy_choices ) ) {
                continue;
            }

            $include_terms = [];
            foreach (
                (array) self::get_global_row_value(
                    $row,
                    [
                        'include_terms',
                        'field_acz_archive_include_terms',
                    ],
                    [ 'all_terms' ]
                ) as $include_term
            ) {
                $include_term = sanitize_text_field( (string) $include_term );

                if ( 'all_terms' === $include_term ) {
                    $include_terms = [ 'all_terms' ];
                    break;
                }

                $term_id = self::normalize_include_term_id( $include_term, $taxonomy );
                $term    = $term_id ? get_term( $term_id, $taxonomy ) : null;

                if ( $term && ! is_wp_error( $term ) ) {
                    $include_terms[] = (string) $term_id;
                }
            }

            if ( empty( $include_terms ) ) {
                $include_terms = [ 'all_terms' ];
            }

            $sample_term = absint(
                self::get_global_row_value(
                    $row,
                    [
                        'sample_term',
                        'field_acz_archive_sample_term',
                    ],
                    0
                )
            );
            $sample_term_object = $sample_term ? get_term( $sample_term, $taxonomy ) : null;

            if ( ! $sample_term_object || is_wp_error( $sample_term_object ) ) {
                $sample_term = 0;
            }

            $sanitized[] = [
                'taxonomy'      => $taxonomy,
                'template_id'   => $template_id,
                'include_terms' => array_values( array_unique( $include_terms ) ),
                'sample_term'   => $sample_term,
            ];
        }

        return $sanitized;
    }

    private function sanitize_repeater_field( $input ): array {
        if ( ! is_array( $input ) ) {
            return [];
        }

        $sanitized = [];
        foreach ( $input as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $template_id = $this->sanitize_template_id(
                self::get_global_row_value(
                    $row,
                    [
                        'template_id',
                        'field_acz_header_template_id',
                        'field_acz_footer_template_id',
                    ],
                    0
                )
            );
            $location    = $this->sanitize_location_rule(
                self::get_global_row_value(
                    $row,
                    [
                        'location',
                        'field_acz_header_location',
                        'field_acz_footer_location',
                    ],
                    'entire_site'
                ),
                'entire_site'
            );
            $include_fields = [];

            foreach (
                (array) self::get_global_row_value(
                    $row,
                    [
                        'include_fields',
                        'field_acz_header_include_fields',
                        'field_acz_footer_include_fields',
                    ],
                    []
                ) as $include_field
            ) {
                $include_field = sanitize_text_field( (string) $include_field );

                if ( '' !== $include_field ) {
                    $include_fields[] = $include_field;
                }
            }

            if ( $template_id ) {
                $sanitized[] = [
                    'template_id'     => $template_id,
                    'location'        => $location,
                    'include_fields'  => $include_fields,
                ];
            }
        }

        return $sanitized;
    }

    private function sanitize_location_rule( $location, string $default ): string {
        $location = self::normalize_location_rule( $location );

        if ( array_key_exists( $location, self::get_location_rule_choices() ) ) {
            return $location;
        }

        if ( is_numeric( $location ) && 0 < absint( $location ) ) {
            return (string) absint( $location );
        }

        return $default;
    }

    private function sanitize_template_id( $template_id ): int {
        $template_id = self::normalize_template_id( $template_id );

        if ( ! $template_id ) {
            return 0;
        }

        if ( 'elementor_library' !== get_post_type( $template_id ) ) {
            return 0;
        }

        if ( ! in_array( get_post_status( $template_id ), [ 'publish', 'private', 'inherit' ], true ) ) {
            return 0;
        }

        return $template_id;
    }


    public function enqueue_admin_assets( string $hook ) {
        if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook ) {
            return;
        }

        if ( function_exists( 'acf_enqueue_scripts' ) ) {
            acf_enqueue_scripts();
        }

        $style_dependencies = [];
        $possible_dependencies = [
            'select2',
            'acf-select2',
            'acf-input',
            'acf-pro-input',
        ];

        foreach ( $possible_dependencies as $dependency ) {
            if ( wp_style_is( $dependency, 'registered' ) || wp_style_is( $dependency, 'enqueued' ) || wp_style_is( $dependency, 'done' ) ) {
                $style_dependencies[] = $dependency;
            }
        }

        wp_enqueue_style(
            'acz-theme-options',
            ACZ_RC_URL . 'assets/css/theme-options.css',
            $style_dependencies,
            defined( 'ACZ_ELEMENTS_VERSION' ) ? ACZ_ELEMENTS_VERSION : '1.2.1'
        );

        wp_enqueue_script(
            'acz-theme-options',
            ACZ_RC_URL . 'assets/js/theme-options.js',
            [],
            defined( 'ACZ_ELEMENTS_VERSION' ) ? ACZ_ELEMENTS_VERSION : '1.2.1',
            true
        );

        wp_localize_script(
            'acz-theme-options',
            'AczThemeOptions',
            [
                'includeChoices'      => self::get_include_field_choices_by_location(),
                'singlePostChoices'   => self::get_single_post_include_post_choices_by_type(),
                'samplePostChoices'   => self::get_single_post_sample_post_choices_by_type(),
                'archiveTermChoices'  => self::get_archive_include_term_choices_by_taxonomy(),
                'sampleTermChoices'   => self::get_archive_sample_term_choices_by_taxonomy(),
            ]
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return;
        }

        if ( ! function_exists( 'acf_form' ) ) {
            echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Advanced Custom Fields is required to render ACZ Theme Options.', 'acz-elements' ) . '</p></div>';
            echo '</div>';
            return;
        }

        echo '<div class="wrap acz-options-wrap is-loading">';
        echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
        echo '<div class="acz-options-loader" aria-hidden="true"></div>';

        acf_form(
            [
                'id'           => 'acz-theme-options-acf-form',
                'post_id'      => 'option',
                'field_groups' => [ 'group_acz_theme_options'],
                'form'         => true,
                'return'       => add_query_arg( 'updated', 'true', menu_page_url( self::PAGE_SLUG, false ) ),
                'submit_value' => esc_html__( 'Save Changes', 'acz-elements' ),
                'updated_message' => esc_html__( 'Theme options updated.', 'acz-elements' ),
            ]
        );

        echo '</div>';
    }


    private function get_elementor_templates(): array {
        $posts = get_posts(
            [
                'post_type'      => 'elementor_library',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'fields'         => 'ids',
            ]
        );

        $templates = [];

        foreach ( $posts as $post_id ) {
            $templates[ (int) $post_id ] = sprintf(
                '%1$s (#%2$d)',
                get_the_title( $post_id ),
                (int) $post_id
            );
        }

        return $templates;
    }
}
