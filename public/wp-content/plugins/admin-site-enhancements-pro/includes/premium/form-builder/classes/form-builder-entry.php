<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Entry {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ), 10 );
        add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 15, 3 );

        add_action( 'wp_ajax_formbuilder_process_entry', array( $this, 'process_entry' ) );
        add_action( 'wp_ajax_nopriv_formbuilder_process_entry', array( $this, 'process_entry' ) );
    }

    public function add_menu() {
        global $form_entry_listing_page;
        $form_entry_listing_page = add_submenu_page(
            'formbuilder', 
            esc_html__( 'Entries', 'admin-site-enhancements' ), 
            esc_html__( 'Entries', 'admin-site-enhancements' ), 
            'manage_options', 
            'formbuilder-entries', 
            array( $this, 'route' )
        );
        add_action("load-$form_entry_listing_page", array( $this, 'listing_page_screen_options' ) );
    }

    public static function route() {
        $action = htmlspecialchars_decode( Form_Builder_Helper::get_var( 'formbuilder_action', 'sanitize_text_field', Form_Builder_Helper::get_var( 'action' ) ));

        if ( Form_Builder_Helper::get_var( 'delete_all' ) ) {
            $action = 'delete_all';
        }

        switch ( $action ) {
            case 'view':
            case 'destroy':
            case 'untrash':
            case 'trash':
            case 'delete_all':
                return self::$action();
            default:

                if (strpos( $action, 'bulk_' ) === 0 ) {
                    self::bulk_actions();
                    return;
                }

                self::display_entry_list();

                return;
        }
    }

    public static function view( $id = 0 ) {
        if ( ! $id ) {
            $id = Form_Builder_Helper::get_var( 'id', 'absint' );
        }
        $entry = self::get_entry_vars( $id );
        // vi( $entry );

        if ( ! $entry ) {
            ?>
            <div id="message" class="error notice is-dismissible">
                <p><?php esc_html_e( 'You are trying to view an entry that does not exist.', 'admin-site-enhancements' ); ?></p>
            </div>
            <?php
            return;
        }

        include( FORMBUILDER_PATH . 'entries/entry-detail.php' );
    }

    public static function display_message( $message, $class ) {
        if ( '' !== $message ) {
            echo '<div id="message" class="' . esc_attr( $class ) . ' notice is-dismissible">';
            echo '<p>' . wp_kses_post( $message ) . '</p>';
            echo '</div>';
        }
    }

    public static function display_entry_list( $message = '', $class = 'updated' ) {
        ?>
        <div class="fb-content">
            <div class="fb-entry-list-wrap wrap">
                <h1 class="wp-heading-inline"><?php echo __( 'Entries', 'admin-site-enhancements' ); ?></h1>
                <hr class="wp-header-end">

                <div id="fb-entry-list">
                    <?php
                    self::display_message( $message, $class );
                    $entry_table = new Form_Builder_Entry_Listing();
                    $entry_status = Form_Builder_Helper::get_var( 'status', 'sanitize_title', 'published' );
                    $entry_table->views();
                    ?>
                    <form id="posts-filter" method="get">
                        <input type="hidden" name="page" value="<?php echo esc_attr( Form_Builder_Helper::get_var( 'page', 'sanitize_title' ) ); ?>" />
                        <input type="hidden" name="status" value="<?php echo esc_attr( $entry_status ); ?>" />
                        <?php
                        $entry_table->prepare_items();
                        $entry_table->search_box( 'Search', 'search' );
                        $entry_table->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function listing_page_screen_options() {

        global $form_entry_listing_page;

        $screen = get_current_screen();
        $formbuilder_action = Form_Builder_Helper::get_var( 'formbuilder_action' );

        // get out of here if we are not on our settings page
        if ( ! is_object( $screen ) || $screen->id != $form_entry_listing_page || ( $formbuilder_action == 'view' ) )
            return;

        $args = array(
            'label' => esc_html__( 'Entries per page', 'admin-site-enhancements' ),
            'default' => 10,
            'option' => 'entries_per_page'
        );

        add_screen_option( 'per_page', $args );

        //new Form_Builder_Entry_Listing();
    }

    public function set_screen_option( $status, $option, $value ) {
        if ( 'entries_per_page' == $option )
            return $value;
    }

    public static function trash() {
        self::change_form_status( 'trash' );
    }

    public static function untrash() {
        self::change_form_status( 'untrash' );
    }

    public static function change_form_status( $status ) {
        $available_status = array(
            'untrash' => array( 'new_status' => 'published' ),
            'trash' => array( 'new_status' => 'trash' ),
        );

        if ( ! isset( $available_status[$status] ) ) {
            return;
        }

        $id = Form_Builder_Helper::get_var( 'id', 'absint' );

        check_admin_referer( $status . '_entry_' . $id );

        $count = 0;
        if (self::set_status( $id, $available_status[$status]['new_status'] ) ) {
            $count++;
        }

        $available_status['untrash']['message'] = sprintf( _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'admin-site-enhancements' ), $count );
        $available_status['trash']['message'] = sprintf( _n( '%1$s form moved to the Trash. %2$sUndo%3$s', '%1$s forms moved to the Trash. %2$sUndo%3$s', $count, 'admin-site-enhancements' ), $count, '<a href="' . esc_url( wp_nonce_url( '?page=formbuilder-entries&formbuilder_action=untrash&id=' . $id, 'untrash_entry_' . $id ) ) . '">', '</a>' );
        $message = $available_status[$status]['message'];

        self::display_entry_list( $message );
    }

    public static function set_status( $id, $status ) {
        $statuses = array( 'published', 'trash' );
        if ( ! in_array( $status, $statuses ) )
            return false;

        global $wpdb;

        if ( is_array( $id ) ) {
            $query = $wpdb->prepare("UPDATE {$wpdb->prefix}asenha_formbuilder_entries SET status=%s WHERE id IN (" . implode( ',', array_fill( 0, count( $id ), '%d' ) ) . ")", $status, ...$id );
            $query_results = $wpdb->query( $query );
        } else {
            $query_results = $wpdb->update( $wpdb->prefix . 'asenha_formbuilder_entries', array( 'status' => $status ), array( 'id' => $id ) );
        }

        return $query_results;
    }

    public static function delete_all() {
        $count = self::delete();
        $message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'admin-site-enhancements' ), $count );
        self::display_entry_list( $message );
    }

    public static function delete() {
        global $wpdb;
        $query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}asenha_formbuilder_entries WHERE status=%s", 'trash' );
        $trash_entries = $wpdb->get_results( $query );
        if ( ! $trash_entries ) {
            return 0;
        }
        $count = 0;
        foreach ( $trash_entries as $entry ) {
            self::destroy_entry( $entry->id );
            $count++;
        }
        return $count;
    }

    public static function destroy() {
        $id = Form_Builder_Helper::get_var( 'id', 'absint' );
        check_admin_referer( 'destroy_entry_' . $id );
        $count = 0;
        if (self::destroy_entry( $id ) ) {
            $count++;
        }
        $message = sprintf( 
            /* translators: %1$s: entries count */
            _n( '%1$s entry permanently deleted', '%1$s entries permanently deleted', $count, 'admin-site-enhancements' ), 
            $count 
        );
        self::display_entry_list( $message );
    }

    public static function destroy_entry( $id ) {
        global $wpdb;
        $entry = self::get_entry_vars( $id ); // Item meta is required for conditional logic in actions with 'delete' events.
        if ( ! $entry ) {
            return false;
        }

        $query = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'asenha_formbuilder_entry_meta WHERE item_id=%d', $id );
        $wpdb->query( $query );

        $query = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'asenha_formbuilder_entries WHERE id=%d', $id );
        $result = $wpdb->query( $query );
        return $result;
    }

    public static function bulk_actions() {
        $message = self::process_bulk_actions();
        self::display_entry_list( $message );
    }

    public static function process_bulk_actions() {
        if ( ! $_REQUEST)
            return;

        $bulkaction = Form_Builder_Helper::get_var( 'action', 'sanitize_text_field' );


        if ( $bulkaction == -1 ) {
            $bulkaction = Form_Builder_Helper::get_var( 'action2', 'sanitize_title' );
        }

        if ( ! empty( $bulkaction ) && strpos( $bulkaction, 'bulk_' ) === 0 ) {
            $bulkaction = str_replace( 'bulk_', '', $bulkaction );
        }

        $ids = Form_Builder_Helper::get_var( 'entry_id', 'sanitize_text_field' );

        if ( empty( $ids ) ) {
            $error = esc_html__( 'No entries were specified', 'admin-site-enhancements' );
            return $error;
        }

        if ( ! is_array( $ids ) ) {
            $ids = explode( ',', $ids );
        }

        switch ( $bulkaction ) {
            case 'delete':
                $message = self::bulk_destroy( $ids );
                break;
            case 'trash':
                $message = self::bulk_trash( $ids );
                break;
            case 'untrash':
                $message = self::bulk_untrash( $ids );
        }

        if ( isset( $message ) && ! empty( $message ) ) {
            return $message;
        }
    }

    public static function bulk_trash( $ids ) {
        $count = self::set_status( $ids, 'trash' );
        if ( ! $count ) {
            return '';
        }
        return sprintf( _n( '%1$s form moved to the Trash. %2$sUndo%3$s', '%1$s forms moved to the Trash. %2$sUndo%3$s', $count, 'admin-site-enhancements' ), $count, '<a href="' . esc_url( wp_nonce_url( '?page=formbuilder-entries&action=bulk_untrash&status=published&entry_id=' . implode( ',', $ids ), 'bulk-toplevel_page_formbuilder' ) ) . '">', '</a>' );
    }

    public static function bulk_untrash( $ids ) {
        $count = self::set_status( $ids, 'published' );
        if ( ! $count ) {
            return '';
        }
        return sprintf( _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'admin-site-enhancements' ), $count );
    }

    public static function bulk_destroy( $ids ) {
        $count = 0;
        foreach ( $ids as $id ) {
            $entry = self::destroy_entry( $id );
            if ( $entry ) {
                $count++;
            }
        }
        $message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'admin-site-enhancements' ), $count );
        return $message;
    }

    public static function get_entry_vars( $id ) {
        global $wpdb;
        $query = "SELECT e.*, f.name AS form_name, f.form_key AS form_key
        FROM {$wpdb->prefix}asenha_formbuilder_entries AS e
        LEFT OUTER JOIN {$wpdb->prefix}asenha_formbuilder_forms AS f ON e.form_id = f.id
        WHERE e.id = %d";

        $query = $wpdb->prepare( $query, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $entry = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $entry = self::get_meta( $entry );
        return $entry;
    }

    public static function get_meta( $entry ) {
        if ( ! $entry ) {
            return $entry;
        }

        global $wpdb;
        $query = "SELECT m.*, f.type AS field_type, f.field_key, f.name ";
        $query .= "FROM {$wpdb->prefix}asenha_formbuilder_entry_meta AS m ";
        $query .= "LEFT JOIN {$wpdb->prefix}asenha_formbuilder_fields AS f ON m.field_id = f.id ";
        $query .= "WHERE m.item_id = %d AND m.field_id != %d ";
        $query .= "ORDER BY m.id ASC";

        $query = $wpdb->prepare( $query, $entry->id, 0 );

        $metas = $wpdb->get_results( $query );

        $entry->metas = array();

        foreach ( $metas as $meta_val ) {
            if ( $meta_val->item_id == $entry->id ) {
                $entry->metas[$meta_val->field_id] = array(
                    'name' => $meta_val->name,
                    'value' => $meta_val->meta_value,
                    'type' => $meta_val->field_type
                );
                continue;
            }

            // include sub entries in an array
            if ( ! isset( $entry->metas[$meta_val->field_id] ) ) {
                $entry->metas[$meta_val->field_id] = array();
            }

            $entry->metas[$meta_val->field_id][] = $meta_val->meta_value;
        }

        return $entry;
    }

    public function process_entry() {
        global $wpdb;
        
        // Parse form submission 'data' from frontend.js and put it inside $data
        // The following code will cause issues when labels contain single apostrophe
        // parse_str( htmlspecialchars_decode( Form_Builder_Helper::get_post( 'data', 'esc_html' ) ), $data );
        // So, we replace esc_html() with htmlspecialchars()
        parse_str( htmlspecialchars_decode( Form_Builder_Helper::get_post( 'data', 'htmlspecialchars' ) ), $data );

        // vi( $data, '', 'before matrix fields normalization' );
        $data = $this->normalize_matrix_fields_answer( $data );
        // vi( $data, '', 'after matrix fields normalization' );

        $location = esc_url( Form_Builder_Helper::get_post( 'location', 'esc_html' ) );

        if ( empty( $data ) || empty( $data['form_id'] ) || ! isset( $data['form_key'] ) ) {
            return;
        }

        $form_id = $data['form_id'];
        $form = Form_Builder_Builder::get_form_vars( $form_id );

        if ( ! $form ) {
            return;
        }
        $errors = '';
        $errors = Form_Builder_Validate::validate( wp_unslash( $data ) );
        // vi( $errors );

        if ( empty( $errors ) ) {
            $form_settings = $form->settings;
            $entry_id = self::create( $data );

            $send_mail = new Form_Builder_Email( $form, $entry_id, $location );
            $check_mail = $send_mail->send_email();

            if ( ! $check_mail ) {
                $wpdb->update( $wpdb->prefix . 'asenha_formbuilder_entries', array( 'delivery_status' => false ), array( 'id' => $entry_id ) );
                return wp_send_json( array(
                    'status' => 'failed',
                    'message' => esc_html( $form_settings['error_message'] )
                ) );
            }
        }

        return wp_send_json( array(
            'status' => 'error',
            'message' => $errors
        ) );
    }
    
    /**
     * Matrix fields will submit an array even if no radio buttons or dropdown options has been selected. We check for those scenarios, and unset the array item from $data if no radio buttons / dropdown options were checked or selected.
     */
    public function normalize_matrix_fields_answer( $data ) {
        if ( is_array( $data['item_meta'] ) 
            && ! empty( $data['item_meta'] ) 
        ) {
            foreach ( $data['item_meta'] as $field_id => $field_value ) {
                if ( is_array( $field_value ) && ! empty( $field_value ) 
                    // Make sure we only normalize matrix fields by checking for the relevantarray keys
                    && ( isset( $field_value['dropdown_matrix_rows'] ) || isset( $field_value['dropdowns_matrix_rows'] ) ) 
                ) {
                    foreach ( $field_value as $key => $value ) {
                        if ( 'dropdown_matrix_rows' == $key || 'dropdowns_matrix_rows' == $key ) {
                            $is_field_value_empty = true;
                            
                            if ( is_array( $value ) && ! empty( $value ) ) {
                                foreach ( $value as $val_key => $val ) {
                                    if ( is_array( $val ) && ! empty( $val ) ) {
                                        foreach ( $val as $val_key => $val_value ) {
                                            if ( ! empty( $val_value ) ) {
                                                $is_field_value_empty = false;
                                                break;
                                            }
                                        }
                                    }
                                }                                
                            }
                            
                            if ( $is_field_value_empty ) {
                                $data['item_meta'][$field_id] = '';
                            }
                        }
                    }                    
                }
            }
        }
        
        return $data;
    }

    public static function create( $values ) {
        global $wpdb;
        $current_user_id = get_current_user_id();
        $user_id = $current_user_id ? $current_user_id : 0;
        $new_values = array(
            'ip' => sanitize_text_field( Form_Builder_Helper::get_ip() ),
            'delivery_status' => 1,
            'form_id' => isset( $values['form_id'] ) ? absint( $values['form_id'] ) : '',
            'created_at' => sanitize_text_field(current_time( 'mysql' ) ),
            'user_id' => absint( $user_id ),
            'status' => 'published'
        );

        $query_results = $wpdb->insert( $wpdb->prefix . 'asenha_formbuilder_entries', $new_values );
        if ( ! $query_results ) {
            return false;
        } else {
            $entry_id = $wpdb->insert_id;
        }
        
        if ( isset( $values['item_meta'] ) 
             && is_array( $values['item_meta'] )
             && ! empty( $values['item_meta'] ) 
        ) {
            foreach ( $values['item_meta'] as $field_id => $meta_value ) {
                $field = Form_Builder_Fields::get_field_vars( $field_id );
                
                // Let's process Likert / Matrix Scale field value so the data is more structured for DB storage
                if ( isset( $meta_value['likert_rows'] ) ) {
                    // vi( $meta_value, '', 'before processing');
                    $processed_meta_value = array();

                    foreach ( $meta_value['likert_rows'] as $key => $value ) {
                        $new_value = explode( '||', $value[0] );
                        $row_label = $new_value[0];
                        $column_label = $new_value[1];
                        $processed_meta_value['likert_rows'][$key]['row_label'] = wp_kses_post( $row_label );
                        $processed_meta_value['likert_rows'][$key]['column_label'] = wp_kses_post( $column_label );
                    }
                    
                    $meta_value = $processed_meta_value;
                    // vi( $meta_value, '', 'after processing');
                }

                // Let's process Matrix of Uniform Dropdowns field value so the data is more structured for DB storage
                if ( isset( $meta_value['dropdown_matrix_rows'] ) ) {
                    // vi( $meta_value, '', 'before processing');
                    $processed_meta_value = array();

                    foreach ( $meta_value['dropdown_matrix_rows'] as $key => $value ) {
                        foreach ( $value as $val_key => $val ) {
                            if ( ! empty( $val ) ) {
                                $new_val = explode( '||', $val );
                                $row_label = $new_val[0];
                                $column_label = $new_val[1];
                                $selected_option = $new_val[2];

                                $processed_meta_value['dropdown_matrix_rows'][$key]['row_label'] = wp_kses_post( $row_label );
                                $processed_meta_value['dropdown_matrix_rows'][$key]['choices'][$val_key]['column_label'] = wp_kses_post( $column_label );
                                $processed_meta_value['dropdown_matrix_rows'][$key]['choices'][$val_key]['selected_option'] = wp_kses_post( $selected_option );
                            }
                        }
                    }
                    
                    $meta_value = $processed_meta_value;
                    // vi( $meta_value, '', 'after processing');
                }

                // Let's process Matrix of Variable Dropdowns field value so the data is more structured for DB storage
                if ( isset( $meta_value['dropdowns_matrix_rows'] ) ) {
                    // vi( $meta_value, '', 'before processing');
                    $processed_meta_value = array();

                    foreach ( $meta_value['dropdowns_matrix_rows'] as $key => $value ) {
                        foreach ( $value as $val_key => $val ) {
                            $new_val = explode( '||', $val );
                            $row_label = $new_val[0];
                            $column_label = isset( $new_val[1] ) ? $new_val[1] : '';
                            $selected_option = isset( $new_val[2] ) ? $new_val[2] : '';

                            $processed_meta_value['dropdowns_matrix_rows'][$key]['row_label'] = wp_kses_post( $row_label );
                            $processed_meta_value['dropdowns_matrix_rows'][$key]['choices'][$val_key]['column_label'] = wp_kses_post( $column_label );
                            $processed_meta_value['dropdowns_matrix_rows'][$key]['choices'][$val_key]['selected_option'] = wp_kses_post( $selected_option );
                        }
                    }
                    
                    $meta_value = $processed_meta_value;
                    // vi( $meta_value, '', 'after processing');
                }
                
                if ( ! empty( $meta_value ) ) {
                    if ( is_array( $meta_value ) ) {
                        $meta_value = serialize( $meta_value );
                    } else {
                        if ( 'textarea' == $field->type ) {
                            $meta_value = sanitize_textarea_field( trim( $meta_value ) );
                        } else {
                            $meta_value = sanitize_text_field( trim( $meta_value ) );
                        }
                    }

                    $meta_values = array(
                        'meta_value' => $meta_value,
                        'item_id' => absint( $entry_id ),
                        'field_id' => absint( $field_id ),
                        'created_at' => sanitize_text_field( current_time( 'mysql' ) ),
                    );

                    self::sanitize_meta_value( $meta_values );

                    $query_results = $wpdb->insert( $wpdb->prefix . 'asenha_formbuilder_entry_meta', $meta_values );
                }
            }
        }
        return $entry_id;
    }

    private static function sanitize_meta_value(&$values ) {
        $field = Form_Builder_Fields::get_field_vars( $values['field_id'] );
        if ( $field ) {
            $field_obj = Form_Builder_Fields::get_field_object( $field );
            $values['meta_value'] = $field_obj->set_value_before_save( $values['meta_value'] );
            $values['meta_value'] = $field_obj->sanitize_value( $values['meta_value'] );
        }
    }

    public static function get_count() {
        global $wpdb;
        $query = $wpdb->prepare("SELECT status FROM {$wpdb->prefix}asenha_formbuilder_entries WHERE id!=%d", 0 );
        $results = $wpdb->get_results( $query );
        $statuses = array( 'published', 'trash' );
        $counts = array_fill_keys( $statuses, 0 );
        foreach ( $results as $row ) {
            if ( 'published' == $row->status ) {
                $counts['published']++;
            } else {
                $counts['trash']++;
            }
        }
        return $counts;
    }

    public static function get_entry_count( $form_id ) {
        global $wpdb;
        $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}asenha_formbuilder_entries e LEFT OUTER JOIN {$wpdb->prefix}asenha_formbuilder_forms f ON e.form_id=f.id WHERE e.form_id=%d AND e.status='published'", $form_id );
        $count = $wpdb->get_var( $query );
        return $count;
    }

    public static function get_prev_entry( $entry_id ) {
        global $wpdb;
        $query = $wpdb->prepare("select id from {$wpdb->prefix}asenha_formbuilder_entries WHERE id < %d ORDER BY id DESC LIMIT 1", $entry_id );
        $results = $wpdb->get_results( $query );
        return $results;
    }

    public static function get_next_entry( $entry_id ) {
        global $wpdb;
        $query = $wpdb->prepare("select id from {$wpdb->prefix}asenha_formbuilder_entries WHERE id > %d ORDER BY id ASC LIMIT 1", $entry_id );
        $results = $wpdb->get_results( $query );
        return $results;
    }

}

new Form_Builder_Entry();
