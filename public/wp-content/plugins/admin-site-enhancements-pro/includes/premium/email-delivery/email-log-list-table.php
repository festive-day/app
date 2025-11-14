<?php

namespace ASENHA\EmailDelivery;

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use WP_List_Table;

/**
 * Class that outputs the list table for email delivery log entries
 *
 * @since 7.1.0
 */
class Email_Log_Table extends WP_List_Table {
    
    private $column_titles;

    public function __construct() {
        parent::__construct(
            array(
                'singular'  => 'email_log_entry',
                'plural'    => 'email_log_entries',
                'ajax'      => false
            )
        );
    }
	
    /**
     * Prepare the items for the list table
     * 
     * @since 7.1.0
     */
    public function prepare_items() {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'asenha_email_delivery';
        
        $columns = $this->get_columns();
        $hidden_columns = $this->get_hidden_columns();
        $sortable_columns = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden_columns, $sortable_columns );
        
        $table_data = $this->get_table_data();
        // usort( $table_data, array( $this, 'usort_reorder' ) );
        // $current_page = $this->get_pagenum();
        $query = '';

        $status = empty( $_REQUEST['status'] ) ? 'all' : sanitize_text_field( $_REQUEST['status'] );
        $search = empty( $_REQUEST['s'] ) ? false : sanitize_text_field( $_REQUEST['s'] );

        $where_clause = false;

        if ( ! empty( $status ) ) {
            if ( 'successful' == $status || 'failed' == $status ) {
                if ( $where_clause ) {
                    $query .= "AND ";
                } else {
                    $query .= "WHERE ";
                    $where_clause = true;
                }
                
                $query .= "( status LIKE "."'%".$wpdb->esc_like($status)."%')";
            }
        }
                
