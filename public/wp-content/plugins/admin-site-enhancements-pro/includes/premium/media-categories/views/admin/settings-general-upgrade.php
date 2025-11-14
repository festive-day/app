<?php
/**
 * Output the Upgrade banner on the Settings General (Filters) panel.
 *
 * @since   1.0.0
 *
 * @package Media_Categories_Module
 * @author  WP Media Library
 */

?>

<div class="wpzinc-option highlight">
	<div class="full">
		<p>
			<?php
			echo esc_html(
				// sprintf(
				// /* translators: Plugin Name */
				// 	__( 'Filter by mutiple Categories and specific File Types with %s Pro', 'admin-site-enhancements' ),
				// 	$this->base->plugin->displayName
				// )
				'Filter by mutiple Categories and specific File Types with ' . $this->base->plugin->displayName . ' Pro'
			);
			?>
		</p>

		<a href="<?php echo esc_attr( $this->base->dashboard->get_upgrade_url( 'settings_inline_upgrade' ) ); ?>" class="button button-primary" target="_blank">
			Upgrade
		</a>
	</div>
</div>
