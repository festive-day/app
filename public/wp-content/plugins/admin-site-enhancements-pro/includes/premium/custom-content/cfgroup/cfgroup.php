<?php

/**
 * This Custom Field Group class/feature is forked from Custom Fields Suite v2.6.3 by Matt Gibbs
 * 
 * @link https://wordpress.org/plugins/custom-field-suite/
 * @since 5.2.0
 */
class Custom_Field_Group
{

    public $api;
    public $form;
    public $fields;
    public $field_group;
    public $group_ids;
    public $validators;
    private static $instance;


    function __construct() {

        // setup variables
        // define( 'CFG_VERSION', '2.6.3' );
        define( 'CFG_VERSION', ASENHA_VERSION );
        define( 'CFG_DIR', dirname( __FILE__ ) );
        define( 'CFG_URL', plugins_url( '', __FILE__ ) );

        // get the gears turning
        include( CFG_DIR . '/includes/init.php' );
    }


    /**
     * Singleton
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    /**
     * Public API methods
     */
    function get( $field_name = false, $post_id = false, $options = [] ) {
        return CFG()->api->get( $field_name, $post_id, $options );
    }


    function get_field_info( $field_name = false, $post_id = false ) {
        return CFG()->api->get_field_info( $field_name, $post_id );
    }


    function get_reverse_related( $post_id, $options = [] ) {
        return CFG()->api->get_reverse_related( $post_id, $options );
    }

    // Saving CFG form for posts and options pages
    function save( $field_data = [], $post_data = [], $options = [] ) {
        return CFG()->api->save_fields( $field_data, $post_data, $options );
    }

    // Saving CFG form for taxonomy terms
    function save_for_term( $field_data = [], $term_data = [], $options = [] ) {
        return CFG()->api->save_fields_for_term( $field_data, $term_data, $options );
    }

    function find_fields( $params = [] ) {
        return CFG()->api->find_input_fields( $params );
    }


    function form( $params = [] ) {
        ob_start();
        CFG()->form->render( $params );
        return ob_get_clean();
    }


    /**
     * Render a field's admin settings HTML
     */
    function field_html( $field ) {
        include( CFG_DIR . '/templates/field_html.php' );
    }


    /**
     * Trigger the field type "html" method
     */
    function create_field( $field ) {
        $defaults = [
            'type'          => 'text',
            'input_name'    => '',
            'input_class'   => '',
            'options'       => [],
            'value'         => '',
        ];

        $field = (object) array_merge( $defaults, (array) $field );
        CFG()->fields[ $field->type ]->html( $field );
    }
}

function CFG() {
    return Custom_Field_Group::instance();
}

$cfgroup = CFG();

include( CFG_DIR . '/cfgroup_functions.php' );