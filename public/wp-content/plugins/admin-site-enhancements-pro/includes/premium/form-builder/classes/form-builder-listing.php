<?php
defined( 'ABSPATH' ) || die();

/**
 * Adding WP List table class if it's not available.
 */
if ( ! class_exists(WP_List_Table::class ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Form_Builder_Listing extends \WP_List_Table {

    private $table_data;
    private $status;

    public function __construct() {
        parent::__construct(
            array(
                'singular' => 'Form',
                'plural' => 'Forms',
                'ajax' => false,
            )
        );
        $this->status = htmlspecialchars_decode( Form_Builder_Helper::get_var( 'status', 'sanitize_text_field', 'published' ) );
    }

    public function no_items() {
        esc_html_e( 'No forms found. Please create a new one.', 'admin-site-enhancements' );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'cd':
            case 'name':
            case 'entries':
            case 'id':
            case 'shortcode':
            case 'created_at':
            default:
                return $item[$column_name];
        }
    }

    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'name' => esc_html__( 'Form Title', 'admin-site-enhancements' ),
            'entries' => esc_html__( 'Entries Count', 'admin-site-enhancements' ),
            'id' => 'Form ID',
            'shortcode' => esc_html__( 'Shortcode', 'admin-site-enhancements' ),
            'created_at' => esc_html__( 'Date', 'admin-site-enhancements' )
        );
    }

    public function column_title( $item ) {
        $form_name = $item['name'];
        $form_id = $item['id'];
        if ( trim( $form_name ) == '' ) {
            $form_name = esc_html__( '( no title )', 'admin-site-enhancements' );
        }
        $edit_url = admin_url( 'admin.php?page=formbuilder&formbuilder_action=edit&id=' . absint( $form_id ) );

        $output = '<strong>';
        if ( 'trash' == $this->status ) {
            $output .= esc_html( $form_name );
        } else {
            $output .= '<a class="row-title" data-form-name="' . esc_attr( $form_name ) . '" href="' . esc_url( $edit_url ) . '" aria-label="' . sprintf(esc_html__( '%s (Edit )', 'admin-site-enhancements' ), $form_name ) . '">' . esc_html( $form_name ) . '</a>';
        }
        $output .= '</strong>';

        // Get actions.
        $actions = $this->get_action_links( $item );
        $row_actions = array();

        foreach ( $actions as $id => $action ) {
            $row_actions[] = '<span class="' . esc_attr( $id ) . '"><a href="' . $action['url'] . '">' . $action['label'] . '</a></span>';
        }

        $output .= '<div class="row-desc">' . $item['description'] . '</div>';

        $output .= '<div class="row-actions">' . implode( ' | ', $row_actions ) . '</div>';

        return $output;
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
        $hidden = ( is_array(get_user_meta(get_current_user_id(), 'managetoplevel_page_formbuildercolumnshidden', true ) )) ? get_user_meta(get_current_user_id(), 'managetoplevel_page_formbuildercolumnshidden', true ) : array();
        $primary = 'id';
        $this->_column_headers = array( $columns, $hidden, $sortable, $primary );

        if ( $this->table_data ) {
            foreach ( $this->table_data as $item ) {
                $id = $item['id'];
                $data[$id] = array(
                    'name' => $this->column_title( $item ),
                    'entries' => $this->get_entry_link( $id ),
                    'entries_count' => $this->get_entries_count( $id ),
                    'id' => $id,
                    'form_key' => $item['form_key'],
                    'shortcode' => '<div class="shortcode-wrapper">
                                        <div class="the-shortcode">[formbuilder id="' . $id . '"]</div>
                                        <span class="copy-shortcode-button" data-clipboard-text="' . $id . '">' . Form_Builder_Icons::get( 'copy' ) . '</span>
                                    </div>',
                    'created_at' => Form_Builder_Helper::convert_date_format( $item['created_at'] )
                );
            }

            usort( $data, array(&$this, 'usort_reorder' ) );

            /* pagination */
            $per_page = $this->get_items_per_page( 'forms_per_page', 10 );
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

    private function usort_reorder( $a, $b ) {
        // If no sort, default to user_login
        $orderby = Form_Builder_Helper::get_var( 'orderby', 'sanitize_text_field', 'created_at' );

        if ( 'entries' == $orderby ) {
            $orderby = 'entries_count';
        }

        // If no order, default to asc
        $order = Form_Builder_Helper::get_var( 'order', 'sanitize_text_field', 'desc' );

        if ( 'entries_count' == $orderby ) {
            // Determine sort order. Ref: https://stackoverflow.com/a/2852918
            if ( 'desc' == $order ) {
                $result = $b[$orderby] - $a[$orderby];
            } else {
                $result = $a[$orderby] - $b[$orderby];                
            }

            // Send final sort direction to usort
            return $result;
        } else {
            // Determine sort order
            $result = strcmp( $a[$orderby], $b[$orderby] );

            // Send final sort direction to usort
            return ( $order === 'asc' ) ? $result : -$result;            
        }
    }

    private function get_table_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'asenha_formbuilder_forms';
        $status = $this->status;
        $search = htmlspecialchars_decode( Form_Builder_Helper::get_var( 's' ) );

        if ( $search ) {
            $query = $wpdb->prepare("SELECT * from {$table} WHERE status=%s AND name Like %s", $status, '%' . $wpdb->esc_like( $search ) . '%' );
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
                $this->extra_tablenav( $which );
            }

            $this->pagination( $which );
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    public function extra_tablenav( $which ) {
        if ( 'trash' == $this->status ) {
            ?>
            <div class="alignleft actions"><?php submit_button(esc_html__( 'Empty Trash', 'admin-site-enhancements' ), 'apply', 'delete_all', false ); ?></div>
            <?php
        }
    }

    public function get_sortable_columns() {
        return array(
            'name' => array( 'name', false ),
            'id' => array( 'id', false ),
            'entries' => array( 'entries', false ),
            'created_at' => array( 'created_at', false ),
        );
    }

    public function get_action_links( $item ) {
        $form_id = $item['id'];
        $actions = array();
        $trash_links = $this->delete_trash_links( $form_id );
        if ( 'trash' == $this->status ) {
            $actions['restore'] = $trash_links['restore'];
            $actions['delete'] = $trash_links['delete'];
        } else {
            $actions['edit'] = array(
                'label' => esc_html__( 'Edit', 'admin-site-enhancements' ),
                'url' => admin_url( 'admin.php?page=formbuilder&formbuilder_action=edit&id=' . $form_id )
            );
            $actions['view'] = array(
                'label' => esc_html__( 'Preview', 'admin-site-enhancements' ),
                'url' => admin_url( 'admin-ajax.php?action=formbuilder_preview&form=' . $form_id )
            );
            $actions['duplicate'] = array(
                'label' => esc_html__( 'Duplicate', 'admin-site-enhancements' ),
                'url' => wp_nonce_url( '?page=formbuilder&formbuilder_action=duplicate&id=' . $form_id )
            );
            $actions['trash'] = $trash_links['trash'];
        }
        return $actions;
    }

    private function delete_trash_links( $id ) {
        $base_url = '?page=formbuilder&id=' . $id;
        return array(
            'restore' => array(
                'label' => esc_html__( 'Restore', 'admin-site-enhancements' ),
                'url' => wp_nonce_url( $base_url . '&formbuilder_action=untrash', 'untrash_form_' . absint( $id ) ),
            ),
            'delete' => array(
                'label' => esc_html__( 'Delete Permanently', 'admin-site-enhancements' ),
                'url' => wp_nonce_url( $base_url . '&formbuilder_action=destroy', 'destroy_form_' . absint( $id ) ),
            ),
            'trash' => array(
                'label' => esc_html__( 'Trash', 'admin-site-enhancements' ),
                'url' => wp_nonce_url( $base_url . '&formbuilder_action=trash', 'trash_form_' . absint( $id ) ),
            )
        );
    }

    public function get_entry_link( $id ) {
        $count = Form_Builder_Entry::get_entry_count( $id );
        return '<a data-entries-count="' . esc_attr( $count ) . '" href="' . esc_url(admin_url( 'admin.php?page=formbuilder-entries&form_id=' . $id ) ) . '">' . esc_html( $count ) . ' ' . __( 'entries', 'admin-site-enhancements' ) . '</a>';
    }

    public function get_entries_count( $id ) {
        $count = Form_Builder_Entry::get_entry_count( $id );
        return intval( $count );
    }

    public function get_views() {
        $statuses = array(
            'published' => esc_html__( 'All', 'admin-site-enhancements' ),
            'trash' => esc_html__( 'Trash', 'admin-site-enhancements' ),
        );

        $links = array();

        $counts = self::get_count();

        foreach ( $statuses as $status => $name ) {
            $class = ( $status == $this->status ) ? ' class="current"' : '';
            if ( $counts->{$status}) {
                $links[$status] = '<a href="' . esc_url( '?page=formbuilder&status=' . $status ) . '" ' . $class . '>' . sprintf( __( '%1$s <span class="count">(%2$s )</span>', 'admin-site-enhancements' ), $name, number_format_i18n( $counts->{$status}) ) . '</a>';
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

    public static function get_count() {
        global $wpdb;
        $query = $wpdb->prepare("SELECT status FROM {$wpdb->prefix}asenha_formbuilder_forms WHERE id!=%d", 0 );
        $results = $wpdb->get_results( $query );
        $statuses = array( 'published', 'draft', 'trash' );
        $counts = array_fill_keys( $statuses, 0 );
        foreach ( $results as $row ) {
            if ( 'trash' != $row->status ) {
                $counts['published']++;
            } else {
                $counts['trash']++;
            }
        }
        $counts = (object ) $counts;
        return $counts;
    }

    public static function get_status( $id = 0 ) {
        global $wpdb;
        $query = $wpdb->prepare("SELECT status FROM {$wpdb->prefix}asenha_formbuilder_forms WHERE id=%d", $id );
        $results = $wpdb->get_results( $query );
        return isset( $results[0] ) ? $results[0]->status : 'unavailable';
    }

}
