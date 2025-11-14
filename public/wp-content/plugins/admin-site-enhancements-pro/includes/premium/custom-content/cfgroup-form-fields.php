<?php

// wp_nonce_field( 'asenha_cfgroup_meta_box_nonce_action', 'asenha_cfgroup__meta_box_nonce_field' );

?>

<input type="hidden" name="afg[save]" value="<?php echo wp_create_nonce('afg_save_fields'); ?>" />

<section class="container">
  
	<ul class="tabs">
		<li class="tab-item"><div id="fields" class="item-active">Fields</div></li>
		<li class="tab-item"><div id="placement">Placement</div></li>
		<li class="tab-item"><div id="settings">Settings</div></li>
	</ul>

	<div class="wrapper_tab-content">
	    
	    <!-- FIELDS tab -->
	    <div id="tab-content-fields" class="tab-content content-visible">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-full-width">

<ul class="fields">
<?php

global $post;

$results = AFG()->api->get_input_fields( array( 'group_id' => $post->ID ) );

?>
		    	</div>
		    </div>
		</div>

	    <!-- PLACEMENT tab -->
	    <div id="tab-content-placement" class="tab-content">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-full-width">
		    		Placement here
		    	</div>
		    </div>
		</div>

	    <!-- SETTINGS tab -->
	    <div id="tab-content-settings" class="tab-content">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-full-width">
		    		Settings here
		    	</div>
		    </div>
		</div>

	</div>
	
</section>
