<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Logo_Carousel_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'acz_logo_carousel';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Logo Carousel', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-logo';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'logo', 'carousel', 'slider', 'repeater', 'acf', 'acz' ];
    }

    public function get_style_depends(): array {
      		return [ 'cec-swiper', 'acz-elements-widget' ];
    }

    public function get_script_depends(): array {
      		return [ 'cec-swiper', 'acz-elements-widget' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_carousel_controls();
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
            'source',
            [
                'label'   => esc_html__( 'Source', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'elementor_repeater',
                'options' => [
                    'elementor_repeater' => esc_html__( 'Elementor Repeater Items', 'acz-elements' ),
                    'acf_theme_options'  => esc_html__( 'Theme Options ACF Repeater', 'acz-elements' ),
                ],
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'name',
            [
                'label'       => esc_html__( 'Name', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__( 'Brand', 'acz-elements' ),
            ]
        );

        $repeater->add_control(
            'logo_image',
            [
                'label' => esc_html__( 'Logo Image', 'acz-elements' ),
                'type'  => \Elementor\Controls_Manager::MEDIA,
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label'       => esc_html__( 'Link', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::URL,
                'show_label'  => true,
                'placeholder' => 'https://example.com',
            ]
        );

        $this->add_control(
            'logo_items',
            [
                'label'       => esc_html__( 'Items', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ name }}}',
                'condition'   => [
                    'source' => 'elementor_repeater',
                ],
            ]
        );

        $this->add_control(
            'acf_repeater_field',
            [
                'label'       => esc_html__( 'ACF Repeater Field', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'logo_carousel',
                'placeholder' => 'logo_carousel',
                'label_block' => true,
                'condition'   => [
                    'source' => 'acf_theme_options',
                ],
            ]
        );

        $this->add_control(
            'acf_name_field',
            [
                'label'       => esc_html__( 'ACF Name Field', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'name',
                'placeholder' => 'name',
                'condition'   => [
                    'source' => 'acf_theme_options',
                ],
            ]
        );

        $this->add_control(
            'acf_logo_field',
            [
                'label'       => esc_html__( 'ACF Logo Field', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'logo_image',
                'placeholder' => 'logo_image',
                'condition'   => [
                    'source' => 'acf_theme_options',
                ],
            ]
        );

        $this->add_control(
            'acf_link_field',
            [
                'label'       => esc_html__( 'ACF Link Field', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'link',
                'placeholder' => 'link',
                'condition'   => [
                    'source' => 'acf_theme_options',
                ],
            ]
        );

        $this->add_control(
            'show_name',
            [
                'label'        => esc_html__( 'Show Name', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'force_nofollow_links',
            [
                'label'        => esc_html__( 'Force Nofollow Links', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'description'  => esc_html__( 'Add rel="nofollow" to all logo links.', 'acz-elements' ),
            ]
        );

        $this->add_control(
            'empty_text',
            [
                'label'       => esc_html__( 'Empty Text', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'No logo items found.', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    private function register_carousel_controls(): void {
        $this->start_controls_section(
            'section_carousel',
            [
                'label' => esc_html__( 'Carousel', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_responsive_control(
            'slides_per_view',
            [
                'label'          => esc_html__( 'Slides Per View', 'acz-elements' ),
                'type'           => \Elementor\Controls_Manager::SELECT,
                'default'        => '5',
                'tablet_default' => '3',
                'mobile_default' => '2',
                'options'        => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                ],
            ]
        );

        $this->add_responsive_control(
            'space_between',
            [
                'label'      => esc_html__( 'Space Between', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default'    => [
                    'size' => 20,
                    'unit' => 'px',
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
                'default'      => 'no',
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
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'autoplay_delay',
            [
                'label'     => esc_html__( 'Autoplay Delay (ms)', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 3000,
                'min'       => 500,
                'step'      => 100,
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_logo',
            [
                'label' => esc_html__( 'Logo', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'logo_height',
            [
                'label'      => esc_html__( 'Logo Height', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'default'    => [
                    'size' => 60,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-logo-carousel-item img' => 'height: {{SIZE}}{{UNIT}}; width: auto; object-fit: contain;',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_alignment',
            [
                'label'     => esc_html__( 'Alignment', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'default'   => 'center',
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
                    '{{WRAPPER}} .acz-logo-carousel-item' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_name',
            [
                'label' => esc_html__( 'Name', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'name_color',
            [
                'label'     => esc_html__( 'Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .acz-logo-carousel-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'name_typography',
                'selector' => '{{WRAPPER}} .acz-logo-carousel-name',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $items    = $this->get_logo_items( $settings );

        if ( empty( $items ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                $empty_text = (string) ( $settings['empty_text'] ?? esc_html__( 'No logo items found.', 'acz-elements' ) );
                echo '<div class="acz-post-gallery-empty">' . esc_html( $empty_text ) . '</div>';
            }
            return;
        }

        $config = [
            'effect'              => 'slide',
            'showArrows'          => 'yes' === (string) ( $settings['show_arrows'] ?? 'yes' ),
            'showPagination'      => 'yes' === (string) ( $settings['show_pagination'] ?? 'no' ),
            'loop'                => 'yes' === (string) ( $settings['loop'] ?? 'yes' ),
            'autoplay'            => 'yes' === (string) ( $settings['autoplay'] ?? 'yes' ),
            'autoplayDelay'       => max( 500, (int) ( $settings['autoplay_delay'] ?? 3000 ) ),
            'pauseOnHover'        => true,
            'speed'               => 600,
            'slidesPerView'       => max( 1, (int) ( $settings['slides_per_view'] ?? 5 ) ),
            'slidesPerViewTablet' => max( 1, (int) ( $settings['slides_per_view_tablet'] ?? 3 ) ),
            'slidesPerViewMobile' => max( 1, (int) ( $settings['slides_per_view_mobile'] ?? 2 ) ),
            'spaceBetween'        => max( 0, (int) ( $settings['space_between']['size'] ?? 20 ) ),
            'spaceBetweenTablet'  => max( 0, (int) ( $settings['space_between_tablet']['size'] ?? ( $settings['space_between']['size'] ?? 20 ) ) ),
            'spaceBetweenMobile'  => max( 0, (int) ( $settings['space_between_mobile']['size'] ?? ( $settings['space_between_tablet']['size'] ?? ( $settings['space_between']['size'] ?? 20 ) ) ) ),
        ];

        $config_json = wp_json_encode( $config );
        if ( ! is_string( $config_json ) || '' === $config_json ) {
            $config_json = '{}';
        }

        $show_name           = 'yes' === (string) ( $settings['show_name'] ?? 'yes' );
        $force_nofollow_link = 'yes' === (string) ( $settings['force_nofollow_links'] ?? '' );

        echo '<div class="acz-logo-carousel cec-effects-carousel" data-cec-config="' . esc_attr( $config_json ) . '">';
        echo '<div class="swiper"><div class="swiper-wrapper">';

        foreach ( $items as $item ) {
            $logo_url = (string) ( $item['logo_url'] ?? '' );
            if ( '' === $logo_url ) {
                continue;
            }

            $name     = (string) ( $item['name'] ?? '' );
            $link_url = (string) ( $item['link_url'] ?? '' );
            $target   = (string) ( $item['target'] ?? '' );
            $nofollow = ! empty( $item['nofollow'] );

            echo '<div class="swiper-slide">';
            echo '<div class="acz-logo-carousel-item">';

            if ( '' !== $link_url ) {
                echo '<a class="acz-logo-carousel-link" href="' . esc_url( $link_url ) . '"';
                if ( '_blank' === $target ) {
                    echo ' target="_blank"';
                }

                $rel = [];
                if ( '_blank' === $target ) {
                    $rel[] = 'noopener';
                }
                if ( $nofollow || $force_nofollow_link ) {
                    $rel[] = 'nofollow';
                }
                if ( ! empty( $rel ) ) {
                    echo ' rel="' . esc_attr( implode( ' ', array_unique( $rel ) ) ) . '"';
                }
                echo '>';
            }

            echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" />';

            if ( '' !== $link_url ) {
                echo '</a>';
            }

            if ( $show_name && '' !== trim( $name ) ) {
                echo '<div class="acz-logo-carousel-name">' . esc_html( $name ) . '</div>';
            }

            echo '</div>';
            echo '</div>';
        }

        echo '</div></div>';

        if ( ! empty( $config['showArrows'] ) ) {
            echo '<div class="swiper-button-prev" aria-label="' . esc_attr__( 'Previous slide', 'acz-elements' ) . '"></div>';
            echo '<div class="swiper-button-next" aria-label="' . esc_attr__( 'Next slide', 'acz-elements' ) . '"></div>';
        }

        if ( ! empty( $config['showPagination'] ) ) {
            echo '<div class="swiper-pagination"></div>';
        }

        echo '</div>';
    }

    private function get_logo_items( array $settings ): array {
        $source = (string) ( $settings['source'] ?? 'elementor_repeater' );

        if ( 'acf_theme_options' === $source ) {
            return $this->get_items_from_theme_options( $settings );
        }

        $rows  = isset( $settings['logo_items'] ) && is_array( $settings['logo_items'] ) ? $settings['logo_items'] : [];
        $items = [];

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $logo_url = $this->extract_image_url( $row['logo_image'] ?? '' );
            if ( '' === $logo_url ) {
                continue;
            }

            $link = $this->extract_link( $row['link'] ?? '' );

            $items[] = [
                'name'     => trim( (string) ( $row['name'] ?? '' ) ),
                'logo_url' => $logo_url,
                'link_url' => $link['url'],
                'target'   => $link['target'],
                'nofollow' => $link['nofollow'],
            ];
        }

        return $items;
    }

    private function get_items_from_theme_options( array $settings ): array {
        if ( ! function_exists( 'get_field' ) ) {
            return [];
        }

        $repeater_field = sanitize_key( (string) ( $settings['acf_repeater_field'] ?? 'logo_carousel' ) );
        $name_field     = sanitize_key( (string) ( $settings['acf_name_field'] ?? 'name' ) );
        $logo_field     = sanitize_key( (string) ( $settings['acf_logo_field'] ?? 'logo_image' ) );
        $link_field     = sanitize_key( (string) ( $settings['acf_link_field'] ?? 'link' ) );

        if ( '' === $repeater_field || '' === $logo_field ) {
            return [];
        }

        $rows = get_field( $repeater_field, 'option' );
        if ( ! is_array( $rows ) ) {
            return [];
        }

        $items = [];

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $logo_url = $this->extract_image_url( $row[ $logo_field ] ?? '' );
            if ( '' === $logo_url ) {
                continue;
            }

            $link = $this->extract_link( $row[ $link_field ] ?? '' );

            $items[] = [
                'name'     => trim( (string) ( $row[ $name_field ] ?? '' ) ),
                'logo_url' => $logo_url,
                'link_url' => $link['url'],
                'target'   => $link['target'],
                'nofollow' => $link['nofollow'],
            ];
        }

        return $items;
    }

    private function extract_image_url( $raw ): string {
        if ( is_array( $raw ) ) {
            if ( isset( $raw['url'] ) && is_string( $raw['url'] ) && '' !== trim( $raw['url'] ) ) {
                return esc_url_raw( $raw['url'] );
            }

            if ( isset( $raw['ID'] ) ) {
                $image_id = absint( $raw['ID'] );
                if ( $image_id > 0 ) {
                    $image_url = wp_get_attachment_image_url( $image_id, 'full' );
                    return is_string( $image_url ) ? $image_url : '';
                }
            }
        }

        if ( is_numeric( $raw ) ) {
            $image_url = wp_get_attachment_image_url( absint( $raw ), 'full' );
            return is_string( $image_url ) ? $image_url : '';
        }

        if ( is_string( $raw ) ) {
            $raw = trim( $raw );
            return '' !== $raw ? esc_url_raw( $raw ) : '';
        }

        return '';
    }

    private function extract_link( $raw ): array {
        $link = [
            'url'      => '',
            'target'   => '',
            'nofollow' => false,
        ];

        if ( is_array( $raw ) ) {
            if ( isset( $raw['url'] ) && is_string( $raw['url'] ) ) {
                $link['url'] = esc_url_raw( $raw['url'] );
            }

            if ( isset( $raw['is_external'] ) && ! empty( $raw['is_external'] ) ) {
                $link['target'] = '_blank';
            }

            if ( isset( $raw['target'] ) && '_blank' === $raw['target'] ) {
                $link['target'] = '_blank';
            }

            if ( isset( $raw['nofollow'] ) && ! empty( $raw['nofollow'] ) ) {
                $link['nofollow'] = true;
            }

            return $link;
        }

        if ( is_string( $raw ) && '' !== trim( $raw ) ) {
            $link['url'] = esc_url_raw( $raw );
        }

        return $link;
    }
}
