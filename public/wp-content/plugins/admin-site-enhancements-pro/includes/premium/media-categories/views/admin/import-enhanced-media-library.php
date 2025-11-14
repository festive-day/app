<?php
/**
 * Output Import from Enhanced Media Library options.
 *
 * @since   1.0.0
 *
 * @package Media_Categories_Module
 * @author  WP Media Library
 */

?>

<!-- Import from Enhanced Media Library-->
<div id="import_enhanced_media_library" class="panel">
	<div class="postbox">
		<header>
			<h3>Import from Enhanced Media Library</h3>
		</header>

		<div class="wpzinc-option">
			<div class="left">
				<strong>Taxonomies</strong>
			</div>
			<div class="right">
				<?php
				foreach ( $import_source['data']['taxonomies'] as $taxonomy_name => $eml_taxonomy ) {
					// Skip non-EML categories.
					if ( ! $eml_taxonomy['eml_media'] ) {
						continue;
					}
					?>
					<label for="taxonomies_<?php echo esc_attr( $taxonomy_name ); ?>">
						<input type="checkbox" name="taxonomies[]" id="taxonomies_<?php echo esc_attr( $taxonomy_name ); ?>" value="<?php echo esc_attr( $taxonomy_name ); ?>" />
						<?php echo esc_html( $eml_taxonomy['labels']['name'] ); ?>
					</label><br />
					<?php
				}
				?>

				<p class="description">
					Select the Taxonomies to import.  The Terms from the chosen Enhanced Media Library Taxonomies above will be imported into Media Categories Module's Media Categories Taxonomy.
				</p>
			</div>
		</div>

		<div class="wpzinc-option">
			<input name="import_enhanced_media_library" type="submit" class="button button-primary" value="Import" />              
		</div>
	</div>
</div>
