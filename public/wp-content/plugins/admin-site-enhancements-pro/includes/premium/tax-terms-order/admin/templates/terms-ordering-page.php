<div class="wrap">
    <h2>Taxonomy Terms Order for <?php echo esc_html( $post_type_label ); ?></h2>
    
    <noscript>
        <div class="error message">
            <p>Jquery library required to this plugin work.</p>
        </div>
    </noscript>

    <div class="clear"></div>
    
    <?php
    $current_section_parent_file = '';
    switch ( $post_type ) { 
        case 'attachment':
			$current_section_parent_file = 'upload.php';
			break;                
        default:
            $current_section_parent_file = 'edit.php';
            break;
    }
    ?>
    
    <form action="<?php echo esc_attr( $current_section_parent_file ); ?>" method="get" id="tax_terms_order_form">
        <input type="hidden" name="page" value="<?php echo esc_attr( $post_type ) ?>-terms-order" />
        <?php
    
        if ( ! in_array( $post_type, array( 'post', 'attachment' ) ) ) {
            echo '<input type="hidden" name="post_type" value="' . esc_attr( $post_type ) . '" />';
		}
        
        $post_type_taxonomies = get_object_taxonomies( $post_type ); // array of post type names/slugs
    
        foreach ( $post_type_taxonomies as $key => $taxonomy_name ) {
            $taxonomy_info = get_taxonomy( $taxonomy_name );
            
            if ( $taxonomy_info->hierarchical !== TRUE ) {
                unset( $post_type_taxonomies[$key] );
            }
        }
            
        
        if ( $taxonomy == '' || ! taxonomy_exists( $taxonomy ) ) {
            reset( $post_type_taxonomies );
            $taxonomy = current( $post_type_taxonomies );
        }
        
        // If there is more than one hierarchical taxonomies attached for the post type
        // We output a taxonomy selector that loads terms sortables via ajax
        if ( count($post_type_taxonomies) > 1 ) {
            $class_name = 'multiple-taxonomies';
        ?>
        <?php
        } else {
            $class_name = 'single-taxonomy';            
        }
        ?>
            <div class="post-type-taxonomies-list <?php echo esc_attr( $class_name ); ?>">
                <?php

                foreach ( $post_type_taxonomies as $post_type_taxonomy ) {
                    $taxonomy_info = get_taxonomy( $post_type_taxonomy );
                    $args = array(
                        'hide_empty'    =>  0,
                        'taxonomy'      =>  $post_type_taxonomy,
                    );
                    $taxonomy_terms = get_terms( $args );
                                     
                    ?>
                        <div class="taxonomy-row" id="taxonomy-<?php echo esc_attr( $taxonomy ); ?>">
                            <input type="radio" onclick="tax_terms_order_change_taxonomy(this)" value="<?php echo esc_attr( $post_type_taxonomy ); ?>" <?php if ( $post_type_taxonomy == $taxonomy ) { echo 'checked="checked"'; } ?> id="<?php echo esc_attr( $post_type_taxonomy ); ?>" name="taxonomy">
                            <label for="<?php echo esc_attr( $post_type_taxonomy ); ?>">
                                <div class="taxonomy-name-slug"><strong><?php echo esc_html( $taxonomy_info->label ); ?></strong> (<?php echo esc_html( $taxonomy_info->name ); ?>):</div> 
                                <div class="taxonomy-terms-count"><?php echo esc_html( count( $taxonomy_terms ) ); ?> terms</div>
                            </label>
                        </div>                        
                    <?php
                }
                ?>
            </div>

    <div id="order-terms">
        <div id="post-body">
                <ul class="sortable" id="wp_term_sortable">
                    <?php $instance->tto_terms_list( $taxonomy ); ?>
                </ul>
                <div class="clear"></div>
        </div>
        
        <div class="actions">
            <p class="submit">
                <a href="javascript:;" id="save-terms-order" class="save-order button-primary">Update</a>
            </p>
        </div>
    </div> 

    </form>
    
    <script type="text/javascript">
    jQuery(document).ready(function() {
        
        jQuery("ul.sortable").sortable({
                'tolerance':'intersect',
                'cursor':'pointer',
                'items':'> li',
                'toleranceElement':'> div',
                'axi': 'y',
                'placeholder':'placeholder',
                'nested': 'ul',
                'opacity': 0.6,
                update: function (event, ui) {
                    // Elements of the "Updating order..." notice
                    var updateNotice = jQuery('#updating-order-notice'), // Wrapper
                        spinner = jQuery('#spinner-img'), // Spinner
                        updateSuccess = jQuery('.updating-order-notice .dashicons.dashicons-saved'); // Check mark

                    ui.item.find('.item:first').append(updateNotice);

                    // Reset the state of the "Updating order..." indicator
                    jQuery(spinner).show();
                    jQuery(updateSuccess).hide();
                    jQuery(updateNotice).css('background-color','#eee').fadeIn();

                    jQuery('#save-terms-order').click();
                }
            });
          
        jQuery(".save-order").bind( "click", function() {
                var mySortable = new Array();
                jQuery(".sortable").each(  function(){
                    
                    var serialized = jQuery(this).sortable("serialize");
                    
                    var parent_tag = jQuery(this).parent().get(0).tagName;
                    parent_tag = parent_tag.toLowerCase()
                    if (parent_tag == 'li') {
                        var tag_id = jQuery(this).parent().attr('id');
                        mySortable[tag_id] = serialized;
                    } else {
                        mySortable[0] = serialized;
                    }
                });

                var updateNotice = jQuery('#updating-order-notice'), // Wrapper
                    spinner = jQuery('#spinner-img'), // Spinner
                    updateSuccess = jQuery('.updating-order-notice .dashicons.dashicons-saved'); // Check mark
                
                var serialize_data = JSON.stringify( array_to_object_conversion(mySortable));
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action:'update-taxonomy-order', 
                        order: serialize_data, 
                        nonce : '<?php echo esc_html( wp_create_nonce( 'update-taxonomy-order' ) ); ?>' 
                    },
                    success: function(response) {
                        // console.log('Order updated');
                        jQuery(spinner).hide();
                        jQuery(updateSuccess).show();
                        jQuery(updateNotice).css('background-color','#cce5cc').delay(1000).fadeOut();
                    },
                    error: function(errorThrown) {
                        console.log(errorThrown);
                    }
                    
                    
                });
            });
        

    }); 
    </script>

    <div id="updating-order-notice" class="updating-order-notice" style="display:none;"><img src="<?php echo esc_url( ASENHA_URL ) . 'assets/img/oval.svg'; ?>" id="spinner-img" class="spinner-img" /><span class="dashicons dashicons-saved" style="display:none;"></span>Updating order...</div>
</div>
