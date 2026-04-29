<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACZ_Post_Carousel_Widget extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'acz_post_carousel';
	}

	public function get_title(): string {
		return esc_html__( 'ACZ Post Carousel', 'acz-elements' );
	}

	public function get_icon(): string {
		return 'eicon-post-slider';
	}

	public function get_categories(): array {
		return [ 'acz' ];
	}

	public function get_keywords(): array {
		return [ 'post', 'carousel', 'slider', 'gallery', 'swiper' ];
	}

	public function get_style_depends(): array {
		return [ 'cec-swiper', 'cec-widget', 'acz-post-gallery' ];
	}

	public function get_script_depends(): array {
		return [ 'cec-swiper', 'cec-widget' ];
	}

	protected function register_controls(): void {
		$this->register_query_controls();
		$this->register_current_query_posts_controls();
		$this->register_carousel_settings_controls();
		$this->register_layout_controls();
		$this->register_content_controls();
		$this->register_style_controls();
	}

	private function get_post_type_options(): array {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$options    = [];
		foreach ( $post_types as $post_type ) {
			$options[ $post_type->name ] = $post_type->label;
		}
		return $options;
	}

	private function get_author_options(): array {
		$authors = get_users( [ 'capability__in' => [ 'edit_posts' ] ] );
		$options = [];
		foreach ( $authors as $author ) {
			$options[ $author->ID ] = $author->display_name;
		}
		return $options;
	}

	private function get_post_options(): array {
		$options = [];
		$posts   = get_posts(
			[
				'post_type'      => 'any',
				'posts_per_page' => 500,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		foreach ( $posts as $post ) {
			$options[ $post->ID ] = $post->post_title . ' (' . $post->ID . ') - [' . $post->post_type . ']';
		}

		return $options;
	}

	private function get_term_options(): array {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$options    = [];
		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_terms( [
				'taxonomy'   => $taxonomy->name,
				'hide_empty' => false,
			] );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$options[ $term->term_id ] = $taxonomy->label . ': ' . $term->name;
				}
			}
		}
		return $options;
	}

	private function get_taxonomy_options(): array {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$options    = [];
		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}
		return $options;
	}

	private function register_query_controls(): void {
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Query', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'posts_source',
			[
				'label'   => esc_html__( 'Source', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'custom_posts',
				'options' => [
					'custom_posts'  => esc_html__( 'Custom Posts', 'acz-elements' ),
					'taxonomies'    => esc_html__( 'Taxonomies', 'acz-elements' ),
					'current_query' => esc_html__( 'Current Query', 'acz-elements' ),
				],
			]
		);

		$this->add_control(
			'post_types',
			[
				'label'       => esc_html__( 'Post Types', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'default'     => [ 'post' ],
				'options'     => $this->get_post_type_options(),
				'condition'   => [
					'posts_source!' => 'taxonomies',
				],
				'label_block' => true,
			]
		);

		$this->add_control(
			'include_post_ids',
			[
				'label'       => esc_html__( 'Include Post IDs', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => [], // Loaded via AJAX ideally, but for now empty or standard
				'label_block' => true,
				'condition'   => [
					'posts_source' => 'custom_posts',
				],
			]
		);

		$this->add_control(
			'exclude_post_ids',
			[
				'label'       => esc_html__( 'Exclude Post IDs', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => [],
				'label_block' => true,
				'condition'   => [
					'posts_source' => 'custom_posts',
				],
			]
		);

		$this->add_control(
			'include_authors',
			[
				'label'       => esc_html__( 'Authors', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_author_options(),
				'label_block' => true,
				'condition'   => [
					'posts_source' => 'custom_posts',
				],
			]
		);

		$this->add_control(
			'include_terms',
			[
				'label'       => esc_html__( 'Include Terms', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_term_options(),
				'label_block' => true,
				'condition'   => [
					'posts_source' => 'custom_posts',
				],
			]
		);

		$this->add_control(
			'source_taxonomy',
			[
				'label'     => esc_html__( 'Taxonomy', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => $this->get_taxonomy_options(),
				'condition' => [
					'posts_source' => 'taxonomies',
				],
			]
		);

		$this->add_control(
			'max_posts',
			[
				'label'   => esc_html__( 'Max Posts', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 10,
			]
		);

		$this->add_control(
			'offset',
			[
				'label'   => esc_html__( 'Offset', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 0,
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'   => esc_html__( 'Order By', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date'          => esc_html__( 'Date', 'acz-elements' ),
					'title'         => esc_html__( 'Title', 'acz-elements' ),
					'ID'            => esc_html__( 'ID', 'acz-elements' ),
					'rand'          => esc_html__( 'Random', 'acz-elements' ),
					'menu_order'    => esc_html__( 'Menu Order', 'acz-elements' ),
					'comment_count' => esc_html__( 'Comment Count', 'acz-elements' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => esc_html__( 'Order', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC'  => esc_html__( 'Ascending', 'acz-elements' ),
					'DESC' => esc_html__( 'Descending', 'acz-elements' ),
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_current_query_posts_controls(): void {
		$this->start_controls_section(
			'section_current_query_posts',
			[
				'label'     => esc_html__( 'Current Query Posts', 'acz-elements' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'posts_source' => 'current_query',
				],
			]
		);

		$this->add_control(
			'current_query_posts_mode',
			[
				'label'   => esc_html__( 'Mode', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'query',
				'options' => [
					'query'   => esc_html__( 'Current Query', 'acz-elements' ),
					'related' => esc_html__( 'Related Posts', 'acz-elements' ),
					'custom'  => esc_html__( 'Custom Posts', 'acz-elements' ),
				],
			]
		);

		$this->add_control(
			'current_query_related_taxonomies',
			[
				'label'       => esc_html__( 'Related By Taxonomies', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_taxonomy_options(),
				'label_block' => true,
				'condition'   => [
					'current_query_posts_mode' => 'related',
				],
			]
		);

		$this->add_control(
			'current_query_related_acf_taxonomy_field',
			[
				'label'       => esc_html__( 'ACF Taxonomy Field Key', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'project_fixtures',
				'description' => esc_html__( 'When current query is a taxonomy term archive, match posts where this ACF taxonomy field includes that term.', 'acz-elements' ),
				'condition'   => [
					'current_query_posts_mode' => 'related',
				],
			]
		);

		$this->add_control(
			'current_query_custom_post_ids',
			[
				'label'       => esc_html__( 'Select Custom Posts', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_post_options(),
				'label_block' => true,
				'condition'   => [
					'current_query_posts_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'current_query_exclude_current_post',
			[
				'label'        => esc_html__( 'Exclude Current Post', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'current_query_posts_mode!' => 'query',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_carousel_settings_controls(): void {
		$this->start_controls_section(
			'section_carousel_settings',
			[
				'label' => esc_html__( 'Carousel Settings', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'effect',
			[
				'label'   => esc_html__( 'Effect', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide'     => esc_html__( 'Slide', 'acz-elements' ),
					'fade'      => esc_html__( 'Fade', 'acz-elements' ),
					'cube'      => esc_html__( 'Cube', 'acz-elements' ),
					'coverflow' => esc_html__( 'Coverflow', 'acz-elements' ),
					'flip'      => esc_html__( 'Flip', 'acz-elements' ),
					'cards'     => esc_html__( 'Cards', 'acz-elements' ),
					'creative'  => esc_html__( 'Creative', 'acz-elements' ),
				],
			]
		);

		$this->add_responsive_control(
			'slides_per_view',
			[
				'label'   => esc_html__( 'Slides Per View', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 6,
			]
		);

		$this->add_responsive_control(
			'space_between',
			[
				'label'   => esc_html__( 'Space Between', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 24,
				'min'     => 0,
				'max'     => 100,
			]
		);

		$this->add_control(
			'speed',
			[
				'label'   => esc_html__( 'Transition Speed (ms)', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 600,
				'min'     => 100,
				'max'     => 5000,
				'step'    => 50,
			]
		);

		$this->add_control(
			'loop',
			[
				'label'        => esc_html__( 'Loop', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => esc_html__( 'Autoplay', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'autoplay_delay',
			[
				'label'     => esc_html__( 'Autoplay Delay (ms)', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 3000,
				'min'       => 500,
				'max'       => 10000,
				'step'      => 100,
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'        => esc_html__( 'Pause on Hover', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_arrows',
			[
				'label'        => esc_html__( 'Show Arrows', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label'        => esc_html__( 'Show Pagination', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();
	}

	private function register_layout_controls(): void {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid'    => esc_html__( 'Standard', 'acz-elements' ),
					'overlay' => esc_html__( 'Overlay', 'acz-elements' ),
				],
				'prefix_class' => 'acz-layout-',
			]
		);

		$this->add_control(
			'content_position',
			[
				'label'   => esc_html__( 'Content Position', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'bottom',
				'options' => [
					'top'    => esc_html__( 'Top', 'acz-elements' ),
					'middle' => esc_html__( 'Middle', 'acz-elements' ),
					'bottom' => esc_html__( 'Bottom', 'acz-elements' ),
				],
				'condition' => [
					'layout' => 'overlay',
				],
				'prefix_class' => 'acz-content-pos-',
			]
		);

		$this->end_controls_section();
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
			'show_image',
			[
				'label'        => esc_html__( 'Show Image', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'acz-elements' ),
				'label_off'    => esc_html__( 'Hide', 'acz-elements' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'        => esc_html__( 'Show Title', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'acz-elements' ),
				'label_off'    => esc_html__( 'Hide', 'acz-elements' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'        => esc_html__( 'Show Excerpt', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'acz-elements' ),
				'label_off'    => esc_html__( 'Hide', 'acz-elements' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label'     => esc_html__( 'Excerpt Length', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 20,
				'condition' => [
					'show_excerpt' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_custom_meta_line',
			[
				'label'        => esc_html__( 'Show Custom Meta Line', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'acz-elements' ),
				'label_off'    => esc_html__( 'Hide', 'acz-elements' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'custom_meta_format',
			[
				'label'       => esc_html__( 'Meta Format', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '%location% - %partner%',
				'placeholder' => '%location% - %partner%',
				'description' => esc_html__( 'Use meta keys wrapped in %. Example: %location% - %partner%', 'acz-elements' ),
				'condition'   => [
					'show_custom_meta_line' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_button',
			[
				'label'        => esc_html__( 'Show Button', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'acz-elements' ),
				'label_off'    => esc_html__( 'Hide', 'acz-elements' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'     => esc_html__( 'Button Text', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'View Project', 'acz-elements' ),
				'condition' => [
					'show_button' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_controls(): void {
		// Image Styles
		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Image', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_image' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label' => esc_html__( 'Height', 'acz-elements' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', 'em' ],
				'range' => [
					'px' => [ 'min' => 100, 'max' => 1000 ],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-card-media img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_object_fit',
			[
				'label' => esc_html__( 'Object Fit', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'fill' => esc_html__( 'Fill', 'acz-elements' ),
					'cover' => esc_html__( 'Cover', 'acz-elements' ),
					'contain' => esc_html__( 'Contain', 'acz-elements' ),
				],
				'default' => 'cover',
				'selectors' => [
					'{{WRAPPER}} .acz-post-card-media img' => 'object-fit: {{VALUE}};',
				],
				'condition' => [
					'image_height[size]!' => '',
				],
			]
		);

		$this->end_controls_section();

		// Card Style
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => esc_html__( 'Card', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'card_background',
				'label'    => esc_html__( 'Background', 'acz-elements' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .acz-post-card',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'label'    => esc_html__( 'Border', 'acz-elements' ),
				'selector' => '{{WRAPPER}} .acz-post-card',
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .acz-post-card',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Padding', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'card_margin',
			[
				'label'      => esc_html__( 'Margin', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Title Style
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Title', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'title_alignment',
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
					'{{WRAPPER}} .acz-post-card-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-card-title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .acz-post-card-title',
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label'      => esc_html__( 'Margin', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__( 'Padding', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Custom Meta Line Style
		$this->start_controls_section(
			'section_style_custom_meta',
			[
				'label' => esc_html__( 'Custom Meta Line', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_custom_meta_line' => 'yes',
				],
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
					'{{WRAPPER}} .acz-post-card-custom-meta' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'custom_meta_color',
			[
				'label'     => esc_html__( 'Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-card-custom-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'custom_meta_typography',
				'selector' => '{{WRAPPER}} .acz-post-card-custom-meta',
			]
		);

		$this->add_responsive_control(
			'custom_meta_margin',
			[
				'label'      => esc_html__( 'Margin', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card-custom-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .acz-post-card-custom-meta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Excerpt Style
		$this->start_controls_section(
			'section_style_excerpt',
			[
				'label' => esc_html__( 'Excerpt', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_excerpt' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'excerpt_alignment',
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
					'justify' => [
						'title' => esc_html__( 'Justified', 'acz-elements' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-card-excerpt' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'excerpt_color',
			[
				'label'     => esc_html__( 'Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-card-excerpt' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .acz-post-card-excerpt',
			]
		);

		$this->add_responsive_control(
			'excerpt_margin',
			[
				'label'      => esc_html__( 'Margin', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'excerpt_padding',
			[
				'label'      => esc_html__( 'Padding', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card-excerpt' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Button Style
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Button', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_button' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'button_alignment',
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
					'{{WRAPPER}} .acz-post-card-button-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .acz-post-card-button',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'acz-elements' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-card-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'button_background',
				'label'    => esc_html__( 'Background', 'acz-elements' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .acz-post-card-button',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'label'    => esc_html__( 'Border', 'acz-elements' ),
				'selector' => '{{WRAPPER}} .acz-post-card-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'acz-elements' ),
			]
		);

		$this->add_control(
			'button_text_color_hover',
			[
				'label'     => esc_html__( 'Text Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-card-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'button_background_hover',
				'label'    => esc_html__( 'Background', 'acz-elements' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .acz-post-card-button:hover',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'button_border_hover',
				'label'    => esc_html__( 'Border', 'acz-elements' ),
				'selector' => '{{WRAPPER}} .acz-post-card-button:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => esc_html__( 'Padding', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'label'      => esc_html__( 'Margin', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-post-card-button-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Navigation Style
		$this->start_controls_section(
			'section_style_navigation',
			[
				'label' => esc_html__( 'Navigation', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_size',
			[
				'label' => esc_html__( 'Size', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 24, 'max' => 120 ],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 8, 'max' => 48 ],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev::after, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next::after' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_offset_horizontal',
			[
				'label' => esc_html__( 'Horizontal Offset', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => -80, 'max' => 80 ],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-next' => 'right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_offset_vertical',
			[
				'label' => esc_html__( 'Vertical Offset', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => -200, 'max' => 200 ],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next' => 'top: calc(50% + {{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_navigation_style' );

		$this->start_controls_tab(
			'tab_navigation_normal',
			[
				'label' => esc_html__( 'Normal', 'acz-elements' ),
			]
		);

		$this->add_control(
			'navigation_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_background_color',
			[
				'label' => esc_html__( 'Background Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_border_color',
			[
				'label' => esc_html__( 'Border Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_navigation_hover',
			[
				'label' => esc_html__( 'Hover', 'acz-elements' ),
			]
		);

		$this->add_control(
			'navigation_icon_color_hover',
			[
				'label' => esc_html__( 'Icon Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev:hover, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_background_color_hover',
			[
				'label' => esc_html__( 'Background Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev:hover, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev:hover, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'navigation_border',
				'selector' => '{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'navigation_box_shadow',
				'selector' => '{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next',
			]
		);

		$this->add_responsive_control(
			'navigation_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-button-prev, {{WRAPPER}} .acz-post-carousel-widget .swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Pagination Style
		$this->start_controls_section(
			'section_style_pagination',
			[
				'label' => esc_html__( 'Pagination', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_pagination' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_spacing_top',
			[
				'label' => esc_html__( 'Top Spacing', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 120 ],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-pagination' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_bullet_size',
			[
				'label' => esc_html__( 'Bullet Size', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 4, 'max' => 32 ],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_bullet_gap',
			[
				'label' => esc_html__( 'Bullet Gap', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 40 ],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-pagination' => 'display: flex; justify-content: center; gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-pagination-bullet' => 'margin: 0 !important;',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_alignment',
			[
				'label' => esc_html__( 'Alignment', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Left', 'acz-elements' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'acz-elements' ),
						'icon'  => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Right', 'acz-elements' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-pagination' => 'display: flex; justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pagination_bullet_color',
			[
				'label' => esc_html__( 'Bullet Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-pagination-bullet' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pagination_bullet_active_color',
			[
				'label' => esc_html__( 'Active Bullet Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-pagination-bullet-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_bullet_border_radius',
			[
				'label' => esc_html__( 'Bullet Border Radius', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .acz-post-carousel-widget .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function trim_words( string $text, int $word_count ): string {
		return wp_trim_words( $text, $word_count );
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

	private function normalize_id_array( $values ): array {
		if ( empty( $values ) ) {
			return [];
		}
		if ( is_string( $values ) ) {
			return array_filter( array_map( 'absint', explode( ',', $values ) ) );
		}
		if ( is_array( $values ) ) {
			return array_filter( array_map( 'absint', $values ) );
		}
		return [];
	}

	private function normalize_post_type_array( $values ): array {
		if ( empty( $values ) ) {
			return [];
		}

		if ( is_string( $values ) ) {
			$values = explode( ',', $values );
		}

		if ( ! is_array( $values ) ) {
			return [];
		}

		$post_types = array_map( 'sanitize_key', $values );
		$post_types = array_filter(
			$post_types,
			static function ( string $post_type ): bool {
				return '' !== $post_type && post_type_exists( $post_type );
			}
		);

		return array_values( array_unique( $post_types ) );
	}

	private function clear_singular_query_constraints( array &$query_args, string $post_type = '' ): void {
		$singular_keys = [
			'name',
			'p',
			'page_id',
			'pagename',
			'attachment',
			'attachment_id',
			'subpost',
			'subpost_id',
			'preview',
			'page',
			'paged',
			'wc_query',
		];

		foreach ( $singular_keys as $key ) {
			unset( $query_args[ $key ] );
		}

		if ( '' !== $post_type ) {
			unset( $query_args[ $post_type ] );
		}
	}

	private function clear_taxonomy_query_constraints( array &$query_args, string $taxonomy = '' ): void {
		$taxonomy_keys = [
			'taxonomy',
			'term',
			'term_id',
			'cat',
			'category_name',
			'tag',
			'tag_id',
			'tag__in',
			'tag__not_in',
			'tag_slug__in',
			'tag_slug__and',
			'tax_query',
			'wc_query',
		];

		foreach ( $taxonomy_keys as $key ) {
			unset( $query_args[ $key ] );
		}

		if ( '' !== $taxonomy ) {
			unset( $query_args[ $taxonomy ] );
		}
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$related_query_debug_message = '';

		$config = [
			'effect'                => $settings['effect'] ?? 'slide',
			'slidesPerView'         => max( 1, (int) ( $settings['slides_per_view'] ?? 3 ) ),
			'slidesPerViewTablet'   => max( 1, (int) ( $settings['slides_per_view_tablet'] ?? 2 ) ),
			'slidesPerViewMobile'   => max( 1, (int) ( $settings['slides_per_view_mobile'] ?? 1 ) ),
			'spaceBetween'          => (int) ( $settings['space_between'] ?? 24 ),
			'spaceBetweenTablet'    => (int) ( $settings['space_between_tablet'] ?? 20 ),
			'spaceBetweenMobile'    => (int) ( $settings['space_between_mobile'] ?? 16 ),
			'speed'                 => max( 100, (int) ( $settings['speed'] ?? 600 ) ),
			'loop'                  => ( 'yes' === ( $settings['loop'] ?? 'yes' ) ),
			'autoplay'              => ( 'yes' === ( $settings['autoplay'] ?? '' ) ),
			'autoplayDelay'         => max( 500, (int) ( $settings['autoplay_delay'] ?? 3000 ) ),
			'pauseOnHover'          => ( 'yes' === ( $settings['pause_on_hover'] ?? 'yes' ) ),
			'showArrows'            => ( 'yes' === ( $settings['show_arrows'] ?? 'yes' ) ),
			'showPagination'        => ( 'yes' === ( $settings['show_pagination'] ?? 'yes' ) ),
		];

		$query_args = [
			'post_status'         => 'publish',
			'posts_per_page'      => (int) ( $settings['max_posts'] ?? 10 ),
			'offset'              => (int) ( $settings['offset'] ?? 0 ),
			'orderby'             => $settings['orderby'] ?? 'date',
			'order'               => $settings['order'] ?? 'DESC',
			'ignore_sticky_posts' => true,
		];

		$posts_source = $settings['posts_source'] ?? 'custom_posts';

		if ( 'taxonomies' === $posts_source ) {
			$taxonomy = $settings['source_taxonomy'] ?? '';
			if ( ! empty( $taxonomy ) ) {
				$query_args['tax_query'] = [
					[
						'taxonomy' => $taxonomy,
						'operator' => 'EXISTS',
					],
				];
			}
		} elseif ( 'current_query' === $posts_source ) {
			global $wp_query;
			if ( $wp_query ) {
				$query_args = array_merge( $wp_query->query_vars, $query_args );
			}

			$current_query_mode = $settings['current_query_posts_mode'] ?? 'query';
			$current_post_id    = absint( get_queried_object_id() );

			if ( 'related' === $current_query_mode && $current_post_id > 0 ) {
				$current_object = get_queried_object();
				$current_term   = ( $current_object instanceof \WP_Term ) ? $current_object : null;
				$current_post   = ( $current_object instanceof \WP_Post ) ? $current_object : null;

				if ( ! ( $current_post instanceof \WP_Post ) && ! ( $current_term instanceof \WP_Term ) ) {
					$current_post = get_post( $current_post_id );
				}

				$selected_post_types = $this->normalize_post_type_array( $settings['post_types'] ?? [] );

				if ( empty( $selected_post_types ) ) {
					$selected_post_types = ( $current_post instanceof \WP_Post )
						? [ $current_post->post_type ]
						: [ 'post' ];
				}

				$query_args['post_type'] = ( 1 === count( $selected_post_types ) )
					? $selected_post_types[0]
					: $selected_post_types;

				if ( $current_term instanceof \WP_Term ) {
					$this->clear_singular_query_constraints( $query_args, (string) ( $query_args['post_type'] ?? '' ) );
					$this->clear_taxonomy_query_constraints( $query_args, $current_term->taxonomy );

					$acf_taxonomy_field = sanitize_key( (string) ( $settings['current_query_related_acf_taxonomy_field'] ?? '' ) );
					if ( '' !== $acf_taxonomy_field ) {
						$query_args['meta_query'] = [
							'relation' => 'OR',
							[
								'key'     => $acf_taxonomy_field,
								'value'   => '"' . $current_term->term_id . '"',
								'compare' => 'LIKE',
							],
							[
								'key'     => $acf_taxonomy_field,
								'value'   => (string) $current_term->term_id,
								'compare' => '=',
							],
							[
								'key'     => $acf_taxonomy_field,
								'value'   => 'i:' . $current_term->term_id . ';',
								'compare' => 'LIKE',
							],
						];
					}
				} elseif ( $current_post instanceof \WP_Post ) {
					$this->clear_singular_query_constraints( $query_args, $current_post->post_type );

					$selected_taxonomies = $settings['current_query_related_taxonomies'] ?? [];
					if ( ! is_array( $selected_taxonomies ) || empty( $selected_taxonomies ) ) {
						$selected_taxonomies = get_object_taxonomies( $current_post->post_type, 'names' );
					}

					$tax_query = [ 'relation' => 'OR' ];
					foreach ( $selected_taxonomies as $taxonomy ) {
						if ( ! taxonomy_exists( $taxonomy ) ) {
							continue;
						}

						$term_tt_ids = wp_get_post_terms( $current_post_id, $taxonomy, [ 'fields' => 'tt_ids' ] );
						if ( is_wp_error( $term_tt_ids ) || empty( $term_tt_ids ) ) {
							continue;
						}

						$tax_query[] = [
							'taxonomy'         => $taxonomy,
							'field'            => 'term_taxonomy_id',
							'terms'            => array_map( 'absint', $term_tt_ids ),
							'include_children' => false,
						];
					}

					if ( count( $tax_query ) > 1 ) {
						$query_args['tax_query'] = $tax_query;
					}
				}

				if ( 'yes' === ( $settings['current_query_exclude_current_post'] ?? 'yes' ) ) {
					$query_args['post__not_in'] = array_values(
						array_unique(
							array_filter(
								array_merge(
									$this->normalize_id_array( $query_args['post__not_in'] ?? [] ),
									[ $current_post_id ]
								)
							)
						)
					);
				}

				$related_query_debug_message = wp_json_encode( $query_args );
			} elseif ( 'custom' === $current_query_mode ) {
				$this->clear_singular_query_constraints( $query_args, (string) ( $query_args['post_type'] ?? '' ) );
				$custom_post_ids = $this->normalize_id_array( $settings['current_query_custom_post_ids'] ?? [] );

				if ( 'yes' === ( $settings['current_query_exclude_current_post'] ?? 'yes' ) && $current_post_id > 0 ) {
					$custom_post_ids = array_values( array_diff( $custom_post_ids, [ $current_post_id ] ) );
				}

				if ( ! empty( $custom_post_ids ) ) {
					$query_args['post_type']      = 'any';
					$query_args['post__in']       = $custom_post_ids;
					$query_args['orderby']        = 'post__in';
					$query_args['posts_per_page'] = min( (int) ( $settings['max_posts'] ?? 10 ), count( $custom_post_ids ) );
				} else {
					$query_args['post__in'] = [ 0 ];
				}
                $related_query_debug_message = wp_json_encode( $query_args );
			}
		} else {
			$selected_post_types = $this->normalize_post_type_array( $settings['post_types'] ?? [] );
			$query_args['post_type'] = ! empty( $selected_post_types ) ? $selected_post_types : [ 'post' ];
			
			$include = $this->normalize_id_array( $settings['include_post_ids'] ?? [] );
			if ( ! empty( $include ) ) {
				$query_args['post__in'] = $include;
			}

			$exclude = $this->normalize_id_array( $settings['exclude_post_ids'] ?? [] );
			if ( ! empty( $exclude ) ) {
				$query_args['post__not_in'] = $exclude;
			}

			$authors = $this->normalize_id_array( $settings['include_authors'] ?? [] );
			if ( ! empty( $authors ) ) {
				$query_args['author__in'] = $authors;
			}

			$terms = $this->normalize_id_array( $settings['include_terms'] ?? [] );
			if ( ! empty( $terms ) ) {
				$tax_query = [ 'relation' => 'OR' ];
				$terms_by_tax = [];
				foreach ( $terms as $term_id ) {
					$term = get_term( $term_id );
					if ( $term && ! is_wp_error( $term ) ) {
						$terms_by_tax[ $term->taxonomy ][] = $term->term_id;
					}
				}
				foreach ( $terms_by_tax as $tax => $ids ) {
					$tax_query[] = [
						'taxonomy' => $tax,
						'field'    => 'term_id',
						'terms'    => $ids,
					];
				}
				$query_args['tax_query'] = $tax_query;
			}

            $related_query_debug_message = wp_json_encode( $query_args );
		}

		$query = new \WP_Query( $query_args );

		/*if ( '' !== $related_query_debug_message
                && \Elementor\Plugin::$instance->editor->is_edit_mode()
        ) {

			echo '<div style="color: red;" class="acz-post-carousel-debug"><strong>' . esc_html__( 'Related Query Args:', 'acz-elements' ) . '</strong><pre>' . esc_html( $related_query_debug_message ) . '</pre></div>';
		}*/

		if ( ! $query->have_posts() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="acz-post-gallery-empty">' . esc_html__( 'No posts found.', 'acz-elements' ) . '</div>';
			}
			return;
		}

		$wrapper_id = 'acz-post-carousel-' . $this->get_id();
		$layout = $settings['layout'] ?? 'grid';
		?>
		<div id="<?php echo esc_attr( $wrapper_id ); ?>" class="cec-effects-carousel acz-post-carousel-widget acz-layout-<?php echo esc_attr( $layout ); ?>" data-cec-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<div class="swiper">
				<div class="swiper-wrapper">
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<div class="swiper-slide">
							<article class="acz-post-card">
								<?php
								$media_html = '';
								if ( 'yes' === ( $settings['show_image'] ?? 'yes' ) && has_post_thumbnail() ) {
									$media_html .= '<a class="acz-post-card-media" href="' . esc_url( get_permalink() ) . '">';
									$media_html .= get_the_post_thumbnail( null, 'large', [ 'loading' => 'lazy' ] );
									$media_html .= '</a>';
								}

								if ( 'overlay' !== $layout ) {
									echo $media_html;
								}
								?>

								<div class="acz-post-card-body">
									<?php if ( 'yes' === ( $settings['show_title'] ?? 'yes' ) ) : ?>
										<h3 class="acz-post-card-title">
											<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
										</h3>
									<?php endif; ?>

									<?php
									if ( 'yes' === ( $settings['show_custom_meta_line'] ?? 'yes' ) ) {
										$meta_line = $this->build_custom_meta_line(
											(int) get_the_ID(),
											(string) ( $settings['custom_meta_format'] ?? '%location% - %partner%' )
										);
										if ( '' !== $meta_line ) {
											echo '<div class="acz-post-card-custom-meta">' . esc_html( $meta_line ) . '</div>';
										}
									}
									?>

									<?php if ( 'yes' === ( $settings['show_excerpt'] ?? 'yes' ) ) : ?>
										<div class="acz-post-card-excerpt">
											<?php echo esc_html( $this->trim_words( get_the_excerpt(), (int) ( $settings['excerpt_length'] ?? 20 ) ) ); ?>
										</div>
									<?php endif; ?>

									<?php if ( 'yes' === ( $settings['show_button'] ?? '' ) ) : ?>
										<div class="acz-post-card-button-wrapper">
											<a href="<?php echo esc_url( get_permalink() ); ?>" class="acz-post-card-button">
												<span class="acz-post-card-button-text"><?php echo esc_html( $settings['button_text'] ); ?></span>
											</a>
										</div>
									<?php endif; ?>
								</div>

								<?php
								if ( 'overlay' === $layout ) {
									echo $media_html;
								}
								?>
							</article>
						</div>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>

				<?php if ( 'yes' === ( $settings['show_arrows'] ?? 'yes' ) ) : ?>
					<div class="swiper-button-prev"></div>
					<div class="swiper-button-next"></div>
				<?php endif; ?>

				<?php if ( 'yes' === ( $settings['show_pagination'] ?? 'yes' ) ) : ?>
					<div class="swiper-pagination"></div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
