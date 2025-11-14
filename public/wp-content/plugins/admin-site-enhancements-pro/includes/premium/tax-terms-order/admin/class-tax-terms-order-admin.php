<?php

/**
 * @package   Tax_Terms_Order
 * @subpackage Tax_Terms_Order/admin

 */
class Tax_Terms_Order_Admin {

	private $plugin_name;
	private $version;
	
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action( 'admin_menu', array( $this, 'add_terms_order_menu' ) );
		add_filter( 'terms_clauses', array( $this, 'maybe_apply_custom_order' ), 10, 3);
		add_filter( 'get_terms_orderby', array( $this, 'get_terms_orderby' ), 1, 2);
		add_action( 'wp_ajax_update-taxonomy-order', array( $this, 'save_terms_order' ) );
	}

	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, TTO_ASSETS_URL . 'css/tax-terms-order-admin.css', array(), $this->version, 'all' );
	
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 
			'jquery' 
		);  
        wp_enqueue_script( 
        	'jquery-ui-sortable', 
        	'', 
        	array( 'jquery' ) 
        );
		wp_enqueue_script( 
			$this->plugin_name, 
			TTO_ASSETS_URL . 'js/tax-terms-order-admin.js', 
			array( 'jquery-ui-sortable' ), 
			$this->version, 
			false 
		);
	}
		
	public function add_terms_order_menu() {

		$options = get_option( ASENHA_SLUG_U, array() );
		$terms_order_for = $options['terms_order_for'];
		$terms_order_enabled_post_types = array();

		foreach ( $options['terms_order_for'] as $post_type_slug => $is_custom_order_enabled ) {
			if ( $is_custom_order_enabled ) {
				$terms_order_enabled_post_types[] = $post_type_slug;
			}
		}

        foreach( $terms_order_enabled_post_types as $post_type ) {

			$post_type_taxonomies = get_object_taxonomies( $post_type );

			// Get the hierarchical taxonomies for the post type

			foreach ( $post_type_taxonomies as $key => $taxonomy_name ) {
                $taxonomy_info = get_taxonomy( $taxonomy_name );

                if ( empty( $taxonomy_info->hierarchical ) ||  $taxonomy_info->hierarchical !== TRUE ) {
                    unset( $post_type_taxonomies[$key] );
                }
            }
            
            // Add terms ordering submenu only if there's at least 1 hierarchical taxonomy for the post type

            if ( count( $post_type_taxonomies ) > 0 ) {

	            if ( 'post' == $post_type ) {
					
	                add_submenu_page(
	                	'edit.php', 
	                	__( 'Terms Order', 'admin-site-enhancements' ), 
	                	__( 'Terms Order', 'admin-site-enhancements' ), 
	                	'manage_categories', 
	                	$post_type . '-terms-order', 
	                	array( $this, 'terms_ordering_page' ) 
	                );
				
				} elseif ( 'attachment' == $post_type ) {
					
	                add_submenu_page(
	                	'upload.php', 
	                	__( 'Terms Order', 'admin-site-enhancements' ), 
	                	__( 'Terms Order', 'admin-site-enhancements' ), 
	                	'manage_categories', 
	                	$post_type . '-terms-order',
	                	array($this, 'terms_ordering_page') 
	                );   
				
				} else {
					
	                add_submenu_page(
	                	'edit.php?post_type='.$post_type, 
	                	__( 'Terms Order', 'admin-site-enhancements' ), 
	                	__( 'Terms Order', 'admin-site-enhancements' ), 
	                	'manage_categories', 
	                	$post_type . '-terms-order', 
	                	array( $this, 'terms_ordering_page' ) 
	                );
				
				}

			}
		}
	}

	public function terms_ordering_page() {
        global $wpdb, $wp_locale;
        
        // Get post type data

        $post_type 	= isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : '';

        if ( empty( $post_type ) ) {
            $screen = get_current_screen();
                
            if ( isset( $screen->post_type ) && ! empty( $screen->post_type ) ) {
                $post_type = $screen->post_type;
			} else {
                switch ( $screen->parent_file ) {
                    case 'upload.php':
                        $post_type = 'attachment';
                    	break;            
					default:
						$post_type = 'post';   
				}
			}       
		} 
                                        
        $post_type_data = get_post_type_object( $post_type );
        $post_type_label = $post_type_data->label;
        
        // Get taxonomy data

        $taxonomy 	= isset( $_GET['taxonomy'] ) ? sanitize_key( $_GET['taxonomy'] ) : '';
        
        if ( ! taxonomy_exists( $taxonomy ) ) {
            $taxonomy = '';
		}
		
		// Pass on class instance when assembling the terms ordering page

		$instance = $this;

		include( 'templates/terms-ordering-page.php' ); 
            
    }

	public function tto_terms_list( $taxonomy ) {
        $args = array(
            'orderby' 		=> 'term_order',
            'depth' 		=> 0,
            'child_of' 		=> 0,
            'hide_empty'	=> 0,
        );

		$taxonomy_terms = get_terms( $taxonomy, $args );

        $output = '';

        if ( count( $taxonomy_terms ) > 0 ){
            $output = $this->tax_terms_order_list_hierarchy( $taxonomy_terms, $args['depth'], $args );    
        }

        echo wp_kses_post( $output );                
    }
        
    public function tax_terms_order_list_hierarchy( $taxonomy_terms, $depth, $r ) {
        $walker = new Tax_Terms_Order_Walker; 
        $args = array( $taxonomy_terms, $depth, $r );
        return call_user_func_array( array( &$walker, 'walk' ), $args );
    }
		
	public function maybe_apply_custom_order( $clauses, $taxonomies, $args ) {		

		$options = get_option( ASENHA_SLUG_U, array() );

		// Get the post types for which taxonomy terms are custom ordered

		$terms_order_for = $options['terms_order_for'];
		$terms_order_enabled_post_types = array();

		foreach ( $options['terms_order_for'] as $post_type_slug => $is_custom_order_enabled ) {
			if ( $is_custom_order_enabled ) {
				$terms_order_enabled_post_types[] = $post_type_slug;
			}
		}

        foreach ( $terms_order_enabled_post_types as $terms_order_post_type ) {

			$post_type_taxonomies = get_object_taxonomies( $terms_order_post_type );

			// Get the hierarchical taxonomies for the post type

			foreach ( $post_type_taxonomies as $key => $taxonomy_name ) {
                $taxonomy_info = get_taxonomy( $taxonomy_name );

                if ( empty( $taxonomy_info->hierarchical ) ||  $taxonomy_info->hierarchical !== TRUE ) {
                    unset( $post_type_taxonomies[$key] );
                }
            }

			// Check if the current taxonomy are part of taxonomies for which custom order is enabled
	        $taxonomy_matches = array_intersect( $taxonomies, $post_type_taxonomies );
            
            foreach ( $taxonomies as $taxonomy ) {
            	$taxonomy_info = get_taxonomy( $taxonomy );
            	if ( is_object( $taxonomy_info ) ) {
	            	$taxonomy_is_for = $taxonomy_info->object_type;
	            	foreach ( $taxonomy_is_for as $taxonomy_post_type ) {

	            		// Only enable custom order logic if the post type's taxonomy is enabled for custom order of terms
	            		if ( $taxonomy_post_type == $terms_order_post_type && is_array( $taxonomy_matches ) && count( $taxonomy_matches ) > 0 ) {
	            			
							if ( apply_filters( 'tto/get_terms_orderby/ignore', FALSE, $clauses['orderby'], $args ) ) {
					            return $clauses;
					        }

					        // Admin terms order

					        if ( is_admin() ) {
					            if ( isset( $_GET['orderby'] ) && $_GET['orderby'] != 'term_order' ) {
					                return $clauses;
								}
					            
					            if ( ( ! isset( $args['ignore_term_order'] ) || ( isset( $args['ignore_term_order'] ) && $args['ignore_term_order'] !== TRUE ) ) ) {
					                $clauses['orderby'] = 'ORDER BY t.term_order';
								}
					                
					            return $clauses;
							}
							
							// Frontend terms order

							$terms_order_frontend = isset( $options['terms_order_frontend'] ) ? $options['terms_order_frontend'] : false;
					        
					    	if ( $terms_order_frontend ) {
					            if ( ( ! isset( $args['ignore_term_order'] ) || ( isset( $args['ignore_term_order'] ) && $args['ignore_term_order'] !== TRUE ) ) ) {
						            $clauses['orderby'] = 'ORDER BY t.term_order';        		
								}
					    	}

	            		}
	            	}
            	}
            }
            
        }
        
        return $clauses; 
	}
		
	public function get_terms_orderby( $orderby, $args ) {
            if ( apply_filters( 'tto/get_terms_orderby/ignore', FALSE, $orderby, $args ) ) {
                return $orderby;
			}
                
            if ( isset( $args['orderby'] ) && $args['orderby'] == "term_order" && $orderby != "term_order" ) {
                return "t.term_order";
            }
                
            return $orderby;
    }
		
	public function save_terms_order() {
            global $wpdb;
            
            if  ( ! wp_verify_nonce( $_POST['nonce'], 'update-taxonomy-order' ) ) {
                die();
			}
            
            $data = stripslashes( sanitize_text_field( $_POST['order'] ) );
            $unserialised_data = json_decode( $data, TRUE );
                    
            if ( is_array( $unserialised_data ) ) {
				foreach ( $unserialised_data as $key => $values ) {
						
					$items = explode("&", $values);
					unset($item);
					foreach ( $items as $item_key => $item_ ) {
						$items[$item_key] = trim( str_replace( "item[]=", "", $item_ ) );
					}
						
					if ( is_array( $items ) && count( $items ) > 0 ) {
						foreach ( $items as $item_key => $term_id ) {
							$wpdb->update( $wpdb->terms, array( 'term_order' => ($item_key + 1) ), array( 'term_id' => $term_id ) );
						}
						clean_term_cache( $items );
					} 
				}
			}
                
            do_action('tto/update-order');
             
            die();
    }

}
