<?php

namespace ASENHA\Classes;

/**
 * Class for Code Snippets Manager module
 *
 * @since 6.9.5
 */
class Code_Snippets_Manager {

    /**
     * Record the post ID of the last edited PHP snippet. This will be used on the custom wp_die_handler callback
     * 
     * @since 5.8.0
     */
    public function record_last_edited_php_snippets__premium_only( $post_id, $data ) {
        
        if ( 'asenha_code_snippet' == get_post_type( $post_id ) ) {
            $snippet_options = get_post_meta( $post_id, 'options', true );
            if ( isset( $_POST['code_snippet_language'] ) && 'php' === $_POST['code_snippet_language'] 
                || ( isset( $snippet_options['language'] ) && 'php' === $snippet_options['language'] )
            ) {
                $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
                $options_extra['last_edited_csm_php_snippet'] = $post_id;
                update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );                
            }
        }
        
    }

    /**
     * Maybe show Safe Mode admin bar status icon
     *
     * @since 5.8.0
     */
    public function maybe_show_safe_mode_admin_bar_icon__premium_only() {

        $is_safe_mode_enabled = defined( 'CSM_SAFE_MODE' ) ? CSM_SAFE_MODE : false;
        
        if ( $is_safe_mode_enabled ) {
            add_action( 'wp_before_admin_bar_render', [ $this, 'add_safe_mode_admin_bar_item__premium_only' ] );
            add_action( 'admin_head', [ $this, 'add_safe_mode_admin_bar_item_styles__premium_only' ] );
            add_action( 'wp_head', [ $this, 'add_safe_mode_admin_bar_item_styles__premium_only' ] );            
        }

    }

    /**
     * Show Safe Mode WP Admin Bar item
     *
     * @since 5.8.0
     */
    public function add_safe_mode_admin_bar_item__premium_only() {
        global $wp_admin_bar;

        if ( is_user_logged_in() ) {
            if ( current_user_can( 'manage_options' ) ) {
                $wp_admin_bar->add_menu( array(
                    'id'    => 'safe_mode',
                    'parent'    => 'top-secondary',
                    'title' => '',
                    'href'  => '',
                    'meta'  => array(
                        'html'  => '<div id="disabling-csm-safe-mode" style="display:none;"><div class="disabling-csm-message"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path fill="currentColor" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"><animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg><span>Disabling safe mode and resuming PHP snippets execution...</span></div></div>',
                        'title' => 'Safe mode for ASE Code Snippets Manager is currently enabled for this site. Execution of all PHP snippets is currently stopped. Once you\'ve fixed the code in the snippet you last edited, click this icon to disable safe mode and re-enable PHP snippets execution.',
                    ),
                ) );
            }
        }

    }

    /**
     * Add JS and CSS for Safe Mode admin bar item
     *
     * @since 5.8.0
     */
    public function add_safe_mode_admin_bar_item_styles__premium_only() {
        
        if ( is_user_logged_in() ) {
            if ( current_user_can( 'manage_options' ) ) {
                ?>
                <script>
                jQuery(document).ready( function() {
                    // Reposition notice
                    jQuery('#disabling-csm-safe-mode').detach().insertAfter('#wpadminbar');
                    // Disable safe mode on clicking admin bar icon for safe mode status
                    jQuery("#wp-admin-bar-safe_mode > .ab-item, #disable-csm-safe-mode-link").click( function(e) {
                        e.preventDefault();
                        jQuery('#disabling-csm-safe-mode').show();
                        let searchParams = new URLSearchParams(window.location.search)
                        if ( searchParams.has('post') ) {
                            var code_id = searchParams.get('post');
                        } else {
                            var code_id = '';
                        }
                        // alert('This is post ' + code_id);
                        jQuery.ajax({
                            url: ajaxurl,
                            data: {
                                'action':'csm_disable_safe_mode',
                                'code_id':code_id
                            },
                            success: function(data){
                                var data = data.slice(0,-1); // remove strange trailing zero in string returned by AJAX call
                                var response = JSON.parse(data);

                                if ( response.success == true ) {
                                    // alert('Safe mode has been disabled');
                                    location.reload();
                                }
                            }
                        });
                    });
                });
                </script>
                <style>
                    #wpadminbar .quicklinks #wp-admin-bar-safe_mode .ab-empty-item {
                        padding: 0 6px;
                    }
                    #wp-admin-bar-safe_mode { 
                        background-color: #ff9a00 !important;
                        transition: .25s;
                    }
                    #wp-admin-bar-safe_mode > .ab-item { 
                        color: #fff !important;  
                    }
                    #wp-admin-bar-safe_mode > .ab-item:before { 
                        content: ""; 
                        display: block;
                        position: relative;
                        top: 2px;
                        z-index: 1;
                        background-image: url("<?php echo esc_html( ASENHA_URL ) . 'includes/premium/code-snippets-manager/assets/images/code.svg'; ?>") !important;
                        background-repeat: no-repeat;
                        background-position: center center;
                        background-size: 20px 20px;
                        width: 28px;
                        height: 28px;
                        padding: 0;
                        margin-right: 0px; 
                    }
                    #wp-admin-bar-safe_mode > .ab-item:after { 
                        content: ""; 
                        display: block;
                        position: relative;
                        top: 2px;
                        z-index: 2;
                        background-image: url("<?php echo esc_html( ASENHA_URL ) . 'includes/premium/code-snippets-manager/assets/images/stop-o.svg'; ?>") !important;
                        background-repeat: no-repeat;
                        background-position: center center;
                        background-size: 28px 28px;
                        width: 28px;
                        height: 28px;
                        padding: 0;
                        margin-right: 0px;
                        transition: .25s;
                    }
                    #wp-admin-bar-safe_mode:hover > .ab-item {
                        background-color: #ff9a00 !important;
                        cursor: pointer;
                    }
                    #wp-admin-bar-safe_mode:hover > .ab-item:after {
                        opacity: 0.25;
                    }
                    #disabling-csm-safe-mode {
                        position:absolute;
                        z-index: 999;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        left: 0;
                        background: rgba(255,255,255,0.9);
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }
                    .disabling-csm-message {
                        display: flex;
                        align-items: center;
                        margin-top: 80px;
                        margin-left: 80px;
                    }
                    .disabling-csm-message svg {
                        margin-right: 8px;
                    }
                    .disabling-csm-message span {
                        font-size: 1.25em;
                        font-weight: 600;
                    }
                </style>
                <?php

            }
        }

    }
    
    /** 
     * Return the custom wp_die_handler callback
     * 
     * @since 5.8.0
     */
    public function custom_wp_die_handler__premium_only() {
        return '_custom_wp_die_handler__premium_only';
    }
    
}