        if ( ! empty( $search ) ) {
            if ( $where_clause ) {
                $query .= "AND ";
            } else {
                $query .= "WHERE ";
                $where_clause = true;
            }

            $query .= "( status LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        error LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        subject LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        message LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        send_to LIKE "."'%".$wpdb->esc_like($search)."%'"." OR 
                        sender LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        reply_to LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        headers LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        attachments LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        backtrace LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        processor LIKE "."'%".$wpdb->esc_like($search)."%')";
        }
                
        $query = empty( $query ) ? '' : $query;
        $total_items = intval( $wpdb->get_var("SELECT COUNT(id) FROM $table_name $query") );
        
        $this->items = $table_data;
        $this->set_pagination_args( array(
            'total_items'   => $total_items,
            'per_page'      => 100,
            'total_pages'   => ceil( $total_items / 100 ), // Round up
        ) );
    }
    
    /**
     * Define custom views
     * 
     * @since 7.1.0
     */
    public function get_views() { 
        $views_options = array(
            'all'           => __( 'All' , 'admin-site-enhancements' ),
            'successful'    => __( 'Successful' , 'admin-site-enhancements' ),
            'failed'        => __( 'Failed' , 'admin-site-enhancements' ),
        );

        $email_log_page_url = $this->get_page_base_url();
        
        $views = array();
        foreach ( $views_options as $status => $label ) {
            $views[$status] = sprintf(
                '<a href="%1$s" %2$s>' . '%3$s' . ' <span class="count">(%4$d)</span></a>',
                esc_url( add_query_arg( 'status', $status, $email_log_page_url ) ),
                $status == $this->get_current_status() ? 'class="current"' : '',
                $label,
                absint( $this->status_count( $status ) )
            );
        }

        return $views;
    }
    
    /**
     * Get the count for delivery status
     * 
     * @since 7.1.0
     */
    public function status_count( $status = 'all' ) {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'asenha_email_delivery';

        if ( 'successful' == $status || 'failed' == $status ) {
            $results = $wpdb->get_results( 
                        "SELECT * FROM $table_name WHERE 
                        ( status LIKE "."'%".$wpdb->esc_like($status)."%')
                        ORDER BY id ASC", 
                        ARRAY_A 
                    );
        } else {
            $results = $wpdb->get_results( 
                        "SELECT * FROM $table_name 
                        ORDER BY id ASC", 
                        ARRAY_A 
                    );            
        }

        if ( is_null( $results ) ) {
            return 0;
        } else {
            return count( $results );        
        }
    }
    
    /**
     * Get current status
     * 
     * @since 7.1.0
     */
    public function get_current_status() {
        $current_status = isset( $_GET['status'] ) && ! empty( $_GET['status'] ) ? $_GET['status'] : 'all';
        return $current_status;
    }
    
    /**
     * Define the columns to use in the list table
     * 
     * @since 7.1.0
     */
    public function get_columns() {
        $columns = array(
            'cb'                => '',
            'subject'           => __( 'Subject', 'admin-site-enhancements'),
            'send_to'           => __( 'To', 'admin-site-enhancements' ),
            'status'            => __( 'Status', 'admin-site-enhancements' ),
            'error'             => __( 'Error', 'admin-site-enhancements' ),
            'sent_on'           => __( 'When', 'admin-site-enhancements' ),
            'action'            => __( 'Action', 'admin-site-enhancements' ),
        );
        
        $this->column_titles = array_keys( $columns );
        
        return $columns;
    }
    
    /**
     * Define hidden columns
     * 
     * @since 7.1.0
     */
    public function get_hidden_columns() {
        return array( 'id' );
    }
    
    /**
     * Define sortable columns
     * 
     * @since 7.1.0
     */
    public function get_sortable_columns() {
        return array(
            'subject'           => array( 'subject', true ),
            'send_to'           => array( 'send_to', true ),
            'status'            => array( 'status', true ),
            'error'             => array( 'error', true ),
            'sent_on'        => array( 'sent_on', true ),
        );
    }
    
    /**
     * Sort log entries based on $_GET variables
     * Not currently in use as order/reorder is handled directly in get_table_data()
     * 
     * @since 7.1.0
     */
    public function usort_reorder( $a, $b ) {        
        // If no orderby, sort by delivery time
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'sent_on';

        // If no order, default to desc
        $order = ( ! empty( $_GET['order'] ) && 'asc' === $_GET['order'] ) ? 'ASC' : 'DESC';

        // Determine sort order
        $result = strcmp( $a[$orderby], $b[$orderby] );

        // Send final sort direction to usort
        if ( $order === 'ASC' ) {
            return $result;
        } else {
            return -$result;        
        }
    }
    
    /**
     * Get data for the list table
     * 
     * @since 7.1.0
     */
    public function get_table_data() {
        global $wpdb;
        $table_name  = $wpdb->prefix.'asenha_email_delivery';
        $table_data = array();
        $status = empty( $_REQUEST['status'] ) ? 'all' : sanitize_text_field( $_REQUEST['status'] );
        $search = empty( $_REQUEST['s'] ) ? false : sanitize_text_field( $_REQUEST['s'] );

        $page = $this->get_pagenum();
        $page = $page - 1;
        $start = $page * 100;

        $orderby = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'sent_on';
        $order = isset( $_REQUEST['order'] ) && 'asc' == $_REQUEST['order'] ? 'ASC' : 'DESC';

        $query = "SELECT * FROM $table_name ";
        $where_clause = false;
        
        if ( ! empty( $search ) ) {
            if ( $where_clause ) {
                $query .= "AND ";
            } else {
                $query .= "WHERE ";
                $where_clause = true;
            }

            $query .= "( status LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        error LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        subject LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        message LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        send_to LIKE "."'%".$wpdb->esc_like($search)."%'"." OR 
                        sender LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        reply_to LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        headers LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        attachments LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        backtrace LIKE "."'%".$wpdb->esc_like($search)."%'"." OR
                        processor LIKE "."'%".$wpdb->esc_like($search)."%')";
        }
        
        if ( ! empty( $status ) ) {
            if ( 'successful' == $status || 'failed' == $status ) {
                if ( $where_clause ) {
                    $query .= "AND ";
                } else {
                    $query .= "WHERE ";
                    $where_clause = true;
                }
                
                $query .= "( status LIKE "."'%".$wpdb->esc_like($status)."%')";
            }
        }

        $query .= "ORDER BY $orderby $order LIMIT $start,100";
        $results = $wpdb->get_results( $query, ARRAY_A );

        $row = array();
        foreach ( $results as $result ) {
            $row['id'] = $result['id'];
            
            foreach ( $this->column_titles as $column_title ) {
                if ( 'sent_on' == $column_title ) {
                    $date_time = \DateTime::createFromFormat( 'Y-m-d H:i:s', $result[$column_title] );
                    $result[$column_title] = sprintf( '%1$s <br />%2$s %3$s',
                                            $date_time->format( 'F d, Y' ),
                                            __( 'at', 'admin-site-enhancements' ),
                                            $date_time->format( 'g:i a' )
                                            );
                }
                                
                $row[$column_title] = isset( $result[$column_title] ) ? $result[$column_title] : '';
                $row[$column_title] = ( strlen( $row[$column_title] ) > 100 ) ? substr( $row[$column_title], 0, 100 ) . '...' : $row[$column_title];
                $row[$column_title] = stripslashes( $row[$column_title] );
                
                if ( 'status' == $column_title ) {
                    switch ( $result['status'] ) {
                        case 'successful';
                            $status = '<div class="delivery-status"><span class="status-indicator status-successful"></span>' . __( 'Successful', 'admin-site-enhancements' ) . '</div>';
                            break;

                        case 'failed';
                            $status = '<div class="delivery-status"><span class="status-indicator status-failed"></span>' . __( 'Failed', 'admin-site-enhancements' ) . '</div>';
                            break;

                        case '';
                            $status = '<div class="delivery-status"><span class="status-indicator status-unknown"></span>' . __( 'Unknown', 'admin-site-enhancements' ) . '</div>';
                            break;
                    }
                    
                    $row[$column_title] = $status;
                }                
            }

            $table_data[] = $row;
        }

        return $table_data;
    }
    
    /**
     * Output search field
     * 
     * @since 7.1.0
     */
    public function search_box( $text, $input_id ) {
        if ( empty( $_GET['s'] ) && ! $this->has_items() ) {
            return;
        }

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['status'] ) ) {
            echo '<input type="hidden" name="status" value="' . esc_attr( $_REQUEST['status'] ) . '" />';
        }

        $input_id  = $input_id . '-search-input';

        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
            <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
            <input type="search" placeholder="<?php _e( 'Enter keyword', 'admin-site-enhancements' ) ?>" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
        <?php
    }
    
    /**
     * Define what data to show on each column of the table
     * 
     * @since 7.1.0
     */
    public function column_default( $item, $column_title ) {
        if ( 'action' == $column_title ) {
            $actions =  '<a href="#" class="button button-secondary log-view-more-info" data-db-row-id="' . $item['id'] . '">' . __( 'View', 'admin-site-enhancements' ) . '</a>';
            
            $actions .= '<a href="admin.php?action=delete_email&amp;message-id=' . $item['id'] . '&amp;nonce=' . wp_create_nonce( 'asenha-delete-email-' . $item['id'] ) . '" class="button button-secondary delete-email" data-db-row-id="' . $item['id'] . '">' . __( 'Delete', 'admin-site-enhancements' ) . '</a>';
            
            $actions .= '<a href="#" class="button button-secondary resend-email" data-db-row-id="' . $item['id'] . '">' . __( 'Resend', 'admin-site-enhancements' ) . '</a>';
            return $actions;
        } else {
            return $item[$column_title];        
        }
    }
    
    /**
     * Define checkbox for bulk action
     * 
     * @since 7.1.0
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['id']
        );
    }
    
}