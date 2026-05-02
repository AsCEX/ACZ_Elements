<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ACZ_Custom_CSS
 *
 * Adds Custom CSS functionality to Elementor widgets.
 */
class ACZ_Custom_CSS {

	public function __construct() {
		add_action( 'elementor/element/common/section_advanced/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/container/section_advanced/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/frontend/element/before_render', [ $this, 'enqueue_custom_css' ], 10, 1 );
	}

	/**
	 * Register Custom CSS controls.
	 *
	 * @param \Elementor\Controls_Stack $element
	 * @param array $args
	 */
	public function register_controls( $element, $args ) {
		$element->start_controls_section(
			'acz_section_custom_css',
			[
				'label' => esc_html__( 'Custom CSS', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'acz_custom_css',
			[
				'type'        => \Elementor\Controls_Manager::CODE,
				'label'       => esc_html__( 'Custom CSS', 'acz-elements' ),
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
			]
		);

		$element->add_control(
			'acz_custom_css_description',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					esc_html__( 'Use "selector" to target the current element. Example: %s', 'acz-elements' ),
					'<br><code>selector { color: red; }</code>'
				),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Enqueue or render custom CSS.
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function enqueue_custom_css( $element ) {
		$custom_css = $element->get_settings_for_display( 'acz_custom_css' );

		if ( empty( $custom_css ) ) {
			return;
		}

		$id       = $element->get_id();
		$selector = '.elementor-element-' . $id;

		if ( $element->get_name() === 'section' || $element->get_name() === 'column' || $element->get_name() === 'container' ) {
			// Some elements might need specific selector adjustments if needed
		}

		$custom_css = str_replace( 'selector', $selector, $custom_css );

		echo '<style id="acz-custom-css-' . esc_attr( $id ) . '">' . $custom_css . '</style>';
	}
}
