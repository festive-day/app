<?php

defined( 'ABSPATH' ) || die();

class Form_Builder_Shortcode {

    public function __construct() {
        add_shortcode( 'formbuilder', array( $this, 'get_form_shortcode' ) );
    }

    public static function get_form_shortcode( $atts ) {
        $shortcode_atts = shortcode_atts( array(
            'id' => '',
        ), $atts );
        
        // Enqueue pre-registered styles in /classes/form-builder-loader.php
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'jquery-timepicker' );
        wp_enqueue_style( 'formbuilder-file-uploader' );
        wp_enqueue_style( 'formbuilder-style' );

        $fonts_url = Form_Builder_Styles::fonts_url();

        if ( $fonts_url ) {
            wp_enqueue_style( 'formbuilder-fonts' );
        }
        
        // Enqueue pre-registered scripts in /classes/form-builder-loader.php
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'jquery-timepicker' );
        wp_enqueue_script( 'formbuilder-file-uploader' );
        wp_enqueue_script( 'moment' );
        wp_enqueue_script( 'frontend' );

        ob_start();
        Form_Builder_Preview::show_form( $shortcode_atts['id'] );
        return ob_get_clean();
    }

}

new Form_Builder_Shortcode();
