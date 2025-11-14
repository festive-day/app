<?php
/**
 * Outputs an error message in the TinyMCE modal telling the user that a shortcode
 * could not be found i.e. it was not registered.
 *
 * @since   1.4.9
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

?>
<form class="wpzinc-tinymce-popup">
	<div class="notice error" style="display:block;">
		The shortcode could not be found. Check it is registered and its class initialized.
	</div>

	<div class="wpzinc-option buttons has-wpzinc-vertical-tabbed-ui">
		<div class="left">
			<button type="button" class="close button">Cancel</button>
		</div>
	</div>
</form>
