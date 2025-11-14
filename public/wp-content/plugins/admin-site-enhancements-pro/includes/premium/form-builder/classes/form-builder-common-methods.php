<?php

class Form_Builder_Common_Methods {
	
	public static function get_kses_extended_ruleset() {
	    $kses_defaults = wp_kses_allowed_html( 'post' );

	    // For SVG icons
		$svg_args = array(
		    'svg'   => array(
		        'class'				=> true,
		        'aria-hidden'		=> true,
		        'aria-labelledby'	=> true,
		        'role'				=> true,
		        'xmlns'				=> true,
		        'width'				=> true,
		        'height'			=> true,
		        'viewbox'			=> true,
		        'viewBox'			=> true,
		    ),
		    'g'     => array( 
		    	'fill' 				=> true,
		    	'fill-rule' 		=> true,
		        'stroke'			=> true,
		        'stroke-width'		=> true,
		        'stroke-linejoin'	=> true,
		        'stroke-linecap'	=> true,
		    ),
		    'title' => array( 'title' => true ),
		    'path'  => array( 
		        'd'					=> true,
		        'fill'				=> true,
		        'stroke'			=> true,
		        'stroke-width'		=> true,
		        'stroke-linejoin'	=> true,
		        'stroke-linecap'	=> true,
		    ),
		    'rect'	=> array(
		    	'width'				=> true,
		    	'height'			=> true,
		    	'x'					=> true,
		    	'y'					=> true,
		    	'rx'				=> true,
		    	'ry'				=> true,
		    	'fill' 				=> true,
		        'stroke'			=> true,
		        'stroke-width'		=> true,
		        'stroke-linejoin'	=> true,
		        'stroke-linecap'	=> true,
		    ),
		    'circle' => array(
		    	'cx'				=> true,
		    	'cy'				=> true,
		    	'r'					=> true,
		        'stroke'			=> true,
		        'stroke-width'		=> true,
		        'stroke-linejoin'	=> true,
		        'stroke-linecap'	=> true,
		    ),
		);

	    $kses_with_extras = array_merge( $kses_defaults, $svg_args );
	    
	    // For embedded PDF viewer
	    $style_script_args = array(
	    	'style'		=> true,
	    	'script'	=> array(
	    		'src'	=> true,
	    	),
	    );
	    
	    return array_merge( $kses_with_extras, $style_script_args );		
	}
}
