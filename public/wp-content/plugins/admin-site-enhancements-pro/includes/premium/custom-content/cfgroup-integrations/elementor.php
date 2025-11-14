<?php
namespace ASENHA\Integrations\Elementor;

// use ElementorPro\Plugin;
// use ElementorPro\Modules\LoopBuilder\Module as LoopBuilderModule;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ase_Elementor_Integration {

	/**
	 * Class constructor
	 * 
	 * @since 6.8.3
	 */
	public function __construct() {
		add_action( 'elementor/dynamic_tags/register', [ $this, 'register_request_variables_dynamic_tag_group' ] );
		add_action( 'elementor/dynamic_tags/register', [ $this, 'register_ase_dynamic_tags' ] );
	}

	/**
	 * Register Request Variables Dynamic Tag Group.
	 *
	 * Register new dynamic tag group for Request Variables.
	 *
	 * @since 6.8.3
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Elementor dynamic tags manager.
	 * @return void
	 */
	public function register_request_variables_dynamic_tag_group( $dynamic_tags_manager ) {
		$dynamic_tags_manager->register_group(
			'ase',
			[
				'title' => 'ASE'
			]
		);
	}

	/**
	 * Register the various field types as dynamic data source
	 *
	 * @since 6.8.3
	 */
	public function register_ase_dynamic_tags( $dynamic_tags_manager ) {
		// $document = Plugin::elementor()->documents->get_current(); // the Elementor template
		// $main_id = $document->main_id; // Template post ID
		// $main_post = $document->get_main_post(); // Template WP_Post_Object
		// $location = $document->get_location(); // e.g. 'single'
		// $template_type = $document->get_template_type(); e.g. 'wp-page' or 'single-post'
		// $elementor_conditions = $document->get_meta( '_elementor_conditions' ); e.g. array( 0 => 'include/singular/acf-movie' )
		// $elementor_page_settings = $document->get_meta( '_elementor_page_settings' ); e.g. array( 'preview_type' => 'single/acf-movie', 'preview_id' => '6801' )

		// Register dynamic data that can return text (including HTML)
		require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor-dynamic-tags/ase-text.php';
		$dynamic_tags_manager->register( new \ASENHA\Integrations\Elementor\Ase_Text );

		// Register dynamic data that can return URL
		require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor-dynamic-tags/ase-url.php';
		$dynamic_tags_manager->register( new \ASENHA\Integrations\Elementor\Ase_Url );

		// Register dynamic data that can return image data
		require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor-dynamic-tags/ase-image.php';
		$dynamic_tags_manager->register( new \ASENHA\Integrations\Elementor\Ase_Image );

		// Register dynamic data that can return gallery data
		require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor-dynamic-tags/ase-gallery.php';
		$dynamic_tags_manager->register( new \ASENHA\Integrations\Elementor\Ase_Gallery );

		// Register dynamic data that can return file data
		require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor-dynamic-tags/ase-file.php';
		$dynamic_tags_manager->register( new \ASENHA\Integrations\Elementor\Ase_File );

		// Register dynamic data that can return number data
		require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor-dynamic-tags/ase-number.php';
		$dynamic_tags_manager->register( new \ASENHA\Integrations\Elementor\Ase_Number );

		// Register dynamic data that can return color data
		require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor-dynamic-tags/ase-color.php';
		$dynamic_tags_manager->register( new \ASENHA\Integrations\Elementor\Ase_Color );
	}

	/**
	 * Get key value pairs for the select element/control used so select which custom field to use as dynamic data source
	 * @param  string $return_type 	the return type of the custom fields
	 * @return array              	array of custom field name - custom field label
	 */
	public function get_control_options( $return_type = 'text', $applicable_field_types = array() ) {
		
		// Get all published custom field groups
		$cf_groups = array();
		$cf_group_fields = array();

		// We don't use WP_Query and wp_reset_postdata() at the end to prevent messing with the main query
		$args = array(
		    'post_type' => 'asenha_cfgroup',
		    'post_status' => 'publish',
		    'numberposts' => -1,
		);

	    $cfgroups = get_posts( $args );

		if ( ! empty( $cfgroups ) ) {
			foreach ( $cfgroups as $cfgroup ) {
				// setup_postdata( $cfgroup ); // Not currently needed to access ID and title
				
		        $cf_group_id = $cfgroup->ID;
		        $fields = CFG()->find_fields( array( 'group_id' => $cf_group_id ) );
		        $cf_group_fields = array();

		        if ( is_array( $fields ) && ! empty( $fields ) ) {
			        foreach ( $fields as $field ) {
			        	// We exclude repeater fields and their sub-fields
			        	if ( 'repeater'	!= $field['type'] && 0 === $field['parent_id'] && 'tab' != $field['type'] ) {
			        		if ( in_array( $field['type'], $applicable_field_types ) ) {
			        			$sub_type = 'none';
			        			switch ( $field['type'] ) {
			        				case 'text':
				        				$sub_type = isset( $field['options']['text_type'] ) ? $field['options']['text_type'] : 'any';
			        					break;
			        				case 'select':
				        				$sub_type = ( 1 == $field['options']['multiple'] ) ? 'multiple' : 'single';
			        					break;
			        				case 'file':
				        				$sub_type = ( 'file' == $field['options']['file_type'] ) ? 'any' : $field['options']['file_type'];
			        					break;
			        			}

			        			$field_key = $field['name'] . '__' . $field['type'] . '__' . $sub_type;
			        			
			        			$should_add_field_to_cf_groups = false;
			        			
			        			// Only include applicable field types and sub-types for each return types
			        			switch ( $return_type ) {
			        				case 'text':
					        			$should_add_field_to_cf_groups = true;
			        					break;

			        				case 'url':
			        					if ( 'text' != $field['type'] ) {
						        			$should_add_field_to_cf_groups = true;
			        					} elseif ( 'text' == $field['type'] 
			        						&& isset( $field['options']['text_type'] ) 
			        						&& 'url' == $field['options']['text_type'] 
			        					) {
						        			$should_add_field_to_cf_groups = true;
			        					} else {}
			        					break;

			        				case 'image':
			        					if ( 'file' == $field['type'] 
			        						&& isset( $field['options']['file_type'] ) 
			        						&& 'image' == $field['options']['file_type'] 
			        					) {
						        			$should_add_field_to_cf_groups = true;
			        					}
			        					break;

			        				case 'gallery':
					        			$should_add_field_to_cf_groups = true;
			        					break;

			        				case 'file':
			        					if ( 'file' == $field['type'] ) {
						        			$should_add_field_to_cf_groups = true;
			        					}
			        					break;

			        				case 'number':
			        					if ( 'text' == $field['type'] 
			        						&& isset( $field['options']['text_type'] ) 
			        						&& 'number' == $field['options']['text_type'] 
			        					) {
						        			$should_add_field_to_cf_groups = true;
			        					} elseif ( 'number' == $field['type'] ) {
						        			$should_add_field_to_cf_groups = true;
			        					}
			        					break;

			        				case 'color':
			        					if ( 'color' == $field['type'] ) {
						        			$should_add_field_to_cf_groups = true;
			        					}
			        					break;
			        			}
			        			
			        			if ( $should_add_field_to_cf_groups ) {
						        	$cf_group_fields[$field_key] = $field['label'];
			        			}
			        		}
				        }
			        }
		        }

		        $post_title = $cfgroup->post_title;
		        $cf_groups[] = array(
		        	'label'		=> $post_title,
		        	'options'	=> $cf_group_fields,
		        );
		    }
		    // wp_reset_postdata(); // this breaks the main query, so, we comment out
		}
		// vi( $cf_groups );
		
		return $cf_groups;
	}

}

$ase_elementor_integration = new Ase_Elementor_Integration();