<?php

namespace ASENHA\Classes;

/**
 * Class for Redirect 404 module
 *
 * @since 6.9.5
 */
class Redirect_Fourofour {

    /**
     * Redirect 404 to homepage
     *
     * @since 1.7.0
     */
    public function redirect_404() {

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $options = get_option( ASENHA_SLUG_U );
            $custom_redirect_slug = isset( $options['redirect_404_to_slug'] ) ? $options['redirect_404_to_slug'] : '';            
        }

        if ( ! is_404() || is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {

            return;

        } elseif ( is_404() ) {

            // wp_safe_redirect( home_url(), 301 );
            
            if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                if ( empty( $custom_redirect_slug ) ) {
                    $redirect_url = site_url();
                } else {
                    $redirect_url = site_url( '/' . $custom_redirect_slug );
                }                
            } else {
                $redirect_url = site_url();
            }

            header( 'HTTP/1.1 301 Moved Permanently');
            header( 'Location: ' . sanitize_url( $redirect_url ) );
            exit();

        } else {}

    }

}