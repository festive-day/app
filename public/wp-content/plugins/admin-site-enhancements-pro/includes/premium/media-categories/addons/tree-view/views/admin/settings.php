<?php
/**
 * Outputs the Tree View Settings at Media Categories Module > Settings.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

?>
<div id="tree-view" class="panel">
	<div class="postbox">
		<header>
			<h3><?php esc_html( 'Tree View Settings' ); ?></h3>

			<p class="description">
				<?php esc_html( 'Display a Category Tree sidebar when viewing the Media Library.' ); ?>
			</p>
		</header>

		<div class="wpzinc-option">
			<div class="left">
				<strong><?php esc_html( 'Enabled' ); ?></strong>
			</div>
			<div class="right">
				<select name="tree-view[enabled]" size="1" data-conditional="tree-view-enabled">
					<option value="1"<?php selected( $this->get_setting( 'tree-view', 'enabled' ), 1 ); ?>>
						<?php esc_attr( 'Enabled' ); ?>
					</option>
					<option value="0"<?php selected( $this->get_setting( 'tree-view', 'enabled' ), 0 ); ?>>
						<?php esc_attr( 'Disabled' ); ?>
					</option>
				</select>
			</div>
		</div>

		<div id="tree-view-enabled">
			<div class="wpzinc-option">
				<div class="left">
					<strong><?php esc_html( 'Expand/Collapse' ); ?></strong>
				</div>
				<div class="right">
					<select name="tree-view[jstree_enabled]" size="1">
						<option value="1"<?php selected( $this->get_setting( 'tree-view', 'jstree_enabled' ), 1 ); ?>>
							<?php esc_attr( 'Enabled' ); ?>
						</option>
						<option value="0"<?php selected( $this->get_setting( 'tree-view', 'jstree_enabled' ), 0 ); ?>>
							<?php esc_attr( 'Disabled' ); ?>
						</option>
					</select>

					<p class="description">
						<?php esc_html( 'If enabled, only top level Categories are displayed in the Tree View. Clicking the icon next to them will reveal Subcategories.' ); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
