<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Nav_Menu_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'acz-nav-menu';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Nav Menu', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-nav-menu';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'nav', 'menu', 'navigation', 'header', 'links', 'acz' ];
    }

    public function get_style_depends(): array {
        return [ 'acz-common' ];
    }

    public function get_script_depends(): array {
        return [ 'acz-elements-widget' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    private function register_content_controls(): void {
        $this->start_controls_section(
            'section_menu',
            [
                'label' => esc_html__( 'Menu', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'menu',
            [
                'label'       => esc_html__( 'Menu', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'label_block' => true,
                'options'     => $this->get_menu_options(),
                'description' => esc_html__( 'Choose a menu created under Appearance > Menus.', 'acz-elements' ),
            ]
        );

        $this->add_responsive_control(
            'align_items',
            [
                'label'   => esc_html__( 'Alignment', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'flex-start',
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Left', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => esc_html__( 'Right', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                    'space-between' => [
                        'title' => esc_html__( 'Justify', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__list' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_gap',
            [
                'label'      => esc_html__( 'Item Gap', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 120,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__list' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_responsive',
            [
                'label' => esc_html__( 'Responsive', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_responsive',
            [
                'label'        => esc_html__( 'Enable Mobile Toggle', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'responsive_breakpoint',
            [
                'label'     => esc_html__( 'Breakpoint', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'tablet',
                'options'   => [
                    'tablet' => esc_html__( 'Tablet & Mobile', 'acz-elements' ),
                    'mobile' => esc_html__( 'Mobile Only', 'acz-elements' ),
                ],
                'condition' => [
                    'enable_responsive' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'toggle_label',
            [
                'label'       => esc_html__( 'Toggle Label', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'Menu', 'acz-elements' ),
                'label_block' => true,
                'condition'   => [
                    'enable_responsive' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_align',
            [
                'label'     => esc_html__( 'Toggle Alignment', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'default'   => 'flex-end',
                'options'   => [
                    'flex-start' => [
                        'title' => esc_html__( 'Left', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => esc_html__( 'Right', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__toggle-wrap' => 'justify-content: {{VALUE}};',
                ],
                'condition' => [
                    'enable_responsive' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'submenu_toggle_visibility_heading',
            [
                'label'     => esc_html__( 'Submenu Toggle Visibility', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'show_submenu_toggle_desktop',
            [
                'label'        => esc_html__( 'Show on Desktop', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

        $this->add_control(
            'show_submenu_toggle_tablet',
            [
                'label'        => esc_html__( 'Show on Tablet', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_submenu_toggle_mobile',
            [
                'label'        => esc_html__( 'Show on Mobile', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_items',
            [
                'label' => esc_html__( 'Items', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'item_typography',
                'selector' => '{{WRAPPER}} .acz-nav-menu__link',
            ]
        );

        $this->start_controls_tabs( 'item_style_tabs' );

        $this->start_controls_tab(
            'item_style_normal',
            [
                'label' => esc_html__( 'Normal', 'acz-elements' ),
            ]
        );

        $this->add_control(
            'item_color',
            [
                'label'     => esc_html__( 'Text Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__link' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'item_style_hover',
            [
                'label' => esc_html__( 'Hover', 'acz-elements' ),
            ]
        );

        $this->add_control(
            'item_hover_color',
            [
                'label'     => esc_html__( 'Text Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__link:hover, {{WRAPPER}} .acz-nav-menu__link:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_hover_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__link:hover, {{WRAPPER}} .acz-nav-menu__link:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'item_style_active',
            [
                'label' => esc_html__( 'Active', 'acz-elements' ),
            ]
        );

        $this->add_control(
            'item_active_color',
            [
                'label'     => esc_html__( 'Text Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu .current-menu-item > .acz-nav-menu__link, {{WRAPPER}} .acz-nav-menu .current-menu-ancestor > .acz-nav-menu__link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_active_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu .current-menu-item > .acz-nav-menu__link, {{WRAPPER}} .acz-nav-menu .current-menu-ancestor > .acz-nav-menu__link' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'separator'  => 'before',
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'item_border',
                'selector' => '{{WRAPPER}} .acz-nav-menu__link',
            ]
        );

        $this->add_responsive_control(
            'item_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_toggle',
            [
                'label'     => esc_html__( 'Toggle Button', 'acz-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_responsive' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'toggle_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__toggle' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .acz-nav-menu__toggle-line' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__toggle' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'toggle_border',
                'selector' => '{{WRAPPER}} .acz-nav-menu__toggle',
            ]
        );

        $this->add_responsive_control(
            'toggle_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_dropdown',
            [
                'label' => esc_html__( 'Dropdown', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'dropdown_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu' => '--acz-nav-dropdown-background: {{VALUE}};',
                    '{{WRAPPER}} .acz-nav-menu .sub-menu' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dropdown_min_width',
            [
                'label'      => esc_html__( 'Min Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min' => 120,
                        'max' => 480,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu .sub-menu' => 'min-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dropdown_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu .sub-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'dropdown_border',
                'selector' => '{{WRAPPER}} .acz-nav-menu .sub-menu',
            ]
        );

        $this->add_responsive_control(
            'dropdown_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu .sub-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_submenu_toggle',
            [
                'label' => esc_html__( 'Submenu Toggle', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'submenu_toggle_color',
            [
                'label'     => esc_html__( 'Icon Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__submenu-toggle' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'submenu_toggle_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-nav-menu__submenu-toggle' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'submenu_toggle_size',
            [
                'label'      => esc_html__( 'Button Size', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min' => 24,
                        'max' => 96,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__submenu-toggle' => 'flex-basis: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'submenu_toggle_icon_size',
            [
                'label'      => esc_html__( 'Icon Size', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min' => 6,
                        'max' => 32,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__submenu-toggle-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'submenu_toggle_border_width',
            [
                'label'      => esc_html__( 'Icon Stroke Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [
                        'min' => 1,
                        'max' => 8,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__submenu-toggle-icon' => 'border-right-width: {{SIZE}}{{UNIT}}; border-bottom-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'submenu_toggle_border_radius',
            [
                'label'      => esc_html__( 'Button Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-nav-menu__submenu-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $menu     = ! empty( $settings['menu'] ) ? $settings['menu'] : '';

        if ( empty( $menu ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-nav-menu-placeholder">' . esc_html__( 'Choose a menu for ACZ Nav Menu.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        $menu_id = 'acz-nav-menu-list-' . $this->get_id();

        $this->add_render_attribute( 'wrapper', 'class', 'acz-nav-menu' );
        $this->add_submenu_toggle_visibility_classes( $settings );

        if ( 'yes' === ( $settings['enable_responsive'] ?? 'yes' ) ) {
            $breakpoint = in_array( $settings['responsive_breakpoint'] ?? 'tablet', [ 'tablet', 'mobile' ], true )
                ? $settings['responsive_breakpoint']
                : 'tablet';

            $this->add_render_attribute( 'wrapper', 'class', 'acz-nav-menu--responsive' );
            $this->add_render_attribute( 'wrapper', 'class', 'acz-nav-menu--breakpoint-' . $breakpoint );
            $this->add_render_attribute( 'wrapper', 'data-acz-nav-menu', 'true' );
            $this->add_render_attribute( 'wrapper', 'data-breakpoint', $breakpoint );
        }

        echo '<nav ' . $this->get_render_attribute_string( 'wrapper' ) . ' aria-label="' . esc_attr__( 'Navigation Menu', 'acz-elements' ) . '">';

        if ( 'yes' === ( $settings['enable_responsive'] ?? 'yes' ) ) {
            $toggle_label = ! empty( $settings['toggle_label'] ) ? $settings['toggle_label'] : esc_html__( '', 'acz-elements' );
            echo '<div class="acz-nav-menu__toggle-wrap">';
            echo '<button class="acz-nav-menu__toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr( $menu_id ) . '">';
            echo '<span class="acz-nav-menu__toggle-icon" aria-hidden="true"><span class="acz-nav-menu__toggle-line"></span><span class="acz-nav-menu__toggle-line"></span><span class="acz-nav-menu__toggle-line"></span></span>';
            echo '<span class="acz-nav-menu__toggle-text">' . esc_html( $toggle_label ) . '</span>';
            echo '</button>';
            echo '</div>';
        }

        wp_nav_menu(
            [
                'menu'            => $menu,
                'container'       => false,
                'fallback_cb'     => false,
                'menu_class'      => 'acz-nav-menu__list',
                'menu_id'         => $menu_id,
                'link_before'     => '<span class="acz-nav-menu__text">',
                'link_after'      => '</span>',
                'walker'          => new ACZ_Nav_Menu_Walker(),
            ]
        );

        echo '</nav>';
    }

    private function add_submenu_toggle_visibility_classes( array $settings ): void {
        $devices = [
            'desktop' => '',
            'tablet'  => 'yes',
            'mobile'  => 'yes',
        ];

        foreach ( $devices as $device => $default ) {
            $setting_key = 'show_submenu_toggle_' . $device;
            $value       = $settings[ $setting_key ] ?? $default;

            if ( 'yes' === $value ) {
                $this->add_render_attribute( 'wrapper', 'class', 'acz-nav-menu--submenu-toggle-' . $device );
            }
        }
    }

    private function get_menu_options(): array {
        $menus = wp_get_nav_menus();

        if ( empty( $menus ) || is_wp_error( $menus ) ) {
            return [];
        }

        $options = [];

        foreach ( $menus as $menu ) {
            $options[ (string) $menu->term_id ] = $menu->name;
        }

        return $options;
    }
}

class ACZ_Nav_Menu_Walker extends \Walker_Nav_Menu {

    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes = empty( $item->classes ) ? [] : (array) $item->classes;
        $classes[] = 'acz-nav-menu__item';

        if ( in_array( 'menu-item-has-children', $classes, true ) ) {
            $classes[] = 'acz-nav-menu__item--has-children';
        }

        $class_names = implode( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) );
        $output .= '<li class="' . esc_attr( $class_names ) . '">';

        $atts = [
            'class' => 'acz-nav-menu__link',
            'href'  => ! empty( $item->url ) ? $item->url : '',
        ];

        if ( ! empty( $item->target ) ) {
            $atts['target'] = $item->target;
        }

        if ( ! empty( $item->xfn ) ) {
            $atts['rel'] = $item->xfn;
        }

        if ( ! empty( $item->attr_title ) ) {
            $atts['title'] = $item->attr_title;
        }

        $attributes = '';

        foreach ( $atts as $attr => $value ) {
            if ( '' === $value ) {
                continue;
            }

            $value = 'href' === $attr ? esc_url( $value ) : esc_attr( $value );
            $attributes .= ' ' . $attr . '="' . $value . '"';
        }

        $title = apply_filters( 'the_title', $item->title, $item->ID );

        $output .= '<a' . $attributes . '>';
        $output .= '<span class="acz-nav-menu__text">' . esc_html( $title ) . '</span>';
        $output .= '</a>';

        if ( in_array( 'menu-item-has-children', $classes, true ) ) {
            $output .= '<button class="acz-nav-menu__submenu-toggle" type="button" aria-expanded="false">';
            $output .= '<span class="screen-reader-text">' . esc_html__( 'Toggle submenu', 'acz-elements' ) . '</span>';
            $output .= '<span class="acz-nav-menu__submenu-toggle-icon" aria-hidden="true"></span>';
            $output .= '</button>';
        }
    }
}
