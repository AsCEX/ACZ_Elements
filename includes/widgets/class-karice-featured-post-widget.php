<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KC_Karice_Featured_Post_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'karice_featured_post';
    }

    public function get_title() {
        return esc_html__( 'Karice Featured Post', 'karice-elements' );
    }

    public function get_icon() {
        return 'eicon-featured-image';
    }

    public function get_categories() {
        return [ 'karice' ];
    }

    public function get_keywords() {
        return [ 'post', 'featured', 'single', 'karice' ];
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    private function get_post_type_options() {
        $options    = [];
        $post_types = get_post_types( [ 'public' => true ], 'objects' );

        foreach ( $post_types as $post_type ) {
            if ( in_array( $post_type->name, [ 'attachment', 'revision', 'nav_menu_item' ], true ) ) {
                continue;
            }
            $options[ $post_type->name ] = $post_type->label;
        }
        return $options;
    }

    private function get_taxonomy_options() {
        $options    = [];
        $taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );

        foreach ( $taxonomies as $taxonomy ) {
            $options[ $taxonomy->name ] = $taxonomy->label;
        }
        return $options;
    }

    private function get_post_options() {
        if ( ! function_exists( 'get_posts' ) ) {
            return [];
        }
        $options = [];
        $posts   = get_posts( [
            'post_type'      => 'any',
            'posts_per_page' => 500, // Increased limit
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        foreach ( $posts as $post ) {
            $options[ $post->ID ] = $post->post_title . ' (' . $post->ID . ') - [' . $post->post_type . ']';
        }
        return $options;
    }

    private function get_term_options_list() {
        if ( ! function_exists( 'get_terms' ) || ! function_exists( 'get_taxonomies' ) ) {
            return [];
        }
        $options = [];
        $terms   = get_terms( [
            'taxonomy'   => get_taxonomies( [ 'public' => true ] ),
            'hide_empty' => false,
            'number'     => 500, // Increased limit
        ] );

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->term_id ] = $term->name . ' (' . $term->taxonomy . ')';
            }
        }
        return $options;
    }

    protected function register_content_controls() {
        $this->start_controls_section(
            'section_query',
            [
                'label' => esc_html__( 'Source', 'karice-elements' ),
            ]
        );

        $this->add_control(
            'source',
            [
                'label'   => esc_html__( 'Source', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'post',
                'options' => [
                    'post'     => esc_html__( 'Post/Custom Post', 'karice-elements' ),
                    'taxonomy' => esc_html__( 'Taxonomy/Category', 'karice-elements' ),
                ],
            ]
        );

        $this->add_control(
            'post_id',
            [
                'label'       => esc_html__( 'Post', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'options'     => $this->get_post_options(),
                'multiple'    => false,
                'label_block' => true,
                'condition'   => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'taxonomy',
            [
                'label'     => esc_html__( 'Taxonomy', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'category',
                'options'   => $this->get_taxonomy_options(),
                'condition' => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $this->add_control(
            'term_id',
            [
                'label'       => esc_html__( 'Term', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'options'     => $this->get_term_options_list(),
                'multiple'    => false,
                'label_block' => true,
                'condition'   => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_post_id',
            [
                'label'       => esc_html__( 'Select Post', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'options'     => $this->get_post_options(),
                'multiple'    => false,
                'label_block' => true,
                'condition'   => [
                    'source'   => 'taxonomy',
                    'term_id!' => '',
                ],
                'description' => esc_html__( 'Optionally select a specific post from the selected term. If left empty, the latest post will be shown.', 'karice-elements' ),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Content', 'karice-elements' ),
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label'        => esc_html__( 'Show Featured Image', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Show', 'karice-elements' ),
                'label_off'    => esc_html__( 'Hide', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'image_source',
            [
                'label'     => esc_html__( 'Image Source', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default' => esc_html__( 'Default (Featured Image)', 'karice-elements' ),
                    'meta'    => esc_html__( 'Custom Meta Field (ID)', 'karice-elements' ),
                    'static'  => esc_html__( 'Static Image', 'karice-elements' ),
                ],
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'image_meta_key',
            [
                'label'       => esc_html__( 'Meta Key (Image ID)', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter your meta key', 'karice-elements' ),
                'condition'   => [
                    'show_image'   => 'yes',
                    'image_source' => 'meta',
                ],
            ]
        );

        $this->add_control(
            'image_static_value',
            [
                'label'     => esc_html__( 'Static Image', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::MEDIA,
                'condition' => [
                    'show_image'   => 'yes',
                    'image_source' => 'static',
                ],
            ]
        );

        $this->add_control(
            'show_taxonomy',
            [
                'label'        => esc_html__( 'Show Taxonomy/Category', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Show', 'karice-elements' ),
                'label_off'    => esc_html__( 'Hide', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            ]
        );

        $this->add_control(
            'taxonomy_source',
            [
                'label'     => esc_html__( 'Taxonomy Source', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default' => esc_html__( 'Default (Post Taxonomy)', 'karice-elements' ),
                    'meta'    => esc_html__( 'Custom Meta Field', 'karice-elements' ),
                    'static'  => esc_html__( 'Static Value', 'karice-elements' ),
                ],
                'condition' => [
                    'show_taxonomy' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_meta_key',
            [
                'label'       => esc_html__( 'Meta Key', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter your meta key', 'karice-elements' ),
                'condition'   => [
                    'show_taxonomy'   => 'yes',
                    'taxonomy_source' => 'meta',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_static_value',
            [
                'label'       => esc_html__( 'Static Value', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter static taxonomy name', 'karice-elements' ),
                'condition'   => [
                    'show_taxonomy'   => 'yes',
                    'taxonomy_source' => 'static',
                ],
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label'        => esc_html__( 'Show Title', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Show', 'karice-elements' ),
                'label_off'    => esc_html__( 'Hide', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            ]
        );

        $this->add_control(
            'title_source',
            [
                'label'     => esc_html__( 'Title Source', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default' => esc_html__( 'Default (Post Title)', 'karice-elements' ),
                    'meta'    => esc_html__( 'Custom Meta Field', 'karice-elements' ),
                    'static'  => esc_html__( 'Static Value', 'karice-elements' ),
                ],
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'title_meta_key',
            [
                'label'       => esc_html__( 'Meta Key', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter your meta key', 'karice-elements' ),
                'condition'   => [
                    'show_title'   => 'yes',
                    'title_source' => 'meta',
                ],
            ]
        );

        $this->add_control(
            'title_static_value',
            [
                'label'       => esc_html__( 'Static Value', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter static title', 'karice-elements' ),
                'condition'   => [
                    'show_title'   => 'yes',
                    'title_source' => 'static',
                ],
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label'        => esc_html__( 'Show Excerpt/Description', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Show', 'karice-elements' ),
                'label_off'    => esc_html__( 'Hide', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            ]
        );

        $this->add_control(
            'excerpt_source',
            [
                'label'     => esc_html__( 'Excerpt Source', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default' => esc_html__( 'Default (Post Excerpt)', 'karice-elements' ),
                    'meta'    => esc_html__( 'Custom Meta Field', 'karice-elements' ),
                    'static'  => esc_html__( 'Static Value', 'karice-elements' ),
                ],
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'excerpt_meta_key',
            [
                'label'       => esc_html__( 'Meta Key', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter your meta key', 'karice-elements' ),
                'condition'   => [
                    'show_excerpt'   => 'yes',
                    'excerpt_source' => 'meta',
                ],
            ]
        );

        $this->add_control(
            'excerpt_static_value',
            [
                'label'       => esc_html__( 'Static Value', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXTAREA,
                'placeholder' => esc_html__( 'Enter static excerpt', 'karice-elements' ),
                'condition'   => [
                    'show_excerpt'   => 'yes',
                    'excerpt_source' => 'static',
                ],
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label'     => esc_html__( 'Excerpt Length', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 25,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_button',
            [
                'label'        => esc_html__( 'Show Button', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Show', 'karice-elements' ),
                'label_off'    => esc_html__( 'Hide', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => 'no',
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
            'button_link_source',
            [
                'label'     => esc_html__( 'Link Source', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default' => esc_html__( 'Default (Post Permalink)', 'karice-elements' ),
                    'meta'    => esc_html__( 'Custom Meta Field', 'karice-elements' ),
                    'static'  => esc_html__( 'Static URL', 'karice-elements' ),
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_meta_key',
            [
                'label'       => esc_html__( 'Meta Key', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter your meta key', 'karice-elements' ),
                'condition'   => [
                    'show_button'        => 'yes',
                    'button_link_source' => 'meta',
                ],
            ]
        );

        $this->add_control(
            'button_static_url',
            [
                'label'       => esc_html__( 'Static URL', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'karice-elements' ),
                'condition'   => [
                    'show_button'        => 'yes',
                    'button_link_source' => 'static',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls() {
        // Container Style
        $this->start_controls_section(
            'section_style_container',
            [
                'label' => esc_html__( 'Container', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'container_alignment',
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
                    '{{WRAPPER}} .krc-featured-post' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'container_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-featured-post' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Image Style
        $this->start_controls_section(
            'section_style_image',
            [
                'label'     => esc_html__( 'Image', 'karice-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label'      => esc_html__( 'Height', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vh', 'custom' ],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-image img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_object_fit',
            [
                'label'     => esc_html__( 'Object Fit', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'cover',
                'options'   => [
                    ''        => esc_html__( 'Default', 'karice-elements' ),
                    'fill'    => esc_html__( 'Fill', 'karice-elements' ),
                    'cover'   => esc_html__( 'Cover', 'karice-elements' ),
                    'contain' => esc_html__( 'Contain', 'karice-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-featured-post-image img' => 'object-fit: {{VALUE}};',
                ],
                'condition' => [
                    'image_height[size]!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Taxonomy Style
        $this->start_controls_section(
            'section_style_taxonomy',
            [
                'label'     => esc_html__( 'Taxonomy', 'karice-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_taxonomy' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'taxonomy_alignment',
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
                    '{{WRAPPER}} .krc-featured-post-taxonomy' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-featured-post-taxonomy' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'taxonomy_typography',
                'selector' => '{{WRAPPER}} .krc-featured-post-taxonomy',
            ]
        );

        $this->add_responsive_control(
            'taxonomy_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-taxonomy' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Title Style
        $this->start_controls_section(
            'section_style_title',
            [
                'label'     => esc_html__( 'Title', 'karice-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title' => 'yes',
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
                    '{{WRAPPER}} .krc-featured-post-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-featured-post-title, {{WRAPPER}} .krc-featured-post-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .krc-featured-post-title, {{WRAPPER}} .krc-featured-post-title a',
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Excerpt Style
        $this->start_controls_section(
            'section_style_excerpt',
            [
                'label'     => esc_html__( 'Excerpt', 'karice-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_excerpt' => 'yes',
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
                    '{{WRAPPER}} .krc-featured-post-excerpt' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-featured-post-excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'excerpt_typography',
                'selector' => '{{WRAPPER}} .krc-featured-post-excerpt',
            ]
        );

        $this->add_responsive_control(
            'excerpt_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Button Style
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
                    '{{WRAPPER}} .krc-featured-post-button-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .krc-featured-post-button',
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
                    '{{WRAPPER}} .krc-featured-post-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-featured-post-button' => 'background-color: {{VALUE}};',
                ],
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
                    '{{WRAPPER}} .krc-featured-post-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-featured-post-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-featured-post-button:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'button_border_border!' => '',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .krc-featured-post-button',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-featured-post-button-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $query_args = [
            'posts_per_page'      => 1,
            'ignore_sticky_posts' => true,
            'post_status'         => 'publish',
        ];

        if ( 'post' === $settings['source'] ) {
            $query_args['post_type'] = 'any';
            if ( ! empty( $settings['post_id'] ) ) {
                $query_args['p'] = intval( $settings['post_id'] );
            }
        } else {
            $taxonomy = ! empty( $settings['taxonomy'] ) ? $settings['taxonomy'] : 'category';
            $query_args['post_type'] = 'any';
            if ( ! empty( $settings['taxonomy_post_id'] ) ) {
                $query_args['p'] = intval( $settings['taxonomy_post_id'] );
            }
            if ( ! empty( $settings['term_id'] ) ) {
                $query_args['tax_query'] = [
                    [
                        'taxonomy' => $taxonomy,
                        'field'    => 'term_id',
                        'terms'    => [ intval( $settings['term_id'] ) ],
                    ],
                ];
            }
        }

        $query = new \WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="krc-featured-post-empty">' . esc_html__( 'No post found.', 'karice-elements' ) . '</div>';
            }
            return;
        }

        $query->the_post();
        $post_id = get_the_ID();

        echo '<div class="krc-featured-post">';

        if ( 'yes' === $settings['show_image'] ) {
            $image_html = '';
            if ( 'static' === $settings['image_source'] && ! empty( $settings['image_static_value']['id'] ) ) {
                $image_html = wp_get_attachment_image( $settings['image_static_value']['id'], 'large' );
            } elseif ( 'meta' === $settings['image_source'] && ! empty( $settings['image_meta_key'] ) ) {
                $attachment_id = get_post_meta( $post_id, $settings['image_meta_key'], true );
                if ( $attachment_id ) {
                    $image_html = wp_get_attachment_image( $attachment_id, 'large' );
                }
            } elseif ( has_post_thumbnail() ) {
                $image_html = get_the_post_thumbnail( $post_id, 'large' );
            }

            if ( $image_html ) {
                echo '<div class="krc-featured-post-image">';
                echo '<a href="' . esc_url( get_permalink() ) . '">';
                echo $image_html;
                echo '</a>';
                echo '</div>';
            }
        }

        if ( 'yes' === $settings['show_taxonomy'] ) {
            $tax_content = '';
            if ( 'static' === $settings['taxonomy_source'] ) {
                $tax_content = esc_html( $settings['taxonomy_static_value'] );
            } elseif ( 'meta' === $settings['taxonomy_source'] && ! empty( $settings['taxonomy_meta_key'] ) ) {
                $tax_content = esc_html( get_post_meta( $post_id, $settings['taxonomy_meta_key'], true ) );
            } else {
                $tax_source = ( 'taxonomy' === $settings['source'] ) ? $settings['taxonomy'] : 'category';
                $terms = get_the_terms( $post_id, $tax_source );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    $term_links = [];
                    foreach ( $terms as $term ) {
                        $term_links[] = '<span>' . esc_html( $term->name ) . '</span>';
                    }
                    $tax_content = implode( ', ', $term_links );
                }
            }

            if ( ! empty( $tax_content ) ) {
                echo '<div class="krc-featured-post-taxonomy">';
                echo $tax_content;
                echo '</div>';
            }
        }

        if ( 'yes' === $settings['show_title'] ) {
            $title = get_the_title();
            if ( 'static' === $settings['title_source'] ) {
                $title = $settings['title_static_value'];
            } elseif ( 'meta' === $settings['title_source'] && ! empty( $settings['title_meta_key'] ) ) {
                $title = get_post_meta( $post_id, $settings['title_meta_key'], true );
            }

            echo '<h3 class="krc-featured-post-title">';
            echo '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( $title ) . '</a>';
            echo '</h3>';
        }

        if ( 'yes' === $settings['show_excerpt'] ) {
            $excerpt = '';
            if ( 'static' === $settings['excerpt_source'] ) {
                $excerpt = $settings['excerpt_static_value'];
            } elseif ( 'meta' === $settings['excerpt_source'] && ! empty( $settings['excerpt_meta_key'] ) ) {
                $excerpt = get_post_meta( $post_id, $settings['excerpt_meta_key'], true );
            } else {
                $excerpt = get_the_excerpt();
                if ( empty( $excerpt ) ) {
                    $excerpt = get_the_content();
                }
            }

            if ( ! empty( $excerpt ) ) {
                echo '<div class="krc-featured-post-excerpt">';
                echo wp_trim_words( $excerpt, intval( $settings['excerpt_length'] ), '...' );
                echo '</div>';
            }
        }

        if ( 'yes' === $settings['show_button'] ) {
            $button_text = ! empty( $settings['button_text'] ) ? $settings['button_text'] : esc_html__( 'Read More', 'karice-elements' );
            $button_url = get_permalink( $post_id );

            if ( 'static' === $settings['button_link_source'] && ! empty( $settings['button_static_url']['url'] ) ) {
                $button_url = $settings['button_static_url']['url'];
                $this->add_link_attributes( 'button', $settings['button_static_url'] );
            } elseif ( 'meta' === $settings['button_link_source'] && ! empty( $settings['button_meta_key'] ) ) {
                $meta_url = get_post_meta( $post_id, $settings['button_meta_key'], true );
                if ( ! empty( $meta_url ) ) {
                    $button_url = $meta_url;
                }
            }

            if ( 'static' !== $settings['button_link_source'] ) {
                $this->add_render_attribute( 'button', 'href', esc_url( $button_url ) );
            }

            $this->add_render_attribute( 'button', 'class', 'krc-featured-post-button' );

            echo '<div class="krc-featured-post-button-wrapper">';
            echo '<a ' . $this->get_render_attribute_string( 'button' ) . '>';
            echo esc_html( $button_text );
            echo '</a>';
            echo '</div>';
        }

        echo '</div>';

        wp_reset_postdata();
    }
}
