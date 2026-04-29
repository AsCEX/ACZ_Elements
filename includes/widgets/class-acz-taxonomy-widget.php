<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Taxonomy_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'acz_taxonomy';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Taxonomy', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-tags';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'taxonomy', 'terms', 'post', 'pill', 'acz' ];
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
            'taxonomies',
            [
                'label'       => esc_html__( 'Taxonomies', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $this->get_taxonomy_options(),
                'description' => esc_html__( 'Select one or more taxonomies to show terms from the current post.', 'acz-elements' ),
            ]
        );

        $this->add_control(
            'show_taxonomy_label',
            [
                'label'        => esc_html__( 'Show Taxonomy Label', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );

        $this->add_control(
            'show_term_link',
            [
                'label'        => esc_html__( 'Link Terms', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'empty_text',
            [
                'label'       => esc_html__( 'Empty Text', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'No taxonomy terms found for this post.', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_list',
            [
                'label' => esc_html__( 'List', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'group_gap',
            [
                'label'      => esc_html__( 'Groups Gap', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-taxonomy-widget' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'widget_flex_direction',
            [
                'label'   => esc_html__( 'Flex Direction', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'column',
                'options' => [
                    'row'            => esc_html__( 'Row', 'acz-elements' ),
                    'row-reverse'    => esc_html__( 'Row Reverse', 'acz-elements' ),
                    'column'         => esc_html__( 'Column', 'acz-elements' ),
                    'column-reverse' => esc_html__( 'Column Reverse', 'acz-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .acz-current-taxonomy-group' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'widget_align_items',
            [
                'label'   => esc_html__( 'Align Items', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'stretch',
                'options' => [
                    'stretch'    => esc_html__( 'Stretch', 'acz-elements' ),
                    'flex-start' => esc_html__( 'Start', 'acz-elements' ),
                    'center'     => esc_html__( 'Center', 'acz-elements' ),
                    'flex-end'   => esc_html__( 'End', 'acz-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .acz-current-taxonomy-widget' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'widget_justify_items',
            [
                'label'   => esc_html__( 'Justify Items', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'flex-start',
                'options' => [
                    'flex-start'    => esc_html__( 'Start', 'acz-elements' ),
                    'center'        => esc_html__( 'Center', 'acz-elements' ),
                    'flex-end'      => esc_html__( 'End', 'acz-elements' ),
                    'space-between' => esc_html__( 'Space Between', 'acz-elements' ),
                    'space-around'  => esc_html__( 'Space Around', 'acz-elements' ),
                    'space-evenly'  => esc_html__( 'Space Evenly', 'acz-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .acz-current-taxonomy-widget' => 'justify-content: {{VALUE}}; justify-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_gap',
            [
                'label'      => esc_html__( 'Items Gap', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-taxonomy-list' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_taxonomy_label',
            [
                'label' => esc_html__( 'Taxonomy Label', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'taxonomy_label_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-current-taxonomy-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'taxonomy_label_typography',
                'selector' => '{{WRAPPER}} .acz-current-taxonomy-label',
            ]
        );

        $this->add_responsive_control(
            'taxonomy_label_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-taxonomy-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'taxonomy_label_margin',
            [
                'label'      => esc_html__( 'Margin', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-taxonomy-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_item',
            [
                'label' => esc_html__( 'Item', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'item_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-current-taxonomy-pill' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_background_color',
            [
                'label'     => esc_html__( 'Background', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-current-taxonomy-pill' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'item_border',
                'selector' => '{{WRAPPER}} .acz-current-taxonomy-pill',
            ]
        );

        $this->add_responsive_control(
            'item_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-taxonomy-pill' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-current-taxonomy-pill' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'item_typography',
                'selector' => '{{WRAPPER}} .acz-current-taxonomy-pill',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings          = $this->get_settings_for_display();
        $selected_taxonomy = isset( $settings['taxonomies'] ) && is_array( $settings['taxonomies'] ) ? array_filter( array_map( 'sanitize_key', $settings['taxonomies'] ) ) : [];
        $show_label        = 'yes' === (string) ( $settings['show_taxonomy_label'] ?? 'no' );
        $show_term_link    = 'yes' === (string) ( $settings['show_term_link'] ?? 'yes' );
        $empty_text        = (string) ( $settings['empty_text'] ?? esc_html__( 'No taxonomy terms found for this post.', 'acz-elements' ) );

        $post_id = get_the_ID();
        if ( ! $post_id ) {
            $post_id = get_queried_object_id();
        }
        $post_id = (int) $post_id;

        if ( $post_id <= 0 || ! is_singular() ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'ACZ Taxonomy works on single post pages.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        if ( empty( $selected_taxonomy ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'Select at least one taxonomy in ACZ Taxonomy widget.', 'acz-elements' ) . '</div>';
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
                echo '<div class="acz-current-taxonomy-empty">' . esc_html( $empty_text ) . '</div>';
            }
            return;
        }

        ?>
        <div class="acz-current-taxonomy-widget">
            <?php foreach ( $groups as $group ) : ?>
                <div class="acz-current-taxonomy-group">
                    <?php if ( $show_label ) : ?>
                        <div class="acz-current-taxonomy-label"><?php echo esc_html( $group['label'] ); ?></div>
                    <?php endif; ?>
                    <ul class="acz-current-taxonomy-list">
                        <?php foreach ( $group['terms'] as $term ) : ?>
                            <?php if ( ! $term instanceof \WP_Term ) {
                                continue;
                            } ?>
                            <li class="acz-current-taxonomy-item">
                                <?php if ( $show_term_link ) : ?>
                                    <?php $term_link = get_term_link( $term ); ?>
                                    <?php if ( ! is_wp_error( $term_link ) ) : ?>
                                        <a class="acz-current-taxonomy-pill" href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $term->name ); ?></a>
                                    <?php else : ?>
                                        <span class="acz-current-taxonomy-pill"><?php echo esc_html( $term->name ); ?></span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="acz-current-taxonomy-pill"><?php echo esc_html( $term->name ); ?></span>
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
