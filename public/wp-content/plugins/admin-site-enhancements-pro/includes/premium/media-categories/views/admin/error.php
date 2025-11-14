<?php
/**
 * Output the Settings header and an error notification.
 *
 * @since   1.0.0
 *
 * @package Media_Categories_Module
 * @author  WP Media Library
 */

?>

<div class="wrap">
	<h2>
		<span class="dashicons dashicons-admin-media"></span>
		<?php echo esc_html( $this->base->plugin->displayName ); ?> 
	</h2>

	<div class="error notice">
		<p><?php echo esc_html( $screen->get_error_message() ); ?></p>
	</div>
</div>
