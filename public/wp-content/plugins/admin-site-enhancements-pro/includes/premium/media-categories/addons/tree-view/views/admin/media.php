<?php
/**
 * Outputs the Tree View in the Media Library.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

?>
<div id="media-categories-module-tree-view">
	<form class="media-categories-module-tree-view-inner">
		<!-- <h2 class="wp-heading-inline"><?php // echo esc_html( $taxonomy->label ); ?></h2> -->

		<div class="wp-filter">
		<?php
		if ( current_user_can( 'manage_categories' ) ) {
			?>
			<div class="search-form">
				<!-- https://icon-sets.iconify.design/mdi/plus-outline/ -->
				<button class="button media-categories-module-tree-view-add"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M4 9h5V4h6v5h5v6h-5v5H9v-5H4V9m7 4v5h2v-5h5v-2h-5V6h-2v5H6v2h5Z"/></svg></button>
				<!-- https://icon-sets.iconify.design/tdesign/edit/ -->
				<button class="button media-categories-module-tree-view-edit" disabled><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M15.748 2.947a2 2 0 0 1 2.828 0l2.475 2.475a2 2 0 0 1 0 2.829L9.158 20.144l-6.38 1.076l1.077-6.38L15.748 2.947Zm-.229 3.057l2.475 2.475l1.643-1.643l-2.475-2.474l-1.643 1.642Zm1.06 3.89l-2.474-2.475l-8.384 8.384l-.503 2.977l2.977-.502l8.385-8.385Z"/></svg></button>
				<!-- https://icon-sets.iconify.design/mi/delete/ -->
				<button class="button media-categories-module-tree-view-delete" disabled><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M7 4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2h4a1 1 0 1 1 0 2h-1.069l-.867 12.142A2 2 0 0 1 17.069 22H6.93a2 2 0 0 1-1.995-1.858L4.07 8H3a1 1 0 0 1 0-2h4V4zm2 2h6V4H9v2zM6.074 8l.857 12H17.07l.857-12H6.074zM10 10a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1z"/></svg></button>
			</div>
			<?php
		}
		?>
		</div>

		<div id="media-categories-module-tree-view-list"<?php echo ( $jstree_enabled ? ' class="media-categories-module-tree-view-enabled"' : '' ); ?>>
			<?php echo $output; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

        <div id="media-categories-spinner" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path fill="currentColor" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"><animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg><?php echo esc_html( __( 'Categorizing...', 'admin-site-enhancements' ) ); ?></div>
	</form>
</div>
