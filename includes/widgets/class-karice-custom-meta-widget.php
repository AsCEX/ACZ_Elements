<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KC_Karice_Custom_Meta_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'karice_custom_meta';
    }

    public function get_title(): string {
        return esc_html__( 'Karice Custom Meta', 'karice-elements' );
    }

    public function get_icon(): string {
        return 'eicon-post-info';
    }

    public function get_categories(): array {
        return [ 'karice' ];
    }

    public function get_keywords(): array {
        return [ 'meta', 'custom field', 'post', 'line', 'karice' ];
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
            'section_content',
            [
                'label' => esc_html__( 'Content', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'custom_meta_format',
            [
                'label'       => esc_html__( 'Custom Meta Format', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '%location% - %partner%',
                'placeholder' => '%location% - %partner%',
                'description' => esc_html__( 'Use meta keys wrapped in %. Example: %location% - %partner%', 'karice-elements' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'custom_meta_label_text',
            [
                'label'       => esc_html__( 'Label Text', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Details:', 'karice-elements' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'empty_text',
            [
                'label'       => esc_html__( 'Empty Text', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'No custom meta values found for this post.', 'karice-elements' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_custom_meta',
            [
                'label' => esc_html__( 'Custom Meta Line', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'custom_meta_alignment',
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
                    '{{WRAPPER}} .krc-current-custom-meta-line' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'custom_meta_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-current-custom-meta-line' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'custom_meta_typography',
                'selector' => '{{WRAPPER}} .krc-current-custom-meta-line',
            ]
        );

        $this->add_responsive_control(
            'custom_meta_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-custom-meta-line' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'custom_meta_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-custom-meta-line' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_custom_meta_label',
            [
                'label' => esc_html__( 'Label', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'custom_meta_label_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-current-custom-meta-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'custom_meta_label_typography',
                'selector' => '{{WRAPPER}} .krc-current-custom-meta-label',
            ]
        );

        $this->add_responsive_control(
            'custom_meta_label_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-custom-meta-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'custom_meta_label_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-custom-meta-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings   = $this->get_settings_for_display();
        $empty_text = (string) ( $settings['empty_text'] ?? esc_html__( 'No custom meta values found for this post.', 'karice-elements' ) );
        $label_text = trim( (string) ( $settings['custom_meta_label_text'] ?? '' ) );
        $post_id    = $this->get_context_post_id();

        if ( $post_id <= 0 || ! is_singular() ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="krc-post-gallery-empty">' . esc_html__( 'Karice Custom Meta works on single post pages.', 'karice-elements' ) . '</div>';
            }
            return;
        }

        $meta_line = $this->build_custom_meta_line(
            $post_id,
            (string) ( $settings['custom_meta_format'] ?? '%location% - %partner%' )
        );

        if ( '' === $meta_line ) {
            if ( '' !== trim( $empty_text ) ) {
                echo '<div class="krc-current-custom-meta-empty">' . esc_html( $empty_text ) . '</div>';
            }
            return;
        }

        echo '<div class="krc-current-custom-meta-line">';
        if ( '' !== $label_text ) {
            echo '<span class="krc-current-custom-meta-label">' . esc_html( $label_text ) . '</span> ';
        }
        echo esc_html( $meta_line );
        echo '</div>';
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

    private function get_context_post_id(): int {
        $post_id = get_the_ID();

        if ( ! $post_id ) {
            $post_id = get_queried_object_id();
        }

        return (int) $post_id;
    }
}
