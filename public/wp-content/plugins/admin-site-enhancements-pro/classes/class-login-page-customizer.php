<?php

namespace ASENHA\Classes;

/**
 * Class for Login Page Customizer module
 *
 * @since 6.9.5
 */
class Login_Page_Customizer {
	
	/**
	 * Enqueue jQuery on the login page
	 * 
	 * @since 7.0.0
	 */
	public function enqueue_jquery() {
	    wp_enqueue_script( 'jquery' );
	}
	
	/**
	 * Add custom style based on settings
	 * 
	 * @since 7.0.0
	 */
	public function add_custom_style() {
        $options = get_option( ASENHA_SLUG_U, array() );
	$common_methods = new Common_Methods;

        // Login Form Position
        $login_page_form_position = isset( $options['login_page_form_position'] ) ? $options['login_page_form_position'] : 'center';
        switch ( $login_page_form_position ) {
        	case 'left-edge':
        		$login_div_margintop = '';
        		$login_div_width = 'min-width: 375px;';
        		$login_div_height = 'height: 100%;';
        		$login_div_padding = 'padding: 20px;';
        		$login_div_left_margin = 'margin-left: 0;';
        		$login_div_right_margin = 'margin-right: auto;';
        		$login_div_border_radius = 'border-radius: 0;';
        		$language_switcher_padding = 'padding: 8px 0 24px;';
        		break;

        	case 'left-half':
        		$login_div_margintop = '';
        		$login_div_width = 'width: 50vw;';
        		$login_div_height = 'height: 100%;';
        		$login_div_padding = 'padding: 20px 10%;';
        		$login_div_left_margin = 'margin-left: 0;';
        		$login_div_right_margin = 'margin-right: auto;';
        		$login_div_border_radius = 'border-radius: 0;';
        		$language_switcher_padding = 'padding: 8px 0 24px;';
        		break;

        	case 'center':
        		$login_div_margintop = 'margin-top: 80px;';
        		$login_div_width = 'min-width: 375px;';
        		$login_div_height = 'height: unset;';
        		$login_div_padding = 'padding: 32px 20px 20px;';
        		$login_div_left_margin = 'margin-left: auto;';
        		$login_div_right_margin = 'margin-right: auto;';
        		$login_div_border_radius = 'border-radius: 4px;';
        		$language_switcher_padding = '';
        		break;

        	case 'right-half':
        		$login_div_margintop = '';
        		$login_div_width = 'width: 50vw;';
        		$login_div_height = 'height: 100%;';
        		$login_div_padding = 'padding: 20px 10%;';
        		$login_div_left_margin = 'margin-left: auto;';
        		$login_div_right_margin = 'margin-right: 0;';
        		$login_div_border_radius = 'border-radius: 0;';
        		$language_switcher_padding = 'padding: 8px 0 24px;';
        		break;

        	case 'right-edge':
        		$login_div_margintop = '';
        		$login_div_width = 'min-width: 375px;';
        		$login_div_height = 'height: 100%;';
        		$login_div_padding = 'padding: 20px;';
        		$login_div_left_margin = 'margin-left: auto;';
        		$login_div_right_margin = 'margin-right: 0;';
        		$login_div_border_radius = 'border-radius: 0;';
        		$language_switcher_padding = 'padding: 8px 0 24px;';
        		break;
        }

        // Login Form Color Scheme
        $login_page_form_color_scheme = isset( $options['login_page_form_color_scheme'] ) ? $options['login_page_form_color_scheme'] : 'light';
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
        $login_form_link_color = '';

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
	        $login_form_link_color = 'color: #fff';
	        $login_form_login_button_hover_color = $common_methods->adjust_bnrightness( $login_form_login_button_color, 0.15 );
	        $login_form_login_button_style = 'color: ' . $login_button_text_color . '; background: ' . $login_form_login_button_color . '; border-color: ' . $login_form_login_button_color . ';';
	        $login_form_login_button_hover_style = 'color: ' . $login_button_text_color . '; background: ' . $login_form_login_button_hover_color . '; border-color: ' . $login_form_login_button_hover_color . ';';
	} else {
		$login_form_link_color = 'color: #50575e';
	        $login_form_login_button_hover_color = $common_methods->adjust_bnrightness( $login_form_login_button_color, -0.15 );
	        $login_form_login_button_style = 'color: ' . $login_button_text_color . '; background: ' . $login_form_login_button_color . '; border-color: ' . $login_form_login_button_color . ';';
	        $login_form_login_button_hover_style = 'color: ' . $login_button_text_color . '; background: ' . $login_form_login_button_hover_color . '; border-color: ' . $login_form_login_button_hover_color . ';';
	}
	        
        // Logo Image
        $logo_image_type = ( isset( $options['login_page_logo_image_type'] ) ) ? $options['login_page_logo_image_type'] : 'custom';
	// In older versions, width was stored with the 'px' suffix, e.g. 240px
	// Since v7.7.0, introducing the ratio calculator, it will be stored as number only, e.g. 240
	// We compensate for that legacy and change here
        $login_page_logo_image_style = '';

