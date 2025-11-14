<?php

class cfgroup_gallery extends cfgroup_field
{

    function __construct() {
        $this->name = 'gallery';
        $this->label = __( 'Gallery', 'admin-site-enhancements' );
    }

    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label validation-label">
                <label><?php _e( 'Validation', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'true_false',
                        'input_name' => "cfgroup[fields][$key][options][required]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option( $field, 'required' ),
                        'options' => [ 'message' => __( 'This is a required field', 'admin-site-enhancements' ) ],
                    ] );
                ?>
            </td>
        </tr>
    <?php
    }

    function html( $field ) {
        $gallery_image_ids = $field->value; // Comma-separated attachemtn IDs, e.g. 7682,7681,7680
        
        // Css class to sho/hide buttons/links
        $show = ( empty( $gallery_image_ids ) ) ? '' : ' hidden';
        $hidden = ( empty( $gallery_image_ids ) ) ? ' hidden' : '';

        ?>
        <div class="gallery-preview">
        <?php 
        if ( ! empty( $gallery_image_ids ) ) {
            $values = explode( ',', $gallery_image_ids );

            foreach ( $values as $id ) {
                $attachment = wp_get_attachment_image_src( $id, 'thumbnail' );
                ?>
                <div class="preview-image"><img src="<?php echo esc_url( ( is_array( $attachment ) ) ? $attachment[0] : '' ); ?>" /></div>
                <?php
            }
        }

        ?>
        </div>
        <div class="gallery-buttons">
        <?php
        if ( ! is_user_logged_in() ) {
            // We show a disabled button when the field is shown to logged-out user
            ?>
            <input type="button" class="button cfg-button<?php echo esc_attr( $show ); ?> disabled" value="<?php _e( 'Select Images', 'admin-site-enhancements' ); ?>" disabled="disabled" />
            <?php
            } else {
            ?>
            <a href="#" class="button cfg-button<?php echo esc_attr( $show ); ?>">Select Images</a>
            <a href="#" class="button cfg-edit-gallery<?php echo esc_attr( $hidden ); ?>">Edit Selection</a>
            <a href="#" class="cfg-clear-gallery<?php echo esc_attr( $hidden ); ?>">Clear</a>        
            <?php
        }
        ?>
        </div>
        <input type="hidden" name="<?php echo esc_attr( $field->input_name ); ?>" value="<?php echo esc_attr( $gallery_image_ids ); ?>" />
        <?php
    }

    function input_head( $field = null ) {
        wp_enqueue_media();
        ?>
        <style>
            #menu-item-insert,
            #menu-item-playlist,
            #menu-item-video-playlist,
            #menu-item-embed,
            .collection-settings.gallery-settings h2,
            .collection-settings.gallery-settings .setting {
                display: none;
            }
            .attachments-browser .media-toolbar-primary>.media-button {
                position: relative;
                top: -10px;
            }
        </style>
        <script>
        (function($) {
            'use strict';    
            
            $(document).ready( function() {
                init_gallery();
            });

            // Clicking "Add Row" button in a repeater field. 
            // Reference: repeater.php:285
            $(document).on( 'cfgroup/ready', function() {
                init_gallery();
            });
            
            function init_gallery() {

                $('.cfgroup_gallery').each(function() {

                    var $this   = $(this),
                        $add    = $this.find('.cfg-button'),
                        $edit   = $this.find('.cfg-edit-gallery'),
                        $clear  = $this.find('.cfg-clear-gallery'),
                        $list   = $this.find('.gallery-preview'),
                        $input  = $this.find('input'),
                        $img    = $this.find('.preview-image'),
                        wp_media_frame;

                    $this.on('click', '.cfg-button, .cfg-edit-gallery', function( e ) {
                        var $el   = $(this),
                            ids   = $input.val(),
                            what  = ( $el.hasClass('cfg-edit-gallery') ) ? 'edit' : 'add',
                            state = ( what === 'add' && !ids.length ) ? 'gallery' : 'gallery-edit';

                        e.preventDefault();
                        
                        if ( typeof window.wp === 'undefined' || ! window.wp.media || ! window.wp.media.gallery ) { 
                            return;
                        }

                        // Open media with state
                        if ( state === 'gallery' ) {

                            wp_media_frame = window.wp.media({
                                library: {
                                    type: 'image'
                                },
                                frame: 'post',
                                state: 'gallery',
                                multiple: true
                            });

                            wp_media_frame.open();

                        } else {

                            wp_media_frame = window.wp.media.gallery.edit( '[gallery ids="'+ ids +'"]' );

                            if ( what === 'add' ) {
                                wp_media_frame.setState('gallery-library');
                            }
                        }

                        // Media Update
                        wp_media_frame.on( 'update', function( selection ) {

                            $list.empty();

                            var selectedIds = selection.models.map( function( attachment ) {

                                var item  = attachment.toJSON();
                                var thumb = ( item.sizes && item.sizes.thumbnail && item.sizes.thumbnail.url ) ? item.sizes.thumbnail.url : item.url;

                                $list.append('<div class="preview-image"><img src="'+ thumb +'"></div>');

                                return item.id;

                            });

                            $input.val( selectedIds.join( ',' ) ).trigger('change');
                            $add.addClass('hidden');
                            $clear.removeClass('hidden');
                            $edit.removeClass('hidden');

                        });
                        
                    });

                    $clear.on('click', function( e ) {
                        e.preventDefault();
                        $list.empty();
                        $input.val('').trigger('change');
                        $add.removeClass('hidden');
                        $clear.addClass('hidden');
                        $edit.addClass('hidden');
                    });

                });
                
            }
            
        })(jQuery);
        </script>
        <?php
    }

    function format_value_for_api( $value, $field = null ) {
        if ( ! empty( $value ) ) {
            // Turn comma-separated IDs into indexed array of integer IDs
            $value = explode( ',', $value );
            $new_value = array();
            foreach( $value as $id ) {
                $new_value[] = (int) $id;
            }
            $value = $new_value;
        } else {
            $value = array();
        }
        
        return $value;
    }

    function format_value_for_display( $value, $field = null ) {
        return get_cf_gallery_justified( explode( ',', $value ), 'medium' );
    }
}