<?php

global $post;

/*---------------------------------------------------------------------------------------------
    Field management screen
---------------------------------------------------------------------------------------------*/

if ( 'post' == $screen->base ) {

    if ( 'asenha_cfgroup' == $screen->post_type ) {
        foreach ( CFG()->fields as $field_name => $field_data ) {
            ob_start();
            CFG()->fields[ $field_name ]->options_html( 'clone', $field_data );
            $options_html[ $field_name ] = ob_get_clean();
        }

        $field_count = get_post_meta( $post->ID, 'cfgroup_fields', true );
        $field_count = is_array( $field_count ) ? count( $field_count ) : 0;

        // Build clone HTML
        $field = (object) [
            'id'            => 0,
            'parent_id'     => 0,
            'name'          => 'new_field',
            'label'         => __( 'New Field', 'admin-site-enhancements' ),
            'type'          => 'text',
            'notes'         => '',
            'weight'        => 'clone',
            'column_width'  => 'full',
        ];

        ob_start();
        CFG()->field_html( $field );
        $field_clone = ob_get_clean();
    ?>

    <script>
    var CFG = CFG || {};
    CFG['field_index'] = <?php echo $field_count; ?>;
    CFG['field_clone'] = <?php echo json_encode( $field_clone ); ?>;
    CFG['options_html'] = <?php echo json_encode( $options_html ); ?>;
    </script>
    <script src="<?php echo CFG_URL; ?>/assets/js/fields.js"></script>
    <script src="<?php echo CFG_URL; ?>/assets/js/select2/select2.min.js"></script>
    <script src="<?php echo CFG_URL; ?>/assets/js/jquery-powertip/jquery.powertip.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo CFG_URL; ?>/assets/css/fields.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo CFG_URL; ?>/assets/js/select2/select2.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo CFG_URL; ?>/assets/js/jquery-powertip/jquery.powertip.css" />

    <?php
    }

    /*---------------------------------------------------------------------------------------------
        Field input
    ---------------------------------------------------------------------------------------------*/

    else {
        $hide_editor = false;
        $field_groups = CFG()->api->get_matching_groups( $post->ID );

        if ( ! empty( $field_groups ) ) {

            // Store field group IDs as an array for front-end forms
            CFG()->group_ids = array_keys( $field_groups );

            // Support for multiple metaboxes
            foreach ( $field_groups as $group_id => $title ) {

                // Get field group options
                $rules = get_post_meta( $group_id, 'cfgroup_rules', true );
                if ( in_array( $rules['placement']['values'], array( 'posts', 'options-pages' ) ) ) {
                    $extras = get_post_meta( $group_id, 'cfgroup_extras', true );
                    $context = isset( $extras['context'] ) ? $extras['context'] : 'normal';
                    $priority = ( 'normal' == $context ) ? 'high' : 'core';

                    if ( isset( $extras['hide_editor'] ) && 0 < (int) $extras['hide_editor'] ) {
                        $hide_editor = true;
                    }

                    $args = [ 'box' => 'input', 'group_id' => $group_id ];
                    add_meta_box( "cfgroup_input_$group_id", $title, [ $this, 'meta_box' ], $post->post_type, $context, $priority, $args );
                    add_filter( "postbox_classes_{$post->post_type}_cfgroup_input_{$group_id}", 'cfgroup_postbox_classes' );
                }
            }

            // Force editor support
            $has_editor = post_type_supports( $post->post_type, 'editor' );
            add_post_type_support( $post->post_type, 'editor' );

            if ( ! $has_editor || $hide_editor ) {
                echo '<style type="text/css">#poststuff .postarea { display: none; }</style>';
            }
        }
    }
    
} 
// else if ( 'term' == $screen->base ) {
    // Do something for taxonomy term screens
// }

function cfgroup_postbox_classes( $classes ) {
    $classes[] = 'cfgroup_input';
    return $classes;
}