        switch ( $logo_image_type ) {
        	case 'site_icon';
        		$logo_image = get_site_icon_url( 256 );
        		break;

        	case 'custom'; // Media library
		        $logo_image = $common_methods->get_image_url( 'login_page_logo_image' );
		        if ( isset( $options['login_page_logo_image_width'] ) ) {
		        	$logo_image_width = str_replace( 'px', '', trim( $options['login_page_logo_image_width'] ) );
		        	$logo_image_width = $logo_image_width . 'px';
		        }
		        if ( isset( $options['login_page_logo_image_height'] ) ) {
		        	$logo_image_height = str_replace( 'px', '', trim( $options['login_page_logo_image_height'] ) );
		        	$logo_image_height = $logo_image_height . 'px';
		        }
        		break;

        	case 'custom_url';
		        $logo_image_legacy = $common_methods->get_image_url( 'login_page_logo_image' );
		        $logo_image = ( isset( $options['login_page_logo_image_external'] ) ) ? $options['login_page_logo_image_external'] : $logo_image_legacy;
		        $logo_image_width = isset( $options['login_page_logo_image_width_external'] ) ? $options['login_page_logo_image_width_external'] . 'px' : '';
		        $logo_image_height = isset( $options['login_page_logo_image_height_external'] ) ? $options['login_page_logo_image_height_external'] . 'px' : '';
        		break;
        }

        switch ( $logo_image_type ) {
        	case 'site_icon';
			$login_page_logo_image_style = 'background-image: url(' . $logo_image . ') !important;';
        		break;

        	case 'custom'; // Media library
        	case 'custom_url';
		        if ( ! empty( $logo_image ) ) {
		            $logo_image = "url($logo_image)";
		            $login_page_logo_image_style = 'background-image: ' . $logo_image . ' !important;';                	
		        }

		        if ( ! empty( $logo_image_width ) && empty( $logo_image_height ) ) {
		        	$login_page_logo_image_style .= 'background-size: ' . $logo_image_width . ' !important;';
		        	$login_page_logo_image_style .= 'width: ' . $logo_image_width . ' !important;';
		        }

		        if ( empty( $logo_image_width ) && ! empty( $logo_image_height ) ) {
		        	$login_page_logo_image_style .= 'background-size: ' . $logo_image_height . ' !important;';
		        	$login_page_logo_image_style .= 'height: ' . $logo_image_height . ' !important;';
		        }
		        
		        if ( ! empty( $logo_image_width ) && ! empty( $logo_image_height ) ) {
		        	$login_page_logo_image_style .= 'background-size: ' . $logo_image_width . ' ' . $logo_image_height . ' !important;';
		        	$login_page_logo_image_style .= 'width: ' . $logo_image_width . ' !important;';
		        	$login_page_logo_image_style .= 'height: ' . $logo_image_height . ' !important;';
		        }

		        if ( empty( $logo_image_width ) && empty( $logo_image_height ) ) {
		        	$login_page_logo_image_style .= 'background-size: 84px !important;';
		        }
        		break;
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

        // Hide Elements
        $login_page_hide_remember_me = isset( $options['login_page_hide_remember_me'] ) ? $options['login_page_hide_remember_me'] : false;
    	$remember_me_display = ( $login_page_hide_remember_me ) ? 'display: none;' : '';

        $login_page_hide_registration_reset = isset( $options['login_page_hide_registration_reset'] ) ? $options['login_page_hide_registration_reset'] : false;
    	$login_nav_display = ( $login_page_hide_registration_reset ) ? 'display: none;' : '';

        $login_page_hide_homepage_link = isset( $options['login_page_hide_homepage_link'] ) ? $options['login_page_hide_homepage_link'] : false;
    	$homepage_link_display = ( $login_page_hide_homepage_link ) ? 'display: none;' : '';

    	// External CSS
    	$external_css_url = isset( $options['login_page_external_css'] ) && ! empty( $options['login_page_external_css'] ) ? $options['login_page_external_css'] : '';
    	
    	// Custom CSS
    	$custom_css = isset( $options['login_page_custom_css'] ) && ! empty( $options['login_page_custom_css'] ) ? $options['login_page_custom_css'] : '';

        ?>
        <style type="text/css" id="login-page-customizer-style">
        	body:not(.interim-login) {
        		<?php echo wp_kses_post( $login_page_background_style ); ?>
        	}

        	.login:not(.interim-login) #login {
        		box-sizing: border-box;
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				background: rgba(255,255,255,.8);
        		<?php echo wp_kses_post( $login_div_margintop ); ?>
        		<?php echo wp_kses_post( $login_div_width ); ?>
				<?php echo wp_kses_post( $login_div_height ); ?>;
				<?php echo wp_kses_post( $login_div_padding ); ?>;
				<?php echo wp_kses_post( $login_div_left_margin ); ?>;
				<?php echo wp_kses_post( $login_div_right_margin ); ?>;
				<?php echo wp_kses_post( $login_div_border_radius ); ?>;
				<?php echo wp_kses_post( $login_form_background ); ?>;
				<?php echo wp_kses_post( $login_form_label ); ?>;
        	}
        	
