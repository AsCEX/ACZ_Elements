<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'acf_field' ) ) {
    return;
}

final class ACZ_ACF_Field_Elementor_Icon extends acf_field {
    private static $script_printed = false;

    public function __construct() {
        $this->name     = 'acz_elementor_icon';
        $this->label    = esc_html__( 'Elementor Icon Picker', 'acz-elements' );
        $this->category = 'choice';
        $this->defaults = [
            'allow_null' => 1,
        ];

        parent::__construct();
    }

    public function render_field( $field ) {
        $value = is_string( $field['value'] ?? '' ) ? trim( (string) $field['value'] ) : '';
        $icons = $this->get_available_icons();

        $this->enqueue_icon_font_styles();

        $wrapper_id = 'acz-acf-icon-picker-' . wp_unique_id();
        ?>
        <style>
            .acz-acf-icon-picker { border: 1px solid #dcdcde; border-radius: 4px; padding: 10px; background: #fff; max-width: 760px; }
            .acz-acf-icon-picker__toolbar { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
            .acz-acf-icon-picker__preview { min-width: 40px; text-align: center; font-size: 18px; }
            .acz-acf-icon-picker__value { color: #50575e; font-family: ui-monospace, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
            .acz-acf-icon-picker__panel { border-top: 1px solid #dcdcde; padding-top: 10px; margin-top: 8px; display: none; }
            .acz-acf-icon-picker__panel.is-open { display: block; }
            .acz-acf-icon-picker__search { width: 100%; margin-bottom: 8px; }
            .acz-acf-icon-picker__grid { max-height: 260px; overflow: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(44px, 1fr)); gap: 6px; }
            .acz-acf-icon-picker__item { border: 1px solid #dcdcde; border-radius: 4px; background: #f6f7f7; height: 40px; cursor: pointer; }
            .acz-acf-icon-picker__item i { font-size: 16px; line-height: 1; }
            .acz-acf-icon-picker__item.is-active { border-color: #2271b1; color: #2271b1; background: #fff; }
        </style>
        <div id="<?php echo esc_attr( $wrapper_id ); ?>" class="acz-acf-icon-picker">
            <input type="hidden" class="acz-acf-icon-picker__input" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
            <div class="acz-acf-icon-picker__toolbar">
                <span class="acz-acf-icon-picker__preview">
                    <?php if ( '' !== $value ) : ?>
                        <i class="<?php echo esc_attr( $value ); ?>" aria-hidden="true"></i>
                    <?php else : ?>
                        <span>-</span>
                    <?php endif; ?>
                </span>
                <span class="acz-acf-icon-picker__value"><?php echo '' !== $value ? esc_html( $value ) : esc_html__( 'No icon selected', 'acz-elements' ); ?></span>
                <button type="button" class="button button-secondary acz-acf-icon-picker__toggle"><?php echo esc_html__( 'Choose Icon', 'acz-elements' ); ?></button>
                <button type="button" class="button-link-delete acz-acf-icon-picker__clear"><?php echo esc_html__( 'Clear', 'acz-elements' ); ?></button>
            </div>
            <div class="acz-acf-icon-picker__panel">
                <input type="search" class="acz-acf-icon-picker__search" placeholder="<?php echo esc_attr__( 'Search icon class (example: house, user, phone)', 'acz-elements' ); ?>" />
                <div class="acz-acf-icon-picker__grid">
                    <?php foreach ( $icons as $icon ) : ?>
                        <button type="button" class="acz-acf-icon-picker__item <?php echo $icon === $value ? 'is-active' : ''; ?>" data-icon="<?php echo esc_attr( $icon ); ?>" title="<?php echo esc_attr( $icon ); ?>">
                            <i class="<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php if ( ! self::$script_printed ) : ?>
            <script>
                (function ($) {
                    function getRoot($el) {
                        return $el.closest('.acz-acf-icon-picker');
                    }

                    function setValue($root, iconClass) {
                        iconClass = String(iconClass || '').trim();

                        var $input = $root.find('.acz-acf-icon-picker__input');
                        var $items = $root.find('.acz-acf-icon-picker__item');
                        var $preview = $root.find('.acz-acf-icon-picker__preview');
                        var $value = $root.find('.acz-acf-icon-picker__value');

                        $input.val(iconClass).trigger('change');

                        if (iconClass) {
                            $preview.html($('<i/>').addClass(iconClass));
                            $value.text(iconClass);
                        } else {
                            $preview.text('-');
                            $value.text('No icon selected');
                        }

                        $items.removeClass('is-active');
                        if (iconClass) {
                            $items.filter('[data-icon="' + iconClass.replace(/"/g, '\\"') + '"]').addClass('is-active');
                        }
                    }

                    $(document).on('click', '.acz-acf-icon-picker__toggle', function (e) {
                        e.preventDefault();
                        var $root = getRoot($(this));
                        var $panel = $root.find('.acz-acf-icon-picker__panel');
                        $panel.toggleClass('is-open');
                        if ($panel.hasClass('is-open')) {
                            $root.find('.acz-acf-icon-picker__search').trigger('focus');
                        }
                    });

                    $(document).on('click', '.acz-acf-icon-picker__clear', function (e) {
                        e.preventDefault();
                        setValue(getRoot($(this)), '');
                    });

                    $(document).on('click', '.acz-acf-icon-picker__item', function (e) {
                        e.preventDefault();
                        setValue(getRoot($(this)), $(this).data('icon'));
                    });

                    $(document).on('input', '.acz-acf-icon-picker__search', function () {
                        var query = String($(this).val() || '').toLowerCase();
                        var $root = getRoot($(this));
                        $root.find('.acz-acf-icon-picker__item').each(function () {
                            var icon = String($(this).data('icon') || '').toLowerCase();
                            $(this).toggle(icon.indexOf(query) !== -1);
                        });
                    });
                })(jQuery);
            </script>
            <?php self::$script_printed = true; ?>
        <?php endif; ?>
        <?php
    }

    public function update_value( $value, $post_id, $field ) {
        if ( ! is_string( $value ) ) {
            return '';
        }

        $value = wp_strip_all_tags( wp_unslash( $value ) );
        $value = preg_replace( '/[^a-zA-Z0-9\-\_\s]/', '', $value );
        $value = preg_replace( '/\s+/', ' ', (string) $value );

        return trim( (string) $value );
    }

    private function enqueue_icon_font_styles() {
        $font_awesome_loaded = false;
        $fa_handles          = [
            'elementor-icons-fa-solid',
            'elementor-icons-fa-regular',
            'elementor-icons-fa-brands',
            'font-awesome-5-all',
        ];

        foreach ( $fa_handles as $handle ) {
            if ( wp_style_is( $handle, 'registered' ) ) {
                wp_enqueue_style( $handle );
                $font_awesome_loaded = true;
            } elseif ( wp_style_is( $handle, 'enqueued' ) ) {
                $font_awesome_loaded = true;
            }
        }

        if ( wp_style_is( 'elementor-icons', 'registered' ) ) {
            wp_enqueue_style( 'elementor-icons' );
        } elseif ( defined( 'ELEMENTOR_ASSETS_URL' ) ) {
            wp_enqueue_style(
                'acz-elementor-eicons-fallback',
                trailingslashit( ELEMENTOR_ASSETS_URL ) . 'lib/eicons/css/elementor-icons.min.css',
                [],
                defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : ACZ_ELEMENTS_VERSION
            );
        }

        if ( ! $font_awesome_loaded ) {
            wp_enqueue_style(
                'acz-font-awesome-fallback',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
                [],
                '6.5.2'
            );
        }
    }

    private function get_available_icons(): array {
        $icons = $this->get_fontawesome_icons();

        // Keep a small set of Elementor icons for users who still use eicons.
        $icons = array_merge(
            $icons,
            [
                'eicon-star',
                'eicon-heart',
                'eicon-check-circle',
                'eicon-close-circle',
                'eicon-user-circle-o',
                'eicon-envelope',
                'eicon-phone',
                'eicon-map-pin',
                'eicon-clock',
                'eicon-calendar',
                'eicon-cart',
                'eicon-gallery-grid',
                'eicon-play',
                'eicon-angle-right',
                'eicon-angle-left',
                'eicon-plus-circle',
                'eicon-minus-circle',
                'eicon-share-arrow',
                'eicon-search',
                'eicon-settings',
            ]
        );

        $icons = apply_filters( 'acz/acf_icon_picker/icons', $icons );
        $icons = is_array( $icons ) ? array_values( array_unique( array_filter( array_map( 'strval', $icons ) ) ) ) : [];

        return $icons;
    }

    private function get_fontawesome_icons(): array {
        $cache_key = 'acz_fa_icon_picker_icons_v3';
        $cached    = get_transient( $cache_key );

        if ( is_array( $cached ) && ! empty( $cached ) ) {
//            return $cached;
        }

        $icons         = [];
        $metadata_json = $this->read_fontawesome_metadata();

        if ( '' !== $metadata_json ) {
            $metadata = json_decode( $metadata_json, true );
            if ( is_array( $metadata ) ) {
                foreach ( $metadata as $icon_name => $icon_data ) {
                    if ( ! is_array( $icon_data ) ) {
                        continue;
                    }

                    $styles = isset( $icon_data['styles'] ) && is_array( $icon_data['styles'] ) ? $icon_data['styles'] : [];
                    foreach ( $styles as $style ) {
                        if ( ! is_string( $style ) ) {
                            continue;
                        }
                        $style = strtolower( $style );
                        if ( 'solid' === $style ) {
                            $icons[] = 'fa-solid fa-' . sanitize_html_class( (string) $icon_name );
                        } elseif ( 'regular' === $style ) {
                            $icons[] = 'fa-regular fa-' . sanitize_html_class( (string) $icon_name );
                        } elseif ( 'brands' === $style ) {
                            $icons[] = 'fa-brands fa-' . sanitize_html_class( (string) $icon_name );
                        }
                    }
                }
            }
        }

        if ( empty( $icons ) ) {
            $icons = $this->get_icons_from_style_json_sets();
        }

        if ( empty( $icons ) ) {
            $css = $this->read_fontawesome_css();
            if ( '' !== $css && preg_match_all( '/\\.fa-([a-z0-9\\-]+):before/i', $css, $matches ) ) {
                $names = array_unique( array_map( 'strtolower', $matches[1] ) );
                foreach ( $names as $name ) {
                    $icons[] = 'fa-solid fa-' . $name;
                }
            }
        }

        // If parsing fails, keep a tiny baseline so picker is still usable.
        if ( empty( $icons ) ) {
            $icons = [
                'fa-solid fa-house',
                'fa-solid fa-user',
                'fa-solid fa-phone',
                'fa-solid fa-envelope',
            ];
        }

        $icons = array_values( array_unique( $icons ) );
        set_transient( $cache_key, $icons, DAY_IN_SECONDS );

        return $icons;
    }

    private function get_icons_from_style_json_sets(): array {
        $icons  = [];
        $styles = [
            'solid'   => 'fa-solid',
            'regular' => 'fa-regular',
//            'brands'  => 'fa-brands',
        ];

        foreach ( $styles as $style_file => $style_prefix ) {
            $json = $this->read_fontawesome_style_json( $style_file );
            if ( '' === $json ) {
                continue;
            }

            $data = json_decode( $json, true );
            if ( ! is_array( $data ) ) {
                continue;
            }

            if ( isset( $data['icons'] ) && is_array( $data['icons'] ) ) {
                $data = $data['icons'];
            }

            /*
             * Elementor FA JSON variants are usually keyed by icon name,
             * but we also support list-like structures for compatibility.
             */
            foreach ( $data as $key => $item ) {
                if ( is_string( $key ) ) {
                    $name = sanitize_html_class( strtolower( $key ) );
                    if ( '' !== $name ) {
                        $icons[] = $style_prefix . ' fa-' . $name;
                    }
                    continue;
                }

                if ( is_array( $item ) ) {
                    $name = '';
                    if ( isset( $item['id'] ) && is_string( $item['id'] ) ) {
                        $name = $item['id'];
                    } elseif ( isset( $item['name'] ) && is_string( $item['name'] ) ) {
                        $name = $item['name'];
                    }

                    $name = sanitize_html_class( strtolower( $name ) );
                    if ( '' !== $name ) {
                        $icons[] = $style_prefix . ' fa-' . $name;
                    }
                }
            }
        }

        return array_values( array_unique( $icons ) );
    }

    private function read_fontawesome_metadata(): string {
        $paths = [];

        if ( defined( 'ELEMENTOR_PATH' ) ) {
            $paths[] = trailingslashit( ELEMENTOR_PATH ) . 'assets/lib/font-awesome/json/regular.json';
        }

        $paths[] = ABSPATH . 'wp-content/plugins/elementor/assets/lib/font-awesome/json/regular.json';

        foreach ( $paths as $path ) {
            if ( is_string( $path ) && '' !== $path && file_exists( $path ) && is_readable( $path ) ) {
                $content = (string) file_get_contents( $path );
                if ( '' !== $content ) {
                    return $content;
                }
            }
        }

        return '';
    }

    private function read_fontawesome_style_json( string $style ): string {
        $paths = [];

        if ( defined( 'ELEMENTOR_PATH' ) ) {
            $paths[] = trailingslashit( ELEMENTOR_PATH ) . 'assets/lib/font-awesome/json/' . $style . '.json';
        }

        $paths[] = ABSPATH . 'wp-content/plugins/elementor/assets/lib/font-awesome/json/' . $style . '.json';

        foreach ( $paths as $path ) {
            if ( is_string( $path ) && '' !== $path && file_exists( $path ) && is_readable( $path ) ) {
                $content = (string) file_get_contents( $path );
                if ( '' !== $content ) {
                    return $content;
                }
            }
        }

        return '';
    }

    private function read_fontawesome_css(): string {
        $paths = [];

        if ( defined( 'ELEMENTOR_PATH' ) ) {
            $paths[] = trailingslashit( ELEMENTOR_PATH ) . 'assets/lib/font-awesome/css/all.min.css';
            $paths[] = trailingslashit( ELEMENTOR_PATH ) . 'assets/lib/font-awesome/css/fontawesome.min.css';
        }

        $paths[] = ABSPATH . 'wp-content/plugins/elementor/assets/lib/font-awesome/css/all.min.css';
        $paths[] = ABSPATH . 'wp-content/plugins/elementor/assets/lib/font-awesome/css/fontawesome.min.css';

        foreach ( $paths as $path ) {
            if ( is_string( $path ) && '' !== $path && file_exists( $path ) && is_readable( $path ) ) {
                $content = (string) file_get_contents( $path );
                if ( '' !== $content ) {
                    return $content;
                }
            }
        }

        return '';
    }
}
