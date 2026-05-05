<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Site_Logo_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'acz-site-logo';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Site Logo', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-site-logo';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'site', 'logo', 'identity', 'header', 'acz' ];
    }

    public function get_style_depends(): array {
        return [ 'acz-common' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_logo',
            [
                'label' => esc_html__( 'Logo', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'custom_logo',
            [
                'label'       => esc_html__( 'Custom Logo', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'description' => esc_html__( 'Leave empty to use the logo from Appearance > Customize > Site Identity.', 'acz-elements' ),
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'      => 'image',
                'default'   => 'full',
                'separator' => 'none',
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label'     => esc_html__( 'Alignment', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [
                        'title' => esc_html__( 'Left', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'  => [
                        'title' => esc_html__( 'Right', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .acz-site-logo' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'link_to',
            [
                'label'   => esc_html__( 'Link', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'site_url',
                'options' => [
                    'none'     => esc_html__( 'None', 'acz-elements' ),
                    'site_url' => esc_html__( 'Site URL', 'acz-elements' ),
                    'custom'   => esc_html__( 'Custom URL', 'acz-elements' ),
                ],
            ]
        );

        $this->add_control(
            'link',
            [
                'label'       => esc_html__( 'Custom URL', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'acz-elements' ),
                'condition'   => [
                    'link_to' => 'custom',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_image',
            [
                'label' => esc_html__( 'Image', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'width',
            [
                'label'      => esc_html__( 'Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
                'range'      => [
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    '%'  => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-site-logo img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'max_width',
            [
                'label'      => esc_html__( 'Max Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
                'default'    => [
                    'unit' => '%',
                ],
                'range'      => [
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    '%'  => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-site-logo img' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'height',
            [
                'label'      => esc_html__( 'Height', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'vh' ],
                'range'      => [
                    'px' => [
                        'min' => 1,
                        'max' => 500,
                    ],
                    'vh' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-site-logo img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'object_fit',
            [
                'label'     => esc_html__( 'Object Fit', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'contain',
                'options'   => [
                    'contain' => esc_html__( 'Contain', 'acz-elements' ),
                    'cover'   => esc_html__( 'Cover', 'acz-elements' ),
                    'fill'    => esc_html__( 'Fill', 'acz-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .acz-site-logo img' => 'object-fit: {{VALUE}};',
                ],
                'condition' => [
                    'height[size]!' => '',
                ],
            ]
        );

        $this->add_control(
            'opacity',
            [
                'label'     => esc_html__( 'Opacity', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'max'  => 1,
                        'min'  => 0.1,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .acz-site-logo img' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name'     => 'css_filters',
                'selector' => '{{WRAPPER}} .acz-site-logo img',
            ]
        );

        $this->add_responsive_control(
            'border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-site-logo img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => esc_html__( 'Hover Animation', 'acz-elements' ),
                'type'  => \Elementor\Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $logo_id  = $this->get_logo_id( $settings );

        $this->add_render_attribute( 'wrapper', 'class', 'acz-site-logo' );
        $this->add_render_attribute( 'image', 'class', 'acz-site-logo-img' );

        if ( ! empty( $settings['hover_animation'] ) ) {
            $this->add_render_attribute( 'image', 'class', 'elementor-animation-' . $settings['hover_animation'] );
        }

        $this->add_link_render_attributes( $settings );

        $image_html = $this->get_logo_html( $settings, $logo_id );

        if ( '' === $image_html ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                $image_html = '<span class="acz-site-logo-placeholder">' . esc_html__( 'Site Logo', 'acz-elements' ) . '</span>';
            } else {
                return;
            }
        }

		$wrapper_attributes = $this->get_render_attribute_string( 'wrapper' );
		$link_attributes = $this->get_render_attribute_string( 'link' );

		echo wp_kses_post( '<div ' . $wrapper_attributes . '>' );

		if ( '' !== $link_attributes ) {
			echo wp_kses_post( '<a ' . $link_attributes . '>' );
			echo wp_kses_post( $image_html );
			echo '</a>';
		} else {
			echo wp_kses_post( $image_html );
		}

        echo '</div>';
    }

    private function get_logo_id( array $settings ): int {
        if ( ! empty( $settings['custom_logo']['id'] ) ) {
            return (int) $settings['custom_logo']['id'];
        }

        return (int) get_theme_mod( 'custom_logo' );
    }

    private function get_logo_html( array $settings, int $logo_id ): string {
        if ( $logo_id > 0 ) {
            $image_src = \Elementor\Group_Control_Image_Size::get_attachment_image_src( $logo_id, 'image', $settings );

            if ( ! $image_src ) {
                $image_src = wp_get_attachment_image_url( $logo_id, 'full' );
            }

            if ( ! $image_src ) {
                return '';
            }

            return sprintf(
                '<img src="%1$s" alt="%2$s" %3$s>',
                esc_url( $image_src ),
                esc_attr( get_bloginfo( 'name' ) ),
                $this->get_render_attribute_string( 'image' )
            );
        }

        if ( empty( $settings['custom_logo']['url'] ) ) {
            return '';
        }

        return sprintf(
            '<img src="%1$s" alt="%2$s" %3$s>',
            esc_url( $settings['custom_logo']['url'] ),
            esc_attr( get_bloginfo( 'name' ) ),
            $this->get_render_attribute_string( 'image' )
        );
    }

    private function add_link_render_attributes( array $settings ): void {
        if ( empty( $settings['link_to'] ) || 'none' === $settings['link_to'] ) {
            return;
        }

        if ( 'site_url' === $settings['link_to'] ) {
            $this->add_render_attribute(
                'link',
                [
                    'href' => home_url( '/' ),
                    'rel'  => 'home',
                ]
            );
            return;
        }

        if ( 'custom' === $settings['link_to'] && ! empty( $settings['link']['url'] ) ) {
            $this->add_link_attributes( 'link', $settings['link'] );
        }
    }
}
