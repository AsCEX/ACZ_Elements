<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Taxonomy_List_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'acz_taxonomy_list';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Taxonomy List', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-bullet-list';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'taxonomy', 'list', 'terms', 'categories', 'acz' ];
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
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Content', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'taxonomy',
            [
                'label'       => esc_html__( 'Taxonomy', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $this->get_taxonomy_options(),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => esc_html__( 'Order By', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name'  => esc_html__( 'Name', 'acz-elements' ),
                    'slug'  => esc_html__( 'Slug', 'acz-elements' ),
                    'count' => esc_html__( 'Count', 'acz-elements' ),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => esc_html__( 'Order', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => [
                    'ASC'  => esc_html__( 'Ascending', 'acz-elements' ),
                    'DESC' => esc_html__( 'Descending', 'acz-elements' ),
                ],
            ]
        );

        $this->add_control(
            'hide_empty',
            [
                'label'        => esc_html__( 'Hide Empty', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_count',
            [
                'label'        => esc_html__( 'Show Count', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Style', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => esc_html__( 'Text Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-taxonomy-list-item' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'text_typography',
                'selector' => '{{WRAPPER}} .acz-taxonomy-list-item',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings   = $this->get_settings_for_display();
        $taxonomy   = sanitize_key( (string) ( $settings['taxonomy'] ?? '' ) );
        $orderby    = (string) ( $settings['orderby'] ?? 'name' );
        $order      = (string) ( $settings['order'] ?? 'ASC' );
        $hide_empty = ( 'yes' === ( $settings['hide_empty'] ?? 'yes' ) );
        $show_count = ( 'yes' === ( $settings['show_count'] ?? 'no' ) );

        if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'Set Taxonomy in ACZ Taxonomy List.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        $terms = get_terms(
            [
                'taxonomy'   => $taxonomy,
                'hide_empty' => $hide_empty,
                'orderby'    => $orderby,
                'order'      => $order,
            ]
        );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'No terms found.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        ?>
        <div class="acz-taxonomy-list-widget">
            <ul class="acz-taxonomy-list">
                <?php foreach ( $terms as $term ) : ?>
                    <li class="acz-taxonomy-list-item">
                        <a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
                            <?php echo esc_html( $term->name ); ?>
                            <?php if ( $show_count ) : ?>
                                <span class="acz-taxonomy-list-count">(<?php echo esc_html( (string) (int) $term->count ); ?>)</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
}