        	#login_error,
        	.login .message, 
        	.login .notice, 
        	.login .success {
        		margin-top: 20px;
        		margin-bottom: 0;
        		color: #3c434a;
        	}
        	
        	.login:not(.interim-login) h1 a,
        	.login:not(.interim-login) h1.wp-login-logo a {
        		margin: 0 auto;
        		<?php echo wp_kses_post( $login_page_logo_image_style ); ?>;
        	}
        	
        	.login:not(.interim-login) #login form {
        		border: none;
        		border-radius: 4px;
        		box-shadow: none;
        		margin-top: 0;
        		padding: 24px;
        		background: transparent;
        	}
        	        	
        	.login:not(.interim-login) label,
        	#login form .indicator-hint, 
        	#login #reg_passmail {
				<?php echo wp_kses_post( $login_form_label ); ?>;
        	}
        	
        	.login:not(.interim-login) .forgetmenot label {
        	}
        	
        	.login:not(.interim-login) input[type="text"],
        	.login:not(.interim-login) input[type="password"] {
        		
        	}
        	
        	.wp-core-ui #wp-submit.button-primary {
        		<?php echo wp_kses_post( $login_form_login_button_style ); ?>;
        	}

        	.wp-core-ui #wp-submit.button-primary:hover {
        		<?php echo wp_kses_post( $login_form_login_button_hover_style ); ?>;
        	}
        	
        	.forgetmenot {
        		<?php echo wp_kses_post( $remember_me_display ); ?>;
        	}
        	
        	.login:not(.interim-login) #nav {
        		margin: 0 0 16px;
        		<?php echo wp_kses_post( $login_nav_display ); ?>;
        	}
        	
        	.login:not(.interim-login) #backtoblog {
        		<?php echo wp_kses_post( $homepage_link_display ); ?>;        		
        	}
        	
        	.login:not(.interim-login) #nav a, 
        	.login:not(.interim-login) #backtoblog a,
        	.login:not(.interim-login) .privacy-policy-page-link a {
        		text-decoration: none;
        		<?php echo wp_kses_post( $login_form_link_color ); ?>
        	}

        	.login:not(.interim-login) #nav a:hover, 
        	.login:not(.interim-login) #backtoblog a:hover,
        	.login:not(.interim-login) .privacy-policy-page-link a:hover {
        		text-decoration: underline;
        	}
        	
        	.login .privacy-policy-page-link {
        		margin: 0;
        	}
        	
        	.login:not(.interim-login) #backtoblog {
        		margin: 0 0 16px;
        	}
        	
        	.language-switcher {
				<?php echo wp_kses_post( $language_switcher_padding ); ?>;
        	}

        	.login:not(.interim-login) #login form#language-switcher {
        		padding: 0;
        	}
        	
        	.login:not(.interim-login) .message.register {
				border-left: 0;
				background: transparent;
				box-shadow: none;
				margin-top: 20px;
				margin-bottom: 0;
				padding: 0;
				font-size: 16px;
				font-weight: 500;
        	}
        	        	
        	@media (max-width: 782px) {        		
        		.login:not(.interim-login) #login {
        			width: 348px;
        			padding: 5% 0 0;
        			margin: 80px auto auto;
        			height: auto;
        		}        		
        	}

        	@media (max-height: 480px) {        		
        		.login:not(.interim-login) #login {
        			height: auto;
        		}        		
        	}        	
        </style>
        <style type="text/css" id="login-page-customizer-custom-style">
		<?php if ( ! empty( $external_css_url ) ) : ?>
		@import url("<?php echo wp_kses_post( $external_css_url ); ?>");
		<?php endif; ?>

        	<?php echo wp_strip_all_tags( $custom_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </style>        
        <script id="login-page-customizer-script">
        	jQuery(document).ready(function() {
        		if ( jQuery('.language-switcher') ) {
        			jQuery('.language-switcher').appendTo('#login');
        		}
        	});
        </script>
        <?php		
	}
	
	/**
	 * Maybe disable registration
	 * 
	 * @since 7.0.0
	 */
	public function maybe_disable_registration() {		
        $options = get_option( ASENHA_SLUG_U, array() );
        $login_page_disable_registration = isset( $options['login_page_disable_registration'] ) ? $options['login_page_disable_registration'] : 'center';
        
        if ( $login_page_disable_registration ) {
        	update_option( 'users_can_register', 0 );
        } else {
        	update_option( 'users_can_register', 1 );        	
        }
	}

}