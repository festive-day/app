<section class="container">
  
	<ul class="tabs">
		<li class="tab-item"><div id="fields" class="item-active">Fields</div></li>
		<li class="tab-item"><div id="placement">Placement</div></li>
		<li class="tab-item"><div id="settings">Settings</div></li>
	</ul>

	<div class="wrapper_tab-content">
	    
	    <!-- FIELDS tab -->
	    <div id="tab-content-fields" class="tab-content content-visible">
	    	<div id="cfgroup_fields" class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-full-width">
		    		<?php include( CFG_DIR . '/templates/meta_box_fields.php' ); ?>
		    	</div>
		    </div>
		</div>

	    <!-- PLACEMENT tab -->
	    <div id="tab-content-placement" class="tab-content">
	    	<div id="cfgroup_rules" class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-full-width">
		    		<?php include( CFG_DIR . '/templates/meta_box_rules.php' ); ?>
		    	</div>
		    </div>
		</div>

	    <!-- SETTINGS tab -->
	    <div id="tab-content-settings" class="tab-content">
	    	<div id="cfgroup_extras" class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-full-width">
		    		<?php include( CFG_DIR . '/templates/meta_box_extras.php' ); ?>
		    	</div>
		    </div>
		</div>

	</div>

</section>