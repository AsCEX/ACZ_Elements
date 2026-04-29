<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Post_Filter_Widget extends \Elementor\Widget_Base {
    public function get_name(): string {
        return 'acz_post_filter';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Post Filter', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-filter';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'filter', 'dropdown', 'taxonomy', 'posts', 'acz' ];
    }

    public function get_style_depends(): array {
        return [ 'acz-post-gallery' ];
    }

    public function get_script_depends(): array {
        return [ 'acz-post-filter-ajax' ];
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

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    private function register_content_controls(): void {
        $this->start_controls_section(
            'section_filter',
            [
                'label' => esc_html__( 'Filter', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'source',
            [
                'label'   => esc_html__( 'Source', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'taxonomy',
                'options' => [
                    'taxonomy' => esc_html__( 'Taxonomy', 'acz-elements' ),
                    'items'    => esc_html__( 'Items', 'acz-elements' ),
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
            'exclude_terms',
            [
                'label'       => esc_html__( 'Exclude Terms (IDs)', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => '3,9,44',
                'description' => esc_html__( 'Enter comma separated list of term IDs to exclude.', 'acz-elements' ),
                'condition'   => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $items_repeater = new \Elementor\Repeater();
        $items_repeater->add_control(
            'item_label',
            [
                'label'       => esc_html__( 'Label', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__( 'Item', 'acz-elements' ),
            ]
        );
        $items_repeater->add_control(
            'item_value',
            [
                'label'       => esc_html__( 'Value', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'item',
            ]
        );

        $this->add_control(
            'source_items',
            [
                'label'       => esc_html__( 'Items', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $items_repeater->get_controls(),
                'title_field' => '{{{ item_label }}} ({{{ item_value }}})',
                'condition'   => [
                    'source' => 'items',
                ],
            ]
        );

        $this->add_control(
            'query_param',
            [
                'label'       => esc_html__( 'URL Parameter', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'project_filter',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'filter_label',
            [
                'label'       => esc_html__( 'Label', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'Filter Projects', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'value_type',
            [
                'label'   => esc_html__( 'Option Value', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'slug',
                'options' => [
                    'slug' => esc_html__( 'Term Slug', 'acz-elements' ),
                    'id'   => esc_html__( 'Term ID', 'acz-elements' ),
                ],
                'condition' => [
                    'source' => 'taxonomy',
                ],
            ]
        );

        $this->add_control(
            'all_option_label',
            [
                'label'       => esc_html__( 'All Option Label', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'All', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'auto_submit',
            [
                'label'        => esc_html__( 'Auto Submit On Change', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'button_label',
            [
                'label'       => esc_html__( 'Button Label', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'Apply', 'acz-elements' ),
                'label_block' => true,
                'condition'   => [
                    'auto_submit!' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_label',
            [
                'label' => esc_html__( 'Label', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-post-filter-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .acz-post-filter-label',
            ]
        );

        $this->add_responsive_control(
            'label_margin',
            [
                'label'      => esc_html__( 'Margin', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-post-filter-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'label_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-post-filter-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
            'dropdown_color',
            [
                'label'     => esc_html__( 'Text Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-post-filter-select' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dropdown_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-post-filter-select' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'dropdown_typography',
                'selector' => '{{WRAPPER}} .acz-post-filter-select',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'dropdown_border',
                'selector' => '{{WRAPPER}} .acz-post-filter-select',
            ]
        );

        $this->add_responsive_control(
            'dropdown_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-post-filter-select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .acz-post-filter-select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dropdown_focus_border_color',
            [
                'label'     => esc_html__( 'Focus Border Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-post-filter-select:focus' => 'border-color: {{VALUE}}; outline: none;',
                ],
            ]
        );

        $this->add_control(
            'dropdown_focus_shadow',
            [
                'label'     => esc_html__( 'Focus Shadow Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-post-filter-select:focus' => 'box-shadow: 0 0 0 2px {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings   = $this->get_settings_for_display();
        $source     = ( 'items' === ( $settings['source'] ?? 'taxonomy' ) ) ? 'items' : 'taxonomy';
        $taxonomy   = sanitize_key( (string) ( $settings['taxonomy'] ?? '' ) );
        $query_param = sanitize_key( (string) ( $settings['query_param'] ?? 'project_filter' ) );
        $value_type = ( 'id' === ( $settings['value_type'] ?? 'slug' ) ) ? 'id' : 'slug';
        $filter_label = (string) ( $settings['filter_label'] ?? '' );
        $all_label  = (string) ( $settings['all_option_label'] ?? esc_html__( 'All', 'acz-elements' ) );
        $auto_submit = ( 'yes' === ( $settings['auto_submit'] ?? 'yes' ) );
        $button_label = (string) ( $settings['button_label'] ?? esc_html__( 'Apply', 'acz-elements' ) );
        $select_id = 'acz-post-filter-' . $this->get_id();
        $items = ( isset( $settings['source_items'] ) && is_array( $settings['source_items'] ) ) ? $settings['source_items'] : [];

        if ( '' === $query_param ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'Set URL Parameter in ACZ Post Filter.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        $terms = [];
        if ( 'taxonomy' === $source ) {
            if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
                if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                    echo '<div class="acz-post-gallery-empty">' . esc_html__( 'Set Taxonomy in ACZ Post Filter.', 'acz-elements' ) . '</div>';
                }
                return;
            }

            $exclude_terms = array_filter( array_map( 'absint', explode( ',', (string) ( $settings['exclude_terms'] ?? '' ) ) ) );

            $terms_args = [
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ];

            if ( ! empty( $exclude_terms ) ) {
                $terms_args['exclude'] = $exclude_terms;
            }

            $terms = get_terms( $terms_args );

            if ( is_wp_error( $terms ) ) {
                return;
            }
        }

        $current_value = '';
        if ( isset( $_GET[ $query_param ] ) ) {
            $current_value = sanitize_text_field( wp_unslash( (string) $_GET[ $query_param ] ) );
        }

        $form_action = remove_query_arg( [ $query_param ] );
        ?>
        <form
            class="acz-post-filter-form"
            method="get"
            action="<?php echo esc_url( $form_action ); ?>"
            data-acz-filter-param="<?php echo esc_attr( $query_param ); ?>"
            data-acz-auto-submit="<?php echo $auto_submit ? 'yes' : 'no'; ?>"
        >
            <?php if ( '' !== trim( $filter_label ) ) : ?>
                <label class="acz-post-filter-label" for="<?php echo esc_attr( $select_id ); ?>">
                    <?php echo esc_html( $filter_label ); ?>
                </label>
            <?php endif; ?>
            <?php foreach ( $_GET as $key => $value ) : ?>
                <?php
                $safe_key = sanitize_key( (string) $key );
                if ( '' === $safe_key || $safe_key === $query_param || 0 === strpos( $safe_key, 'acz_pg_' ) ) {
                    continue;
                }

                if ( is_array( $value ) ) {
                    foreach ( $value as $nested_value ) {
                        echo '<input type="hidden" name="' . esc_attr( $safe_key ) . '[]" value="' . esc_attr( sanitize_text_field( wp_unslash( (string) $nested_value ) ) ) . '">';
                    }
                } else {
                    echo '<input type="hidden" name="' . esc_attr( $safe_key ) . '" value="' . esc_attr( sanitize_text_field( wp_unslash( (string) $value ) ) ) . '">';
                }
                ?>
            <?php endforeach; ?>

            <select
                id="<?php echo esc_attr( $select_id ); ?>"
                class="acz-post-filter-select"
                name="<?php echo esc_attr( $query_param ); ?>"
            >
                <option value=""><?php echo esc_html( $all_label ); ?></option>
                <?php if ( 'taxonomy' === $source ) : ?>
                    <?php foreach ( $terms as $term ) : ?>
                        <?php
                        $option_value = ( 'id' === $value_type ) ? (string) $term->term_id : (string) $term->slug;
                        ?>
                        <option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $current_value, $option_value ); ?>>
                            <?php echo esc_html( $term->name ); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php foreach ( $items as $item ) : ?>
                        <?php
                        if ( ! is_array( $item ) ) {
                            continue;
                        }
                        $option_value = sanitize_text_field( (string) ( $item['item_value'] ?? '' ) );
                        $option_label = (string) ( $item['item_label'] ?? '' );
                        if ( '' === $option_value && '' === trim( $option_label ) ) {
                            continue;
                        }
                        if ( '' === trim( $option_label ) ) {
                            $option_label = $option_value;
                        }
                        ?>
                        <option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $current_value, $option_value ); ?>>
                            <?php echo esc_html( $option_label ); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <?php if ( ! $auto_submit ) : ?>
                <button type="submit" class="acz-post-filter-button"><?php echo esc_html( $button_label ); ?></button>
            <?php endif; ?>
        </form>
        <?php
    }
}
