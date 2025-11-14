<?php

namespace ASENHA\Classes;

use WP_Error;
use ASENHA\EmailDelivery\Email_Log_Table;

/**
 * Class for Email Delivery module
 *
 * @since 6.9.5
 */
class Email_Delivery {
    private $log_entry_id;

    /**
     * Send emails using external SMTP service
     *
     * @since 4.6.0
     */
    public function deliver_email_via_smtp( $phpmailer ) {

        $options                    = get_option( ASENHA_SLUG_U, array() );
        $smtp_host                  = $options['smtp_host'];
        $smtp_port                  = $options['smtp_port'];
        $smtp_security              = $options['smtp_security'];
        $smtp_authentication        = isset( $options['smtp_authentication'] ) ? $options['smtp_authentication'] : 'enable';
        $smtp_username              = $options['smtp_username'];
        $smtp_password              = $options['smtp_password'];
        $smtp_default_from_name     = $options['smtp_default_from_name'];
        $smtp_default_from_email    = $options['smtp_default_from_email'];
        $smtp_force_from            = $options['smtp_force_from'];
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $smtp_replyto_name      = isset( $options['smtp_replyto_name'] ) ? $options['smtp_replyto_name'] : '';
            $smtp_replyto_email     = isset( $options['smtp_replyto_email'] ) ? $options['smtp_replyto_email'] : '';
            $smtp_bcc_emails        = isset( $options['smtp_bcc_emails'] ) ? $options['smtp_bcc_emails'] : '';
        }
        $smtp_bypass_ssl_verification   = $options['smtp_bypass_ssl_verification'];
        $smtp_debug                 = $options['smtp_debug'];

        // Do nothing if host or password is empty
        // if ( empty( $smtp_host ) || empty( $smtp_password ) ) {
        //  return;
        // }

        // Maybe override FROM email and/or name if the sender is "WordPress <wordpress@sitedomain.com>", the default from WordPress core and not yet overridden by another plugin.

        $from_name = $phpmailer->FromName;
        $from_email_beginning = substr( $phpmailer->From, 0, 9 ); // Get the first 9 characters of the current FROM email

        if ( $smtp_force_from ) {
            $phpmailer->FromName    = $smtp_default_from_name;          
            $phpmailer->From        = $smtp_default_from_email;
        } else {
            if ( ( 'WordPress' === $from_name ) && ! empty( $smtp_default_from_name ) ) {
                $phpmailer->FromName = $smtp_default_from_name;
            }

            if ( ( 'wordpress' === $from_email_beginning ) && ! empty( $smtp_default_from_email ) ) {
                $phpmailer->From = $smtp_default_from_email;
            }
        }
        
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            if ( ! empty( $smtp_replyto_email ) ) {
                $phpmailer->clearReplyTos();
                $phpmailer->addReplyTo( $smtp_replyto_email, $smtp_replyto_name );
            }

