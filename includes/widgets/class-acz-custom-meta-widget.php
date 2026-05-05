<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Custom_Meta_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'acz_custom_meta';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Custom Meta', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-post-info';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'meta', 'custom field', 'post', 'line', 'acz' ];
    }

    public function get_style_depends(): array {
        return [ 'acz-post-gallery' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    private function register_content_controls(): void {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Content', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'custom_meta_format',
            [
                'label'       => esc_html__( 'Custom Meta Format', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '%location% - %partner%',
                'placeholder' => '%location% - %partner%',
                'description' => esc_html__( 'Use meta keys wrapped in %. Example: %location% - %partner%', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'custom_meta_label_text',
            [
                'label'       => esc_html__( 'Label Text', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Details:', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'empty_text',
            [
                'label'       => esc_html__( 'Empty Text', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'No custom meta values found for this post.', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_custom_meta',
            [
                'label' => esc_html__( 'Custom Meta Line', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'custom_meta_alignment',
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
                    '{{WRAPPER}} .acz-current-custom-meta-line' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'custom_meta_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-current-custom-meta-line' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'custom_meta_typography',
                'selector' => '{{WRAPPER}} .acz-current-custom-meta-line',
            ]
        );

        $this->add_responsive_control(
            'custom_meta_margin',
            [
                'label'      => esc_html__( 'Margin', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-custom-meta-line' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'custom_meta_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-custom-meta-line' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_custom_meta_label',
            [
                'label' => esc_html__( 'Label', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'custom_meta_label_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-current-custom-meta-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'custom_meta_label_typography',
                'selector' => '{{WRAPPER}} .acz-current-custom-meta-label',
            ]
        );

        $this->add_responsive_control(
            'custom_meta_label_margin',
            [
                'label'      => esc_html__( 'Margin', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-custom-meta-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'custom_meta_label_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-custom-meta-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings   = $this->get_settings_for_display();
        $empty_text = (string) ( $settings['empty_text'] ?? esc_html__( 'No custom meta values found for this post.', 'acz-elements' ) );
        $label_text = trim( (string) ( $settings['custom_meta_label_text'] ?? '' ) );
        $post_id    = $this->get_context_post_id();

        $using_sample_post = class_exists( 'ACZ_Theme_Options' )
            && ACZ_Theme_Options::is_elementor_preview_context()
            && $post_id === ACZ_Theme_Options::get_elementor_preview_sample_post_id();

        if ( $post_id <= 0 || ( ! is_singular() && ! $using_sample_post ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'ACZ Custom Meta works on single post pages.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        $meta_line = $this->build_custom_meta_line(
            $post_id,
            (string) ( $settings['custom_meta_format'] ?? '%location% - %partner%' )
        );

        if ( '' === $meta_line ) {
            if ( '' !== trim( $empty_text ) ) {
                echo '<div class="acz-current-custom-meta-empty">' . esc_html( $empty_text ) . '</div>';
            }
            return;
        }

        echo '<div class="acz-current-custom-meta-line">';
        if ( '' !== $label_text ) {
            echo '<span class="acz-current-custom-meta-label">' . esc_html( $label_text ) . '</span> ';
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
        if ( class_exists( 'ACZ_Theme_Options' ) && ACZ_Theme_Options::is_elementor_preview_context() ) {
            $sample_post_id = ACZ_Theme_Options::get_elementor_preview_sample_post_id();

            if ( $sample_post_id ) {
                return $sample_post_id;
            }
        }

        $post_id = get_the_ID();

        if ( ! $post_id ) {
            $post_id = get_queried_object_id();
        }

        return (int) $post_id;
    }
}
