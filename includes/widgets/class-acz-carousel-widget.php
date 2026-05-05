<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACZ_Carousel_Widget extends \Elementor\Widget_Base {
    public function get_name(): string {
        return 'acz_carousel';
    }

    public function get_title(): string {
        return esc_html__( 'ACZ Carousel', 'acz-elements' );
    }

    public function get_icon(): string {
        return 'eicon-nested-carousel';
    }

    public function get_categories(): array {
        return [ 'acz' ];
    }

    public function get_keywords(): array {
        return [ 'carousel', 'slider', 'swiper', 'fade', 'coverflow', 'cube', 'video', 'background' ];
    }

    public function get_style_depends(): array {
      		return [ 'cec-swiper', 'acz-elements-widget' ];
    }

    public function get_script_depends(): array {
      		return [ 'cec-swiper', 'acz-elements-widget' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_settings_controls();
        $this->register_style_controls();
    }

    private function register_content_controls(): void {
        $this->start_controls_section(
            'section_slides',
            [
                'label' => esc_html__( 'Slides', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->start_controls_tabs( 'slide_content_tabs' );
        $repeater->start_controls_tab(
            'slide_content_tab_background',
            [
                'label' => esc_html__( 'Background', 'acz-elements' ),
            ]
        );

        $repeater->add_control(
            'background_type',
            [
                'label'   => esc_html__( 'Background Media', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'none',
                'options' => [
                    'none'  => [
                        'title' => esc_html__( 'None', 'acz-elements' ),
                        'icon'  => 'eicon-ban',
                    ],
                    'image' => [
                        'title' => esc_html__( 'Image', 'acz-elements' ),
                        'icon'  => 'eicon-image-bold',
                    ],
                    'video' => [
                        'title' => esc_html__( 'Video', 'acz-elements' ),
                        'icon'  => 'eicon-video-camera',
                    ],
                ],
                'toggle'  => false,
            ]
        );

        $repeater->add_control(
            'background_image',
            [
                'label'      => esc_html__( 'Background Image', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::MEDIA,
                'condition'  => [
                    'background_type' => 'image',
                ],
                'default'    => [
                    'url' => '',
                ],
            ]
        );

        $repeater->add_control(
            'background_video_mp4',
            [
                'label'       => esc_html__( 'Background Video URL (MP4)', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/slide-background.mp4',
                'description' => esc_html__( 'Use a direct MP4 file URL, or paste a YouTube link if you want an iframe video background.', 'acz-elements' ),
                'condition'   => [
                    'background_type' => 'video',
                ],
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'background_video_webm',
            [
                'label'       => esc_html__( 'Background Video URL (WebM)', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/slide-background.webm',
                'description' => esc_html__( 'Optional WebM fallback for better browser support.', 'acz-elements' ),
                'condition'   => [
                    'background_type' => 'video',
                ],
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'background_video_url',
            [
                'label'       => esc_html__( 'Legacy / Fallback Video URL', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/slide-background.mp4',
                'description' => esc_html__( 'Optional fallback URL kept for compatibility with older widget data. You can also paste a YouTube URL here.', 'acz-elements' ),
                'condition'   => [
                    'background_type' => 'video',
                ],
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'background_video_poster',
            [
                'label'      => esc_html__( 'Video Poster Image', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::MEDIA,
                'condition'  => [
                    'background_type' => 'video',
                ],
                'default'    => [
                    'url' => '',
                ],
            ]
        );

        $repeater->add_control(
            'background_overlay_color',
            [
                'label'      => esc_html__( 'Background Overlay Color', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::COLOR,
                'default'    => 'rgba(17, 24, 39, 0.35)',
                'condition'  => [
                    'background_type!' => 'none',
                ],
            ]
        );

        $repeater->add_control(
            'background_media_fit',
            [
                'label'     => esc_html__( 'Background Fit', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'cover',
                'options'   => [
                    'cover'   => esc_html__( 'Cover', 'acz-elements' ),
                    'contain' => esc_html__( 'Contain', 'acz-elements' ),
                    'fill'    => esc_html__( 'Fill', 'acz-elements' ),
                ],
                'condition' => [
                    'background_type!' => 'none',
                ],
            ]
        );

        $repeater->add_control(
            'background_position_x',
            [
                'label'      => esc_html__( 'Background Position X', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ '%' ],
                'default'    => [
                    'size' => 50,
                    'unit' => '%',
                ],
                'range'      => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'condition'  => [
                    'background_type!' => 'none',
                ],
            ]
        );

        $repeater->add_control(
            'background_position_y',
            [
                'label'      => esc_html__( 'Background Position Y', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ '%' ],
                'default'    => [
                    'size' => 50,
                    'unit' => '%',
                ],
                'range'      => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'condition'  => [
                    'background_type!' => 'none',
                ],
            ]
        );

        $repeater->add_control(
            'background_iframe_offset_x',
            [
                'label'       => esc_html__( 'YouTube Offset X', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::SLIDER,
                'size_units'  => [ 'px', '%' ],
                'default'     => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'range'       => [
                    'px' => [
                        'min' => -600,
                        'max' => 600,
                    ],
                    '%'  => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'condition'   => [
                    'background_type' => 'video',
                ],
                'description' => esc_html__( 'Useful for YouTube iframe backgrounds.', 'acz-elements' ),
            ]
        );

        $repeater->add_control(
            'background_iframe_offset_y',
            [
                'label'       => esc_html__( 'YouTube Offset Y', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::SLIDER,
                'size_units'  => [ 'px', '%' ],
                'default'     => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'range'       => [
                    'px' => [
                        'min' => -600,
                        'max' => 600,
                    ],
                    '%'  => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'condition'   => [
                    'background_type' => 'video',
                ],
                'description' => esc_html__( 'Useful for YouTube iframe backgrounds.', 'acz-elements' ),
            ]
        );
        $repeater->end_controls_tab();

        $position_options = [
            'flow'          => esc_html__( 'Flow (Default)', 'acz-elements' ),
            'top-left'      => esc_html__( 'Top Left', 'acz-elements' ),
            'top-center'    => esc_html__( 'Top Center', 'acz-elements' ),
            'top-right'     => esc_html__( 'Top Right', 'acz-elements' ),
            'center-left'   => esc_html__( 'Center Left', 'acz-elements' ),
            'center-center' => esc_html__( 'Center Center', 'acz-elements' ),
            'center-right'  => esc_html__( 'Center Right', 'acz-elements' ),
            'bottom-left'   => esc_html__( 'Bottom Left', 'acz-elements' ),
            'bottom-center' => esc_html__( 'Bottom Center', 'acz-elements' ),
            'bottom-right'  => esc_html__( 'Bottom Right', 'acz-elements' ),
        ];

        $repeater->start_controls_tab(
            'slide_content_tab_title',
            [
                'label' => esc_html__( 'Title', 'acz-elements' ),
            ]
        );
        $repeater->add_control(
            'title',
            [
                'label'       => esc_html__( 'Title', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'Slide title', 'acz-elements' ),
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'title_color',
            [
                'label' => esc_html__( 'Color', 'acz-elements' ),
                'type'  => \Elementor\Controls_Manager::COLOR,
                'default' => '#111827',
                'global' => [
                    'active' => true,
                ],
            ]
        );
        $repeater->add_control(
            'title_alignment',
            [
                'label'   => esc_html__( 'Alignment', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default' => '',
            ]
        );
        $repeater->add_control(
            'title_align_self',
            [
                'label'   => esc_html__( 'Align Self', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => '',
                'toggle'  => false,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Start', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'End', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                    'stretch' => [
                        'title' => esc_html__( 'Stretch', 'acz-elements' ),
                        'icon'  => 'eicon-v-align-stretch',
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'title_position',
            [
                'label'   => esc_html__( 'Position', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'flow',
                'options' => $position_options,
            ]
        );
        $repeater->add_control(
            'title_max_width',
            [
                'label'      => esc_html__( 'Max Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 2000 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
            ]
        );
        $repeater->add_control(
            'title_margin',
            [
                'label'      => esc_html__( 'Margin', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
            ]
        );
        $repeater->add_control(
            'title_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
            ]
        );
        $repeater->add_control(
            'title_tag',
            [
                'label'   => esc_html__( 'HTML Tag', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'h1',
                'options' => [
                    'h1'   => 'H1',
                    'h2'   => 'H2',
                    'h3'   => 'H3',
                    'h4'   => 'H4',
                    'h5'   => 'H5',
                    'h6'   => 'H6',
                    'div'  => 'div',
                    'span' => 'span',
                    'p'    => 'p',
                ],
            ]
        );
        $repeater->add_control(
            'title_width',
            [
                'label'      => esc_html__( 'Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vw' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 2000 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                    'vw' => [ 'min' => 0, 'max' => 100 ],
                ],
            ]
        );
        $repeater->end_controls_tab();

        $repeater->start_controls_tab(
            'slide_content_tab_description',
            [
                'label' => esc_html__( 'Description', 'acz-elements' ),
            ]
        );
        $repeater->add_control(
            'description',
            [
                'label'   => esc_html__( 'Description', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__( 'Add your slide content here.', 'acz-elements' ),
            ]
        );
        $repeater->add_control(
            'description_color',
            [
                'label' => esc_html__( 'Color', 'acz-elements' ),
                'type'  => \Elementor\Controls_Manager::COLOR,
                'default' => '#4b5563',
                'global' => [
                    'active' => true,
                ],
            ]
        );
        $repeater->add_control(
            'description_alignment',
            [
                'label'   => esc_html__( 'Alignment', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default' => '',
            ]
        );
        $repeater->add_control(
            'description_align_self',
            [
                'label'   => esc_html__( 'Align Self', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => '',
                'toggle'  => false,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Start', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'End', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                    'stretch' => [
                        'title' => esc_html__( 'Stretch', 'acz-elements' ),
                        'icon'  => 'eicon-v-align-stretch',
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'description_position',
            [
                'label'   => esc_html__( 'Position', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'flow',
                'options' => $position_options,
            ]
        );
        $repeater->add_control(
            'description_max_width',
            [
                'label'      => esc_html__( 'Max Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 2000 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
            ]
        );
        $repeater->add_control(
            'description_margin',
            [
                'label'      => esc_html__( 'Margin', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
            ]
        );
        $repeater->add_control(
            'description_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
            ]
        );
        $repeater->add_control(
            'description_width',
            [
                'label'      => esc_html__( 'Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vw' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 2000 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                    'vw' => [ 'min' => 0, 'max' => 100 ],
                ],
            ]
        );
        $repeater->end_controls_tab();

        $repeater->start_controls_tab(
            'slide_content_tab_button',
            [
                'label' => esc_html__( 'Button', 'acz-elements' ),
            ]
        );
        $repeater->add_control(
            'button_text',
            [
                'label'       => esc_html__( 'Button Text', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'Learn more', 'acz-elements' ),
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'button_color',
            [
                'label' => esc_html__( 'Color', 'acz-elements' ),
                'type'  => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'global' => [
                    'active' => true,
                ],
            ]
        );
        $repeater->add_control(
            'button_alignment',
            [
                'label'   => esc_html__( 'Alignment', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default' => '',
            ]
        );
        $repeater->add_control(
            'button_align_self',
            [
                'label'   => esc_html__( 'Align Self', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => '',
                'toggle'  => false,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Start', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'End', 'acz-elements' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                    'stretch' => [
                        'title' => esc_html__( 'Stretch', 'acz-elements' ),
                        'icon'  => 'eicon-v-align-stretch',
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'button_link',
            [
                'label'         => esc_html__( 'Button Link', 'acz-elements' ),
                'type'          => \Elementor\Controls_Manager::URL,
                'placeholder'   => 'https://example.com',
                'show_external' => true,
                'default'       => [
                    'url' => '',
                ],
            ]
        );
        $repeater->add_control(
            'button_position',
            [
                'label'   => esc_html__( 'Position', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'flow',
                'options' => $position_options,
            ]
        );
        $repeater->add_control(
            'button_max_width',
            [
                'label'      => esc_html__( 'Max Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 2000 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
            ]
        );
        $repeater->add_control(
            'button_margin',
            [
                'label'      => esc_html__( 'Margin', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
            ]
        );
        $repeater->add_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
            ]
        );
        $repeater->add_control(
            'button_width',
            [
                'label'      => esc_html__( 'Width', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vw' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 2000 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                    'vw' => [ 'min' => 0, 'max' => 100 ],
                ],
            ]
        );
        $repeater->end_controls_tab();
        $repeater->end_controls_tabs();

        $this->add_control(
            'slides',
            [
                'label'       => esc_html__( 'Slides', 'acz-elements' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ title }}}',
                'default'     => [
                    [
                        'title'                    => esc_html__( 'Slide One', 'acz-elements' ),
                        'description'              => esc_html__( 'Use fade, coverflow, cube, flip, cards, creative, or plain slide.', 'acz-elements' ),
                        'button_text'              => esc_html__( 'Read more', 'acz-elements' ),
                        'background_type'          => 'image',
                        'background_overlay_color' => 'rgba(17, 24, 39, 0.35)',
                    ],
                    [
                        'title'                    => esc_html__( 'Slide Two', 'acz-elements' ),
                        'description'              => esc_html__( 'This widget uses its own Swiper instance instead of Elementor\'s built-in Carousel widget.', 'acz-elements' ),
                        'button_text'              => esc_html__( 'Explore', 'acz-elements' ),
                        'background_type'          => 'none',
                    ],
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_settings_controls(): void {
        $this->start_controls_section(
            'section_settings',
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
                    'slide'      => esc_html__( 'Slide', 'acz-elements' ),
                    'fade'       => esc_html__( 'Fade', 'acz-elements' ),
                    'cube'       => esc_html__( 'Cube', 'acz-elements' ),
                    'coverflow'  => esc_html__( 'Coverflow', 'acz-elements' ),
                    'flip'       => esc_html__( 'Flip', 'acz-elements' ),
                    'cards'      => esc_html__( 'Cards', 'acz-elements' ),
                    'creative'   => esc_html__( 'Creative', 'acz-elements' ),
                ],
            ]
        );

        $this->add_control(
            'slides_per_view',
            [
                'label'   => esc_html__( 'Slides Per View (Desktop)', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min'     => 1,
                'max'     => 6,
            ]
        );

        $this->add_control(
            'slides_per_view_tablet',
            [
                'label'   => esc_html__( 'Slides Per View (Tablet)', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min'     => 1,
                'max'     => 4,
            ]
        );

        $this->add_control(
            'slides_per_view_mobile',
            [
                'label'   => esc_html__( 'Slides Per View (Mobile)', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min'     => 1,
                'max'     => 2,
            ]
        );

        $this->add_control(
            'space_between',
            [
                'label'   => esc_html__( 'Space Between (Desktop)', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 24,
                'min'     => 0,
                'max'     => 120,
            ]
        );

        $this->add_control(
            'space_between_tablet',
            [
                'label'   => esc_html__( 'Space Between (Tablet)', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
                'min'     => 0,
                'max'     => 120,
            ]
        );

        $this->add_control(
            'space_between_mobile',
            [
                'label'   => esc_html__( 'Space Between (Mobile)', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 16,
                'min'     => 0,
                'max'     => 120,
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
                'max'       => 20000,
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
                'label'        => esc_html__( 'Arrows', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'arrow_prev_icon',
            [
                'label'     => esc_html__( 'Previous Arrow Icon', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::ICONS,
                'condition' => [
                    'show_arrows' => 'yes',
                ],
                'default'   => [
                    'value'   => 'eicon-chevron-left',
                    'library' => 'eicons',
                ],
                'skin'      => 'inline',
                'label_block' => false,
            ]
        );

        $this->add_control(
            'arrow_next_icon',
            [
                'label'     => esc_html__( 'Next Arrow Icon', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::ICONS,
                'condition' => [
                    'show_arrows' => 'yes',
                ],
                'default'   => [
                    'value'   => 'eicon-chevron-right',
                    'library' => 'eicons',
                ],
                'skin'      => 'inline',
                'label_block' => false,
            ]
        );

        $this->add_control(
            'show_pagination',
            [
                'label'        => esc_html__( 'Pagination', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'auto_height',
            [
                'label'        => esc_html__( 'Auto Height', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

        $this->add_control(
            'grab_cursor',
            [
                'label'        => esc_html__( 'Grab Cursor', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();
    }


    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Card Style', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_alignment',
            [
                'label'   => esc_html__( 'Default Alignment', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'   => 'left',
                'selectors' => [
                    '{{WRAPPER}} .cec-slide-card' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'content_vertical_alignment',
            [
                'label'   => esc_html__( 'Background Content Position', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'flex-end',
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Top', 'acz-elements' ),
                        'icon'  => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Middle', 'acz-elements' ),
                        'icon'  => 'eicon-v-align-middle',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'Bottom', 'acz-elements' ),
                        'icon'  => 'eicon-v-align-bottom',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cec-slide-card.has-background-media' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_gap',
            [
                'label'      => esc_html__( 'Content Gap', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'default'    => [
                    'size' => 16,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-slide-content' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'card_background',
            [
                'label'     => esc_html__( 'Card Background', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cec-slide-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label'      => esc_html__( 'Card Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem', '%' ],
                'default'    => [
                    'top' => 24,
                    'right' => 24,
                    'bottom' => 24,
                    'left' => 24,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-slide-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'background_min_height',
            [
                'label'      => esc_html__( 'Slide Min Height', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'default'    => [
                    'size' => 420,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-slide-card' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_title_style',
            [
                'label' => esc_html__( 'Title', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .cec-slide-title',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_text_style',
            [
                'label' => esc_html__( 'Text', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'text_typography',
                'selector' => '{{WRAPPER}} .cec-slide-description',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_button_style',
            [
                'label' => esc_html__( 'Button', 'acz-elements' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_control(
            'button_background_color',
            [
                'label'     => esc_html__( 'Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .cec-slide-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label'     => esc_html__( 'Hover Text Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cec-slide-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background_color',
            [
                'label'     => esc_html__( 'Hover Background Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cec-slide-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .cec-slide-button',
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem', '%' ],
                'default'    => [
                    'top' => 12,
                    'right' => 20,
                    'bottom' => 12,
                    'left' => 20,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-slide-button' => 'display: inline-flex; align-items: center; justify-content: center; padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'default'    => [
                    'top' => 999,
                    'right' => 999,
                    'bottom' => 999,
                    'left' => 999,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-slide-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_section();

        $this->start_controls_section(
            'section_navigation_style',
            [
                'label'     => esc_html__( 'Carousel Navigation', 'acz-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'navigation_alignment',
            [
                'label'   => esc_html__( 'Alignment', 'acz-elements' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'center',
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Left', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => esc_html__( 'Center', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => esc_html__( 'Right', 'acz-elements' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cec-carousel-navigation' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'navigation_inside_slides',
            [
                'label'        => esc_html__( 'Show Inside Slides', 'acz-elements' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'acz-elements' ),
                'label_off'    => esc_html__( 'No', 'acz-elements' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_responsive_control(
            'navigation_gap',
            [
                'label'      => esc_html__( 'Items Gap', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default'    => [
                    'size' => 14,
                    'unit' => 'px',
                ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 120 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-carousel-navigation' => 'column-gap: {{SIZE}}{{UNIT}}; row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_bottom_offset',
            [
                'label'      => esc_html__( 'Bottom Offset', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vh' ],
                'default'    => [
                    'size' => 24,
                    'unit' => 'px',
                ],
                'range'      => [
                    'px' => [ 'min' => -200, 'max' => 400 ],
                    '%'  => [ 'min' => -20, 'max' => 100 ],
                    'vh' => [ 'min' => -20, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-carousel-navigation' => 'bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_offset_x',
            [
                'label'      => esc_html__( 'Offset X', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vw' ],
                'range'      => [
                    'px' => [ 'min' => -400, 'max' => 400 ],
                    '%'  => [ 'min' => -100, 'max' => 100 ],
                    'vw' => [ 'min' => -100, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-carousel-navigation' => '--cec-nav-offset-x: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_offset_y',
            [
                'label'      => esc_html__( 'Offset Y', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => -400, 'max' => 400 ],
                    '%'  => [ 'min' => -100, 'max' => 100 ],
                    'vh' => [ 'min' => -100, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-carousel-navigation' => '--cec-nav-offset-y: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_padding',
            [
                'label'      => esc_html__( 'Padding', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-carousel-navigation' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'navigation_background_color',
            [
                'label'     => esc_html__( 'Background', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cec-carousel-navigation' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-carousel-navigation' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_z_index',
            [
                'label'      => esc_html__( 'Z-Index', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::NUMBER,
                'default'    => 6,
                'selectors'  => [
                    '{{WRAPPER}} .cec-carousel-navigation' => 'z-index: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_arrows_style',
            [
                'label'     => esc_html__( 'Arrows', 'acz-elements' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_arrows' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrow_box_size',
            [
                'label'      => esc_html__( 'Arrow Box Size', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default'    => [
                    'size' => 44,
                    'unit' => 'px',
                ],
                'range'      => [
                    'px' => [ 'min' => 20, 'max' => 140 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-nav-button' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrow_icon_size',
            [
                'label'      => esc_html__( 'Arrow Icon Size', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default'    => [
                    'size' => 18,
                    'unit' => 'px',
                ],
                'range'      => [
                    'px' => [ 'min' => 8, 'max' => 80 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-nav-icon' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cec-nav-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'arrow_color',
            [
                'label'     => esc_html__( 'Arrow Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cec-nav-button' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cec-nav-button svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'arrow_background_color',
            [
                'label'     => esc_html__( 'Arrow Background', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => 'transparent',
                'selectors' => [
                    '{{WRAPPER}} .cec-nav-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'arrow_hover_color',
            [
                'label'     => esc_html__( 'Arrow Hover Color', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cec-nav-button:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cec-nav-button:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'arrow_hover_background_color',
            [
                'label'     => esc_html__( 'Arrow Hover Background', 'acz-elements' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cec-nav-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrow_border_radius',
            [
                'label'      => esc_html__( 'Arrow Border Radius', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'default'    => [
                    'top' => 999,
                    'right' => 999,
                    'bottom' => 999,
                    'left' => 999,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-nav-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrow_prev_offset_x',
            [
                'label'      => esc_html__( 'Prev Arrow Offset X', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vw' ],
                'range'      => [
                    'px' => [ 'min' => -400, 'max' => 400 ],
                    '%'  => [ 'min' => -100, 'max' => 100 ],
                    'vw' => [ 'min' => -100, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-nav-button-prev' => 'position: relative; left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrow_prev_offset_y',
            [
                'label'      => esc_html__( 'Prev Arrow Offset Y', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => -400, 'max' => 400 ],
                    '%'  => [ 'min' => -100, 'max' => 100 ],
                    'vh' => [ 'min' => -100, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-nav-button-prev' => 'position: relative; top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrow_next_offset_x',
            [
                'label'      => esc_html__( 'Next Arrow Offset X', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vw' ],
                'range'      => [
                    'px' => [ 'min' => -400, 'max' => 400 ],
                    '%'  => [ 'min' => -100, 'max' => 100 ],
                    'vw' => [ 'min' => -100, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-nav-button-next' => 'position: relative; left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrow_next_offset_y',
            [
                'label'      => esc_html__( 'Next Arrow Offset Y', 'acz-elements' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => -400, 'max' => 400 ],
                    '%'  => [ 'min' => -100, 'max' => 100 ],
                    'vh' => [ 'min' => -100, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cec-nav-button-next' => 'position: relative; top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function extract_youtube_video_id( string $url ): string {
        $url = trim( $url );

        if ( '' === $url ) {
            return '';
        }

        $parts = wp_parse_url( $url );

        if ( empty( $parts['host'] ) ) {
            return '';
        }

        $host = strtolower( preg_replace( '/^www\./', '', $parts['host'] ) );

        if ( 'youtu.be' === $host ) {
            $path = trim( $parts['path'] ?? '', '/' );
            if ( preg_match( '/^[A-Za-z0-9_-]{6,20}$/', $path ) ) {
                return $path;
            }
        }

        if ( in_array( $host, [ 'youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com' ], true ) ) {
            $path = trim( $parts['path'] ?? '', '/' );

            if ( 'watch' === $path && ! empty( $parts['query'] ) ) {
                parse_str( $parts['query'], $query_args );
                if ( ! empty( $query_args['v'] ) && preg_match( '/^[A-Za-z0-9_-]{6,20}$/', $query_args['v'] ) ) {
                    return $query_args['v'];
                }
            }

            if ( preg_match( '#^(embed|shorts|live)/([A-Za-z0-9_-]{6,20})$#', $path, $matches ) ) {
                return $matches[2];
            }
        }

        return '';
    }

    private function get_youtube_embed_url( string $url ): string {
        $video_id = $this->extract_youtube_video_id( $url );

        if ( '' === $video_id ) {
            return '';
        }

        $query_args = [
            'autoplay'        => 1,
            'mute'            => 1,
            'controls'        => 0,
            'loop'            => 1,
            'playlist'        => $video_id,
            'playsinline'     => 1,
            'rel'             => 0,
            'modestbranding'  => 1,
            'iv_load_policy'  => 3,
            'disablekb'       => 1,
            'fs'              => 0,
        ];

        return add_query_arg( $query_args, 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video_id ) );
    }

    private function render_nav_icon( array $icon, string $fallback_class ): void {
        echo '<span class="cec-nav-icon" aria-hidden="true">';

        if ( ! empty( $icon['value'] ) ) {
            \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
        } else {
            echo '<i class="' . esc_attr( $fallback_class ) . '"></i>';
        }

        echo '</span>';
    }

    private function build_dimensions_style( $dimensions, string $property ): string {
        if ( ! is_array( $dimensions ) ) {
            return '';
        }

        $top    = isset( $dimensions['top'] ) ? trim( (string) $dimensions['top'] ) : '';
        $right  = isset( $dimensions['right'] ) ? trim( (string) $dimensions['right'] ) : '';
        $bottom = isset( $dimensions['bottom'] ) ? trim( (string) $dimensions['bottom'] ) : '';
        $left   = isset( $dimensions['left'] ) ? trim( (string) $dimensions['left'] ) : '';

        if ( '' === $top && '' === $right && '' === $bottom && '' === $left ) {
            return '';
        }

        $unit = isset( $dimensions['unit'] ) ? trim( (string) $dimensions['unit'] ) : 'px';

        if ( ! in_array( $unit, [ 'px', '%', 'em', 'rem', 'vh', 'vw' ], true ) ) {
            $unit = 'px';
        }

        $top    = '' === $top ? '0' : $top;
        $right  = '' === $right ? '0' : $right;
        $bottom = '' === $bottom ? '0' : $bottom;
        $left   = '' === $left ? '0' : $left;

        return sprintf( '%1$s:%2$s%6$s %3$s%6$s %4$s%6$s %5$s%6$s;', $property, $top, $right, $bottom, $left, $unit );
    }

    private function build_position_style( string $position ): string {
        $map = [
            'top-left'      => 'position:absolute;top:0;left:0;z-index:3;',
            'top-center'    => 'position:absolute;top:0;left:50%;transform:translateX(-50%);z-index:3;',
            'top-right'     => 'position:absolute;top:0;right:0;z-index:3;',
            'center-left'   => 'position:absolute;top:50%;left:0;transform:translateY(-50%);z-index:3;',
            'center-center' => 'position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:3;',
            'center-right'  => 'position:absolute;top:50%;right:0;transform:translateY(-50%);z-index:3;',
            'bottom-left'   => 'position:absolute;bottom:0;left:0;z-index:3;',
            'bottom-center' => 'position:absolute;bottom:0;left:50%;transform:translateX(-50%);z-index:3;',
            'bottom-right'  => 'position:absolute;bottom:0;right:0;z-index:3;',
        ];

        return $map[ $position ] ?? '';
    }

    private function build_slider_style( $slider, string $property ): string {
        if ( ! is_array( $slider ) || ! isset( $slider['size'] ) || '' === (string) $slider['size'] ) {
            return '';
        }

        $unit = isset( $slider['unit'] ) ? (string) $slider['unit'] : 'px';

        if ( ! in_array( $unit, [ 'px', '%', 'vw', 'vh', 'em', 'rem' ], true ) ) {
            $unit = 'px';
        }

        return $property . ':' . $slider['size'] . $unit . ' !important;';
    }

    private function resolve_color_value( $value ): string {
        $raw = '';

        if ( is_string( $value ) ) {
            $raw = trim( $value );
        }

        if ( '' === $raw && is_array( $value ) ) {
            foreach ( [ 'value', 'color' ] as $key ) {
                if ( ! empty( $value[ $key ] ) && is_string( $value[ $key ] ) ) {
                    $raw = trim( $value[ $key ] );
                    break;
                }
            }
        }

        if ( '' === $raw ) {
            return '';
        }

        if ( preg_match( '/^globals\\/colors\\?id=([a-zA-Z0-9_-]+)$/', $raw, $matches ) ) {
            return 'var(--e-global-color-' . $matches[1] . ')';
        }

        if ( 0 === strpos( $raw, 'var(--e-global-color-' ) ) {
            return $raw;
        }

        if ( preg_match( '/^[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $raw ) ) {
            return '#' . $raw;
        }

        return $raw;
    }

    private function find_global_reference_for_slide_field( array $globals, array $slide, int $slide_index, string $field ): string {
        if ( empty( $globals ) ) {
            return '';
        }

        $slide_id = isset( $slide['_id'] ) ? (string) $slide['_id'] : '';

        $direct_candidates = [
            "slides.$slide_index.$field",
            "slides[$slide_index].$field",
            "slides[$slide_index][$field]",
        ];

        if ( '' !== $slide_id ) {
            $direct_candidates[] = "slides.$slide_id.$field";
            $direct_candidates[] = "slides[$slide_id].$field";
            $direct_candidates[] = "slides[$slide_id][$field]";
        }

        foreach ( $direct_candidates as $candidate_key ) {
            if ( isset( $globals[ $candidate_key ] ) && is_string( $globals[ $candidate_key ] ) ) {
                return $globals[ $candidate_key ];
            }
        }

        $matches = [];

        $walker = static function ( $node, string $path = '' ) use ( &$walker, &$matches, $field, $slide_index, $slide_id ): void {
            if ( is_array( $node ) ) {
                foreach ( $node as $key => $value ) {
                    $next_path = '' === $path ? (string) $key : $path . '.' . $key;
                    $walker( $value, $next_path );
                }
                return;
            }

            if ( ! is_string( $node ) ) {
                return;
            }

            $value = trim( $node );
            if ( ! preg_match( '/^globals\\/colors\\?id=[a-zA-Z0-9_-]+$/', $value ) ) {
                return;
            }

            if ( false === strpos( $path, $field ) ) {
                return;
            }

            $score = 1;

            if ( false !== strpos( $path, (string) $slide_index ) ) {
                $score += 2;
            }

            if ( '' !== $slide_id && false !== strpos( $path, $slide_id ) ) {
                $score += 3;
            }

            if ( false !== strpos( $path, 'slides' ) ) {
                $score += 1;
            }

            $matches[] = [
                'score' => $score,
                'value' => $value,
            ];
        };

        $walker( $globals );

        if ( ! empty( $matches ) ) {
            usort(
                $matches,
                static function ( array $a, array $b ): int {
                    return $b['score'] <=> $a['score'];
                }
            );

            return $matches[0]['value'];
        }

        return '';
    }

    private function resolve_color_from_slide( array $slide, string $field, array $settings_globals = [], int $slide_index = 0 ): string {
        // Global token should take precedence over raw/default value.
        if ( isset( $slide['__globals__'][ $field ] ) && is_string( $slide['__globals__'][ $field ] ) ) {
            $global_resolved = $this->resolve_color_value( $slide['__globals__'][ $field ] );
            if ( '' !== $global_resolved ) {
                return $global_resolved;
            }
        }

        $global_reference = $this->find_global_reference_for_slide_field( $settings_globals, $slide, $slide_index, $field );
        if ( '' !== $global_reference ) {
            $global_resolved = $this->resolve_color_value( $global_reference );
            if ( '' !== $global_resolved ) {
                return $global_resolved;
            }
        }

        return $this->resolve_color_value( $slide[ $field ] ?? '' );
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $slides   = $settings['slides'] ?? [];
        $settings_globals = ( isset( $settings['__globals__'] ) && is_array( $settings['__globals__'] ) ) ? $settings['__globals__'] : [];

        if ( empty( $slides ) ) {
            return;
        }

        $config = [
            'effect'               => $settings['effect'] ?? 'slide',
            'slidesPerView'        => max( 1, (int) ( $settings['slides_per_view'] ?? 1 ) ),
            'slidesPerViewTablet'  => max( 1, (int) ( $settings['slides_per_view_tablet'] ?? 1 ) ),
            'slidesPerViewMobile'  => max( 1, (int) ( $settings['slides_per_view_mobile'] ?? 1 ) ),
            'spaceBetween'         => max( 0, (int) ( $settings['space_between'] ?? 24 ) ),
            'spaceBetweenTablet'   => max( 0, (int) ( $settings['space_between_tablet'] ?? 20 ) ),
            'spaceBetweenMobile'   => max( 0, (int) ( $settings['space_between_mobile'] ?? 16 ) ),
            'speed'                => max( 100, (int) ( $settings['speed'] ?? 600 ) ),
            'loop'                 => ( 'yes' === ( $settings['loop'] ?? '' ) ),
            'autoplay'             => ( 'yes' === ( $settings['autoplay'] ?? '' ) ),
            'autoplayDelay'        => max( 500, (int) ( $settings['autoplay_delay'] ?? 3000 ) ),
            'pauseOnHover'         => ( 'yes' === ( $settings['pause_on_hover'] ?? '' ) ),
            'showArrows'           => ( 'yes' === ( $settings['show_arrows'] ?? '' ) ),
            'showPagination'       => ( 'yes' === ( $settings['show_pagination'] ?? '' ) ),
            'autoHeight'           => ( 'yes' === ( $settings['auto_height'] ?? '' ) ),
            'grabCursor'           => ( 'yes' === ( $settings['grab_cursor'] ?? '' ) ),
        ];

        $config_json = wp_json_encode( $config );
        $wrapper_id  = 'cec-acz-elements-' . $this->get_id();
        ?>
        <style>
            #<?php echo esc_attr( $wrapper_id ); ?> {
                position: relative;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .swiper {
                padding-bottom: 0;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .swiper-slide {
                height: auto;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-slide-card {
                position: relative;
                overflow: hidden;
                height: 100%;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-slide-content {
                position: relative;
                z-index: 2;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-slide-overlay {
                position: absolute;
                inset: 0;
                z-index: 1;
                pointer-events: none;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-carousel-navigation {
                --cec-nav-offset-x: 0px;
                --cec-nav-offset-y: 0px;
                position: absolute;
                left: 50%;
                bottom: 24px;
                transform: translate(calc(-50% + var(--cec-nav-offset-x, 0px)), var(--cec-nav-offset-y, 0px));
                z-index: 6;
                display: flex;
                align-items: center;
                justify-content: center;
                column-gap: 14px;
                row-gap: 10px;
                flex-wrap: wrap;
                margin-top: 0;
                width: max-content;
                max-width: calc(100% - 24px);
            }
            #<?php echo esc_attr( $wrapper_id ); ?>.is-nav-outside .cec-carousel-navigation {
                position: relative;
                left: auto;
                bottom: auto;
                transform: none;
                margin-top: 16px;
                width: auto;
                max-width: 100%;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-nav-button {
                position: relative;
                inset: auto;
                margin: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 auto;
                border: none;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-nav-button::after {
                content: none !important;
                display: none !important;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-nav-icon,
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-nav-icon > i,
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-nav-icon > svg {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1em;
                height: 1em;
                line-height: 1;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .swiper-pagination {
                position: relative;
                inset: auto;
                width: auto;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin: 0;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-slide-background {
                --cec-bg-media-fit: cover;
                --cec-bg-media-pos-x: 50%;
                --cec-bg-media-pos-y: 50%;
                --cec-bg-iframe-offset-x: 0px;
                --cec-bg-iframe-offset-y: 0px;
                position: absolute;
                inset: 0;
                overflow: hidden;
                z-index: 0;
                background: #000;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-background-image,
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-background-video,
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-background-video-embed {
                position: absolute;
                inset: 0;
                display: block;
                width: 100% !important;
                height: 100% !important;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-background-image,
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-background-video {
                min-width: 100%;
                min-height: 100%;
                max-width: none;
                max-height: none;
                object-fit: var(--cec-bg-media-fit, cover);
                object-position: var(--cec-bg-media-pos-x, 50%) var(--cec-bg-media-pos-y, 50%);
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-background-video {
                background: #000;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-background-video-embed {
                overflow: hidden;
                pointer-events: none;
                background: #000;
                --cec-bg-iframe-scale: 1.35;
            }
            #<?php echo esc_attr( $wrapper_id ); ?> .cec-background-video-iframe {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 100%;
                height: 100%;
                min-width: 100%;
                min-height: 100%;
                transform: translate(
                    calc(-50% + var(--cec-bg-iframe-offset-x, 0px)),
                    calc(-50% + var(--cec-bg-iframe-offset-y, 0px))
                ) scale(var(--cec-bg-iframe-scale, 1.35));
                transform-origin: center center;
                pointer-events: none;
                border: 0;
            }
        </style>
        <div id="<?php echo esc_attr( $wrapper_id ); ?>" class="cec-effects-carousel<?php echo esc_attr( ( 'yes' === ( $settings['navigation_inside_slides'] ?? 'yes' ) ) ? ' is-nav-inside' : ' is-nav-outside' ); ?>" data-cec-config="<?php echo esc_attr( $config_json ); ?>">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php foreach ( $slides as $slide_index => $slide ) : ?>
                        <?php
                        $slide = wp_parse_args(
                            $slide
                        );

                        $background_type          = $slide['background_type'] ?? 'none';
                        $has_background_image     = 'image' === $background_type && ( ! empty( $slide['background_image']['id'] ) || ! empty( $slide['background_image']['url'] ) );
                        $background_video_mp4     = ! empty( $slide['background_video_mp4'] ) ? trim( $slide['background_video_mp4'] ) : '';
                        $background_video_webm    = ! empty( $slide['background_video_webm'] ) ? trim( $slide['background_video_webm'] ) : '';
                        $background_video_url     = ! empty( $slide['background_video_url'] ) ? trim( $slide['background_video_url'] ) : '';
                        $youtube_source_url       = '';
                        $youtube_embed_url        = '';

                        foreach ( [ $background_video_mp4, $background_video_webm, $background_video_url ] as $candidate_video_url ) {
                            if ( ! empty( $candidate_video_url ) ) {
                                $youtube_embed_url = $this->get_youtube_embed_url( $candidate_video_url );
                                if ( ! empty( $youtube_embed_url ) ) {
                                    $youtube_source_url = $candidate_video_url;
                                    break;
                                }
                            }
                        }

                        $has_background_youtube = 'video' === $background_type && ! empty( $youtube_embed_url );
                        $has_background_video   = 'video' === $background_type && ( $has_background_youtube || ! empty( $background_video_mp4 ) || ! empty( $background_video_webm ) || ! empty( $background_video_url ) );
                        $has_background_media   = $has_background_image || $has_background_video;
                        $overlay_color          = $slide['background_overlay_color'] ?? '';
                        $poster_url             = $slide['background_video_poster']['url'] ?? '';
                        $background_fit         = ! empty( $slide['background_media_fit'] ) ? $slide['background_media_fit'] : 'cover';
                        $background_position_x  = isset( $slide['background_position_x']['size'] ) ? $slide['background_position_x']['size'] . ( $slide['background_position_x']['unit'] ?? '%' ) : '50%';
                        $background_position_y  = isset( $slide['background_position_y']['size'] ) ? $slide['background_position_y']['size'] . ( $slide['background_position_y']['unit'] ?? '%' ) : '50%';
                        $background_iframe_x    = isset( $slide['background_iframe_offset_x']['size'] ) ? $slide['background_iframe_offset_x']['size'] . ( $slide['background_iframe_offset_x']['unit'] ?? 'px' ) : '0px';
                        $background_iframe_y    = isset( $slide['background_iframe_offset_y']['size'] ) ? $slide['background_iframe_offset_y']['size'] . ( $slide['background_iframe_offset_y']['unit'] ?? 'px' ) : '0px';
                        $background_media_style = sprintf(
                            '--cec-bg-media-fit:%1$s;--cec-bg-media-pos-x:%2$s;--cec-bg-media-pos-y:%3$s;--cec-bg-iframe-offset-x:%4$s;--cec-bg-iframe-offset-y:%5$s;',
                            esc_attr( $background_fit ),
                            esc_attr( $background_position_x ),
                            esc_attr( $background_position_y ),
                            esc_attr( $background_iframe_x ),
                            esc_attr( $background_iframe_y )
                        );
                        ?>
                        <div class="swiper-slide">
                            <div class="cec-slide-card<?php echo esc_attr( $has_background_media ? ' has-background-media' : '' ); ?>">
                                <?php if ( $has_background_media ) : ?>
                                    <div class="cec-slide-background" aria-hidden="true" style="<?php echo esc_attr( $background_media_style ); ?>">
                                        <?php if ( $has_background_image ) : ?>
                                            <?php if ( ! empty( $slide['background_image']['id'] ) ) : ?>
                                                <?php echo wp_kses_post( wp_get_attachment_image( (int) $slide['background_image']['id'], 'full', false, [ 'class' => 'cec-background-image' ] ) ); ?>
                                            <?php else : ?>
                                                <img class="cec-background-image" src="<?php echo esc_url( $slide['background_image']['url'] ); ?>" alt="" loading="lazy">
                                            <?php endif; ?>
                                        <?php elseif ( $has_background_video ) : ?>
                                            <?php if ( $has_background_youtube ) : ?>
                                                <div class="cec-background-video cec-background-video-embed">
                                                    <iframe
                                                        class="cec-background-video-iframe"
                                                        src="<?php echo esc_url( $youtube_embed_url ); ?>"
                                                        title="<?php echo esc_attr( wp_strip_all_tags( $slide['title'] ?? 'Background video' ) ); ?>"
                                                        loading="lazy"
                                                        frameborder="0"
                                                        allow="autoplay; encrypted-media; picture-in-picture"
                                                        referrerpolicy="strict-origin-when-cross-origin"
                                                        tabindex="-1"
                                                        aria-hidden="true"
                                                    ></iframe>
                                                </div>
                                            <?php else : ?>
                                                <video
                                                    class="cec-background-video"
                                                    autoplay
                                                    muted
                                                    loop
                                                    playsinline
                                                    webkit-playsinline
                                                    preload="auto"
                                                    <?php if ( $poster_url ) : ?>
                                                        poster="<?php echo esc_url( $poster_url ); ?>"
                                                    <?php endif; ?>
                                                >
                                                    <?php if ( ! empty( $background_video_webm ) && empty( $this->get_youtube_embed_url( $background_video_webm ) ) ) : ?>
                                                        <source src="<?php echo esc_url( $background_video_webm ); ?>" type="video/webm">
                                                    <?php endif; ?>
                                                    <?php if ( ! empty( $background_video_mp4 ) && empty( $this->get_youtube_embed_url( $background_video_mp4 ) ) ) : ?>
                                                        <source src="<?php echo esc_url( $background_video_mp4 ); ?>" type="video/mp4">
                                                    <?php endif; ?>
                                                    <?php if ( empty( $background_video_mp4 ) && empty( $background_video_webm ) && ! empty( $background_video_url ) && empty( $this->get_youtube_embed_url( $background_video_url ) ) ) : ?>
                                                        <source src="<?php echo esc_url( $background_video_url ); ?>" type="video/mp4">
                                                    <?php endif; ?>
                                                </video>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ( ! empty( $overlay_color ) ) : ?>
                                            <span class="cec-slide-overlay" style="background-color: <?php echo esc_attr( $overlay_color ); ?>;"></span>
                                        <?php endif; ?>

                                    </div>
                                <?php endif; ?>

                                <div class="cec-slide-content">
                                    <?php
                                    $title_style = $this->build_position_style( $slide['title_position'] ?? 'flow' );
                                    $title_style .= $this->build_dimensions_style( $slide['title_margin'] ?? [], 'margin' );
                                    $title_style .= $this->build_dimensions_style( $slide['title_padding'] ?? [], 'padding' );
                                    $title_style .= $this->build_slider_style( $slide['title_max_width'] ?? null, 'max-width' );
                                    if ( isset( $slide['title_width']['size'] ) && '' !== (string) $slide['title_width']['size'] ) {
                                        $title_width_unit = $slide['title_width']['unit'] ?? 'px';
                                        $title_style .= 'width:' . $slide['title_width']['size'] . $title_width_unit . ';';
                                    }
                                    $title_color = $this->resolve_color_from_slide( $slide, 'title_color', $settings_globals, (int) $slide_index );
                                    if ( '' !== $title_color ) {
                                        $title_style .= 'color:' . $title_color . ' !important;';
                                    }
                                    if ( ! empty( $slide['title_alignment'] ) ) {
                                        $title_style .= 'text-align:' . $slide['title_alignment'] . ' !important;';
                                    }
                                    if ( ! empty( $slide['title_align_self'] ) && in_array( $slide['title_align_self'], [ 'flex-start', 'center', 'flex-end', 'stretch' ], true ) ) {
                                        $title_style .= 'align-self:' . $slide['title_align_self'] . ';';
                                    }
                                    ?>
                                    <?php if ( ! empty( $slide['title'] ) ) : ?>
                                        <?php
                                        $title_tag = tag_escape( $slide['title_tag'] ?? 'h1' );
                                        ?>
                                        <<?php echo esc_html( $title_tag ); ?> class="cec-slide-title"<?php if ( '' !== $title_style ) : ?> style="<?php echo esc_attr( $title_style ); ?>"<?php endif; ?>><?php echo esc_html( $slide['title'] ); ?></<?php echo esc_html( $title_tag ); ?>>
                                    <?php endif; ?>

                                    <?php
                                    $description_style = $this->build_position_style( $slide['description_position'] ?? 'flow' );
                                    $description_style .= $this->build_dimensions_style( $slide['description_margin'] ?? [], 'margin' );
                                    $description_style .= $this->build_dimensions_style( $slide['description_padding'] ?? [], 'padding' );
                                    $description_style .= $this->build_slider_style( $slide['description_max_width'] ?? null, 'max-width' );
                                    if ( isset( $slide['description_width']['size'] ) && '' !== (string) $slide['description_width']['size'] ) {
                                        $description_width_unit = $slide['description_width']['unit'] ?? 'px';
                                        $description_style .= 'width:' . $slide['description_width']['size'] . $description_width_unit . ';';
                                    }
                                    $description_color = $this->resolve_color_from_slide( $slide, 'description_color', $settings_globals, (int) $slide_index );
                                    if ( '' !== $description_color ) {
                                        $description_style .= 'color:' . $description_color . ' !important;';
                                    }
                                    if ( ! empty( $slide['description_alignment'] ) ) {
                                        $description_style .= 'text-align:' . $slide['description_alignment'] . ' !important;';
                                    }
                                    if ( ! empty( $slide['description_align_self'] ) && in_array( $slide['description_align_self'], [ 'flex-start', 'center', 'flex-end', 'stretch' ], true ) ) {
                                        $description_style .= 'align-self:' . $slide['description_align_self'] . ';';
                                    }
                                    ?>
                                    <?php if ( ! empty( $slide['description'] ) ) : ?>
                                        <div class="cec-slide-description"<?php if ( '' !== $description_style ) : ?> style="<?php echo esc_attr( $description_style ); ?>"<?php endif; ?>><?php echo wp_kses_post( $slide['description'] ); ?></div>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $slide['button_text'] ) ) : ?>
                                        <?php
                                        $url         = $slide['button_link']['url'] ?? '';
                                        $is_external = ! empty( $slide['button_link']['is_external'] );
                                        $nofollow    = ! empty( $slide['button_link']['nofollow'] );
                                        $rel_parts   = [];
                                        $button_actions_style = '';
                                        $button_style = $this->build_position_style( $slide['button_position'] ?? 'flow' );
                                        $button_style .= $this->build_dimensions_style( $slide['button_margin'] ?? [], 'margin' );
                                        $button_style .= $this->build_dimensions_style( $slide['button_padding'] ?? [], 'padding' );
                                        $button_style .= $this->build_slider_style( $slide['button_max_width'] ?? null, 'max-width' );
                                        if ( isset( $slide['button_width']['size'] ) && '' !== (string) $slide['button_width']['size'] ) {
                                            $button_width_unit = $slide['button_width']['unit'] ?? 'px';
                                            $button_style .= 'width:' . $slide['button_width']['size'] . $button_width_unit . ';';
                                        }
                                        $button_color = $this->resolve_color_from_slide( $slide, 'button_color', $settings_globals, (int) $slide_index );
                                        if ( '' !== $button_color ) {
                                            $button_style .= 'color:' . $button_color . ' !important;';
                                        }
                                        if ( ! empty( $slide['button_alignment'] ) ) {
                                            $button_actions_style = 'text-align:' . $slide['button_alignment'] . ' !important;';
                                        }
                                        if ( ! empty( $slide['button_align_self'] ) && in_array( $slide['button_align_self'], [ 'flex-start', 'center', 'flex-end', 'stretch' ], true ) ) {
                                            $button_actions_style .= 'align-self:' . $slide['button_align_self'] . ';';
                                        }

                                        if ( $nofollow ) {
                                            $rel_parts[] = 'nofollow';
                                        }

                                        if ( $is_external ) {
                                            $rel_parts[] = 'noopener';
                                        }
                                        ?>
                                        <div class="cec-slide-actions"<?php if ( '' !== $button_actions_style ) : ?> style="<?php echo esc_attr( $button_actions_style ); ?>"<?php endif; ?>>
                                            <a
                                                class="cec-slide-button"
                                                href="<?php echo esc_url( $url ?: '#' ); ?>"
                                                <?php if ( $is_external ) : ?>
                                                    target="_blank"
                                                <?php endif; ?>
                                                <?php if ( ! empty( $rel_parts ) ) : ?>
                                                    rel="<?php echo esc_attr( implode( ' ', array_unique( $rel_parts ) ) ); ?>"
                                                <?php endif; ?>
                                                <?php if ( '' !== $button_style ) : ?>
                                                    style="<?php echo esc_attr( $button_style ); ?>"
                                                <?php endif; ?>
                                            >
                                                <?php echo esc_html( $slide['button_text'] ); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <?php if ( 'yes' === ( $settings['show_pagination'] ?? '' ) || 'yes' === ( $settings['show_arrows'] ?? '' ) ) : ?>
                <div class="cec-carousel-navigation">
                    <?php if ( 'yes' === ( $settings['show_arrows'] ?? '' ) ) : ?>
                        <button type="button" class="swiper-button-prev cec-nav-button cec-nav-button-prev" aria-label="<?php echo esc_attr__( 'Previous slide', 'acz-elements' ); ?>">
                            <?php $this->render_nav_icon( $settings['arrow_prev_icon'] ?? [], 'eicon-chevron-left' ); ?>
                        </button>
                    <?php endif; ?>

                    <?php if ( 'yes' === ( $settings['show_pagination'] ?? '' ) ) : ?>
                        <div class="swiper-pagination"></div>
                    <?php endif; ?>

                    <?php if ( 'yes' === ( $settings['show_arrows'] ?? '' ) ) : ?>
                        <button type="button" class="swiper-button-next cec-nav-button cec-nav-button-next" aria-label="<?php echo esc_attr__( 'Next slide', 'acz-elements' ); ?>">
                            <?php $this->render_nav_icon( $settings['arrow_next_icon'] ?? [], 'eicon-chevron-right' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
