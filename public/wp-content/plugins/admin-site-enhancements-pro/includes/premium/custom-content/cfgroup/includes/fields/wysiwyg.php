<?php

class cfgroup_wysiwyg extends cfgroup_field
{

    function __construct() {
        $this->name = 'wysiwyg';
        $this->label = __( 'WYSIWYG Editor', 'admin-site-enhancements' );

        // add the "code" button
        add_filter( 'mce_external_plugins', [ $this, 'mce_external_plugins' ], 20 );
    }


    function html( $field ) {
        $input_name_parts = explode( '][', $field->input_name );
        $field_id = isset( $input_name_parts[1] ) ? $input_name_parts[1] : '';
        $field_value = isset( $field->value ) ? $field->value : '';

        $common_methods = new ASENHA\Classes\Common_Methods;
        $full_url = $common_methods->get_full_url();
        ?>
        <div class="wp-editor-wrap">
            <?php 
            if ( is_user_logged_in() ) {
            ?>
            <div class="wp-media-buttons">
                <?php do_action( 'media_buttons' ); ?>
            </div>
            <?php
            }
            ?>
            <div class="wp-editor-container">
                <textarea id="wysiwyg-field-<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field->input_name ); ?>" class="wp-editor-area <?php echo esc_attr( $field->input_class ); ?>" style="height:300px"><?php echo $field->value; ?></textarea>
            </div>
            <?php
            if ( ! is_user_logged_in() // Frontend form
                || ( is_admin() && ( false !== strpos( $full_url, 'wp-admin/edit-tags.php' ) || false !== strpos( $full_url, 'wp-admin/term.php' ) ) ) // Taxonomy term screens
            ) {
                $editor_settings = array(
                    'media_buttons'     => false,
                    'textarea_rows'     => 5,
                    'tiny_mce'          => true,
                    // 'tinymce'           => array(
                        // 'toolbar1'      => 'bold,italic,underline,strikethrough,superscript,subscript,blockquote,bullist,numlist,alignleft,aligncenter,alignjustify,alignright,alignnone,link,unlink,fontsizeselect,forecolor,undo,redo,removeformat,code',
                        // 'toolbar1'      => 'bold,italic,underline,strikethrough,blockquote,bullist,numlist,alignleft,aligncenter,alignjustify,alignright,link,undo,redo,removeformat,fullscreen',
                        // 'content_css'   => ASENHA_URL . 'assets/css/settings-wpeditor.css',
                    // ),
                    'editor_css'        => '',
                    'wpautop'           => true,
                    'quicktags'         => false,
                    'default_editor'    => 'tinymce', // 'tinymce' or 'html'
                );
                
                // Frontend form
                if ( ! is_user_logged_in() ) {
                    $editor_settings['tinymce'] = array(
                        'toolbar1'      => 'bold,italic,underline,strikethrough,blockquote,bullist,numlist,alignleft,aligncenter,alignjustify,alignright,link,undo,redo,removeformat,code,fullscreen',                    
                    );
                }

                // Taxonomy term screens
                if ( ( is_admin() && ( false !== strpos( $full_url, 'wp-admin/edit-tags.php' ) || false !== strpos( $full_url, 'wp-admin/term.php' ) ) ) ) {
                    $editor_settings['tinymce'] = array(
                        'toolbar1'      => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignjustify,alignright,link,undo,redo,removeformat,fullscreen',                    
                    );
                }
                
                wp_editor( $field_value, 'wysiwyg-field-' . $field_id, $editor_settings );                
            }
            ?>
        </div>
        <?php
    }


    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Formatting', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][formatting]",
                        'options' => [
                            'choices' => [
                                'default' => __( 'Default', 'admin-site-enhancements' ),
                                'none' => __( 'None (bypass filters)', 'admin-site-enhancements' )
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'formatting', 'default' ),
                    ] );
                ?>
            </td>
        </tr>
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


    function input_head( $field = null ) {

        // make sure the user has WYSIWYG enabled
        if ( 'true' == get_user_meta( get_current_user_id(), 'rich_editing', true ) ) {
            if ( ! is_admin() ) {
    ?>
        <div class="hidden"><?php wp_editor( '', 'cfgroupwysi' ); ?></div>
    <?php
            }
    ?>
        <script>
        (function($) {

            var wpautop;
            var resize;
            var wysiwyg_count = 0;

            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_wysiwyg:not(.ready)').init_wysiwyg();
                });
                $('.cfgroup_wysiwyg').init_wysiwyg();

                // set the active editor clicking the Add Media button
                $(document).on('click', 'button.add_media', function() {
                    var editor_id = $(this).closest('.wp-editor-wrap').find('.wp-editor-area').attr('id');
                    wpActiveEditor = editor_id;
                });
            });

            $.fn.init_wysiwyg = function() {
                this.each(function() {
                    $(this).addClass('ready');

                    // Generate CSS id
                    wysiwyg_count = wysiwyg_count + 1;
                    var input_id = 'cfgroup_wysiwyg_' + wysiwyg_count;

                    // Set the WYSIWYG css id
                    $(this).find('.wysiwyg').attr('id', input_id);
                    $(this).find('button.add_media').attr('data-editor', input_id);
                    
                    // if all editors on page are in 'text' tab, tinyMCE.settings will not be set
                    if ('undefined' == typeof tinyMCE.settings || Object.keys(tinyMCE.settings).length === 0) {

                        // let's pull from tinyMCEPreInit for main content area (if it's set)
                        if ('undefined' !== typeof tinyMCEPreInit && 'undefined' !== typeof tinyMCEPreInit.mceInit.content) {
                            tinyMCE.settings = tinyMCEPreInit.mceInit.content;
                        }
                        // otherwise, setup basic settings object
                        else {
                            tinyMCE.settings = {
                                wpautop : true,
                                resize : 'vertical'
                            };  
                        }
                    }

                    // Settings values:
                    // toolbar1: "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv"
                    // toolbar2: "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help,code"
                    // plugins: "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpgallery,wplink,wpdialogs,wptextpattern,wpview"

                    tinyMCE.settings.height = 174;
                    tinyMCE.settings.add_unload_trigger = false;
                    tinyMCE.settings.branding = false;
                    tinyMCE.settings.browser_spellcheck = true;
                    tinyMCE.settings.convert_urls = false;
                    tinyMCE.settings.end_container_on_empty_block = true;
                    tinyMCE.settings.entities = '38,amp,60,lt,62,gt';
                    tinyMCE.settings.entity_encoding = 'raw';
                    tinyMCE.settings.content_css = document.location.origin + '/wp-includes/css/dashicons.css,' + document.location.origin + '/wp-includes/js/tinymce/skins/wordpress/wp-content.css';
                    tinyMCE.settings.fix_list_elements = true;
                    tinyMCE.settings.indent = false;
                    tinyMCE.settings.keep_styles = false;
                    tinyMCE.settings.menubar = false;
                    tinyMCE.settings.plugins = 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpgallery,wplink,wpdialogs,wptextpattern,wpview';
                    tinyMCE.settings.external_plugins = {'code':document.location.origin + '/wp-content/plugins/admin-site-enhancements-pro/includes/premium/custom-content/cfgroup/assets/js/tinymce/code.min.js'};
                    tinyMCE.settings.preview_styles = 'font-family font-size font-weight font-style text-decoration text-transform';
                    tinyMCE.settings.relative_urls = false;
                    tinyMCE.settings.remove_script_host = false;
                    tinyMCE.settings.resize = 'vertical';
                    tinyMCE.settings.skin = 'lightgray';
                    tinyMCE.settings.theme = 'modern';
                    tinyMCE.settings.wp_autoresize_on = true;
                    tinyMCE.settings.wp_keep_scroll_position = true;
                    tinymce.settings.wpautop = true;         

                    tinyMCE.settings.toolbar1 = 'formatselect,bold,italic,bullist,numlist,alignleft,aligncenter,alignright,link,wp_adv';
                    tinyMCE.settings.toolbar2 = 'blockquote,strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,code,wp_help,fullscreen';
                    // console.log(tinyMCE.settings);
                    
                    // Create WYSIWYG editor
                    tinyMCE.execCommand('mceAddEditor', false, input_id);
                });
            };

            $('.meta-box-sortables, .cfgroup_repeater').on('sortstart', function(event, ui) {
                $(this).find('.wysiwyg').each(function() {
                    tinyMCE.execCommand('mceRemoveEditor', false, $(this).attr('id'));
                });
            });

            $('.meta-box-sortables, .cfgroup_repeater').on('sortstop', function(event, ui) {
                $(this).find('.wysiwyg').each(function() {
                    tinyMCE.execCommand('mceAddEditor', false, $(this).attr('id'));
                });
            });
        })(jQuery);
        </script>
    <?php
        }
    }


    function mce_external_plugins( $plugins ) {
        if ( version_compare( get_bloginfo( 'version' ), '3.9', '>=' ) ) {
            $plugins['code'] = CFG_URL . '/assets/js/tinymce/code.min.js';
        }
        return $plugins;
    }


    function format_value_for_input( $value, $field = null ) {
        if ( version_compare( get_bloginfo( 'version' ), '4.3', '>=' ) ) {
            return format_for_editor( $value );
        }
        else {
            return wp_richedit_pre( $value );
        }
    }


    function format_value_for_api( $value, $field = null ) {
        if ( ! is_null( $field ) && property_exists( $field, 'option_pages' ) ) {
            if ( ! empty( $field->option_pages ) ) {
                // This WYSIWYG field is part of an option page, let's bypass filters and return content as is
                $formatting = 'none';
            } else {
                $formatting = $this->get_option( $field, 'formatting', 'default' );      
            }
        } else {
            $formatting = $this->get_option( $field, 'formatting', 'default' );
        }

        return ( 'none' == $formatting ) ? $value : apply_filters( 'the_content', $value );
    }


    function format_value_for_display( $value, $field = null ) {
        return $this->format_value_for_api( $value, $field );
    }
}
