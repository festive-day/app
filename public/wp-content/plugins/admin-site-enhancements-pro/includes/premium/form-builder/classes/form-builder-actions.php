<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Actions {

    public $form;
    public $entry_id;
    public $form_settings;
    public $metas;
    public $location;

    public function __construct( $form, $entry_id, $form_settings, $metas, $location  ) {
        $this->form = $form;
        $this->entry_id = $entry_id;
        $this->form_settings = $form_settings;
        $this->metas = $metas;
        $this->location = $location;
    }
    
    public function run() {
    	$this->maybe_remove_db_entry();
    	$this->maybe_process_entry_data_for_webhooks();
    }
    
    public function maybe_remove_db_entry() {
        $enable_db_entries = isset( $this->form_settings['enable_db_entries'] ) ? $this->form_settings['enable_db_entries'] : 'on';

        if ( 'on' != $enable_db_entries ) {
            Form_Builder_Entry::destroy_entry( $this->entry_id );
        }
    }
    
    public function maybe_process_entry_data_for_webhooks() {
        $enable_webhooks = isset( $this->form_settings['enable_webhooks'] ) ? $this->form_settings['enable_webhooks'] : 'off';

        if ( 'on' == $enable_webhooks ) {
	    	// Let's unserialize entry data / metas for certain field types
	    	$processed_metas = array();
	    	
	    	foreach ( $this->metas as $field_id => $meta ) {
	    		$processed_metas[$field_id]['name'] = $meta['name'];
	    		$processed_metas[$field_id]['type'] = $meta['type'];
	    		
	    		switch ( $meta['type'] ) {
	    			case 'name':
	    			case 'address':
	    			case 'radio':
	    			case 'checkbox':
	    			case 'image_select':
	    			case 'likert_matrix_scale':
	    			case 'matrix_of_dropdowns':
	    			case 'matrix_of_variable_dropdowns_two':
	    			case 'matrix_of_variable_dropdowns_three':
	    			case 'matrix_of_variable_dropdowns_four':
	    			case 'matrix_of_variable_dropdowns_five':
	    				$processed_metas[$field_id]['value'] = maybe_unserialize( $meta['value'] );
	    				break;
	    				
	    			default:
	    				$processed_metas[$field_id]['value'] = $meta['value'];
	    		}
	    	}

	    	$payload = array(
	    		'form_id'			=> $this->form->id,
	    		'form_title'		=> $this->form->name,
	    		'form_url'			=> $this->location, // URL of where form is being placed on
	    		'form_data'			=> $processed_metas,
	    	);    	

	        // Use WordPress built-in WP_Http class to send the POST request
	        $webhook_urls = isset( $this->form_settings['webhook_urls'] ) ? $this->form_settings['webhook_urls'] : '';
	        if ( ! empty( $webhook_urls ) ) {
	        	$webhook_urls = explode( ',', $webhook_urls );

	        	foreach ( $webhook_urls as $webhook_url ) {
			        $response = wp_remote_post( $webhook_url, array(
			            'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
			            'body'        => json_encode( $payload ),
			            'method'      => 'POST',
			            'data_format' => 'body',
			        ));
	        	}
	        }
        }
    }

}