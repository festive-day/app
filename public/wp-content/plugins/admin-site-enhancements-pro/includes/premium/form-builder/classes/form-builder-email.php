<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Email {

    public $form;
    public $entry_id;
    public $location;

    public function __construct( $form, $entry_id, $location ) {
        $this->form = $form;
        $this->entry_id = $entry_id;
        $this->location = $location;
    }

    private function get_form_settings() {
        return $this->form->settings;
    }
    
    public function set_html_mail_content_type() {
        return 'text/html';
    }
    
    /**
     * Detect strings that begins with #field_id_ in a sentence
     * 
     * @return $matches[0]  array of matches
     * @since 7.8.1
     */
    public function get_field_tags( string $content ): array {
        $pattern = '/#field_id_\w+/';
        preg_match_all( $pattern, $content, $matches );

        $matches = $matches[0];
        
        // Let's sort so that tags with larger number comes first,
        // e.g. array( '#field_id_1', '#field_id_2', #field_id_183', '#field_id_184' )
        // will become array( '#field_id_2', '#field_id_183', #field_id_184', '#field_id_1' )
        // This will make sure larger field tags are replaced first during replace_field_tags_with_values()
        // e.g. #field_id_183 will be replaced first with the correspoding value
        // i.e. #field_id_183 will not be replaced with the value of #field_id_1 + '83'
        arsort( $matches );

        return $matches;
    }
    
    /**
     * Get the value of a field tag, e.g. #field_id_1 where 1 is the field ID
     * 
     * @since 7.8.1
     */
    public function get_field_tag_value( $field_tag, $metas, $context = 'short_text' ) {
        $field_id = str_replace( '#field_id_', '', $field_tag );
        if ( isset( $metas[$field_id] ) ) {
            $field_type = $metas[$field_id]['type'];            
            $field_tag_value_raw = $metas[$field_id]['value'];
            $field_tag_value_processed = Form_Builder_Helper::unserialize_or_decode( $field_tag_value_raw );
        } else {
            $field_type = 'text';
            $field_tag_value_raw = '';
            $field_tag_value_processed = '';
        }

        $field_tag_value = '';
        
        switch ( $field_type ) {
            case 'url';
            case 'text';
            case 'textarea';
            case 'number';
            case 'range_slider';
            case 'spinner';
            case 'star';
            case 'scale';
            case 'select';
            case 'date';
            case 'time';
                $field_tag_value = $field_tag_value_processed;
                break;

            case 'upload';
                $output = '';
                if ( ! empty( $field_tag_value_processed ) ) {
                    $file_urls = explode( ',', $field_tag_value_processed ); // array of full URLs of uploaded file in /wp-content/uploads/form-builder/ folder
                    foreach ( $file_urls as $file_url ) {
                        $file_url_parts = explode( '/', $file_url );
                        $filename = end( $file_url_parts );
                        
                        switch ( $context ) {
                            case 'short_text';
                                $output .= $filename . ', ';
                                break;
                                
                            case 'content';
                                $output .= '<a href="' . $file_url . '">' . $filename . '</a>, ';                            
                                break;
                        }
                    }
                }
                $field_tag_value = trim( $output, ', ' );
                break;
                        
            case 'name';
                if ( is_array( $field_tag_value_processed ) && ! empty( $field_tag_value_processed ) ) {
                    $return_val = '';
                    foreach ( $field_tag_value_processed as $key => $val ) {
                        $return_val .= $val . ' ';
                    }
                    $field_tag_value = trim( $return_val ); // e.g. John Robert Doe
                } else {
                    $field_tag_value = $field_tag_value_processed;
                }
                break;
            
            case 'address';
            case 'radio';
            case 'checkbox';
            case 'image_select';
                if ( is_array( $field_tag_value_processed ) && ! empty( $field_tag_value_processed ) ) {
                    $return_val = '';
                    foreach ( $field_tag_value_processed as $key => $val ) {
                        if ( ! empty( $val ) ) {
                            $return_val .= $val . ', ';
                        }
                    }
                    $field_tag_value = trim( trim( $return_val, ', ' ) ); // e.g. full address, comma-separated
                } else {
                    $field_tag_value = $field_tag_value_processed;
                }
                break;

            case 'likert_matrix_scale';
                if ( is_array( $field_tag_value_processed ) && ! empty( $field_tag_value_processed ) ) {
                    $return_val = '';

                    switch ( $context ) {
                        case 'short_text';
                            $return_val .= '---';
                            break;
                            
                        case 'content';
                            $field_tag_value_processed = $field_tag_value_processed['likert_rows'];
                            foreach ( $field_tag_value_processed as $key => $val ) {
                                $return_val .= '<strong>' . $val['row_label'] . '</strong><br />';
                                $return_val .= $val['column_label'] . '<br /><br />';                        
                            }
                            break;
                    }

                    $field_tag_value = $return_val;
                } else {
                    $field_tag_value = $field_tag_value_processed;
                }
                break;

            case 'matrix_of_dropdowns';
            case 'matrix_of_variable_dropdowns_two';
            case 'matrix_of_variable_dropdowns_three';
            case 'matrix_of_variable_dropdowns_four';
            case 'matrix_of_variable_dropdowns_five';
                if ( is_array( $field_tag_value_processed ) && ! empty( $field_tag_value_processed ) ) {
                    $return_val = '';

                    switch ( $context ) {
                        case 'short_text';
                            $return_val .= '---';
                            break;
                            
                        case 'content';
                            if ( isset( $field_tag_value_processed['dropdown_matrix_rows'] ) ) {
                                $field_tag_value_processed = $field_tag_value_processed['dropdown_matrix_rows'];
                            } elseif ( isset( $field_tag_value_processed['dropdowns_matrix_rows'] ) ) {
                                $field_tag_value_processed = $field_tag_value_processed['dropdowns_matrix_rows'];
                            } else {
                                $field_tag_value_processed = array();
                            }

                            if ( ! empty( $field_tag_value_processed ) ) {
                                foreach ( $field_tag_value_processed as $key => $val ) {
                                    $return_val .= '<strong>' . $val['row_label'] . '</strong><br />';
                                    foreach ( $val['choices'] as $key => $val ) {
                                        $return_val .= '<em>' . $val['column_label'] . '</em><br />';
                                        $return_val .= $val['selected_option'] . '<br /><br />';
                                    }
                                }                                
                            }
                            break;
                    }

                    $field_tag_value = $return_val;
                } else {
                    $field_tag_value = $field_tag_value_processed;
                }
                break;
                                
            default:
                $field_tag_value = $field_tag_value_raw;
                break;
        }
        
        return $field_tag_value;
    }
    
    /**
     * Replace field tags with field values
     * 
     * @since 7.8.1
     */
    public function replace_field_tags_with_values( $content, $field_tags, $metas, $context ) {
        if ( ! empty( $field_tags ) ) {
            foreach ( $field_tags as $field_tag ) {
                $content = str_replace( $field_tag, $this->get_field_tag_value( $field_tag, $metas, $context ), $content );
            }            
        }
        
        return $content;
    }

    public function send_email() {
        // $location = $this->location; // Permalink of the form
        $attachments = array();
        $form_settings = $this->get_form_settings();
        // vi( $form_settings );
        $entry = Form_Builder_Entry::get_entry_vars( $this->entry_id );
        $metas = $entry->metas;
        // vi( $metas );

        $email_to = isset( $form_settings['email_to'] ) ? explode( ',', $form_settings['email_to'] ) : '';
        $email_from = isset( $form_settings['email_from'] ) ? $form_settings['email_from'] : '';
        if ( '[admin_email]' == trim( $email_from ) ) {
            $email_from = get_option( 'admin_email' );
        }
        $email_from_name = isset( $form_settings['email_from_name'] ) ? $form_settings['email_from_name'] : '';
        $email_subject = isset( $form_settings['email_subject'] ) ? $form_settings['email_subject'] : '';

        // Reply-to email for email notification
        $reply_to_email = isset( $form_settings['reply_to_email'] ) ? $form_settings['reply_to_email'] : '';
        $field_tags = $this->get_field_tags( $reply_to_email );
        $reply_to_email = $this->replace_field_tags_with_values( $reply_to_email, $field_tags, $metas, 'short_text' );

        // Auto responder destination
        $send_to_ar = isset( $form_settings['send_to_ar'] ) ? '#field_id_' . $form_settings['send_to_ar'] : ''; // Field tag, e.g. #field_id_10
        $field_tags = $this->get_field_tags( $send_to_ar );
        $send_to_ar = $this->replace_field_tags_with_values( $send_to_ar, $field_tags, $metas, 'short_text' );

        $settings = Form_Builder_Settings::get_settings();
        $email_template = $settings['email_template'] ? sanitize_text_field( $settings['email_template'] ) : 'boxed-plain';
        $email_template_method = str_replace( '-', '_', $email_template ) . '_row_template'; // e.g. boxed_plain_row_template
        $header_image = sanitize_text_field( $settings['header_image'] );
        $email_msg = isset( $form_settings['email_message'] ) ? sanitize_textarea_field( $form_settings['email_message'] ) : '';
        $footer_text = isset( $form_settings['footer_text'] ) ? wp_kses_post( $form_settings['footer_text'] ) : __( 'This email was sent from #linked_site_name.', 'admin-site-enhancements' );
        $linked_site_name = '<a href="' . get_site_url() . '">' . get_bloginfo( 'name' ) . '</a>';
        $email_table = $this->get_entry_rows( $email_template_method ); // Entries table is mainly generated here
        $form_title = $this->form->name;
        $file_img_placeholder = FORMBUILDER_URL . 'assets/img/attachment.png';

        $email_from_name = str_replace( '#site_name', get_bloginfo( 'name' ), $email_from_name );

        // Email subject
        $field_tags = $this->get_field_tags( $email_subject );
        $email_subject = str_replace( '#form_title', $form_title, $email_subject );
        $email_subject = $this->replace_field_tags_with_values( $email_subject, $field_tags, $metas, 'short_text' );

        // Email message
        $field_tags = $this->get_field_tags( $email_msg );
        $email_msg = str_replace( '#form_details', $email_table, $email_msg );
        $email_msg = $this->replace_field_tags_with_values( $email_msg, $field_tags, $metas, 'content' );
        $email_message = empty( $email_msg ) ? '' : wpautop( $email_msg );

        // Footer text
        $footer_text = str_replace( '#linked_site_name', $linked_site_name, $footer_text );

        ob_start();
        include( FORMBUILDER_PATH . 'settings/email-templates/' . $email_template . '.php' );
        $email_message = ob_get_clean();

        $head = array();
        $head[] = 'Content-Type: text/html; charset=UTF-8';
        $head[] = 'From: ' . esc_html( $email_from_name ) . ' <' . esc_html( $email_from ) . '>';
        if ( $reply_to_email ) {
            $head[] = 'Reply-To: ' . esc_html( $reply_to_email );
        }

        $recipients = array();

        foreach ( $email_to as $row ) {
            $recipients[] = ( trim( $row ) == '[admin_email]' ) ? get_option( 'admin_email' ) : $row;
        }

        add_filter( 'wp_mail_content_type', array( $this, 'set_html_mail_content_type' ) );

        if ( ! empty( $attachments ) ) {
            $mail = wp_mail( $recipients, $email_subject, $email_message, $head, $attachments );
        } else {
            $mail = wp_mail( $recipients, $email_subject, $email_message, $head );
        }
        
        remove_filter( 'wp_mail_content_type', array( $this, 'set_html_mail_content_type' ) );

        if ( $mail ) {
            if ( isset( $form_settings['enable_ar'] ) && $form_settings['enable_ar'] == 'on' ) {
                $attachments = isset( $attachments ) ? $attachments : array();
                $from_ar = isset( $form_settings['from_ar'] ) ? trim( $form_settings['from_ar'] ) : '';
                $from_ar_name = isset( $form_settings['from_ar_name'] ) && ( $form_settings['from_ar_name'] != '' ) ? esc_html( $form_settings['from_ar_name'] ) : esc_html__( 'No Name', 'admin-site-enhancements' );

                $email_subject = isset( $form_settings['email_subject_ar'] ) && ( $form_settings['email_subject_ar'] != '' ) ? $form_settings['email_subject_ar'] : esc_html__( 'New Form Submission', 'admin-site-enhancements' );
                $email_message = isset( $form_settings['email_message_ar'] ) ? sanitize_textarea_field( $form_settings['email_message_ar'] ) : '';
                $footer_text_ar = isset( $form_settings['footer_text_ar'] ) ? wp_kses_post( $form_settings['footer_text_ar'] ) : __( 'This email was sent from #linked_site_name.', 'admin-site-enhancements' );

                $settings = Form_Builder_Settings::get_settings();
                $header_image = $settings['header_image'];

                $from_ar_name = str_replace( '#site_name', get_bloginfo( 'name' ), $from_ar_name );

                // Email subject
                $field_tags = $this->get_field_tags( $email_subject );
                $email_subject = str_replace( '#form_title', $form_title, $email_subject );
                $email_subject = $this->replace_field_tags_with_values( $email_subject, $field_tags, $metas, 'short_text' );

                // Email message
                $field_tags = $this->get_field_tags( $email_message );
                $email_message = str_replace( '#form_details', $email_table, $email_message );
                $email_message = $this->replace_field_tags_with_values( $email_message, $field_tags, $metas, 'content' );
                $email_message = empty( $email_message ) ? '' : wpautop( $email_message );

                // Footer text
                $footer_text_ar = str_replace( '#linked_site_name', $linked_site_name, $footer_text_ar );
                $footer_text = $footer_text_ar;

                ob_start();
                include( FORMBUILDER_PATH . 'settings/email-templates/' . $email_template . '.php' );
                $form_html = ob_get_clean();

                $from_ar = ( $from_ar == '[admin_email]' ) ? get_option( 'admin_email' ) : esc_attr( $from_ar );

                $head = array();
                $head[] = 'Content-Type: text/html; charset=UTF-8';
                $head[] = 'From: ' . esc_html( $from_ar_name ) . ' <' . esc_html( $from_ar ) . '>';
                wp_mail( $send_to_ar, $email_subject, $form_html, $head, $attachments );
            }
            $redirect_url = '';

            if ( $form_settings['confirmation_type'] == 'show_page' ) {
                $redirect_url = get_permalink( $form_settings['show_page_id'] );
            } else if ( $form_settings['confirmation_type'] == 'redirect_url' ) {
                $redirect_url = $form_settings['redirect_url_page'];
            }

            do_action( 'formbuilder_after_email', array(
                'form' => $this->form,
                'entry_id' => $this->entry_id,
                'form_settings' => $form_settings,
                'metas' => $metas,
                'location' => $this->location,
            ) );
            
            $form_actions = new Form_Builder_Actions( $this->form, $this->entry_id, $form_settings, $metas, $this->location );
            $form_actions->run();

            if ( ! empty( $redirect_url ) ) {
                return wp_send_json( array(
                    'status' => 'redirect',
                    'message' => esc_url( $redirect_url )
                ) );
            }

            $hide_form_after_submission = isset( $form_settings['hide_form_after_submission'] ) ? $form_settings['hide_form_after_submission'] : 'off';

            return wp_send_json( array(
                'status' => 'success',
                'message' => esc_html( $form_settings['confirmation_message'] ),
                'hide_form_after_submission' => esc_html( $hide_form_after_submission )
            ) );
        } else {
            return false;
        }
    }

    public function get_entry_rows( $email_template_method ) {
        $settings = Form_Builder_Settings::get_settings();
        $entry = Form_Builder_Entry::get_entry_vars( $this->entry_id );
        $entry_rows = '';
        $file_img_placeholder = FORMBUILDER_URL . 'assets/img/attachment.png';
        $count = 0;
        foreach ( $entry->metas as $id => $value ) {
            $count++;
            $title = $value['name'];
            $entry_value = Form_Builder_Helper::unserialize_or_decode( $value['value'] );
            $entry_type = $value['type'];
            if ( is_array( $entry_value ) ) {
                if ( $entry_type == 'name' ) {
                    $entry_value = implode( ' ', array_filter( $entry_value ) );
                } elseif ( $entry_type == 'repeater_field' ) {
                    $entry_val = '<table><thead><tr>';
                    foreach ( array_keys( $entry_value ) as $key ) {
                        $entry_val .= '<th>' . $key . '</th>';
                    }
                    $entry_val .= '</tr></thead><tbody>';
                    $out = array();
                    foreach ( $entry_value as $rowkey => $row ) {
                        foreach ( $row as $colkey => $col ) {
                            $out[$colkey][$rowkey] = $col;
                        }
                    }
                    foreach ( $out as $key => $val ) {
                        foreach ( $val as $eval ) {
                            $entry_val .= '<td>' . $eval . '</td>';
                        }
                        $entry_val .= '</tr>';
                    }
                    $entry_val .= '</tbody></table>';
                    $entry_value = $entry_val;
                } elseif ( $entry_type == 'likert_matrix_scale' ) {
                    $entry_value = $entry_value['likert_rows'];
                    $entry_val = '';
                    foreach ( $entry_value as $key => $val ) {
                        $entry_val .= '<div style="border-bottom:1px solid #ddd;padding-bottom:8px;margin-bottom:8px;font-weight:bold">' . $val['row_label'] . '</div>';
                        $entry_val .= '<p>' . $val['column_label'] . '</p>';
                        $entry_val .= '<div style="display:block;margin-bottom:24px;"></div>';
                    }
                    $entry_value = $entry_val;
                } elseif ( $entry_type == 'matrix_of_dropdowns' ) {
                    $entry_val = '';
                    
                    if ( isset( $entry_value['dropdown_matrix_rows'] ) ) {
                        foreach ( $entry_value['dropdown_matrix_rows'] as $key => $values ) {
                            $entry_val .= '<div style="border-bottom:1px solid #ddd;padding-bottom:8px;margin-bottom:8px;font-weight:bold">' . $values['row_label'] . '</div>';
                            foreach ( $values['choices'] as $choice_key => $choice ) {
                                $entry_val .= '<p style="margin-bottom:8px;"><span>' . $choice['column_label'] . '</span><br />'  . $choice['selected_option'] . '</p>';
                            }
                            $entry_val .= '<div style="display:block;margin-bottom:24px;"></div>';
                        }
                    }

                    $entry_value = $entry_val;
                } elseif ( false !== strpos( $entry_type, 'matrix_of_variable_dropdowns' ) ) {
                    $entry_val = '';

                    if ( isset( $entry_value['dropdowns_matrix_rows'] ) ) {
                        foreach ( $entry_value['dropdowns_matrix_rows'] as $key => $values ) {
                            $entry_val .= '<div style="border-bottom:1px solid #ddd;padding-bottom:8px;margin-bottom:8px;font-weight:bold">' . $values['row_label'] . '</div>';
                            foreach ( $values['choices'] as $choice_key => $choice ) {
                                $entry_val .= '<p style="margin-bottom:8px;"><span>' . $choice['column_label'] . '</span><br />'  . $choice['selected_option'] . '</p>';
                            }
                            $entry_val .= '<div style="display:block;margin-bottom:24px;"></div>';
                        }
                    }

                    $entry_value = $entry_val;
                } else {
                    $entry_value = implode( ',<br>', array_filter( $entry_value ) );
                }
            }

            if ( $entry_type == 'upload' && $entry_value ) {
                $files_arr = explode( ',', $entry_value );
                $upload_value = '';
                foreach ( $files_arr as $file ) {
                    $file_info = pathinfo( $file );
                    $file_name = $file_info['basename'];
                    $file_extension = $file_info['extension'];

                    $upload_value .= '<div style="margin-bottom: 10px;padding-bottom: 10px;border-bottom: 1px solid #EEE;">';
                    $upload_value .= '<div><a href="' . esc_url( $file ) . '" target="_blank">';
                    if ( in_array( $file_extension, array( 'jpg', 'jpeg', 'png', 'gif', 'bmp' ) )) {
                        $upload_value .= '<img style="width:150px" src="' . esc_url( $file ) . '">';
                    } else {
                        $upload_value .= '<img style="width: 40px;border: 1px solid #666;border-radius: 6px;padding: 4px;" src="' . esc_url( $file_img_placeholder ) . '">';
                    }
                    $upload_value .= '</a></div>';
                    $upload_value .= '<label><a href="' . esc_url( $file ) . '" target="_blank">';
                    $upload_value .= esc_html( $file_name ) . '</a></label>';
                    $upload_value .= '</div>';
                }
                $entry_value = $upload_value;
            }
            $entry_rows .= call_user_func( 'Form_Builder_Email::' . $email_template_method, $title, $entry_value, $count );
        }
        return $entry_rows;
    }

    // For "Plain" email template
    public static function plain_row_template( $title, $entry_value, $count ) {
        ob_start();
        ?>
        <div class="field-row" style="width: 100%; margin-bottom: 25px">
            <div class="field-label" style="font-family: sans-serif;font-weight: 600;vertical-align: top;text-align:left; line-height: 18px;"><?php echo esc_html( $title ); ?></div>
            <div class="field-value" style="font-family: sans-serif;vertical-align: top; padding: 4px 0 0 0; line-height: 18px;"><?php echo esc_html( $entry_value ); ?></div>
        </div>
        <?php
        $form_html = ob_get_clean();
        return $form_html;
    }

    // For "Boxed Plain" email template
    public static function boxed_plain_row_template( $title, $entry_value, $count ) {
        ob_start();
        ?>
        <table class="field-row" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; margin-bottom: 25px">
            <tbody>
                <tr class="field-row-tr">
                    <th class="field-label" style="font-family: sans-serif; font-size: 14px; vertical-align: top;text-align:left; line-height: 18px;" valign="top"><?php echo esc_html( $title ); ?></th>
                </tr>
                <tr class="field-row-tr">
                    <td class="field-value" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding: 10px 0 0 0; line-height: 18px;" valign="top"><?php echo esc_html( $entry_value ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
        $form_html = ob_get_clean();
        return $form_html;
    }

    // For "Lightly Striped" email template
    public static function lightly_striped_row_template( $title, $entry_value, $count ) {
        ob_start();
        $border_style = '';

        if ( $count == 1 ) {
            $border_class_name = 'field-row-with-top-border';
        } else {
            $border_class_name = 'field-row-no-top-border';
        }

        if ( $count % 2 == 0 ) {
            $color_class_name = 'field-row-highlight-color';
        } else {
            $color_class_name = 'field-row-regular-color';
        }
        ?>
        <table class="field-row <?php echo esc_attr( $border_class_name ); ?> <?php echo esc_attr( $color_class_name ); ?>" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;padding: 25px;">
            <tbody>
                <tr class="field-row-tr">
                    <th class="field-label" style="font-family: sans-serif; font-size: 14px; vertical-align: top;text-align:left; line-height: 18px;" valign="top"><?php echo esc_html( $title ); ?></th>
                </tr>
                <tr class="field-row-tr">
                    <td class="field-value" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding: 10px 0 0 0; line-height: 18px;" valign="top"><?php echo esc_html( $entry_value ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
        $form_html = ob_get_clean();
        return $form_html;
    }

    // For "solid-bar-labels" email template
    public static function solid_bar_labels_row_template( $title, $entry_value, $count ) {
        ob_start();
        ?>
        <table class="field-row" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background:#FFF; margin-bottom: 25px">
            <tbody>
                <tr class="field-row-tr">
                    <th class="field-label" style="font-family: sans-serif; font-size: 14px; vertical-align: top;text-align:left; line-height: 18px;text-transform: uppercase;padding: 10px 20px;" valign="top"><?php echo esc_html( $title ); ?></th>
                </tr>
                <tr class="field-row-tr">
                    <td class="field-value" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding: 10px 0 0 0; line-height: 18px;padding: 20px !important;" valign="top"><?php echo esc_html( $entry_value ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
        $form_html = ob_get_clean();
        return $form_html;
    }

}
