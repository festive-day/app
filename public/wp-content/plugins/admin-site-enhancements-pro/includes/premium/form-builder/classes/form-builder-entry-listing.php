<?php
defined( 'ABSPATH' ) || die();

/**
 * Adding WP List table class if it's not available.
 */
if ( ! class_exists(WP_List_Table::class ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Form_Builder_Entry_Listing extends \WP_List_Table {

    private $table_data;
    private $status;

    public function __construct() {
        parent::__construct(
            array(
                'singular' => 'Entry',
                'plural' => 'Entries',
                'ajax' => false,
            )
        );
        $this->status = Form_Builder_Helper::get_var( 'status', 'sanitize_text_field', 'published' );
    }

    public function no_items() {
        esc_html_e( 'No entries found.', 'admin-site-enhancements' );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'cd':
            case 'id':
            case 'name':
            case 'preview':
            case 'form_id':
            case 'user_id':
            case 'delivery_status':
            // case 'ip':
            case 'created_at':
            default:
                return $item[$column_name];
        }
    }

    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'name' => esc_html__( 'Entry Number', 'admin-site-enhancements' ),
            'form_id' => esc_html__( 'Form Title', 'admin-site-enhancements' ),
            'preview' => esc_html__( 'Preview', 'admin-site-enhancements' ),
            'user_id' => esc_html__( 'Created By', 'admin-site-enhancements' ),
            'delivery_status' => esc_html__( 'Status', 'admin-site-enhancements' ),
            // 'ip' => esc_html__( 'IP', 'admin-site-enhancements' ),
            'created_at' => esc_html__( 'Created At', 'admin-site-enhancements' )
        );
    }

    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s_id[]" value="%2$s" />', esc_attr( $this->_args['singular'] ), esc_attr( $item['id'] )
        );
    }

    public function prepare_items() {
        $this->table_data = $this->get_table_data();

        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $hidden = ( is_array(get_user_meta(get_current_user_id(), 'managetoplevel_page_formbuilder-entriescolumnshidden', true ) )) ? get_user_meta(get_current_user_id(), 'managetoplevel_page_formbuilder-entriescolumnshidden', true ) : array();
        $primary = 'id';
        $this->_column_headers = array( $columns, $hidden, $sortable, $primary );

        if ( $this->table_data ) {
            foreach ( $this->table_data as $item ) {
                $id = $item['id'];
                $data[$id] = array(
                    'id' => intval( $item['id'] ),
                    'name' => $this->get_column_id( $item ),
                    'form_id' => $this->get_form_link( $item['form_id'] ),
                    'preview' => $this->get_entry_preview( $item ),
                    'user_id' => $this->get_user_link( $item['user_id'] ),
                    'delivery_status' => $item['delivery_status'] ? esc_html__( 'Success', 'admin-site-enhancements' ) : esc_html__( 'Failed', 'admin-site-enhancements' ),
                    'created_at' => Form_Builder_Helper::convert_date_format( $item['created_at'] ),
                    'ip' => $item['ip']
                );
            }

            usort( $data, array(&$this, 'usort_reorder' ) );

            /* pagination */
            $per_page = $this->get_items_per_page( 'entries_per_page', 10 );
            $current_page = $this->get_pagenum();
            $total_items = count( $data );

            $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

            $this->set_pagination_args( array(
                'total_items' => $total_items,
                'per_page' => $per_page,
                'total_pages' => ceil( $total_items / $per_page )
            ) );

            $this->items = $data;
        }
    }

    public function get_column_id( $item ) {
        $entry_id = $item['id'];

        $edit_url = admin_url( 'admin.php?page=formbuilder-entries&formbuilder_action=view&id=' . $entry_id );

        $output = '<strong>';
        if ( 'trash' == $this->status ) {
            $output .= esc_html( $entry_id );
        } else {
            $output .= '<a class="row-title" href="' . esc_url( $edit_url ) . '" aria-label="' . sprintf(/* translators: %1$s: entry ID */esc_html__( '%s (Edit )', 'admin-site-enhancements' ), $entry_id ) . '">' . esc_html( __( 'Entry', 'admin-site-enhancements' ) . ' #' . $entry_id ) . '</a>';
        }
        $output .= '</strong>';

        // Get actions.
        $actions = $this->get_action_links( $item );
        $row_actions = array();

        foreach ( $actions as $id => $action ) {
            $row_actions[] = '<span class="' . esc_attr( $id ) . '"><a href="' . $action['url'] . '">' . $action['label'] . '</a></span>';
        }


        $output .= '<div class="row-actions">' . implode( ' | ', $row_actions ) . '</div>';

        return $output;
    }
    
    public function get_entry_preview( $item ) {
        $form = Form_Builder_Builder::get_form_vars( $item['form_id'] );
        $form_settings = $form->settings;
        $entry_preview_field_id = isset( $form_settings['entry_preview_field_id'] ) ? $form_settings['entry_preview_field_id'] : 0;
        $entry = Form_Builder_Entry::get_entry_vars( $item['id'] );
        $entry_metas = $entry->metas;

        $entry_preview_field_name = isset( $entry_metas[$entry_preview_field_id] ) ? $entry_metas[$entry_preview_field_id]['name'] : '';
        $entry_preview_field_value_raw = isset( $entry_metas[$entry_preview_field_id] ) ? $entry_metas[$entry_preview_field_id]['value'] : '';
        $entry_preview_field_type = isset( $entry_metas[$entry_preview_field_id] ) ? $entry_metas[$entry_preview_field_id]['type'] : 'text';

        $entry_preview_field_value_processed = Form_Builder_Helper::unserialize_or_decode( $entry_preview_field_value_raw );

        // if ( '110' == $item['id'] ) {
            // vi( $entry_type );
            // vi( $entry_metas );
            // vi( $entry_preview_field_value_processed );
        // }
        
        $entry_value = '';

        switch ( $entry_preview_field_type ) {
            case 'name':
                // Full name
                if ( isset( $entry_preview_field_value_processed['full'] ) ) {
                    $entry_value = $entry_preview_field_value_processed['full'];
                }
                // First, middle and last names
                if ( isset( $entry_preview_field_value_processed['middle'] ) ) {
                    $entry_value = $entry_preview_field_value_processed['first'] . ' ' . $entry_preview_field_value_processed['middle'] . ' ' . $entry_preview_field_value_processed['last'];
                }
                // First and last names
                if ( isset( $entry_preview_field_value_processed['first'] ) && ! isset( $entry_preview_field_value_processed['middle'] ) && isset( $entry_preview_field_value_processed['last'] ) ) {
                    $entry_value = $entry_preview_field_value_processed['first'] . ' ' . $entry_preview_field_value_processed['last'];
                }
                // First name only
                if ( isset( $entry_preview_field_value_processed['first'] ) && ! isset( $entry_preview_field_value_processed['middle'] ) && ! isset( $entry_preview_field_value_processed['last'] ) ) {
                    $entry_value = $entry_preview_field_value_processed['first'];
                }
                break;
                
            case 'address':
                $entry_preview_field_value_processed = array_values( $entry_preview_field_value_processed );
                $entry_value = trim( implode( ', ', $entry_preview_field_value_processed ), ', ' );
                break;
                
            case 'email':
            case 'url':
            case 'phone':
            case 'text':
            case 'number':
            case 'range_slider':
            case 'spinner':
            case 'star':
            case 'scale':
            case 'date':
            case 'time':
            case 'hidden':
            case 'select':
                $entry_value = $entry_preview_field_value_processed;
                break;
                
            case 'radio':
            case 'checkbox':
            case 'image_select':
                $entry_preview_field_value_processed = array_values( $entry_preview_field_value_processed );
                $entry_value = trim( implode( ', ', $entry_preview_field_value_processed ), ', ' );
                break;

            case 'textarea':
                $entry_value = wp_trim_words( $entry_preview_field_value_processed, 10 );
                break;
                
            case 'upload':
                $full_url = $entry_preview_field_value_processed;
                $wp_upload_dir = wp_upload_dir();
                $filename = str_replace( $wp_upload_dir['baseurl'] . '/form-builder/', '', $entry_preview_field_value_processed );
                $entry_value = '<a href="' . $full_url . '">' . $filename . '</a>';
                break;
        }

        // if ( '110' == $item['id'] ) {
            // vi( $entry_value );
        // }

        return '<span title="' . $entry_preview_field_name . '">' . $entry_value . '</span>';
    }

    public function usort_reorder( $a, $b ) {
        // If no sort, default to user_login
        $orderby = Form_Builder_Helper::get_var( 'orderby', 'sanitize_text_field', 'id' );

        if ( 'name' == $orderby ) {
            $orderby = 'id';
        }

        // If no order, default to asc
        $order = Form_Builder_Helper::get_var( 'order', 'sanitize_text_field', 'desc' );

        if ( 'id' == $orderby ) {
            // Determine sort order. Ref: https://stackoverflow.com/a/2852918
            if ( 'desc' == $order ) {
                $result = $b[$orderby] - $a[$orderby];
            } else {
                $result = $a[$orderby] - $b[$orderby];                
            }

            // Send final sort direction to usort
            return $result;
        } else {
            // Determine sort order. 
            $result = strcmp( $a[$orderby], $b[$orderby] );

            // Send final sort direction to usort
            return ( $order === 'asc' ) ? $result : -$result;
        }
    }

    private function get_table_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'asenha_formbuilder_entries';
        $status = $this->status;

        if ( $search = htmlspecialchars_decode( Form_Builder_Helper::get_var( 's' ) )) {
            $query = $wpdb->prepare("SELECT * from {$table} WHERE status=%s AND form_id Like %s", $status, '%' . $wpdb->esc_like( $search ) . '%' );
            return $wpdb->get_results( $query, ARRAY_A);
        } else if ( $form_id = Form_Builder_Helper::get_var( 'form_id', 'absint' ) ) {
            $query = $wpdb->prepare("SELECT * from {$table} WHERE status=%s AND form_id=%d", $status, $form_id );
            return $wpdb->get_results( $query, ARRAY_A);
        } else {
            $query = $wpdb->prepare("SELECT * from {$table} WHERE status=%s", $status );
            return $wpdb->get_results( $query, ARRAY_A);
        }
    }

    public function get_bulk_actions() {
        if ( $this->status == 'published' ) {
            return array(
                'bulk_trash' => esc_html__( 'Move to Trash', 'admin-site-enhancements' ),
            );
        } else {
            return array(
                'bulk_untrash' => esc_html__( 'Restore', 'admin-site-enhancements' ),
                'bulk_delete' => esc_html__( 'Delete Permanently', 'admin-site-enhancements' )
            );
        }
    }

    protected function display_tablenav( $which ) {
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            <?php if ( $this->has_items() ) { ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions( $which ); ?>
                </div>
                <?php
            }

            $this->extra_tablenav( $which );

            $this->pagination( $which );
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    public function extra_tablenav( $which ) {
        if ( $this->has_items() ) {
            if ( 'trash' == $this->status ) {
                ?>
                <div class="alignleft actions"><?php submit_button(esc_html__( 'Empty Trash', 'admin-site-enhancements' ), 'apply', 'delete_all', false ); ?></div>
                <?php
            }
        }

        if ( $which === 'top' ) {
            $form_id = Form_Builder_Helper::get_var( 'form_id', 'absint', 0 );
            ?>
            <div class="alignleft actions">
                <?php
                self::forms_dropdown( 'form_id', $form_id );
                submit_button(esc_html__( 'Filter', 'admin-site-enhancements' ), 'filter_action', '', false, array( 'id' => 'post-query-submit' ) );
                ?>
            </div>
            <?php
        }
    }

    public static function forms_dropdown( $field_name, $field_value = '' ) {
        $forms = Form_Builder_Builder::get_all_forms();
        ?>
        <select name="<?php echo esc_attr( $field_name ); ?>">
            <option value=""><?php echo esc_html__( 'All', 'admin-site-enhancements' ); ?></option>
            <?php foreach ( $forms as $form ) { ?>
                <option value="<?php echo esc_attr( $form->id ); ?>" <?php selected( $field_value, $form->id ); ?>>
                    <?php echo ( '' === $form->name ? esc_html__( '( no title )', 'admin-site-enhancements' ) : esc_html( $form->name ) ); ?>
                </option>
            <?php } ?>
        </select>
        <?php
    }

    public function get_sortable_columns() {
        return array(
            'name' => array( 'name', true ),
            'form_id' => array( 'form_id', true ),
            'preview' => array( 'preview', true ),
            'user_id' => array( 'user_id', true ),
            'delivery_status' => array( 'delivery_status', true ),
            // 'ip' => array( 'ip', true ),
            'created_at' => array( 'created_at', true ),
        );
    }

    public function get_action_links( $item ) {
        $entry_id = $item['id'];
        $actions = array();
        $trash_links = self::delete_trash_links( $entry_id );
        if ( 'trash' == $this->status ) {
            $actions['restore'] = $trash_links['restore'];
            $actions['delete'] = $trash_links['delete'];
        } else {
            $actions['view'] = array(
                'label' => esc_html__( 'View', 'admin-site-enhancements' ),
                'url' => admin_url( 'admin.php?page=formbuilder-entries&formbuilder_action=view&id=' . $entry_id )
            );
            $actions['trash'] = $trash_links['trash'];
        }
        return $actions;
    }

    private static function delete_trash_links( $id ) {
        $base_url = '?page=formbuilder-entries&id=' . $id;
        return array(
            'restore' => array(
                'label' => esc_html__( 'Restore', 'admin-site-enhancements' ),
                'url' => wp_nonce_url( $base_url . '&formbuilder_action=untrash', 'untrash_entry_' . absint( $id ) ),
            ),
            'delete' => array(
                'label' => esc_html__( 'Delete Permanently', 'admin-site-enhancements' ),
                'url' => wp_nonce_url( $base_url . '&formbuilder_action=destroy', 'destroy_entry_' . absint( $id ) ),
            ),
            'trash' => array(
                'label' => esc_html__( 'Trash', 'admin-site-enhancements' ),
                'url' => wp_nonce_url( $base_url . '&formbuilder_action=trash', 'trash_entry_' . absint( $id ) ),
            )
        );
    }

    public function get_views() {
        $statuses = array(
            'published' => esc_html__( 'All', 'admin-site-enhancements' ),
            'trash' => esc_html__( 'Trash', 'admin-site-enhancements' ),
        );

        $links = array();

        $counts = Form_Builder_Entry::get_count();

        foreach ( $statuses as $status => $name ) {
            $class = ( $status == $this->status ) ? ' class="current"' : '';
            if ( $counts[$status] ) {
                $links[$status] = '<a href="' . esc_url( '?page=formbuilder-entries&status=' . $status ) . '" ' . $class . '>' . sprintf( __( /* translators: %1$s: status name, %2$s: number */'%1$s <span class="count">(%2$s )</span>', 'admin-site-enhancements' ), $name, number_format_i18n( $counts[$status] ) ) . '</a>';
            }
        }
        return $links;
    }

    public function views() {
        $views = $this->get_views();
        if ( empty( $views ) )
            return;
        echo "<ul class='subsubsub'>\n";
        foreach ( $views as $class => $view ) {
            $views[$class] = "\t" . '<li class="' . esc_attr( $class ) . '">' . wp_kses_post( $view );
        }
        echo wp_kses_post(implode(" |</li>\n", $views ) . "</li>\n");
        echo '</ul>';
    }

    private function get_form_link( $form_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'asenha_formbuilder_forms';
        $query = $wpdb->prepare("SELECT name from {$table} WHERE id=%d", $form_id );
        $form_name = $wpdb->get_row( $query, ARRAY_A);
        return '<a data-form-name="' . esc_attr( $form_name['name'] ) . '" href="' . esc_url(admin_url( 'admin.php?page=formbuilder&formbuilder_action=edit&id=' . $form_id ) ) . '">' . esc_html( $form_name['name'] ) . '</a>';
    }

    private function get_user_link( $user_id ) {
        if ( $user_id ) {
            $user_obj = get_user_by( 'id', $user_id );
            return '<a data-id="' . esc_attr( $user_id ) . '" href="' . get_edit_user_link( $user_id ) . '">' . esc_html( $user_obj->display_name ) . '</a>';
        } else {
            return esc_html( 'Guest', 'admin-site-enhancements' );
        }
    }

}
