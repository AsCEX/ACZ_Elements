<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KC_Karice_Post_Tabs_Widget extends \Elementor\Widget_Base {
    public function get_name(): string {
        return 'karice_post_tabs';
    }

    public function get_title(): string {
        return esc_html__( 'Karice Post Tabs', 'karice-elements' );
    }

    public function get_icon(): string {
        return 'eicon-tabs';
    }

    public function get_categories(): array {
        return [ 'karice' ];
    }

    public function get_keywords(): array {
        return [ 'karice', 'tabs', 'post', 'taxonomy', 'acf' ];
    }

    public function get_style_depends(): array {
        return [ 'karice-post-gallery' ];
    }

    public function get_script_depends(): array {
        return [ 'cec-widget' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_style_controls();
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
            'source',
            [
                'label'   => esc_html__( 'Source', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'items',
                'options' => [
                    'items'         => esc_html__( 'Items', 'karice-elements' ),
                    'post'          => esc_html__( 'Post', 'karice-elements' ),
                    'taxonomy'      => esc_html__( 'Taxonomy', 'karice-elements' ),
                    'current_post'  => esc_html__( 'Current Post', 'karice-elements' ),
                    'current_taxonomy' => esc_html__( 'Current Taxonomy', 'karice-elements' ),
                    'theme_options' => esc_html__( 'Theme Options (ACF Repeater)', 'karice-elements' ),
                ],
            ]
        );

        $this->add_control(
            'tab_direction',
            [
                'label'   => esc_html__( 'Tab Direction', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'up',
                'toggle'  => false,
                'options' => [
                    'up'    => [
                        'title' => esc_html__( 'Up', 'karice-elements' ),
                        'icon'  => 'eicon-arrow-up',
                    ],
                    'down'  => [
                        'title' => esc_html__( 'Down', 'karice-elements' ),
                        'icon'  => 'eicon-arrow-down',
                    ],
                    'left'  => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
            ]
        );

        $items_repeater = new \Elementor\Repeater();
        $items_repeater->add_control(
            'title',
            [
                'label'       => esc_html__( 'Title', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'Tab Title', 'karice-elements' ),
                'label_block' => true,
            ]
        );
        $items_repeater->add_control(
            'content',
            [
                'label'   => esc_html__( 'Content', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__( 'Tab content', 'karice-elements' ),
            ]
        );

        $this->add_control(
            'items',
            [
                'label'       => esc_html__( 'Items', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $items_repeater->get_controls(),
                'title_field' => '{{{ title }}}',
                'default'     => [
                    [
                        'title'   => esc_html__( 'Tab One', 'karice-elements' ),
                        'content' => esc_html__( 'Tab One content', 'karice-elements' ),
                    ],
                    [
                        'title'   => esc_html__( 'Tab Two', 'karice-elements' ),
                        'content' => esc_html__( 'Tab Two content', 'karice-elements' ),
                    ],
                ],
                'condition'   => [
                    'source' => 'items',
                ],
            ]
        );

        $this->add_control(
            'post_type',
            [
                'label'       => esc_html__( 'Post Type', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $this->get_post_type_options(),
                'default'     => 'post',
                'label_block' => true,
                'condition'   => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'     => esc_html__( 'Posts Limit', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 5,
                'min'       => 1,
                'max'       => 50,
                'condition' => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'post_content_source',
            [
                'label'     => esc_html__( 'Post Content', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'excerpt',
                'options'   => [
                    'excerpt' => esc_html__( 'Excerpt', 'karice-elements' ),
                    'content' => esc_html__( 'Full Content', 'karice-elements' ),
                ],
                'condition' => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'excerpt_words',
            [
                'label'     => esc_html__( 'Excerpt Words', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 25,
                'min'       => 5,
                'max'       => 200,
                'condition' => [
                    'source'              => 'post',
                    'post_content_source' => 'excerpt',
                ],
            ]
        );

        $this->add_control(
            'taxonomy',
            [
                'label'       => esc_html__( 'Taxonomy', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $this->get_taxonomy_options(),
                'label_block' => true,
                'condition'   => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $this->add_control(
            'terms_limit',
            [
                'label'     => esc_html__( 'Terms Limit', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 8,
                'min'       => 1,
                'max'       => 100,
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
            'acf_repeater_field',
            [
                'label'       => esc_html__( 'ACF Repeater Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'custom_lighting',
                'label_block' => true,
                'description' => esc_html__( 'ACF repeater field name from Theme Options, Current Post, or Current Taxonomy.', 'karice-elements' ),
                'condition'   => [
                    'source' => [ 'theme_options', 'current_post', 'current_taxonomy' ],
                ],
            ]
        );

        $this->add_control(
            'acf_title_field',
            [
                'label'       => esc_html__( 'Repeater Title Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'title',
                'label_block' => true,
                'condition'   => [
                    'source' => [ 'theme_options', 'current_post', 'current_taxonomy' ],
                ],
            ]
        );

        $this->add_control(
            'acf_content_field',
            [
                'label'       => esc_html__( 'Repeater Content Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'description',
                'label_block' => true,
                'condition'   => [
                    'source' => [ 'theme_options', 'current_post', 'current_taxonomy' ],
                ],
            ]
        );

        $this->add_control(
            'acf_image_field',
            [
                'label'       => esc_html__( 'Repeater Image Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'image',
                'label_block' => true,
                'condition'   => [
                    'source' => [ 'theme_options', 'current_post', 'current_taxonomy' ],
                ],
            ]
        );

        $this->add_control(
            'acf_icon_field',
            [
                'label'       => esc_html__( 'Repeater Icon Class Field', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'icon',
                'label_block' => true,
                'description' => esc_html__( 'Use Font Awesome class value (e.g. fa-solid fa-house).', 'karice-elements' ),
                'condition'   => [
                    'source' => [ 'theme_options', 'current_post', 'current_taxonomy' ],
                ],
            ]
        );

        $this->add_control(
            'panel_image_position',
            [
                'label'   => esc_html__( 'Panel Image Position', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'up',
                'toggle'  => false,
                'options' => [
                    'up'    => [
                        'title' => esc_html__( 'Up', 'karice-elements' ),
                        'icon'  => 'eicon-arrow-up',
                    ],
                    'down'  => [
                        'title' => esc_html__( 'Down', 'karice-elements' ),
                        'icon'  => 'eicon-arrow-down',
                    ],
                    'left'  => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_tabs',
            [
                'label' => esc_html__( 'Tabs', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tab_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_bg_color',
            [
                'label'     => esc_html__( 'Background', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_active_text_color',
            [
                'label'     => esc_html__( 'Active Text Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-button.is-active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_active_bg_color',
            [
                'label'     => esc_html__( 'Active Background', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-button.is-active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_icon_color',
            [
                'label'     => esc_html__( 'Icon Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-icon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_icon_hover_color',
            [
                'label'     => esc_html__( 'Icon Hover Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-button:hover .krc-post-tabs-icon, {{WRAPPER}} .krc-post-tabs-button:focus .krc-post-tabs-icon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_icon_active_color',
            [
                'label'     => esc_html__( 'Icon Active Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-button.is-active .krc-post-tabs-icon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tab_icon_size',
            [
                'label'      => esc_html__( 'Icon Size', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tab_icon_gap',
            [
                'label'      => esc_html__( 'Icon Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_gap',
            [
                'label'      => esc_html__( 'Tabs Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-nav' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_panel_gap',
            [
                'label'      => esc_html__( 'Tabs / Panel Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs' => '--krc-tabs-panel-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_text_align',
            [
                'label'   => esc_html__( 'Text Alignment', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left'    => [
                        'title' => esc_html__( 'Left', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'  => [
                        'title' => esc_html__( 'Center', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'   => [
                        'title' => esc_html__( 'Right', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__( 'Justify', 'karice-elements' ),
                        'icon'  => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-button' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'tab_typography',
                'selector' => '{{WRAPPER}} .krc-post-tabs-button',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'tab_border',
                'selector' => '{{WRAPPER}} .krc-post-tabs-button',
            ]
        );

        $this->add_responsive_control(
            'tab_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_panel',
            [
                'label' => esc_html__( 'Panel', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'panel_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-panel' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'panel_bg_color',
            [
                'label'     => esc_html__( 'Background', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-panel' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'panel_typography',
                'selector' => '{{WRAPPER}} .krc-post-tabs-panel',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'panel_border',
                'selector' => '{{WRAPPER}} .krc-post-tabs-panel',
            ]
        );

        $this->add_responsive_control(
            'panel_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'panel_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-panel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'panel_image_width',
            [
                'label'      => esc_html__( 'Image Width', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 40,
                        'max' => 1000,
                    ],
                    '%'  => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-panel-media' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'panel_inner_gap',
            [
                'label'      => esc_html__( 'Panel Inner Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-panel-inner' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_panel_title',
            [
                'label' => esc_html__( 'Panel Title', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'panel_title_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-post-tabs-panel-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'panel_title_typography',
                'selector' => '{{WRAPPER}} .krc-post-tabs-panel-title',
            ]
        );

        $this->add_responsive_control(
            'panel_title_alignment',
            [
                'label'   => esc_html__( 'Alignment', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
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
                    '{{WRAPPER}} .krc-post-tabs-panel-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'panel_title_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-post-tabs-panel-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $tabs     = $this->resolve_tabs_data( $settings );

        if ( empty( $tabs ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="krc-post-gallery-empty">' . esc_html__( 'No tab items found for selected source.', 'karice-elements' ) . '</div>';
                $debug_message = $this->build_empty_state_debug_message( $settings );
                if ( '' !== $debug_message ) {
                    echo '<pre class="krc-post-gallery-empty">' . esc_html( $debug_message ) . '</pre>';
                }
            }
            return;
        }

        $widget_id = 'krc-post-tabs-' . esc_attr( $this->get_id() );
        $direction = sanitize_key( (string) ( $settings['tab_direction'] ?? 'up' ) );
        $image_pos = sanitize_key( (string) ( $settings['panel_image_position'] ?? 'up' ) );

        if ( ! in_array( $direction, [ 'up', 'down', 'left', 'right' ], true ) ) {
            $direction = 'up';
        }
        if ( ! in_array( $image_pos, [ 'up', 'down', 'left', 'right' ], true ) ) {
            $image_pos = 'up';
        }
        ?>
        <div class="krc-post-tabs krc-post-tabs--dir-<?php echo esc_attr( $direction ); ?>" id="<?php echo esc_attr( $widget_id ); ?>">
            <div class="krc-post-tabs-nav" role="tablist">
                <?php foreach ( $tabs as $index => $tab ) : ?>
                    <?php
                    $tab_button_id = $widget_id . '-tab-' . $index;
                    $panel_id      = $widget_id . '-panel-' . $index;
                    $is_active     = 0 === $index;
                    ?>
                    <button
                        type="button"
                        id="<?php echo esc_attr( $tab_button_id ); ?>"
                        class="krc-post-tabs-button<?php echo $is_active ? ' is-active' : ''; ?>"
                        role="tab"
                        data-target="<?php echo esc_attr( $panel_id ); ?>"
                        aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                        aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                        tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
                    >
                        <?php if ( ! empty( $tab['icon'] ) ) : ?>
                            <i class="krc-post-tabs-icon <?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></i>
                        <?php endif; ?>
                        <span class="krc-post-tabs-label"><?php echo esc_html( $tab['title'] ); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="krc-post-tabs-panels">
                <?php foreach ( $tabs as $index => $tab ) : ?>
                    <?php
                    $panel_id      = $widget_id . '-panel-' . $index;
                    $tab_button_id = $widget_id . '-tab-' . $index;
                    $is_active     = 0 === $index;
                    $has_image     = ! empty( $tab['image'] );
                    $inner_class   = 'krc-post-tabs-panel-inner';
                    if ( $has_image ) {
                        $inner_class .= ' krc-post-tabs-panel-inner--img-' . $image_pos;
                    }
                    ?>
                    <div
                        id="<?php echo esc_attr( $panel_id ); ?>"
                        class="krc-post-tabs-panel<?php echo $is_active ? ' is-active' : ''; ?>"
                        role="tabpanel"
                        aria-labelledby="<?php echo esc_attr( $tab_button_id ); ?>"
                        <?php echo $is_active ? '' : 'hidden'; ?>
                    >
                        <div class="<?php echo esc_attr( $inner_class ); ?>">
                            <?php if ( $has_image ) : ?>
                                <div class="krc-post-tabs-panel-media">
                                    <img src="<?php echo esc_url( $tab['image'] ); ?>" alt="<?php echo esc_attr( $tab['title'] ); ?>" />
                                </div>
                            <?php endif; ?>
                            <div class="krc-post-tabs-panel-main">
                                <h3 class="krc-post-tabs-panel-title"><?php echo esc_html( $tab['title'] ); ?></h3>
                                <div class="krc-post-tabs-panel-content">
                                    <?php echo wp_kses_post( $tab['content'] ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private function resolve_tabs_data( array $settings ): array {
        $source = (string) ( $settings['source'] ?? 'items' );

        if ( 'post' === $source ) {
            return $this->build_tabs_from_posts( $settings );
        }

        if ( 'taxonomy' === $source ) {
            return $this->build_tabs_from_taxonomy( $settings );
        }

        if ( 'current_post' === $source ) {
            return $this->build_tabs_from_current_post( $settings );
        }

        if ( 'current_taxonomy' === $source ) {
            return $this->build_tabs_from_current_taxonomy( $settings );
        }

        if ( 'theme_options' === $source ) {
            return $this->build_tabs_from_theme_options( $settings );
        }

        return $this->build_tabs_from_items( $settings );
    }

    private function build_tabs_from_items( array $settings ): array {
        $items = isset( $settings['items'] ) && is_array( $settings['items'] ) ? $settings['items'] : [];
        $tabs  = [];

        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $title   = trim( (string) ( $item['title'] ?? '' ) );
            $content = (string) ( $item['content'] ?? '' );

            if ( '' === $title && '' === trim( wp_strip_all_tags( $content ) ) ) {
                continue;
            }

            if ( '' === $title ) {
                $title = esc_html__( 'Tab', 'karice-elements' );
            }

            $tabs[] = [
                'title'   => $title,
                'content' => $content,
                'icon'    => '',
                'image'   => '',
            ];
        }

        return $tabs;
    }

    private function build_tabs_from_posts( array $settings ): array {
        $post_type        = sanitize_key( (string) ( $settings['post_type'] ?? 'post' ) );
        $posts_per_page   = max( 1, (int) ( $settings['posts_per_page'] ?? 5 ) );
        $content_source   = (string) ( $settings['post_content_source'] ?? 'excerpt' );
        $excerpt_words    = max( 5, (int) ( $settings['excerpt_words'] ?? 25 ) );
        $tabs             = [];

        if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
            return $tabs;
        }

        $query = new \WP_Query(
            [
                'post_type'           => $post_type,
                'post_status'         => 'publish',
                'posts_per_page'      => $posts_per_page,
                'ignore_sticky_posts' => true,
            ]
        );

        if ( ! $query->have_posts() ) {
            return $tabs;
        }

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            if ( ! $post_id ) {
                continue;
            }

            if ( 'content' === $content_source ) {
                $content = apply_filters( 'the_content', (string) get_post_field( 'post_content', $post_id ) );
            } else {
                $excerpt = get_the_excerpt( $post_id );
                if ( '' === trim( $excerpt ) ) {
                    $excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
                }
                $content = '<p>' . esc_html( wp_trim_words( $excerpt, $excerpt_words ) ) . '</p>';
            }

            $tabs[] = [
                'title'   => get_the_title( $post_id ),
                'content' => $content,
                'icon'    => '',
                'image'   => '',
            ];
        }

        wp_reset_postdata();
        return $tabs;
    }

    private function build_tabs_from_taxonomy( array $settings ): array {
        $taxonomy   = sanitize_key( (string) ( $settings['taxonomy'] ?? '' ) );
        $limit      = max( 1, (int) ( $settings['terms_limit'] ?? 8 ) );
        $hide_empty = 'yes' === (string) ( $settings['hide_empty'] ?? 'yes' );
        $tabs       = [];

        if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
            return $tabs;
        }

        $terms = get_terms(
            [
                'taxonomy'   => $taxonomy,
                'hide_empty' => $hide_empty,
                'number'     => $limit,
            ]
        );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return $tabs;
        }

        foreach ( $terms as $term ) {
            if ( ! $term instanceof \WP_Term ) {
                continue;
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

            $tabs[] = [
                'title'   => (string) $term->name,
                'content' => $description,
                'icon'    => '',
                'image'   => '',
            ];
        }

        return $tabs;
    }

    private function build_tabs_from_theme_options( array $settings ): array {
        $repeater_field = sanitize_key( (string) ( $settings['acf_repeater_field'] ?? 'custom_lighting' ) );
        $title_field    = sanitize_key( (string) ( $settings['acf_title_field'] ?? 'title' ) );
        $content_field  = sanitize_key( (string) ( $settings['acf_content_field'] ?? 'description' ) );
        $image_field    = sanitize_key( (string) ( $settings['acf_image_field'] ?? 'image' ) );
        $icon_field     = sanitize_key( (string) ( $settings['acf_icon_field'] ?? 'icon' ) );
        $tabs           = [];

        if ( '' === $repeater_field || ! function_exists( 'get_field' ) ) {
            return $tabs;
        }

        $rows = get_field( $repeater_field, 'option' );
        if ( ! is_array( $rows ) ) {
            return $tabs;
        }

        foreach ( $rows as $index => $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row[ $title_field ] ) ? trim( (string) $row[ $title_field ] ) : '';
            if ( '' === $title ) {
                $title = sprintf( esc_html__( 'Tab %d', 'karice-elements' ), (int) $index + 1 );
            }

            $content_raw = isset( $row[ $content_field ] ) ? $row[ $content_field ] : '';
            $content     = '';

            if ( is_string( $content_raw ) ) {
                $content = wpautop( wp_kses_post( $content_raw ) );
            } elseif ( is_scalar( $content_raw ) ) {
                $content = '<p>' . esc_html( (string) $content_raw ) . '</p>';
            }

            $tabs[] = [
                'title'   => $title,
                'content' => $content,
                'icon'    => $this->sanitize_icon_class( isset( $row[ $icon_field ] ) ? (string) $row[ $icon_field ] : '' ),
                'image'   => $this->extract_image_url( isset( $row[ $image_field ] ) ? $row[ $image_field ] : '' ),
            ];
        }

        return $tabs;
    }

    private function build_tabs_from_current_post( array $settings ): array {
        $repeater_field = sanitize_key( (string) ( $settings['acf_repeater_field'] ?? 'custom_lighting' ) );
        $title_field    = sanitize_key( (string) ( $settings['acf_title_field'] ?? 'title' ) );
        $content_field  = sanitize_key( (string) ( $settings['acf_content_field'] ?? 'description' ) );
        $image_field    = sanitize_key( (string) ( $settings['acf_image_field'] ?? 'image' ) );
        $icon_field     = sanitize_key( (string) ( $settings['acf_icon_field'] ?? 'icon' ) );
        $tabs           = [];
        $post_id        = get_queried_object_id();

        if ( ! $post_id || '' === $repeater_field || ! function_exists( 'get_field' ) ) {
            return $tabs;
        }

        $rows = get_field( $repeater_field, $post_id );
        if ( ! is_array( $rows ) ) {
            return $tabs;
        }

        foreach ( $rows as $index => $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row[ $title_field ] ) ? trim( (string) $row[ $title_field ] ) : '';
            if ( '' === $title ) {
                $title = sprintf( esc_html__( 'Tab %d', 'karice-elements' ), (int) $index + 1 );
            }

            $content_raw = isset( $row[ $content_field ] ) ? $row[ $content_field ] : '';
            $content     = '';

            if ( is_string( $content_raw ) ) {
                $content = wpautop( wp_kses_post( $content_raw ) );
            } elseif ( is_scalar( $content_raw ) ) {
                $content = '<p>' . esc_html( (string) $content_raw ) . '</p>';
            }

            $tabs[] = [
                'title'   => $title,
                'content' => $content,
                'icon'    => $this->sanitize_icon_class( isset( $row[ $icon_field ] ) ? (string) $row[ $icon_field ] : '' ),
                'image'   => $this->extract_image_url( isset( $row[ $image_field ] ) ? $row[ $image_field ] : '' ),
            ];
        }

        return $tabs;
    }

    private function build_tabs_from_current_taxonomy( array $settings ): array {
        $repeater_field = sanitize_key( (string) ( $settings['acf_repeater_field'] ?? 'custom_lighting' ) );
        $title_field    = sanitize_key( (string) ( $settings['acf_title_field'] ?? 'title' ) );
        $content_field  = sanitize_key( (string) ( $settings['acf_content_field'] ?? 'description' ) );
        $image_field    = sanitize_key( (string) ( $settings['acf_image_field'] ?? 'image' ) );
        $icon_field     = sanitize_key( (string) ( $settings['acf_icon_field'] ?? 'icon' ) );
        $tabs           = [];
        $term           = get_queried_object();

        if ( '' === $repeater_field || ! function_exists( 'get_field' ) || ! ( $term instanceof \WP_Term ) ) {
            return $tabs;
        }

        $rows = $this->get_acf_term_field_value( $repeater_field, $term );
        if ( ! is_array( $rows ) ) {
            return $tabs;
        }

        foreach ( $rows as $index => $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row[ $title_field ] ) ? trim( (string) $row[ $title_field ] ) : '';
            if ( '' === $title ) {
                $title = sprintf( esc_html__( 'Tab %d', 'karice-elements' ), (int) $index + 1 );
            }

            $content_raw = isset( $row[ $content_field ] ) ? $row[ $content_field ] : '';
            $content     = '';

            if ( is_string( $content_raw ) ) {
                $content = wpautop( wp_kses_post( $content_raw ) );
            } elseif ( is_scalar( $content_raw ) ) {
                $content = '<p>' . esc_html( (string) $content_raw ) . '</p>';
            }

            $tabs[] = [
                'title'   => $title,
                'content' => $content,
                'icon'    => $this->sanitize_icon_class( isset( $row[ $icon_field ] ) ? (string) $row[ $icon_field ] : '' ),
                'image'   => $this->extract_image_url( isset( $row[ $image_field ] ) ? $row[ $image_field ] : '' ),
            ];
        }

        return $tabs;
    }

    private function get_acf_term_field_value( string $field_name, \WP_Term $term ) {
        if ( '' === $field_name || ! function_exists( 'get_field' ) ) {
            return null;
        }

        $contexts = [
            $term,
            'term_' . (int) $term->term_id,
            $term->taxonomy . '_' . (int) $term->term_id,
            (int) $term->term_id,
        ];

        foreach ( $contexts as $context ) {
            $value = get_field( $field_name, $context );
            if ( is_array( $value ) ) {
                return $value;
            }
        }

        return null;
    }

    private function build_empty_state_debug_message( array $settings ): string {
        $source = (string) ( $settings['source'] ?? 'items' );
        if ( 'current_taxonomy' !== $source ) {
            return '';
        }

        $repeater_field = sanitize_key( (string) ( $settings['acf_repeater_field'] ?? '' ) );
        $queried_object = get_queried_object();
        $lines          = [
            '[Karice Post Tabs Debug]',
            'source: ' . $source,
            'acf_available: ' . ( function_exists( 'get_field' ) ? 'yes' : 'no' ),
            'acf_repeater_field: ' . ( '' !== $repeater_field ? $repeater_field : '(empty)' ),
            'queried_object_type: ' . ( is_object( $queried_object ) ? get_class( $queried_object ) : gettype( $queried_object ) ),
        ];

        if ( ! ( $queried_object instanceof \WP_Term ) ) {
            $lines[] = 'queried_term: not detected (make sure Elementor Preview is set to a taxonomy archive term)';
            return implode( "\n", $lines );
        }

        $lines[] = 'queried_term_id: ' . (string) (int) $queried_object->term_id;
        $lines[] = 'queried_taxonomy: ' . (string) $queried_object->taxonomy;

        if ( ! function_exists( 'get_field' ) || '' === $repeater_field ) {
            return implode( "\n", $lines );
        }

        $contexts = [
            'WP_Term'                => $queried_object,
            'term_' . (int) $queried_object->term_id => 'term_' . (int) $queried_object->term_id,
            $queried_object->taxonomy . '_' . (int) $queried_object->term_id => $queried_object->taxonomy . '_' . (int) $queried_object->term_id,
            'term_id_int'            => (int) $queried_object->term_id,
        ];

        foreach ( $contexts as $label => $context ) {
            $value = get_field( $repeater_field, $context );
            if ( is_array( $value ) ) {
                $lines[] = 'get_field(' . $repeater_field . ', ' . $label . '): array(' . count( $value ) . ')';
                continue;
            }

            if ( null === $value ) {
                $lines[] = 'get_field(' . $repeater_field . ', ' . $label . '): null';
                continue;
            }

            $lines[] = 'get_field(' . $repeater_field . ', ' . $label . '): ' . gettype( $value );
        }

        return implode( "\n", $lines );
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

    private function extract_image_url( $image_value ): string {
        if ( is_string( $image_value ) ) {
            $url = trim( $image_value );
            return '' !== $url ? esc_url_raw( $url ) : '';
        }

        if ( is_numeric( $image_value ) ) {
            $url = wp_get_attachment_image_url( (int) $image_value, 'large' );
            return is_string( $url ) ? $url : '';
        }

        if ( is_array( $image_value ) ) {
            if ( isset( $image_value['url'] ) && is_string( $image_value['url'] ) ) {
                return esc_url_raw( $image_value['url'] );
            }
            if ( isset( $image_value['ID'] ) && is_numeric( $image_value['ID'] ) ) {
                $url = wp_get_attachment_image_url( (int) $image_value['ID'], 'large' );
                return is_string( $url ) ? $url : '';
            }
            if ( isset( $image_value['id'] ) && is_numeric( $image_value['id'] ) ) {
                $url = wp_get_attachment_image_url( (int) $image_value['id'], 'large' );
                return is_string( $url ) ? $url : '';
            }
        }

        return '';
    }

    private function sanitize_icon_class( string $icon_class ): string {
        $icon_class = trim( $icon_class );
        if ( '' === $icon_class ) {
            return '';
        }

        $icon_class = preg_replace( '/[^A-Za-z0-9\-\_\s]/', '', $icon_class );
        $icon_class = preg_replace( '/\s+/', ' ', (string) $icon_class );

        return trim( (string) $icon_class );
    }
}
