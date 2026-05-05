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
		// Use a more universal hook for registering controls to avoid duplicates
		add_action( 'elementor/element/after_section_end', [ $this, 'on_after_section_end' ], 10, 2 );

		// Rendering
		add_action( 'elementor/element/parse_css', [ $this, 'add_post_css' ], 20, 2 );
		add_action( 'elementor/element/before_render', [ $this, 'render_custom_css' ], 20, 1 );
		add_filter( 'elementor/widget/render_content', [ $this, 'filter_widget_content' ], 20, 2 );
	}

	/**
	 * Consolidate control registration.
	 *
	 * @param \Elementor\Controls_Stack $element
	 * @param string $section_id
	 */
	public function on_after_section_end( $element, $section_id ) {
		// We want to add our section after the 'section_advanced' or '_section_style'
		if ( 'section_advanced' !== $section_id && '_section_style' !== $section_id ) {
			return;
		}

		// Only register once per element
		if ( $element->get_controls( 'acz_section_custom_css' ) ) {
			return;
		}

		$this->register_controls( $element );
	}

	/**
	 * Add custom CSS to Elementor post CSS.
	 *
	 * @param \Elementor\Core\DynamicTags\Dynamic_CSS $post_css
	 * @param \Elementor\Element_Base $element
	 */
	public function add_post_css( $post_css, $element ) {
		$custom_css = $element->get_settings( 'acz_custom_css' );

		if ( empty( $custom_css ) ) {
			return;
		}

		$id       = $element->get_id();
		$selector = '.elementor-element-' . $id;

		// Use a case-insensitive replacement for 'selector'
		$custom_css = $this->prepare_custom_css( $custom_css, $selector );

		if ( empty( $custom_css ) ) {
			return;
		}

		$post_css->get_stylesheet()->add_raw_css( $custom_css );
	}

	/**
	 * Render custom CSS for non-widget elements (Sections, Columns, Containers).
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function render_custom_css( $element ) {
		// Widgets are handled by filter_widget_content
		if ( $element instanceof \Elementor\Widget_Base ) {
			return;
		}

		$custom_css = $element->get_settings( 'acz_custom_css' );

		if ( empty( $custom_css ) ) {
			return;
		}

		$id       = $element->get_id();
		$selector = '.elementor-element-' . $id;

		$custom_css = $this->prepare_custom_css( $custom_css, $selector );

		if ( '' === $custom_css ) {
			return;
		}

		echo $this->get_custom_css_style_tag( $id, $custom_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped with wp_kses() in get_custom_css_style_tag().
	}

	/**
	 * Filter widget content to include custom CSS.
	 *
	 * @param string $content
	 * @param \Elementor\Widget_Base $widget
	 * @return string
	 */
	public function filter_widget_content( $content, $widget ) {
		$custom_css = $widget->get_settings( 'acz_custom_css' );

		if ( empty( $custom_css ) ) {
			return $content;
		}

		$id       = $widget->get_id();
		$selector = '.elementor-element-' . $id;

		$custom_css = $this->prepare_custom_css( $custom_css, $selector );

		if ( '' === $custom_css ) {
			return $content;
		}

		$style = $this->get_custom_css_style_tag( $id, $custom_css );

		return $style . $content;
	}

	private function prepare_custom_css( $custom_css, string $selector ): string {
		$custom_css = is_string( $custom_css ) ? wp_strip_all_tags( $custom_css ) : '';
		$selector   = sanitize_html_class( ltrim( $selector, '.' ) );

		if ( '' === $custom_css || '' === $selector ) {
			return '';
		}

		return (string) preg_replace( '/selector\b/i', '.' . $selector, $custom_css );
	}

	/**
	 * Build the custom CSS style tag.
	 *
	 * The CSS has already been stripped of HTML tags in prepare_custom_css().
	 *
	 * @param string $id Element ID.
	 * @param string $custom_css Prepared CSS.
	 * @return string
	 */
	private function get_custom_css_style_tag( string $id, string $custom_css ): string {
		$style_tag = sprintf(
			'<style id="%1$s">%2$s</style>',
			esc_attr( 'acz-custom-css-' . $id ),
			$custom_css
		);

		return wp_kses(
			$style_tag,
			[
				'style' => [
					'id' => true,
				],
			]
		);
	}

	/**
	 * Register Custom CSS controls.
	 *
	 * @param \Elementor\Controls_Stack $element
	 */
	public function register_controls( $element ) {
		$element->start_controls_section(
			'acz_section_custom_css',
			[
				'label' => esc_html__( 'ACZ Custom CSS', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'acz_custom_css',
			[
				'type'        => \Elementor\Controls_Manager::CODE,
				'label'       => esc_html__( 'ACZ Custom CSS', 'acz-elements' ),
				'language'    => 'css',
				'render_type' => 'template',
				'show_label'  => false,
			]
		);

		$element->add_control(
			'acz_custom_css_description',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					/* translators: %s: Example CSS snippet for the current Elementor selector. */
					esc_html__( 'Use "selector" to target the current element. Example: %s', 'acz-elements' ),
					'<br><code>selector { color: red; }</code>'
				),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$element->end_controls_section();
	}

}
