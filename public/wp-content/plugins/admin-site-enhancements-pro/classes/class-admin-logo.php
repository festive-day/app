<?php

namespace ASENHA\Classes;

/**
 * Class for Admin Logo module
 *
 * @since 7.2.0
 */
class Admin_Logo {

    /**
     * Add admin logo to the admin bar
     * 
     * @since 7.2.0
     */
    public function add_admin_bar_logo( $wp_admin_bar ) {
        $common_methods = new Common_Methods;
        $logo_image = $common_methods->get_image_url( 'admin_logo_image' );

        $options = get_option( ASENHA_SLUG_U, array() );
        $admin_bar_logo_on_frontend_link_to_dashboard = ( isset( $options['admin_logo_link_frontend'] ) ) ? $options['admin_logo_link_frontend'] : false;

        if ( $admin_bar_logo_on_frontend_link_to_dashboard ) {
            if ( ! is_admin() ) {
                $logo_link = get_admin_url();            
            } else {
                $logo_link = get_site_url();            
            }
        } else {
            $logo_link = get_site_url();
        }

        if ( ! empty( $logo_image ) ) {
            $args = array(
                'id' => 'asenha-admin-bar-logo',
                'href' => $logo_link,
                'title' => sprintf(
                    '<img src="%s" alt="%s" />', 
                    $logo_image, 
                    __( 'Admin Logo', 'admin-site-enhancements' )
                ),
                'meta' => array(
                    'class'     => 'asenha-admin-logo', 
                    'title'     => __( 'Visit the homepage', 'admin-site-enhancements' ), 
                    // 'target'    => '_blank',
                )
            );

            $wp_admin_bar->add_node( $args );            
        }
    }
    
    /**
     * Add inline styles for admin bar logo
     * 
     * @since 7.2.0
     */
    public function add_admin_bar_logo_css() {
        ?>
        <style type="text/css" id="admin-menu-logo-css">
            .asenha-admin-logo .ab-item, 
            .asenha-admin-logo a {
                line-height: 28px !important;
                display: flex;
                align-items: center;
            }

            .asenha-admin-logo img {
                vertical-align: middle;
                height: 20px !important;
            }
            
            @media screen and (max-width: 782px) {
                #wpadminbar li#wp-admin-bar-asenha-admin-bar-logo {
                    display: block;
                }
                
                #wpadminbar li#wp-admin-bar-asenha-admin-bar-logo a {
                    display: flex;
                    margin-left: 8px;
                }
            }
        </style>
        <?php
    }
    
    /**
     * Add admin logo to the top of the admin menu
     * 
     * @since 7.2.0
     */
    public function add_admin_menu_logo() {
        $common_methods = new Common_Methods;
        $logo_image = $common_methods->get_image_url( 'admin_logo_image' );

        $options = get_option( ASENHA_SLUG_U, array() );
        if ( array_key_exists( 'wider_admin_menu', $options ) && $options['wider_admin_menu'] ) {
            $admin_menu_width = ( isset( $options['admin_menu_width'] ) ) ? $options['admin_menu_width'] : '';        
        } else {
            $admin_menu_width = '160px;';
        }
        ?>
        <script type="text/javascript" id="admin-menu-logo-script">
            /* <![CDATA[ */
            jQuery(document).ready(function() {
                jQuery("#adminmenu").before('<div id="admin_menu_logo"><img src="<?php echo wp_kses_post( $logo_image ); ?>" /></div>');
                var url = "<?php echo get_site_url(); ?>";
                jQuery("#admin_menu_logo").attr('onclick','window.open(\"' + url +'\");');
                jQuery("#admin_menu_logo").attr('title','<?php echo __( "Visit the homepage', 'admin-site-enhancements"); ?>');
            });
            /* ]]> */
        </script>
        <style type="text/css" id="admin-menu-logo-css">
            #admin_menu_logo {
                min-height: 28px;
                cursor: pointer;
                transition: .25s;
            }
            #admin_menu_logo:hover {
                background: #2c3338;
            }
            #admin_menu_logo img {
                box-sizing: border-box;
                width: <?php echo wp_kses_post( $admin_menu_width ); ?>;
                padding: 16px 16px 16px 10px;
            }
            .folded #admin_menu_logo {
                display: none;
            }
            @media screen and ( min-width: 783px ) {
                #adminmenu {
                    margin-top: 0;
                }                
            }
            @media screen and ( max-width: 960px ) {
                #admin_menu_logo {
                    display: none;
                }
            }
        </style>
        <?php        
    }

}