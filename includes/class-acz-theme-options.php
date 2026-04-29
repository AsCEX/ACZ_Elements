<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class ACZ_Theme_Options {
    const OPTION_KEY = 'acz_theme_options';
    const PAGE_SLUG  = 'acz-theme-options';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'acf/init', [ $this, 'register_acf_options_page' ] );
    }

    public static function get_defaults(): array {
        return [
            'tagline'                      => '',
            'widget_carousel_enabled'      => true,
            'widget_post_gallery_enabled'  => true,
            'widget_post_filter_enabled'   => true,
            'widget_breadcrumbs_enabled'   => true,
            'widget_taxonomy_list_enabled' => true,
            'widget_featured_post_enabled' => true,
            'widget_post_carousel_enabled' => true,
            'widget_post_tabs_enabled'     => true,
            'widget_faq_enabled'           => true,
            'widget_post_list_enabled'     => true,
            'widget_taxonomy_enabled'      => true,
            'widget_custom_meta_enabled'   => true,
            'widget_media_gallery_enabled' => true,
            'widget_post_content_enabled'  => true,
            'widget_logo_carousel_enabled' => true,
            'taxonomy_add_form_collapsed'  => true,
            'custom_lighting'              => [],
            'faq'                          => [],
        ];
    }

    public static function get_all(): array {
        $defaults = self::get_defaults();
        $saved    = get_option( self::OPTION_KEY, [] );
        $saved    = is_array( $saved ) ? $saved : [];
        $options  = $defaults;

        foreach ( $defaults as $key => $default ) {
            if ( ! array_key_exists( $key, $saved ) ) {
                continue;
            }

            if ( is_bool( $default ) ) {
                $options[ $key ] = (bool) $saved[ $key ];
                continue;
            }

            if ( is_array( $default ) ) {
                $options[ $key ] = is_array( $saved[ $key ] ) ? $saved[ $key ] : [];
                continue;
            }

            $options[ $key ] = is_scalar( $saved[ $key ] ) ? (string) $saved[ $key ] : $default;
        }

        return $options;
    }

    public static function get( string $key, $default = null ) {
        $options = self::get_all();

        if ( array_key_exists( $key, $options ) ) {
            return $options[ $key ];
        }

        return $default;
    }

    public static function is_widget_enabled( string $widget_key ): bool {
        return (bool) self::get( 'widget_' . sanitize_key( $widget_key ) . '_enabled', true );
    }

    public function register_menu() {
        if ( function_exists( 'acf_add_options_page' ) ) {
            return;
        }

        add_menu_page(
            esc_html__( 'ACZ Theme Options', 'acz-elements' ),
            esc_html__( 'ACZ Options', 'acz-elements' ),
            'edit_theme_options',
            self::PAGE_SLUG,
            [ $this, 'render_page' ],
            'dashicons-sort',
            4
        );
    }

    public function register_acf_options_page() {
        if ( ! function_exists( 'acf_add_options_page' ) ) {
            return;
        }

        acf_add_options_page(
            [
                'page_title' => esc_html__( 'ACZ Theme Options', 'acz-elements' ),
                'menu_title' => esc_html__( 'ACZ Options', 'acz-elements' ),
                'menu_slug'  => self::PAGE_SLUG,
                'capability' => 'edit_theme_options',
                'position'   => 4,
                'icon_url' => 'dashicons-sort',
                'redirect'   => false,
            ]
        );
    }

    public function register_settings() {
        register_setting(
            'acz_theme_options_group',
            self::OPTION_KEY,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_options' ],
                'default'           => self::get_defaults(),
            ]
        );
    }

    public function sanitize_options( $input ): array {
        $defaults  = self::get_defaults();
        $current   = self::get_all();
        $sanitized = $defaults;
        $input     = is_array( $input ) ? $input : [];

        $sanitized['tagline'] = isset( $input['tagline'] ) ? sanitize_text_field( wp_unslash( (string) $input['tagline'] ) ) : '';

        $checkboxes = [
            'widget_carousel_enabled',
            'widget_post_gallery_enabled',
            'widget_post_filter_enabled',
            'widget_breadcrumbs_enabled',
            'widget_taxonomy_list_enabled',
            'widget_featured_post_enabled',
            'widget_post_carousel_enabled',
            'widget_post_tabs_enabled',
            'widget_faq_enabled',
            'widget_post_list_enabled',
            'widget_taxonomy_enabled',
            'widget_custom_meta_enabled',
            'widget_media_gallery_enabled',
            'widget_post_content_enabled',
            'widget_logo_carousel_enabled',
            'taxonomy_add_form_collapsed',
        ];

        foreach ( $checkboxes as $key ) {
            if ( array_key_exists( $key, $input ) ) {
                $sanitized[ $key ] = (bool) $input[ $key ];
            } else {
                $sanitized[ $key ] = false;
            }
        }

        $sanitized['custom_lighting'] = $this->sanitize_repeater_items(
            isset( $input['custom_lighting'] ) ? $input['custom_lighting'] : [],
            [ 'title', 'image', 'image_icon', 'description' ]
        );
        $sanitized['faq'] = $this->sanitize_repeater_items(
            isset( $input['faq'] ) ? $input['faq'] : [],
            [ 'title', 'content' ]
        );

        if ( ! array_key_exists( 'taxonomy_add_form_collapsed', $input ) ) {
            $sanitized['taxonomy_add_form_collapsed'] = (bool) $current['taxonomy_add_form_collapsed'];
        }

        return $sanitized;
    }

    private function sanitize_repeater_items( $items, array $allowed_keys ): array {
        if ( ! is_array( $items ) ) {
            return [];
        }

        $sanitized = [];

        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $row = [];
            foreach ( $allowed_keys as $key ) {
                $raw = isset( $item[ $key ] ) ? (string) $item[ $key ] : '';
                if ( 'image' === $key || 'image_icon' === $key ) {
                    $row[ $key ] = esc_url_raw( wp_unslash( $raw ) );
                } else {
                    $row[ $key ] = sanitize_textarea_field( wp_unslash( $raw ) );
                }
            }

            $has_content = false;
            foreach ( $row as $value ) {
                if ( '' !== trim( (string) $value ) ) {
                    $has_content = true;
                    break;
                }
            }

            if ( $has_content ) {
                $sanitized[] = $row;
            }
        }

        return $sanitized;
    }

    public function enqueue_admin_assets( string $hook ) {
        if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script( 'jquery' );
    }

    public function render_page() {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return;
        }

        $options = self::get_all();
        $widgets = [
            'widget_carousel_enabled'      => esc_html__( 'ACZ Carousel', 'acz-elements' ),
            'widget_post_gallery_enabled'  => esc_html__( 'ACZ Post Gallery', 'acz-elements' ),
            'widget_post_filter_enabled'   => esc_html__( 'ACZ Post Filter', 'acz-elements' ),
            'widget_breadcrumbs_enabled'   => esc_html__( 'ACZ Breadcrumbs', 'acz-elements' ),
            'widget_taxonomy_list_enabled' => esc_html__( 'ACZ Taxonomy List', 'acz-elements' ),
            'widget_featured_post_enabled' => esc_html__( 'ACZ Featured Post', 'acz-elements' ),
            'widget_post_carousel_enabled' => esc_html__( 'ACZ Post Carousel', 'acz-elements' ),
            'widget_post_tabs_enabled'     => esc_html__( 'ACZ Post Tabs', 'acz-elements' ),
            'widget_faq_enabled'           => esc_html__( 'ACZ FAQ', 'acz-elements' ),
            'widget_post_list_enabled'     => esc_html__( 'ACZ Post List', 'acz-elements' ),
            'widget_taxonomy_enabled'      => esc_html__( 'ACZ Taxonomy', 'acz-elements' ),
            'widget_custom_meta_enabled'   => esc_html__( 'ACZ Custom Meta', 'acz-elements' ),
            'widget_media_gallery_enabled' => esc_html__( 'ACZ Media Gallery', 'acz-elements' ),
            'widget_post_content_enabled'  => esc_html__( 'ACZ Post Content', 'acz-elements' ),
            'widget_logo_carousel_enabled' => esc_html__( 'ACZ Logo Carousel', 'acz-elements' ),
        ];
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'ACZ Theme Options', 'acz-elements' ); ?></h1>
            <style>
                .acz-tabs-nav {
                    display: flex;
                    gap: 8px;
                    border-bottom: 1px solid #dcdcde;
                    margin: 16px 0 18px;
                    padding-bottom: 10px;
                }
                .acz-tab-btn {
                    border: 1px solid #dcdcde;
                    border-radius: 4px;
                    background: #f6f7f7;
                    padding: 8px 12px;
                    font-weight: 600;
                    cursor: pointer;
                }
                .acz-tab-btn.is-active {
                    background: #fff;
                    border-color: #2271b1;
                    color: #2271b1;
                }
                .acz-tab-panel {
                    display: none;
                    max-width: 980px;
                }
                .acz-tab-panel.is-active {
                    display: block;
                }
                .acz-field {
                    margin: 0 0 16px;
                }
                .acz-field label {
                    display: block;
                    margin-bottom: 6px;
                    font-weight: 600;
                }
                .acz-field input[type="text"],
                .acz-field textarea {
                    width: 100%;
                    max-width: 700px;
                }
                .acz-checkbox-list label {
                    display: block;
                    margin-bottom: 8px;
                }
                .acz-repeater {
                    margin-top: 10px;
                }
                .acz-repeater-item {
                    border: 1px solid #dcdcde;
                    border-radius: 4px;
                    padding: 14px;
                    margin: 0 0 12px;
                    background: #fff;
                    max-width: 900px;
                }
                .acz-actions {
                    margin-top: 8px;
                }
                .acz-media-row {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                .acz-media-row input[type="text"] {
                    flex: 1;
                }
            </style>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'acz_theme_options_group' );
                submit_button();
                ?>
                <div class="acz-tabs-nav">
                    <button type="button" class="acz-tab-btn is-active" data-tab="general"><?php echo esc_html__( 'General', 'acz-elements' ); ?></button>
                    <button type="button" class="acz-tab-btn" data-tab="widgets"><?php echo esc_html__( 'Widgets', 'acz-elements' ); ?></button>
                    <button type="button" class="acz-tab-btn" data-tab="custom-lighting"><?php echo esc_html__( 'Custom Lighting', 'acz-elements' ); ?></button>
                    <button type="button" class="acz-tab-btn" data-tab="faq"><?php echo esc_html__( 'FAQ', 'acz-elements' ); ?></button>
                </div>

                <div class="acz-tab-panel is-active" data-panel="general">
                    <div class="acz-field">
                        <label for="acz-tagline"><?php echo esc_html__( 'Tagline', 'acz-elements' ); ?></label>
                        <input id="acz-tagline" type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[tagline]" value="<?php echo esc_attr( (string) $options['tagline'] ); ?>" />
                    </div>
                    <div class="acz-field">
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[taxonomy_add_form_collapsed]" value="1" <?php checked( ! empty( $options['taxonomy_add_form_collapsed'] ) ); ?> />
                            <?php echo esc_html__( 'Collapse Taxonomy Add Form by Default', 'acz-elements' ); ?>
                        </label>
                    </div>
                </div>

                <div class="acz-tab-panel" data-panel="widgets">
                    <div class="acz-checkbox-list">
                        <?php foreach ( $widgets as $key => $label ) : ?>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $options[ $key ] ) ); ?> />
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="acz-tab-panel" data-panel="custom-lighting">
                    <div class="acz-repeater" data-repeater="custom_lighting" data-title="Custom Lighting">
                        <?php $this->render_repeater_items( 'custom_lighting', $options['custom_lighting'], [ 'title', 'image', 'image_icon', 'description' ] ); ?>
                    </div>
                    <div class="acz-actions">
                        <button type="button" class="button button-secondary acz-add-repeater" data-repeater="custom_lighting"><?php echo esc_html__( 'Add Lighting Item', 'acz-elements' ); ?></button>
                    </div>
                </div>

                <div class="acz-tab-panel" data-panel="faq">
                    <div class="acz-repeater" data-repeater="faq" data-title="FAQ">
                        <?php $this->render_repeater_items( 'faq', $options['faq'], [ 'title', 'content' ] ); ?>
                    </div>
                    <div class="acz-actions">
                        <button type="button" class="button button-secondary acz-add-repeater" data-repeater="faq"><?php echo esc_html__( 'Add FAQ Item', 'acz-elements' ); ?></button>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <script>
            (function ($) {
                var $tabs = $('.acz-tab-btn');
                var $panels = $('.acz-tab-panel');
                $tabs.on('click', function () {
                    var tab = $(this).data('tab');
                    $tabs.removeClass('is-active');
                    $(this).addClass('is-active');
                    $panels.removeClass('is-active');
                    $('.acz-tab-panel[data-panel="' + tab + '"]').addClass('is-active');
                });

                function createField(name, value, label, isTextarea, isMedia) {
                    var $field = $('<div class="acz-field"></div>');
                    $field.append($('<label></label>').text(label));
                    if (isMedia) {
                        var $row = $('<div class="acz-media-row"></div>');
                        var $input = $('<input type="text" />').attr('name', name).val(value || '');
                        var $btn = $('<button type="button" class="button button-secondary acz-media-select">Select Image</button>');
                        $row.append($input).append($btn);
                        $field.append($row);
                        return $field;
                    }
                    if (isTextarea) {
                        $field.append($('<textarea rows="4"></textarea>').attr('name', name).val(value || ''));
                    } else {
                        $field.append($('<input type="text" />').attr('name', name).val(value || ''));
                    }
                    return $field;
                }

                function openMediaPicker($input) {
                    var frame = wp.media({ title: 'Select Image', multiple: false });
                    frame.on('select', function () {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $input.val(attachment.url);
                    });
                    frame.open();
                }

                function reindexRepeater(repeaterName) {
                    var fields = repeaterName === 'custom_lighting'
                        ? ['title', 'image', 'image_icon', 'description']
                        : ['title', 'content'];

                    $('.acz-repeater[data-repeater="' + repeaterName + '"] .acz-repeater-item').each(function (rowIndex) {
                        $(this).find('input, textarea').each(function () {
                            var $field = $(this);
                            var match = ($field.attr('name') || '').match(/\[([a-z_]+)\]$/i);
                            if (!match) {
                                return;
                            }
                            var fieldName = match[1];
                            if (fields.indexOf(fieldName) === -1) {
                                return;
                            }
                            $field.attr('name', '<?php echo esc_js( self::OPTION_KEY ); ?>[' + repeaterName + '][' + rowIndex + '][' + fieldName + ']');
                        });
                    });
                }

                function addRepeaterItem(repeaterName, values) {
                    var $container = $('.acz-repeater[data-repeater="' + repeaterName + '"]');
                    var index = $container.find('.acz-repeater-item').length;
                    var fields = repeaterName === 'custom_lighting'
                        ? ['title', 'image', 'image_icon', 'description']
                        : ['title', 'content'];
                    var labels = {
                        title: 'Title',
                        image: 'Image',
                        image_icon: 'Image Icon',
                        description: 'Description',
                        content: 'Content'
                    };
                    var $item = $('<div class="acz-repeater-item"></div>');
                    fields.forEach(function (field) {
                        var name = '<?php echo esc_js( self::OPTION_KEY ); ?>[' + repeaterName + '][' + index + '][' + field + ']';
                        var val = values && values[field] ? values[field] : '';
                        var isTextarea = (field === 'description' || field === 'content');
                        var isMedia = (field === 'image' || field === 'image_icon');
                        $item.append(createField(name, val, labels[field], isTextarea, isMedia));
                    });
                    var $remove = $('<button type="button" class="button-link-delete">Remove</button>');
                    $remove.on('click', function () {
                        $item.remove();
                        reindexRepeater(repeaterName);
                    });
                    $item.append($remove);
                    $container.append($item);
                }

                $('.acz-add-repeater').on('click', function () {
                    addRepeaterItem($(this).data('repeater'));
                });

                $(document).on('click', '.acz-remove-repeater', function () {
                    var $container = $(this).closest('.acz-repeater');
                    var repeaterName = $container.data('repeater');
                    $(this).closest('.acz-repeater-item').remove();
                    reindexRepeater(repeaterName);
                });

                $(document).on('click', '.acz-media-select', function () {
                    var $input = $(this).siblings('input[type="text"]');
                    openMediaPicker($input);
                });

                $('form[action="options.php"]').on('submit', function () {
                    reindexRepeater('custom_lighting');
                    reindexRepeater('faq');
                });
            })(jQuery);
        </script>
        <?php
    }

    private function render_repeater_items( string $name, array $items, array $fields ) {
        foreach ( $items as $index => $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            echo '<div class="acz-repeater-item">';

            foreach ( $fields as $field ) {
                $value = isset( $item[ $field ] ) ? (string) $item[ $field ] : '';
                $label = ucwords( str_replace( '_', ' ', $field ) );

                echo '<div class="acz-field">';
                echo '<label>' . esc_html( $label ) . '</label>';

                $input_name = sprintf(
                    '%1$s[%2$s][%3$d][%4$s]',
                    self::OPTION_KEY,
                    $name,
                    (int) $index,
                    $field
                );

                if ( 'image' === $field || 'image_icon' === $field ) {
                    echo '<div class="acz-media-row">';
                    printf(
                        '<input type="text" name="%1$s" value="%2$s" />',
                        esc_attr( $input_name ),
                        esc_attr( $value )
                    );
                    echo '<button type="button" class="button button-secondary acz-media-select">' . esc_html__( 'Select Image', 'acz-elements' ) . '</button>';
                    echo '</div>';
                } elseif ( 'description' === $field || 'content' === $field ) {
                    printf(
                        '<textarea rows="4" name="%1$s">%2$s</textarea>',
                        esc_attr( $input_name ),
                        esc_textarea( $value )
                    );
                } else {
                    printf(
                        '<input type="text" name="%1$s" value="%2$s" />',
                        esc_attr( $input_name ),
                        esc_attr( $value )
                    );
                }
                echo '</div>';
            }

            echo '<button type="button" class="button-link-delete acz-remove-repeater">' . esc_html__( 'Remove', 'acz-elements' ) . '</button>';
            echo '</div>';
        }
    }
}
