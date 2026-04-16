<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KC_Karice_Post_Gallery_Widget extends \Elementor\Widget_Base {
    public function get_name(): string {
        return 'karice_post_gallery';
    }

    public function get_title(): string {
        return esc_html__( 'Karice Project Gallery', 'karice-elements' );
    }

    public function get_icon(): string {
        return 'eicon-posts-grid';
    }

    public function get_categories(): array {
        return [ 'karice' ];
    }

    public function get_keywords(): array {
        return [ 'posts', 'gallery', 'grid', 'blog', 'karice' ];
    }

    public function get_style_depends(): array {
        return [ 'karice-post-gallery' ];
    }

    public function get_script_depends(): array {
        return [ 'krc-post-filter-ajax' ];
    }

    protected function register_controls(): void {
        $this->register_query_controls();
        $this->register_layout_controls();
        $this->register_content_controls();
        $this->register_pagination_controls();
        $this->register_style_controls();
    }

    private function get_post_type_options(): array {
        $options    = [];
        $post_types = get_post_types( [
            'public' => true
        ], 'objects' );

        foreach ( $post_types as $post_type ) {
            if ( empty( $post_type->name ) ) {
                continue;
            }

            // Include plugin CPTs that are visible in admin UI even if not fully public.
            if ( empty( $post_type->public ) && empty( $post_type->show_ui ) ) {
                continue;
            }

            // Skip technical/internal post types.
            if ( in_array( $post_type->name, [ 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_template', 'wp_template_part', 'wp_navigation' ], true ) ) {
                continue;
            }

            $label = ! empty( $post_type->labels->singular_name )
                ? $post_type->labels->singular_name
                : ( ! empty( $post_type->label ) ? $post_type->label : $post_type->name );

            $options[ $post_type->name ] = $label;
        }

        asort( $options, SORT_NATURAL | SORT_FLAG_CASE );
        return $options;
    }

    private function get_author_options(): array {
        $options = [];
        $users   = get_users(
            [
                'fields' => [ 'ID', 'display_name' ],
                'orderby' => 'display_name',
                'order' => 'ASC',
            ]
        );

        foreach ( $users as $user ) {
            $options[ (string) $user->ID ] = $user->display_name;
        }

        return $options;
    }

    private function get_term_options(): array {
        $options    = [];
        $taxonomies = get_taxonomies(
            [
                'public' => true,
            ],
            'objects'
        );

        foreach ( $taxonomies as $taxonomy ) {
            $terms = get_terms(
                [
                    'taxonomy'   => $taxonomy->name,
                    'hide_empty' => false,
                ]
            );

            if ( is_wp_error( $terms ) ) {
                continue;
            }

            foreach ( $terms as $term ) {
                $options[ (string) $term->term_id ] = sprintf(
                    '%1$s: %2$s',
                    $taxonomy->label,
                    $term->name
                );
            }
        }

        return $options;
    }

    private function get_taxonomy_options(): array {
        $options    = [];
        $taxonomies = get_taxonomies(
            [
                'public'  => true,
                'show_ui' => true,
            ],
            'objects'
        );

        foreach ( $taxonomies as $taxonomy ) {
            if ( empty( $taxonomy->name ) ) {
                continue;
            }

            $options[ $taxonomy->name ] = ! empty( $taxonomy->label ) ? $taxonomy->label : $taxonomy->name;
        }

        asort( $options, SORT_NATURAL | SORT_FLAG_CASE );
        return $options;
    }

    private function register_query_controls(): void {
        $this->start_controls_section(
            'section_query',
            [
                'label' => esc_html__( 'Gallery Posts Query', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $post_type_options = $this->get_post_type_options();
        $author_options    = $this->get_author_options();
        $term_options      = $this->get_term_options();
        $taxonomy_options  = $this->get_taxonomy_options();

        $this->add_control(
            'posts_source',
            [
                'label'   => esc_html__( 'Posts Source', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'custom_posts',
                'options' => [
                    'custom_posts'  => esc_html__( 'Custom Posts', 'karice-elements' ),
                    'taxonomies'    => esc_html__( 'Taxonomies', 'karice-elements' ),
                    'current_query' => esc_html__( 'Current Query', 'karice-elements' ),
                ],
            ]
        );

        $this->add_control(
            'source_taxonomy',
            [
                'label'       => esc_html__( 'Taxonomy', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $taxonomy_options,
                'label_block' => true,
                'condition'   => [
                    'posts_source' => 'taxonomies',
                ],
            ]
        );

        $this->add_control(
            'source_taxonomy_hide_empty',
            [
                'label'        => esc_html__( 'Hide Empty', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'posts_source' => 'taxonomies',
                ],
            ]
        );

        $this->add_control(
            'source_taxonomy_include',
            [
                'label'       => esc_html__( 'Include Terms only', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $term_options,
                'condition'   => [
                    'posts_source' => 'taxonomies',
                ],
            ]
        );

        $this->add_control(
            'source_taxonomy_exclude',
            [
                'label'       => esc_html__( 'Exclude Terms', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $term_options,
                'condition'   => [
                    'posts_source' => 'taxonomies',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_field_map_title',
            [
                'label'       => esc_html__( 'Title Field Map (Meta Key)', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => esc_html__( 'Enter meta key or leave empty for default', 'karice-elements' ),
                'condition'   => [
                    'posts_source' => 'taxonomies',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_field_map_description',
            [
                'label'       => esc_html__( 'Description Field Map (Meta Key)', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => esc_html__( 'Enter meta key or leave empty for default', 'karice-elements' ),
                'condition'   => [
                    'posts_source' => 'taxonomies',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_field_map_image',
            [
                'label'       => esc_html__( 'Image Field Map (Meta Key)', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'thumbnail_id',
                'placeholder' => esc_html__( 'Enter meta key for image ID', 'karice-elements' ),
                'condition'   => [
                    'posts_source' => 'taxonomies',
                ],
            ]
        );

        $this->add_control(
            'post_types',
            [
                'label'       => esc_html__( 'Post Types', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'default'     => [ 'post' ],
                'options' => $post_type_options,
                'condition'   => [
                    'posts_source' => 'custom_posts',
                ],
            ]
        );

        $this->add_control(
            'include_by',
            [
                'label'       => esc_html__( 'Include By', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => [
                    'post_ids' => esc_html__( 'Posts', 'karice-elements' ),
                    'terms'    => esc_html__( 'Terms', 'karice-elements' ),
                    'authors'  => esc_html__( 'Authors', 'karice-elements' ),
                ],
                'condition' => [
                    'posts_source' => 'custom_posts',
                ],
            ]
        );

        $this->add_control(
            'include_post_ids',
            [
                'label'       => esc_html__( 'Include By Posts', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => [], // AJAX needed for large sites, but matching existing pattern for now
                'condition'   => [
                    'posts_source' => 'custom_posts',
                    'include_by'   => 'post_ids',
                ],
            ]
        );

        $this->add_control(
            'include_terms',
            [
                'label'       => esc_html__( 'Include By Terms', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $term_options,
                'condition'   => [
                    'posts_source' => 'custom_posts',
                    'include_by'   => 'terms',
                ],
            ]
        );

        $this->add_control(
            'include_terms_relation',
            [
                'label'   => esc_html__( 'Include Terms Relation', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'AND',
                'options' => [
                    'AND' => esc_html__( 'And', 'karice-elements' ),
                    'OR'  => esc_html__( 'Or', 'karice-elements' ),
                ],
                'condition' => [
                    'posts_source' => 'custom_posts',
                    'include_by'   => 'terms',
                ],
            ]
        );

        $this->add_control(
            'include_terms_children',
            [
                'label'        => esc_html__( 'Include Terms Children', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'condition'    => [
                    'posts_source' => 'custom_posts',
                    'include_by'   => 'terms',
                ],
            ]
        );

        $this->add_control(
            'include_authors',
            [
                'label'       => esc_html__( 'Include By Authors', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $author_options,
                'condition'   => [
                    'posts_source' => 'custom_posts',
                    'include_by'   => 'authors',
                ],
            ]
        );

        $this->add_control(
            'exclude_by',
            [
                'label'       => esc_html__( 'Exclude By', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => [
                    'current_post' => esc_html__( 'Current Post', 'karice-elements' ),
                    'post_ids'     => esc_html__( 'Posts', 'karice-elements' ),
                    'terms'        => esc_html__( 'Terms', 'karice-elements' ),
                    'authors'      => esc_html__( 'Authors', 'karice-elements' ),
                ],
                'condition' => [
                    'posts_source' => 'custom_posts',
                ],
            ]
        );

        $this->add_control(
            'exclude_post_ids',
            [
                'label'       => esc_html__( 'Exclude By Posts', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => [],
                'condition'   => [
                    'posts_source' => 'custom_posts',
                    'exclude_by'   => 'post_ids',
                ],
            ]
        );

        $this->add_control(
            'exclude_terms',
            [
                'label'       => esc_html__( 'Exclude By Terms', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $term_options,
                'condition'   => [
                    'posts_source' => 'custom_posts',
                    'exclude_by'   => 'terms',
                ],
            ]
        );

        $this->add_control(
            'exclude_authors',
            [
                'label'       => esc_html__( 'Exclude By Authors', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $author_options,
                'condition'   => [
                    'posts_source' => 'custom_posts',
                    'exclude_by'   => 'authors',
                ],
            ]
        );

        $this->add_control(
            'post_status',
            [
                'label'       => esc_html__( 'Post Status', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'default'     => [ 'publish' ],
                'options'     => [
                    'publish' => esc_html__( 'Publish', 'karice-elements' ),
                    'future'  => esc_html__( 'Future', 'karice-elements' ),
                    'draft'   => esc_html__( 'Draft', 'karice-elements' ),
                    'pending' => esc_html__( 'Pending', 'karice-elements' ),
                    'private' => esc_html__( 'Private', 'karice-elements' ),
                ],
            ]
        );

        $this->add_control(
            'max_posts',
            [
                'label'   => esc_html__( 'Max Posts', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 10,
                'min'     => 1,
                'max'     => 200,
            ]
        );

        $this->add_control(
            'offset',
            [
                'label'   => esc_html__( 'Offset', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min'     => 0,
            ]
        );

        $this->add_control(
            'ignore_sticky_posts',
            [
                'label'        => esc_html__( 'Ignore Sticky Posts', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => esc_html__( 'Order By', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default'       => esc_html__( 'Default', 'karice-elements' ),
                    'date'          => esc_html__( 'Date', 'karice-elements' ),
                    'title'         => esc_html__( 'Title', 'karice-elements' ),
                    'modified'      => esc_html__( 'Modified', 'karice-elements' ),
                    'menu_order'    => esc_html__( 'Menu Order', 'karice-elements' ),
                    'rand'          => esc_html__( 'Random', 'karice-elements' ),
                    'comment_count' => esc_html__( 'Comment Count', 'karice-elements' ),
                    'ID'            => esc_html__( 'ID', 'karice-elements' ),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => esc_html__( 'Order By Direction', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => esc_html__( 'Default', 'karice-elements' ),
                    'DESC'    => esc_html__( 'Descending', 'karice-elements' ),
                    'ASC'     => esc_html__( 'Ascending', 'karice-elements' ),
                ],
            ]
        );

        $this->add_control(
            'external_filter_heading',
            [
                'label'     => esc_html__( 'External Filters', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'enable_external_filter',
            [
                'label'        => esc_html__( 'Enable External Filter', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

        $this->add_control(
            'external_filter_param',
            [
                'label'       => esc_html__( 'URL Parameter', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'project_filter',
                'label_block' => true,
                'description' => esc_html__( 'Your dropdown should set this query string key. Example: ?project_filter=interior', 'karice-elements' ),
                'condition'   => [
                    'enable_external_filter' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'external_filter_taxonomy',
            [
                'label'       => esc_html__( 'Filter Taxonomy', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $taxonomy_options,
                'label_block' => true,
                'condition'   => [
                    'enable_external_filter' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'external_filter_operator',
            [
                'label'     => esc_html__( 'Match Mode', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'IN',
                'options'   => [
                    'IN'     => esc_html__( 'Any Selected Term', 'karice-elements' ),
                    'AND'    => esc_html__( 'Match All Terms', 'karice-elements' ),
                    'NOT IN' => esc_html__( 'Exclude Selected Terms', 'karice-elements' ),
                ],
                'condition' => [
                    'enable_external_filter' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'external_filter_multi_heading',
            [
                'label'     => esc_html__( 'Multi Filter Rules', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'condition' => [
                    'enable_external_filter' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'external_filter_tax_relation',
            [
                'label'     => esc_html__( 'Taxonomy Rules Relation', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'AND',
                'options'   => [
                    'AND' => esc_html__( 'AND', 'karice-elements' ),
                    'OR'  => esc_html__( 'OR', 'karice-elements' ),
                ],
                'condition' => [
                    'enable_external_filter' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'external_filter_meta_relation',
            [
                'label'     => esc_html__( 'Meta Rules Relation', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'AND',
                'options'   => [
                    'AND' => esc_html__( 'AND', 'karice-elements' ),
                    'OR'  => esc_html__( 'OR', 'karice-elements' ),
                ],
                'condition' => [
                    'enable_external_filter' => 'yes',
                ],
            ]
        );

        $external_filter_repeater = new \Elementor\Repeater();

        $external_filter_repeater->add_control(
            'param_key',
            [
                'label'       => esc_html__( 'URL Parameter', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => 'project_category',
            ]
        );

        $external_filter_repeater->add_control(
            'rule_type',
            [
                'label'   => esc_html__( 'Rule Type', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'taxonomy',
                'options' => [
                    'taxonomy' => esc_html__( 'Taxonomy', 'karice-elements' ),
                    'meta'     => esc_html__( 'Meta Field', 'karice-elements' ),
                ],
            ]
        );

        $external_filter_repeater->add_control(
            'taxonomy',
            [
                'label'     => esc_html__( 'Taxonomy', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'options'   => $taxonomy_options,
                'condition' => [
                    'rule_type' => 'taxonomy',
                ],
            ]
        );

        $external_filter_repeater->add_control(
            'taxonomy_operator',
            [
                'label'     => esc_html__( 'Taxonomy Operator', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'IN',
                'options'   => [
                    'IN'     => esc_html__( 'IN', 'karice-elements' ),
                    'AND'    => esc_html__( 'AND', 'karice-elements' ),
                    'NOT IN' => esc_html__( 'NOT IN', 'karice-elements' ),
                ],
                'condition' => [
                    'rule_type' => 'taxonomy',
                ],
            ]
        );

        $external_filter_repeater->add_control(
            'meta_key',
            [
                'label'       => esc_html__( 'Meta Key', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => 'location',
                'condition'   => [
                    'rule_type' => 'meta',
                ],
            ]
        );

        $external_filter_repeater->add_control(
            'meta_compare',
            [
                'label'     => esc_html__( 'Meta Compare', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => '=',
                'options'   => [
                    '='           => '=',
                    '!='          => '!=',
                    '>'           => '>',
                    '>='          => '>=',
                    '<'           => '<',
                    '<='          => '<=',
                    'LIKE'        => 'LIKE',
                    'NOT LIKE'    => 'NOT LIKE',
                    'IN'          => 'IN',
                    'NOT IN'      => 'NOT IN',
                    'BETWEEN'     => 'BETWEEN',
                    'NOT BETWEEN' => 'NOT BETWEEN',
                    'EXISTS'      => 'EXISTS',
                    'NOT EXISTS'  => 'NOT EXISTS',
                ],
                'condition' => [
                    'rule_type' => 'meta',
                ],
            ]
        );

        $external_filter_repeater->add_control(
            'meta_type',
            [
                'label'     => esc_html__( 'Meta Type', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'CHAR',
                'options'   => [
                    'CHAR'     => 'CHAR',
                    'NUMERIC'  => 'NUMERIC',
                    'DECIMAL'  => 'DECIMAL',
                    'SIGNED'   => 'SIGNED',
                    'UNSIGNED' => 'UNSIGNED',
                    'DATE'     => 'DATE',
                    'DATETIME' => 'DATETIME',
                    'TIME'     => 'TIME',
                    'BINARY'   => 'BINARY',
                ],
                'condition' => [
                    'rule_type' => 'meta',
                ],
            ]
        );

        $this->add_control(
            'external_filter_rules',
            [
                'label'       => esc_html__( 'External Filter Rules', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $external_filter_repeater->get_controls(),
                'title_field' => '{{{ rule_type }}}: {{{ param_key }}}',
                'condition'   => [
                    'enable_external_filter' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_pagination_controls(): void {
        $this->start_controls_section(
            'section_pagination',
            [
                'label' => esc_html__( 'Pagination', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_pagination',
            [
                'label'        => esc_html__( 'Enable Pagination', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

        $this->add_control(
            'pagination_prev_text',
            [
                'label'     => esc_html__( 'Prev Label', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::TEXT,
                'default'   => esc_html__( 'Previous', 'karice-elements' ),
                'condition' => [
                    'enable_pagination' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'pagination_prev_icon',
            [
                'label'            => esc_html__( 'Prev Icon', 'karice-elements' ),
                'type'             => \Elementor\Controls_Manager::ICONS,
                'fa4compatibility' => 'pagination_prev_icon_legacy',
                'condition'        => [
                    'enable_pagination' => 'yes',
                ],
                'default'          => [
                    'value'   => 'eicon-chevron-left',
                    'library' => 'eicons',
                ],
            ]
        );

        $this->add_control(
            'pagination_prev_icon_position',
            [
                'label'     => esc_html__( 'Prev Icon Position', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'before',
                'options'   => [
                    'before' => esc_html__( 'Before Text', 'karice-elements' ),
                    'after'  => esc_html__( 'After Text', 'karice-elements' ),
                ],
                'condition' => [
                    'enable_pagination' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'pagination_next_text',
            [
                'label'     => esc_html__( 'Next Label', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::TEXT,
                'default'   => esc_html__( 'Next', 'karice-elements' ),
                'condition' => [
                    'enable_pagination' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'pagination_next_icon',
            [
                'label'            => esc_html__( 'Next Icon', 'karice-elements' ),
                'type'             => \Elementor\Controls_Manager::ICONS,
                'fa4compatibility' => 'pagination_next_icon_legacy',
                'condition'        => [
                    'enable_pagination' => 'yes',
                ],
                'default'          => [
                    'value'   => 'eicon-chevron-right',
                    'library' => 'eicons',
                ],
            ]
        );

        $this->add_control(
            'pagination_next_icon_position',
            [
                'label'     => esc_html__( 'Next Icon Position', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'after',
                'options'   => [
                    'before' => esc_html__( 'Before Text', 'karice-elements' ),
                    'after'  => esc_html__( 'After Text', 'karice-elements' ),
                ],
                'condition' => [
                    'enable_pagination' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_layout_controls(): void {
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__( 'Layout', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'layout',
            [
                'label'   => esc_html__( 'Layout', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid'    => esc_html__( 'Grid', 'karice-elements' ),
                    'overlay' => esc_html__( 'Overlay', 'karice-elements' ),
                ],
                'prefix_class' => 'krc-layout-',
            ]
        );

        $this->add_control(
            'content_position',
            [
                'label'   => esc_html__( 'Content Position', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'bottom',
                'options' => [
                    'top'    => esc_html__( 'Top', 'karice-elements' ),
                    'middle' => esc_html__( 'Middle', 'karice-elements' ),
                    'bottom' => esc_html__( 'Bottom', 'karice-elements' ),
                ],
                'condition' => [
                    'layout' => 'overlay',
                ],
                'prefix_class' => 'krc-content-pos-',
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label'   => esc_html__( 'Columns', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-gallery' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
                ],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label'      => esc_html__( 'Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'default'    => [
                    'size' => 24,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-gallery' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'enable_tile_rearrange',
            [
                'label'        => esc_html__( 'Rearrange Tiles On Load', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

        $this->add_control(
            'tile_rearrange_duration',
            [
                'label'      => esc_html__( 'Rearrange Duration', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'ms' ],
                'default'    => [
                    'size' => 700,
                    'unit' => 'ms',
                ],
                'range'      => [
                    'ms' => [
                        'min' => 100,
                        'max' => 2500,
                    ],
                ],
                'condition'  => [
                    'enable_tile_rearrange' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_content_controls(): void {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Content', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label'        => esc_html__( 'Show Image', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label'        => esc_html__( 'Show Excerpt', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label'     => esc_html__( 'Excerpt Length (words)', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 20,
                'min'       => 5,
                'max'       => 120,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label'        => esc_html__( 'Show Date', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_custom_meta_line',
            [
                'label'        => esc_html__( 'Show Custom Meta Line', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'custom_meta_format',
            [
                'label'       => esc_html__( 'Custom Meta Format', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '%location% - %partner%',
                'label_block' => true,
                'description' => esc_html__( 'Use %meta_key% placeholders. Example: %location% - %partner%', 'karice-elements' ),
                'condition'   => [
                    'show_custom_meta_line' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_author',
            [
                'label'        => esc_html__( 'Show Author', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

        $this->add_control(
            'show_button',
            [
                'label'        => esc_html__( 'Show Button', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'separator'    => 'before',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label'       => esc_html__( 'Button Text', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'Read More', 'karice-elements' ),
                'placeholder' => esc_html__( 'Read More', 'karice-elements' ),
                'condition'   => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_icon',
            [
                'label'     => esc_html__( 'Button Icon', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::ICONS,
                'default'   => [],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_icon_position',
            [
                'label'     => esc_html__( 'Icon Position', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'after',
                'options'   => [
                    'before' => esc_html__( 'Before', 'karice-elements' ),
                    'after'  => esc_html__( 'After', 'karice-elements' ),
                ],
                'condition' => [
                    'show_button' => 'yes',
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_card',
            [
                'label' => esc_html__( 'Card', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_background',
            [
                'label'     => esc_html__( 'Background', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'card_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label'      => esc_html__( 'Body Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'card_content_gap',
            [
                'label'      => esc_html__( 'Content Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-body' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'card_entry_effect',
            [
                'label'        => esc_html__( 'Entry Effect', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'default'      => 'none',
                'options'      => [
                    'none'       => esc_html__( 'None', 'karice-elements' ),
                    'fade'       => esc_html__( 'Fade In', 'karice-elements' ),
                    'slide-up'   => esc_html__( 'Slide In Up', 'karice-elements' ),
                    'slide-left' => esc_html__( 'Slide In Left', 'karice-elements' ),
                    'slide-right'=> esc_html__( 'Slide In Right', 'karice-elements' ),
                    'slide-down' => esc_html__( 'Slide In Down', 'karice-elements' ),
                    'zoom-in'    => esc_html__( 'Zoom In', 'karice-elements' ),
                    'flip-up'    => esc_html__( 'Flip In Up', 'karice-elements' ),
                    'blur-in'    => esc_html__( 'Blur In', 'karice-elements' ),
                ],
                'prefix_class' => 'krc-entry-',
            ]
        );

        $this->add_control(
            'card_entry_duration',
            [
                'label'      => esc_html__( 'Entry Duration', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'ms' ],
                'default'    => [
                    'size' => 500,
                    'unit' => 'ms',
                ],
                'range'      => [
                    'ms' => [
                        'min' => 100,
                        'max' => 3000,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}}' => '--krc-entry-duration: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'card_entry_effect!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'card_entry_stagger',
            [
                'label'      => esc_html__( 'Entry Stagger', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'ms' ],
                'default'    => [
                    'size' => 90,
                    'unit' => 'ms',
                ],
                'range'      => [
                    'ms' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}}' => '--krc-entry-stagger: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'card_entry_effect!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'card_entry_distance',
            [
                'label'      => esc_html__( 'Slide Distance', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default'    => [
                    'size' => 22,
                    'unit' => 'px',
                ],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 120,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}}' => '--krc-entry-distance: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'card_entry_effect' => [ 'slide-up', 'slide-left' ],
                ],
            ]
        );

        $this->add_control(
            'update_exit_effect',
            [
                'label'   => esc_html__( 'Update Exit Effect', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'     => esc_html__( 'None', 'karice-elements' ),
                    'fade-out' => esc_html__( 'Fade Out', 'karice-elements' ),
                ],
            ]
        );

        $this->add_control(
            'update_exit_duration',
            [
                'label'      => esc_html__( 'Update Exit Duration', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'ms' ],
                'default'    => [
                    'size' => 220,
                    'unit' => 'ms',
                ],
                'range'      => [
                    'ms' => [
                        'min' => 80,
                        'max' => 2000,
                    ],
                ],
                'condition'  => [
                    'update_exit_effect' => 'fade-out',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_image',
            [
                'label' => esc_html__( 'Image', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_aspect_ratio',
            [
                'label'      => esc_html__( 'Aspect Ratio', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::TEXT,
                'default'    => '16 / 10',
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-media img' => 'aspect-ratio: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label'      => esc_html__( 'Height', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-media img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_object_fit',
            [
                'label'   => esc_html__( 'Object Fit', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'cover',
                'options' => [
                    'cover'   => esc_html__( 'Cover', 'karice-elements' ),
                    'contain' => esc_html__( 'Contain', 'karice-elements' ),
                    'fill'    => esc_html__( 'Fill', 'karice-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-media img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_title',
            [
                'label' => esc_html__( 'Title', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-title, {{WRAPPER}} .krc-post-card-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .krc-post-card-title',
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-title' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'title_border',
                'selector' => '{{WRAPPER}} .krc-post-card-title',
            ]
        );

        $this->add_responsive_control(
            'title_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_alignment',
            [
                'label'     => esc_html__( 'Alignment', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'  => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_width',
            [
                'label'      => esc_html__( 'Width', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                    '%'  => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-title' => 'width: {{SIZE}}{{UNIT}}; max-width: 100%;',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_custom_meta',
            [
                'label' => esc_html__( 'Custom Meta Line', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'custom_meta_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-custom-meta' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'custom_meta_typography',
                'selector' => '{{WRAPPER}} .krc-post-card-custom-meta',
            ]
        );

        $this->add_responsive_control(
            'custom_meta_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-custom-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_meta',
            [
                'label' => esc_html__( 'Date & Author', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'meta_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-meta' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'meta_typography',
                'selector' => '{{WRAPPER}} .krc-post-card-meta',
            ]
        );

        $this->add_responsive_control(
            'meta_gap',
            [
                'label'      => esc_html__( 'Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-meta' => 'column-gap: {{SIZE}}{{UNIT}}; row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_excerpt',
            [
                'label' => esc_html__( 'Excerpt', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'excerpt_typography',
                'selector' => '{{WRAPPER}} .krc-post-card-excerpt',
            ]
        );

        $this->add_responsive_control(
            'excerpt_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'excerpt_alignment',
            [
                'label'     => esc_html__( 'Alignment', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'  => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__( 'Justified', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-excerpt' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_button',
            [
                'label'     => esc_html__( 'Button', 'karice-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .krc-post-card-button',
            ]
        );

        $this->start_controls_tabs( 'tabs_button_style' );

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'karice-elements' ),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-button' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .krc-post-card-button svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .krc-post-card-button',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__( 'Hover', 'karice-elements' ),
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-button:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .krc-post-card-button:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator'  => 'before',
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_icon_gap',
            [
                'label'      => esc_html__( 'Icon Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-card-button' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_alignment',
            [
                'label'     => esc_html__( 'Alignment', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'  => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-card-button-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_pagination',
            [
                'label' => esc_html__( 'Pagination', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'pagination_alignment',
            [
                'label'   => esc_html__( 'Alignment', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'   => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'pagination_typography',
                'selector' => '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers a, {{WRAPPER}} .krc-post-gallery-pagination .page-numbers .current',
            ]
        );

        $this->add_control(
            'pagination_gap',
            [
                'label'      => esc_html__( 'Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_item_radius',
            [
                'label'      => esc_html__( 'Item Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers a, {{WRAPPER}} .krc-post-gallery-pagination .page-numbers .current' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_item_padding',
            [
                'label'      => esc_html__( 'Item Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers a, {{WRAPPER}} .krc-post-gallery-pagination .page-numbers .current' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( 'pagination_style_tabs' );

        $this->start_controls_tab(
            'pagination_tab_normal',
            [
                'label' => esc_html__( 'Normal', 'karice-elements' ),
            ]
        );

        $this->add_control(
            'pagination_color',
            [
                'label'     => esc_html__( 'Text Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers a' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers a' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'pagination_tab_active',
            [
                'label' => esc_html__( 'Active', 'karice-elements' ),
            ]
        );

        $this->add_control(
            'pagination_active_color',
            [
                'label'     => esc_html__( 'Text Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers .current' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_active_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers .current' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_active_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-gallery-pagination .page-numbers .current' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'pagination_margin_top',
            [
                'label'      => esc_html__( 'Top Spacing', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-gallery-pagination' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function trim_words( string $text, int $word_count ): string {
        $text = wp_strip_all_tags( $text );
        return wp_trim_words( $text, max( 1, $word_count ) );
    }

    private function build_custom_meta_line( int $post_id, string $format ): string {
        $format = trim( $format );

        if ( '' === $format ) {
            return '';
        }

        $line = preg_replace_callback(
            '/%([a-zA-Z0-9_\-]+)%/',
            static function ( array $matches ) use ( $post_id ): string {
                $meta_key = sanitize_key( $matches[1] ?? '' );

                if ( '' === $meta_key ) {
                    return '';
                }

                $value = get_post_meta( $post_id, $meta_key, true );

                if ( is_array( $value ) ) {
                    $value = implode( ', ', array_map( 'wp_strip_all_tags', $value ) );
                }

                return is_scalar( $value ) ? (string) $value : '';
            },
            $format
        );

        if ( ! is_string( $line ) ) {
            return '';
        }

        $line = preg_replace( '/\s+/', ' ', $line );
        if ( ! is_string( $line ) ) {
            return '';
        }

        // Normalize separators/spaces so empty placeholders do not leave awkward output.
        $line = preg_replace( '/\s*,\s*/', ', ', $line );
        $line = preg_replace( '/\s*-\s*/', ' - ', $line );
        $line = preg_replace( '/(?:,\s*){2,}/', ', ', $line );
        $line = preg_replace( '/(?:\s-\s){2,}/', ' - ', $line );
        $line = preg_replace( '/,\s*-\s*/', ' - ', $line );
        $line = preg_replace( '/-\s*,\s*/', ' - ', $line );
        $line = preg_replace( '/\s+/', ' ', $line );

        if ( ! is_string( $line ) ) {
            return '';
        }

        $line = trim( $line );
        return trim( $line, " \t\n\r\0\x0B,-|" );
    }

    private function parse_ids( string $value ): array {
        if ( '' === trim( $value ) ) {
            return [];
        }

        $parts = array_filter( array_map( 'trim', explode( ',', $value ) ) );
        $ids   = array_map( 'absint', $parts );
        return array_values( array_filter( $ids ) );
    }

    private function normalize_id_array( $values ): array {
        if ( ! is_array( $values ) ) {
            return [];
        }

        $ids = array_map( 'absint', $values );
        return array_values( array_filter( $ids ) );
    }

    private function group_terms_by_taxonomy( array $term_ids ): array {
        $by_taxonomy = [];

        foreach ( $term_ids as $term_id ) {
            $term = get_term( $term_id );

            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }

            if ( ! isset( $by_taxonomy[ $term->taxonomy ] ) ) {
                $by_taxonomy[ $term->taxonomy ] = [];
            }

            $by_taxonomy[ $term->taxonomy ][] = (int) $term->term_id;
        }

        return $by_taxonomy;
    }

    private function parse_query_filter_values( $value ): array {
        $values = [];

        if ( is_array( $value ) ) {
            $values = $value;
        } elseif ( is_scalar( $value ) ) {
            $values = explode( ',', (string) $value );
        }

        $values = array_map(
            static function ( $item ): string {
                return trim( (string) $item );
            },
            $values
        );

        $values = array_values( array_filter( $values, static function ( string $item ): bool {
            return '' !== $item;
        } ) );

        return array_slice( $values, 0, 50 );
    }

    private function ensure_query_relation( array &$query_args, string $query_key, string $relation = 'AND' ): void {
        $relation = ( 'OR' === strtoupper( $relation ) ) ? 'OR' : 'AND';

        if ( ! isset( $query_args[ $query_key ] ) || ! is_array( $query_args[ $query_key ] ) ) {
            $query_args[ $query_key ] = [
                'relation' => $relation,
            ];
            return;
        }

        if ( ! isset( $query_args[ $query_key ]['relation'] ) ) {
            $query_args[ $query_key ]['relation'] = $relation;
        }
    }

    private function append_taxonomy_filter_clause( array &$query_args, string $taxonomy, array $values, string $operator = 'IN', string $relation = 'AND' ): void {
        if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) || empty( $values ) ) {
            return;
        }

        $operator = strtoupper( $operator );
        if ( ! in_array( $operator, [ 'IN', 'AND', 'NOT IN' ], true ) ) {
            $operator = 'IN';
        }

        $all_ids = count(
            array_filter(
                $values,
                static function ( string $item ): bool {
                    return ctype_digit( $item );
                }
            )
        ) === count( $values );

        if ( $all_ids ) {
            $field = 'term_id';
            $terms = array_values( array_filter( array_map( 'absint', $values ) ) );
        } else {
            $field = 'slug';
            $terms = array_values( array_filter( array_map( 'sanitize_title', $values ) ) );
        }

        if ( empty( $terms ) ) {
            return;
        }

        $this->ensure_query_relation( $query_args, 'tax_query', $relation );
        $query_args['tax_query'][] = [
            'taxonomy' => $taxonomy,
            'field'    => $field,
            'terms'    => $terms,
            'operator' => $operator,
        ];
    }

    private function append_meta_filter_clause( array &$query_args, string $meta_key, array $values, string $compare = '=', string $type = 'CHAR', string $relation = 'AND' ): void {
        $meta_key = sanitize_key( $meta_key );
        if ( '' === $meta_key ) {
            return;
        }

        $compare = strtoupper( trim( $compare ) );
        $type    = strtoupper( trim( $type ) );

        $allowed_compare = [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS' ];
        $allowed_type    = [ 'CHAR', 'NUMERIC', 'DECIMAL', 'SIGNED', 'UNSIGNED', 'DATE', 'DATETIME', 'TIME', 'BINARY' ];

        if ( ! in_array( $compare, $allowed_compare, true ) ) {
            $compare = '=';
        }
        if ( ! in_array( $type, $allowed_type, true ) ) {
            $type = 'CHAR';
        }

        $clause = [
            'key'     => $meta_key,
            'compare' => $compare,
            'type'    => $type,
        ];

        if ( ! in_array( $compare, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {
            $sanitized_values = array_values(
                array_filter(
                    array_map(
                        static function ( string $item ): string {
                            return sanitize_text_field( $item );
                        },
                        $values
                    ),
                    static function ( string $item ): bool {
                        return '' !== $item;
                    }
                )
            );

            if ( empty( $sanitized_values ) ) {
                return;
            }

            if ( in_array( $compare, [ 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ], true ) ) {
                $clause['value'] = ( in_array( $compare, [ 'BETWEEN', 'NOT BETWEEN' ], true ) )
                    ? array_slice( $sanitized_values, 0, 2 )
                    : $sanitized_values;
            } else {
                $clause['value'] = (string) $sanitized_values[0];
            }
        }

        $this->ensure_query_relation( $query_args, 'meta_query', $relation );
        $query_args['meta_query'][] = $clause;
    }

    private function apply_external_filter( array &$query_args, array $settings ): void {
        if ( 'yes' !== ( $settings['enable_external_filter'] ?? '' ) ) {
            return;
        }

        // Backward-compatible single taxonomy filter.
        $param_name = sanitize_key( (string) ( $settings['external_filter_param'] ?? 'project_filter' ) );
        $taxonomy   = sanitize_key( (string) ( $settings['external_filter_taxonomy'] ?? '' ) );
        $operator   = strtoupper( (string) ( $settings['external_filter_operator'] ?? 'IN' ) );

        if ( '' !== $param_name && '' !== $taxonomy && taxonomy_exists( $taxonomy ) && isset( $_GET[ $param_name ] ) ) {
            $legacy_values = $this->parse_query_filter_values( wp_unslash( $_GET[ $param_name ] ) );
            $this->append_taxonomy_filter_clause( $query_args, $taxonomy, $legacy_values, $operator, (string) ( $settings['external_filter_tax_relation'] ?? 'AND' ) );
        }

        // Multi-rule external filters: taxonomy and meta fields.
        $rules         = ( isset( $settings['external_filter_rules'] ) && is_array( $settings['external_filter_rules'] ) ) ? $settings['external_filter_rules'] : [];
        $tax_relation  = (string) ( $settings['external_filter_tax_relation'] ?? 'AND' );
        $meta_relation = (string) ( $settings['external_filter_meta_relation'] ?? 'AND' );

        foreach ( $rules as $rule ) {
            if ( ! is_array( $rule ) ) {
                continue;
            }

            $rule_type = sanitize_key( (string) ( $rule['rule_type'] ?? 'taxonomy' ) );
            $rule_param = sanitize_key( (string) ( $rule['param_key'] ?? '' ) );
            if ( '' === $rule_param || ! isset( $_GET[ $rule_param ] ) ) {
                continue;
            }

            $values = $this->parse_query_filter_values( wp_unslash( $_GET[ $rule_param ] ) );
            if ( empty( $values ) && ! in_array( strtoupper( (string) ( $rule['meta_compare'] ?? '' ) ), [ 'EXISTS', 'NOT EXISTS' ], true ) ) {
                continue;
            }

            if ( 'meta' === $rule_type ) {
                $this->append_meta_filter_clause(
                    $query_args,
                    (string) ( $rule['meta_key'] ?? '' ),
                    $values,
                    (string) ( $rule['meta_compare'] ?? '=' ),
                    (string) ( $rule['meta_type'] ?? 'CHAR' ),
                    $meta_relation
                );
                continue;
            }

            $this->append_taxonomy_filter_clause(
                $query_args,
                sanitize_key( (string) ( $rule['taxonomy'] ?? '' ) ),
                $values,
                (string) ( $rule['taxonomy_operator'] ?? 'IN' ),
                $tax_relation
            );
        }
    }

    private function get_pagination_query_arg(): string {
        $widget_id = preg_replace( '/[^a-zA-Z0-9_]/', '', (string) $this->get_id() );
        return 'krc_pg_' . $widget_id;
    }

    private function get_current_page( string $arg_name ): int {
        $fallback = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

        if ( ! isset( $_GET[ $arg_name ] ) ) {
            return $fallback;
        }

        $value = absint( wp_unslash( $_GET[ $arg_name ] ) );
        return max( 1, $value );
    }

    private function render_icon_html( array $icon, string $class_name ): string {
        if ( empty( $icon['value'] ) ) {
            return '';
        }

        ob_start();
        echo '<span class="' . esc_attr( $class_name ) . '" aria-hidden="true">';
        \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
        echo '</span>';
        return (string) ob_get_clean();
    }

    private function build_pagination_label( string $text, array $icon, string $position ): string {
        $text      = '' !== trim( $text ) ? trim( $text ) : '';
        $icon_html = $this->render_icon_html( $icon, 'krc-pagination-icon' );

        if ( '' !== $icon_html && in_array( strtolower( $text ), [ 'next', 'previous', 'prev' ], true ) ) {
            $text = '';
        }

        if ( '' === $icon_html ) {
            return '<span class="krc-pagination-label-text">' . esc_html( $text ) . '</span>';
        }

        if ( '' === $text ) {
            return $icon_html;
        }

        if ( 'before' === $position ) {
            return $icon_html . '<span class="krc-pagination-label-text">' . esc_html( $text ) . '</span>';
        }

        return '<span class="krc-pagination-label-text">' . esc_html( $text ) . '</span>' . $icon_html;
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $widget_id = 'krc-post-gallery-' . $this->get_id();
        $filter_param = '';
        $ajax_filter_params = [];
        $rearrange_enabled = ( 'yes' === ( $settings['enable_tile_rearrange'] ?? '' ) );
        $rearrange_duration = isset( $settings['tile_rearrange_duration']['size'] ) ? absint( $settings['tile_rearrange_duration']['size'] ) : 700;
        $entry_stagger = isset( $settings['card_entry_stagger']['size'] ) ? absint( $settings['card_entry_stagger']['size'] ) : 90;
        $update_exit_effect = (string) ( $settings['update_exit_effect'] ?? 'none' );
        $update_exit_duration = isset( $settings['update_exit_duration']['size'] ) ? absint( $settings['update_exit_duration']['size'] ) : 220;
        if ( $rearrange_duration < 100 ) {
            $rearrange_duration = 700;
        }
        if ( $entry_stagger < 0 ) {
            $entry_stagger = 0;
        }
        if ( $update_exit_duration < 80 ) {
            $update_exit_duration = 220;
        }

        if ( 'yes' === ( $settings['enable_external_filter'] ?? '' ) ) {
            $filter_param = sanitize_key( (string) ( $settings['external_filter_param'] ?? 'project_filter' ) );
            if ( '' !== $filter_param ) {
                $ajax_filter_params[] = $filter_param;
            }

            $rules = ( isset( $settings['external_filter_rules'] ) && is_array( $settings['external_filter_rules'] ) ) ? $settings['external_filter_rules'] : [];
            foreach ( $rules as $rule ) {
                if ( ! is_array( $rule ) ) {
                    continue;
                }
                $rule_param = sanitize_key( (string) ( $rule['param_key'] ?? '' ) );
                if ( '' !== $rule_param ) {
                    $ajax_filter_params[] = $rule_param;
                }
            }
        }

        $ajax_filter_params = array_values( array_unique( array_filter( $ajax_filter_params ) ) );
        $ajax_filter_params_attr = implode( ',', $ajax_filter_params );

        $layout = (string) ( $settings['layout'] ?? 'grid' );
        $content_pos = (string) ( $settings['content_position'] ?? 'bottom' );
        $widget_classes = [
            'krc-post-gallery-widget',
            'krc-layout-' . $layout,
        ];

        if ( 'overlay' === $layout ) {
            $widget_classes[] = 'krc-content-pos-' . $content_pos;
        }

        echo '<div id="' . esc_attr( $widget_id ) . '" class="' . esc_attr( implode( ' ', $widget_classes ) ) . '" data-krc-ajax-enabled="' . ( ! empty( $ajax_filter_params ) ? 'yes' : 'no' ) . '" data-krc-filter-param="' . esc_attr( $filter_param ) . '" data-krc-filter-params="' . esc_attr( $ajax_filter_params_attr ) . '" data-krc-rearrange="' . ( $rearrange_enabled ? 'yes' : 'no' ) . '" data-krc-rearrange-duration="' . esc_attr( (string) $rearrange_duration ) . '" data-krc-exit-effect="' . esc_attr( $update_exit_effect ) . '" data-krc-exit-duration="' . esc_attr( (string) $update_exit_duration ) . '">';
        echo '<div class="krc-post-gallery-ajax-slot">';

        $pagination_enabled = ( 'yes' === ( $settings['enable_pagination'] ?? '' ) );
        $pagination_arg     = $this->get_pagination_query_arg();
        $current_page       = $pagination_enabled ? $this->get_current_page( $pagination_arg ) : 1;

        $max_posts = max(
            1,
            (int) ( $settings['max_posts'] ?? $settings['posts_per_page'] ?? 10 )
        );
        $offset    = max( 0, (int) ( $settings['offset'] ?? 0 ) );
        $post_status = ! empty( $settings['post_status'] ) && is_array( $settings['post_status'] )
            ? array_values( array_map( 'sanitize_key', $settings['post_status'] ) )
            : [ 'publish' ];

        $query_args = [
            'post_status'         => $post_status,
            'posts_per_page'      => $max_posts,
            'ignore_sticky_posts' => ( 'yes' === ( $settings['ignore_sticky_posts'] ?? 'yes' ) ),
            'no_found_rows'       => ! $pagination_enabled,
        ];

        if ( $pagination_enabled ) {
            $query_args['paged'] = $current_page;
            $query_args['offset'] = $offset + ( ( $current_page - 1 ) * $max_posts );
            $query_args['krc_offset_base'] = $offset;
        } else {
            $query_args['offset'] = $offset;
        }

        $posts_source = $settings['posts_source'] ?? 'custom_posts';

        if ( 'taxonomies' === $posts_source ) {
            $source_taxonomy = sanitize_key( (string) ( $settings['source_taxonomy'] ?? '' ) );
            $hide_empty = ( 'yes' === ( $settings['source_taxonomy_hide_empty'] ?? 'yes' ) );
            $source_taxonomy_include = $this->normalize_id_array( $settings['source_taxonomy_include'] ?? [] );
            $source_taxonomy_exclude = $this->normalize_id_array( $settings['source_taxonomy_exclude'] ?? [] );

            if ( '' === $source_taxonomy || ! taxonomy_exists( $source_taxonomy ) ) {
                if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                    echo '<div class="krc-post-gallery-empty">' . esc_html__( 'Please select a taxonomy.', 'karice-elements' ) . '</div>';
                }
                echo '</div></div>';
                return;
            }

            $terms_args = [
                'taxonomy'   => $source_taxonomy,
                'hide_empty' => $hide_empty,
                'number'     => $max_posts,
                'offset'     => $offset,
            ];

            if ( ! empty( $source_taxonomy_include ) ) {
                $terms_args['include'] = $source_taxonomy_include;
            }

            if ( ! empty( $source_taxonomy_exclude ) ) {
                $terms_args['exclude'] = $source_taxonomy_exclude;
            }

            $orderby = $settings['orderby'] ?? 'name';
            if ( 'default' === $orderby ) {
                $orderby = 'name';
            }
            $terms_args['orderby'] = sanitize_key( $orderby );

            $order = strtoupper( (string) ( $settings['order'] ?? 'ASC' ) );
            if ( 'DEFAULT' === $order ) {
                $order = 'ASC';
            }
            if ( in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
                $terms_args['order'] = $order;
            }

            $terms = get_terms( $terms_args );

            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                echo '<div class="krc-post-gallery-empty">' . esc_html__( 'No terms found.', 'karice-elements' ) . '</div>';
                echo '</div></div>';
                return;
            }

            echo '<div class="krc-post-gallery">';
            $card_index = 0;

            foreach ( $terms as $term ) {
                $permalink = get_term_link( $term );
                if ( is_wp_error( $permalink ) ) {
                    $permalink = '#';
                }

                $title_meta_key = ! empty( $settings['taxonomy_field_map_title'] ) ? sanitize_key( $settings['taxonomy_field_map_title'] ) : '';
                $desc_meta_key  = ! empty( $settings['taxonomy_field_map_description'] ) ? sanitize_key( $settings['taxonomy_field_map_description'] ) : '';
                $image_meta_key = ! empty( $settings['taxonomy_field_map_image'] ) ? sanitize_key( $settings['taxonomy_field_map_image'] ) : 'thumbnail_id';

                $term_title       = ! empty( $title_meta_key ) ? get_term_meta( $term->term_id, $title_meta_key, true ) : $term->name;
                $term_description = ! empty( $desc_meta_key ) ? get_term_meta( $term->term_id, $desc_meta_key, true ) : $term->description;

                if ( empty( $term_title ) ) {
                    $term_title = $term->name;
                }

                $entry_delay = $card_index * $entry_stagger;
                $card_classes = [ 'krc-post-card', 'krc-term-card' ];
                echo '<article class="' . esc_attr( implode( ' ', $card_classes ) ) . '" style="--krc-card-index:' . esc_attr( (string) $card_index ) . ';--krc-card-delay:' . esc_attr( (string) $entry_delay ) . 'ms;">';

                $media_html = '';
                if ( 'yes' === ( $settings['show_image'] ?? 'yes' ) ) {
                    $thumbnail_id = get_term_meta( $term->term_id, $image_meta_key, true );
                    $media_html .= '<a class="krc-post-card-media" href="' . esc_url( $permalink ) . '">';
                    if ( $thumbnail_id ) {
                        $media_html .= wp_get_attachment_image( $thumbnail_id, 'large', false, [ 'loading' => 'lazy' ] );
                    } else {
                        // Show a placeholder or nothing
                        $media_html .= '<div class="krc-post-card-image-placeholder"></div>';
                    }
                    $media_html .= '</a>';
                }

                if ( 'overlay' !== $layout ) {
                    echo $media_html;
                }

                echo '<div class="krc-post-card-body">';
                echo '<h3 class="krc-post-card-title"><a href="' . esc_url( $permalink ) . '">' . esc_html( $term_title ) . '</a></h3>';

                if ( 'yes' === ( $settings['show_excerpt'] ?? 'yes' ) && ! empty( $term_description ) ) {
                    echo '<div class="krc-post-card-excerpt">' . esc_html( $this->trim_words( $term_description, (int) ( $settings['excerpt_length'] ?? 20 ) ) ) . '</div>';
                }

                if ( 'yes' === ( $settings['show_button'] ?? '' ) ) {
                    $button_text = $settings['button_text'] ?? '';
                    $button_icon = $settings['button_icon'] ?? [];
                    $icon_pos    = $settings['button_icon_position'] ?? 'after';

                    echo '<div class="krc-post-card-button-wrapper">';
                    echo '<a href="' . esc_url( $permalink ) . '" class="krc-post-card-button">';

                    if ( ! empty( $button_icon['value'] ) && 'before' === $icon_pos ) {
                        \Elementor\Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] );
                    }

                    if ( '' !== $button_text ) {
                        echo '<span class="krc-post-card-button-text">' . esc_html( $button_text ) . '</span>';
                    }

                    if ( ! empty( $button_icon['value'] ) && 'after' === $icon_pos ) {
                        \Elementor\Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] );
                    }

                    echo '</a>';
                    echo '</div>';
                }

                echo '</div>';

                if ( 'overlay' === $layout ) {
                    echo $media_html;
                }

                echo '</article>';
                $card_index++;
            }

            echo '</div>';
            echo '</div></div>';
            return;
        }

        if ( 'current_query' === $posts_source && isset( $GLOBALS['wp_query'] ) && $GLOBALS['wp_query'] instanceof \WP_Query ) {
            $current_query_args = $GLOBALS['wp_query']->query_vars;
            unset( $current_query_args['paged'], $current_query_args['page'] );
            $query_args = array_merge( $current_query_args, $query_args );
        } else {
            $selected_post_types = ! empty( $settings['post_types'] ) && is_array( $settings['post_types'] )
                ? array_map( 'sanitize_key', $settings['post_types'] )
                : [ 'post' ];
            $query_args['post_type'] = ! empty( $selected_post_types ) ? $selected_post_types : [ 'post' ];

            $include_post_ids = $this->normalize_id_array( $settings['include_post_ids'] ?? [] );
            $exclude_post_ids = $this->normalize_id_array( $settings['exclude_post_ids'] ?? [] );

            $exclude_by = (array) ( $settings['exclude_by'] ?? [] );
            if ( in_array( 'current_post', $exclude_by, true ) ) {
                $current_id = get_queried_object_id();
                if ( $current_id && ! in_array( $current_id, $exclude_post_ids, true ) ) {
                    $exclude_post_ids[] = $current_id;
                }
            }

            if ( ! empty( $include_post_ids ) ) {
                $query_args['post__in'] = $include_post_ids;
            }

            if ( ! empty( $exclude_post_ids ) ) {
                $query_args['post__not_in'] = $exclude_post_ids;
            }

            $include_authors = $this->normalize_id_array( $settings['include_authors'] ?? [] );
            $exclude_authors = $this->normalize_id_array( $settings['exclude_authors'] ?? [] );

            if ( ! empty( $include_authors ) ) {
                $query_args['author__in'] = $include_authors;
            }

            if ( ! empty( $exclude_authors ) ) {
                $query_args['author__not_in'] = $exclude_authors;
            }

            $tax_query         = [ 'relation' => 'AND' ];
            $include_terms_ids = $this->normalize_id_array( $settings['include_terms'] ?? [] );
            $exclude_terms_ids = $this->normalize_id_array( $settings['exclude_terms'] ?? [] );

            if ( ! empty( $include_terms_ids ) ) {
                $include_group = [
                    'relation' => ( 'OR' === ( $settings['include_terms_relation'] ?? 'AND' ) ) ? 'OR' : 'AND',
                ];
                foreach ( $this->group_terms_by_taxonomy( $include_terms_ids ) as $taxonomy => $term_ids ) {
                    $include_group[] = [
                        'taxonomy'         => $taxonomy,
                        'field'            => 'term_id',
                        'terms'            => $term_ids,
                        'operator'         => 'IN',
                        'include_children' => ( 'yes' === ( $settings['include_terms_children'] ?? '' ) ),
                    ];
                }
                if ( count( $include_group ) > 1 ) {
                    $tax_query[] = $include_group;
                }
            }

            if ( ! empty( $exclude_terms_ids ) ) {
                foreach ( $this->group_terms_by_taxonomy( $exclude_terms_ids ) as $taxonomy => $term_ids ) {
                    $tax_query[] = [
                        'taxonomy'         => $taxonomy,
                        'field'            => 'term_id',
                        'terms'            => $term_ids,
                        'operator'         => 'NOT IN',
                        'include_children' => true,
                    ];
                }
            }

            if ( count( $tax_query ) > 1 ) {
                $query_args['tax_query'] = $tax_query;
            }
        }

        $this->apply_external_filter( $query_args, $settings );

        $orderby = $settings['orderby'] ?? 'default';
        if ( 'default' !== $orderby ) {
            $query_args['orderby'] = sanitize_key( $orderby );
        }

        $order = strtoupper( (string) ( $settings['order'] ?? 'default' ) );
        if ( in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
            $query_args['order'] = $order;
        }

        $found_posts_filter = null;

        if ( $pagination_enabled && $offset > 0 ) {
            $found_posts_filter = static function ( $found_posts, $query ) {
                $base_offset = (int) $query->get( 'krc_offset_base' );

                if ( $base_offset <= 0 ) {
                    return $found_posts;
                }

                return max( 0, (int) $found_posts - $base_offset );
            };
            add_filter( 'found_posts', $found_posts_filter, 10, 2 );
        }

        $query = new \WP_Query( $query_args );

        if ( $found_posts_filter ) {
            remove_filter( 'found_posts', $found_posts_filter, 10 );
        }

        if ( ! $query->have_posts() ) {
            echo '<div class="krc-post-gallery-empty">' . esc_html__( 'No posts found.', 'karice-elements' ) . '</div>';
            echo '</div></div>';
            return;
        }

        echo '<div class="krc-post-gallery">';
        $card_index = 0;

        while ( $query->have_posts() ) {
            $query->the_post();
            $permalink = get_permalink();
            $entry_delay = $card_index * $entry_stagger;
            echo '<article class="krc-post-card" style="--krc-card-index:' . esc_attr( (string) $card_index ) . ';--krc-card-delay:' . esc_attr( (string) $entry_delay ) . 'ms;">';

            $media_html = '';
            if ( 'yes' === ( $settings['show_image'] ?? 'yes' ) && has_post_thumbnail() ) {
                $media_html .= '<a class="krc-post-card-media" href="' . esc_url( $permalink ) . '">';
                $media_html .= get_the_post_thumbnail( null, 'large', [ 'loading' => 'lazy' ] );
                $media_html .= '</a>';
            }

            if ( 'overlay' !== $layout ) {
                echo $media_html;
            }

            echo '<div class="krc-post-card-body">';
            echo '<h3 class="krc-post-card-title"><a href="' . esc_url( $permalink ) . '">' . esc_html( get_the_title() ) . '</a></h3>';

            if ( 'yes' === ( $settings['show_custom_meta_line'] ?? 'yes' ) ) {
                $meta_line = $this->build_custom_meta_line(
                    (int) get_the_ID(),
                    (string) ( $settings['custom_meta_format'] ?? '%location% - %partner%' )
                );

                if ( '' !== $meta_line ) {
                    echo '<div class="krc-post-card-custom-meta">' . esc_html( $meta_line ) . '</div>';
                }
            }

            if ( 'yes' === ( $settings['show_date'] ?? 'yes' ) || 'yes' === ( $settings['show_author'] ?? '' ) ) {
                echo '<div class="krc-post-card-meta">';

                if ( 'yes' === ( $settings['show_date'] ?? 'yes' ) ) {
                    echo '<span class="krc-post-card-date">' . esc_html( get_the_date() ) . '</span>';
                }

                if ( 'yes' === ( $settings['show_author'] ?? '' ) ) {
                    echo '<span class="krc-post-card-author">' . esc_html( get_the_author() ) . '</span>';
                }

                echo '</div>';
            }

            if ( 'yes' === ( $settings['show_excerpt'] ?? 'yes' ) ) {
                $excerpt = get_the_excerpt();
                if ( '' === trim( $excerpt ) ) {
                    $excerpt = get_the_content( null, false );
                }

                echo '<div class="krc-post-card-excerpt">' . esc_html( $this->trim_words( $excerpt, (int) ( $settings['excerpt_length'] ?? 20 ) ) ) . '</div>';
            }

            if ( 'yes' === ( $settings['show_button'] ?? '' ) ) {
                $button_text = $settings['button_text'] ?? '';
                $button_icon = $settings['button_icon'] ?? [];
                $icon_pos    = $settings['button_icon_position'] ?? 'after';

                echo '<div class="krc-post-card-button-wrapper">';
                echo '<a href="' . esc_url( $permalink ) . '" class="krc-post-card-button">';

                if ( ! empty( $button_icon['value'] ) && 'before' === $icon_pos ) {
                    \Elementor\Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] );
                }

                if ( '' !== $button_text ) {
                    echo '<span class="krc-post-card-button-text">' . esc_html( $button_text ) . '</span>';
                }

                if ( ! empty( $button_icon['value'] ) && 'after' === $icon_pos ) {
                    \Elementor\Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] );
                }

                echo '</a>';
                echo '</div>';
            }

            echo '</div>';

            if ( 'overlay' === $layout ) {
                echo $media_html;
            }

            echo '</article>';
            $card_index++;
        }

        echo '</div>';

        if ( $pagination_enabled && $query->max_num_pages > 1 ) {
            $pagination_base = remove_query_arg( $pagination_arg );
            $prev_label      = $this->build_pagination_label(
                (string) ( $settings['pagination_prev_text'] ?? esc_html__( 'Previous', 'karice-elements' ) ),
                is_array( $settings['pagination_prev_icon'] ?? null ) ? $settings['pagination_prev_icon'] : [],
                (string) ( $settings['pagination_prev_icon_position'] ?? 'before' )
            );
            $next_label      = $this->build_pagination_label(
                (string) ( $settings['pagination_next_text'] ?? esc_html__( 'Next', 'karice-elements' ) ),
                is_array( $settings['pagination_next_icon'] ?? null ) ? $settings['pagination_next_icon'] : [],
                (string) ( $settings['pagination_next_icon_position'] ?? 'after' )
            );
            $pagination_html = paginate_links(
                [
                    'base'      => esc_url_raw( add_query_arg( $pagination_arg, '%#%', $pagination_base ) ),
                    'format'    => '',
                    'current'   => $current_page,
                    'total'     => (int) $query->max_num_pages,
                    'mid_size'  => 1,
                    'prev_next' => true,
                    'prev_text' => $prev_label,
                    'next_text' => $next_label,
                    'type'      => 'list',
                ]
            );

            if ( ! empty( $pagination_html ) ) {
                echo '<nav class="krc-post-gallery-pagination" aria-label="' . esc_attr__( 'Posts Pagination', 'karice-elements' ) . '">';
                echo wp_kses_post( $pagination_html );
                echo '</nav>';
            }
        }

        wp_reset_postdata();
        echo '</div></div>';
    }
}
