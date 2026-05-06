<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_FAQ_Widget extends \Elementor\Widget_Base {
    public function get_name(): string {
        return 'acz_faq';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ FAQ', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-accordion';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'acz', 'faq', 'accordion', 'post', 'taxonomy', 'acf' ];
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
            'source',
            [
                'label'   => esc_html__( 'Item Source', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'theme_options',
                'options' => [
                    'items'         => esc_html__( 'Items', 'acz-elements' ),
                    'post'          => esc_html__( 'Post', 'acz-elements' ),
                    'taxonomy'      => esc_html__( 'Taxonomy', 'acz-elements' ),
                    'current_post'  => esc_html__( 'Current Post', 'acz-elements' ),
                    'current_taxonomy' => esc_html__( 'Current Taxonomy', 'acz-elements' ),
                    'theme_options' => esc_html__( 'Theme Options (ACF Field)', 'acz-elements' ),
                ],
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'item_title',
            [
                'label'       => esc_html__( 'Title', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'FAQ title', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'item_content',
            [
                'label'   => esc_html__( 'Content', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__( 'FAQ content goes here.', 'acz-elements' ),
            ]
        );

        $this->add_control(
            'items',
            [
                'label'       => esc_html__( 'Items', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'item_title'   => esc_html__( 'What is ACZ FAQ?', 'acz-elements' ),
                        'item_content' => esc_html__( 'Use this item source to add FAQ entries directly in Elementor.', 'acz-elements' ),
                    ],
                    [
                        'item_title'   => esc_html__( 'Can I reorder items?', 'acz-elements' ),
                        'item_content' => esc_html__( 'Yes. Drag items in the Elementor repeater to change their order.', 'acz-elements' ),
                    ],
                ],
                'title_field' => '{{{ item_title }}}',
                'condition'   => [
                    'source' => 'items',
                ],
            ]
        );

        $this->add_control(
            'post_type',
            [
                'label'       => esc_html__( 'Post Type', 'acz-elements' ),
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
                'label'     => esc_html__( 'Items Limit', 'acz-elements' ),
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
            'post_content_source',
            [
                'label'     => esc_html__( 'Post Content', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'excerpt',
                'options'   => [
                    'excerpt' => esc_html__( 'Excerpt', 'acz-elements' ),
                    'content' => esc_html__( 'Full Content', 'acz-elements' ),
                ],
                'condition' => [
                    'source' => 'post',
                ],
            ]
        );

        $this->add_control(
            'excerpt_words',
            [
                'label'     => esc_html__( 'Excerpt Words', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 30,
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
                'label'       => esc_html__( 'Taxonomy', 'acz-elements' ),
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
                'label'     => esc_html__( 'Items Limit', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 10,
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
                'label'        => esc_html__( 'Hide Empty Terms', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
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
                'label'       => esc_html__( 'ACF Repeater Field', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'faq',
                'description' => esc_html__( 'ACF repeater field name from Theme Options, Current Post, or Current Taxonomy.', 'acz-elements' ),
                'label_block' => true,
                'condition'   => [
                    'source' => [ 'theme_options', 'current_post', 'current_taxonomy' ],
                ],
            ]
        );

        $this->add_control(
            'acf_title_field',
            [
                'label'       => esc_html__( 'Title Field', 'acz-elements' ),
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
                'label'       => esc_html__( 'Content Field', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'content',
                'label_block' => true,
                'condition'   => [
                    'source' => [ 'theme_options', 'current_post', 'current_taxonomy' ],
                ],
            ]
        );

        $this->add_control(
            'open_first',
            [
                'label'        => esc_html__( 'Open First Item', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_item',
            [
                'label' => esc_html__( 'Item', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'item_bg',
            [
                'label'     => esc_html__( 'Background', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-faq-item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'item_border',
                'selector' => '{{WRAPPER}} .acz-faq-item',
            ]
        );

        $this->add_responsive_control(
            'item_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-faq-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_spacing',
            [
                'label'      => esc_html__( 'Items Spacing', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-faq' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_title',
            [
                'label' => esc_html__( 'Title', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-faq-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .acz-faq-title',
            ]
        );

        $this->add_responsive_control(
            'title_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-faq-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_content',
            [
                'label' => esc_html__( 'Content', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-faq-content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'content_background',
            [
                'label'     => esc_html__( 'Background', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-faq-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'content_typography',
                'selector' => '{{WRAPPER}} .acz-faq-content',
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-faq-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $items    = $this->resolve_faq_items( $settings );

        if ( empty( $items ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'No FAQ items found for selected source.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        $open_first = 'yes' === (string) ( $settings['open_first'] ?? 'no' );
        ?>
        <div class="acz-faq">
            <?php foreach ( $items as $index => $item ) : ?>
				<details class="acz-faq-item"<?php if ( $open_first && 0 === $index ) : ?> open<?php endif; ?>>
                    <summary class="acz-faq-title"><?php echo esc_html( $item['title'] ); ?></summary>
                    <div class="acz-faq-content"><?php echo wp_kses_post( $item['content'] ); ?></div>
                </details>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function resolve_faq_items( array $settings ): array {
        $source = (string) ( $settings['source'] ?? 'theme_options' );

        if ( 'items' === $source ) {
            return $this->build_from_repeater_items( $settings );
        }

        if ( 'post' === $source ) {
            return $this->build_from_posts( $settings );
        }

        if ( 'taxonomy' === $source ) {
            return $this->build_from_taxonomy( $settings );
        }

        if ( 'current_post' === $source ) {
            return $this->build_from_current_post( $settings );
        }

        if ( 'current_taxonomy' === $source ) {
            return $this->build_from_current_taxonomy( $settings );
        }

        return $this->build_from_theme_options( $settings );
    }

    private function build_from_repeater_items( array $settings ): array {
        $rows  = isset( $settings['items'] ) && is_array( $settings['items'] ) ? $settings['items'] : [];
        $items = [];

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row['item_title'] ) ? trim( (string) $row['item_title'] ) : '';
            if ( '' === $title ) {
                continue;
            }

            $content_raw = isset( $row['item_content'] ) ? (string) $row['item_content'] : '';

            $items[] = [
                'title'   => $title,
                'content' => wpautop( wp_kses_post( $content_raw ) ),
            ];
        }

        return $items;
    }

    private function build_from_posts( array $settings ): array {
        $post_type      = sanitize_key( (string) ( $settings['post_type'] ?? 'post' ) );
        $posts_per_page = max( 1, (int) ( $settings['posts_per_page'] ?? 8 ) );
        $content_source = (string) ( $settings['post_content_source'] ?? 'excerpt' );
        $excerpt_words  = max( 5, (int) ( $settings['excerpt_words'] ?? 30 ) );
        $items          = [];

        if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
            return $items;
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
            return $items;
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

            $items[] = [
                'title'   => get_the_title( $post_id ),
                'content' => $content,
            ];
        }

        wp_reset_postdata();
        return $items;
    }

    private function build_from_taxonomy( array $settings ): array {
        $taxonomy   = sanitize_key( (string) ( $settings['taxonomy'] ?? '' ) );
        $limit      = max( 1, (int) ( $settings['terms_limit'] ?? 10 ) );
        $hide_empty = 'yes' === (string) ( $settings['hide_empty'] ?? 'yes' );
        $items      = [];

        if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
            return $items;
        }

        $terms = get_terms(
            [
                'taxonomy'   => $taxonomy,
                'hide_empty' => $hide_empty,
                'number'     => $limit,
            ]
        );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return $items;
        }

        foreach ( $terms as $term ) {
            if ( ! $term instanceof \WP_Term ) {
                continue;
            }

            $description = (string) $term->description;
            if ( '' === trim( wp_strip_all_tags( $description ) ) ) {
                $description = sprintf(
                    '<p>%1$s: %2$d</p>',
                    esc_html__( 'Posts', 'acz-elements' ),
                    (int) $term->count
                );
            } else {
                $description = wpautop( wp_kses_post( $description ) );
            }

            $items[] = [
                'title'   => (string) $term->name,
                'content' => $description,
            ];
        }

        return $items;
    }

    private function build_from_theme_options( array $settings ): array {
        $repeater_field = sanitize_key( (string) ( $settings['acf_repeater_field'] ?? 'faq' ) );
        $title_field    = sanitize_key( (string) ( $settings['acf_title_field'] ?? 'title' ) );
        $content_field  = sanitize_key( (string) ( $settings['acf_content_field'] ?? 'content' ) );
        $items          = [];

        if ( '' === $repeater_field || ! function_exists( 'get_field' ) ) {
            return $items;
        }

        $rows = get_field( $repeater_field, 'option' );
        if ( ! is_array( $rows ) ) {
            return $items;
        }

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row[ $title_field ] ) ? trim( (string) $row[ $title_field ] ) : '';
            if ( '' === $title ) {
                continue;
            }

            $content_raw = isset( $row[ $content_field ] ) ? $row[ $content_field ] : '';
            $content     = '';

            if ( is_string( $content_raw ) ) {
                $content = wpautop( wp_kses_post( $content_raw ) );
            } elseif ( is_scalar( $content_raw ) ) {
                $content = '<p>' . esc_html( (string) $content_raw ) . '</p>';
            }

            $items[] = [
                'title'   => $title,
                'content' => $content,
            ];
        }

        return $items;
    }

    private function build_from_current_post( array $settings ): array {
        $repeater_field = sanitize_key( (string) ( $settings['acf_repeater_field'] ?? 'faq' ) );
        $title_field    = sanitize_key( (string) ( $settings['acf_title_field'] ?? 'title' ) );
        $content_field  = sanitize_key( (string) ( $settings['acf_content_field'] ?? 'content' ) );
        $items          = [];
        $post_id        = get_queried_object_id();

        if ( class_exists( 'ACZ_Theme_Options' ) && ACZ_Theme_Options::is_elementor_preview_context() ) {
            $sample_post_id = ACZ_Theme_Options::get_elementor_preview_sample_post_id();

            if ( $sample_post_id ) {
                $post_id = $sample_post_id;
            }
        }

        if ( ! $post_id || '' === $repeater_field || ! function_exists( 'get_field' ) ) {
            return $items;
        }

        $rows = get_field( $repeater_field, $post_id );
        if ( ! is_array( $rows ) ) {
            return $items;
        }

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row[ $title_field ] ) ? trim( (string) $row[ $title_field ] ) : '';
            if ( '' === $title ) {
                continue;
            }

            $content_raw = isset( $row[ $content_field ] ) ? $row[ $content_field ] : '';
            $content     = '';

            if ( is_string( $content_raw ) ) {
                $content = wpautop( wp_kses_post( $content_raw ) );
            } elseif ( is_scalar( $content_raw ) ) {
                $content = '<p>' . esc_html( (string) $content_raw ) . '</p>';
            }

            $items[] = [
                'title'   => $title,
                'content' => $content,
            ];
        }

        return $items;
    }

    private function build_from_current_taxonomy( array $settings ): array {
        $repeater_field = sanitize_key( (string) ( $settings['acf_repeater_field'] ?? 'faq' ) );
        $title_field    = sanitize_key( (string) ( $settings['acf_title_field'] ?? 'title' ) );
        $content_field  = sanitize_key( (string) ( $settings['acf_content_field'] ?? 'content' ) );
        $items          = [];
        $term           = get_queried_object();

        if ( class_exists( 'ACZ_Theme_Options' ) && ACZ_Theme_Options::is_elementor_preview_context() ) {
            $sample_term = ACZ_Theme_Options::get_elementor_preview_sample_term();

            if ( $sample_term instanceof \WP_Term ) {
                $term = $sample_term;
            }
        }

        if ( '' === $repeater_field || ! function_exists( 'get_field' ) || ! ( $term instanceof \WP_Term ) ) {
            return $items;
        }

        $rows = $this->get_acf_term_field_value( $repeater_field, $term );
        if ( ! is_array( $rows ) ) {
            return $items;
        }

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row[ $title_field ] ) ? trim( (string) $row[ $title_field ] ) : '';
            if ( '' === $title ) {
                continue;
            }

            $content_raw = isset( $row[ $content_field ] ) ? $row[ $content_field ] : '';
            $content     = '';

            if ( is_string( $content_raw ) ) {
                $content = wpautop( wp_kses_post( $content_raw ) );
            } elseif ( is_scalar( $content_raw ) ) {
                $content = '<p>' . esc_html( (string) $content_raw ) . '</p>';
            }

            $items[] = [
                'title'   => $title,
                'content' => $content,
            ];
        }

        return $items;
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
