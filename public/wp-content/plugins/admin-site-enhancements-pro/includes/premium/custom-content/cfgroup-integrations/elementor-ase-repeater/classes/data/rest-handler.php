<?php
namespace ElementorAseRepeater\Data;

use ElementorAseRepeater\Configurator;
use ElementorAseRepeater\Controls\DynamicTagControls;
use ElementorAseRepeater\Controls\RepeaterFieldSelector;
use WP_REST_Request;
use WP_Error;

class RestHandler {
    private $configurator;
    private $controls;
    private $settings;

    public function __construct() {
        $this->configurator = Configurator::instance();
        $this->controls = DynamicTagControls::instance();
        $this->settings = RepeaterFieldSelector::instance();
    }

    

    public function register_rest_routes() {
   
        register_rest_route( 'elementor-ase-repeater/v1', '/update-dynamic-tag-controls', [
            'methods' => 'POST',
            'callback' => [ $this, 'get_updated_dynamic_tag_controls' ],
            'permission_callback' => [ $this, 'permission_callback' ],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    },
                ],
                'selected_repeater' => [
                    'required' => true,
                    'validate_callback' => function( $param ) {
                        return is_string( $param ) || is_array( $param );
                    },
                ],
                'tags' => [
                    'required' => true,
                    'validate_callback' => function( $param ) {
                        return is_string( $param ) || is_array( $param );
                    },
                ],
            ],
        ]);

        // route for getting saved repeater field
        register_rest_route('elementor-ase-repeater/v1', '/get-saved-repeater-field', [
            'methods' => 'GET',
            'callback' => [ $this, 'handle_get_saved_repeater_field_request' ],
            'permission_callback' => [ $this, 'permission_callback' ],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    },
                ],
            ],
        ]);

        // New route for handling ASE Repeater tag changes
        register_rest_route( 'elementor-ase-repeater/v1', '/handle-ase-repeater-change', [
            'methods' => 'POST',
            'callback' => [ $this, 'handle_ase_repeater_change' ],
            'permission_callback' => [ $this, 'permission_callback' ],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    },
                ],
                'element_id' => [
                    'required' => false,
                    'validate_callback' => function( $param ) {
                        return is_string( $param ) || is_null( $param );
                    },
                ],
                'is_removed' => [
                    'required' => true,
                    'validate_callback' => function( $param ) {
                        return is_bool( $param ) || ( is_string( $param ) && in_array( strtolower( $param ), [ 'true', 'false', '0', '1' ] ) );
                    },
                ],
            ],
        ]);
        
    }

    public function permission_callback( $request ) {
        return current_user_can( 'edit_posts' );
    }

    public function get_updated_dynamic_tag_controls( $request = null ) {
        try {
            // Handle both REST and AJAX requests
            if ($request instanceof \WP_REST_Request) {
                $data = $request->get_params();
            } else {
                // AJAX request
                check_ajax_referer( 'elementor-editing', 'nonce' );
                $data = array(
                    'post_id' => isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0,
                    'selected_repeater' => isset( $_POST['selected_repeater'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_repeater'] ) ) : '',
                    'tags' => isset( $_POST['tags'] ) ? sanitize_text_field( wp_unslash( $_POST['tags'] ) ) : ''
                );
            }
        
            $post_id = absint( $data['post_id'] );
            $selected_repeater = sanitize_text_field( $data['selected_repeater'] );
            $tags = json_decode( stripslashes( sanitize_text_field( $data['tags'] ) ), true );
            
            if ( ! $post_id || ! $selected_repeater || ! is_array( $tags ) ) {
                return new WP_Error( 'invalid_data', 'Invalid input data provided', array( 'status' => 400 ) );
            }
        
            $selected_repeater = apply_filters( 'asenha_pre_update_controls', $selected_repeater, $post_id, $this->configurator->is_edit_mode( $post_id ) );
    
            $updated_tags = $this->configurator->get_updated_dynamic_tag_controls( $post_id, $selected_repeater, $tags, $request );
    
            $response_data = [
                'tags' => $updated_tags,
                'selected_repeater' => $selected_repeater
            ];
    
            return $response_data;
        } catch ( Exception $e ) {
      
            return new WP_Error( 'rest_error', $e->getMessage(), array( 'status' => 500 ) );
        }
    }

    public function handle_get_saved_repeater_field_request( $request ) {
        $post_id = absint( $request->get_param( 'post_id' ) );
        
        if ( ! $post_id ) {
            return new WP_Error( 'invalid_post_id', 'Invalid post ID provided', array( 'status' => 400 ) );
        }
        
        $repeater_field = $this->settings->get_saved_repeater_field( $post_id );
        
        $field_name = '';

        if ( $repeater_field ) {
            $repeater_field = sanitize_text_field( $repeater_field );
            $field_info = find_cf( array( 'field_name' => $repeater_field ) );
            $field_name = $field_info ? sanitize_text_field( $field_info[$repeater_field]['label'] ) : '';
        }
                
        return [
            'repeater_field' => sanitize_text_field( $repeater_field ),
            'repeater_field_name' => $field_name
        ];
    }

    public function handle_ase_repeater_change( $request ) {
        $post_id = absint( $request->get_param( 'post_id' ) );
        $element_id = sanitize_key( $request->get_param( 'element_id' ) );
        $is_removed = filter_var( $request->get_param( 'is_removed' ), FILTER_VALIDATE_BOOLEAN );
        
        if ( ! $post_id) {
            return new WP_Error( 'invalid_data', __( 'Invalid input data provided', 'admin-site-enhancements' ), array( 'status' => 400 ) );
        }
    
        $has_ase_repeater_tag = ! $is_removed;
    
        if ( $element_id ) {
            $meta_key = sanitize_key( "widget_has_ase_repeater_tag_{$element_id}" );
            update_post_meta( $post_id, $meta_key, $has_ase_repeater_tag );
        }
        
        return [
            'success' => true,
            'message' => 'ASE Repeater change handled successfully',
            'has_ase_repeater_tag' => $has_ase_repeater_tag
        ];
    }
    
}