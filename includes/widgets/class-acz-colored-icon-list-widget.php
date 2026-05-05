<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * ACZ Colored Icon List Widget.
 *
 * Elementor widget that displays a list with colored icons.
 */
class ACZ_Colored_Icon_List_Widget extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'acz-colored-icon-list';
	}

	public function get_title(): string {
		return esc_html__( 'ACZ Colored Icon List', 'acz-elements' );
	}

	public function get_icon(): string {
		return 'eicon-bullet-list';
	}

	public function get_categories(): array {
		return [ 'acz' ];
	}

	public function get_keywords(): array {
		return [ 'icon', 'list', 'colored', 'acz' ];
	}

	public function get_style_depends(): array {
		return [ 'acz-common' ];
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'vertical' => [
						'title' => esc_html__( 'Vertical', 'acz-elements' ),
						'icon'  => 'eicon-editor-list-ul',
					],
					'inline'   => [
						'title' => esc_html__( 'Inline', 'acz-elements' ),
						'icon'  => 'eicon-ellipsis-h',
					],
				],
				'default' => 'vertical',
				'prefix_class' => 'acz-colored-icon-list-layout-',
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'text',
			[
				'label'       => esc_html__( 'Text', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'List Item', 'acz-elements' ),
			]
		);

		$repeater->add_control(
			'icon_class',
			[
				'label'       => esc_html__( 'Icon Class', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'ci-spotify',
				'description' => sprintf(
					/* translators: %s: Link to icons repository */
					esc_html__( 'Input the icon class name (e.g., ci-spotify). See the full list at %s.', 'acz-elements' ),
					'<a href="https://github.com/dheereshag/coloured-icons" target="_blank">coloured-icons repository</a>'
				),
				'default'     => 'ci-spotify',
			]
		);

		$repeater->add_control(
			'link',
			[
				'label'       => esc_html__( 'Link', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'acz-elements' ),
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
						'text'       => esc_html__( 'Spotify', 'acz-elements' ),
						'icon_class' => 'ci-spotify',
					],
					[
						'text'       => esc_html__( 'React', 'acz-elements' ),
						'icon_class' => 'ci-react',
					],
					[
						'text'       => esc_html__( 'Vue', 'acz-elements' ),
						'icon_class' => 'ci-vuejs',
					],
				],
				'title_field' => '{{{ text }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_list',
			[
				'label' => esc_html__( 'List', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'space_between',
			[
				'label'     => esc_html__( 'Space Between', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}}.acz-colored-icon-list-layout-vertical .acz-colored-icon-list-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.acz-colored-icon-list-layout-inline .acz-colored-icon-list-item:not(:last-child)'   => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'divider',
			[
				'label'        => esc_html__( 'Divider', 'acz-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_off'    => esc_html__( 'Off', 'acz-elements' ),
				'label_on'     => esc_html__( 'On', 'acz-elements' ),
				'selectors'    => [
					'{{WRAPPER}}.acz-colored-icon-list-layout-vertical .acz-colored-icon-list-item:not(:last-child)' => 'border-bottom: 1px solid #ccc; padding-bottom: calc({{SIZE}}{{UNIT}} / 2); margin-bottom: calc({{SIZE}}{{UNIT}} / 2);',
					'{{WRAPPER}}.acz-colored-icon-list-layout-inline .acz-colored-icon-list-item:not(:last-child)'   => 'border-right: 1px solid #ccc; padding-right: calc({{SIZE}}{{UNIT}} / 2); margin-right: calc({{SIZE}}{{UNIT}} / 2);',
				],
				'separator'    => 'before',
				'return_value' => 'yes',
				'condition'    => [],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label'     => esc_html__( 'Divider Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-colored-icon-list-item:not(:last-child)' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'divider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'vertical_align',
			[
				'label'     => esc_html__( 'Vertical Alignment', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [
						'title' => esc_html__( 'Top', 'acz-elements' ),
						'icon'  => 'eicon-v-align-top',
					],
					'center'     => [
						'title' => esc_html__( 'Middle', 'acz-elements' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'flex-end'   => [
						'title' => esc_html__( 'Bottom', 'acz-elements' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .acz-colored-icon-list-item' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => esc_html__( 'Icon', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'     => esc_html__( 'Size', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'default'   => [
					'size' => 24,
				],
				'range'     => [
					'px' => [
						'min' => 6,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-colored-icon-list-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_indent',
			[
				'label'     => esc_html__( 'Indent', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'default'   => [
					'size' => 8,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-colored-icon-list-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_text',
			[
				'label' => esc_html__( 'Text', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-colored-icon-list-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-colored-icon-list-item:hover .acz-colored-icon-list-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .acz-colored-icon-list-text',
			]
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['items'] ) ) {
			return;
		}

		echo '<div class="acz-colored-icon-list">';

		foreach ( $settings['items'] as $index => $item ) {
			$repeater_setting_key = $this->get_repeater_setting_key( 'text', 'items', $index );

			$this->add_render_attribute( $repeater_setting_key, 'class', 'acz-colored-icon-list-item' );

			$icon_class = ! empty( $item['icon_class'] ) ? $item['icon_class'] : '';

			$link_url = ! empty( $item['link']['url'] ) ? $item['link'] : '';

			if ( ! empty( $link_url ) ) {
				$this->add_link_attributes( 'link_' . $index, $item['link'] );
				$tag = 'a';
			} else {
				$tag = 'div';
			}

			echo '<' . $tag . ' ' . $this->get_render_attribute_string( $repeater_setting_key ) . ( ! empty( $link_url ) ? ' ' . $this->get_render_attribute_string( 'link_' . $index ) : '' ) . '>';

			if ( ! empty( $icon_class ) ) {
				echo '<i class="acz-colored-icon-list-icon ci ' . esc_attr( $icon_class ) . '"></i>';
			}

			echo '<span class="acz-colored-icon-list-text">' . esc_html( $item['text'] ) . '</span>';

			echo '</' . $tag . '>';
		}

		echo '</div>';
	}
}
