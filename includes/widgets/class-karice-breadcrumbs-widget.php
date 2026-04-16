<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Karice Breadcrumbs Widget.
 *
 * Elementor widget that displays breadcrumbs.
 */
class KC_Karice_Breadcrumbs_Widget extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'karice-breadcrumbs';
	}

	public function get_title(): string {
		return esc_html__( 'Karice Breadcrumbs', 'karice-elements' );
	}

	public function get_icon(): string {
		return 'eicon-product-breadcrumbs';
	}

	public function get_categories(): array {
		return [ 'karice' ];
	}

	public function get_keywords(): array {
		return [ 'breadcrumbs', 'navigation', 'karice' ];
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'karice-elements' ),
			]
		);

		$this->add_control(
			'home_label',
			[
				'label' => esc_html__( 'Home Label', 'karice-elements' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Home', 'karice-elements' ),
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => esc_html__( 'Separator', 'karice-elements' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '/',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'karice-elements' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .karice-breadcrumbs',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'karice-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .karice-breadcrumbs' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_color',
			[
				'label' => esc_html__( 'Link Color', 'karice-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .karice-breadcrumbs a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label' => esc_html__( 'Link Hover Color', 'karice-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .karice-breadcrumbs a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'separator_spacing',
			[
				'label' => esc_html__( 'Separator Spacing', 'karice-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .karice-breadcrumbs .separator' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		echo '<div class="karice-breadcrumbs">';
		
		echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $settings['home_label'] ) . '</a>';

		if ( is_singular() ) {
			$post_type = get_post_type();
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );
			$primary_taxonomy = '';

			foreach ( $taxonomies as $taxonomy ) {
				if ( $taxonomy->public && $taxonomy->hierarchical ) {
					$primary_taxonomy = $taxonomy;
					break;
				}
			}

			if ( ! $primary_taxonomy && ! empty( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {
					if ( $taxonomy->public ) {
						$primary_taxonomy = $taxonomy;
						break;
					}
				}
			}

			if ( $primary_taxonomy ) {
				$terms = get_the_terms( get_the_ID(), $primary_taxonomy->name );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					// We take the first term and show its hierarchy.
					$term = array_shift( $terms );
					$post_type_archive_link = get_post_type_archive_link( $post_type );
					$post_type_object = get_post_type_object( $post_type );
					$post_type_label = $post_type_object ? $post_type_object->label : ucfirst( $post_type );

					// Special case for 'post' type, it's often 'Blog' or similar.
					if ( 'post' === $post_type && get_option( 'page_for_posts' ) ) {
						$post_type_label = get_the_title( get_option( 'page_for_posts' ) );
					}

					echo ' <span class="separator">' . esc_html( $settings['separator'] ) . '</span> ';
					if ( $post_type_archive_link ) {
						echo '<a href="' . esc_url( $post_type_archive_link ) . '" class="post-type-archive-link"><span class="post-type-label">' . esc_html( $post_type_label ) . '</span></a>';
					} else {
						echo '<span class="post-type-label">' . esc_html( $post_type_label ) . '</span>';
					}

					// Show hierarchy of the term.
					if ( is_taxonomy_hierarchical( $primary_taxonomy->name ) ) {
						$ancestors = get_ancestors( $term->term_id, $primary_taxonomy->name );
						$ancestors = array_reverse( $ancestors );
						foreach ( $ancestors as $ancestor ) {
							$ancestor_term = get_term( $ancestor, $primary_taxonomy->name );
							if ( ! is_wp_error( $ancestor_term ) && $ancestor_term ) {
								echo ' <span class="separator">' . esc_html( $settings['separator'] ) . '</span> ';
								echo '<a href="' . esc_url( get_term_link( $ancestor_term ) ) . '">' . esc_html( $ancestor_term->name ) . '</a>';
							}
						}
					}

					echo ' <span class="separator">' . esc_html( $settings['separator'] ) . '</span> ';
					echo '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
				}
			}

			echo ' <span class="separator">' . esc_html( $settings['separator'] ) . '</span> ';
			the_title();
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$queried_object = get_queried_object();
			if ( $queried_object ) {
				$taxonomy = get_taxonomy( $queried_object->taxonomy );
				if ( $taxonomy ) {
					$post_type = ! empty( $taxonomy->object_type ) ? array_shift( $taxonomy->object_type ) : 'post';
					$post_type_archive_link = get_post_type_archive_link( $post_type );
					$post_type_object = get_post_type_object( $post_type );
					$post_type_label = $post_type_object ? $post_type_object->label : ucfirst( $post_type );

					if ( 'post' === $post_type && get_option( 'page_for_posts' ) ) {
						$post_type_label = get_the_title( get_option( 'page_for_posts' ) );
					}

					echo ' <span class="separator">' . esc_html( $settings['separator'] ) . '</span> ';
					if ( $post_type_archive_link ) {
						echo '<a href="' . esc_url( $post_type_archive_link ) . '" class="post-type-archive-link"><span class="post-type-label">' . esc_html( $post_type_label ) . '</span></a>';
					} else {
						echo '<span class="post-type-label">' . esc_html( $post_type_label ) . '</span>';
					}

					// Show hierarchy of the term if it's hierarchical.
					if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
						$ancestors = get_ancestors( $queried_object->term_id, $taxonomy->name );
						$ancestors = array_reverse( $ancestors );
						foreach ( $ancestors as $ancestor ) {
							$ancestor_term = get_term( $ancestor, $taxonomy->name );
							if ( ! is_wp_error( $ancestor_term ) && $ancestor_term ) {
								echo ' <span class="separator">' . esc_html( $settings['separator'] ) . '</span> ';
								echo '<a href="' . esc_url( get_term_link( $ancestor_term ) ) . '">' . esc_html( $ancestor_term->name ) . '</a>';
							}
						}
					}

					echo ' <span class="separator">' . esc_html( $settings['separator'] ) . '</span> ';
					echo esc_html( $queried_object->name );
				}
			}
		} elseif ( is_archive() ) {
			echo ' <span class="separator">' . esc_html( $settings['separator'] ) . '</span> ';
			the_archive_title();
		}

		echo '</div>';
	}
}
