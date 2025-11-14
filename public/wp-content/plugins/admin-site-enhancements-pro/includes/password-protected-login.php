<?php

global $error, $password_protected_errors, $is_iphone;

$options = get_option( ASENHA_SLUG_U, array() );

if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
	$password_field_label = isset( $options['password_protection_password_field_label'] ) && ! empty( $options['password_protection_password_field_label']) ? $options['password_protection_password_field_label'] : __( 'Password', 'admin-site-enhancements' );
	$button_label = isset( $options['password_protection_button_label'] ) && ! empty( $options['password_protection_button_label'] ) ? $options['password_protection_button_label'] : __( 'View Content', 'admin-site-enhancements' );
} else {
	$password_field_label = __( 'Password', 'admin-site-enhancements' );
	$button_label = __( 'View Content', 'admin-site-enhancements' );
}

if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
	// Inherit styling from Login Page Customizer module if the module is enabled
	if ( array_key_exists( 'login_page_customizer', $options ) && $options['login_page_customizer'] ) {
        // Login Form Color Scheme
        $login_page_form_color_scheme = isset( $options['login_page_form_color_scheme'] ) ? $options['login_page_form_color_scheme'] : 'light';

		$common_methods = new ASENHA\Classes\Common_Methods;
        $login_form_section_color_bg = isset( $options['login_page_form_section_color_bg'] ) ? $options['login_page_form_section_color_bg'] : '#1e73be';
        $login_form_section_color_transparency = isset( $options['login_page_form_section_color_transparency'] ) ? $options['login_page_form_section_color_transparency'] : '0.8';
        $login_form_login_button_color =  isset( $options['login_page_login_button_color'] ) ? $options['login_page_login_button_color'] : '#2271b1';
        $is_login_button_color_dark = $common_methods->is_color_dark( $login_form_login_button_color );
        if ( $is_login_button_color_dark ) {
        	$login_button_text_color = '#fff';
        } else {
        	$login_button_text_color = '#3c434a';        	
        }


        $login_form_background = '';
        $login_form_label = '';
        // $login_form_link_color = '';

        switch ( $login_page_form_color_scheme ) {
        	case 'light':
		        $is_color_dark = false;
        		break;
        	case 'dark':
        		$login_form_background = 'background: rgba(0,0,0,.8);';
		        $is_color_dark = true;
        		break;
        	
        	case 'custom':
        		if ( ! empty( $login_form_section_color_bg ) ) {
	        		$login_form_background = 'background: ' . $common_methods->hex_to_rgba( $login_form_section_color_bg, $login_form_section_color_transparency ) . ';';
	        		$is_color_dark = $common_methods->is_color_dark( $login_form_section_color_bg );
        		}
        		break;
        }

	if ( $is_color_dark ) {
		$login_form_label = 'color: #fff;';
	        // $login_form_link_color = 'color: #fff';
	        $login_form_login_button_hover_color = $common_methods->adjust_bnrightness( $login_form_login_button_color, 0.15 );
	        $login_form_login_button_style = 'color: ' . $login_button_text_color . '; background: ' . $login_form_login_button_color . '; border-color: ' . $login_form_login_button_color . ';';
	        $login_form_login_button_hover_style = 'color: ' . $login_button_text_color . '; background: ' . $login_form_login_button_hover_color . '; border-color: ' . $login_form_login_button_hover_color . ';';
	} else {
		// $login_form_link_color = 'color: #50575e';
	        $login_form_login_button_hover_color = $common_methods->adjust_bnrightness( $login_form_login_button_color, -0.15 );
	        $login_form_login_button_style = 'color: ' . $login_button_text_color . '; background: ' . $login_form_login_button_color . '; border-color: ' . $login_form_login_button_color . ';';
	        $login_form_login_button_hover_style = 'color: ' . $login_button_text_color . '; background: ' . $login_form_login_button_hover_color . '; border-color: ' . $login_form_login_button_hover_color . ';';
	}

        // Login Page Background
        $login_page_background = isset( $options['login_page_background'] ) ? $options['login_page_background'] : '';
        $login_page_background_style = 'background: #f0f0f1;';

        switch ( $login_page_background ) {
        	case 'pattern':
                $background_pattern = ( isset( $options['login_page_background_pattern'] ) ) ? ASENHA_URL . 'assets/premium/img/patterns/' . $options['login_page_background_pattern'] . '.svg' : '';
                if ( ! empty( $background_pattern ) ) {
	                $background_pattern = "url($background_pattern)";
	                $login_page_background_style = 'background-image: ' . $background_pattern . ';
	                background-size: cover;
	                background-position: center center;
	                background-repeat: no-repeat;'; 
               	
                }
                break;

        	case 'image':
	        $background_image = $common_methods->get_image_url( 'login_page_background_image' );

                if ( ! empty( $background_image ) ) {
	                $background_image = "url($background_image)";
	                $login_page_background_style = 'background-image: ' . $background_image . ';
	                background-size: cover;
	                background-position: center center;
	                background-repeat: no-repeat;';                	
                }
        		break;
        		
        	case 'solid_color':
                $background_color = ( isset( $options['login_page_background_color'] ) ) ? $options['login_page_background_color'] : '';
                if ( ! empty( $background_color ) ) {
	                $login_page_background_style = 'background: ' . $background_color;
	            }
        		break;
        }

	}
}

/**
 * WP Shake JS
 */
