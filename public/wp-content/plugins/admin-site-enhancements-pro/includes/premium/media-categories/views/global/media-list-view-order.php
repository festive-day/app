<?php
/**
 * Output Order By and Order Filters in the List View.
 *
 * @since   1.0.0
 *
 * @package Media_Categories_Module
 * @author  WP Media Library
 */

// Order By Filter.
if ( $this->base->get_class( 'settings' )->get_setting( 'general', 'orderby_enabled' ) ) {
	$asenha_media_orderby = $this->base->get_class( 'common' )->get_orderby_options();
	if ( ! empty( $asenha_media_orderby ) ) {
		?>
		<select name="orderby" id="asenha-media-orderby" size="1">
			<?php
			foreach ( $asenha_media_orderby as $key => $value ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $current_orderby, $key ); ?>><?php echo esc_html( $value ); ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}
}

// Order Filter.
if ( $this->base->get_class( 'settings' )->get_setting( 'general', 'order_enabled' ) ) {
	$asenha_media_order = $this->base->get_class( 'common' )->get_order_options();
	if ( ! empty( $asenha_media_order ) ) {
		?>
		<select name="order" id="asenha-media-order" size="1">
			<?php
			foreach ( $asenha_media_order as $key => $value ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $current_order, $key ); ?>><?php echo esc_html( $value ); ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}
}
