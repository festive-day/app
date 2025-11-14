<?php

// Manually load TinyMCE as the process done in CFG()->form->load_assets() is not working for term add/edit screens
wp_enqueue_script( 'wp-tinymce-root', '/wp-includes/js/tinymce/tinymce.min.js', array(), ASENHA_VERSION, false );
wp_enqueue_script( 'wp-tinymce', '/wp-includes/js/tinymce/plugins/compat3x/plugin.min.js?ver=49110-20201110', array( 'wp-tinymce-root' ), ASENHA_VERSION, false );

CFG()->form->load_assets();
wp_enqueue_script( 'cfgroup-terms', CFG_URL . '/assets/js/terms.js', [ 'jquery' ], CFG_VERSION ); // Has repeater field add/remove row JS

if ( $term ) {
    $term_id = $term->term_id;
} else {
    $term_id = 0;
}

echo CFG()->form( [
    'object_type'   => 'term',
    'term_id'       => $term_id,
    'field_groups'  => $args['group_id'], // never empty, always identifiable
    'front_end'     => false,
] );