            if ( ! empty( $smtp_bcc_emails ) ) {
                $smtp_bcc_emails = explode( ',', $smtp_bcc_emails );
                foreach ( $smtp_bcc_emails as $bcc_email ) {
                    $phpmailer->addBcc( trim( $bcc_email ) );
                }
            }
        }

        // Only attempt to send via SMTP if all the required info is present. Otherwise, use default PHP Mailer settings as set by wp_mail()
        if ( ! empty( $smtp_host ) && ! empty( $smtp_port ) && ! empty( $smtp_security ) ) {

            // Send using SMTP
            $phpmailer->isSMTP(); // phpcs:ignore

            if ( 'enable' == $smtp_authentication ) {
                $phpmailer->SMTPAuth = true; // phpcs:ignore
            } else {
                $phpmailer->SMTPAuth = false; // phpcs:ignore
            }

            // Set some other defaults
            // $phpmailer->CharSet  = 'utf-8'; // phpcs:ignore
            $phpmailer->XMailer     = 'Admin and Site Enhancements v' . ASENHA_VERSION . ' - a WordPress plugin'; // phpcs:ignore

            $phpmailer->Host        = $smtp_host;       // phpcs:ignore
            $phpmailer->Port        = $smtp_port;       // phpcs:ignore
            $phpmailer->SMTPSecure  = $smtp_security;   // phpcs:ignore

            if ( 'enable' == $smtp_authentication ) {
                $phpmailer->Username    = trim( $smtp_username );   // phpcs:ignore
                $phpmailer->Password    = trim( $smtp_password );   // phpcs:ignore                
            }

        }
        
        // If verification of SSL certificate is bypassed
        // Reference: https://www.php.net/manual/en/context.ssl.php & https://stackoverflow.com/a/30803024

        if ( $smtp_bypass_ssl_verification ) {
            $phpmailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];
        }

        // If debug mode is enabled, send debug info (SMTP::DEBUG_CONNECTION) to WordPress debug.log file set in wp-config.php
        // Reference: https://github.com/PHPMailer/PHPMailer/wiki/SMTP-Debugging

        if ( $smtp_debug ) {
            $phpmailer->SMTPDebug   = 4;            //phpcs:ignore
            $phpmailer->Debugoutput = 'error_log';  //phpcs:ignore
        }

    }
    
    /**
     * Send a test email and use SMTP host if defined in settings
     * 
     * @since 5.3.0
     */
    public function send_test_email() {
        
        if ( isset( $_REQUEST['email_to'] ) 
            && isset( $_REQUEST['nonce'] ) 
            && current_user_can( 'manage_options' )
        ) {
            if ( wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'send-test-email-nonce_' . get_current_user_id() ) ) {

                $content = array(
                    array(
                        'title' => 'Hey... are you getting this?',
                        'body'  => '<p><strong>Looks like you did!</strong></p>',
                    ),
                    array(
                        'title' => 'There\'s a message for you...',
                        'body'  => '<p><strong>Here it is:</strong></p>',
                    ),
                    array(
                        'title' => 'Is it working?',
                        'body'  => '<p><strong>Yes, it\'s working!</strong></p>',
                    ),
                    array(
                        'title' => 'Hope you\'re getting this...',
                        'body'  => '<p><strong>Looks like this was sent out just fine and you got it.</strong></p>',
                    ),
                    array(
                        'title' => 'Testing delivery configuration...',
                        'body'  => '<p><strong>Everything looks good!</strong></p>',
                    ),
                    array(
                        'title' => 'Testing email delivery',
                        'body'  => '<p><strong>Looks good!</strong></p>',
                    ),
                    array(
                        'title' => 'Config is looking good',
                        'body'  => '<p><strong>Seems like everything has been set up properly!</strong></p>',
                    ),
                    array(
                        'title' => 'All set up',
                        'body'  => '<p><strong>Your configuration is working properly.</strong></p>',
                    ),
                    array(
                        'title' => 'Good to go',
                        'body'  => '<p><strong>Config is working great.</strong></p>',
                    ),
                    array(
                        'title' => 'Good job',
                        'body'  => '<p><strong>Everything is set.</strong></p>',
                    ),
                );
                
                $random_number = rand( 0, count( $content ) - 1 );

                $to = $_REQUEST['email_to'];
                $title = $content[$random_number]['title'];
                $body  = $content[$random_number]['body'] . '<p>This message was sent from <a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'url' ) . '</a> on ' . wp_date( 'F j, Y' ) . ' at '  . wp_date( 'H:i:s' ) . ' via ASE.</p>';
                $headers = array('Content-Type: text/html; charset=UTF-8');

                $success = wp_mail( $to, $title, $body, $headers );
                
                if ( $success ) {
                    $response = array(
                        'status'    => 'success',
                    );
                } else {
                    $response = array(
                        'status'    => 'failed',
                    );
                }
                
                echo json_encode( $response );
                                
            }
        }
        
    }
    
    /**
     * Process mail and log to database
     * 
     * @since v7.1.0
     */
    public function log_via_wp_mail__premium_only( $mail ) {
        // We keep the original mal for later
        $original_mail = $mail;

        // Normalize the list of recipients
        // Reference: https://plugins.trac.wordpress.org/browser/mailarchiver/tags/4.0.0/includes/listeners/class-corelistener.php#L139
        $recipients = null;

        if ( isset( $mail['to'] ) ) {
            if ( is_string( $mail['to'] ) ) {
                foreach( [ ',', ';' ] as $separator ) {
                    if ( false !== strpos( $mail['to'], $separator ) ) {
                        $recipients = explode( $separator, $mail['to'] );
                        break;
                    }
                }
                if ( ! isset( $recipients ) ) {
                    $recipients = $mail['to'];
                }
            } elseif ( isset( $mail['to'] ) ) {
                $recipients = $mail['to'];
            } else {
                $recipients = array( 'nobody@nowhere.com' );
            }            
        }
        
        $send_to = array();
        $this->get_all_emails__premium_only( $recipients, $send_to );
        natcasesort( $send_to );
        $mail['to'] = $send_to;

        // Log the email in database
        global $wpdb;

        // Maybe create table if it does not exist yet, e.g. upgraded from previous version of plugin, so, no activation methods are fired
        $table_name = $wpdb->prefix . 'asenha_email_delivery';
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

        if ( $wpdb->get_var( $query ) === $table_name ) {
            // Table already exists, do nothing.
        } else {
            $activation = new Activation;
            $activation->create_email_delivery_log_table();
        }

        // Time stamps
        $unixtime = time();
        if ( function_exists( 'wp_date' ) ) {
            $datetime_wp = wp_date( 'Y-m-d H:i:s', $unixtime );
        } else {
            $datetime_wp = date_i18n( 'Y-m-d H:i:s', $unixtime );
        }
        
        // Send to
        $send_to = is_array( $mail['to'] ) ? implode( ', ', $mail['to'] ) : $mail['to'];
        
        // Attachments - get an array of attachment filenames with file extensions
        // Reference: https://plugins.trac.wordpress.org/browser/mailarchiver/tags/4.0.0/includes/features/class-capture.php#L106
        $attachments_array = array();
        $mail_attachments = isset( $mail['attachments'] ) ? $mail['attachments'] : array();

        if ( ! is_array( $mail_attachments ) ) {
            $attachments = explode( "\n", str_replace( "\r\n", "\n", $mail_attachments ) );
        } else {
            $attachments = $mail_attachments;
        }

        // Extra data
        $options = get_option( ASENHA_SLUG_U, array() );
        $smtp_bcc_emails = isset( $options['smtp_bcc_emails'] ) ? $options['smtp_bcc_emails'] : '';
        $smtp_bcc_emails = str_replace( array( ', ', ',  ' ), ',', trim( $smtp_bcc_emails ) );
        
        $extra_array = array(
            'bcc'   => $smtp_bcc_emails,
        );

        $data = array(
            'status'            => 'successful',
            'error'             => 'None',
            'subject'           => sanitize_text_field( $mail['subject'] ),
            'message'           => wp_kses_post( $mail['message'] ),
            'send_to'           => sanitize_text_field( str_replace( array( '<', '>' ), array( '(', ')' ), $send_to ) ),
            'sender'            => '', // Will be updated via additional_logging__premium_only()
            'headers'           => '', // Will be updated via additional_logging__premium_only()
            'attachments'       => serialize( $attachments ),
            'backtrace'         => json_encode( $this->get_backtrace__premium_only( 'wp_mail' ) ),
            'processor'         => 'wp_mail',
            'sent_on'           => $datetime_wp,
            'sent_on_unixtime'  => $unixtime,
            'extra'             => serialize( $extra_array ),
        );

        $data_format = array(
            '%s', // string - status
            '%s', // string - error
            '%s', // string - subject 
            '%s', // string - message
            '%s', // string - send_to
            '%s', // string - sender
            '%s', // string - headers
            '%s', // string - attachments
            '%s', // string - backtrace
            '%s', // string - processor
            '%s', // string - sent_on
            '%d', // integer - sent_on_unixtime
            '%s', // string - extra
        );

        // Insert into the database
        // https://developer.wordpress.org/reference/classes/wpdb/insert/
        $result = $wpdb->insert(
            $table_name,
            $data,
            $data_format
        );
        
        // Store the entry ID in the log table
        $this->log_entry_id = $wpdb->insert_id;
        
        // We return the original email for sending
        return $original_mail;
    }
    
    /**
     * Process delivery error and log it to the database
     * 
     * @since 7.1.0
     */
    public function process_error__premium_only( $error ) {
        if ( $error instanceof \WP_Error ) {
            $error_message = $error->get_error_message();
            if ( '' === $error_message ) {
                $error_message = 'Unknown error.';
            }
            // $error_data = $error->get_error_data();
            
            // Make sure error message does not exceed 250 characters
            // [TODO] update DB table so the error column uses longtext like the message column
            if ( strlen( $error_message ) > 250 ) {
                $error_message = substr( $error_message, 0, 247 ) . '...';
            }

            // Add error status and message to the existing log entry for the mail
            // https://developer.wordpress.org/reference/classes/wpdb/update/
            global $wpdb;
            $result = $wpdb->update(
                $wpdb->prefix . 'asenha_email_delivery', // Log table name  
                array( 
                    'status'    => 'failed',
                    'error'     => sanitize_text_field( $error_message ),
                ), 
                array( 
                    'id' => $this->log_entry_id,
                ),
                array(
                    '%s', // string - status
                    '%s', // string - error
                ),
                array(
                    '%d', // integer - id        
                )
            );
        }
    }
    
    /**
     * Perform additional logging for data that are not properly logged via wp_mail hook
     * 
     * @since 7.1.0
     */
    public function additional_logging__premium_only( $phpmailer ) {
        // Set custom Message-ID header, e.g. // e.g. <message-3@subdomain.domain.com>>
        // The number here is the ID of the message in the email delivery log table
        $message_id = '<message-' . $this->log_entry_id . '@' . parse_url( get_site_url(), PHP_URL_HOST ) . '>';
        $phpmailer->MessageID = $message_id;

        // Get headers
        // Reference: https://plugins.trac.wordpress.org/browser/mailarchiver/tags/4.0.0/includes/listeners/class-corelistener.php#L216
        if ( method_exists( $phpmailer, 'createHeader' ) ) {
            $headers = $phpmailer->createHeader();

            if ( ! is_array( $headers ) ) {
                $headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
            }
            
            // Remove empty elements and get sender info (name and email)
            $sender = '';
            $headers_array = array();
            $sender = '';
            $reply_to = '';
            if ( ! empty( $headers ) ) {
                foreach( $headers as $header ) {
                    if ( '' !== $header ) {
                        $headers_array[] = $header;
                    }
                    if ( false !== strpos( $header, 'From:' ) ) {
                        $sender_array = explode( ': ', $header );
                        $sender = isset( $sender_array[1] ) ? $sender_array[1] : '';
                    }
                    if ( false !== strpos( $header, 'Reply-To:' ) ) {
                        $reply_to_array = explode( ': ', $header );
                        $reply_to = isset( $reply_to_array[1] ) ? $reply_to_array[1] : '';
                    }
                    if ( false !== strpos( $header, 'Content-Type:' ) ) {
                        $content_type_array = explode( ': ', $header );
                        $content_type_maybe_with_charset = isset( $content_type_array[1] ) ? $content_type_array[1] : ''; // e.g. text/html; charset=UTF-8
                        if ( false !== strpos( $content_type_maybe_with_charset, 'charset' ) ) {
                            $content_type_array = explode( '; ', $content_type_maybe_with_charset );
                            $content_type = isset( $content_type_array[0] ) ? $content_type_array[0] : '';
                        } else {
                            $content_type = trim( $content_type_maybe_with_charset );
                        }
                    }
                }
            }
            
            // Add sender and headers info to the existing log entry for the mail
            // https://developer.wordpress.org/reference/classes/wpdb/update/
            global $wpdb;
            $result = $wpdb->update(
                $wpdb->prefix . 'asenha_email_delivery', // Log table name  
                array( 
                    'sender'        => sanitize_text_field( str_replace( array( '<', '>' ), array( '(', ')' ), $sender ) ),
                    'reply_to'      => sanitize_text_field( str_replace( array( '<', '>' ), array( '(', ')' ), $reply_to ) ),
                    'content_type'  => sanitize_text_field( $content_type ),
                    'headers'       => serialize( $headers_array ),
                ), 
                array( 
                    'id' => $this->log_entry_id,
                ),
                array(
                    '%s', // string - sender
                    '%s', // string - reply_to
                    '%s', // string - content_type
                    '%s', // string - headers
                ),
                array(
                    '%d', // integer - id        
                )
            );
        }
    }

    /**
     * Recursively get all "to" email adresses
     *
     * @link https://plugins.trac.wordpress.org/browser/mailarchiver/tags/4.0.0/includes/listeners/class-corelistener.php#L116
     * @since    7.1.0
     */
    public function get_all_emails__premium_only( $a, &$result ) {
        if ( is_array( $a ) ) {
            foreach ( $a as $item ) {
                $this->get_all_emails__premium_only( $item, $result );
            }
        }
        if ( is_object( $a ) ) {
            foreach ( (array) $a as $item ) {
                $this->get_all_emails__premium_only( $item, $result );
            }
        }
        if ( is_string( $a ) && false !== strpos( $a, '@' ) ) {
            $result[] = trim( $a) ;
        }
    }
    
    /**
     * Get backtrace info for debugging
     * 
     * @link https://plugins.trac.wordpress.org/browser/wp-mail-catcher/tags/2.1.9/src/Loggers/LogHelper.php#L181
     * @since 7.1.0
     */
    public function get_backtrace__premium_only( $function_name = 'wp_mail' ) {
        $backtrace_segment = null;
        $backtrace = debug_backtrace();
        
        foreach ( $backtrace as $segment ) {
            if ( $function_name == $segment['function'] ) {
                $backtrace_segment = $segment;
                $backtrace_segment['file'] = '/' . str_replace( ABSPATH, '', $backtrace_segment['file'] );
                unset( $backtrace_segment['args'] ); // This contains email to, subject, message, header. Not needed.
            }
        }
        
        return $backtrace_segment; // array
    }

    /**
     * Add submenu item and admin page for the email delivery log
     * 
     * @since 7.1.0
     */
    public function add_email_log_submenu__premium_only() {
        add_submenu_page(
            'tools.php', // Parent page/menu
            __( 'Email Delivery Log', 'admin-site-enhancements' ), // Browser tab/window title
            __( 'Email Log', 'admin-site-enhancements' ), // Sube menu title
            'manage_options', // Minimal user capabililty
            'email-delivery-log', // Page slug. Shows up in URL.
            array( $this, 'add_email_log_page__premium_only' )
        );
    }
    
    /**
     * Output the email delivery log page
     * 
     * @since 7.1.0
     */
    public function add_email_log_page__premium_only() {
        $list_table = new Email_Log_Table();
        $list_table->prepare_items();
        $clear_log_nonce = wp_create_nonce( 'asenha-clear-log-' . get_current_user_id() );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <a href="admin.php?action=clear_log&amp;nonce=<?php echo wp_kses_post( $clear_log_nonce ); ?>" class="page-title-action clear-log-button"><?php echo __( 'Clear Log', 'admin-site-enhancements' ); ?></a>
            <hr class="wp-header-end">
            <?php $list_table->views(); ?>
            <form method="get">
                <?php $list_table->search_box( __( 'Search', 'admin-site-enhancements' ) , 'search'); ?>
                <?php $list_table->display(); ?>
            </form>
        </div>
        <div class="log-more-info">
            <div class="popup">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="close">×</span>
                        <h2 class="modal-title"><?php echo esc_html__( 'Email Delivery Log', 'admin-site-enhancements' ); ?></h2>
                        <div class="modal-message"></div>
                    </div>
                    <div class="modal-body">
                        <div class="loader"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#2271b1" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path fill="#2271b1" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"><animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg></div>
                        <div class="data"></div>
                    </div>
                    <div class="modal-footer">
                        <label id="resend-to-label" for="resend-to" style="display:none;"><?php echo esc_html__( 'Resend to', 'admin-site-enhancements' ); ?></label>
                        <input type="text" id="resend-to" name="resend-to" class="resend-to" placeholder="" value="" style="display:none;">
                        <div class="primary-action-wrapper">
                            <button type="button" class="button button-default footer-close primary-action"><?php echo esc_html__( 'Close', 'admin-site-enhancements' ); ?></button>
                            <div class="resend-loader" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#2271b1" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path fill="#2271b1" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"><animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg></div>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get the log entry data by the table row id
     * 
     * @since 7.1.0
     */
    public function get_ed_log_data__premium_only() {
        if ( current_user_can( 'manage_options' )
             && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'email-delivery-log' . get_current_user_id() ) 
        ) {
            global $wpdb;
            $table_name  = $wpdb->prefix.'asenha_email_delivery';
            $table_row_id = isset( $_POST['db_row_id'] ) ? intval( $_POST['db_row_id'] ) : 0;
            $db_row_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = '$table_row_id' LIMIT 1 ", ARRAY_A );
            
            $purpose = sanitize_text_field( $_POST['purpose'] );
            
            switch ( $purpose ) {
                case 'view';
                    $popup_html = $this->assemble_log_details_popup_html__premium_only( $db_row_data );
                    break;

                case 'resend';
                    $popup_html = $this->assemble_resend_email_popup_html__premium_only( $db_row_data );
                    break;
            }
            echo $popup_html;
            die();            
        } else {
            echo '';
            die();
        }
    }
    
    /**
     * Assemble email log entry's popup HTML from raw database data
     * 
     * @since 7.1.0
     */
    public function assemble_log_details_popup_html__premium_only( $db_row_data ) {
        $status = sanitize_text_field( $db_row_data['status'] );
        switch ( $status ) {
            case 'successful';
                $status_message = '<div class="delivery-status"><span class="status-indicator status-successful"></span>' . __( 'Successful', 'admin-site-enhancements' ) . '</div>';
                break;
            case 'failed';
                $status_message = '<div class="delivery-status"><span class="status-indicator status-failed"></span>' . __( 'Failed', 'admin-site-enhancements' ) . '</div>';
                break;
            case '';
                $status_message = '<div class="delivery-status"><span class="status-indicator status-unknown"></span>' . __( 'Unknown', 'admin-site-enhancements' ) . '</div>';
                break;
        }
        $error = sanitize_text_field( $db_row_data['error'] );
        $subject = sanitize_text_field( $db_row_data['subject'] );

        // Get message text
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $db_row_data['message'], $matches);
        $message_source = $db_row_data['message'];
        $message_text = isset( $matches[1] ) ? $matches[1] : $message_source;
        $message_text = preg_replace_callback('/\<[\w.]+@[\w.]+\>/', function( $arr ) {
            return esc_html( $arr[0] );
        }, $message_text);

        $content_type = $db_row_data['content_type'];
        if ( 'text/plain' == $content_type ) {
            $message_text = nl2br( $message_text );
        }
        if ( 'text/html' == $content_type ) {
            $message_text = str_replace( array( '\r\n','\n' ), PHP_EOL, $message_text );
        }
        $message = stripslashes( $message_text );

        $send_to = str_replace( array( '(', ')' ), array( '<', '>' ), $db_row_data['send_to'] );
        $sender = str_replace( array( '(', ')' ), array( '<', '>' ), $db_row_data['sender'] );
        $reply_to = str_replace( array( '(', ')' ), array( '<', '>' ), $db_row_data['reply_to'] );

        $headers_raw = unserialize( $db_row_data['headers'] );
        
        $attachment_urls = '';
        $attachments = unserialize( $db_row_data['attachments'] );
        if ( is_array( $attachments ) & ! empty( $attachments ) ) {
            foreach ( $attachments as $attachment ) {
                $attachment_url = str_replace( ABSPATH, get_site_url(), $attachment );
                $attachment_filename_parts = explode( '/', $attachment );
                $attachment_filename = end( $attachment_filename_parts );
                $attachment_urls .= '<a href="' . $attachment_url . '" target="_blank">' . $attachment_filename . '</a>'; 
            }
        }

        $processor = sanitize_text_field( $db_row_data['processor'] );
        $backtrace = json_decode( $db_row_data['backtrace'], true );
        $backtrace_file = '';
        $backtrace_line = '';
        $backtrace_function = '';
        if ( is_array( $backtrace ) && ! empty( $backtrace ) ) {
            $backtrace_file = isset( $backtrace['file'] ) ? $backtrace['file'] : '';
            $backtrace_line = isset( $backtrace['line'] ) ? $backtrace['line'] : '';
            $backtrace_function = isset( $backtrace['function'] ) ? $backtrace['function'] : '';
        }
        
        $sent_datetime = \DateTime::createFromFormat( 'Y-m-d H:i:s', $db_row_data['sent_on'] );
        $sent_on    = sprintf( '%1$s %2$s %3$s', 
                            $sent_datetime->format( 'F d, Y'),
                            __('at','postbox-email-logs'),
                            $sent_datetime->format('g:i a')
                        );

        $extra = unserialize( $db_row_data['extra'] );
        $bcc = str_replace( ',', ', ', $extra['bcc'] );

        ob_start();
        ?>
        <div id="log-more-info-tabs">
            <ul>
                <li><a href="#tab-info"><?php echo esc_html__('Info','admin-site-enhancements') ?></a></li>
                <li><a href="#tab-message"><?php echo esc_html__('Message','admin-site-enhancements') ?></a></li>
                <li><a href="#tab-source"><?php echo esc_html__('Source','admin-site-enhancements') ?></a></li>
            </ul>

            <div id="tab-info">
                <div class="email-info">
                    <div class="subject">
                        <div class="info-heading"><?php echo esc_html__( 'Subject', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo esc_html( $subject ); ?></div>
                    </div>
                    <div class="send-to">
                        <div class="info-heading"><?php echo esc_html__( 'To', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo esc_html( $send_to ); ?></div>
                    </div>
                    <div class="sender">
                        <div class="info-heading"><?php echo esc_html__( 'Sender', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo esc_html( $sender ); ?></div>
                    </div>
                    <div class="reply-to">
                        <div class="info-heading"><?php echo esc_html__( 'Reply to', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo esc_html( $reply_to ); ?></div>
                    </div>
                    <?php if ( ! empty( $bcc ) ) : ?>
                    <div class="bcc">
                        <div class="info-heading"><?php echo esc_html__( 'Bcc', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo esc_html( $bcc ); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="delivery-status">
                        <div class="info-heading"><?php echo esc_html__( 'Delivery status', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo wp_kses_post( $status_message ); ?></div>
                    </div>
                    <?php
                    if ( 'failed' == $status ) {
                        ?>
                        <div class="delivery-error">
                            <div class="info-heading"><?php echo esc_html__( 'Error', 'admin-site-enhancements' ); ?></div>
                            <div class="info-content"><?php echo wp_kses_post( $error ); ?></div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="headers">
                        <div class="info-heading"><?php echo esc_html__( 'Headers', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content">
                        <?php
                        $headers = '';
                        if ( is_array( $headers_raw ) && ! empty( $headers_raw ) ) {
                            foreach ( $headers_raw as $header ) {
                                echo esc_html( $header ) . '<br />';
                            }
                        }
                        ?>
                        </div>
                    </div>
                    <div class="attachments">
                        <div class="info-heading"><?php echo esc_html__( 'Attachments', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content">
                            <?php echo wp_kses_post( $attachment_urls ); ?>
                        </div>
                    </div>
                    <div class="debug">
                        <div class="info-heading"><?php echo esc_html__( 'Debug', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content">
                            <?php echo esc_html__( 'Triggered from:', 'admin-site-enhancements' ) . ' ' . wp_kses_post( $backtrace_file ) . '<br />'; ?>
                            <?php echo esc_html__( 'On line:', 'admin-site-enhancements' ) . ' ' . wp_kses_post( $backtrace_line ) . '<br />'; ?>
                            <?php echo esc_html__( 'Function:', 'admin-site-enhancements' ) . ' ' . wp_kses_post( $backtrace_function ) . '<br />'; ?>
                        </div>
                    </div>
                    <div class="delivery-processor">
                        <div class="info-heading"><?php echo esc_html__( 'Processor', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo wp_kses_post( $processor ); ?></div>
                    </div>
                </div>
            </div>
            <div id="tab-message">
                <?php echo wp_kses( $message, $this->allowed_html__premium_only() ); ?>
            </div>
            <div id="tab-source">
                <div class="source-wrap">
                    <?php echo $this->esc_message_html__premium_only( $message_source ); ?>
                </div>
            </div>
        </div>
        <?php
        // return ob_get_clean();
        $data = ob_get_clean();

        $response = array(
            'modal_message'     => '',
            'data'              => $data,
            'button_label'      => __( 'Close', 'admin-site-enhancements' ),
        );
        
        return json_encode( $response );
    }

    /**
     * Assemble email log entry's popup HTML from raw database data
     * 
     * @since 7.1.3
     */
    public function assemble_resend_email_popup_html__premium_only( $db_row_data ) {
        $subject = sanitize_text_field( $db_row_data['subject'] );

        // Get message text
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $db_row_data['message'], $matches);
        $message_source = $db_row_data['message'];
        $message_text = isset( $matches[1] ) ? $matches[1] : $message_source;
        $message_text = preg_replace_callback('/\<[\w.]+@[\w.]+\>/', function( $arr ) {
            return esc_html( $arr[0] );
        }, $message_text);

        $content_type = $db_row_data['content_type'];
        if ( 'text/plain' == $content_type ) {
            $message_text = nl2br( $message_text );
        }
        if ( 'text/html' == $content_type ) {
            $message_text = str_replace( array( '\r\n','\n' ), PHP_EOL, $message_text );
        }
        $message = stripslashes( $message_text );

        $send_to = str_replace( array( '(', ')' ), array( '<', '>' ), $db_row_data['send_to'] );

        ob_start();
        ?>
        <div id="log-more-info-tabs">
            <ul>
                <li><a href="#tab-info"><?php echo esc_html__('Info','admin-site-enhancements') ?></a></li>
                <li><a href="#tab-message"><?php echo esc_html__('Message','admin-site-enhancements') ?></a></li>
                <li><a href="#tab-source"><?php echo esc_html__('Source','admin-site-enhancements') ?></a></li>
            </ul>

            <div id="tab-info">
                <div class="email-info">
                    <div class="subject flex-column">
                        <div class="info-heading"><?php echo esc_html__( 'Subject', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo esc_html( $subject ); ?></div>
                    </div>
                    <div class="message-content flex-column">
                        <div class="info-heading"><?php echo esc_html__( 'Message', 'admin-site-enhancements' ); ?></div>
                        <div class="info-content"><?php echo wp_kses( $message, $this->allowed_html__premium_only() ); ?></div>
                    </div>
                </div>
            </div>
            <div id="tab-message">
            </div>
            <div id="tab-source">
            </div>
        </div>
        <?php
        $data = ob_get_clean();

        $response = array(
            'modal_message'     => __( 'You can resend the following message to the same destination or to a different one.', 'admin-site-enhancements' ),
            'db_row_id'         => $db_row_data['id'],
            'data'              => $data,
            'send_to'           => $send_to,
            'button_label'      => __( 'Resend Now', 'admin-site-enhancements' ),
        );
        
        return json_encode( $response );
    }
    
    /**
     * Escape HTML for email message
     * 
     * @link https://plugins.trac.wordpress.org/browser/postbox-email-logs/trunk/inc/utility.php#L151
     * @since 7.1.0
     */
    public function allowed_html__premium_only() {
        $allowed_tags = wp_kses_allowed_html('post');
        $allowed_tags['link'] = array(
            'rel'   => true,
            'href'  => true,
            'type'  => true,
            'media' => true,
        );
        return $allowed_tags;
    }

    /**
     * Special HTMl escaping for message source
     * 
     * @link https://plugins.trac.wordpress.org/browser/postbox-email-logs/trunk/inc/utility.php#L143
     * @since 7.1.0
     */
    public function esc_message_html__premium_only( $html ) {
        $html        = \esc_html( $html );
        $html        = str_replace( array('\r\n', '\n'), '<br/>', $html );
        $html        = nl2br( $html );
        $html        = stripslashes( $html );
        return $html;
    }

    /**
     * Trigger scheduling of email delivery log clean up event
     * 
     * @since 7.1.1
     */
    public function trigger_clear_or_schedule_log_clean_up_by_amount__premium_only( $option_name ) {
        if ( 'smtp_email_log_schedule_cleanup_by_amount' == $option_name ) {
            $this->clear_or_schedule_log_clean_up_by_amount__premium_only();        
        }
    }
    
    /**
     * Schedule email delivery log clean up event
     * 
     * @link https://plugins.trac.wordpress.org/browser/lana-email-logger/tags/1.1.0/lana-email-logger.php#L750
     * @since 7.1.1
     */
    public function clear_or_schedule_log_clean_up_by_amount__premium_only() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $smtp_email_log_schedule_cleanup_by_amount = isset( $options['smtp_email_log_schedule_cleanup_by_amount'] ) ? $options['smtp_email_log_schedule_cleanup_by_amount'] : false;
        
        // If scheduled clean up is not enabled, let's clear the schedule
        if ( ! $smtp_email_log_schedule_cleanup_by_amount ) {
            wp_clear_scheduled_hook( 'asenha_email_log_cleanup_by_amount' );
            return;            
        }
        
        // If there's no next scheduled clean up event, let's schedule one
        if ( ! wp_next_scheduled( 'asenha_email_log_cleanup_by_amount' ) ) {
            wp_schedule_event( time(), 'hourly', 'asenha_email_log_cleanup_by_amount' );
        }
    }
    
    /**
     * Perform clean up of email delivery log by the amount of entries to keep
     * 
     * @link https://plugins.trac.wordpress.org/browser/lana-email-logger/tags/1.1.0/lana-email-logger.php#L768
     * @since 7.1.1
     */
    public function perform_email_log_clean_up_by_amount__premium_only() {
        global $wpdb;
        
        $options = get_option( ASENHA_SLUG_U, array() );
        $smtp_email_log_schedule_cleanup_by_amount = isset( $options['smtp_email_log_schedule_cleanup_by_amount'] ) ? $options['smtp_email_log_schedule_cleanup_by_amount'] : false;
        $smtp_email_log_entries_amount_to_keep = isset( $options['smtp_email_log_entries_amount_to_keep'] ) ? $options['smtp_email_log_entries_amount_to_keep'] : 1000;
        
        // Bail if scheduled clean up by amount is not enabled
        if ( ! $smtp_email_log_schedule_cleanup_by_amount ) {
            return;
        }
                
        $table_name  = $wpdb->prefix.'asenha_email_delivery';
        
        $wpdb->query( "DELETE email_log_entries FROM " . $table_name . " 
                        AS email_log_entries JOIN ( SELECT id FROM " . $table_name . " ORDER BY id DESC LIMIT 1 OFFSET " . $smtp_email_log_entries_amount_to_keep . " ) 
                        AS email_log_entries_limit ON email_log_entries.id <= email_log_entries_limit.id;" );
    }
    
    /**
     * Resend email that failed on the first attempt and marked as such
     * 
     * @since 7.1.3
     */
    public function resend_email__premium_only() {
        if ( isset( $_REQUEST['action'] ) 
            && 'resend_email' == $_REQUEST['action'] 
            && isset( $_REQUEST['message_id'] ) 
            && ! empty( $_REQUEST['message_id'] )
            && is_numeric( $_REQUEST['message_id'] )
            && isset( $_REQUEST['resend_to'] ) 
            && ! empty( $_REQUEST['resend_to'] )
            && isset( $_REQUEST['nonce'] ) 
            && ! empty( $_REQUEST['nonce'] )
        ) {
            $db_row_id = intval( sanitize_text_field( $_REQUEST['message_id'] ) );
            $send_to = sanitize_text_field( $_REQUEST['resend_to'] );
            $nonce = sanitize_text_field( $_REQUEST['nonce'] );
            // $nonce_check = wp_verify_nonce( $nonce, 'email-delivery-log' . get_current_user_id() );

            if ( wp_verify_nonce( $nonce, 'email-delivery-log' . get_current_user_id() ) ) {
                global $wpdb;
                $table_name  = $wpdb->prefix.'asenha_email_delivery';
        
                $email = $wpdb->get_row(
                    'SELECT * FROM ' . $table_name . '
                    WHERE id = "' . $db_row_id . '"', ARRAY_A
                );

                // Set conte type to text/html. Default is text/plain.
                add_filter( 'wp_mail_content_type', array( $this, 'return_html_email_type__premium_only' ) );
                
                // $email_sent = true; // For testing
                $email_sent = wp_mail(
                    $send_to,
                    $email['subject'],
                    $email['message'],
                    '',
                    $email['attachments']
                );
                
                // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
                remove_filter( 'wp_mail_content_type', array( $this, 'return_html_email_type__premium_only' ) );
                
                if ( $email_sent ) {
                    $response = array(
                        'resend_status'     => 'successful',
                        'send_to'           => $send_to,
                        'notice_message'    => '<span class="dashicons dashicons-yes-alt"></span>' . __( 'Email was sent. This page will reload now...', 'admin-site-enhancements' ),
                    );
                    
                    echo json_encode( $response );                    
                } else {
                    $response = array(
                        'resend_status'     => 'failed',
                        'send_to'           => $send_to,
                        'notice_message'    => '<span class="dashicons dashicons-warning"></span>' . __( 'Something went wrong. Please check the latest log entry for details. This page will reload now...', 'admin-site-enhancements' ),
                    );
                    
                    echo json_encode( $response );
                }
            }
        }
    }

    /**
     * Return 'text/html' email content type for wp_mail_content_type filter hook
     * 
     * @since 7.1.3
     */
    public function return_html_email_type__premium_only() {
        return 'text/html';
    }

    /**
     * Delete individual email archive
     * 
     * @since 7.1.4
     */
    public function delete_email__premium_only() {
        if ( isset( $_REQUEST['action'] ) 
            && 'delete_email' == $_REQUEST['action'] 
            && isset( $_REQUEST['message-id'] ) 
            && ! empty( $_REQUEST['message-id'] )
            && is_numeric( $_REQUEST['message-id'] )
            && isset( $_REQUEST['nonce'] ) 
            && ! empty( $_REQUEST['nonce'] )
        ) {
            $db_row_id = intval( sanitize_text_field( $_REQUEST['message-id'] ) );
            $nonce = sanitize_text_field( $_REQUEST['nonce'] );

            if ( wp_verify_nonce( $nonce, 'asenha-delete-email-' . $db_row_id ) ) {
                global $wpdb;
                $table_name  = $wpdb->prefix.'asenha_email_delivery';

                // https://developer.wordpress.org/reference/classes/wpdb/delete/
                $result = $wpdb->delete(
                    $table_name,
                    array( 
                        'id' => $db_row_id,
                    ),
                    array(
                        '%d', // integer - id        
                    )
                );
                
                if ( 1 === $result ) {
                    wp_redirect( admin_url( 'tools.php?page=email-delivery-log&email-deletion=successful&message-id=' . $db_row_id ) );                    
                } else {
                    wp_redirect( admin_url( 'tools.php?page=email-delivery-log&email-deletion=failed&message-id=' . $db_row_id ) );
                }
                
            }
        }
    }
    
    /**
     * Show email deletion notice on deletion success
     * 
     * @since 7.1.4
     */
    public function maybe_show_email_deletion_notice__premium_only() {
        $screen = get_current_screen();
        
        if ( 'tools_page_email-delivery-log' === $screen->id ) {
            if ( isset( $_REQUEST['email-deletion'] ) 
                && in_array( $_REQUEST['email-deletion'], array( 'successful', 'failed' ) )
                && isset( $_REQUEST['message-id'] )
                && is_numeric( intval( $_REQUEST['message-id'] ) )
            ) {
                if ( 'successful' == sanitize_text_field( $_REQUEST['email-deletion'] ) ) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php printf(
                                    /* translators: %s: email message ID */
                                    __( 'Email delivery log entry with the ID %s was successfully deleted.', 'admin-site-enhancements' ),
                                    intval( $_REQUEST['message-id'] )
                                )
                            ?>
                        </p>
                    </div>
                    <?php
                } else if ( 'failed' == sanitize_text_field( $_REQUEST['email-deletion'] ) ) {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php printf(
                                    /* translators: %s: email message ID */
                                    __( 'Something went wrong. Unable to delete email delivery log entry with the ID %s.', 'admin-site-enhancements' ),
                                    intval( $_REQUEST['message-id'] )
                                )
                            ?>
                        </p>
                    </div>
                    <?php
                }
            }
        }
        
    }

    /**
     * Clear the email delivery log. This will delete all data.
     * 
     * @since 7.1.4
     */
    public function clear_log__premium_only() {
        if ( isset( $_REQUEST['action'] ) 
            && 'clear_log' == $_REQUEST['action'] 
            && isset( $_REQUEST['nonce'] ) 
            && ! empty( $_REQUEST['nonce'] )
        ) {
            $nonce = sanitize_text_field( $_REQUEST['nonce'] );
            if ( wp_verify_nonce( $nonce, 'asenha-clear-log-' . get_current_user_id() ) ) {
                global $wpdb;
                $table_name  = $wpdb->prefix.'asenha_email_delivery';
                
                // https://developer.wordpress.org/reference/classes/wpdb/query/
                $result = $wpdb->query( "TRUNCATE {$table_name}" );

                if ( $result ) {
                    wp_redirect( admin_url( 'tools.php?page=email-delivery-log&clear-log=successful' ) );                    
                } else {
                    wp_redirect( admin_url( 'tools.php?page=email-delivery-log&clear-log=failed' ) );
                }
            }
        }
    }

    /**
     * Show clear log notice on clearing success
     * 
     * @since 7.1.4
     */
    public function maybe_show_clear_log_notice__premium_only() {
        $screen = get_current_screen();
        
        if ( 'tools_page_email-delivery-log' === $screen->id ) {
            if ( isset( $_REQUEST['clear-log'] ) 
                && in_array( $_REQUEST['clear-log'], array( 'successful', 'failed' ) )
            ) {
                if ( 'successful' == sanitize_text_field( $_REQUEST['clear-log'] ) ) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php echo __( 'Log has been successfully cleared.', 'admin-site-enhancements' ); ?></p>
                    </div>
                    <?php
                } else if ( 'failed' == sanitize_text_field( $_REQUEST['clear-log'] ) ) {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php echo __( 'Something went wrong. Log was not cleared.', 'admin-site-enhancements' ); ?></p>
                    </div>
                    <?php
                }
            }
        }
        
    }
}