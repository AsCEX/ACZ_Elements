<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'acf_field' ) ) {
    return;
}

/**
 * ACZ Custom Repeater Field
 * 
 * A custom field type for ACF that provides repeater functionality.
 * This is designed to mimic the core ACF Pro repeater.
 */
class ACZ_ACF_Field_Repeater extends acf_field {

    public function __construct() {
        $this->name     = 'acz_repeater';
        $this->label    = esc_html__( 'ACZ Repeater', 'acz-elements' );
        $this->category = 'layout';
        $this->defaults = [
            'sub_fields'   => [],
            'layout'       => 'table',
            'button_label' => esc_html__( 'Add Row', 'acz-elements' ),
            'min'          => 0,
            'max'          => 0,
        ];

        $this->add_field_filter( 'acf/prepare_field_for_import', [ $this, 'prepare_field_for_import' ] );

        parent::__construct();
    }

    public function load_field( $field ) {
        if ( empty( $field['sub_fields'] ) && function_exists( 'acf_get_fields' ) ) {
            $sub_fields = acf_get_fields( $field );

            if ( $sub_fields ) {
                $field['sub_fields'] = $sub_fields;
            }
        }

        return $field;
    }

    public function prepare_field_for_import( $field ) {
        if ( empty( $field['sub_fields'] ) || ! is_array( $field['sub_fields'] ) ) {
            return $field;
        }

        $sub_fields = $field['sub_fields'];

        foreach ( $sub_fields as $i => $sub_field ) {
            if ( ! is_array( $sub_field ) ) {
                unset( $sub_fields[ $i ] );
                continue;
            }

            $sub_fields[ $i ]['parent']     = $field['key'];
            $sub_fields[ $i ]['menu_order'] = $i;
        }

        return array_merge( [ $field ], array_values( $sub_fields ) );
    }

    /**
     * Render field settings
     *
     * @param array $field
     */
    public function render_field_settings( $field ) {
        // Sub Fields - This is complex as it requires a field list.
        // For now, we'll try to use the core sub_fields logic if possible, 
        // or provide a placeholder for them.
        
        // Note: Core ACF Repeater uses a specialized 'sub_fields' setting.
        // We'll add some basic settings for now.
        
        acf_render_field_setting( $field, [
            'label'        => esc_html__( 'Layout', 'acz-elements' ),
            'instructions' => esc_html__( 'Specify the style used to render the sub fields', 'acz-elements' ),
            'type'         => 'radio',
            'name'         => 'layout',
            'layout'       => 'horizontal',
            'choices'      => [
                'table' => esc_html__( 'Table', 'acz-elements' ),
                'block' => esc_html__( 'Block', 'acz-elements' ),
                'row'   => esc_html__( 'Row', 'acz-elements' ),
            ],
        ]);

        acf_render_field_setting( $field, [
            'label'        => esc_html__( 'Button Label', 'acz-elements' ),
            'instructions' => '',
            'type'         => 'text',
            'name'         => 'button_label',
        ]);
    }

