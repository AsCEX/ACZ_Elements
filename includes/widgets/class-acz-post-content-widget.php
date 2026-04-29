<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Post_Content_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'acz_post_content';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Post Content', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-post-content';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'post', 'content', 'field', 'acf', 'acz' ];
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
            'post_field',
            [
                'label'   => esc_html__( 'Post Field', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'post_content',
                'options' => [
                    'post_title'   => esc_html__( 'Title', 'acz-elements' ),
                    'post_excerpt' => esc_html__( 'Excerpt', 'acz-elements' ),
                    'post_content' => esc_html__( 'Content', 'acz-elements' ),
                    'post_date'    => esc_html__( 'Publish Date', 'acz-elements' ),
                    'post_author'  => esc_html__( 'Author Name', 'acz-elements' ),
                    'post_slug'    => esc_html__( 'Slug', 'acz-elements' ),
                    'post_id'      => esc_html__( 'Post ID', 'acz-elements' ),
                    'woo_price'    => esc_html__( 'WooCommerce Product Price', 'acz-elements' ),
                    'acf'          => esc_html__( 'ACF Field', 'acz-elements' ),
                ],
            ]
        );

        $this->add_control(
            'acf_field_name',
            [
                'label'       => esc_html__( 'ACF Field Name', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'content',
                'placeholder' => 'content',
                'label_block' => true,
                'condition'   => [
                    'post_field' => 'acf',
                ],
            ]
        );

        $this->add_control(
            'date_format',
            [
                'label'       => esc_html__( 'Date Format', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => get_option( 'date_format' ),
                'placeholder' => get_option( 'date_format' ),
                'description' => esc_html__( 'Used only for Publish Date field.', 'acz-elements' ),
                'condition'   => [
                    'post_field' => 'post_date',
                ],
            ]
        );

        $this->add_control(
            'empty_text',
            [
                'label'       => esc_html__( 'Empty Text', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'No value found for the selected field.', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_content',
            [
                'label' => esc_html__( 'Content', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'alignment',
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
                    '{{WRAPPER}} .acz-post-content-widget' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-post-content-widget' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'typography',
                'selector' => '{{WRAPPER}} .acz-post-content-widget',
            ]
        );

        $this->add_responsive_control(
            'margin',
            [
                'label'      => esc_html__( 'Margin', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-post-content-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-post-content-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings   = $this->get_settings_for_display();
        $post_id    = $this->get_context_post_id();
        $empty_text = (string) ( $settings['empty_text'] ?? esc_html__( 'No value found for the selected field.', 'acz-elements' ) );

        if ( $post_id <= 0 || ! is_singular() ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'ACZ Post Content works on single post pages.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        $field = (string) ( $settings['post_field'] ?? 'post_content' );
        $value = $this->get_field_value( $post_id, $field, $settings );

        if ( '' === trim( wp_strip_all_tags( $value ) ) ) {
            if ( '' !== trim( $empty_text ) ) {
                echo '<div class="acz-post-content-empty">' . esc_html( $empty_text ) . '</div>';
            }
            return;
        }

        echo '<div class="acz-post-content-widget">';
        if ( 'post_content' === $field ) {
            echo wp_kses_post( $value );
        } else {
            echo esc_html( $value );
        }
        echo '</div>';
    }

    private function get_field_value( int $post_id, string $field, array $settings ): string {
        if ( 'acf' === $field ) {
            return $this->get_acf_field_value( $post_id, (string) ( $settings['acf_field_name'] ?? 'content' ) );
        }

        if ( 'post_title' === $field ) {
            return (string) get_the_title( $post_id );
        }

        if ( 'post_excerpt' === $field ) {
            $excerpt = (string) get_post_field( 'post_excerpt', $post_id );
            if ( '' === trim( $excerpt ) ) {
                $excerpt = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 55 );
            }
            return $excerpt;
        }

        if ( 'post_content' === $field ) {
            return (string) apply_filters( 'the_content', (string) get_post_field( 'post_content', $post_id ) );
        }

        if ( 'post_date' === $field ) {
            $date_format = (string) ( $settings['date_format'] ?? get_option( 'date_format' ) );
            if ( '' === trim( $date_format ) ) {
                $date_format = get_option( 'date_format' );
            }
            return (string) get_the_date( $date_format, $post_id );
        }

        if ( 'post_author' === $field ) {
            $author_id = (int) get_post_field( 'post_author', $post_id );
            return (string) get_the_author_meta( 'display_name', $author_id );
        }

        if ( 'post_slug' === $field ) {
            return (string) get_post_field( 'post_name', $post_id );
        }

        if ( 'post_id' === $field ) {
            return (string) $post_id;
        }

        if ( 'woo_price' === $field ) {
            return $this->get_woo_price( $post_id );
        }

        return '';
    }

    private function get_woo_price( int $post_id ): string {
        if ( ! function_exists( 'wc_get_product' ) || 'product' !== get_post_type( $post_id ) ) {
            return '';
        }

        $product = wc_get_product( $post_id );
        if ( ! $product ) {
            return '';
        }

        $price_html = $product->get_price_html();
        if ( ! is_string( $price_html ) ) {
            return '';
        }

        return wp_strip_all_tags( $price_html );
    }

    private function get_acf_field_value( int $post_id, string $field_name ): string {
        $field_name = trim( $field_name );
        if ( '' === $field_name ) {
            return '';
        }

        if ( function_exists( 'get_field' ) ) {
            $value = get_field( $field_name, $post_id );
        } else {
            $value = get_post_meta( $post_id, $field_name, true );
        }

        if ( is_array( $value ) ) {
            $flat = [];
            foreach ( $value as $item ) {
                if ( is_scalar( $item ) ) {
                    $flat[] = wp_strip_all_tags( (string) $item );
                }
            }
            return implode( ', ', $flat );
        }

        if ( is_object( $value ) ) {
            if ( method_exists( $value, '__toString' ) ) {
                return (string) $value;
            }
            return '';
        }

        return is_scalar( $value ) ? (string) $value : '';
    }

    private function get_context_post_id(): int {
        $post_id = get_the_ID();

        if ( ! $post_id ) {
            $post_id = get_queried_object_id();
        }

        return (int) $post_id;
    }
}
