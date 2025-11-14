<?php

class cfgroup_line_break extends cfgroup_field
{

    function __construct() {
        $this->name = 'line_break';
        $this->label = __( 'Line Break', 'admin-site-enhancements' );
    }

    function options_html( $key, $field ) {
    	// Nothing to add
    }

    function html( $field ) {
    	?>
    	<span></span>
    	<?php
    }

}