<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Media_Gallery_Widget extends \Elementor\Widget_Base {
    public function get_name(): string {
        return 'acz_media_gallery';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Media Gallery', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-gallery-grid';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'acz', 'acf', 'gallery', 'media', 'images', 'woocommerce', 'product' ];
    }

    public function get_style_depends(): array {
        return [ 'cec-widget' ];
    }

    public function get_script_depends(): array {
        return [ 'cec-widget' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_layout_controls();
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
            'gallery_source',
            [
                'label'   => esc_html__( 'Gallery Source', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'acf_gallery',
                'options' => [
                    'acf_gallery'      => esc_html__( 'ACF Gallery Field', 'acz-elements' ),
                    'woo_product'      => esc_html__( 'WooCommerce Product Gallery', 'acz-elements' ),
                ],
            ]
        );

        $this->add_control(
            'acf_gallery_field',
            [
                'label'       => esc_html__( 'ACF Gallery Field Name', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'gallery',
                'placeholder' => 'gallery',
                'label_block' => true,
                'condition'   => [
                    'gallery_source' => 'acf_gallery',
                ],
            ]
        );

        $this->add_control(
            'woo_include_featured',
            [
                'label'        => esc_html__( 'Include Featured Image', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'gallery_source' => 'woo_product',
                ],
            ]
        );

        $this->add_control(
            'fallback_post_id',
            [
                'label'       => esc_html__( 'Fallback Post ID', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::NUMBER,
                'min'         => 0,
                'step'        => 1,
                'description' => esc_html__( 'Used only when current post context is unavailable.', 'acz-elements' ),
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label'   => esc_html__( 'Image Size', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'large',
                'options' => $this->get_image_size_options(),
            ]
        );

        $this->add_control(
            'layout_mode',
            [
                'label'   => esc_html__( 'Layout Mode', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid'     => esc_html__( 'Grid', 'acz-elements' ),
                    'carousel' => esc_html__( 'Carousel', 'acz-elements' ),
                ],
            ]
        );

        $this->add_control(
            'link_to',
            [
                'label'   => esc_html__( 'Link To', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'       => esc_html__( 'None', 'acz-elements' ),
                    'file'       => esc_html__( 'Media File', 'acz-elements' ),
                    'attachment' => esc_html__( 'Attachment Page', 'acz-elements' ),
                ],
            ]
        );

        $this->add_control(
            'open_new_tab',
            [
                'label'        => esc_html__( 'Open Link in New Tab', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => [
                    'link_to!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'empty_text',
            [
                'label'       => esc_html__( 'Empty Text', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'No gallery images found for this post.', 'acz-elements' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_carousel',
            [
                'label'     => esc_html__( 'Carousel', 'acz-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'layout_mode' => 'carousel',
                ],
            ]
        );

        $this->add_responsive_control(
            'carousel_slides_per_view',
            [
                'label'          => esc_html__( 'Slides Per View', 'acz-elements' ),
                'type'           => \Elementor\Controls_Manager::SELECT,
                'default'        => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options'        => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
            ]
        );

        $this->add_responsive_control(
            'carousel_space_between',
            [
                'label'      => esc_html__( 'Space Between', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default'    => [
                    'size' => 16,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_control(
            'carousel_show_arrows',
            [
                'label'        => esc_html__( 'Show Arrows', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'carousel_show_pagination',
            [
                'label'        => esc_html__( 'Show Pagination', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'carousel_loop',
            [
                'label'        => esc_html__( 'Loop', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'carousel_autoplay',
            [
                'label'        => esc_html__( 'Autoplay', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );

        $this->add_control(
            'carousel_autoplay_delay',
            [
                'label'      => esc_html__( 'Autoplay Delay (ms)', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::NUMBER,
                'default'    => 3000,
                'min'        => 500,
                'step'       => 100,
                'condition'  => [
                    'carousel_autoplay' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'carousel_pause_on_hover',
            [
                'label'        => esc_html__( 'Pause on Hover', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => [
                    'carousel_autoplay' => 'yes',
                ],
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

        $this->add_responsive_control(
            'columns',
            [
                'label'          => esc_html__( 'Columns', 'acz-elements' ),
                'type'           => \Elementor\Controls_Manager::SELECT,
                'default'        => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options'        => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors'      => [
                    '{{WRAPPER}} .acz-media-gallery' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
                ],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label'      => esc_html__( 'Gap', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'default'    => [
                    'size' => 16,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-media-gallery' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style_image',
            [
                'label' => esc_html__( 'Image', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label'      => esc_html__( 'Height', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-media-gallery-image' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .acz-media-gallery-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings        = $this->get_settings_for_display();
        $gallery_source  = sanitize_key( (string) ( $settings['gallery_source'] ?? 'acf_gallery' ) );
        $field_name      = sanitize_text_field( (string) ( $settings['acf_gallery_field'] ?? '' ) );
        $empty_text      = (string) ( $settings['empty_text'] ?? esc_html__( 'No gallery images found for this post.', 'acz-elements' ) );
        $image_size      = sanitize_key( (string) ( $settings['image_size'] ?? 'large' ) );
        $layout_mode     = sanitize_key( (string) ( $settings['layout_mode'] ?? 'grid' ) );
        $link_to         = sanitize_key( (string) ( $settings['link_to'] ?? 'none' ) );
        $new_tab         = 'yes' === (string) ( $settings['open_new_tab'] ?? 'no' );
        $include_featured = 'yes' === (string) ( $settings['woo_include_featured'] ?? 'yes' );
        $post_id         = $this->get_context_post_id();

        if ( $post_id <= 0 && ! empty( $settings['fallback_post_id'] ) ) {
            $post_id = (int) $settings['fallback_post_id'];
        }

        if ( 'acf_gallery' === $gallery_source && '' === $field_name ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'Please set an ACF Gallery field name.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        if ( $post_id <= 0 ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="acz-post-gallery-empty">' . esc_html__( 'ACZ Media Gallery needs a post context.', 'acz-elements' ) . '</div>';
            }
            return;
        }

        if ( 'woo_product' === $gallery_source ) {
            if ( ! function_exists( 'wc_get_product' ) ) {
                if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                    echo '<div class="acz-post-gallery-empty">' . esc_html__( 'WooCommerce is required for Product Gallery source.', 'acz-elements' ) . '</div>';
                }
                return;
            }
            $gallery_items = $this->get_woocommerce_gallery_items( $post_id, $image_size, $include_featured );
        } else {
            $gallery_items = $this->get_gallery_items( $field_name, $post_id, $image_size );
        }

        if ( empty( $gallery_items ) ) {
            if ( '' !== trim( $empty_text ) ) {
                echo '<div class="acz-current-media-gallery-empty">' . esc_html( $empty_text ) . '</div>';
            }
            return;
        }

        if ( 'carousel' === $layout_mode ) {
            $this->render_carousel( $settings, $gallery_items, $link_to, $new_tab );
            return;
        }

        echo '<div class="acz-media-gallery">';
        foreach ( $gallery_items as $item ) {
            echo $this->get_gallery_item_markup( $item, $link_to, $new_tab ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        echo '</div>';
    }

    private function get_gallery_item_markup( array $item, string $link_to, bool $new_tab ): string {
        $image_html = $item['image_html'] ?? '';

        if ( ! is_string( $image_html ) || '' === $image_html ) {
            return '';
        }

        $markup = '<figure class="acz-media-gallery-item">';

        $href = '';
        if ( 'file' === $link_to && ! empty( $item['file_url'] ) ) {
            $href = (string) $item['file_url'];
        } elseif ( 'attachment' === $link_to && ! empty( $item['attachment_url'] ) ) {
            $href = (string) $item['attachment_url'];
        }

        if ( '' !== $href ) {
            $markup .= '<a class="acz-media-gallery-link" href="' . esc_url( $href ) . '"';
            if ( $new_tab ) {
                $markup .= ' target="_blank" rel="noopener noreferrer"';
            }
            $markup .= '>';
            $markup .= $image_html;
            $markup .= '</a>';
        } else {
            $markup .= $image_html;
        }

        $markup .= '</figure>';
        return $markup;
    }

    private function render_carousel( array $settings, array $gallery_items, string $link_to, bool $new_tab ): void {
        $config = [
            'effect'              => 'slide',
            'showArrows'          => 'yes' === (string) ( $settings['carousel_show_arrows'] ?? 'yes' ),
            'showPagination'      => 'yes' === (string) ( $settings['carousel_show_pagination'] ?? 'yes' ),
            'loop'                => 'yes' === (string) ( $settings['carousel_loop'] ?? 'yes' ),
            'autoplay'            => 'yes' === (string) ( $settings['carousel_autoplay'] ?? 'no' ),
            'autoplayDelay'       => max( 500, (int) ( $settings['carousel_autoplay_delay'] ?? 3000 ) ),
            'pauseOnHover'        => 'yes' === (string) ( $settings['carousel_pause_on_hover'] ?? 'no' ),
            'speed'               => 600,
            'slidesPerView'       => max( 1, (int) ( $settings['carousel_slides_per_view'] ?? 3 ) ),
            'slidesPerViewTablet' => max( 1, (int) ( $settings['carousel_slides_per_view_tablet'] ?? 2 ) ),
            'slidesPerViewMobile' => max( 1, (int) ( $settings['carousel_slides_per_view_mobile'] ?? 1 ) ),
            'spaceBetween'        => max( 0, (int) ( $settings['carousel_space_between']['size'] ?? 16 ) ),
            'spaceBetweenTablet'  => max( 0, (int) ( $settings['carousel_space_between_tablet']['size'] ?? ( $settings['carousel_space_between']['size'] ?? 16 ) ) ),
            'spaceBetweenMobile'  => max( 0, (int) ( $settings['carousel_space_between_mobile']['size'] ?? ( $settings['carousel_space_between_tablet']['size'] ?? ( $settings['carousel_space_between']['size'] ?? 16 ) ) ) ),
        ];

        $config_json = wp_json_encode( $config );
        if ( ! is_string( $config_json ) || '' === $config_json ) {
            $config_json = '{}';
        }

        echo '<div class="acz-media-gallery-carousel cec-effects-carousel" data-cec-config="' . esc_attr( $config_json ) . '">';
        echo '<div class="swiper"><div class="swiper-wrapper">';

        foreach ( $gallery_items as $item ) {
            $markup = $this->get_gallery_item_markup( $item, $link_to, $new_tab );
            if ( '' === $markup ) {
                continue;
            }
            echo '<div class="swiper-slide">' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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

    private function get_gallery_items( string $field_name, int $post_id, string $image_size ): array {
        $raw = null;

        if ( function_exists( 'get_field' ) ) {
            $raw = get_field( $field_name, $post_id );
        }

        if ( null === $raw ) {
            $raw = get_post_meta( $post_id, $field_name, true );
        }

        if ( ! is_array( $raw ) || empty( $raw ) ) {
            return [];
        }

        $items = [];

        foreach ( $raw as $item ) {
            $normalized = $this->normalize_gallery_item( $item, $image_size );
            if ( null === $normalized ) {
                continue;
            }
            $items[] = $normalized;
        }

        return $items;
    }

    private function get_woocommerce_gallery_items( int $post_id, string $image_size, bool $include_featured ): array {
        if ( ! function_exists( 'wc_get_product' ) ) {
            return [];
        }

        $product = wc_get_product( $post_id );
        if ( ! $product ) {
            return [];
        }

        $attachment_ids = $product->get_gallery_image_ids();
        if ( ! is_array( $attachment_ids ) ) {
            $attachment_ids = [];
        }

        if ( $include_featured ) {
            $featured_id = $product->get_image_id();
            if ( $featured_id > 0 ) {
                array_unshift( $attachment_ids, $featured_id );
            }
        }

        $attachment_ids = array_values( array_unique( array_filter( array_map( 'intval', $attachment_ids ) ) ) );
        if ( empty( $attachment_ids ) ) {
            return [];
        }

        $items = [];
        foreach ( $attachment_ids as $attachment_id ) {
            $normalized = $this->normalize_gallery_item( $attachment_id, $image_size );
            if ( null === $normalized ) {
                continue;
            }
            $items[] = $normalized;
        }

        return $items;
    }

    private function normalize_gallery_item( $item, string $image_size ): ?array {
        $attachment_id = 0;
        $file_url      = '';
        $alt           = '';

        if ( is_numeric( $item ) ) {
            $attachment_id = (int) $item;
        } elseif ( is_array( $item ) ) {
            $attachment_id = isset( $item['ID'] ) ? (int) $item['ID'] : ( isset( $item['id'] ) ? (int) $item['id'] : 0 );
            $file_url      = isset( $item['url'] ) && is_string( $item['url'] ) ? $item['url'] : '';
            $alt           = isset( $item['alt'] ) && is_string( $item['alt'] ) ? $item['alt'] : '';
        } elseif ( is_string( $item ) ) {
            $file_url = $item;
        }

        $image_html = '';

        if ( $attachment_id > 0 ) {
            $image_html = wp_get_attachment_image(
                $attachment_id,
                $image_size,
                false,
                [
                    'class'   => 'acz-media-gallery-image',
                    'loading' => 'lazy',
                ]
            );

            if ( '' === $file_url ) {
                $file_url = wp_get_attachment_url( $attachment_id ) ?: '';
            }
        }

        if ( '' === $image_html && '' !== $file_url ) {
            $image_html = sprintf(
                '<img class="acz-media-gallery-image" src="%1$s" alt="%2$s" loading="lazy" />',
                esc_url( $file_url ),
                esc_attr( $alt )
            );
        }

        if ( '' === $image_html ) {
            return null;
        }

        return [
            'image_html'      => $image_html,
            'file_url'        => $file_url,
            'attachment_url'  => $attachment_id > 0 ? get_attachment_link( $attachment_id ) : '',
        ];
    }

    private function get_image_size_options(): array {
        $options = [
            'thumbnail' => esc_html__( 'Thumbnail', 'acz-elements' ),
            'medium'    => esc_html__( 'Medium', 'acz-elements' ),
            'large'     => esc_html__( 'Large', 'acz-elements' ),
            'full'      => esc_html__( 'Full', 'acz-elements' ),
        ];

        $registered_sizes = get_intermediate_image_sizes();
        foreach ( $registered_sizes as $size ) {
            if ( isset( $options[ $size ] ) ) {
                continue;
            }
            $options[ $size ] = ucwords( str_replace( [ '-', '_' ], ' ', $size ) );
        }

        return $options;
    }

    private function get_context_post_id(): int {
        $post_id = get_the_ID();

        if ( ! $post_id ) {
            $post_id = get_queried_object_id();
        }

        return (int) $post_id;
    }
}
