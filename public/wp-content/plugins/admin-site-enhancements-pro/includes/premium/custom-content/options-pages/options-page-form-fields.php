<?php

wp_nonce_field( 'asenha_cpt_ctax_optionp_meta_box_nonce_action', 'asenha_cpt_ctax_optionp__meta_box_nonce_field' );

?>
<section class="container">
  
	<ul class="tabs">
		<li class="tab-item"><div id="main" class="item-active">Main</div></li>
		<li class="tab-item"><div id="advanced">Advanced</div></li>
	</ul>

	<div class="wrapper_tab-content">
	    
	    <!-- MAIN tab -->
	    <div id="tab-content-main" class="tab-content content-visible">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-full-width">
		            <label for="options_page_title">Page Title <span class="required">*</span></label>
		            <input type="text" name="options_page_title" id="options_page_title" placeholder="e.g. Site Options" value="<?php echo $options_page_title; ?>" maxlength="200" required />
		    	</div>
		    	<div class="cct-form-input cct-form-input-halves">
		            <label for="options_page_menu_title">Menu Title <span class="required">*</span></label>
		            <input type="text" name="options_page_menu_title" id="options_page_menu_title" placeholder="e.g. Site Options" value="<?php echo $options_page_menu_title; ?>" maxlength="220" required />
		            <div class="input-description">What is shown in the admin side menu.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-halves">
		            <label for="options_page_menu_slug">Menu Key/Slug <span class="required">*</span></label>
		            <input type="text" name="options_page_menu_slug" id="options_page_menu_slug" placeholder="e.g. site-options" value="<?php echo $options_page_menu_slug; ?>" maxlength="220" required />
		            <div class="input-description">Only lowercase alphanumeric characters, dashes, and underscores.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-halves">
		            <label for="options_page_menu_icon"> Menu icon</label>
		            <?php
		            $options_page_menu_icon_svg = base64_decode( str_replace( 'data:image/svg+xml;base64,', '', $options_page_menu_icon ) );
		            ?>
		            <div class="icon-picker">
				    	<div id="selected-menu-icon" class="selected-menu-icon"><?php echo $options_page_menu_icon_svg; ?></div>		
			            <input type="text" name="options_page_menu_icon" id="the_menu_icon" value="<?php echo $options_page_menu_icon; ?>" placeholder="e.g. dashicons-media-video" />
		            </div>
		            <div class="input-description" style="display: none;"><a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a> slug or <a href="https://developer.wordpress.org/reference/functions/register_post_type/#menu_icon" target="_blank">base64-encoded SVG</a>.</div>
		            <div class="choose-search-icon">
			            <button id="icon-picker-button" class="button">Change Icon</button>
			            <input type="search" id="search-input" placeholder="Search..." style="display:none;" />
		            </div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-halves">
		            <label for="options_page_parent_menu">Parent Menu</label>
		            <select name="options_page_parent_menu" id="options_page_parent_menu" tabindex="18">
		                <option value="none" <?php selected( $options_page_parent_menu, 'none' ); ?>>No Parent</option>
		            <?php
		            global $menu;
		            foreach ( $menu as $menu_item ) {
		            	// Only show as option menu items that are not separators
		            	if ( false === strpos( $menu_item[2], 'separator' ) ) {
		            		if ( 'asenha_show_hidden_menu' != $menu_item[2] && 'asenha_hide_hidden_menu' != $menu_item[2] ) {
			            		?>
				                <option value="<?php echo esc_attr( $menu_item[2] ); ?>" <?php selected( $options_page_parent_menu, $menu_item[2] ); ?>><?php echo esc_html( $common_methods->strip_html_tags_and_content( $menu_item[0] ) ); ?></option>
			            		<?php
		            		}
		            	}
		            }
		            ?>
		            </select>
		            <div class="input-description">This option page will be placed as a sub-menu.</div>
		    	</div>
		    	<div id="menu-icons-row" class="cct-form-input cct-form-input-full-width zero-margin-bottom">
		    		<span id="close-icon-picker" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><path fill="currentColor" d="M15.1 3.1L12.9.9L8 5.9L3.1.9L.9 3.1l5 4.9l-5 4.9l2.2 2.2l4.9-5l4.9 5l2.2-2.2l-5-4.9z"/></svg></span>
		    	<?php
					require_once ASENHA_PATH . 'includes/premium/custom-content/assets/img/menu-icons.php';
		    	?>
		    	</div>
	    	</div>
	    </div>
	    
	    <!-- ADVANCED tab -->
 	    <div id="tab-content-advanced" class="tab-content">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-halves">
		            <label for="options_page_capability">Capability</label>
		            <select name="options_page_capability" id="options_page_capability" tabindex="18">
		                <option value="manage_options" <?php selected( $options_page_capability, 'manage_options' ); ?>>manage_options (Administrators)</option>
		                <option value="edit_others_posts" <?php selected( $options_page_capability, 'edit_others_posts' ); ?>>edit_others_posts (Administrators, Editors)</option>
		                <option value="custom" <?php selected( $options_page_capability, 'custom' ); ?>>Custom</option>
		            </select>
		            <input type="text" name="options_page_capability_custom" id="options_page_capability_custom" value="<?php echo $options_page_capability_custom; ?>" placeholder="Enter capability name, e.g. manage_woocommerce" style="display:none;" />
		            <div class="input-description">The <a href="https://developer.wordpress.org/reference/functions/register_post_type/#capability_type" target="_blank">capability</a> required for this menu to be displayed to the user.</div>
		        </div>
		    	<div class="cct-form-input cct-form-input-halves">
		    	</div>
		    </div>
	    </div>
	    
	</div>
  
</section>
<?php