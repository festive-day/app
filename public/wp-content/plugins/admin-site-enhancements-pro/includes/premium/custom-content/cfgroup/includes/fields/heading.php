<?php

class cfgroup_heading extends cfgroup_field
{

    function __construct() {
        $this->name = 'heading';
        $this->label = __( 'Heading', 'admin-site-enhancements' );
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