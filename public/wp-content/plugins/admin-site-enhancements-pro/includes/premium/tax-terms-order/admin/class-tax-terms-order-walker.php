<?php

class Tax_Terms_Order_Walker extends Walker {

    var $db_fields = array (
        'parent'    => 'parent', 
        'id'        => 'term_id',
    );

    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        extract( $args, EXTR_SKIP ); 
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent<ul class='children sortable'>\n";
    }

    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        extract( $args, EXTR_SKIP );     
        $indent = str_repeat( "\t", $depth );
        $output .= "$indent</ul>\n";
    }

    public function start_el( &$output, $term, $depth = 0, $args = array(), $current_object_id = 0 ) {
        $page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        $page = explode( '-', $page );
        $post_type = $page[0];

        $term_edit_link = add_query_arg( array(
            'post_type' => $post_type,
            'taxonomy'  => $term->taxonomy,
            'tag_ID'    => $term->term_id,
        ), admin_url( 'term.php' ) );
        
        if ( $depth ) {
            $indent = str_repeat( "\t", $depth );
        } else{
            $indent = '';
		}
        
        $output .= $indent . '<li class="term_type_li" id="item_'.$term->term_id.'"><div class="item"><span class="dashicons dashicons-menu"></span><a href="' . $term_edit_link . '" class="item-term">' . $term->name . '</a></div>';
    }

    public function end_el( &$output, $object, $depth = 0, $args = array() ) {
        $output .= "</li>\n";
    }

}