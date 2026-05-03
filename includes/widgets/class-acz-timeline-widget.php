<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * ACZ Timeline Widget.
 *
 * Elementor widget that displays a timeline.
 */
class ACZ_Timeline_Widget extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'acz-timeline';
	}

	public function get_title(): string {
		return esc_html__( 'ACZ Timeline', 'acz-elements' );
	}

	public function get_icon(): string {
		return 'eicon-post-list';
	}

	public function get_categories(): array {
		return [ 'acz' ];
	}

	public function get_keywords(): array {
		return [ 'timeline', 'history', 'acz' ];
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

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Timeline Title', 'acz-elements' ),
			]
		);

		$repeater->add_control(
			'company',
			[
				'label'       => esc_html__( 'Company', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Company Name', 'acz-elements' ),
			]
		);

		$repeater->add_control(
			'location',
			[
				'label'       => esc_html__( 'Location', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Location', 'acz-elements' ),
			]
		);

		$repeater->add_control(
			'employment',
			[
				'label'       => esc_html__( 'Employment', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Employment Type', 'acz-elements' ),
			]
		);

		$repeater->add_control(
			'date',
			[
				'label'       => esc_html__( 'Date', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'May 2026 - Present', 'acz-elements' ),
			]
		);

		$repeater->add_control(
			'content',
			[
				'label'   => esc_html__( 'Content', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Timeline content description goes here.', 'acz-elements' ),
			]
		);

		$this->add_control(
			'items',
			[
				'label'       => esc_html__( 'Timeline Items', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'title'   => esc_html__( 'Job Title', 'acz-elements' ),
						'company' => esc_html__( 'Company Name', 'acz-elements' ),
						'date'    => esc_html__( '2024 - Present', 'acz-elements' ),
					],
				],
				'title_field' => '{{{ title }}}',
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

		$this->add_responsive_control(
			'item_margin',
			[
				'label'      => esc_html__( 'Margin', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-timeline-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Padding', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-timeline-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} .acz-timeline-item',
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .acz-timeline-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_line',
			[
				'label' => esc_html__( 'Line', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'line_color',
			[
				'label'     => esc_html__( 'Line Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline::before' => 'background-color: {{VALUE}};',
				],
				'default'   => '#d1d5db',
			]
		);

		$this->add_responsive_control(
			'line_width',
			[
				'label'     => esc_html__( 'Line Width', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-timeline::before' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .acz-timeline'         => 'padding-left: calc(20px + ({{SIZE}}{{UNIT}} / 2) - 1px);',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_dot',
			[
				'label' => esc_html__( 'Dot', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'dot_color',
			[
				'label'     => esc_html__( 'Dot Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-item::before' => 'background-color: {{VALUE}};',
				],
				'default'   => '#111827',
			]
		);

		$this->add_responsive_control(
			'dot_size',
			[
				'label'     => esc_html__( 'Dot Size', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 4,
						'max' => 40,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-item::before' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; left: calc(-20px - ({{SIZE}}{{UNIT}} / 2) + 1px);',
				],
			]
		);

		$this->add_control(
			'dot_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-item::before' => 'border-color: {{VALUE}};',
				],
				'default'   => '#ffffff',
			]
		);

		$this->add_responsive_control(
			'dot_border_width',
			[
				'label'     => esc_html__( 'Border Width', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-item::before' => 'border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dot_position',
			[
				'label'   => esc_html__( 'Position', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'top'    => [
						'title' => esc_html__( 'Top', 'acz-elements' ),
						'icon'  => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'acz-elements' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'acz-elements' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default' => 'top',
				'prefix_class' => 'acz-dot-align-',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_details',
			[
				'label' => esc_html__( 'Details Group', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'details_direction',
			[
				'label' => esc_html__( 'Direction', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => [
					'vertical' => esc_html__( 'Vertical', 'acz-elements' ),
					'horizontal' => esc_html__( 'Horizontal', 'acz-elements' ),
				],
				'prefix_class' => 'acz-timeline-details-',
			]
		);

		$this->add_responsive_control(
			'details_spacing',
			[
				'label' => esc_html__( 'Spacing', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-details' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'details_separator',
			[
				'label' => esc_html__( 'Separator', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '|',
				'placeholder' => esc_html__( 'e.g. |', 'acz-elements' ),
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-details-separator::before' => 'content: "{{VALUE}}";',
				],
				'condition' => [
					'details_direction' => 'horizontal',
				],
			]
		);

		$this->add_control(
			'details_separator_color',
			[
				'label' => esc_html__( 'Separator Color', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-details-separator' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_direction' => 'horizontal',
					'details_separator!' => '',
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
				'label'     => esc_html__( 'Title Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .acz-timeline-title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_company',
			[
				'label' => esc_html__( 'Company', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'company_color',
			[
				'label'     => esc_html__( 'Company Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-company' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'company_typography',
				'selector' => '{{WRAPPER}} .acz-timeline-company',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_date',
			[
				'label' => esc_html__( 'Date', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'date_color',
			[
				'label'     => esc_html__( 'Date Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-date' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'date_typography',
				'selector' => '{{WRAPPER}} .acz-timeline-date',
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
				'label'     => esc_html__( 'Content Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-content' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'content_typography',
				'selector' => '{{WRAPPER}} .acz-timeline-content',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_location',
			[
				'label' => esc_html__( 'Location', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'location_color',
			[
				'label'     => esc_html__( 'Location Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-location' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'location_typography',
				'selector' => '{{WRAPPER}} .acz-timeline-location',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_employment',
			[
				'label' => esc_html__( 'Employment', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'employment_color',
			[
				'label'     => esc_html__( 'Employment Color', 'acz-elements' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .acz-timeline-employment' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'employment_typography',
				'selector' => '{{WRAPPER}} .acz-timeline-employment',
			]
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['items'] ) ) {
			return;
		}
		?>
		<div class="acz-timeline">
			<?php foreach ( $settings['items'] as $item ) : ?>
				<div class="acz-timeline-item">
					<div class="acz-timeline-header">
						<?php if ( ! empty( $item['title'] ) ) : ?>
							<h3 class="acz-timeline-title"><?php echo esc_html( $item['title'] ); ?></h3>
						<?php endif; ?>
						
						<div class="acz-timeline-details">
							<?php
							$details = [];
							if ( ! empty( $item['company'] ) ) {
								$details[] = '<div class="acz-timeline-company">' . esc_html( $item['company'] ) . '</div>';
							}
							if ( ! empty( $item['location'] ) ) {
								$details[] = '<div class="acz-timeline-location">' . esc_html( $item['location'] ) . '</div>';
							}
							if ( ! empty( $item['employment'] ) ) {
								$details[] = '<div class="acz-timeline-employment">' . esc_html( $item['employment'] ) . '</div>';
							}

							$separator = '';
							if ( ! empty( $settings['details_separator'] ) && $settings['details_direction'] === 'horizontal' ) {
								$separator = '<span class="acz-timeline-details-separator"></span>';
							}

							echo implode( $separator, $details );
							?>
						</div>

						<?php if ( ! empty( $item['date'] ) ) : ?>
							<div class="acz-timeline-date"><?php echo esc_html( $item['date'] ); ?></div>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $item['content'] ) ) : ?>
						<div class="acz-timeline-content">
							<?php echo $this->parse_text_editor( $item['content'] ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