if ( ! function_exists( 'wp_shake_js' ) ) {
	function wp_shake_js() {
		if ( isset( $is_iphone ) ) {
			if ( $is_iphone ) {
				return;			
			}
		}
		?>
		<script>
		addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
		function s(id,pos){g(id).left=pos+'px';}
		function g(id){return document.getElementById(id).style;}
		function shake(id,a,d){c=a.shift();s(id,c);if(a.length>0){setTimeout(function(){shake(id,a,d);},d);}else{try{g(id).position='static';wp_attempt_focus();}catch(e){}}}
		addLoadEvent(function(){ var p=new Array(15,30,15,0,-15,-30,-15,0);p=p.concat(p.concat(p));var i=document.forms[0].id;g(i).position='relative';shake(i,p,20);});
		</script>
		<?php
	}
}

nocache_headers();
header( 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=' . get_bloginfo( 'charset' ) );

// Maybe show error message above login form
$shake_error_codes = array( 'empty_password', 'incorrect_password' );
if ( $password_protected_errors->get_error_code() && in_array( $password_protected_errors->get_error_code(), $shake_error_codes ) ) {
	add_action( 'asenha_password_protection_login_head', 'wp_shake_js', 12 );
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width" />
	<meta name="robots" content="noindex">
	<title><?php bloginfo( 'name' ); ?></title>
	<?php
	wp_admin_css( 'login', true );
	do_action( 'asenha_password_protection_login_head' );
	if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
		if ( array_key_exists( 'login_page_customizer', $options ) && $options['login_page_customizer'] ) {
			?>
		    <style type="text/css" id="protected-page-login-style">
		    	body.protected-page-login {
		    		<?php echo wp_kses_post( $login_page_background_style ); ?>
		    	}

		    	.login #login {
					box-sizing: border-box;
					display: flex;
					flex-direction: column;
					justify-content: center;
					align-items: center;
	        		margin-top: 80px;
	        		min-width: 375px;
	        		height: unset;
	        		padding: 20px;
	        		margin-left: auto;
	        		margin-right: auto;
	        		border-radius: 4px;
					background: rgba(255,255,255,.8);
					<?php echo wp_kses_post( $login_form_background ); ?>;
					<?php echo wp_kses_post( $login_form_label ); ?>;
		    	}
		    	
		    	#login_error {
					box-sizing: border-box;
		    		width: 287px;
					border-left: 4px solid #d63638;
					padding: 12px;
					margin-top: 28px;
					margin-bottom: 0;
					background-color: #fff;
					box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
					word-wrap: break-word;
		    		color: #3c434a;
		    	}
		    			    	
		    	.login #login form {
		    		border: none;
		    		border-radius: 4px;
		    		box-shadow: none;
		    		margin-top: 0;
		    		padding: 24px;
		    		background: transparent;
		    	}
		    	        	
		    	.login label,
		    	#login form .indicator-hint, 
		    	#login #reg_passmail {
					<?php echo wp_kses_post( $login_form_label ); ?>;
		    	}
		    			    	
		    	.login input[type="text"],
		    	.login input[type="password"] {
		    		
		    	}
		    	
	        	.wp-core-ui #wp-submit.button-primary {
	        		<?php echo wp_kses_post( $login_form_login_button_style ); ?>;
	        	}

	        	.wp-core-ui #wp-submit.button-primary:hover {
	        		<?php echo wp_kses_post( $login_form_login_button_hover_style ); ?>;
	        	}
		    			    	        	
		    	@media (max-width: 782px) {        		
		    		.login #login {
		    			width: 348px;
		    			padding: 5% 0 0;
		    			margin: 80px auto auto;
		    			height: auto;
		    		}        		
		    	}

		    	@media (max-height: 480px) {
		    		.login #login {
		    			height: auto;
		    		}
		    	}
		    </style>
			<?php
		} else {
			?>
		    <style type="text/css" id="protected-page-login-style">
		    	#login_error {
					box-sizing: border-box;
		    		width: 320px;
					border-left: 4px solid #d63638;
					padding: 12px;
					margin-top: 20px;
					margin-bottom: 0;
					background-color: #fff;
					box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
					word-wrap: break-word;
		    		color: #3c434a;
		    	}
		    </style>
			<?php			
		}
	} else {
		?>
	    <style type="text/css" id="protected-page-login-style">
	    	#login_error {
				box-sizing: border-box;
	    		width: 287px;
				border-left: 4px solid #d63638;
				padding: 12px;
				margin-top: 20px;
				margin-bottom: 0;
				background-color: #fff;
				box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
				word-wrap: break-word;
	    		color: #3c434a;
	    	}
	    </style>
		<?php
	}
	?>
</head>
<body class="login protected-page-login wp-core-ui">

<div id="login">
	<?php do_action( 'asenha_password_protection_error_messages' ); ?>
	<form name="loginform" id="loginform" action="<?php echo esc_url( add_query_arg( 'protected-page', 'view', home_url( '/' ) ) ); ?>" method="post">
		<label for="protected_page_pwd"><?php echo esc_html( $password_field_label ); ?></label>
		<input type="password" name="protected_page_pwd" id="protected_page_pwd" class="input" value="" size="20" />
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php echo esc_attr( $button_label ); ?>" />
			<input type="hidden" name="protected-page" value="view" />
			<input type="hidden" name="source" value="<?php echo esc_attr( ! empty( $_REQUEST['source'] ) ? $_REQUEST['source'] : '' ); ?>" />
		</p>
	</form>
</div>

<?php do_action( 'login_footer' ); ?>

</body>
</html>