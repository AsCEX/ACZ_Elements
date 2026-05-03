<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * ACZ Site Logo Widget.
 *
 * Elementor widget that displays the site logo.
 */
class ACZ_Site_Logo_Widget extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'acz-site-logo';
	}

	public function get_title(): string {
		return esc_html__( 'ACZ Site Logo', 'acz-elements' );
	}

	public function get_icon(): string {
		return 'eicon-site-logo';
	}

	public function get_categories(): array {
		return [ 'acz' ];
	}

	public function get_keywords(): array {
		return [ 'site', 'logo', 'acz' ];
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
			'custom_logo',
			[
				'label'   => esc_html__( 'Custom Logo', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name'      => 'image', // Usage: 'image_size'
				'default'   => 'full',
				'separator' => 'none',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'   => esc_html__( 'Alignment', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
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
					'{{WRAPPER}} .acz-site-logo' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_to',
			[
				'label'   => esc_html__( 'Link', 'acz-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'site_url',
				'options' => [
					'none'     => esc_html__( 'None', 'acz-elements' ),
					'site_url' => esc_html__( 'Site URL', 'acz-elements' ),
					'custom'   => esc_html__( 'Custom URL', 'acz-elements' ),
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label'       => esc_html__( 'Link', 'acz-elements' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'acz-elements' ),
				'condition'   => [
					'link_to' => 'custom',
				],
				'show_label'  => false,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Image', 'acz-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label' => esc_html__( 'Width', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-site-logo img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'max_width',
			[
				'label' => esc_html__( 'Max Width', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .acz-site-logo img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'acz-elements' ),
				'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Opacity::get_type(),
			[
				'name' => 'opacity',
				'selector' => '{{WRAPPER}} .acz-site-logo img',
			]
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$logo_id = 0;

		if ( ! empty( $settings['custom_logo']['url'] ) ) {
			$logo_id = $settings['custom_logo']['id'];
		} else {
			$logo_id = get_theme_mod( 'custom_logo' );
		}

		if ( empty( $logo_id ) ) {
			// Fallback if no ID but URL exists for some reason (rare in Elementor)
			if ( ! empty( $settings['custom_logo']['url'] ) ) {
				$this->add_render_attribute( 'wrapper', 'class', 'acz-site-logo' );

				$link_url = '';
				if ( 'site_url' === $settings['link_to'] ) {
					$link_url = home_url( '/' );
				} elseif ( 'custom' === $settings['link_to'] && ! empty( $settings['link']['url'] ) ) {
					$link_url = $settings['link']['url'];
					$this->add_link_attributes( 'link', $settings['link'] );
				}

				if ( ! empty( $link_url ) ) {
					$this->add_render_attribute( 'link', 'href', $link_url );
					if ( 'site_url' === $settings['link_to'] ) {
						$this->add_render_attribute( 'link', 'rel', 'home' );
					}
				}
				?>
				<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
					<?php if ( ! empty( $link_url ) ) : ?>
						<a <?php $this->print_render_attribute_string( 'link' ); ?>>
							<img src="<?php echo esc_url( $settings['custom_logo']['url'] ); ?>" class="site-logo" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						</a>
					<?php else : ?>
						<img src="<?php echo esc_url( $settings['custom_logo']['url'] ); ?>" class="site-logo" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<?php endif; ?>
				</div>
				<?php
			}
			return;
		}

		$this->add_render_attribute( 'wrapper', 'class', 'acz-site-logo' );

		$link_url = '';
		if ( 'site_url' === $settings['link_to'] ) {
			$link_url = home_url( '/' );
		} elseif ( 'custom' === $settings['link_to'] && ! empty( $settings['link']['url'] ) ) {
			$link_url = $settings['link']['url'];
			$this->add_link_attributes( 'link', $settings['link'] );
		}

		if ( ! empty( $link_url ) ) {
			$this->add_render_attribute( 'link', 'href', $link_url );
			if ( 'site_url' === $settings['link_to'] ) {
				$this->add_render_attribute( 'link', 'rel', 'home' );
			}
		}

		$image_html = \Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings, 'image', 'custom_logo' );

		if ( empty( $settings['custom_logo']['url'] ) ) {
			$image_html = \Elementor\Group_Control_Image_Size::get_attachment_image_html( [
				'image_size' => $settings['image_size'] ?? 'full',
				'image_custom_dimension' => $settings['image_custom_dimension'] ?? [],
			], 'image', [ 'id' => $logo_id ] );
		}

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( ! empty( $link_url ) ) : ?>
				<a <?php $this->print_render_attribute_string( 'link' ); ?>>
					<?php echo $image_html; ?>
				</a>
			<?php else : ?>
				<?php echo $image_html; ?>
			<?php endif; ?>
		</div>
		<?php
	}
}
