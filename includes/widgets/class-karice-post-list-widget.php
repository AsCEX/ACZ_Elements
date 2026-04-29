<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KC_Karice_Post_List_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'karice_post_list';
    }

    public function get_title(): string {
        return esc_html__( 'Karice Post LIst', 'karice-elements' );
    }

    public function get_icon(): string {
        return 'eicon-post-list';
    }

    public function get_categories(): array {
        return [ 'karice' ];
    }

    public function get_keywords(): array {
        return [ 'karice', 'post', 'list', 'taxonomy', 'acf', 'gallery' ];
    }

    public function get_style_depends(): array {
        return [ 'karice-post-gallery' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    private function register_content_controls(): void {
        $this->start_controls_section(
            'section_source',
            [
                'label' => esc_html__( 'Source', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'source',
            [
                'label'   => esc_html__( 'Item Source', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'items',
                'options' => [
                    'items'    => esc_html__( 'Items (Elementor Repeater)', 'karice-elements' ),
                    'post'     => esc_html__( 'Post', 'karice-elements' ),
                    'taxonomy' => esc_html__( 'Taxonomy', 'karice-elements' ),
                ],
            ]
        );

        $this->register_items_repeater_controls();
        $this->register_post_source_controls();
        $this->register_taxonomy_source_controls();

        $this->end_controls_section();
    }

    private function register_items_repeater_controls(): void {
        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'title',
            [
                'label'       => esc_html__( 'Title', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'List Item Title', 'karice-elements' ),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'content',
            [
                'label'   => esc_html__( 'Content', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'List item content.', 'karice-elements' ),
                'rows'    => 4,
            ]
        );

        $repeater->add_control(
            'gallery',
            [
                'label'   => esc_html__( 'Gallery', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::GALLERY,
                'default' => [],
            ]
        );

        $this->add_control(
            'items',
            [
                'label'       => esc_html__( 'Items', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ title }}}',
                'default'     => [
                    [
                        'title'   => esc_html__( 'Item One', 'karice-elements' ),
                        'content' => esc_html__( 'Content for item one.', 'karice-elements' ),
                    ],
                    [
                        'title'   => esc_html__( 'Item Two', 'karice-elements' ),
                        'content' => esc_html__( 'Content for item two.', 'karice-elements' ),
                    ],
                ],
                'condition'   => [
                    'source' => 'items',
                ],
            ]
        );
    }

    private function register_post_source_controls(): void {
        $this->add_control(
            'post_type',
            [
                'label'       => esc_html__( 'Post Type', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default'     => 'post',
                'options'     => $this->get_post_type_options(),
                'label_block' => true,
                'condition'   => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'     => esc_html__( 'Items Limit', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 8,
                'min'       => 1,
                'max'       => 100,
                'condition' => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'post_include_current_taxonomy',
            [
                'label'        => esc_html__( 'Include Current Taxonomy', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'karice-elements' ),
                'label_off'    => esc_html__( 'No', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => '',
                'description'  => esc_html__( 'When on a taxonomy archive page, only posts assigned to the current term will be shown.', 'karice-elements' ),
                'condition'    => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'post_title_map',
            [
                'label'     => esc_html__( 'Title Map', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default' => esc_html__( 'Default (Post Title)', 'karice-elements' ),
                    'acf'     => esc_html__( 'ACF Field', 'karice-elements' ),
                ],
                'condition' => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'post_title_acf_field',
            [
                'label'       => esc_html__( 'Title ACF Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'title',
                'label_block' => true,
                'condition'   => [
                    'source'         => 'post',
                    'post_title_map' => 'acf',
                ],
            ]
        );

        $this->add_control(
            'post_content_source_option',
            [
                'label'     => esc_html__( 'Content Options', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default'  => esc_html__( 'Default', 'karice-elements' ),
                    'template' => esc_html__( 'Elementor Template', 'karice-elements' ),
                ],
                'condition' => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'post_content_map',
            [
                'label'     => esc_html__( 'Content Map', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'excerpt',
                'options'   => [
                    'excerpt' => esc_html__( 'Default (Excerpt)', 'karice-elements' ),
                    'content' => esc_html__( 'Default (Content)', 'karice-elements' ),
                    'acf'     => esc_html__( 'ACF Field', 'karice-elements' ),
                ],
                'condition' => [
                    'source'                     => 'post',
                    'post_content_source_option' => 'default',
                ],
            ]
        );

        $this->add_control(
            'post_content_template_id',
            [
                'label'       => esc_html__( 'Content Template', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'options'     => $this->get_elementor_template_options(),
                'label_block' => true,
                'condition'   => [
                    'source'                     => 'post',
                    'post_content_source_option' => 'template',
                ],
            ]
        );

        $this->add_control(
            'excerpt_words',
            [
                'label'     => esc_html__( 'Excerpt Words', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 26,
                'min'       => 5,
                'max'       => 200,
                'condition' => [
                    'source'                     => 'post',
                    'post_content_source_option' => 'default',
                    'post_content_map'           => 'excerpt',
                ],
            ]
        );

        $this->add_control(
            'post_content_acf_field',
            [
                'label'       => esc_html__( 'Content ACF Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'content',
                'label_block' => true,
                'condition'   => [
                    'source'                     => 'post',
                    'post_content_source_option' => 'default',
                    'post_content_map'           => 'acf',
                ],
            ]
        );

        $this->add_control(
            'post_gallery_field',
            [
                'label'       => esc_html__( 'Gallery Map (ACF Repeater)', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'gallery',
                'label_block' => true,
                'condition'   => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'post_item_repeater_field',
            [
                'label'       => esc_html__( 'Item Repeater Field (Optional)', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'label_block' => true,
                'description' => esc_html__( 'If set and has rows, each repeater row becomes one list item.', 'karice-elements' ),
                'condition'   => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'post_item_repeater_title_field',
            [
                'label'       => esc_html__( 'Repeater Title Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'title',
                'label_block' => true,
                'condition'   => [
                    'source'                   => 'post',
                    'post_item_repeater_field!' => '',
                ],
            ]
        );

        $this->add_control(
            'post_item_repeater_content_field',
            [
                'label'       => esc_html__( 'Repeater Content Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'content',
                'label_block' => true,
                'condition'   => [
                    'source'                   => 'post',
                    'post_item_repeater_field!' => '',
                ],
            ]
        );

        $this->add_control(
            'post_item_repeater_gallery_image_field',
            [
                'label'       => esc_html__( 'Repeater Gallery Image Sub Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'image',
                'label_block' => true,
                'condition'   => [
                    'source'                   => 'post',
                    'post_item_repeater_field!' => '',
                ],
            ]
        );

        $this->add_control(
            'post_gallery_image_field',
            [
                'label'       => esc_html__( 'Gallery Image Sub Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'image',
                'label_block' => true,
                'condition'   => [
                    'source' => 'post',
                ],
            ]
        );
    }

    private function register_taxonomy_source_controls(): void {
        $this->add_control(
            'taxonomy',
            [
                'label'       => esc_html__( 'Taxonomy', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => array_merge(
                    [
                        'current_term' => esc_html__( 'Current Term', 'karice-elements' ),
                    ],
                    $this->get_taxonomy_options()
                ),
                'label_block' => true,
                'condition'   => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $this->add_control(
            'terms_limit',
            [
                'label'     => esc_html__( 'Items Limit', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 8,
                'min'       => 1,
                'max'       => 200,
                'condition' => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $this->add_control(
            'hide_empty',
            [
                'label'        => esc_html__( 'Hide Empty Terms', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'karice-elements' ),
                'label_off'    => esc_html__( 'No', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_item_content_source',
            [
                'label'     => esc_html__( 'Item Content Source', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default'  => esc_html__( 'Default (Title + Content + Gallery)', 'karice-elements' ),
                    'repeater' => esc_html__( 'ACF Repeater Field', 'karice-elements' ),
                ],
                'condition'   => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_title_map',
            [
                'label'     => esc_html__( 'Title Map', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default' => esc_html__( 'Default (Term Name)', 'karice-elements' ),
                    'acf'     => esc_html__( 'ACF Field', 'karice-elements' ),
                ],
                'condition' => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'default',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_title_acf_field',
            [
                'label'       => esc_html__( 'Title ACF Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'title',
                'label_block' => true,
                'condition'   => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'default',
                    'taxonomy_title_map'           => 'acf',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_content_map',
            [
                'label'     => esc_html__( 'Content Map', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'default',
                'options'   => [
                    'default' => esc_html__( 'Default (Term Description)', 'karice-elements' ),
                    'acf'     => esc_html__( 'ACF Field', 'karice-elements' ),
                ],
                'condition' => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'default',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_content_acf_field',
            [
                'label'       => esc_html__( 'Content ACF Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'content',
                'label_block' => true,
                'condition'   => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'default',
                    'taxonomy_content_map'         => 'acf',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_gallery_field',
            [
                'label'       => esc_html__( 'Gallery Map (ACF Repeater)', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'gallery',
                'label_block' => true,
                'condition'   => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'default',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_gallery_image_field',
            [
                'label'       => esc_html__( 'Gallery Image Sub Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'image',
                'label_block' => true,
                'condition'   => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'default',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_item_repeater_field',
            [
                'label'       => esc_html__( 'Item Repeater Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'label_block' => true,
                'description' => esc_html__( 'Example: area_list', 'karice-elements' ),
                'condition'   => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'repeater',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_item_repeater_title_field',
            [
                'label'       => esc_html__( 'Repeater Title Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'area_title',
                'label_block' => true,
                'condition'   => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'repeater',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_item_repeater_content_field',
            [
                'label'       => esc_html__( 'Repeater Content Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'area_content',
                'label_block' => true,
                'condition'   => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'repeater',
                ],
            ]
        );

        $this->add_control(
            'taxonomy_item_repeater_gallery_image_field',
            [
                'label'       => esc_html__( 'Repeater Gallery Image Sub Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'image',
                'label_block' => true,
                'condition'   => [
                    'source'                       => 'taxonomy',
                    'taxonomy_item_content_source' => 'repeater',
                ],
            ]
        );
    }

    private function register_style_controls(): void {
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
                    '{{WRAPPER}} .krc-post-list-item-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_align',
            [
                'label'     => esc_html__( 'Alignment', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__( 'Justified', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-list-item-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .krc-post-list-item-title',
            ]
        );

        $this->add_responsive_control(
            'title_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-item-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-item-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_content',
            [
                'label' => esc_html__( 'Content', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-list-item-content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_align',
            [
                'label'     => esc_html__( 'Alignment', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__( 'Justified', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-list-item-content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'content_typography',
                'selector' => '{{WRAPPER}} .krc-post-list-item-content',
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-item-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-item-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_gallery',
            [
                'label' => esc_html__( 'Gallery', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'gallery_gap',
            [
                'label'      => esc_html__( 'Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-item-gallery' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'gallery_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-item-gallery' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'gallery_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-item-gallery' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'gallery_image_height',
            [
                'label'      => esc_html__( 'Image Height', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-image'     => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .krc-post-list-image img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'gallery_image_fit',
            [
                'label'     => esc_html__( 'Image Fit', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'cover',
                'options'   => [
                    'cover'   => esc_html__( 'Cover', 'karice-elements' ),
                    'contain' => esc_html__( 'Contain', 'karice-elements' ),
                    'fill'    => esc_html__( 'Fill', 'karice-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-list-image img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'gallery_image_radius',
            [
                'label'      => esc_html__( 'Image Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-list-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $items    = $this->resolve_items( $settings );

        if ( empty( $items ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="krc-post-gallery-empty">' . esc_html__( 'No list items found for selected source.', 'karice-elements' ) . '</div>';
            }
            return;
        }

        ?>
        <div class="krc-post-list-widget">
            <?php foreach ( $items as $item ) : ?>
                <article class="krc-post-list-item">
                    <div class="krc-post-list-item-text">
                        <h3 class="krc-post-list-item-title"><?php echo esc_html( $item['title'] ); ?></h3>
                        <div class="krc-post-list-item-content">
                            <?php
                            if ( ! empty( $item['content_is_template'] ) ) {
                                echo $item['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            } else {
                                echo wp_kses_post( $item['content'] );
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    $image_count = is_array( $item['images'] ) ? count( $item['images'] ) : 0;
                    $gallery_classes = 'krc-post-list-item-gallery';
                    if ( 1 === $image_count ) {
                        $gallery_classes .= ' krc-post-list-item-gallery-single';
                    }
                    ?>
                    <div class="<?php echo esc_attr( $gallery_classes ); ?>">
                        <?php if ( ! empty( $item['images'] ) ) : ?>
                            <?php foreach ( $item['images'] as $image_url ) : ?>
                                <figure class="krc-post-list-image">
                                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>">
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function resolve_items( array $settings ): array {
        $source = (string) ( $settings['source'] ?? 'items' );

        if ( 'post' === $source ) {
            return $this->build_from_posts( $settings );
        }

        if ( 'taxonomy' === $source ) {
            return $this->build_from_taxonomy( $settings );
        }

        return $this->build_from_items( $settings );
    }

    private function build_from_items( array $settings ): array {
        $rows  = isset( $settings['items'] ) && is_array( $settings['items'] ) ? $settings['items'] : [];
        $items = [];

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row['title'] ) ? trim( (string) $row['title'] ) : '';
            if ( '' === $title ) {
                continue;
            }

            $gallery = isset( $row['gallery'] ) && is_array( $row['gallery'] ) ? $row['gallery'] : [];
            $items[] = [
                'title'               => $title,
                'content'             => $this->format_text_content( $row['content'] ?? '' ),
                'content_is_template' => false,
                'images'              => $this->extract_gallery_images_from_elementor_gallery( $gallery ),
            ];
        }

        return $items;
    }

    private function build_from_posts( array $settings ): array {
        $post_type           = sanitize_key( (string) ( $settings['post_type'] ?? 'post' ) );
        $posts_per_page      = max( 1, (int) ( $settings['posts_per_page'] ?? 8 ) );
        $title_map           = (string) ( $settings['post_title_map'] ?? 'default' );
        $title_acf_field     = sanitize_key( (string) ( $settings['post_title_acf_field'] ?? 'title' ) );
        $content_map         = (string) ( $settings['post_content_map'] ?? 'excerpt' );
        $content_source      = (string) ( $settings['post_content_source_option'] ?? 'default' );
        $content_template_id = absint( $settings['post_content_template_id'] ?? 0 );
        $content_acf_field   = sanitize_key( (string) ( $settings['post_content_acf_field'] ?? 'content' ) );
        $excerpt_words       = max( 5, (int) ( $settings['excerpt_words'] ?? 26 ) );
        $gallery_field       = sanitize_key( (string) ( $settings['post_gallery_field'] ?? 'gallery' ) );
        $gallery_image_field = sanitize_key( (string) ( $settings['post_gallery_image_field'] ?? 'image' ) );
        $item_repeater_field = sanitize_key( (string) ( $settings['post_item_repeater_field'] ?? '' ) );
        $item_title_field    = sanitize_key( (string) ( $settings['post_item_repeater_title_field'] ?? 'title' ) );
        $item_content_field  = sanitize_key( (string) ( $settings['post_item_repeater_content_field'] ?? 'content' ) );
        $item_image_field    = sanitize_key( (string) ( $settings['post_item_repeater_gallery_image_field'] ?? 'image' ) );
        $include_current_tax = 'yes' === (string) ( $settings['post_include_current_taxonomy'] ?? '' );
        $items               = [];

        if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
            return $items;
        }

        $query_args = [
            'post_type'           => $post_type,
            'post_status'         => 'publish',
            'posts_per_page'      => $posts_per_page,
            'ignore_sticky_posts' => true,
        ];

        if ( $include_current_tax ) {
            $queried_term = get_queried_object();

            if ( $queried_term instanceof \WP_Term && taxonomy_exists( $queried_term->taxonomy ) ) {
                $query_args['tax_query'] = [
                    [
                        'taxonomy'         => $queried_term->taxonomy,
                        'field'            => 'term_id',
                        'terms'            => [ (int) $queried_term->term_id ],
                        'include_children' => true,
                    ],
                ];
            }
        }

        $query = new \WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            return $items;
        }

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = (int) get_the_ID();
            if ( $post_id <= 0 ) {
                continue;
            }

            $title   = $this->get_post_mapped_title( $post_id, $title_map, $title_acf_field );
            $content = $this->get_post_mapped_content( $post_id, $content_map, $content_acf_field, $excerpt_words );
            $content_is_template = false;

            if ( 'template' === $content_source && $content_template_id > 0 ) {
                $template_content = $this->get_elementor_template_content( $content_template_id, $post_id );

                if ( '' !== trim( $template_content ) ) {
                    $content             = $template_content;
                    $content_is_template = true;
                } else {
                    $content = '';
                }
            }

            if ( '' === trim( $title ) ) {
                continue;
            }

            if ( function_exists( 'get_field' ) && '' !== $item_repeater_field ) {
                $item_rows      = get_field( $item_repeater_field, $post_id );
                $repeater_items = $this->map_items_from_repeater_rows( $item_rows, $item_title_field, $item_content_field, $item_image_field );

                if ( ! empty( $repeater_items ) ) {
                    $items = array_merge( $items, $repeater_items );
                    continue;
                }
            }

            $images = [];
            if ( function_exists( 'get_field' ) && '' !== $gallery_field ) {
                $gallery_rows = get_field( $gallery_field, $post_id );
                $images       = $this->extract_gallery_images( $gallery_rows, $gallery_image_field );
            }

            $items[] = [
                'title'               => $title,
                'content'             => $content,
                'content_is_template' => $content_is_template,
                'images'              => $images,
            ];
        }

        wp_reset_postdata();

        return $items;
    }

    private function build_from_taxonomy( array $settings ): array {
        $taxonomy            = sanitize_key( (string) ( $settings['taxonomy'] ?? '' ) );
        $use_current_term    = 'current_term' === $taxonomy;
        $limit               = max( 1, (int) ( $settings['terms_limit'] ?? 8 ) );
        $hide_empty          = 'yes' === (string) ( $settings['hide_empty'] ?? 'yes' );
        $item_content_source = (string) ( $settings['taxonomy_item_content_source'] ?? 'default' );
        $title_map           = (string) ( $settings['taxonomy_title_map'] ?? 'default' );
        $title_acf_field     = sanitize_key( (string) ( $settings['taxonomy_title_acf_field'] ?? 'title' ) );
        $content_map         = (string) ( $settings['taxonomy_content_map'] ?? 'default' );
        $content_acf_field   = sanitize_key( (string) ( $settings['taxonomy_content_acf_field'] ?? 'content' ) );
        $gallery_field       = sanitize_key( (string) ( $settings['taxonomy_gallery_field'] ?? 'gallery' ) );
        $gallery_image_field = sanitize_key( (string) ( $settings['taxonomy_gallery_image_field'] ?? 'image' ) );
        $item_repeater_field = sanitize_key( (string) ( $settings['taxonomy_item_repeater_field'] ?? '' ) );
        $item_title_field    = sanitize_key( (string) ( $settings['taxonomy_item_repeater_title_field'] ?? 'title' ) );
        $item_content_field  = sanitize_key( (string) ( $settings['taxonomy_item_repeater_content_field'] ?? 'content' ) );
        $item_image_field    = sanitize_key( (string) ( $settings['taxonomy_item_repeater_gallery_image_field'] ?? 'image' ) );
        $items               = [];

        if ( ! $use_current_term && ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) ) {
            return $items;
        }

        if ( $use_current_term ) {
            $queried_term = get_queried_object();

            if ( ! ( $queried_term instanceof \WP_Term ) || ! taxonomy_exists( $queried_term->taxonomy ) ) {
                return $items;
            }

            if ( $hide_empty && (int) $queried_term->count < 1 ) {
                return $items;
            }

            $terms = [ $queried_term ];
        } else {
            $terms = get_terms(
                [
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => $hide_empty,
                    'number'     => $limit,
                ]
            );
        }

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return $items;
        }

        foreach ( $terms as $term ) {
            if ( ! ( $term instanceof \WP_Term ) ) {
                continue;
            }

            if ( 'repeater' === $item_content_source ) {
                if ( ! function_exists( 'get_field' ) || '' === $item_repeater_field ) {
                    continue;
                }

                $item_rows      = get_field( $item_repeater_field, $term );
                $repeater_items = $this->map_items_from_repeater_rows( $item_rows, $item_title_field, $item_content_field, $item_image_field );

                if ( ! empty( $repeater_items ) ) {
                    $items = array_merge( $items, $repeater_items );
                }
                continue;
            }

            $title   = $this->get_term_mapped_title( $term, $title_map, $title_acf_field );
            $content = $this->get_term_mapped_content( $term, $content_map, $content_acf_field );

            if ( '' === trim( $title ) ) {
                continue;
            }

            $images = [];
            if ( function_exists( 'get_field' ) && '' !== $gallery_field ) {
                $gallery_rows = get_field( $gallery_field, $term );
                $images       = $this->extract_gallery_images( $gallery_rows, $gallery_image_field );
            }

            $items[] = [
                'title'               => $title,
                'content'             => $content,
                'content_is_template' => false,
                'images'              => $images,
            ];
        }

        return $items;
    }

    private function get_post_mapped_title( int $post_id, string $map, string $acf_field ): string {
        if ( 'acf' === $map && function_exists( 'get_field' ) && '' !== $acf_field ) {
            $value = get_field( $acf_field, $post_id );
            if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
                return (string) $value;
            }
        }

        return (string) get_the_title( $post_id );
    }

    private function get_post_mapped_content( int $post_id, string $map, string $acf_field, int $excerpt_words ): string {
        if ( 'acf' === $map && function_exists( 'get_field' ) && '' !== $acf_field ) {
            return $this->format_text_content( get_field( $acf_field, $post_id ) );
        }

        if ( 'content' === $map ) {
            return apply_filters( 'the_content', (string) get_post_field( 'post_content', $post_id ) );
        }

        $excerpt = get_the_excerpt( $post_id );
        if ( '' === trim( $excerpt ) ) {
            $excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
        }

        return '<p>' . esc_html( wp_trim_words( $excerpt, $excerpt_words ) ) . '</p>';
    }

    private function get_term_mapped_title( \WP_Term $term, string $map, string $acf_field ): string {
        if ( 'acf' === $map && function_exists( 'get_field' ) && '' !== $acf_field ) {
            $value = get_field( $acf_field, $term );
            if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
                return (string) $value;
            }
        }

        return (string) $term->name;
    }

    private function get_term_mapped_content( \WP_Term $term, string $map, string $acf_field ): string {
        if ( 'acf' === $map && function_exists( 'get_field' ) && '' !== $acf_field ) {
            return $this->format_text_content( get_field( $acf_field, $term ) );
        }

        $description = (string) $term->description;
        if ( '' === trim( wp_strip_all_tags( $description ) ) ) {
            $description = sprintf(
                '<p>%1$s: %2$d</p>',
                esc_html__( 'Posts', 'karice-elements' ),
                (int) $term->count
            );
        } else {
            $description = wpautop( wp_kses_post( $description ) );
        }

        return $description;
    }

    private function map_items_from_repeater_rows( $rows, string $title_field, string $content_field, string $image_field ): array {
        if ( ! is_array( $rows ) ) {
            return [];
        }

        $items = [];

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row[ $title_field ] ) ? trim( (string) $row[ $title_field ] ) : '';
            if ( '' === $title ) {
                continue;
            }

            $gallery_rows = $this->resolve_repeater_gallery_rows( $row );
            $items[]      = [
                'title'               => $title,
                'content'             => $this->format_text_content( $row[ $content_field ] ?? '' ),
                'content_is_template' => false,
                'images'              => $this->extract_gallery_images( $gallery_rows, $image_field ),
            ];
        }

        return $items;
    }

    private function resolve_repeater_gallery_rows( array $row ) {
        if ( array_key_exists( 'gallery', $row ) ) {
            return $row['gallery'];
        }

        if ( array_key_exists( 'images', $row ) ) {
            return $row['images'];
        }

        if ( array_key_exists( 'image', $row ) ) {
            return $row['image'];
        }

        foreach ( $row as $key => $value ) {
            if ( ! is_string( $key ) ) {
                continue;
            }

            if ( false !== strpos( $key, 'gallery' ) || false !== strpos( $key, 'image' ) ) {
                return $value;
            }
        }

        return [];
    }

    private function extract_gallery_images_from_elementor_gallery( array $gallery ): array {
        $images = [];

        foreach ( $gallery as $image ) {
            if ( ! is_array( $image ) ) {
                continue;
            }

            $url = '';
            if ( isset( $image['url'] ) ) {
                $url = esc_url_raw( (string) $image['url'] );
            } elseif ( isset( $image['id'] ) ) {
                $url = $this->normalize_image_url( $image['id'] );
            }

            if ( '' === $url ) {
                continue;
            }

            $images[] = $url;
            if ( count( $images ) >= 2 ) {
                break;
            }
        }

        return $images;
    }

    private function extract_gallery_images( $gallery_rows, string $image_field ): array {
        $images = [];

        if ( empty( $gallery_rows ) ) {
            return $images;
        }

        if ( is_array( $gallery_rows ) ) {
            foreach ( $gallery_rows as $row ) {
                $image = $this->resolve_image_url_from_row( $row, $image_field );
                if ( '' === $image ) {
                    continue;
                }

                $images[] = $image;
                if ( count( $images ) >= 2 ) {
                    break;
                }
            }
        }

        return $images;
    }

    private function resolve_image_url_from_row( $row, string $image_field ): string {
        if ( is_numeric( $row ) ) {
            return $this->normalize_image_url( $row );
        }

        if ( is_object( $row ) ) {
            return $this->normalize_image_url( $row );
        }

        if ( is_string( $row ) ) {
            return esc_url_raw( $row );
        }

        if ( is_array( $row ) ) {
            if ( isset( $row[ $image_field ] ) ) {
                return $this->normalize_image_url( $row[ $image_field ] );
            }

            if ( isset( $row['url'] ) ) {
                return esc_url_raw( (string) $row['url'] );
            }

            if ( isset( $row['ID'] ) ) {
                return $this->normalize_image_url( $row['ID'] );
            }
        }

        return '';
    }

    private function normalize_image_url( $image ): string {
        if ( is_object( $image ) ) {
            if ( isset( $image->url ) ) {
                return esc_url_raw( (string) $image->url );
            }

            if ( isset( $image->ID ) ) {
                $url = wp_get_attachment_image_url( (int) $image->ID, 'large' );
                return $url ? esc_url_raw( $url ) : '';
            }

            if ( isset( $image->id ) ) {
                $url = wp_get_attachment_image_url( (int) $image->id, 'large' );
                return $url ? esc_url_raw( $url ) : '';
            }
        }

        if ( is_array( $image ) ) {
            if ( isset( $image['url'] ) ) {
                return esc_url_raw( (string) $image['url'] );
            }

            if ( isset( $image['ID'] ) ) {
                $url = wp_get_attachment_image_url( (int) $image['ID'], 'large' );
                return $url ? esc_url_raw( $url ) : '';
            }

            if ( isset( $image['id'] ) ) {
                $url = wp_get_attachment_image_url( (int) $image['id'], 'large' );
                return $url ? esc_url_raw( $url ) : '';
            }
        }

        if ( is_numeric( $image ) ) {
            $url = wp_get_attachment_image_url( (int) $image, 'large' );
            return $url ? esc_url_raw( $url ) : '';
        }

        if ( is_string( $image ) ) {
            return esc_url_raw( $image );
        }

        return '';
    }

    private function format_text_content( $content_raw ): string {
        if ( is_string( $content_raw ) ) {
            return wpautop( wp_kses_post( $content_raw ) );
        }

        if ( is_scalar( $content_raw ) ) {
            return '<p>' . esc_html( (string) $content_raw ) . '</p>';
        }

        return '';
    }

    private function get_elementor_template_content( int $template_id, int $post_id ): string {
        if ( $template_id <= 0 || ! class_exists( '\Elementor\Plugin' ) ) {
            return '';
        }

        $template_post = get_post( $template_id );
        if ( ! $template_post instanceof \WP_Post || 'publish' !== $template_post->post_status ) {
            return '';
        }

        $frontend = \Elementor\Plugin::$instance->frontend ?? null;
        if ( ! $frontend || ! method_exists( $frontend, 'get_builder_content_for_display' ) ) {
            return '';
        }

        global $post;

        $previous_post = $post instanceof \WP_Post ? $post : null;
        $target_post   = get_post( $post_id );
        $target_post   = $target_post instanceof \WP_Post ? $target_post : null;

        if ( $target_post ) {
            $post = $target_post;
            setup_postdata( $post );
        }

        $content = $frontend->get_builder_content_for_display( $template_id, true );

        if ( $target_post ) {
            $post = $previous_post;

            if ( $post instanceof \WP_Post ) {
                setup_postdata( $post );
            }
        }

        return is_string( $content ) ? $content : '';
    }

    private function get_elementor_template_options(): array {
        $options = [];

        if ( ! post_type_exists( 'elementor_library' ) ) {
            return $options;
        }

        $templates = get_posts(
            [
                'post_type'      => 'elementor_library',
                'post_status'    => 'publish',
                'posts_per_page' => 500,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'fields'         => 'ids',
            ]
        );

        foreach ( $templates as $template_id ) {
            $template_id = absint( $template_id );
            if ( $template_id <= 0 ) {
                continue;
            }

            $title = get_the_title( $template_id );
            if ( '' === trim( $title ) ) {
                $title = '#' . $template_id;
            }

            $options[ (string) $template_id ] = $title . ' (' . $template_id . ')';
        }

        return $options;
    }

    private function get_post_type_options(): array {
        $options    = [];
        $post_types = get_post_types(
            [
                'public'  => true,
                'show_ui' => true,
            ],
            'objects'
        );

        foreach ( $post_types as $post_type ) {
            if ( empty( $post_type->name ) ) {
                continue;
            }

            $options[ $post_type->name ] = ! empty( $post_type->label ) ? $post_type->label : $post_type->name;
        }

        asort( $options, SORT_NATURAL | SORT_FLAG_CASE );
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
}
