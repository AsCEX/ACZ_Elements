<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KC_Karice_Taxonomy_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'karice_taxonomy';
    }

    public function get_title(): string {
        return esc_html__( 'Karice Taxonomy', 'karice-elements' );
    }

    public function get_icon(): string {
        return 'eicon-tags';
    }

    public function get_categories(): array {
        return [ 'karice' ];
    }

    public function get_keywords(): array {
        return [ 'taxonomy', 'terms', 'post', 'pill', 'karice' ];
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
            'taxonomies',
            [
                'label'       => esc_html__( 'Taxonomies', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $this->get_taxonomy_options(),
                'description' => esc_html__( 'Select one or more taxonomies to show terms from the current post.', 'karice-elements' ),
            ]
        );

        $this->add_control(
            'show_taxonomy_label',
            [
                'label'        => esc_html__( 'Show Taxonomy Label', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'karice-elements' ),
                'label_off'    => esc_html__( 'No', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );

        $this->add_control(
            'show_term_link',
            [
                'label'        => esc_html__( 'Link Terms', 'karice-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'karice-elements' ),
                'label_off'    => esc_html__( 'No', 'karice-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'empty_text',
            [
                'label'       => esc_html__( 'Empty Text', 'karice-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'No taxonomy terms found for this post.', 'karice-elements' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_list',
            [
                'label' => esc_html__( 'List', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'group_gap',
            [
                'label'      => esc_html__( 'Groups Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-taxonomy-widget' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'widget_flex_direction',
            [
                'label'   => esc_html__( 'Flex Direction', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'column',
                'options' => [
                    'row'            => esc_html__( 'Row', 'karice-elements' ),
                    'row-reverse'    => esc_html__( 'Row Reverse', 'karice-elements' ),
                    'column'         => esc_html__( 'Column', 'karice-elements' ),
                    'column-reverse' => esc_html__( 'Column Reverse', 'karice-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-current-taxonomy-group' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'widget_align_items',
            [
                'label'   => esc_html__( 'Align Items', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'stretch',
                'options' => [
                    'stretch'    => esc_html__( 'Stretch', 'karice-elements' ),
                    'flex-start' => esc_html__( 'Start', 'karice-elements' ),
                    'center'     => esc_html__( 'Center', 'karice-elements' ),
                    'flex-end'   => esc_html__( 'End', 'karice-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-current-taxonomy-widget' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'widget_justify_items',
            [
                'label'   => esc_html__( 'Justify Items', 'karice-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'flex-start',
                'options' => [
                    'flex-start'    => esc_html__( 'Start', 'karice-elements' ),
                    'center'        => esc_html__( 'Center', 'karice-elements' ),
                    'flex-end'      => esc_html__( 'End', 'karice-elements' ),
                    'space-between' => esc_html__( 'Space Between', 'karice-elements' ),
                    'space-around'  => esc_html__( 'Space Around', 'karice-elements' ),
                    'space-evenly'  => esc_html__( 'Space Evenly', 'karice-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .krc-current-taxonomy-widget' => 'justify-content: {{VALUE}}; justify-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_gap',
            [
                'label'      => esc_html__( 'Items Gap', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-taxonomy-list' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_taxonomy_label',
            [
                'label' => esc_html__( 'Taxonomy Label', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'taxonomy_label_color',
            [
                'label'     => esc_html__( 'Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-current-taxonomy-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'taxonomy_label_typography',
                'selector' => '{{WRAPPER}} .krc-current-taxonomy-label',
            ]
        );

        $this->add_responsive_control(
            'taxonomy_label_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-taxonomy-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'taxonomy_label_margin',
            [
                'label'      => esc_html__( 'Margin', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-taxonomy-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_item',
            [
                'label' => esc_html__( 'Item', 'karice-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'item_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-current-taxonomy-pill' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_background_color',
            [
                'label'     => esc_html__( 'Background', 'karice-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .krc-current-taxonomy-pill' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'item_border',
                'selector' => '{{WRAPPER}} .krc-current-taxonomy-pill',
            ]
        );

        $this->add_responsive_control(
            'item_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-taxonomy-pill' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => esc_html__( 'Padding', 'karice-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .krc-current-taxonomy-pill' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'item_typography',
                'selector' => '{{WRAPPER}} .krc-current-taxonomy-pill',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings          = $this->get_settings_for_display();
        $selected_taxonomy = isset( $settings['taxonomies'] ) && is_array( $settings['taxonomies'] ) ? array_filter( array_map( 'sanitize_key', $settings['taxonomies'] ) ) : [];
        $show_label        = 'yes' === (string) ( $settings['show_taxonomy_label'] ?? 'no' );
        $show_term_link    = 'yes' === (string) ( $settings['show_term_link'] ?? 'yes' );
        $empty_text        = (string) ( $settings['empty_text'] ?? esc_html__( 'No taxonomy terms found for this post.', 'karice-elements' ) );

        $post_id = get_the_ID();
        if ( ! $post_id ) {
            $post_id = get_queried_object_id();
        }
        $post_id = (int) $post_id;

        if ( $post_id <= 0 || ! is_singular() ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="krc-post-gallery-empty">' . esc_html__( 'Karice Taxonomy works on single post pages.', 'karice-elements' ) . '</div>';
            }
            return;
        }

        if ( empty( $selected_taxonomy ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="krc-post-gallery-empty">' . esc_html__( 'Select at least one taxonomy in Karice Taxonomy widget.', 'karice-elements' ) . '</div>';
            }
            return;
        }

        $groups    = [];
        $post_type = get_post_type( $post_id );

        foreach ( $selected_taxonomy as $taxonomy ) {
            if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            if ( ! is_object_in_taxonomy( (string) $post_type, $taxonomy ) ) {
                continue;
            }

            $terms = get_the_terms( $post_id, $taxonomy );
            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                continue;
            }

            $taxonomy_obj = get_taxonomy( $taxonomy );
            $groups[]     = [
                'label' => $taxonomy_obj && ! empty( $taxonomy_obj->labels->singular_name ) ? (string) $taxonomy_obj->labels->singular_name : $taxonomy,
                'terms' => $terms,
            ];
        }

        if ( empty( $groups ) ) {
            if ( '' !== trim( $empty_text ) ) {
                echo '<div class="krc-current-taxonomy-empty">' . esc_html( $empty_text ) . '</div>';
            }
            return;
        }

        ?>
        <div class="krc-current-taxonomy-widget">
            <?php foreach ( $groups as $group ) : ?>
                <div class="krc-current-taxonomy-group">
                    <?php if ( $show_label ) : ?>
                        <div class="krc-current-taxonomy-label"><?php echo esc_html( $group['label'] ); ?></div>
                    <?php endif; ?>
                    <ul class="krc-current-taxonomy-list">
                        <?php foreach ( $group['terms'] as $term ) : ?>
                            <?php if ( ! $term instanceof \WP_Term ) {
                                continue;
                            } ?>
                            <li class="krc-current-taxonomy-item">
                                <?php if ( $show_term_link ) : ?>
                                    <?php $term_link = get_term_link( $term ); ?>
                                    <?php if ( ! is_wp_error( $term_link ) ) : ?>
                                        <a class="krc-current-taxonomy-pill" href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $term->name ); ?></a>
                                    <?php else : ?>
                                        <span class="krc-current-taxonomy-pill"><?php echo esc_html( $term->name ); ?></span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="krc-current-taxonomy-pill"><?php echo esc_html( $term->name ); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
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