    /**
     * Render the field
     *
     * @param array $field
     */
    public function render_field( $field ) {
        $value        = isset( $field['value'] ) && is_array( $field['value'] ) ? $field['value'] : [];
        $sub_fields   = isset( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ? $field['sub_fields'] : [];
        $layout       = isset( $field['layout'] ) ? $field['layout'] : $this->defaults['layout'];
        $button_label = isset( $field['button_label'] ) ? $field['button_label'] : $this->defaults['button_label'];
        $min          = isset( $field['min'] ) ? $field['min'] : $this->defaults['min'];
        $max          = isset( $field['max'] ) ? $field['max'] : $this->defaults['max'];

        if ( empty( $sub_fields ) ) {
            return;
        }

        // Enqueue assets for reordering (sortable)
        wp_enqueue_script( 'jquery-ui-sortable' );

        ?>
        <div class="acz-repeater" data-layout="<?php echo esc_attr( $layout ); ?>" data-min="<?php echo esc_attr( $min ); ?>" data-max="<?php echo esc_attr( $max ); ?>">
            <table class="acf-table">
                <?php if ( 'table' === $layout ) : ?>
                    <thead>
                        <tr>
                            <th class="acf-row-handle"></th>
                            <?php foreach ( $sub_fields as $sub_field ) : ?>
                                <?php if ( ! is_array( $sub_field ) ) : ?>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <?php $sub_field = acf_prepare_field( $sub_field ); ?>
                                <?php if ( ! $sub_field ) : ?>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <?php
                                $sub_field_name  = isset( $sub_field['_name'] ) ? $sub_field['_name'] : ( isset( $sub_field['name'] ) ? $sub_field['name'] : '' );
                                $sub_field_type  = isset( $sub_field['type'] ) ? $sub_field['type'] : '';
                                $sub_field_key   = isset( $sub_field['key'] ) ? $sub_field['key'] : '';
                                $sub_field_label = isset( $sub_field['label'] ) ? $sub_field['label'] : $sub_field_name;
                                ?>
                                <th class="acf-th" data-name="<?php echo esc_attr( $sub_field_name ); ?>" data-type="<?php echo esc_attr( $sub_field_type ); ?>" data-key="<?php echo esc_attr( $sub_field_key ); ?>">
                                    <label><?php echo esc_html( $sub_field_label ); ?></label>
                                    <?php if ( ! empty( $sub_field['instructions'] ) ) : ?>
                                        <p class="description"><?php echo esc_html( $sub_field['instructions'] ); ?></p>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="acf-row-handle"></th>
                        </tr>
                    </thead>
                <?php endif; ?>

                <tbody class="acz-repeater-rows">
                    <?php 
                    // Render existing rows
                    if ( ! empty( $value ) ) {
                        foreach ( $value as $i => $row ) {
                            $this->render_row( $field, $i, $row );
                        }
                    }
                    ?>
                </tbody>
            </table>

            <div class="acz-repeater-footer">
                <div class="acf-actions">
                    <a class="button button-primary acz-repeater-add-row" href="#"><?php echo esc_html( $button_label ); ?></a>
                </div>
            </div>

            <script type="text/template" class="acz-repeater-clone">
                <?php $this->render_row( $field, 'acfcloneindex', [] ); ?>
            </script>
        </div>
        <style>
            .acz-repeater .acf-table { margin-bottom: 10px; }
            .acz-repeater-rows .acf-row-handle.order { cursor: move; width: 16px; text-align: center; vertical-align: middle; background: #f9f9f9; }
            .acz-repeater-rows .acf-row-handle.remove { width: 16px; text-align: center; vertical-align: middle; }
            .acz-repeater-rows .acf-row-handle .acf-icon { display: inline-block; width: 20px; height: 20px; line-height: 20px; font-size: 14px; color: #a0a5aa; text-decoration: none; }
            .acz-repeater-rows .acf-row-handle .acf-icon:hover { color: #f00; }
        </style>
        <?php
        
        // Output JS for repeater if not already printed
        $this->render_scripts();
    }

    /**
     * Render a single row
     */
    private function render_row( $field, $i, $row_value ) {
        $sub_fields = isset( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ? $field['sub_fields'] : [];
        ?>
        <tr class="acf-row" data-id="<?php echo esc_attr( $i ); ?>">
            <td class="acf-row-handle order">
                <span><?php echo is_numeric( $i ) ? intval( $i ) + 1 : ''; ?></span>
            </td>
            <?php 
            foreach ( $sub_fields as $sub_field ) : 
                if ( ! is_array( $sub_field ) ) {
                    continue;
                }

                if ( empty( $sub_field['name'] ) ) {
                    continue;
                }

                $sub_field['_name'] = isset( $sub_field['_name'] ) ? $sub_field['_name'] : $sub_field['name'];
                $sub_field_key      = isset( $sub_field['key'] ) ? $sub_field['key'] : $sub_field['name'];

                // Set value for sub field
                if ( is_array( $row_value ) && array_key_exists( $sub_field_key, $row_value ) ) {
                    $sub_field['value'] = $row_value[ $sub_field_key ];
                } elseif ( is_array( $row_value ) && array_key_exists( $sub_field['_name'], $row_value ) ) {
                    $sub_field['value'] = $row_value[ $sub_field['_name'] ];
                } else {
                    $sub_field['value'] = isset( $sub_field['default_value'] ) ? $sub_field['default_value'] : '';
                }
                
                // Set prefix for ACF internal usage
                $sub_field['prefix'] = ( isset( $field['name'] ) ? $field['name'] : '' ) . '[' . $i . ']';
                acf_render_field_wrap( $sub_field, 'td' );
                ?>
            <?php endforeach; ?>
            <td class="acf-row-handle remove">
                <a class="acf-icon -minus acz-repeater-remove-row" href="#" title="<?php esc_attr_e( 'Remove row', 'acz-elements' ); ?>"></a>
            </td>
        </tr>
        <?php
    }

    private static $scripts_printed = false;

    private function render_scripts() {
        if ( self::$scripts_printed ) {
            return;
        }
        self::$scripts_printed = true;
        ?>
        <script>
        (function($){
            $(document).on('click', '.acz-repeater-add-row', function(e){
                e.preventDefault();
                var $repeater = $(this).closest('.acz-repeater');
                var $rows = $repeater.find('.acz-repeater-rows');
                var template = $repeater.find('.acz-repeater-clone').html();
                
                var count = $rows.find('> .acf-row').length;
                var html = template.replace(/acfcloneindex/g, count);
                
                var $newRow = $(html);
                $rows.append($newRow);
                
	                updateOrder($repeater);

	                // Trigger ACF to initialize fields in the new row.
	                if (typeof acf !== 'undefined') {
	                    if (typeof acf.doAction === 'function') {
	                        acf.doAction('append', $newRow);
	                    } else if (typeof acf.do_action === 'function') {
	                        acf.do_action('append', $newRow);
	                    }
	                }
	            });

            $(document).on('click', '.acz-repeater-remove-row', function(e){
                e.preventDefault();
                var $row = $(this).closest('.acf-row');
                var $repeater = $row.closest('.acz-repeater');
                
                $row.remove();
                updateOrder($repeater);
            });

            function updateOrder($repeater) {
                $repeater.find('.acz-repeater-rows > .acf-row').each(function(i){
                    $(this).find('.acf-row-handle.order span').text(i + 1);
                    
                    // Update field names
                    $(this).find('[name]').each(function(){
                        var name = $(this).attr('name');
                        if (name) {
                            $(this).attr('name', name.replace(/\[\d+\]|\[acfcloneindex\]/, '[' + i + ']'));
                        }
                    });
                    
                    // Update data-id attribute
                    $(this).attr('data-id', i);
                });
            }

            // Initialize sortable
            $('.acz-repeater-rows').sortable({
                handle: '.order',
                items: '> .acf-row',
                update: function(event, ui) {
                    updateOrder($(this).closest('.acz-repeater'));
                }
            });

        })(jQuery);
        </script>
        <?php
    }

    /**
     * Load value
     */
    public function load_value( $value, $post_id, $field ) {
        return $value;
    }

    /**
     * Format value
     */
    public function format_value( $value, $post_id, $field ) {
        if ( empty( $value ) || ! is_array( $value ) ) {
            return $value;
        }

        $sub_fields = isset( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ? $field['sub_fields'] : [];
        if ( empty( $sub_fields ) ) {
            return $value;
        }

        foreach ( $value as $row_index => $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            foreach ( $sub_fields as $sub_field ) {
                if ( ! is_array( $sub_field ) || empty( $sub_field['name'] ) || empty( $sub_field['key'] ) ) {
                    continue;
                }

                if ( array_key_exists( $sub_field['key'], $row ) && ! array_key_exists( $sub_field['name'], $row ) ) {
                    $value[ $row_index ][ $sub_field['name'] ] = $row[ $sub_field['key'] ];
                }
            }
        }

        return $value;
    }

    /**
     * Update value
     */
    public function update_value( $value, $post_id, $field ) {
        if ( empty( $value ) || ! is_array( $value ) ) {
            return [];
        }

        unset( $value['acfcloneindex'] );

        $sub_fields = isset( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ? $field['sub_fields'] : [];
        $rows       = [];

        foreach ( $value as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $normalized_row = [];

            foreach ( $sub_fields as $sub_field ) {
                if ( ! is_array( $sub_field ) || empty( $sub_field['name'] ) ) {
                    continue;
                }

                $sub_field_name = isset( $sub_field['_name'] ) ? $sub_field['_name'] : $sub_field['name'];
                $sub_field_key  = isset( $sub_field['key'] ) ? $sub_field['key'] : $sub_field_name;

                if ( array_key_exists( $sub_field_key, $row ) ) {
                    $normalized_row[ $sub_field_name ] = $row[ $sub_field_key ];
                } elseif ( array_key_exists( $sub_field_name, $row ) ) {
                    $normalized_row[ $sub_field_name ] = $row[ $sub_field_name ];
                }
            }

            if ( ! empty( $normalized_row ) ) {
                $rows[] = $normalized_row;
            }
        }

        return $rows;
    }
}
