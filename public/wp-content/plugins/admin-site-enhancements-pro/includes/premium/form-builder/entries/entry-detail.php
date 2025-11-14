<?php
defined( 'ABSPATH' ) || die();
$prev_entry = Form_Builder_Entry::get_prev_entry( $entry->id );
$prev_entry = isset( $prev_entry[0] ) ? $prev_entry[0] : '';
$prev_entry_id = isset( $prev_entry->id ) ? $prev_entry->id : '';
$prev_url = $prev_entry_id ? admin_url( 'admin.php?page=formbuilder-entries&formbuilder_action=view&id=' . $prev_entry_id ) : '#';

$next_entry = Form_Builder_Entry::get_next_entry( $entry->id );
$next_entry = isset( $next_entry[0] ) ? $next_entry[0] : '';
$next_entry_id = isset( $next_entry->id ) ? $next_entry->id : '';
$next_url = $next_entry_id ? admin_url( 'admin.php?page=formbuilder-entries&formbuilder_action=view&id=' . $next_entry_id ) : '#';
?>

<div class="fb-form-entry-details-wrap wrap">
    <h1></h1>
    <div id="fb-form-entry-details">
        <div class="fb-page-title">
            <h3>
                <span><?php esc_html_e( 'Entry', 'admin-site-enhancements' ); ?></span>
                <span class="fb-sub-label">
                    <?php printf(
                        /* translators: %s: entry ID */
                        esc_html__( '(ID %d )', 'admin-site-enhancements' ), 
                        absint( $entry->id ) 
                    ); ?>
                </span>
            </h3>
            <div class="fb-form-entry-navigation">
                <a class="button fb-form-entry-prev<?php echo $prev_url == '#' ? ' fb-disabled' : ''; ?>" href="<?php echo esc_url( $prev_url ); ?>">
                    <?php echo wp_kses( Form_Builder_Icons::get( 'previous' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?><?php echo esc_html__( 'Previous', 'admin-site-enhancements' ) ?>
                </a>
                <a class="button fb-form-entry-next<?php echo $next_url == '#' ? ' fb-disabled' : ''; ?>" href="<?php echo esc_url( $next_url ); ?>">
                    <?php echo esc_html__( 'Next', 'admin-site-enhancements' ) ?><?php echo wp_kses( Form_Builder_Icons::get( 'next' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?>
                </a>
            </div>
        </div>
        <table>
            <tbody>
                <?php
                $file_img_placeholder = FORMBUILDER_URL . 'assets/img/attachment.png';
                foreach ( $entry->metas as $id => $value ) {
                    $title = $value['name'];
                    $entry_value = Form_Builder_Helper::unserialize_or_decode( $value['value'] );
                    $entry_type = $value['type']; // e.g. radio, scale, etc.

                    if ( is_array( $entry_value ) ) {
                        $entry_value = array_filter( $entry_value );
                        if ( $entry_type == 'name' ) {
                            $entry_value = implode( ' ', $entry_value );
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
                                $entry_val .= '<div class="fb-form-entry-matrix-row-label">' . $val['row_label'] . '</div>';
                                $entry_val .= '<p>' . $val['column_label'] . '</p>';
                            }
                            $entry_value = $entry_val;
                        } elseif ( $entry_type == 'matrix_of_dropdowns' ) {
                            $entry_val = '';

                            if ( isset( $entry_value['dropdown_matrix_rows'] ) ) {
                                foreach ( $entry_value['dropdown_matrix_rows'] as $key => $values ) {
                                    $entry_val .= '<div class="fb-form-entry-matrix-row-label">' . $values['row_label'] . '</div>';
                                    foreach ( $values['choices'] as $choice_key => $choice ) {
                                        $entry_val .= '<p class="fb-form-entry-matrix-choice"><span class="fb-form-entry-matrix-column-label">' . $choice['column_label'] . '</span><br />'  . $choice['selected_option'] . '</p>';
                                    }
                                }
                            }

                            $entry_value = $entry_val;
                        } elseif ( false !== strpos( $entry_type, 'matrix_of_variable_dropdowns' ) ) {
                            $entry_val = '';

                            if ( isset( $entry_value['dropdowns_matrix_rows'] ) ) {
                                foreach ( $entry_value['dropdowns_matrix_rows'] as $key => $values ) {
                                    $entry_val .= '<div class="fb-form-entry-matrix-row-label">' . $values['row_label'] . '</div>';
                                    foreach ( $values['choices'] as $choice_key => $choice ) {
                                        $entry_val .= '<p class="fb-form-entry-matrix-choice"><span class="fb-form-entry-matrix-column-label">' . $choice['column_label'] . '</span><br />'  . $choice['selected_option'] . '</p>';
                                    }
                                }
                            }

                            $entry_value = $entry_val;
                        } else {
                            $entry_value = implode( ',<br>', $entry_value );
                        }
                    }

                    if ( $entry_type == 'upload' && $entry_value ) {
                        $files_arr = explode( ',', $entry_value );
                        $upload_value = '';
                        foreach ( $files_arr as $file ) {
                            $file_info = pathinfo( $file );
                            $file_name = $file_info['basename'];
                            $file_extension = $file_info['extension'];

                            $upload_value .= '<div class="fb-form-entry-preview">';
                            $upload_value .= '<div class="fb-form-entry-preview-image"><a href="' . esc_url( $file ) . '" target="_blank">';
                            if ( in_array( $file_extension, array( 'jpg', 'jpeg', 'png', 'gif', 'bmp' ) )) {
                                $upload_value .= '<img src="' . esc_url( $file ) . '">';
                            } else {
                                $upload_value .= '<img class="fb-attachment-icon" src="' . esc_url( $file_img_placeholder ) . '">';
                            }
                            $upload_value .= '</a></div>';
                            $upload_value .= '<label><a href="' . esc_url( $file ) . '" target="_blank">';
                            $upload_value .= esc_html( $file_name ) . '</a></label>';
                            $upload_value .= '</div>';
                        }
                        $entry_value = $upload_value;
                    }

                    if ( $entry_type == 'textarea' ) {
                            $entry_value = nl2br( $entry_value );
                    }
                     
                    echo '<tr>';
                    echo '<th>' . esc_html( $title ) . '</th>';
                    echo '<td>' . wp_kses_post( $entry_value ) . '</td>';
                    echo '</tr>';
                }

                do_action( 'hf_after_entry_detail_view', $entry );
                ?>
            </tbody>
        </table>
    </div>
</div>